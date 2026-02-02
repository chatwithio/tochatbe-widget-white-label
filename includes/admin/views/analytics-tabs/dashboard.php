<?php
/**
 * Analytics Dashboard
 *
 * @package TOCHAT\Admin
 * @version 1.3.0
 * @since 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Fetch stats from API using the stored widget key.
$widget_stats = tochat_api_get_widget_stats( get_option( 'tochat_key' ) );

if ( ! is_wp_error( $widget_stats ) ) : ?>

	<?php
	/**
	 * Internal Website Clicks Section.
	 */
	if ( ! empty( $widget_stats['clicksAllWebsite'] ) ) :
		?>

		<div class="tochat-analytics">

			<h3 class="tochat-analytics__heading"><?php esc_html_e( 'Clicks on your Widget from your Website', 'tochat' ); ?></h3>

			<ul class="tochat-analytics__list">

				<li class="tochat-analytics__item">
					<span class="tochat-analytics__count"><?php echo absint( $widget_stats['clicksAllWebsite'] ); ?></span>
					<span class="tochat-analytics__label"><?php esc_html_e( 'Total clicks', 'tochat' ); ?></span>
				</li>

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

	<?php endif; ?>

	<?php
	/**
	 * External Source Clicks Section (Google, Instagram, etc).
	 */
	if ( ! empty( $widget_stats['clicksAllGoogle'] ) ) :
		?>

		<div class="tochat-analytics">

			<h3 class="tochat-analytics__heading"><?php esc_html_e( 'Click on your Widget from outside your Website (other sources like Google, Instagram, etc...)', 'tochat' ); ?></h3>

			<ul class="tochat-analytics__list">

				<li class="tochat-analytics__item">
					<span class="tochat-analytics__count"><?php echo absint( $widget_stats['clicksAllGoogle'] ); ?></span>
					<span class="tochat-analytics__label"><?php esc_html_e( 'Total clicks', 'tochat' ); ?></span>
				</li>

				<li class="tochat-analytics__item down">
					<span class="tochat-analytics__count"><?php echo absint( $widget_stats['clicksLastDayGoogle'] ); ?></span>
					<span class="tochat-analytics__label"><?php esc_html_e( 'Last 24 hours clicks', 'tochat' ); ?></span>
				</li>

				<li class="tochat-analytics__item down">
					<span class="tochat-analytics__count"><?php echo absint( $widget_stats['clicksLastWeekGoogle'] ); ?></span>
					<span class="tochat-analytics__label"><?php esc_html_e( 'Last week clicks', 'tochat' ); ?></span>
				</li>

				<li class="tochat-analytics__item up">
					<span class="tochat-analytics__count"><?php echo absint( $widget_stats['clicksLastMonthGoogle'] ); ?></span>
					<span class="tochat-analytics__label"><?php esc_html_e( 'Last 28 days clicks', 'tochat' ); ?></span>
				</li>

				<li class="tochat-analytics__item up">
					<span class="tochat-analytics__count"><?php echo absint( $widget_stats['clicksLastYearGoogle'] ); ?></span>
					<span class="tochat-analytics__label"><?php esc_html_e( 'Last year clicks', 'tochat' ); ?></span>
				</li>

			</ul>

		</div>

	<?php endif; ?>

<?php else : ?>

	<div class="notice notice-error">
		<p><?php echo esc_html( $widget_stats->get_error_message() ); ?></p>
	</div>

<?php endif; ?>
