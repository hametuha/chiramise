<?php

// Register script
add_action(
	'init',
	function () {
		$dir = plugin_dir_url( __DIR__ ) . 'assets';
		// TOC Helper
		wp_register_script( 'chiramise-toc', $dir . '/js/toc-helper.js', [ 'jquery' ], CHIRAMISE_VERSION, true );
	}
);
