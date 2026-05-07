<?php
/**
 * CWC Accommodations — Newsletter Subscribers.
 *
 * Admin page to view and manage newsletter subscribers.
 *
 * @package CWC_Accommodations
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Subscribers submenu under Accommodations.
 */
function cwc_register_subscribers_menu() {
	add_submenu_page(
		'edit.php?post_type=accommodation',
		__( 'Subscribers', 'cwc-accommodations' ),
		__( 'Subscribers', 'cwc-accommodations' ),
		'manage_options',
		'cwc-newsletter-subscribers',
		'cwc_render_subscribers_page'
	);
}
add_action( 'admin_menu', 'cwc_register_subscribers_menu' );

/**
 * Render the subscribers list page.
 */
function cwc_render_subscribers_page() {
	// Handle deletion if requested
	if ( isset( $_GET['action'] ) && 'delete' === $_GET['action'] && isset( $_GET['email'] ) ) {
		check_admin_referer( 'cwc_delete_subscriber' );
		
		$email_to_delete = sanitize_email( $_GET['email'] );
		$subscribers     = get_option( 'cwc_newsletter_subscribers', array() );
		
		$subscribers = array_filter( $subscribers, function( $sub ) use ( $email_to_delete ) {
			return $sub['email'] !== $email_to_delete;
		} );
		
		update_option( 'cwc_newsletter_subscribers', array_values( $subscribers ) );
		
		echo '<div class="updated"><p>' . esc_html__( 'Subscriber deleted.', 'cwc-accommodations' ) . '</p></div>';
	}

	$subscribers = get_option( 'cwc_newsletter_subscribers', array() );
	?>
	<div class="wrap">
		<h1 class="wp-heading-inline"><?php esc_html_e( 'Newsletter Subscribers', 'cwc-accommodations' ); ?></h1>
		<hr class="wp-header-end">

		<div class="card" style="max-width: 100%; margin-top: 20px;">
			<p><?php esc_html_e( 'These are the users who have subscribed to your newsletter via the website footer.', 'cwc-accommodations' ); ?></p>
			
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Email', 'cwc-accommodations' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Subscription Date', 'cwc-accommodations' ); ?></th>
						<th scope="col"><?php esc_html_e( 'IP Address', 'cwc-accommodations' ); ?></th>
						<th scope="col" style="width: 100px;"><?php esc_html_e( 'Actions', 'cwc-accommodations' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( ! empty( $subscribers ) ) : ?>
						<?php foreach ( array_reverse( $subscribers ) as $sub ) : ?>
							<tr>
								<td><strong><?php echo esc_html( $sub['email'] ); ?></strong></td>
								<td><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $sub['subscribed'] ) ) ); ?></td>
								<td><?php echo esc_html( $sub['ip'] ?: '—' ); ?></td>
								<td>
									<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'delete', 'email' => $sub['email'] ) ), 'cwc_delete_subscriber' ) ); ?>" 
									   class="button button-link-delete" 
									   onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to delete this subscriber?', 'cwc-accommodations' ); ?>');">
										<?php esc_html_e( 'Delete', 'cwc-accommodations' ); ?>
									</a>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr>
							<td colspan="4"><?php esc_html_e( 'No subscribers found.', 'cwc-accommodations' ); ?></td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>

			<?php if ( ! empty( $subscribers ) ) : ?>
				<div style="margin-top: 20px;">
					<button class="button" onclick="cwcExportSubscribers()"><?php esc_html_e( 'Export to CSV', 'cwc-accommodations' ); ?></button>
				</div>
				<script>
				function cwcExportSubscribers() {
					const subscribers = <?php echo json_encode( $subscribers ); ?>;
					let csv = 'Email,Date,IP\n';
					subscribers.forEach(sub => {
						csv += `${sub.email},"${sub.subscribed}",${sub.ip || ''}\n`;
					});
					
					const blob = new Blob([csv], { type: 'text/csv' });
					const url = window.URL.createObjectURL(blob);
					const a = document.createElement('a');
					a.setAttribute('href', url);
					a.setAttribute('download', 'cwc-subscribers.csv');
					a.click();
				}
				</script>
			<?php endif; ?>
		</div>
	</div>
	<?php
}
