<?php
/**
 * Admin Actions Handler.
 *
 * @package TOCHAT\Admin
 * @version 1.3.0
 * @since 1.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TOCHAT_Admin_Actions
 *
 * Handles admin-specific actions like API disconnection.
 *
 * @since 1.3.0
 */
class TOCHAT_Admin_Actions {

	/**
	 * Class constructor.
	 *
	 * Sets up hooks for admin actions based on URL parameters.
	 *
	 * @since 1.3.0
	 */
	public function __construct() {
		// Tochat actions handler.
		if ( isset( $_GET['tochat_action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			add_action( 'admin_init', array( $this, 'handle_disconnect' ) );
		}
	}

	/**
	 * Handle the disconnect action.
	 *
	 * Verifies security nonces, clears local credentials/tokens, and redirects the user.
	 *
	 * @since 1.3.0
	 *
	 * @return void
	 */
	public function handle_disconnect() {
		// Verify nonce for security.
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'tochat_api_disconnect_nonce' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'tochat' ) );
		}

		// Clear stored credentials.
		delete_option( 'tochat_email' );
		delete_option( 'tochat_password' );

		// Clear the API token.
		if ( function_exists( 'tochat_api_delete_token' ) ) {
			tochat_api_delete_token();
		}

		// Redirect back to the analytics page with a success message.
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'         => 'tochat-analytics',
					'disconnected' => '1',
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}
}

/**
 * Initialize the class.
 */
return new TOCHAT_Admin_Actions();
