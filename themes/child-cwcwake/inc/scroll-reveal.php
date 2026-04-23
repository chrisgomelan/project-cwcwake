<?php
/**
 * CWC Wake — Scroll Reveal Filter
 *
 * Automatically adds `data-reveal` attributes to block wrapper output
 * based on a centrally managed mapping. This keeps individual render.php
 * files untouched while enabling per-section scroll entrance animations.
 *
 * How it works:
 *   1. `cwc_reveal_map()` defines which blocks get which animation variant.
 *   2. A `render_block` filter inspects the rendered HTML for the matching
 *      class and injects the `data-reveal` (and optional delay/duration)
 *      attribute into the outermost element.
 *
 * To change a section's animation, edit the map below — no template
 * or render.php changes required.
 *
 * Available variants:
 *   fade-up | fade-down | fade-left | fade-right
 *   zoom-in | zoom-out | flip-up | stagger | split
 *
 * @package CWC_Wake
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reveal configuration map.
 *
 * Each key is a CSS class that already exists on the block's outermost
 * element (set via `get_block_wrapper_attributes()` in render.php).
 *
 * Values:
 *   'variant'  — (required) one of the supported animation names.
 *   'delay'    — (optional) entrance delay in ms.
 *   'duration' — (optional) transition duration in ms.
 *   'threshold'— (optional) IntersectionObserver threshold 0–1.
 *
 * @return array<string, array{variant:string, delay?:int, duration?:int, threshold?:float}>
 */
function cwc_reveal_map() {
	return [

		/* ---- Home page ---- */
		'cwc-intro'            => [ 'variant' => 'split' ],
		'cwc-showcase--cards'  => [ 'variant' => 'fade-up' ],
		'cwc-showcase--videos' => [ 'variant' => 'fade-up',   'delay' => 100 ],
		'cwc-showcase--social' => [ 'variant' => 'zoom-in' ],
		'cwc-accommodations'   => [ 'variant' => 'fade-up' ],
		'cwc-reviews'          => [ 'variant' => 'split' ],

		/* ---- About page ---- */
		'cwc-timeline'         => [ 'variant' => 'fade-up' ],
		'cwc-champions'        => [ 'variant' => 'fade-up' ],
		'cwc-certified'        => [ 'variant' => 'zoom-in' ],
		'cwc-empower'          => [ 'variant' => 'fade-up' ],

		/* ---- Accommodations page ---- */
		'cwc-why-stay'         => [ 'variant' => 'stagger' ],

		/* ---- Gallery page ---- */
		'cwc-gallery-grid'     => [ 'variant' => 'fade-up' ],

		/* ---- Shared components ---- */
		'cwc-cards-section--detailed' => [ 'variant' => 'stagger' ],
		'cwc-cards-section--overlay'  => [ 'variant' => 'fade-up' ],
		'cwc-cta-footer'              => [ 'variant' => 'fade-up' ],

		/* ---- Blogs page ---- */
		'cwc-featured-blogs'   => [ 'variant' => 'fade-up' ],
		'cwc-all-blogs'        => [ 'variant' => 'fade-up',   'delay' => 100 ],
		'cwc-upcoming-events'  => [ 'variant' => 'fade-right' ],

		/* ---- FAQ page ---- */
		'cwc-faq'              => [ 'variant' => 'fade-up' ],

		/* ---- Contact page ---- */
		'cwc-contact-info'     => [ 'variant' => 'split' ],
		'cwc-contact-form'     => [ 'variant' => 'fade-up' ],

		/* ---- Rates page ---- */
		'cwc-rates-manager'    => [ 'variant' => 'fade-up' ],

		/* ---- Water Sports page ---- */
		'cwc-feature-split'    => [ 'variant' => 'split' ],
		'cwc-feature-banner'   => [ 'variant' => 'fade-up' ],
		'cwc-coaching'         => [ 'variant' => 'split' ],
	];
}

/**
 * Inject `data-reveal` attributes into rendered block HTML.
 *
 * Runs at `render_block` priority 20 (after WordPress core) so the
 * class string is fully assembled.
 *
 * @param string $html  Block HTML output.
 * @param array  $block Block metadata (name, attrs, etc.).
 * @return string Modified HTML.
 */
function cwc_apply_scroll_reveal( $html, $block ) {
	/* Skip empty or admin-only output. */
	if ( empty( $html ) || is_admin() ) {
		return $html;
	}

	$map = cwc_reveal_map();

	foreach ( $map as $class => $config ) {
		/* Does the outermost element contain this class? */
		if ( strpos( $html, $class ) === false ) {
			continue;
		}

		$variant   = esc_attr( $config['variant'] );
		$extra     = '';

		if ( ! empty( $config['delay'] ) ) {
			$extra .= sprintf( ' data-reveal-delay="%d"', (int) $config['delay'] );
		}
		if ( ! empty( $config['duration'] ) ) {
			$extra .= sprintf( ' data-reveal-duration="%d"', (int) $config['duration'] );
		}
		if ( isset( $config['threshold'] ) ) {
			$extra .= sprintf( ' data-reveal-threshold="%s"', esc_attr( $config['threshold'] ) );
		}

		$attr = sprintf( 'data-reveal="%s"%s', $variant, $extra );

		/*
		 * Insert the attribute into the first opening tag.
		 * Pattern: <section class="..." → <section data-reveal="..." class="..."
		 */
		$html = preg_replace(
			'/^(<\w+)\s/',
			'$1 ' . $attr . ' ',
			ltrim( $html ),
			1
		);

		/* Each block should match at most one mapping. */
		break;
	}

	return $html;
}
add_filter( 'render_block', 'cwc_apply_scroll_reveal', 20, 2 );
