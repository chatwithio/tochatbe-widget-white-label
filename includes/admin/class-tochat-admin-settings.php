<?php
/**
 * Settings.
 *
 * @package TOCHAT\Classes\Admin
 * @version 1.0.0
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * TOCHAT_Admin_Settings class.
 *
 * @since 1.0.0
 */
class TOCHAT_Admin_Settings {

	/**
	 * Class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Register settings.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_settings() {
		register_setting( 'tochat_settings', 'tochat_key', 'sanitize_text_field' );
		register_setting(
			'tochat_settings',
			'tochat_exclude_ids',
			function ( $input ) {
				if ( null === $input ) {
					return array();
				}

				return array_map( 'absint', $input );
			}
		);
		register_setting(
			'tochat_settings',
			'tochat_add_widget_post_ids',
			function ( $input ) {
				$widget_key = array_map( 'sanitize_text_field', (array) $input['widget_key'] );
				$post_id    = array_map( 'absint', (array) $input['post_id'] );

				return array(
					'widget_key' => $widget_key,
					'post_id'    => $post_id,
				);
			}
		);
		register_setting(
			'tochat_settings',
			'tochat_add_widget_urls',
			function ( $input ) {
				$widget_key = array_map( 'sanitize_text_field', (array) $input['widget_key'] );
				$url        = array_map( 'esc_url_raw', (array) $input['url'] );

				return array(
					'widget_key' => $widget_key,
					'url'        => $url,
				);
			}
		);
		register_setting( 'tochat_settings', 'tochat_backend_key', 'sanitize_text_field' );

		// Register Email Setting.
		register_setting(
			'tochat_analytics_settings',
			'tochat_email',
			array(
				'sanitize_callback' => function ( $input ) {
					$email    = isset( $_POST['tochat_email'] ) ? sanitize_email( wp_unslash( $_POST['tochat_email'] ) ) : ''; // phpcs:ignore
					$password = isset( $_POST['tochat_password'] ) ? sanitize_text_field( wp_unslash( $_POST['tochat_password'] ) ) : ''; // phpcs:ignore
					$token    = tochat_api_generate_token( $email, $password );

					if ( is_wp_error( $token ) ) {
						// Add an error message to show at the top of the page.
						add_settings_error(
							'tochat_email',
							'invalid_credentials',
							$token->get_error_message(),
							'error'
						);

						// Store the error state in a global so the next function can see it.
						$GLOBALS['tochat_auth_failed'] = true;

						return '';
					}

					// If we reach here, authentication was successful. Store the token.
					tochat_api_set_token( $token );

					return sanitize_email( $input );
				},
			)
		);

		// Register Password Setting.
		register_setting(
			'tochat_analytics_settings',
			'tochat_password',
			array(
				'sanitize_callback' => function ( $input ) {
					if ( isset( $GLOBALS['tochat_auth_failed'] ) && true === $GLOBALS['tochat_auth_failed'] ) {
						return '';
					}

					return sanitize_text_field( $input );
				},
			)
		);
	}
}

return new TOCHAT_Admin_Settings();
