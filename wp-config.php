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
define( 'DB_NAME', 'skyhighzest_db' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

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
define( 'AUTH_KEY',         'QVpMpj:B]^MLL:a~5CvJBz_y^qF/su4O=wn4#6B7`A;maD^-g85ns;`mMyaBgU9/' );
define( 'SECURE_AUTH_KEY',  'Q;iS~T;ROq}O^nP_9L}ILA[bjBV@v12|]2<c-f2o9:Ot8iC #z2G$yP;K*`7)6co' );
define( 'LOGGED_IN_KEY',    'zaAdJ[C8u6I<6l@uljtq$>VTCx?Ebs|snh,n(L&$c$dN$R]qu{b4]s{qX`nQplUS' );
define( 'NONCE_KEY',        'Oc^q^mj7~f;`e~d@f% UaOg}?v)mE$?!c7{2iv |VYn&[m=*?gE&7V[C3mDO(*&Z' );
define( 'AUTH_SALT',        'P L^)#qL2m<p8&lFQ~EkFN1^4V^(H;B9@BQ#;Q6b~}n}$Yg+C7&T43J7}1>NyA]J' );
define( 'SECURE_AUTH_SALT', '`{Z_U&f}sz=j!(*a~&^9lc9eK?|`vo1^-ViW<Eb@n/T[&j2GHbhrGOvr?k/Xd~Sy' );
define( 'LOGGED_IN_SALT',   '|gGCpk58A} ,71iV[3e*+H^GQ9jz[|&X~4#!u6,8X~MCe}# 6FZG72ER1SC&{V!M' );
define( 'NONCE_SALT',       '}fw0o=ho#}?Y!OVNTY&z)Gl_|F=xT&4`?]=ut2ac:Y?3h/[+mDXzzbX&Li($J+U%' );

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
//


/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
