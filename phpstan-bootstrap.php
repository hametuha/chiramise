<?php
/**
 * PHPStan bootstrap file.
 *
 * @package chiramise
 */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/wp/' );
}

if ( ! defined( 'WPINC' ) ) {
	define( 'WPINC', 'wp-includes' );
}

if ( ! defined( 'CHIRAMISE_VERSION' ) ) {
	define( 'CHIRAMISE_VERSION', 'phpstan' );
}
