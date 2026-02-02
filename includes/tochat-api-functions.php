<?php
/**
 * TOCHAT API functions.
 *
 * @package TOCHAT\Functions
 * @version 1.3.0
 * @since 1.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Authenticates with the ToChat API and retrieves a Bearer token.
 *
 * @since 1.3.0
 *
 * @param string $email    The ToChat account email.
 * @param string $password The ToChat account password.
 * @return string|WP_Error The API token on success, WP_Error on failure.
 */
function tochat_api_generate_token( $email, $password ) {
	$url  = 'https://services.tochat.be/api/authentication_token';
	$args = array(
		'headers'   => array(
			'Content-Type' => 'application/json',
		),
		'body'      => wp_json_encode(
			array(
				'email'    => $email,
				'password' => $password,
			)
		),
		'timeout'   => 20,
		'sslverify' => true,
	);

	// Perform the remote POST request.
	$response = wp_remote_post( $url, $args );

	// 1. Handle WP_Error (Network-level failures).
	if ( is_wp_error( $response ) ) {
		return new WP_Error(
			'tochat_api_connection_error',
			__( 'Failed to connect to TOCHAT API.', 'tochat' ),
			$response->get_error_message()
		);
	}

	// 2. Validate the HTTP response code.
	$response_code = wp_remote_retrieve_response_code( $response );

	if ( 401 === $response_code ) {
		return new WP_Error(
			'tochat_api_authentication_failed',
			__( 'Invalid credentials.', 'tochat' ),
			$response_code
		);
	}

	if ( 200 !== $response_code ) {
		return new WP_Error(
			'tochat_api_unexpected_response',
			__( 'Unexpected response from TOCHAT API.', 'tochat' ),
			$response_code
		);
	}

	// 3. Retrieve and decode the body.
	$body = wp_remote_retrieve_body( $response );
	$data = json_decode( $body, true );

	// 4. Verify token exists in the response.
	if ( ! is_array( $data ) || empty( $data['token'] ) ) {
		return new WP_Error(
			'tochat_api_invalid_data',
			__( 'Invalid response format from TOCHAT API.', 'tochat' )
		);
	}

	return sanitize_text_field( $data['token'] );
}

/**
 * Get the stored ToChat API token.
 *
 * @since 1.3.0
 *
 * @return string|false The stored API token, or false if not found.
 */
function tochat_api_get_token() {
	$token           = get_transient( 'tochat_api_token' );
	$tochat_email    = get_option( 'tochat_email' );
	$tochat_password = get_option( 'tochat_password' );

	if ( ! $token && $tochat_email && $tochat_password ) {
		// Attempt to generate a new token using stored credentials.
		$token = tochat_api_generate_token( $tochat_email, $tochat_password );

		if ( is_wp_error( $token ) ) {
			return false;
		}

		// Store the new token.
		tochat_api_set_token( $token );
	}

	return get_transient( 'tochat_api_token' );
}

/**
 * Set the ToChat API token.
 *
 * @since 1.3.0
 *
 * @param string $token The API token to store.
 * @return void
 */
function tochat_api_set_token( $token ) {
	set_transient( 'tochat_api_token', $token, MINUTE_IN_SECONDS * 10 );
}

/**
 * Deletes the stored ToChat API token.
 *
 * @since 1.3.0
 *
 * @return void
 */
function tochat_api_delete_token() {
	delete_transient( 'tochat_api_token' );
}

/**
 * Retrieves widget statistics from the ToChat API.
 *
 * @since 1.3.0
 *
 * @param string $widget_id The string-based ID of the widget.
 * @return array|WP_Error The statistics array on success, WP_Error on failure.
 */
function tochat_api_get_widget_stats( $widget_id ) {
	$token = tochat_api_get_token();

	// Return the error if token retrieval failed.
	if ( ! $token ) {
		return new WP_Error(
			'tochat_api_no_token',
			__( 'No API token found. Please authenticate first.', 'tochat' )
		);
	}

	// Sanitize the string ID and encode it for use in a URL.
	$clean_id = rawurlencode( sanitize_text_field( $widget_id ) );
	$url      = 'https://services.tochat.be/api/v2/widget_stats/' . $clean_id;

	$args = array(
		'headers' => array(
			'Content-Type'  => 'application/json',
			'Authorization' => 'Bearer ' . $token,
		),
		'timeout' => 15,
	);

	// Perform the remote GET request.
	$response = wp_remote_get( $url, $args );

	// 1. Handle WP_Error (Network-level failures).
	if ( is_wp_error( $response ) ) {
		return new WP_Error(
			'tochat_api_connection_error',
			__( 'Failed to connect to TOCHAT API.', 'tochat' ),
			$response->get_error_message()
		);
	}

	// 2. Validate the HTTP response code.
	$response_code = wp_remote_retrieve_response_code( $response );

	// Clear token if unauthorized so it refreshes on next try.
	if ( 401 === $response_code ) {
		// Clear stored credentials.
		delete_option( 'tochat_email' );
		delete_option( 'tochat_password' );

		// Clear the API token.
		tochat_api_delete_token();

		// Add log.
		error_log( 'TOCHAT API token unauthorized. Cleared stored credentials and token.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log

		return new WP_Error(
			'tochat_api_authentication_failed',
			__( 'Invalid or expired JWT Token.', 'tochat' ),
			$response_code
		);
	}

	if ( 404 === $response_code ) {
		return new WP_Error(
			'tochat_api_widget_not_found',
			__( 'Invalid or no widget found.', 'tochat' ),
			$response_code
		);
	}

	if ( 200 !== $response_code ) {
		return new WP_Error(
			'tochat_api_unexpected_response',
			__( 'Unexpected response from TOCHAT API.', 'tochat' ),
			$response_code
		);
	}

	// 3. Retrieve and decode the body.
	$body = wp_remote_retrieve_body( $response );
	$data = json_decode( $body, true );

	// 4. Verify data integrity.
	if ( ! is_array( $data ) ) {
		return new WP_Error(
			'tochat_api_invalid_data',
			__( 'Invalid response format from TOCHAT API.', 'tochat' )
		);
	}

	return $data;
}

/**
 * Retrieves widget referral data from the ToChat API.
 *
 * @since 1.4.0
 *
 * @param string $widget_id The string-based ID of the widget.
 * @return array|WP_Error The referrals array on success, WP_Error on failure.
 */
function tochat_api_get_widget_referrals( $widget_id ) {
	$token = tochat_api_get_token();

	// Return the error if token retrieval failed.
	if ( ! $token ) {
		return new WP_Error(
			'tochat_api_no_token',
			__( 'No API token found. Please authenticate first.', 'tochat' )
		);
	}

	// Sanitize the string ID and encode it for use in a URL.
	$url = add_query_arg(
		array(
			'order' => 'desc',
			'from'  => wp_date( 'Y-m-d', strtotime( '-1 year' ) ),
			'to'    => wp_date( 'Y-m-d', strtotime( '-1 day' ) ),
		),
		'https://services.tochat.be/api/v2/' . rawurlencode( sanitize_text_field( $widget_id ) ) . '/referer-graph'
	);

	$args = array(
		'headers' => array(
			'Content-Type'  => 'application/json',
			'Authorization' => 'Bearer ' . $token,
		),
		'timeout' => 15,
	);

	// Perform the remote GET request.
	$response = wp_remote_get( $url, $args );

	// 1. Handle WP_Error (Network-level failures).
	if ( is_wp_error( $response ) ) {
		return new WP_Error(
			'tochat_api_connection_error',
			__( 'Failed to connect to TOCHAT API.', 'tochat' ),
			$response->get_error_message()
		);
	}

	// 2. Validate the HTTP response code.
	$response_code = wp_remote_retrieve_response_code( $response );

	// Clear token if unauthorized so it refreshes on next try.
	if ( 401 === $response_code ) {
		// Clear stored credentials.
		delete_option( 'tochat_email' );
		delete_option( 'tochat_password' );

		// Clear the API token.
		tochat_api_delete_token();

		// Add log.
		error_log( 'TOCHAT API token unauthorized. Cleared stored credentials and token.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log

		return new WP_Error(
			'tochat_api_authentication_failed',
			__( 'Invalid or expired JWT Token.', 'tochat' ),
			$response_code
		);
	}

	if ( 404 === $response_code ) {
		return new WP_Error(
			'tochat_api_widget_not_found',
			__( 'Invalid or no widget found.', 'tochat' ),
			$response_code
		);
	}

	if ( 200 !== $response_code ) {
		return new WP_Error(
			'tochat_api_unexpected_response',
			__( 'Unexpected response from TOCHAT API.', 'tochat' ),
			$response_code
		);
	}

	// 3. Retrieve and decode the body.
	$body = wp_remote_retrieve_body( $response );
	$data = json_decode( $body, true );

	return $data;
}

/**
 * Get the URL for disconnecting from the ToChat API.
 *
 * @since 1.3.0
 *
 * @return string The disconnect URL with nonce.
 */
function tochat_get_api_disconnect_url() {
	$admin_url      = admin_url( 'admin.php?page=tochat-analytics' );
	$disconnect_url = add_query_arg(
		array(
			'tochat_action' => 'tochat_api_disconnect',
			'_wpnonce'      => wp_create_nonce( 'tochat_api_disconnect_nonce' ),
		),
		$admin_url
	);

	return $disconnect_url;
}
