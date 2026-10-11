<?php

/**
 * Register chiramise short code for compatibility.
 */
add_shortcode(
	'Chiramise',
	function () {
		return '';
	}
);

/**
 * Filter content
 */
add_filter( 'the_content', chiramise_filter_content_function(), 1 );
add_filter( 'the_content_rss', chiramise_filter_content_function(), 1 );
