<?php

/**
 * Get chiramise splitter
 *
 * @param null|int|WP_Post $post
 *
 * @return mixed|void
 */
function chiramise_get_splitters( $post = null ) {
	$post      = get_post( $post );
	$splitters = [
		'<!--more-->',
		'<!--nextpage-->',
		'[Chiramise]',
	];
	/**
	 * Filter for contents splitters.
	 *
	 * @filter chiramise_splitters
	 * @param array   $splitters Default is more tag, nextpage tag, Chiramise short code.
	 * @param WP_Post $post      Post to split
	 * @return array
	 */
	return apply_filters( 'chiramise_splitters', $splitters, $post );
}

/**
 * Get segment.
 *
 * @param null|int|WP_Post $post
 *
 * @return string
 */
function chiramise_split( $post = null ) {
	$segments = chiramise_get_segments( $post );
	return $segments[0];
}

/**
 * Get chunked segments.
 *
 * @param null|int|WP_Post $post
 * @return string[]
 */
function chiramise_get_segments( $post = null ) {
	$post      = get_post( $post );
	$segments  = [ $post->post_content ];
	$splitters = chiramise_get_splitters( $post );
	foreach ( $splitters as $splitter ) {
		$parsed = [];
		foreach ( $segments as $segment ) {
			foreach ( explode( $splitter, $segment ) as $chunk ) {
				$parsed[] = $chunk;
			}
		}
		$segments = $parsed;
	}
	return $segments;
}

/**
 * Get content filter function
 *
 * @param null|int|WP_Post $post
 *
 * @return callable
 */
function chiramise_filter_content_function( $post = null ) {
	$post = get_post( $post );
	/**
	 * Content filter function for Chiramise
	 *
	 * @filter chiramise_default_content_filter
	 * @see chiramise_filter_content()
	 * @param callable $function Default 'chiramise_filter_content'
	 * @param WP_Post  $post
	 * @return callable
	 */
	return apply_filters( 'chiramise_default_content_filter', 'chiramise_filter_content', $post );
}

/**
 * Filter the_content
 *
 * @param string $content
 *
 * @return string
 */
function chiramise_filter_content( $content ) {
	$post = get_post();
	if ( ! chiramise_should_check( $post ) ) {
		return $content;
	}
	if ( chiramise_can_read( $post ) ) {
		return $content;
	}
	return chiramise_split( $post );
}

/**
 * Return readable content length with percentile.
 *
 * @param null|int|WP_Post $post
 *
 * @return int
 */
function chiramise_content_ratio( $post = null ) {
	$post    = get_post( $post );
	$total   = strlen( $post->post_content );
	$segment = strlen( chiramise_split( $post ) );
	return (int) round( 100 - 100 * ( $segment / $total ) );
}

/**
 * Get content list
 *
 * @param null|int|WP_Post $post
 *
 * @return array
 */
function chiramise_get_toc( $post = null ) {
	$post = get_post( $post );
	if ( ! chiramise_should_check( $post ) || chiramise_can_read( $post ) ) {
		$split_text = $post->post_content;
	} else {
		$split_text = chiramise_split( $post );
	}
	$list = [];
	if ( preg_match_all( '#<h([1-6])([^>]?)>(.*?)</h[1-6]>#u', $post->post_content, $matches ) ) {
		for ( $i = 0, $l = count( $matches[0] ); $i < $l; $i++ ) {
			$list[] = [
				'label'    => $matches[3][ $i ],
				'depth'    => $matches[1][ $i ],
				'public'   => ( false !== strpos( $split_text, $matches[0][ $i ] ) ),
				'original' => $matches[0][ $i ],
			];
		}
	}

	/**
	 * A list of content block.
	 *
	 * @filter chiramise_content_list
	 * @param array   $list An array of content list
	 * @param WP_Post $post Post object
	 * @param string  $split_text Text displayed to unauthenticated user.
	 * @return string
	 */
	return apply_filters( 'chiramise_toc', $list, $post, $split_text );
}

/**
 * Show table of contents
 *
 * @param string $target
 * @param null|int|WP_Post $post
 */
function chiramise_the_toc( $target = '.entry-content', $post = null ) {
	$list = chiramise_get_toc( $post );
	if ( ! $list ) {
		return;
	}
	$out = [
		sprintf( '<ul class="chiramise-toc" data-target="%s">', esc_attr( $target ) ),
	];
	foreach ( $list as $item ) {
		if ( $item['public'] ) {
			$label = sprintf(
				'<a class="chiramise-toc-link" href="#">%s</a>',
				esc_html( $item['label'] )
			);
		} else {
			$label = sprintf( '<span class="chiramise-toc-invisible">%s</span>', esc_html( $item['label'] ) );
		}
		$html = sprintf(
			'<li class="chiramise-toc-item" data-depth="%d" data-visible="%s">%s</li>',
			intval( $item['depth'] ),
			( $item['public'] ? 'true' : false ),
			$label
		);
		/**
		 * Filter for each toc item
		 *
		 * @filter chiramise_toc_item
		 * @param string  $html
		 * @param array   $item
		 * @param WP_Post $post
		 * @return string
		 */
		$out[] = apply_filters( 'chiramise_toc_item', $html, $item, $post );
	}
	$out[] = '</ul>';
	$out   = implode( "\n", $out );
	wp_enqueue_script( 'chiramise-toc' );
	/**
	 * Filter the output of toc
	 *
	 * @filter chiramise_the_toc
	 * @param string $out
	 * @param array $list
	 * @param WP_Post $post
	 * @return string
	 */
	echo apply_filters( 'chiramise_the_toc', $out, $list, $post );
}
