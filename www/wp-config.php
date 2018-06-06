<?php
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
define('DB_NAME', 'services');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'sorofing');

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
define('AUTH_KEY',         'U2uIU<{Y8Xw=Ve3L6X@{A5z k<8&Orn6(=8A(kWybU}Jiay!?~n3hW6#3<$H:x0%');
define('SECURE_AUTH_KEY',  'tka@-s^MupsQSUT0A|m} ?d%r*a]I0 qH#X@1Q&R>mvG7HJ)6s(xhp).aW$t3}[,');
define('LOGGED_IN_KEY',    '/Am&Xw|u_y=_DcTq>p{Mo#u wySJ8wK0C8t-6W&uO!#d{Gi]|c8<*NgO@W4p}3*8');
define('NONCE_KEY',        'vnWN-ZBHaU_]I,pEz/Ei#`:RC7mLMnLI/n5Y)/^<^tN_{Uq*4M$jsFe+G1Ud$lU<');
define('AUTH_SALT',        '4@R)}Q8nBc{8`($~ik9/u2=%nIJ9KE|G-46biM%5#8]zfR!0^i>h^7d824oPl8B ');
define('SECURE_AUTH_SALT', '?1[zJz8&%B8*3,_d{4mSb~,STgokkuMj{dMs [|l613i)#1Etg-.3M 7x+3zSQeh');
define('LOGGED_IN_SALT',   '{OLlcL-%Jes$|=u8`ef>sNpb7&luv~z|*$JKpj+Xq-GB/?1(9]|]1 mv%ZV5y.,F');
define('NONCE_SALT',       'QQF=(ju9J)L?5xh!:`IE45K#&aRZX]]80M0.wv$K5Pa~JOfU=bz:[^gF=k>`A[2c');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'serv_';

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
