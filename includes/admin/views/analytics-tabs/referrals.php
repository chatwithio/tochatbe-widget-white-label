<?php
/**
 * Analytics Referrals
 *
 * @package TOCHAT\Admin
 * @version 1.4.0
 * @since 1.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$widget_referrals = tochat_api_get_widget_referrals( get_option( 'tochat_key' ) );

if ( ! is_wp_error( $widget_referrals ) ) : ?>
	<div style="height: 20px;"></div>
	<table class="wp-list-table widefat fixed striped table-view-list">
		<thead>
			<tr>
				<th scope="col" class="manage-column"><?php esc_html_e( 'Referrals', 'tochat' ); ?></th>
				<th scope="col" class="manage-column"><?php esc_html_e( 'Count', 'tochat' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $widget_referrals as $referral ) : ?>
				<tr>
					<td>
						<?php
						if ( 'unknown' === $referral['name'] ) {
							esc_html_e( 'No identifiable referrer*', 'tochat' );
						} else {
							echo wp_sprintf(
								/* translators: 1: Referral URL. 2: Referral Name. */
								'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
								esc_url( 'http://' . $referral['name'] ),
								esc_html( $referral['name'] )
							);
						}
						?>
					</td>
					<td><?php echo absint( $referral['total'] ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<p><?php esc_html_e( '* The sending server did not set a referrer header.', 'tochat' ); ?></p>


<?php else : ?>

	<div class="notice notice-error">
		<p><?php echo esc_html( $widget_referrals->get_error_message() ); ?></p>
	</div>

<?php endif; ?>
