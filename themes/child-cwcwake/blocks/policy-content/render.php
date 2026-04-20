<?php
/**
 * Render template for the cwc/policy-content block.
 *
 * Renders the body of the legal pages — Privacy Policy and Terms &
 * Conditions — as a short intro paragraph followed by a single
 * decorative card containing the labelled clauses. Layout, colours,
 * and typography follow `designs/privacy-terms-design.md`.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner block markup (unused).
 * @var WP_Block $block      Block instance.
 *
 * @package CWC_Wake
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$intro    = isset( $attributes['intro'] ) ? (string) $attributes['intro'] : '';
$sections = isset( $attributes['sections'] ) && is_array( $attributes['sections'] ) ? $attributes['sections'] : [];

/*
 * Bail out gracefully when the page hasn't been seeded yet so the
 * editor doesn't render an empty card frame.
 */
if ( '' === trim( $intro ) && empty( $sections ) ) {
	return;
}

if ( ! function_exists( 'cwc_policy_paragraphs' ) ) {
	/**
	 * Convert a multi-paragraph string into separate `<p>` elements.
	 *
	 * Body copy in the seeder is stored as a single string with `\n\n`
	 * paragraph breaks. Splitting here keeps the seed data readable
	 * while preserving semantic paragraph boundaries in the output.
	 *
	 * @since 1.0.0
	 *
	 * @param string $text Raw body copy with `\n\n` paragraph separators.
	 * @return string Rendered `<p>` markup, all values escaped.
	 */
	function cwc_policy_paragraphs( $text ) {
		$paragraphs = preg_split( "/\r?\n\r?\n/", trim( $text ) );
		if ( empty( $paragraphs ) ) {
			return '';
		}

		$out = '';
		foreach ( $paragraphs as $paragraph ) {
			$paragraph = trim( $paragraph );
			if ( '' === $paragraph ) {
				continue;
			}
			$out .= '<p class="cwc-policy__body">' . nl2br( esc_html( $paragraph ) ) . '</p>';
		}
		return $out;
	}
}

$wrapper_attrs = get_block_wrapper_attributes( [ 'class' => 'cwc-policy' ] );
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( '' !== trim( $intro ) ) : ?>
		<div class="cwc-policy__intro">
			<?php
			echo cwc_policy_paragraphs( $intro ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $sections ) ) : ?>
		<div class="cwc-policy__panel">
			<?php foreach ( $sections as $section ) :
				$label = isset( $section['label'] ) ? (string) $section['label'] : '';
				$body  = isset( $section['body'] ) ? (string) $section['body'] : '';

				if ( '' === trim( $label ) && '' === trim( $body ) ) {
					continue;
				}

				/*
				 * Build a stable id from the label so the section is
				 * deep-linkable (e.g. `#section-cookies`).
				 */
				$anchor = 'section-' . sanitize_title( $label );
			?>
				<article class="cwc-policy__section" id="<?php echo esc_attr( $anchor ); ?>">
					<?php if ( '' !== trim( $label ) ) : ?>
						<h2 class="cwc-policy__label"><?php echo esc_html( $label ); ?></h2>
					<?php endif; ?>

					<?php if ( '' !== trim( $body ) ) : ?>
						<div class="cwc-policy__body-wrap">
							<?php
							echo cwc_policy_paragraphs( $body ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							?>
						</div>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>
