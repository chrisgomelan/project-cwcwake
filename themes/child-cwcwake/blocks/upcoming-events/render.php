<?php
/**
 * Upcoming Events block — server render template.
 *
 * Renders the timeline + active-event card section described in
 * `designs/blogs-design.md` § 2.
 *
 * Data source:
 *
 *   - Posts with `_cwc_event_date` meta key (set by the seeder when
 *     `event_offset_days` is present, or by an editor entering an
 *     event date manually).
 *   - Limited to events whose date is today or in the future, sorted
 *     ascending so the soonest event becomes the "active" item.
 *
 * The active event (first in the result) renders the full content
 * card on the right; the rest of the timeline only shows their day +
 * month markers in the left rail. Clicking a non-active marker
 * navigates to the corresponding post (the design doesn't include
 * an inline switcher, and a JS-driven swap would force editors to
 * pre-author multiple cards).
 *
 * @package CWC_Wake
 * @since   1.0.0
 *
 * @var array $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Helper declarations live above the markup so they're available
 * on the very first render. Wrapping in `function_exists()` keeps
 * us safe against double-include — but the guard prevents PHP's
 * normal top-of-file function hoisting, which is why these cannot
 * live at the bottom of the file.
 */
if ( ! function_exists( 'cwc_upcoming_events_query' ) ) :
	/**
	 * Resolve upcoming event posts ordered by event date ascending.
	 *
	 * Limits results to events with a future-or-today
	 * `_cwc_event_date` value so the timeline never shows stale
	 * entries. Falls back to a date-string comparison (`DATE` cast)
	 * so PHP-side filtering isn't required and indices on the meta
	 * table still apply.
	 *
	 * @since 1.0.0
	 *
	 * @param int $limit Maximum number of upcoming events to return.
	 * @return WP_Post[] Posts ordered by `_cwc_event_date` ascending (soonest first).
	 */
	function cwc_upcoming_events_query( int $limit ): array {
		if ( $limit <= 0 ) {
			return [];
		}

		$meta_key = defined( 'CWC_BLOG_META_EVENT_DATE' ) ? CWC_BLOG_META_EVENT_DATE : '_cwc_event_date';
		$today    = gmdate( 'Y-m-d' );

		return get_posts(
			[
				'post_type'              => 'post',
				'post_status'            => 'publish',
				'posts_per_page'         => $limit,
				'meta_key'               => $meta_key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'orderby'                => 'meta_value',
				'order'                  => 'ASC',
				'meta_query'             => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					[
						'key'     => $meta_key,
						'value'   => $today,
						'compare' => '>=',
						'type'    => 'DATE',
					],
				],
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
			]
		);
	}
endif;

if ( ! function_exists( 'cwc_event_meta_for_post' ) ) :
	/**
	 * Build the formatted day / month / readable-date triple for an event.
	 *
	 * Centralizes date formatting so the timeline rail and the
	 * SR-only announcements share one source of truth — preventing
	 * drift like "Aug 15" vs. "August 15".
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $post Event post.
	 * @return array{day:string,month:string,readable:string} Day number (e.g. "15"), month (e.g. "August"), and full readable date.
	 */
	function cwc_event_meta_for_post( WP_Post $post ): array {
		$meta_key = defined( 'CWC_BLOG_META_EVENT_DATE' ) ? CWC_BLOG_META_EVENT_DATE : '_cwc_event_date';
		$raw      = (string) get_post_meta( (int) $post->ID, $meta_key, true );

		/*
		 * No event date set → fall back to the publish date so the
		 * marker still renders. This shouldn't happen for events
		 * surfaced by `cwc_upcoming_events_query()` (the meta query
		 * filters them out) but we keep the guard for defensive use
		 * elsewhere.
		 */
		if ( '' === $raw ) {
			$raw = (string) get_the_date( 'Y-m-d', $post );
		}

		$timestamp = strtotime( $raw );
		if ( false === $timestamp ) {
			$timestamp = (int) get_post_time( 'U', true, $post );
		}

		return [
			'day'      => date_i18n( 'j', $timestamp ),
			'month'    => date_i18n( 'F', $timestamp ),
			'readable' => date_i18n( get_option( 'date_format', 'F j, Y' ), $timestamp ),
		];
	}
endif;

$heading           = isset( $attributes['heading'] ) ? (string) $attributes['heading'] : '';
$heading_highlight = isset( $attributes['headingHighlight'] ) ? (string) $attributes['headingHighlight'] : '';
$description       = isset( $attributes['description'] ) ? (string) $attributes['description'] : '';
$limit             = isset( $attributes['limit'] ) ? max( 1, (int) $attributes['limit'] ) : 3;
$placeholder       = isset( $attributes['placeholderImage'] ) ? (string) $attributes['placeholderImage'] : '';

$events = cwc_upcoming_events_query( $limit );

if ( empty( $events ) ) {
	return;
}

$active = array_shift( $events );
$rest   = $events;

$active_id   = (int) $active->ID;
$active_meta = cwc_event_meta_for_post( $active );
$active_img  = cwc_blog_card_image_url( $active_id, $placeholder );

$wrapper_attrs = get_block_wrapper_attributes( [ 'class' => 'cwc-upcoming-events' ] );
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<header class="cwc-upcoming-events__header">
		<?php if ( '' !== $heading || '' !== $heading_highlight ) : ?>
			<h2 class="cwc-upcoming-events__title">
				<?php if ( '' !== $heading ) : ?>
					<span class="cwc-upcoming-events__title-text"><?php echo esc_html( $heading ); ?></span>
				<?php endif; ?>
				<?php if ( '' !== $heading_highlight ) : ?>
					<em class="cwc-upcoming-events__title-highlight"><?php echo esc_html( $heading_highlight ); ?></em>
				<?php endif; ?>
			</h2>
		<?php endif; ?>

		<?php if ( '' !== $description ) : ?>
			<p class="cwc-upcoming-events__description"><?php echo esc_html( $description ); ?></p>
		<?php endif; ?>
	</header>

	<div class="cwc-upcoming-events__layout">
		<ol class="cwc-upcoming-events__timeline" aria-label="<?php esc_attr_e( 'Upcoming events', 'child-cwcwake' ); ?>">
			<li class="cwc-upcoming-events__rail-item cwc-upcoming-events__rail-item--active">
				<span class="cwc-upcoming-events__month"><?php echo esc_html( $active_meta['month'] ); ?></span>
				<span class="cwc-upcoming-events__day cwc-upcoming-events__day--active" aria-hidden="true">
					<?php echo esc_html( $active_meta['day'] ); ?>
				</span>
				<span class="screen-reader-text">
					<?php
					/* translators: 1: Event title. 2: Event date. */
					echo esc_html( sprintf( __( 'Active event: %1$s on %2$s.', 'child-cwcwake' ), get_the_title( $active_id ), $active_meta['readable'] ) );
					?>
				</span>
			</li>

			<?php foreach ( $rest as $event ) :
				$event_id   = (int) $event->ID;
				$event_meta = cwc_event_meta_for_post( $event );
				$event_url  = (string) get_permalink( $event_id );
				?>
				<li class="cwc-upcoming-events__rail-item">
					<span class="cwc-upcoming-events__month"><?php echo esc_html( $event_meta['month'] ); ?></span>
					<a class="cwc-upcoming-events__day" href="<?php echo esc_url( $event_url ); ?>"
						aria-label="<?php
						/* translators: 1: Event title. 2: Event date. */
						echo esc_attr( sprintf( __( '%1$s on %2$s', 'child-cwcwake' ), get_the_title( $event_id ), $event_meta['readable'] ) );
						?>">
						<?php echo esc_html( $event_meta['day'] ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ol>

		<article class="cwc-upcoming-events__card">
			<?php if ( '' !== $active_img ) : ?>
				<a class="cwc-upcoming-events__card-image-link" href="<?php echo esc_url( get_permalink( $active_id ) ); ?>"
					aria-label="<?php echo esc_attr( get_the_title( $active_id ) ); ?>">
					<span class="cwc-upcoming-events__card-image" role="img"
						aria-label="<?php echo esc_attr( get_the_title( $active_id ) ); ?>"
						style="background-image:url('<?php echo esc_url( $active_img ); ?>');"></span>
				</a>
			<?php endif; ?>

			<div class="cwc-upcoming-events__card-body">
				<h3 class="cwc-upcoming-events__card-title">
					<a href="<?php echo esc_url( get_permalink( $active_id ) ); ?>"><?php echo esc_html( get_the_title( $active_id ) ); ?></a>
				</h3>

				<?php
				$excerpt = cwc_blog_card_excerpt( $active, 60 );
				if ( '' !== $excerpt ) : ?>
					<p class="cwc-upcoming-events__card-excerpt"><?php echo esc_html( $excerpt ); ?></p>
				<?php endif; ?>
			</div>
		</article>
	</div>
</section>
