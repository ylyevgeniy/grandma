<?php
/** Enable W3 Total Cache */
define('WP_CACHE', true); // Added by W3 Total Cache

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'grandmac_a2wp125');

/** MySQL database username */
define('DB_USER', 'grandmac_a2wp125');

/** MySQL database password */
define('DB_PASSWORD', '(-27G0Seep');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'ruqrsf59diiz2souqncompzyo3rorzidlxc2nczqgvikgrku5we0ba4ep0nwcxra');
define('SECURE_AUTH_KEY',  'm8pnltg5xyfp7yn2ntaltthzi5avsdum6fwo4telha3cgmlklpcgrr0xo8iko3dy');
define('LOGGED_IN_KEY',    '8jxjespp4uvwzsyfnhhwhd5otqkib55upumm5jsmcwq8na6uhaomsm1dpwmdikqj');
define('NONCE_KEY',        'ksjz5zfvtzaiblfaj8kdfyp2jeczfhh6c5zahvaqarlrtn7tv8ve52c6nav6d4nq');
define('AUTH_SALT',        'zknr9i3nup747v5kal80xio0avasywowitdvkms5cqzluzmogwaf0luurqjybx7w');
define('SECURE_AUTH_SALT', 'poladt8piv40t3yudmhobhoos7d4q9ndvqyaegs3ctsueymrwcjie8i4t1cpnmtb');
define('LOGGED_IN_SALT',   'pqybtc3tu17zbi822qmrfysls0zjsqgrjs4ywigcmfbhlrmsuhzv8a5bbwg81c1g');
define('NONCE_SALT',       'exquemnfbtb1frvchtvmust7rtr994opjctggqsbv3jqoukxyvjmgr5nwbszgese');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wpls_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
