<?php
//Begin Really Simple Security key
define('RSSSL_KEY', 'omQqwnFls5QJogTqic98tnDTXBB4RXDT4yeP78BAHEsrN5DCtc56D0ZkA6CIIPJW');
//END Really Simple Security key

//Begin Really Simple SSL session cookie settings
@ini_set('session.cookie_httponly', true);
@ini_set('session.cookie_secure', true);
@ini_set('session.use_only_cookies', true);
//END Really Simple SSL cookie settings
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'lastrega_playground' );

/** Database username */
define( 'DB_USER', 'lastrega_wp384' );

/** Database password */
define( 'DB_PASSWORD', 'O]]72pS9XV' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'j66kz46ysoeqdifols6mktshzwv9j7ghz5w66aklc3bv26ia2dszgvkroeim0v05' );
define( 'SECURE_AUTH_KEY',  'lmguklfheqfag2lp9zskzbwcgzgi3xkt3buijqofbmoonlhol3hxgqzwtwp8732o' );
define( 'LOGGED_IN_KEY',    'k3qypqzbqikswpdhvsit5d1dxs6wckvqbzwnjr0kra3t2hlmir8ytcxwzkfpgi8o' );
define( 'NONCE_KEY',        'dvxkabrpphzaw7fcpm7mvimcbm41qvbd9agzmn7oaakvdxqpjsr3q1k65d58wogn' );
define( 'AUTH_SALT',        'qjv8pwerbpd4wjdlk2pyhwa9f1vkhcbbw29xdgfwhjdoot8awdql83f5m5x5pysa' );
define( 'SECURE_AUTH_SALT', 'iw28sakijlaspkabyfbutbzkqrwvjesyaq61td895jm5gnycyoxxclgn9vcnzedr' );
define( 'LOGGED_IN_SALT',   'kutlmubs4anqkfj6rlfmijagz5tzqfjw3go1zour7veojfokczpeu19lxnyyjrop' );
define( 'NONCE_SALT',       'l7xvnrtauzgah80segaowlryfek0kicetvpkhlmkayep4xcbqxkuvsykpubgmhbd' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp11_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', true );
define( 'SCRIPT_DEBUG', true );
/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
