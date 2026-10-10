<?php
/**
Plugin Name: Chiramise
Plugin URI: https://gianism.info/add-on/chiramise/
Description: A WordPress plugin which makes your contents "members only".
Author: Hametuha INC.
Version: 1.1.2
Requires PHP: 7.4
Author URI: https://gianism.info/
Text Domain: chiramise
*/

$info = get_file_data(
	__FILE__,
	[
		'version'     => 'Version',
		'php_version' => 'PHP Version',
	]
);

define( 'CHIRAMISE_VERSION', $info['version'] );

load_plugin_textdomain( 'chiramise', false, 'chiramise/languages' );

try {
	if ( ! version_compare( phpversion(), $info['php_version'], '>=' ) ) {
		// translators: %1$s PHP version, %2$s Required PHP version.
		$message = sprintf( __( '[Chiramise] PHP <code>%1$s</code> is required, but your version is <code>%2$s</code>. So this plugin is still in silence. Please contact server administrator.', 'chiramise' ), phpversion(), $info['php_version'] );
		throw new Exception( $message );
	}
	// Load functions
	foreach ( array( 'functions', 'hooks' ) as $dir_name ) {
		$dir = __DIR__ . '/' . $dir_name . '/';
		foreach ( scandir( $dir ) as $file ) {
			if ( preg_match( '#^[^.](.*)\.php$#u', $file ) ) {
				require $dir . $file;
			}
		}
	}
} catch ( Exception $e ) {
	add_action(
		'admin_notices',
		function () use ( $e ) {
			printf( '<div class="error"><p>%s</p></div>', wp_kses( $e->getMessage(), [ 'code' => [] ] ) );
		}
	);
}
