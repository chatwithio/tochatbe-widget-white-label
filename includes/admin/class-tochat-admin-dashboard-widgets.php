<?php
/**
 * Admin Dashboard Widgets Handler.
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
 * Class TOCHAT_Admin_Dashboard_Widgets
 *
 * Handles the registration and rendering of the Tochat dashboard widget.
 *
 * @since 1.3.0
 */
class TOCHAT_Admin_Dashboard_Widgets {

	/**
	 * Class constructor.
	 *
	 * Sets up the hook for dashboard widget registration.
	 *
	 * @since 1.3.0
	 */
	public function __construct() {
		add_action( 'wp_dashboard_setup', array( $this, 'register_dashboard_widgets' ) );
	}

	/**
	 * Register the dashboard widget.
	 *
	 * Adds the "Tochat Click Analytics" widget to the WordPress dashboard.
	 *
	 * @since 1.3.0
	 *
	 * @return void
	 */
	public function register_dashboard_widgets() {
		wp_add_dashboard_widget(
			'tochat_click_analysis',
			wp_sprintf(
				/* translators: %s: Plugin Name */
				esc_html__( '%s Click Analytics', 'tochat' ),
				esc_html( TOCHAT_PLUGIN_NAME )
			),
			array( $this, 'click_analysis' )
		);
	}

	/**
	 * Render the dashboard widget content.
	 *
	 * Displays the click statistics or connection prompts.
	 *
	 * @since 1.3.0
	 *
	 * @return void
	 */
	public function click_analysis() {
		/**
		 * Case 1: API Token is missing.
		 */
		if ( ! tochat_api_get_token() ) {
			echo wp_kses_post(
				sprintf(
					/* translators: %s: URL to analytics page */
					__( 'Please connect your account on the <a href="%s">analytics page</a> to view click analytics.', 'tochat' ),
					esc_url( admin_url( 'admin.php?page=tochat-analytics' ) )
				)
			);

		} elseif ( ! get_option( 'tochat_key' ) ) {
			/**
			 * Case 2: Widget Key is missing.
			 */
			echo wp_kses_post(
				sprintf(
					/* translators: %s: URL to settings page */
					__( 'No Tochat widget key found. Please add a widget key in the <a href="%s">settings</a> to view analytics.', 'tochat' ),
					esc_url( admin_url( 'admin.php?page=tochat' ) )
				)
			);
		} else {
			/**
			 * Case 3: Key exists, fetch stats.
			 */
			$widget_stats = tochat_api_get_widget_stats( get_option( 'tochat_key' ) );

			if ( is_wp_error( $widget_stats ) ) {
				echo '<p>' . esc_html( $widget_stats->get_error_message() ) . '</p>';
			} else {
				?>
				<p><strong><?php esc_html_e( 'Clicks on your Widget from your Website', 'tochat' ); ?></strong></p>
				<div class="tochat-analytics" style="font-size: 0.8rem;">
					<ul class="tochat-analytics__list">

						<li class="tochat-analytics__item">
							<span class="tochat-analytics__count"><?php echo absint( $widget_stats['clicksLastDayWebsite'] ); ?></span>
							<span class="tochat-analytics__label"><?php esc_html_e( 'Last 24 hours clicks', 'tochat' ); ?></span>
						</li>

						<li class="tochat-analytics__item">
							<span class="tochat-analytics__count"><?php echo absint( $widget_stats['clicksLastWeekWebsite'] ); ?></span>
							<span class="tochat-analytics__label"><?php esc_html_e( 'Last week clicks', 'tochat' ); ?></span>
						</li>

						<li class="tochat-analytics__item">
							<span class="tochat-analytics__count"><?php echo absint( $widget_stats['clicksLastMonthWebsite'] ); ?></span>
							<span class="tochat-analytics__label"><?php esc_html_e( 'Last 28 days clicks', 'tochat' ); ?></span>
						</li>

						<li class="tochat-analytics__item">
							<span class="tochat-analytics__count"><?php echo absint( $widget_stats['clicksLastYearWebsite'] ); ?></span>
							<span class="tochat-analytics__label"><?php esc_html_e( 'Last year clicks', 'tochat' ); ?></span>
						</li>

					</ul>
				</div>
				<p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=tochat-analytics' ) ); ?>">
						<?php esc_html_e( 'View detailed analytics', 'tochat' ); ?>
					</a>
				</p>
				<?php
			}
		}
	}
}

/**
 * Initialize the class.
 */
return new TOCHAT_Admin_Dashboard_Widgets();
