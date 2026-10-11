<?php
/**
 * Add REST API
 *
 * @package chiramise
 */


/**
 * Register rest route
 */
add_action(
	'rest_api_init',
	function () {
		// Add POST API.
		register_rest_route(
			'chiramise/v1',
			'content/(?P<post_id>\d+)/?',
			[
				[
					'methods'             => 'POST',
					'args'                => [
						'post_id' => [
							'required'          => true,
							'type'              => 'integer',
							'description'       => __( 'Post ID to retrieve the restricted content', 'chiramise' ),
							'validate_callback' => function ( $value ) {
								if ( ! is_numeric( $value ) ) {
									return false;
								}
								$post = get_post( $value );
								return $post && chiramise_supported( $post->post_type );
							},
						],
					],
					'permission_callback' => function ( WP_REST_Request $request ) {
						return chiramise_can_read( $request->get_param( 'post_id' ) );
					},
					'callback'            => function ( WP_REST_Request $request ) {
						try {
							$post = get_post( $request->get_param( 'post_id' ) );
							if ( ! chiramise_can_read( $post, get_current_user_id() ) ) {
								throw new \Exception( __( 'You have no permission to read the entire article.', 'chiramise' ), 401 );
							}
							// Remove filter.
							remove_filter( 'the_content', chiramise_filter_content_function( $post ), 1 );
							setup_postdata( $post );
							$segments = chiramise_get_segments( $post );
							array_shift( $segments );
							if ( ! count( $segments ) ) {
								throw new \Exception( __( 'This content is not restricted.', 'chiramise' ), 404 );
							}
							$content = apply_filters( 'the_content', implode( "\n", $segments ) );
							/**
							 * chiramise_restricted_contents_in_rest
							 *
							 * Contents retrieved via REST API.
							 *
							 * @param string  $content
							 * @param WP_Post $post
							 * @param int     $user_id
							 * @return string
							 */
							$content = apply_filters( 'chiramise_restricted_contents_in_rest', $content, $post, get_current_user_id() );
							return new WP_REST_Response(
								[
									'success' => true,
									'content' => $content,
								]
							);
						} catch ( \Exception $e ) {
							$status = $e->getCode();
							if ( ! preg_match( '/^\d{3}$/u', $status ) ) {
								$status = 500;
							}
							return new WP_Error(
								'chiramise_failed_content',
								$e->getMessage(),
								[
									'status' => $status,
								]
							);
						}
					},
				],
			]
		);
	}
);
