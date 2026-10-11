<?php

/**
 * Add setting field to admin screen
 */
add_action(
	'admin_init',
	function () {

		// Add setting section on general.
		add_settings_section(
			'chiramise_support_section',
			__( 'Setting for Chiramise', 'chiramise' ),
			function () {
				printf(
					'<p class="description">%s</p>',
					sprintf(
						// translators: %s is document link.
						__( 'Setting value for Chiramise. You can customize these setting with hooks. Please check our site <a href="%s">gianism.info</a>.', 'chiramise' ),
						'https://ginaism.info/add-on/chiramise/'
					)
				);
			},
			'general'
		);

		// Supported post type
		add_settings_field(
			'chiramise_support_post_type',
			__( 'Post types allowed for Chiramise', 'chiramise' ),
			function () {
				$post_types = get_post_types(
					[
						'public' => true,
					]
				);
				/**
				 * Filter post type to select
				 *
				 * @filter chiramise_post_type_available
				 * @param array $post_types
				 * @return array
				 */
				$post_types = apply_filters( 'chiramise_post_type_available', $post_types );
				if ( ! $post_types ) {
					printf( '<p style="color: red;">%s</p>', __( 'No post type available!' ) );
					return;
				}
				$option = (array) get_option( 'chiramise_support_post_type', [] );
				foreach ( $post_types as $post_type ) {
					?>
				<label style="margin-right: 1em; display: inline-block">
					<input type="checkbox" name="chiramise_support_post_type[]"
								value="<?php echo esc_attr( $post_type ); ?>" <?php checked( false !== array_search( $post_type, $option ) ); // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict -- option の型が保証されず strict 化で判定が変わり得るため。#8 で対応。 ?> />
						<?php echo esc_html( get_post_type_object( $post_type )->label ); ?>
				</label>
					<?php
				}
			},
			'general',
			'chiramise_support_section'
		);

		// Register setting field
		register_setting( 'general', 'chiramise_support_post_type' );
	}
);

