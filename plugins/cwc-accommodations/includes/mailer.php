<?php
/**
 * CWC Accommodations — Promo Mailer.
 *
 * Bulk email functionality for announcements and promos.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Mailer submenu under Accommodations.
 */
function cwc_register_mailer_menu() {
	add_submenu_page(
		'edit.php?post_type=accommodation',
		__( 'Promo Mailer', 'cwc-accommodations' ),
		__( 'Promo Mailer', 'cwc-accommodations' ),
		'manage_options',
		'cwc-promo-mailer',
		'cwc_render_mailer_page'
	);
}
add_action( 'admin_menu', 'cwc_register_mailer_menu' );

/**
 * Render the Mailer page.
 */
function cwc_render_mailer_page() {
	$all_recipients = cwc_mailer_get_all_recipients_data();
    $coupons        = get_posts(['post_type' => 'cwc_coupon', 'post_status' => 'publish', 'numberposts' => -1]);

	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Promo Mailer', 'cwc-accommodations' ); ?></h1>
		<p><?php esc_html_e( 'Send announcements, promos, and coupons to your subscribers and past guests.', 'cwc-accommodations' ); ?></p>

		<div class="cwc-mailer-container" style="max-width: 900px; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);">
			<form id="cwc-mailer-form">
				<div style="margin-bottom: 20px;">
					<label style="display: block; font-weight: 600; margin-bottom: 8px;">Subject</label>
					<input type="text" id="mailer-subject" name="subject" class="regular-text" style="width: 100%;" placeholder="e.g. Special Summer Discount!">
				</div>

				<div style="margin-bottom: 20px;">
					<label style="display: block; font-weight: 600; margin-bottom: 8px;">Banner Heading</label>
					<input type="text" id="mailer-heading" name="heading" class="regular-text" style="width: 100%;" placeholder="e.g. Limited Time Offer">
				</div>

				<div style="margin-bottom: 20px;">
					<label style="display: block; font-weight: 600; margin-bottom: 8px;">Message Content</label>
					<?php wp_editor( '', 'mailer_content', array( 'textarea_name' => 'content', 'media_buttons' => true, 'textarea_rows' => 10 ) ); ?>
				</div>

				<div style="margin-bottom: 20px; display: flex; gap: 20px;">
					<div style="flex: 1;">
						<label style="display: block; font-weight: 600; margin-bottom: 8px;">Include Coupon (Optional)</label>
						<select id="mailer-coupon" name="coupon" style="width: 100%;">
							<option value="">-- No Coupon --</option>
							<?php foreach ( $coupons as $coupon ) : ?>
								<option value="<?php echo esc_attr( $coupon->post_title ); ?>"><?php echo esc_html( $coupon->post_title ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div style="flex: 1;">
						<label style="display: block; font-weight: 600; margin-bottom: 8px;">Recipients</label>
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px; align-items: center;">
                                <div style="display: flex; gap: 8px;">
                                    <button type="button" class="button button-small js-mailer-select-all">All</button>
                                    <button type="button" class="button button-small js-mailer-select-none">None</button>
                                </div>
                                <input type="text" id="mailer-recipient-search" placeholder="Search..." style="width: 150px; font-size: 12px; height: 28px;">
                            </div>
                            <div id="mailer-recipient-list" style="max-height: 200px; overflow-y: auto; border: 1px solid #cbd5e1; background: #fff; border-radius: 4px;">
                                <?php foreach ($all_recipients as $r): ?>
                                    <label class="recipient-item" style="display: flex; align-items: center; padding: 6px 10px; border-bottom: 1px solid #f1f5f9; cursor: pointer; font-size: 13px;">
                                        <input type="checkbox" name="selected_emails[]" value="<?php echo esc_attr($r['email']); ?>" style="margin: 0 8px 0 0;" checked>
                                        <div style="flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            <strong><?php echo esc_html($r['name']); ?></strong>
                                            <span style="color: #64748b; font-size: 11px;">&lt;<?php echo esc_html($r['email']); ?>&gt;</span>
                                        </div>
                                        <span style="font-size: 10px; padding: 1px 6px; background: #f1f5f9; border-radius: 4px; color: #64748b;"><?php echo esc_html($r['type']); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
					</div>
				</div>

				<div style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
					<button type="button" id="mailer-send-btn" class="button button-primary button-large">Send Promo Emails</button>
					<span id="mailer-status" style="margin-left: 15px; font-weight: 600; color: #2563eb;"></span>
				</div>
                
                <div id="mailer-progress-wrap" style="display:none; margin-top: 20px;">
                    <div style="width: 100%; background: #e2e8f0; height: 12px; border-radius: 6px; overflow: hidden;">
                        <div id="mailer-progress-bar" style="width: 0%; background: #2563eb; height: 100%; transition: width 0.3s;"></div>
                    </div>
                    <p id="mailer-progress-text" style="font-size: 13px; color: #64748b; margin-top: 8px;"></p>
                </div>
			</form>
		</div>
	</div>

	<script>
	document.addEventListener('DOMContentLoaded', function() {
		const sendBtn = document.getElementById('mailer-send-btn');
		const status = document.getElementById('mailer-status');
        const progressWrap = document.getElementById('mailer-progress-wrap');
        const progressBar = document.getElementById('mailer-progress-bar');
        const progressText = document.getElementById('mailer-progress-text');
        const searchInput = document.getElementById('mailer-recipient-search');
        const selectAllBtn = document.querySelector('.js-mailer-select-all');
        const selectNoneBtn = document.querySelector('.js-mailer-select-none');

		if (!sendBtn) return;

        // Search logic
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const query = searchInput.value.toLowerCase();
                document.querySelectorAll('.recipient-item').forEach(item => {
                    const text = item.textContent.toLowerCase();
                    item.style.display = text.includes(query) ? 'flex' : 'none';
                });
            });
        }

        // Selection logic
        if (selectAllBtn) {
            selectAllBtn.addEventListener('click', () => {
                document.querySelectorAll('input[name="selected_emails[]"]').forEach(cb => {
                    if (cb.closest('.recipient-item').style.display !== 'none') {
                        cb.checked = true;
                    }
                });
            });
        }
        if (selectNoneBtn) {
            selectNoneBtn.addEventListener('click', () => {
                document.querySelectorAll('input[name="selected_emails[]"]').forEach(cb => {
                    if (cb.closest('.recipient-item').style.display !== 'none') {
                        cb.checked = false;
                    }
                });
            });
        }

		sendBtn.addEventListener('click', async function() {
			const subject = document.getElementById('mailer-subject').value;
			const heading = document.getElementById('mailer-heading').value;
			const content = tinymce.get('mailer_content').getContent();
			const coupon = document.getElementById('mailer-coupon').value;
			const selectedEmails = Array.from(document.querySelectorAll('input[name="selected_emails[]"]:checked')).map(cb => cb.value);

			if (!subject || !content || !selectedEmails.length) {
				alert('Please fill in subject, content, and select at least one recipient.');
				return;
			}

			if (!confirm('Are you sure you want to send this email to ' + selectedEmails.length + ' recipients?')) return;

			sendBtn.disabled = true;
			status.textContent = 'Preparing batch...';
            progressWrap.style.display = 'block';
            progressBar.style.width = '0%';

			try {
				const emails = selectedEmails;
				let sentCount = 0;
				const total = emails.length;

				// Send in batches of 5 to avoid timeouts
				const batchSize = 5;
				for (let i = 0; i < emails.length; i += batchSize) {
					const batch = emails.slice(i, i + batchSize);
					
					const batchResp = await fetch(ajaxurl, {
						method: 'POST',
						headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
						body: new URLSearchParams({
							action: 'cwc_mailer_send_batch',
							emails: JSON.stringify(batch),
							subject: subject,
							heading: heading,
							content: content,
							coupon: coupon,
							nonce: '<?php echo wp_create_nonce("cwc_mailer_nonce"); ?>'
						})
					});
					
					sentCount += batch.length;
                    const pct = Math.round((sentCount / total) * 100);
                    progressBar.style.width = pct + '%';
                    progressText.textContent = `Sent ${sentCount} of ${total} emails...`;
				}

				status.textContent = 'All emails sent successfully!';
                status.style.color = '#16a34a';
			} catch (err) {
				console.error(err);
				alert('Error: ' + err.message);
				status.textContent = 'Error sending emails.';
				status.style.color = '#dc2626';
			} finally {
				sendBtn.disabled = false;
			}
		});
	});
	</script>
	<?php
}

/**
 * Get all unique recipients with metadata.
 */
function cwc_mailer_get_all_recipients_data() {
    $subscribers = get_option( 'cwc_newsletter_subscribers', array() );
    $bookings = get_posts([
        'post_type' => 'cwc_booking',
        'numberposts' => -1,
        'post_status' => 'publish'
    ]);
    
    $recipients = [];
    
    // Process subscribers
    foreach ($subscribers as $sub) {
        $email = strtolower($sub['email']);
        $recipients[$email] = [
            'email' => $email,
            'name' => 'Subscriber',
            'type' => 'Subscriber'
        ];
    }
    
    // Process guests (bookings)
    foreach ($bookings as $booking) {
        $email = strtolower(get_post_meta($booking->ID, '_cwc_bk_email', true));
        $name = get_post_meta($booking->ID, '_cwc_bk_name', true);
        if (is_email($email)) {
            // Keep the most recent name if guest already exists
            $recipients[$email] = [
                'email' => $email,
                'name' => $name ?: ($recipients[$email]['name'] ?? 'Guest'),
                'type' => 'Past Guest'
            ];
        }
    }
    
    ksort($recipients);
    return array_values($recipients);
}

/**
 * Get unique guest emails (Legacy support).
 */
function cwc_get_all_unique_guest_emails() {
	$emails = [];
    $data = cwc_mailer_get_all_recipients_data();
    foreach ($data as $r) {
        if ($r['type'] === 'Past Guest') $emails[] = $r['email'];
    }
	return $emails;
}

/**
 * AJAX: Send batch of emails.
 */
function cwc_mailer_send_batch_ajax() {
	check_ajax_referer( 'cwc_mailer_nonce', 'nonce' );
	
	$emails  = isset( $_POST['emails'] ) ? json_decode( wp_unslash( $_POST['emails'] ), true ) : [];
	$subject = sanitize_text_field( $_POST['subject'] );
	$heading = sanitize_text_field( $_POST['heading'] );
	$content = wp_kses_post( wp_unslash( $_POST['content'] ) );
	$coupon  = sanitize_text_field( $_POST['coupon'] );
	
	if ( empty( $emails ) ) {
		wp_send_json_error( [ 'message' => 'No emails in batch.' ] );
	}

	// Build Coupon HTML if provided
	$promo_html = '';
	if ( ! empty( $coupon ) ) {
		$promo_html .= '<div style="margin-top: 30px; padding: 20px; border: 2px dashed #2563eb; background: #eff6ff; text-align: center; border-radius: 12px;">';
		$promo_html .= '<p style="margin: 0 0 10px; font-weight: 600; color: #1e40af; text-transform: uppercase; letter-spacing: 1px;">Your Promo Code</p>';
		$promo_html .= '<div style="font-size: 32px; font-weight: 800; color: #2563eb; font-family: monospace;">' . esc_html( $coupon ) . '</div>';
		$promo_html .= '<p style="margin: 10px 0 0; font-size: 14px; color: #64748b;">Apply this code at checkout to get your discount!</p>';
		$promo_html .= '</div>';
	}

	$headers = ['Content-Type: text/html; charset=UTF-8'];
	
	foreach ( $emails as $email ) {
		$full_html = '';
		if ( function_exists( 'cwc_get_email_template' ) ) {
			$full_html = cwc_get_email_template( $heading, $content . $promo_html, [
				'banner_title' => $heading,
				'banner_subtitle' => 'Exclusive offer for you!'
			] );
		} else {
			$full_html = $content . $promo_html;
		}
		
		wp_mail( $email, $subject, $full_html, $headers );
	}
	
	wp_send_json_success();
}
add_action( 'wp_ajax_cwc_mailer_send_batch', 'cwc_mailer_send_batch_ajax' );
