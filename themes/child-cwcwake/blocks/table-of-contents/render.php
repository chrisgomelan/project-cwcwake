<?php
/**
 * Table of Contents block — render template.
 *
 * Parses H2/H3 headings from the current post's content and builds a
 * scrollspy-friendly navigation panel. The matching JS (single-post.js)
 * assigns real IDs on the headings at runtime and drives the dot/progress
 * indicator.
 *
 * @package CWC_Wake
 * @since   1.0.0
 */

global $post;

if ( ! $post ) {
	return;
}

$post_content = $post->post_content;

preg_match_all( '/<h([23]).*?>(.*?)<\/h\1>/i', $post_content, $matches, PREG_SET_ORDER );

if ( empty( $matches ) ) {
	if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
		echo '<div class="cwc-toc admin-placeholder"><p style="color:#888;font-size:14px;">TOC: Add H2 or H3 headings to display the table of contents.</p></div>';
	}
	return;
}
?>
<div class="cwc-toc">
	<h3 class="cwc-toc__title">Table of Contents</h3>
	<div class="cwc-toc__list-wrap">
		<div class="cwc-toc__rail">
			<div class="cwc-toc__rail-progress"></div>
			<div class="cwc-toc__dot"></div>
		</div>
		<ul class="cwc-toc__list">
			<?php
			foreach ( $matches as $match ) :
				$level  = $match[1];
				$text   = strip_tags( $match[2] );
				$anchor = sanitize_title( $text );
				?>
				<li class="cwc-toc__item cwc-toc__item--h<?php echo esc_attr( $level ); ?>">
					<a href="#<?php echo esc_attr( $anchor ); ?>" class="cwc-toc__link">
						<?php echo esc_html( $text ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>
<?php
