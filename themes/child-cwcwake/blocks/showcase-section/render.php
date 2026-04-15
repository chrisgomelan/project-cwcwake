<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$variant        = $attributes['variant'] ?? 'cards';
$heading_start  = $attributes['headingStart'] ?? 'Choose Your';
$heading_emp    = $attributes['headingEmphasis'] ?? 'ADVENTURE';
$emphasis_color = $attributes['emphasisColor'] ?? 'accent';
$subtitle       = $attributes['subtitle'] ?? '';
$bg_image       = $attributes['backgroundImage'] ?? '';
$items          = $attributes['items'] ?? [];

$profile_logo      = $attributes['profileLogo'] ?? '';
$profile_name      = $attributes['profileName'] ?? 'cwcwakepark';
$profile_tagline   = $attributes['profileTagline'] ?? 'Best Wakepark on Earth';
$profile_url       = $attributes['profileUrl'] ?? '';
$profile_btn_label = $attributes['profileBtnLabel'] ?? 'Follow Us';

$fallback_items_cards = [
	[ 'title' => 'Water Sports',     'image' => '', 'buttonLabel' => 'EXPLORE', 'buttonUrl' => '/water-sports/' ],
	[ 'title' => 'Land Activities',  'image' => '', 'buttonLabel' => 'EXPLORE', 'buttonUrl' => '/land-activities/' ],
	[ 'title' => 'Elite Facilities', 'image' => '', 'buttonLabel' => 'EXPLORE', 'buttonUrl' => '/elite-facilities/' ],
];

$fallback_items_videos = [
	[ 'videoUrl' => '', 'videoPoster' => '' ],
	[ 'videoUrl' => '', 'videoPoster' => '' ],
	[ 'videoUrl' => '', 'videoPoster' => '' ],
];

$fallback_items_social = [
	[ 'image' => '' ],
	[ 'image' => '' ],
	[ 'image' => '' ],
];

if ( empty( $items ) ) {
	if ( 'videos' === $variant ) {
		$items = $fallback_items_videos;
	} elseif ( 'social' === $variant ) {
		$items = $fallback_items_social;
	} else {
		$items = $fallback_items_cards;
	}
}

$bg_style = '';
if ( ! empty( $bg_image ) ) {
	$bg_style = sprintf( 'background-image:url(%s);', esc_url( $bg_image ) );
}

$wrapper_attrs = get_block_wrapper_attributes( [
	'class' => 'cwc-showcase cwc-showcase--' . esc_attr( $variant ),
	'style' => $bg_style,
] );
?>

<section <?php echo $wrapper_attrs; ?>>
	<div class="cwc-showcase__inner">

		<?php if ( 'social' === $variant ) : ?>
			<!-- Social profile header -->
			<div class="cwc-showcase__social-header">
				<?php if ( ! empty( $profile_logo ) ) : ?>
					<img src="<?php echo esc_url( $profile_logo ); ?>" alt="<?php echo esc_attr( $profile_name ); ?>" class="cwc-showcase__social-logo" loading="lazy">
				<?php endif; ?>

				<div class="cwc-showcase__social-info">
					<span class="cwc-showcase__social-name"><?php echo esc_html( $profile_name ); ?></span>
					<span class="cwc-showcase__social-tagline"><?php echo esc_html( $profile_tagline ); ?></span>
				</div>

				<?php if ( ! empty( $profile_url ) ) : ?>
					<a href="<?php echo esc_url( $profile_url ); ?>" class="cwc-showcase__social-btn" target="_blank" rel="noopener noreferrer">
						<?php echo esc_html( $profile_btn_label ); ?>
					</a>
				<?php endif; ?>
			</div>
		<?php else : ?>
			<!-- Header -->
			<div class="cwc-showcase__header">
				<h2 class="cwc-showcase__heading">
					<?php echo esc_html( $heading_start ); ?>
					<em style="color:var(--wp--preset--color--<?php echo esc_attr( $emphasis_color ); ?>)">
						<?php echo esc_html( $heading_emp ); ?>
					</em>
				</h2>

				<?php if ( ! empty( $subtitle ) ) : ?>
					<p class="cwc-showcase__subtitle"><?php echo esc_html( $subtitle ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<!-- Grid -->
		<div class="cwc-showcase__grid">
			<?php foreach ( $items as $item ) : ?>

				<?php if ( 'social' === $variant ) : ?>
					<?php $social_img = $item['image'] ?? ''; ?>
					<div class="cwc-showcase__card cwc-showcase__card--social">
						<?php if ( ! empty( $social_img ) ) : ?>
							<img src="<?php echo esc_url( $social_img ); ?>" alt="" loading="lazy">
						<?php else : ?>
							<div class="cwc-showcase__social-placeholder"></div>
						<?php endif; ?>
					</div>

				<?php elseif ( 'videos' === $variant ) : ?>
					<?php
					$vid_url    = $item['videoUrl'] ?? '';
					$vid_poster = $item['videoPoster'] ?? '';
					?>
					<div class="cwc-showcase__card cwc-showcase__card--video">
						<?php if ( ! empty( $vid_url ) ) : ?>
							<video
								class="cwc-showcase__video"
								src="<?php echo esc_url( $vid_url ); ?>"
								<?php echo ! empty( $vid_poster ) ? 'poster="' . esc_url( $vid_poster ) . '"' : ''; ?>
								controls
								playsinline
								preload="metadata"
							></video>
						<?php elseif ( ! empty( $vid_poster ) ) : ?>
							<div class="cwc-showcase__poster">
								<img src="<?php echo esc_url( $vid_poster ); ?>" alt="" loading="lazy">
								<span class="cwc-showcase__play" aria-hidden="true">
									<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48"><circle cx="24" cy="24" r="23" fill="rgba(0,0,0,0.5)" stroke="white" stroke-width="2"/><polygon points="19,14 35,24 19,34" fill="white"/></svg>
								</span>
							</div>
						<?php else : ?>
							<div class="cwc-showcase__poster cwc-showcase__poster--empty">
								<span class="cwc-showcase__play" aria-hidden="true">
									<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48"><circle cx="24" cy="24" r="23" fill="rgba(0,0,0,0.5)" stroke="white" stroke-width="2"/><polygon points="19,14 35,24 19,34" fill="white"/></svg>
								</span>
							</div>
						<?php endif; ?>
					</div>

				<?php else : ?>
					<?php
					$card_title = $item['title'] ?? '';
					$card_image = $item['image'] ?? '';
					$hover_gif  = $item['hoverGif'] ?? '';
					$btn_label  = $item['buttonLabel'] ?? 'EXPLORE';
					$btn_url    = $item['buttonUrl'] ?? '#';
					?>
					<a href="<?php echo esc_url( $btn_url ); ?>" class="cwc-showcase__card cwc-showcase__card--image" <?php echo ! empty( $card_image ) ? 'style="background-image:url(' . esc_url( $card_image ) . ')"' : ''; ?>>
						<?php if ( ! empty( $hover_gif ) ) : ?>
							<img class="cwc-showcase__card-gif" src="<?php echo esc_url( $hover_gif ); ?>" alt="" loading="lazy">
						<?php endif; ?>
						<div class="cwc-showcase__card-content">
							<?php if ( ! empty( $card_title ) ) : ?>
								<h3 class="cwc-showcase__card-title"><?php echo esc_html( $card_title ); ?></h3>
							<?php endif; ?>
							<span class="cwc-showcase__card-btn"><?php echo esc_html( $btn_label ); ?></span>
						</div>
					</a>
				<?php endif; ?>

			<?php endforeach; ?>
		</div>

		<?php if ( 'videos' === $variant && count( $items ) > 1 ) : ?>
			<div class="cwc-showcase__carousel-nav">
				<button class="cwc-showcase__arrow cwc-showcase__arrow--prev" aria-label="Previous">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
				</button>
				<div class="cwc-showcase__dots">
					<?php for ( $i = 0; $i < count( $items ); $i++ ) : ?>
						<span class="cwc-showcase__dot<?php echo 0 === $i ? ' cwc-showcase__dot--active' : ''; ?>"></span>
					<?php endfor; ?>
				</div>
				<button class="cwc-showcase__arrow cwc-showcase__arrow--next" aria-label="Next">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
				</button>
			</div>
		<?php endif; ?>

	</div>
</section>
