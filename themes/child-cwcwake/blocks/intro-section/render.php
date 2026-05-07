<?php
/**
 * Intro Section block — render template.
 *
 * @package CWC_Wake
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$heading_line1    = $attributes['headingLine1'] ?? 'The Ultimate';
$heading_emphasis = $attributes['headingEmphasis'] ?? 'WAKEBOARDING';
$heading_line2    = $attributes['headingLine2'] ?? 'Destination';
$description      = $attributes['description'] ?? '';
$tagline          = $attributes['tagline'] ?? 'EXPERIENCE. THE. THRILL.';
$video_url        = $attributes['videoUrl'] ?? '';
$video_poster     = $attributes['videoPoster'] ?? '';

$is_vimeo = ! empty( $video_url ) && preg_match( '/vimeo\.com\/(\d+)/', $video_url, $vimeo_match );
$vimeo_id = $is_vimeo ? $vimeo_match[1] : '';

$wrapper_attrs = get_block_wrapper_attributes(
	array(
		'class' => 'cwc-intro',
	)
);
?>

<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="cwc-intro__inner">

		<!-- Left: text column -->
		<div class="cwc-intro__text">
			<h2 class="cwc-intro__heading">
				<?php echo esc_html( $heading_line1 ); ?><br>
				<em><?php echo esc_html( $heading_emphasis ); ?></em><br>
				<?php echo esc_html( $heading_line2 ); ?>
			</h2>

			<?php if ( ! empty( $description ) ) : ?>
				<p class="cwc-intro__description"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</div>

		<!-- Right: video column -->
		<div class="cwc-intro__media">
			<?php if ( ! empty( $tagline ) ) : ?>
				<span class="cwc-intro__tagline"><?php echo esc_html( $tagline ); ?></span>
			<?php endif; ?>

			<div class="cwc-intro__bracket cwc-intro__bracket--tl" aria-hidden="true"></div>
			<div class="cwc-intro__bracket cwc-intro__bracket--br" aria-hidden="true"></div>

			<div class="cwc-intro__video-wrap">
				<?php if ( $is_vimeo ) : ?>
					<div class="cwc-intro__lite-embed" data-embed-url="https://player.vimeo.com/video/<?php echo esc_attr( $vimeo_id ); ?>?byline=0&portrait=0&title=0&autoplay=1">
						<?php if ( ! empty( $video_poster ) ) : ?>
							<img src="<?php echo esc_url( $video_poster ); ?>" alt="" class="cwc-intro__poster-img" loading="lazy">
						<?php else : ?>
							<div class="cwc-intro__poster-img cwc-intro__poster-img--empty"></div>
						<?php endif; ?>
						
						<div class="cwc-intro__play-overlay">
							<span class="cwc-intro__play-icon" aria-hidden="true">
								<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="currentColor">
									<circle cx="12" cy="12" r="11" fill="rgba(0,0,0,0.6)" stroke="white" stroke-width="1.5"/>
									<polygon points="9.5,7 17,12 9.5,17" fill="white"/>
								</svg>
							</span>
						</div>
					</div>
				<?php elseif ( ! empty( $video_url ) ) : ?>
					<video
						class="cwc-intro__video"
						src="<?php echo esc_url( $video_url ); ?>"
						<?php echo ! empty( $video_poster ) ? 'poster="' . esc_url( $video_poster ) . '"' : ''; ?>
						controls
						playsinline
						preload="metadata"
					></video>
				<?php else : ?>
					<div class="cwc-intro__video-placeholder">
						<?php if ( ! empty( $video_poster ) ) : ?>
							<img src="<?php echo esc_url( $video_poster ); ?>" alt="" loading="lazy">
						<?php endif; ?>
						<span class="cwc-intro__play-icon" aria-hidden="true">
							<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="11" fill="rgba(0,0,0,0.5)" stroke="white" stroke-width="1"/><polygon points="9.5,7 17,12 9.5,17" fill="white"/></svg>
						</span>
					</div>
				<?php endif; ?>
			</div>
		</div>

	</div>
</section>
