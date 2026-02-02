<?php
/**
 * TOCHAT setup
 *
 * @package TOCHAT\Classes
 * @version 1.0.0
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class TOCHAT
 *
 * Main plugin class.
 *
 * @since 1.0.0
 */
final class TOCHAT {

	/**
	 * Holds the class instance.
	 *
	 * @since 1.0.0
	 *
	 * @var TOCHAT $instance
	 */
	private static $instance = null;

	/**
	 * Return an instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return TOCHAT class instance.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * TOCHAT constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Include necessary files.
		$this->includes();

		// Load plugin text domain.
		load_plugin_textdomain( 'tochat', false, basename( __DIR__ ) . '/languages' );
	}

	/**
	 * Include necessary files.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function includes() {
		// Classes.

		// Functions.
		include_once TOCHAT_PLUGIN_PATH . 'includes/tochat-functions.php';
		include_once TOCHAT_PLUGIN_PATH . 'includes/tochat-api-functions.php';

		// Admin classes.
		if ( is_admin() ) {
			include_once TOCHAT_PLUGIN_PATH . 'includes/admin/class-tochat-admin-settings.php';
			include_once TOCHAT_PLUGIN_PATH . 'includes/admin/class-tochat-admin-scripts.php';
			include_once TOCHAT_PLUGIN_PATH . 'includes/admin/class-tochat-admin-menus.php';
			include_once TOCHAT_PLUGIN_PATH . 'includes/admin/class-tochat-admin-widget.php';
			include_once TOCHAT_PLUGIN_PATH . 'includes/admin/class-tochat-admin-actions.php';
			include_once TOCHAT_PLUGIN_PATH . 'includes/admin/class-tochat-admin-dashboard-widgets.php';
		} else {
			include_once TOCHAT_PLUGIN_PATH . 'includes/class-tochat-code.php';
		}
	}
}
