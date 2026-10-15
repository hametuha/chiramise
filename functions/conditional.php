<?php
/**
 * Conditional functions.
 *
 * @package chiramise
 */

/**
 * Normalize post type list to a unique array of non-empty strings.
 *
 * @param mixed $value Raw option value.
 * @return string[]
 */
function chiramise_normalize_post_types( $value ) {
	if ( is_null( $value ) || '' === $value ) {
		return [];
	}
	$values = array_filter( (array) $value, 'is_scalar' );
	$values = array_map( 'strval', $values );
	$values = array_filter(
		$values,
		function ( $v ) {
			return '' !== $v;
		}
	);
	return array_values( array_unique( $values ) );
}

/**
 * Get normalized supported post types.
 *
 * @return string[]
 */
function chiramise_get_supported_post_types() {
	return chiramise_normalize_post_types( get_option( 'chiramise_support_post_type', [] ) );
}

/**
 * Detect if Chiramise supports this post type.
 *
 * @param string $post_type
 *
 * @return bool
 */
function chiramise_supported( $post_type ) {
	return in_array( (string) $post_type, chiramise_get_supported_post_types(), true );
}

/**
 * Detect if current user can read post.
 *
 * @param null|int|WP_Post $post     Current post if not set.
 * @param null|int         $user_id  Current user if null.
 * @return bool
 */
function chiramise_can_read( $post = null, $user_id = null ) {
	$post = get_post( $post );
	if ( is_null( $user_id ) ) {
		$user_id = get_current_user_id();
	}
	if ( is_singular() && chiramise_always_filter() ) {
		return false;
	}
	if ( ! chiramise_supported( $post->post_type ) ) {
		return true;
	}
	$user = get_userdata( $user_id );
	if ( ! $user ) {
		return false;
	}
	return user_can( $user, chiramise_capability( $post ), $post->ID );
}

/**
 * Check if post should be check capability
 *
 * @param null|int|WP_Post $post
 *
 * @return bool
 */
function chiramise_should_check( $post = null ) {
	$post = get_post( $post );
	if ( ! chiramise_supported( $post->post_type ) ) {
		return false;
	}
	foreach ( chiramise_get_splitters( $post ) as $splitter ) {
		if ( false !== strpos( $post->post_content, $splitter ) ) {
			return true;
		}
	}
	return false;
}


/**
 * Detect if contents should be always filtered.
 *
 * @since 1.1.0
 * @return bool
 */
function chiramise_always_filter() {
	$should_always_filter = defined( 'CHIRAMISE_ALWAYS_FILTER' ) && CHIRAMISE_ALWAYS_FILTER;
	/**
	 * Detect if always filter if user is logged-in or not.
	 *
	 * @param bool $should_always_filter
	 * @return bool
	 */
	return apply_filters( 'chiramise_always_filter', $should_always_filter );
}
