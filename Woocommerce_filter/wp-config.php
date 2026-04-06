<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress' );

/** Database username */
define( 'DB_USER', 'example_user' );

/** Database password */
define( 'DB_PASSWORD', 'password' );

/** Database hostname */
define( 'DB_HOST', '127.0.0.1' );

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
define('AUTH_KEY',         '>le+cIT^_ci(Di;]s?R{PtWP4J={2@T Pvr^t-q/F+tL@g@[)%Q^j}ZUMP-Lunof');
define('SECURE_AUTH_KEY',  '1S;-+Ah<x+-[kb-Yr x<@R}/6{!}Pg|[Y8|#_ifl(f?pz=F^{nM*vfFI&1##CdC)');
define('LOGGED_IN_KEY',    'y8G<T GV~?``Swt)0<[[SYqfZ|7-ND|RGH,+Y$U]</o_DxUZo4q[DPel--Elm+D[');
define('NONCE_KEY',        'nE(Z&YnS|<eO}q8}5M+t-VpZfQ7na@j[i~Q[4~z)GpYKcM14WUCh-_6@gkrvGQ-Y');
define('AUTH_SALT',        '}o.kG%q4}@SMY)rEiSJ?NCvApt4=HL$Z5S)wBmx,@,3TnG4sbvJ(,G>;q!j:?6w6');
define('SECURE_AUTH_SALT', '%;>-ez}j>~?!D/sh^ZAIHtW73b9{-++S2o[ )b;8i0j0 |?De->Mmav$CQC+WSu6');
define('LOGGED_IN_SALT',   '(DH-l%E}G-L[lG(>J9_#f3|0pZy|u!*wL@olj13l(<3:xcA@U6~=bhx5|ujScazl');
define('NONCE_SALT',       '6Uh$>gyQPjBJ(,p!P#nk~Bd6@/>0(}-Dr!%pDz,~bEy,xRMnO40cqd{}}ok4F,sf');

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
