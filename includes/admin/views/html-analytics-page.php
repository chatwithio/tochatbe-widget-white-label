<?php
/**
 * Admin View: Widget analytics page.
 *
 * @package TOCHAT\Admin
 * @version 1.3.0
 * @since 1.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'dashboard'; // phpcs:ignore

?>
<div class="wrap">

	<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
		<?php // translators: %s: Plugin name. ?>
		<h1 class="wp-heading-inline">
			<?php
			echo wp_sprintf(
				/* translators: %s: The plugin name */
				esc_html__( '%s Analytics', 'tochat' ),
				esc_html( TOCHAT_PLUGIN_NAME )
			);
			?>
		</h1>

		<?php if ( tochat_api_get_token() ) : ?>

			<p style="font-size: 14px;">
				<span><?php esc_html_e( 'Connected as', 'tochat' ); ?></span>
				<strong><?php echo esc_html( get_option( 'tochat_email' ) ); ?></strong> |
				<a href="<?php echo esc_url( tochat_get_api_disconnect_url() ); ?>"><?php esc_html_e( 'Disconnect', 'tochat' ); ?></a>
			</p>

		<?php endif; ?>
	</div>

	<?php
	// Display settings errors (notices).
	settings_errors();
	?>

	<hr class="wp-header-end">

	<?php
	/**
	 * Check if the user is connected to the API.
	 * If not, show the connection form.
	 */
	if ( ! tochat_api_get_token() ) :
		?>

		<form action="options.php" method="post">

			<?php settings_fields( 'tochat_analytics_settings' ); ?>

			<p>
				<?php
				echo wp_kses_post(
					sprintf(
						/* translators: 1: Tochat website link, 2: Plugin Name. */
						__( 'Enter your <a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a> credentials to connect and view analytics.', 'tochat' ),
						esc_url( TOCHAT_PLUGIN_LOGIN_URL ),
						esc_html( TOCHAT_PLUGIN_NAME )
					)
				);
				?>
			</p>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="tochat_email"><?php esc_html_e( 'Email', 'tochat' ); ?></label></th>
					<td>
						<input type="email" id="tochat_email" name="tochat_email" value="<?php echo esc_attr( get_option( 'tochat_email' ) ); ?>" class="regular-text" />
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="tochat_password"><?php esc_html_e( 'Password', 'tochat' ); ?></label></th>
					<td>
						<input type="password" id="tochat_password" name="tochat_password" value="<?php echo esc_attr( get_option( 'tochat_password' ) ); ?>" class="regular-text" />
					</td>
				</tr>
			</table>

			<?php submit_button( esc_html__( 'Connect', 'tochat' ) ); ?>

		</form>

	<?php else : ?>

		<?php
		/**
		 * If connected, check for the widget key.
		 */
		if ( ! get_option( 'tochat_key' ) ) :
			?>

			<div class="notice notice-warning">
				<p>
					<?php
					echo wp_kses_post(
						sprintf(
							/* translators: %s: URL to settings page. */
							__( 'No Tochat widget key found. Please add a widget key in the <a href="%s">settings</a> to view analytics.', 'tochat' ),
							esc_url( admin_url( 'admin.php?page=tochat' ) )
						)
					);
					?>
				</p>
			</div>

		<?php else : ?>

			<nav class="nav-tab-wrapper tochat-nav-tab-wrapper">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=tochat-analytics&tab=dashboard' ) ); ?>" class="nav-tab <?php echo 'dashboard' === $tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Dashboard', 'tochat' ); ?></a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=tochat-analytics&tab=referrals' ) ); ?>" class="nav-tab <?php echo 'referrals' === $tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Referrals', 'tochat' ); ?></a>
			</nav>

			<?php
			if ( 'referrals' === $tab ) {
				include_once TOCHAT_PLUGIN_PATH . 'includes/admin/views/analytics-tabs/referrals.php';
			} else {
				include_once TOCHAT_PLUGIN_PATH . 'includes/admin/views/analytics-tabs/dashboard.php';
			}
			?>

			<p style="margin-top: 20px; text-align: right;">
				<?php
				if ( defined( 'TOCHAT_PLUGIN_ANALYTICS_VIEW_MORE_STATS_URL' ) ) {
					echo wp_kses_post(
						sprintf(
							/* translators: %s: Account URL for statistics. */
							__( '<a href="%s" target="_blank" rel="noopener noreferrer">View more stats in your account</a>', 'tochat' ),
							esc_url( trailingslashit( TOCHAT_PLUGIN_ANALYTICS_VIEW_MORE_STATS_URL ) . get_option( 'tochat_key' ) )
						)
					);
				}
				?>
				<span> | </span>
				<?php
				if ( defined( 'TOCHAT_PLUGIN_ANALYTICS_VIEW_ALL_LEADS_URL' ) ) {
					echo wp_kses_post(
						sprintf(
							/* translators: %s: Account URL for leads. */
							__( '<a href="%s" target="_blank" rel="noopener noreferrer">View all your leads in your account</a>', 'tochat' ),
							esc_url( trailingslashit( TOCHAT_PLUGIN_ANALYTICS_VIEW_ALL_LEADS_URL ) . get_option( 'tochat_key' ) )
						)
					);
				}
				?>
			</p>

		<?php endif; ?>

	<?php endif; ?>

</div>
