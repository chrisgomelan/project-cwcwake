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
				<div class="cwc-showcase__social-profile">
					<?php if ( ! empty( $profile_logo ) ) : ?>
						<div class="cwc-showcase__social-logo-ring">
							<img src="<?php echo esc_url( $profile_logo ); ?>" alt="<?php echo esc_attr( $profile_name ); ?>" class="cwc-showcase__social-logo" loading="lazy">
						</div>
					<?php endif; ?>

					<div class="cwc-showcase__social-info">
						<span class="cwc-showcase__social-name"><?php echo esc_html( $profile_name ); ?></span>
						<span class="cwc-showcase__social-tagline"><?php echo esc_html( $profile_tagline ); ?></span>
					</div>
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
					<a href="<?php echo esc_url( $profile_url ); ?>" class="cwc-showcase__card cwc-showcase__card--social" target="_blank" rel="noopener noreferrer">
						<?php if ( ! empty( $social_img ) ) : ?>
							<img src="<?php echo esc_url( $social_img ); ?>" alt="" loading="lazy">
						<?php else : ?>
							<div class="cwc-showcase__social-placeholder"></div>
						<?php endif; ?>
						
						<div class="cwc-showcase__social-hover">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" fill="#ffffff" width="64" height="64"><path d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"/></svg>
						</div>
					</a>

				<?php elseif ( 'videos' === $variant ) : ?>
					<?php
					$vid_url    = $item['videoUrl'] ?? '';
					$vid_poster = $item['videoPoster'] ?? '';
					$embed_url  = '';

					if ( strpos( $vid_url, 'vimeo.com' ) !== false ) {
						if ( preg_match( '/vimeo\.com\/(\d+)/', $vid_url, $matches ) ) {
							$embed_url = "https://player.vimeo.com/video/" . $matches[1] . "?badge=0&autopause=1";
						}
					} elseif ( strpos( $vid_url, 'youtube.com' ) !== false || strpos( $vid_url, 'youtu.be' ) !== false ) {
						if ( preg_match( '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $vid_url, $matches ) ) {
							$embed_url = "https://www.youtube.com/embed/" . $matches[1];
						}
					}
					?>
					<div class="cwc-showcase__card cwc-showcase__card--video">
						<?php if ( ! empty( $embed_url ) ) : ?>
							<iframe
								class="cwc-showcase__video cwc-showcase__video--iframe"
								src="<?php echo esc_url( $embed_url ); ?>"
								frameborder="0"
								allow="autoplay; fullscreen; picture-in-picture"
								allowfullscreen
							></iframe>
						<?php elseif ( ! empty( $vid_url ) ) : ?>
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

		<?php if ( 'videos' === $variant && count( $items ) > 3 ) : ?>
			<div class="cwc-showcase__carousel-nav">
				<button class="cwc-showcase__arrow cwc-showcase__arrow--prev" aria-label="Previous">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
				</button>
				<div class="cwc-showcase__dots">
					<?php 
					$dots_count = ceil( count( $items ) / 3 );
					for ( $i = 0; $i < $dots_count; $i++ ) : 
					?>
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
