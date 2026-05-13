<?php
/**
 * Site-wide floating chat assistant (Groq, OpenAI-compatible API) — server proxy + assets.
 *
 * @package CWC_Wake
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current view is a booking-flow context (no chat widget).
 *
 * @return bool
 */
function cwc_chat_is_booking_flow_page() {
	if ( is_admin() ) {
		return false;
	}

	if ( is_page( 'booking' ) ) {
		return true;
	}

	$obj = get_queried_object();
	if ( $obj instanceof WP_Post && 'revision' !== $obj->post_type ) {
		if ( function_exists( 'has_block' ) && has_block( 'cwc/booking-flow', $obj->post_content ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Load chat assets on front-end only, excluding booking-flow pages.
 *
 * @return bool
 */
function cwc_chat_should_load_widget() {
	if ( is_admin() || wp_doing_ajax() || wp_is_json_request() ) {
		return false;
	}

	if ( cwc_chat_is_booking_flow_page() ) {
		return false;
	}

	return true;
}

/**
 * Client IP for rate limiting (best-effort; not for security guarantees).
 *
 * @return string
 */
function cwc_chat_client_ip() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
		return '0.0.0.0';
	}
	return $ip;
}

/**
 * Fixed system prompt — factual tone; site index is appended separately.
 *
 * @return string
 */
function cwc_chat_system_prompt() {
	$prompt = implode(
		' ',
		array(
			'You are a helpful assistant for CWC Wake (CamSur Watersports Complex — wakeboarding, water sports, and resort stays in the Philippines).',
			'You will receive a live site index with real URLs—treat it as the source of truth for where to send visitors.',
			'Answer concisely. For starting a reservation (dates, room, guests), send visitors to the **Book now** URL from the index—not the checkout-only booking page unless they are finishing payment.',
			'Use index “Details” for facts when helpful, but never paste long blocks verbatim (especially contact-page email/phone boilerplate)—answer briefly and link to the page.',
			'Never invent prices, availability, discounts, or legal terms—defer to Rates, Accommodations, Book now, FAQs, Contact, or policy pages from the index.',
			'Format answers in short Markdown: **bold** for key labels, optional ## headings and "- " bullets, and use [short label](full_url) for links instead of pasting raw URLs when you can.',
			'You only help with CWC Wake, CamSur Watersports Complex, resort stays, wakeboarding and water sports, activities, accommodations, rates, booking, location, policies, and what appears in the site index.',
			'For unrelated requests (homework, arithmetic, general trivia, coding, politics, other brands, or anything not about this resort), do **not** answer the off-topic question—give one short polite line that you only cover CWC Wake, and point them to ask about the resort or use **Contact** from the index.',
		)
	);

	/**
	 * Filter the chat system prompt (base instructions only; site index follows).
	 *
	 * @param string $prompt Default prompt.
	 */
	return apply_filters( 'cwc_chat_system_prompt', $prompt );
}

/**
 * Resolve a published page URL from one or more path slugs (hierarchical supported).
 *
 * @param string ...$paths Relative paths without leading slash.
 * @return string
 */
function cwc_chat_resolve_page_url( ...$paths ) {
	foreach ( $paths as $path ) {
		$path = trim( (string) $path, '/' );
		if ( '' === $path ) {
			continue;
		}
		$home = trailingslashit( home_url() );
		foreach ( array( $home . $path . '/', $home . $path ) as $candidate ) {
			$post_id = url_to_postid( $candidate );
			if ( $post_id ) {
				return get_permalink( $post_id );
			}
		}
	}
	return '';
}

/**
 * Plain-text summary for chat context (excerpt + trimmed body + optional meta).
 *
 * @param WP_Post $post         Post object.
 * @param int     $max_length  Max characters for body-derived text.
 * @param string  $context_key Optional key from canonical map (for filters).
 * @return string
 */
function cwc_chat_summarize_post_for_context( WP_Post $post, $max_length = 300, $context_key = '' ) {
	$slug = isset( $post->post_name ) ? (string) $post->post_name : '';
	if ( 'contact' === $context_key || in_array( $slug, array( 'contact', 'contact-us' ), true ) ) {
		return __( 'Location, map, email, and phone — link here; do not paste the full contact block from the page.', 'child-cwcwake' );
	}

	$parts = array();

	$excerpt = trim( wp_strip_all_tags( $post->post_excerpt ) );
	if ( '' !== $excerpt ) {
		$parts[] = $excerpt;
	}

	$content = (string) $post->post_content;
	if ( '' !== $content ) {
		$html = function_exists( 'do_blocks' ) ? do_blocks( $content ) : $content;
		$text = wp_strip_all_tags( $html );
		$text = preg_replace( '/\s+/u', ' ', $text );
		$text = trim( $text );
		if ( '' !== $text ) {
			if ( function_exists( 'mb_strlen' ) && function_exists( 'mb_substr' ) && mb_strlen( $text ) > $max_length ) {
				$text = mb_substr( $text, 0, $max_length ) . '…';
			} elseif ( strlen( $text ) > $max_length ) {
				$text = substr( $text, 0, $max_length ) . '…';
			}
			$parts[] = $text;
		}
	}

	if ( 'accommodation' === $post->post_type ) {
		$acc_keys = apply_filters(
			'cwc_chat_accommodation_meta_keys',
			array( '_cwc_price', '_cwc_price_sub', '_cwc_capacity' ),
			$post
		);
		foreach ( (array) $acc_keys as $meta_key ) {
			$meta_key = sanitize_key( $meta_key );
			if ( '' === $meta_key ) {
				continue;
			}
			$v = get_post_meta( $post->ID, $meta_key, true );
			if ( is_string( $v ) && '' !== trim( $v ) ) {
				$label = ltrim( $meta_key, '_' );
				$parts[] = $label . ': ' . wp_strip_all_tags( $v );
			}
		}
	}

	$extra_keys = apply_filters( 'cwc_chat_include_post_meta_keys', array(), $post, $context_key );
	foreach ( (array) $extra_keys as $meta_key ) {
		$meta_key = is_string( $meta_key ) ? sanitize_key( $meta_key ) : '';
		if ( '' === $meta_key ) {
			continue;
		}
		$v = get_post_meta( $post->ID, $meta_key, true );
		if ( is_string( $v ) && '' !== trim( $v ) ) {
			$parts[] = $meta_key . ': ' . wp_strip_all_tags( $v );
		}
	}

	$out = trim( implode( ' | ', array_filter( array_map( 'trim', $parts ) ) ) );
	/**
	 * Filter the generated page summary string for chat context.
	 *
	 * @param string  $out          Summary text.
	 * @param WP_Post $post         Post.
	 * @param string  $context_key  Map key or empty.
	 */
	return apply_filters( 'cwc_chat_post_context_summary', $out, $post, $context_key );
}

/**
 * Canonical front-end destinations for prompts and suggestion chips.
 *
 * @return array<string, array{label: string, url: string, hint?: string}>
 */
function cwc_chat_canonical_map() {
	static $map = null;
	if ( is_array( $map ) ) {
		return $map;
	}

	$defs = array(
		'home'               => array( 'label' => __( 'Home', 'child-cwcwake' ), 'paths' => array() ),
		'rates'              => array( 'label' => __( 'Rates', 'child-cwcwake' ), 'paths' => array( 'rates' ) ),
		'book_now'           => array( 'label' => __( 'Book now', 'child-cwcwake' ), 'paths' => array( 'book-now' ) ),
		'booking_checkout'   => array( 'label' => __( 'Complete reservation (checkout)', 'child-cwcwake' ), 'paths' => array( 'booking' ) ),
		'accommodations'     => array( 'label' => __( 'Accommodations', 'child-cwcwake' ), 'paths' => array( 'accommodations' ) ),
		'contact'            => array( 'label' => __( 'Contact', 'child-cwcwake' ), 'paths' => array( 'contact-us', 'contact' ) ),
		'faqs'               => array( 'label' => __( 'FAQs', 'child-cwcwake' ), 'paths' => array( 'plan-your-trip/faqs', 'faqs' ) ),
		'blogs'              => array( 'label' => __( 'Blog', 'child-cwcwake' ), 'paths' => array( 'plan-your-trip/blogs', 'blogs' ) ),
		'water-sports'       => array( 'label' => __( 'Water sports', 'child-cwcwake' ), 'paths' => array( 'activities/water-sports', 'water-sports' ) ),
		'land-activities'    => array( 'label' => __( 'Land activities', 'child-cwcwake' ), 'paths' => array( 'activities/land-activities', 'land-activities' ) ),
		'elite-facilities'   => array( 'label' => __( 'Elite facilities', 'child-cwcwake' ), 'paths' => array( 'activities/elite-facilities', 'elite-facilities' ) ),
		'gallery'            => array( 'label' => __( 'Gallery', 'child-cwcwake' ), 'paths' => array( 'gallery' ) ),
		'about'              => array( 'label' => __( 'About', 'child-cwcwake' ), 'paths' => array( 'about' ) ),
		'privacy-policy'     => array( 'label' => __( 'Privacy policy', 'child-cwcwake' ), 'paths' => array( 'privacy-policy' ) ),
		'terms'              => array( 'label' => __( 'Terms & conditions', 'child-cwcwake' ), 'paths' => array( 'terms-and-conditions' ) ),
	);

	$map = array();
	foreach ( $defs as $key => $def ) {
		if ( 'home' === $key ) {
			$map['home'] = array(
				'label' => $def['label'],
				'url'   => home_url( '/' ),
				'hint'  => '',
			);
			continue;
		}
		$url = '';
		foreach ( $def['paths'] as $p ) {
			$url = cwc_chat_resolve_page_url( $p );
			if ( $url ) {
				break;
			}
		}
		if ( 'gallery' === $key && ! $url && post_type_exists( 'cwc_album' ) ) {
			$arch = get_post_type_archive_link( 'cwc_album' );
			if ( $arch ) {
				$url = $arch;
			}
		}
		if ( $url ) {
			$row   = array(
				'label' => $def['label'],
				'url'   => $url,
				'hint'  => '',
			);
			$pid = url_to_postid( $url );
			if ( $pid ) {
				$pobj = get_post( $pid );
				if ( $pobj instanceof WP_Post ) {
					$len = ( 'accommodation' === $pobj->post_type ) ? 360 : 280;
					$row['hint'] = cwc_chat_summarize_post_for_context( $pobj, $len, $key );
				}
			}
			$map[ $key ] = $row;
		}
	}

	return $map;
}

/**
 * Large site index string for the model (cached per request).
 *
 * @return string
 */
function cwc_chat_site_context_block() {
	static $cached = null;
	if ( is_string( $cached ) ) {
		return $cached;
	}

	$lines   = array();
	$lines[] = sprintf(
		/* translators: 1: site name, 2: tagline */
		__( 'Site: %1$s — %2$s', 'child-cwcwake' ),
		get_bloginfo( 'name' ),
		wp_strip_all_tags( get_bloginfo( 'description' ) )
	);
	$lines[] = __( 'Primary destinations (each entry may include a “Details” line from the page):', 'child-cwcwake' );
	foreach ( cwc_chat_canonical_map() as $key => $row ) {
		$line = '- ' . $row['label'] . ': ' . $row['url'];
		if ( ! empty( $row['hint'] ) ) {
			$line .= "\n  " . preg_replace( '/\s+/u', ' ', $row['hint'] );
		}
		$lines[] = $line;
	}

	$lines[] = __( 'Other published pages:', 'child-cwcwake' );
	$pages     = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'numberposts'    => 40,
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
			'no_found_rows'  => true,
		)
	);
	$pi = 0;
	foreach ( $pages as $p ) {
		++$pi;
		$line = '- ' . wp_strip_all_tags( $p->post_title ) . ': ' . get_permalink( $p );
		if ( $pi <= 15 ) {
			$sum = cwc_chat_summarize_post_for_context( $p, 220, 'page' );
			if ( '' !== $sum ) {
				$line .= "\n  " . preg_replace( '/\s+/u', ' ', $sum );
			}
		}
		$lines[] = $line;
	}

	if ( post_type_exists( 'accommodation' ) ) {
		$lines[] = __( 'Accommodation / room types:', 'child-cwcwake' );
		$rooms   = get_posts(
			array(
				'post_type'      => 'accommodation',
				'post_status'    => 'publish',
				'numberposts'    => 30,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'no_found_rows'  => true,
			)
		);
		foreach ( $rooms as $r ) {
			$line = '- ' . wp_strip_all_tags( $r->post_title ) . ': ' . get_permalink( $r );
			$sum  = cwc_chat_summarize_post_for_context( $r, 300, 'accommodation' );
			if ( '' !== $sum ) {
				$line .= "\n  " . preg_replace( '/\s+/u', ' ', $sum );
			}
			$lines[] = $line;
		}
	}

	$block = implode( "\n", $lines );
	$max   = (int) apply_filters( 'cwc_chat_site_context_max_chars', 14000 );
	if ( strlen( $block ) > $max ) {
		$block = substr( $block, 0, $max ) . "\n…";
	}

	$cached = apply_filters( 'cwc_chat_site_context_block', $block );
	return $cached;
}

/**
 * Full system message: instructions + live site index.
 *
 * @return string
 */
function cwc_chat_full_system_content() {
	return cwc_chat_system_prompt() . "\n\n## Site index\n" . cwc_chat_site_context_block();
}

/**
 * Suggestion CTAs derived from the visitor's last message (deterministic).
 *
 * @param string $user_text Last user message.
 * @return array<int, array{label: string, url: string, hint?: string}>
 */
function cwc_chat_suggestions_for_message( $user_text ) {
	$map = cwc_chat_canonical_map();

	$hits = array();
	$seen = array();

	$push = static function ( array $keys ) use ( &$hits, &$seen, $map ) {
		foreach ( $keys as $key ) {
			if ( empty( $map[ $key ]['url'] ) ) {
				continue;
			}
			$url = $map[ $key ]['url'];
			if ( isset( $seen[ $url ] ) ) {
				continue;
			}
			$seen[ $url ] = true;
			$hint = isset( $map[ $key ]['hint'] ) ? (string) $map[ $key ]['hint'] : '';
			if ( '' !== $hint ) {
				$hint = function_exists( 'mb_substr' ) ? mb_substr( $hint, 0, 220 ) : substr( $hint, 0, 220 );
			}
			$hits[]       = array(
				'label' => $map[ $key ]['label'],
				'url'   => $url,
				'hint'  => $hint,
			);
		}
	};

	$rules = array(
		array( 'regex' => '/\b(rate|rates|pricing|price|cost|nightly|package|peso|discount|promo)\b|₱/iu', 'keys' => array( 'rates', 'accommodations' ) ),
		array( 'regex' => '/\b(villa|room|cabin|suite|accommodation|bedroom|overnight|stay|sleep)\b/i', 'keys' => array( 'accommodations', 'rates' ) ),
		array( 'regex' => '/\b(book|booking|reserve|reservation|availability|check-?in|check-?out)\b/i', 'keys' => array( 'book_now', 'rates' ) ),
		array( 'regex' => '/\b(wake|wakeboard|wakeboarding|water\s*sport|kneeboard)\b/i', 'keys' => array( 'water-sports' ) ),
		array( 'regex' => '/\b(land\s*activ|atv|quad|trail\s*bike|hike)\b/i', 'keys' => array( 'land-activities' ) ),
		array( 'regex' => '/\b(gym|spa|elite|facility|facilities)\b/i', 'keys' => array( 'elite-facilities' ) ),
		array( 'regex' => '/\b(photo|gallery|album|picture|image)\b/i', 'keys' => array( 'gallery' ) ),
		array( 'regex' => '/\b(blog|news|article|post)\b/i', 'keys' => array( 'blogs' ) ),
		array( 'regex' => '/\b(faq|faqs|policy|policies|cancel|refund|terms|privacy)\b/i', 'keys' => array( 'faqs', 'terms' ) ),
		array( 'regex' => '/\b(contact|email|phone|call|reach|location|address|direction)\b/i', 'keys' => array( 'contact' ) ),
		array( 'regex' => '/\b(about|who\s*are|team|history)\b/i', 'keys' => array( 'about' ) ),
	);

	foreach ( $rules as $rule ) {
		if ( preg_match( $rule['regex'], $user_text ) ) {
			$push( $rule['keys'] );
		}
	}

	if ( count( $hits ) < 2 ) {
		$push( array( 'accommodations', 'rates', 'contact' ) );
	}

	/**
	 * Filter suggestion chips for the chat widget.
	 *
	 * @param array<int, array{label: string, url: string, hint?: string}> $hits Suggestions.
	 * @param string                                        $user_text User message.
	 */
	$hits = apply_filters( 'cwc_chat_suggestions', $hits, $user_text );

	return array_slice( $hits, 0, 4 );
}

/**
 * Whether the user message mentions the resort, booking, or site-related topics (loose keyword check).
 *
 * @param string $text Plain text.
 * @return bool
 */
function cwc_chat_message_has_resort_context( $text ) {
	$text = mb_strtolower( (string) $text, 'UTF-8' );
	if ( '' === trim( $text ) ) {
		return false;
	}
	$tokens = array(
		'cwc', 'wake park', 'wakeboard', 'wake board', 'watersport', 'water sport', 'water-sport',
		'resort', 'camsur', 'cam sur', 'cam-sur', 'accommodation', 'cabin', 'cabana', 'dwell', 'villa',
		'book', 'booking', 'reserve', 'reservation', 'rate', 'pricing', 'price', 'nightly', 'overnight',
		'guest', 'check in', 'check-in', 'check out', 'check-out', 'peso', 'php', 'philippine', 'bicol',
		'location', 'direction', 'address', 'contact', 'amenit', 'pool', 'spa', 'cable', 'kneeboard',
		'session', 'lesson', 'package', 'policy', 'cancel', 'payment', 'faq', 'wake', 'sport', 'stay',
		'room', 'trip', 'visit', 'site', 'facility', 'elite', 'land activ', 'gallery', 'blog',
	);
	foreach ( $tokens as $tok ) {
		if ( false !== strpos( $text, $tok ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Obvious homework / calculator-style prompts without resort context.
 *
 * @param string $text Plain text.
 * @return bool
 */
function cwc_chat_message_looks_like_arithmetic_or_solve_request( $text ) {
	$text = trim( preg_replace( '/\s+/u', ' ', (string) $text ) );
	if ( '' === $text ) {
		return false;
	}
	if ( strlen( $text ) > 220 ) {
		return false;
	}
	if ( preg_match( '/\b\d{1,7}\s+divided\s+by\s+\d{1,7}\b/iu', $text ) ) {
		return true;
	}
	if ( preg_match( '/\b\d{1,7}\s+(?:times|multiplied\s+by)\s+\d{1,7}\b/iu', $text ) ) {
		return true;
	}
	if ( preg_match( '/\b\d{1,7}\s+(?:plus|minus)\s+\d{1,7}\b/iu', $text ) ) {
		return true;
	}
	if ( preg_match( '/\b\d{1,7}\s*[\+\-\*\/×÷]\s*\d{1,7}\b/u', $text ) ) {
		return true;
	}
	if ( preg_match( '/^(?:what\'s|what\s+is|whats)\s+\d{1,7}\s*[\+\-\*\/×÷]\s*\d{1,7}/iu', $text ) ) {
		return true;
	}
	if ( preg_match( '/^(?:calc|calculate|compute|solve)\b/iu', $text ) ) {
		return true;
	}
	if ( strlen( $text ) <= 14 && preg_match( '/^\d{1,5}\s*[\+\-\*\/×÷]\s*\d{1,5}$/', $text ) ) {
		return true;
	}
	return false;
}

/**
 * Skip the model for clearly off-topic messages (extend via filter).
 *
 * @param string $text Last user message.
 * @return bool
 */
function cwc_chat_should_refuse_off_topic( $text ) {
	$text = trim( (string) $text );
	if ( '' === $text ) {
		return false;
	}
	if ( cwc_chat_message_has_resort_context( $text ) ) {
		return false;
	}
	if ( cwc_chat_message_looks_like_arithmetic_or_solve_request( $text ) ) {
		return true;
	}
	/**
	 * Return true to refuse without calling Groq (custom moderation).
	 *
	 * @param bool   $refuse Default false after built-in checks.
	 * @param string $text   Last user message.
	 */
	return (bool) apply_filters( 'cwc_chat_refuse_message_off_topic', false, $text );
}

/**
 * Short reply when the message is outside assistant scope.
 *
 * @return string
 */
function cwc_chat_off_topic_reply_text() {
	return __( 'I can only help with CWC Wake—stays, water sports, activities, rates, and booking. Ask me something about the resort, or use **Contact** on the site for other questions.', 'child-cwcwake' );
}

/**
 * Rate limit check (transient per IP).
 *
 * @return true|WP_Error
 */
function cwc_chat_rate_limit_check() {
	$max     = (int) apply_filters( 'cwc_chat_rate_limit_max', 40 );
	$window  = (int) apply_filters( 'cwc_chat_rate_limit_window', 15 * MINUTE_IN_SECONDS );
	$max     = max( 1, min( 200, $max ) );
	$window  = max( 60, min( HOUR_IN_SECONDS, $window ) );

	$key = 'cwc_chat_rl_' . md5( cwc_chat_client_ip() );
	$n   = (int) get_transient( $key );

	if ( $n >= $max ) {
		return new WP_Error(
			'cwc_chat_rate_limited',
			__( 'Too many requests. Please try again in a few minutes.', 'child-cwcwake' ),
			array( 'status' => 429 )
		);
	}

	set_transient( $key, $n + 1, $window );

	return true;
}

/**
 * Normalize and validate messages from the client JSON body.
 *
 * @param mixed $raw Raw messages value.
 * @return array|WP_Error
 */
function cwc_chat_sanitize_messages( $raw ) {
	if ( ! is_array( $raw ) ) {
		return new WP_Error(
			'cwc_chat_bad_messages',
			__( 'Invalid message payload.', 'child-cwcwake' ),
			array( 'status' => 400 )
		);
	}

	$max_items = (int) apply_filters( 'cwc_chat_max_messages', 20 );
	$max_items = max( 2, min( 40, $max_items ) );
	if ( count( $raw ) > $max_items ) {
		$raw = array_slice( $raw, -$max_items );
	}

	$out = array();
	foreach ( $raw as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$role = isset( $row['role'] ) ? sanitize_key( $row['role'] ) : '';
		if ( ! in_array( $role, array( 'user', 'assistant' ), true ) ) {
			continue;
		}
		$content = isset( $row['content'] ) ? $row['content'] : '';
		if ( ! is_string( $content ) ) {
			continue;
		}
		$content = wp_strip_all_tags( $content );
		$content = trim( $content );
		if ( '' === $content ) {
			continue;
		}
		if ( strlen( $content ) > 4000 ) {
			$content = substr( $content, 0, 4000 );
		}
		$out[] = array(
			'role'    => $role,
			'content' => $content,
		);
	}

	if ( count( $out ) < 1 ) {
		return new WP_Error(
			'cwc_chat_empty',
			__( 'Please enter a message.', 'child-cwcwake' ),
			array( 'status' => 400 )
		);
	}

	return $out;
}

/**
 * REST: POST chat completion via Groq (OpenAI-compatible).
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function cwc_rest_chat_completion( WP_REST_Request $request ) {
	if ( ! defined( 'CWC_GROQ_API_KEY' ) || ! CWC_GROQ_API_KEY ) {
		return new WP_Error(
			'cwc_chat_unconfigured',
			__( 'Assistant is temporarily unavailable.', 'child-cwcwake' ),
			array( 'status' => 503 )
		);
	}

	$params   = $request->get_json_params();
	$messages = isset( $params['messages'] ) ? $params['messages'] : null;
	$messages = cwc_chat_sanitize_messages( $messages );
	if ( is_wp_error( $messages ) ) {
		return $messages;
	}

	$rl = cwc_chat_rate_limit_check();
	if ( is_wp_error( $rl ) ) {
		return $rl;
	}

	$last_user = '';
	for ( $i = count( $messages ) - 1; $i >= 0; $i-- ) {
		if ( isset( $messages[ $i ]['role'] ) && 'user' === $messages[ $i ]['role'] && ! empty( $messages[ $i ]['content'] ) ) {
			$last_user = (string) $messages[ $i ]['content'];
			break;
		}
	}

	/**
	 * Whether to skip Groq for off-topic messages (math/homework etc. without resort context).
	 *
	 * @param bool   $enabled   Default true.
	 * @param string $last_user Last user message.
	 */
	if ( apply_filters( 'cwc_chat_block_off_topic', true, $last_user ) && '' !== $last_user && cwc_chat_should_refuse_off_topic( $last_user ) ) {
		return rest_ensure_response(
			array(
				'reply'       => cwc_chat_off_topic_reply_text(),
				'suggestions' => cwc_chat_suggestions_for_message( $last_user ),
			)
		);
	}

	$model = defined( 'CWC_GROQ_CHAT_MODEL' ) && CWC_GROQ_CHAT_MODEL ? CWC_GROQ_CHAT_MODEL : 'llama-3.3-70b-versatile';
	/**
	 * Filter Groq chat model id.
	 *
	 * @param string $model Model id.
	 */
	$model = apply_filters( 'cwc_groq_chat_model', $model );

	$body_messages = array_merge(
		array(
			array(
				'role'    => 'system',
				'content' => cwc_chat_full_system_content(),
			),
		),
		$messages
	);

	$api_body = array(
		'model'       => $model,
		'messages'    => $body_messages,
		'temperature' => 0.6,
		'max_tokens'  => 900,
	);

	$response = wp_remote_post(
		'https://api.groq.com/openai/v1/chat/completions',
		array(
			'timeout' => 45,
			'headers' => array(
				'Authorization' => 'Bearer ' . CWC_GROQ_API_KEY,
				'Content-Type'  => 'application/json',
			),
			'body'    => wp_json_encode( $api_body ),
		)
	);

	if ( is_wp_error( $response ) ) {
		return new WP_Error(
			'cwc_chat_upstream',
			__( 'Assistant is temporarily unavailable.', 'child-cwcwake' ),
			array( 'status' => 502 )
		);
	}

	$code = (int) wp_remote_retrieve_response_code( $response );
	$raw  = wp_remote_retrieve_body( $response );
	$data = json_decode( $raw, true );

	if ( $code < 200 || $code >= 300 || ! is_array( $data ) ) {
		return new WP_Error(
			'cwc_chat_upstream',
			__( 'Assistant is temporarily unavailable.', 'child-cwcwake' ),
			array( 'status' => 502 )
		);
	}

	$text = '';
	if ( ! empty( $data['choices'][0]['message']['content'] ) && is_string( $data['choices'][0]['message']['content'] ) ) {
		$text = $data['choices'][0]['message']['content'];
	}

	if ( '' === trim( $text ) ) {
		return new WP_Error(
			'cwc_chat_empty_reply',
			__( 'No response from assistant. Please try again.', 'child-cwcwake' ),
			array( 'status' => 502 )
		);
	}

	$suggestions = cwc_chat_suggestions_for_message( $last_user );

	return rest_ensure_response(
		array(
			'reply'        => $text,
			'suggestions'  => $suggestions,
		)
	);
}

/**
 * REST permission: valid wp_rest nonce (works for anonymous same-site requests).
 *
 * @return bool
 */
function cwc_rest_chat_permission() {
	$nonce = isset( $_SERVER['HTTP_X_WP_NONCE'] )
		? sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_WP_NONCE'] ) )
		: '';

	return (bool) wp_verify_nonce( $nonce, 'wp_rest' );
}

/**
 * Register REST route.
 */
function cwc_register_chat_rest_route() {
	register_rest_route(
		'cwc/v1',
		'/chat',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'cwc_rest_chat_completion',
			'permission_callback' => 'cwc_rest_chat_permission',
		)
	);
}
add_action( 'rest_api_init', 'cwc_register_chat_rest_route' );

/**
 * Preset “quick question” chips above the composer (filterable).
 *
 * @return array<int, array{label: string, text: string}>
 */
function cwc_chat_default_starter_prompts() {
	return array(
		array(
			'label' => __( 'Rates', 'child-cwcwake' ),
			'text'  => __( 'What are your current rates for rooms and villas?', 'child-cwcwake' ),
		),
		array(
			'label' => __( 'Book a stay', 'child-cwcwake' ),
			'text'  => __( 'How do I book a room and what is the check-in process?', 'child-cwcwake' ),
		),
		array(
			'label' => __( 'Wakeboarding', 'child-cwcwake' ),
			'text'  => __( 'What water sports and wakeboarding sessions do you offer?', 'child-cwcwake' ),
		),
		array(
			'label' => __( 'Rooms', 'child-cwcwake' ),
			'text'  => __( 'What types of accommodations and villas do you have?', 'child-cwcwake' ),
		),
		array(
			'label' => __( 'Location', 'child-cwcwake' ),
			'text'  => __( 'Where is CWC Wake located and how do I get there?', 'child-cwcwake' ),
		),
		array(
			'label' => __( 'Policies', 'child-cwcwake' ),
			'text'  => __( 'What are your cancellation and payment policies?', 'child-cwcwake' ),
		),
	);
}

/**
 * Enqueue chat widget assets.
 */
function cwc_enqueue_chat_assistant() {
	if ( ! cwc_chat_should_load_widget() ) {
		return;
	}

	wp_enqueue_style(
		'cwc-chat-assistant',
		get_stylesheet_directory_uri() . '/assets/css/chat-assistant.css',
		array(),
		CWC_VERSION
	);

	wp_enqueue_script(
		'cwc-chat-assistant',
		get_stylesheet_directory_uri() . '/assets/js/chat-assistant.js',
		array(),
		CWC_VERSION,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	$contact_url = home_url( '/' );
	foreach ( array( 'contact-us', 'contact' ) as $slug ) {
		$ids = get_posts(
			array(
				'name'           => $slug,
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);
		if ( ! empty( $ids ) ) {
			$contact_url = get_permalink( (int) $ids[0] );
			break;
		}
	}

	$chat_logo_url = '';
	$custom_logo_id = (int) get_theme_mod( 'custom_logo' );
	if ( $custom_logo_id ) {
		$logo_src = wp_get_attachment_image_url( $custom_logo_id, 'medium' );
		if ( ! $logo_src ) {
			$logo_src = wp_get_attachment_image_url( $custom_logo_id, 'thumbnail' );
		}
		if ( ! $logo_src ) {
			$logo_src = wp_get_attachment_image_url( $custom_logo_id, 'full' );
		}
		if ( $logo_src ) {
			$chat_logo_url = $logo_src;
		}
	}
	if ( ! $chat_logo_url ) {
		$chat_logo_url = get_stylesheet_directory_uri() . '/assets/images/cwc-header-logo.svg';
	}
	/**
	 * URL for the chat panel header brand image (Custom Logo or child theme fallback).
	 *
	 * @param string $chat_logo_url Default logo URL.
	 */
	$chat_logo_url = apply_filters( 'cwc_chat_logo_url', $chat_logo_url );

	wp_localize_script(
		'cwc-chat-assistant',
		'cwcChat',
		array(
			'restUrl'         => esc_url_raw( rest_url( 'cwc/v1/chat' ) ),
			'nonce'           => wp_create_nonce( 'wp_rest' ),
			'homeUrl'         => esc_url_raw( home_url( '/' ) ),
			'contactUrl'      => esc_url_raw( $contact_url ),
			'logoUrl'         => esc_url( $chat_logo_url ),
			'starterPrompts'  => apply_filters( 'cwc_chat_starter_prompts', cwc_chat_default_starter_prompts() ),
			'i18n'            => array(
				'open'              => __( 'Open assistant', 'child-cwcwake' ),
				'close'             => __( 'Close', 'child-cwcwake' ),
				'title'             => __( 'CWC Wake AI Assistant', 'child-cwcwake' ),
				'placeholder'       => __( 'Ask about the resort…', 'child-cwcwake' ),
				'send'              => __( 'Send', 'child-cwcwake' ),
				'thinking'          => __( 'Thinking…', 'child-cwcwake' ),
				'error'             => __( 'Something went wrong. Please try again or use our contact page.', 'child-cwcwake' ),
				'contactCta'        => __( 'Contact us', 'child-cwcwake' ),
				'empty'             => __( 'Please type a message first.', 'child-cwcwake' ),
				'suggestionsLabel'  => __( 'Suggested pages', 'child-cwcwake' ),
				'startersLabel'     => __( 'Quick questions', 'child-cwcwake' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'cwc_enqueue_chat_assistant', 25 );

/**
 * Mount node for the chat widget (JS builds UI).
 */
function cwc_chat_assistant_mount() {
	if ( ! wp_script_is( 'cwc-chat-assistant', 'enqueued' ) ) {
		return;
	}
	echo '<div id="cwc-chat-root" class="cwc-chat-root" hidden></div>';
}
add_action( 'wp_footer', 'cwc_chat_assistant_mount', 50 );
