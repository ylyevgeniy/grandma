<?php

/**
 * PECL Memcached class
 */
if (!defined('W3TC')) {
	die();
}

w3_require_once(W3TC_LIB_W3_DIR . '/Cache/Base.php');

/**
 * Class W3_Cache_Memcached
 */
class W3_Cache_Memcached extends W3_Cache_Base {
	/**
	 * Memcache object
	 *
	 * @var Memcache
	 */
	private $_memcache = null;

	/*
	 * Used for faster flushing
	 *
	 * @var integer $_key_version
	 */
	private $_key_version = array();

	/*
	 * Unique keys for this site
	 *
	 * @var integer $_key_version
	 */
	private $key_prefix;

	/**
	 * constructor
	 *
	 * @param array $config
	 */
	public function __construct($config) {
		parent::__construct($config);

		$isMemcached = class_exists('Memcached')?true:false;
		$persistant = isset($config['persistant']) ? (boolean) $config['persistant'] : false;

		if ($isMemcached) {
			$this->_memcache = new Memcached($persistant?'persist':'');
			$this->_memcache->setOption(Memcached::OPT_COMPRESSION, false);
		} else {
			$this->_memcache = new Memcache();
		}

		if (!empty($config['servers'])) {
			foreach ((array) $config['servers'] as $server) {
				if (substr($server, 0, 5) == 'unix:' || substr($server, 0, 4) == '/opt') {
					$this->_memcache->addServer(trim($server), 0, $isMemcached?0:$persistant);
				} else {
					list($ip, $port) = explode(':', $server);
					$this->_memcache->addServer(trim($ip), (integer) trim($port), $isMemcached?0:$persistant);
				}
			}
		} else {
			return false;
		}

		if (!empty($config['compress_threshold'])) {
			if ($isMemcached) {
				ini_set('memcached.compression_threshold', $config['compress_threshold']);
			} else {
				$this->_memcache->setCompressThreshold((integer) $config['compress_threshold']);
			}
		}

		$this->key_prefix = md5(__DIR__);

		return true;
	}

	/**
	 * Adds data
	 *
	 * @param string $key
	 * @param mixed $var
	 * @param integer $expire
	 * @param string $group Used to differentiate between groups of cache values
	 * @return boolean
	 */
	public function add($key, &$var, $expire = 0, $group = '') {
		$key = $this->key_prefix . $key;

		return $this->set($key, $var, $expire, $group);
	}

	/**
	 * Sets data
	 *
	 * @param string $key
	 * @param mixed $var
	 * @param integer $expire
	 * @param string $group Used to differentiate between groups of cache values
	 * @return boolean
	 */
	public function set($key, $var, $expire = 0, $group = '') {
		$key = $this->key_prefix . $key;
		$key = $this->get_item_key($key);

		$var['key_version'] = $this->_get_key_version($group);

		if (class_exists('Memcached')) {
			$res = @$this->_memcache->set($key . '_' . $this->_blog_id, $var, $expire);
		} else {
			$res = @$this->_memcache->set($key . '_' . $this->_blog_id, $var, false, $expire);
		}

		return $res;
	}

	/**
	 * Returns data
	 *
	 * @param string $key
	 * @param string $group Used to differentiate between groups of cache values
	 * @return mixed
	 */
	public function get_with_old($key, $group = '') {
		$key = $this->key_prefix . $key;
		$has_old_data = false;

		$key = $this->get_item_key($key);

		$v = @$this->_memcache->get($key . '_' . $this->_blog_id);
		if (!is_array($v) || !isset($v['key_version'])) {
			return array(null, $has_old_data);
		}

		$key_version = $this->_get_key_version($group);
		if ($v['key_version'] == $key_version) {
			return array($v, $has_old_data);
		}

		if ($v['key_version'] > $key_version) {
			$this->_set_key_version($v['key_version'], $group);

			return array($v, $has_old_data);
		}

		// key version is old
		if (!$this->_use_expired_data) {
			return array(null, $has_old_data);
		}

		// if we have expired data - update it for future use and let
		// current process recalculate it
		$expires_at = isset($v['expires_at']) ? $v['expires_at'] : null;
		if ($expires_at == null || time() > $expires_at) {
			$v['expires_at'] = time() + 30;

			if (class_exists('Memcached')) {
				@$this->_memcache->set($key . '_' . $this->_blog_id, $v, 0);
			} else {
				@$this->_memcache->set($key . '_' . $this->_blog_id, $v, false, 0);
			}

			$has_old_data = true;

			return array(null, $has_old_data);
		}

		// return old version
		return array($v, $has_old_data);
	}

	/**
	 * Replaces data
	 *
	 * @param string $key
	 * @param mixed $var
	 * @param integer $expire
	 * @param string $group Used to differentiate between groups of cache values
	 * @return boolean
	 */
	public function replace($key, &$var, $expire = 0, $group = '') {
		$key = $this->key_prefix . $key;

		return $this->set($key, $var, $expire, $group);
	}

	/**
	 * Deletes data
	 *
	 * @param string $key
	 * @param string $group
	 * @return boolean
	 */
	public function delete($key, $group = '') {
		$key = $this->key_prefix . $key;
		$key = $this->get_item_key($key);

		if ($this->_use_expired_data) {
			$v = @$this->_memcache->get($key . '_' . $this->_blog_id);
			if (is_array($v)) {
				$v['key_version'] = 0;

				if (class_exists('Memcached')) {
					@$this->_memcache->set($key . '_' . $this->_blog_id, $v, 0);
				} else {
					@$this->_memcache->set($key . '_' . $this->_blog_id, $v, false, 0);
				}

				return true;
			}
		}

		return @$this->_memcache->delete($key . '_' . $this->_blog_id, 0);
	}

	/**
	 * Key to delete, deletes .old and primary if exists.
	 * @param $key
	 * @return bool
	 */
	public function hard_delete($key) {
		$key = $this->key_prefix . $key;
		$key = $this->get_item_key($key);

		return @$this->_memcache->delete($key . '_' . $this->_blog_id, 0);
	}

	/**
	 * Flushes all data
	 *
	 * @param string $group Used to differentiate between groups of cache values
	 * @return boolean
	 */
	public function flush($group = '') {
		$this->_get_key_version($group);   // initialize $this->_key_version
		$this->_key_version[$group]++;
		$this->_set_key_version($this->_key_version[$group], $group);

		return true;
	}

	/**
	 * Checks if engine can function properly in this environment
	 * @return bool
	 */
	public function available() {
		return class_exists('Memcache') || class_exists('Memcached');
	}

	/**
	 * Returns key version
	 *
	 * @param string $group Used to differentiate between groups of cache values
	 * @return integer
	 */
	private function _get_key_version($group = '') {
		if (!isset($this->_key_version[$group]) || $this->_key_version[$group] <= 0) {
			$v = @$this->_memcache->get($this->_get_key_version_key($group));
			$v = intval($v);
			$this->_key_version[$group] = ($v > 0 ? $v : 1);
		}

		return $this->_key_version[$group];
	}

	/**
	 * Sets new key version
	 *
	 * @param $v
	 * @param string $group Used to differentiate between groups of cache values
	 * @return boolean
	 */
	private function _set_key_version($v, $group = '') {
		if (class_exists('Memcached')) {
			@$this->_memcache->set($this->_get_key_version_key($group), $v, 0);
		} else {
			@$this->_memcache->set($this->_get_key_version_key($group), $v, false, 0);
		}
	}
}
