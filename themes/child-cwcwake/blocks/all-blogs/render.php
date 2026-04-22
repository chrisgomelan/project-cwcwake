<?php
/**
 * All Blogs block — server render template.
 *
 * Renders the paginated three-column blog grid + category filter
 * documented in `designs/blogs-design.md` § 3.
 *
 * Pagination strategy:
 *
 *   - Reads the page number from the `blog_page` query var (NOT
 *     `paged`, which conflicts with WP's main-loop pagination on
 *     a static page template).
 *   - Builds links via `add_query_arg()` so the URL stays
 *     bookmarkable, history-navigable, and accessible without JS.
 *
 * Category filter:
 *
 *   - Submit-on-change `<select>` that posts back to the same page
 *     with `?blog_cat=<slug>`. Empty value = "All Blogs".
 *   - All filter changes reset pagination to page 1 — surfacing
 *     page 4 of "Events" when only 2 pages of "Events" exist would
 *     leave the user staring at an empty grid.
 *
 * @package CWC_Wake
 * @since   1.0.0
 *
 * @var array $attributes Block attributes.
 */

if (!defined('ABSPATH')) {
	exit;
}

/*
 * Helper declarations live above the markup so they're available
 * on the very first render. Wrapping in `function_exists()` keeps
 * us safe against double-include — but the guard prevents PHP's
 * normal top-of-file function hoisting, which is why these cannot
 * live at the bottom of the file.
 */
if (!function_exists('cwc_all_blogs_build_query')):
	/**
	 * Build the WP_Query for the All Blogs grid.
	 *
	 * Centralized so the per-page query and the pagination math
	 * (`max_num_pages`) come from the same place — drift between
	 * the two is the most common bug in custom paginators.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $per_page     Posts per page.
	 * @param int    $current_page Current 1-indexed page number.
	 * @param string $category     Optional category slug to filter by. Empty = no filter.
	 * @return WP_Query Built query (already executed).
	 */
	function cwc_all_blogs_build_query(int $per_page, int $current_page, string $category = ''): WP_Query
	{
		$args = [
			'post_type' => 'post',
			'post_status' => 'publish',
			'posts_per_page' => $per_page,
			'paged' => $current_page,
			'orderby' => 'date',
			'order' => 'DESC',
			'ignore_sticky_posts' => true,
			'update_post_term_cache' => false,
		];

		if ('' !== $category) {
			$args['category_name'] = $category;
		}

		return new WP_Query($args);
	}
endif;

if (!function_exists('cwc_render_blog_pagination')):
	/**
	 * Render numeric pagination controls for the All Blogs grid.
	 *
	 * Mirrors the design's `‹ 1 2 3 4 ... ›` pattern. Implemented
	 * as raw HTML rather than `paginate_links()` so we control the
	 * exact markup (BEM classes, `aria-current`, etc.) — the
	 * WordPress helper emits classes we don't want to override
	 * globally.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $total_pages  Total number of pages in the query.
	 * @param int    $current_page Currently active page (1-indexed).
	 * @param string $category     Active category slug, preserved across page links.
	 * @return string Pagination markup (empty string when only one page).
	 */
	function cwc_render_blog_pagination(int $total_pages, int $current_page, string $category = ''): string
	{
		if ($total_pages <= 1) {
			return '';
		}

		$base_url = remove_query_arg(['blog_page', 'blog_cat']);

		$build_url = static function (int $page) use ($base_url, $category): string {
			$args = ['blog_page' => $page];
			if ('' !== $category) {
				$args['blog_cat'] = $category;
			}
			return add_query_arg($args, $base_url);
		};

		/*
		 * Window of page numbers around the current page so the
		 * control stays compact on long archives. We always show
		 * first + last, with `…` separators when there's a gap.
		 */
		$window = 1;
		$pages = [];
		$pages[] = 1;
		for ($i = max(2, $current_page - $window); $i <= min($total_pages - 1, $current_page + $window); $i++) {
			$pages[] = $i;
		}
		if ($total_pages > 1) {
			$pages[] = $total_pages;
		}
		$pages = array_values(array_unique($pages));

		ob_start();
		?>
		<nav class="cwc-all-blogs__pagination" aria-label="<?php esc_attr_e('Blog pagination', 'child-cwcwake'); ?>">
			<?php if ($current_page > 1): ?>
				<a class="cwc-all-blogs__page cwc-all-blogs__page--prev"
					href="<?php echo esc_url($build_url($current_page - 1)); ?>"
					aria-label="<?php esc_attr_e('Previous page', 'child-cwcwake'); ?>">‹</a>
			<?php else: ?>
				<span class="cwc-all-blogs__page cwc-all-blogs__page--prev cwc-all-blogs__page--disabled"
					aria-hidden="true">‹</span>
			<?php endif; ?>

			<?php
			$prev_page = 0;
			foreach ($pages as $page):
				if ($prev_page > 0 && ($page - $prev_page) > 1):
					?>
					<span class="cwc-all-blogs__page cwc-all-blogs__page--ellipsis" aria-hidden="true">…</span>
					<?php
				endif;

				if ($page === $current_page):
					?>
					<span class="cwc-all-blogs__page cwc-all-blogs__page--current" aria-current="page">
						<?php echo esc_html((string) $page); ?>
					</span>
					<?php
				else:
					?>
					<a class="cwc-all-blogs__page" href="<?php echo esc_url($build_url($page)); ?>">
						<?php echo esc_html((string) $page); ?>
					</a>
					<?php
				endif;

				$prev_page = $page;
			endforeach;
			?>

			<?php if ($current_page < $total_pages): ?>
				<a class="cwc-all-blogs__page cwc-all-blogs__page--next"
					href="<?php echo esc_url($build_url($current_page + 1)); ?>"
					aria-label="<?php esc_attr_e('Next page', 'child-cwcwake'); ?>">›</a>
			<?php else: ?>
				<span class="cwc-all-blogs__page cwc-all-blogs__page--next cwc-all-blogs__page--disabled"
					aria-hidden="true">›</span>
			<?php endif; ?>
		</nav>
		<?php
		return (string) ob_get_clean();
	}
endif;

$heading = isset($attributes['heading']) ? (string) $attributes['heading'] : '';
$heading_highlight = isset($attributes['headingHighlight']) ? (string) $attributes['headingHighlight'] : '';
$description = isset($attributes['description']) ? (string) $attributes['description'] : '';
$per_page = isset($attributes['perPage']) ? max(1, (int) $attributes['perPage']) : 6;
$read_more_label = isset($attributes['readMoreLabel']) ? (string) $attributes['readMoreLabel'] : __('Read More', 'child-cwcwake');
$placeholder = isset($attributes['placeholderImage']) ? (string) $attributes['placeholderImage'] : '';

$current_page = isset($_GET['blog_page']) ? max(1, absint(wp_unslash($_GET['blog_page']))) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$active_cat = isset($_GET['blog_cat']) ? sanitize_title(wp_unslash($_GET['blog_cat'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

$query = cwc_all_blogs_build_query($per_page, $current_page, $active_cat);
$posts = $query->posts;

$wrapper_attrs = get_block_wrapper_attributes(['class' => 'cwc-all-blogs']);

$base_url = remove_query_arg(['blog_page', 'blog_cat']);
$categories = get_categories(['hide_empty' => true]);
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<header class="cwc-all-blogs__header">
		<div class="cwc-all-blogs__intro">
			<?php if ('' !== $heading || '' !== $heading_highlight): ?>
				<h2 class="cwc-all-blogs__title">
					<?php if ('' !== $heading): ?>
						<span class="cwc-all-blogs__title-text"><?php echo esc_html($heading); ?></span>
					<?php endif; ?>
					<?php if ('' !== $heading_highlight): ?>
						<em class="cwc-all-blogs__title-highlight"><?php echo esc_html($heading_highlight); ?></em>
					<?php endif; ?>
				</h2>
			<?php endif; ?>

			<?php if ('' !== $description): ?>
				<p class="cwc-all-blogs__description"><?php echo esc_html($description); ?></p>
			<?php endif; ?>
		</div>

		<div class="cwc-all-blogs__filter js-all-blogs-filter">
			<button type="button" class="cwc-all-blogs__filter-trigger js-filter-trigger" aria-haspopup="listbox" aria-expanded="false">
				<span class="js-filter-current"><?php echo $active_cat ? esc_html( get_category_by_slug( $active_cat )->name ) : esc_html__( 'All Blogs', 'child-cwcwake' ); ?></span>
				<svg class="cwc-all-blogs__filter-chevron" width="12" height="8" viewBox="0 0 12 8" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M1 1L6 6L11 1" stroke="#1A1A1A" stroke-width="2" stroke-linecap="round"/>
				</svg>
			</button>

			<div class="cwc-all-blogs__filter-dropdown js-filter-dropdown" role="listbox">
				<button type="button" class="cwc-all-blogs__filter-option <?php echo ! $active_cat ? 'is-active' : ''; ?>" data-value="">
					<?php esc_html_e( 'All Blogs', 'child-cwcwake' ); ?>
					<span class="cwc-all-blogs__filter-dot"></span>
				</button>
				<?php foreach ( $categories as $cat ) : ?>
					<button type="button" class="cwc-all-blogs__filter-option <?php echo $active_cat === $cat->slug ? 'is-active' : ''; ?>" 
						data-value="<?php echo esc_attr( $cat->slug ); ?>">
						<?php echo esc_html( $cat->name ); ?>
						<span class="cwc-all-blogs__filter-dot"></span>
					</button>
				<?php endforeach; ?>
			</div>

			<select name="blog_cat" class="cwc-all-blogs__filter-native js-filter-native" style="display:none;">
				<option value="" <?php selected( '', $active_cat ); ?>><?php esc_html_e( 'All Blogs', 'child-cwcwake' ); ?></option>
				<?php foreach ( $categories as $cat ) : ?>
					<option value="<?php echo esc_attr( $cat->slug ); ?>" <?php selected( $cat->slug, $active_cat ); ?>>
						<?php echo esc_html( $cat->name ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
			<div class="cwc-all-blogs__content js-all-blogs-target">
				<?php if (empty($posts)): ?>
					<p class="cwc-all-blogs__empty">
						<?php esc_html_e('No blog posts found for this filter.', 'child-cwcwake'); ?></p>
				<?php else: ?>
					<div class="cwc-all-blogs__grid">
						<?php
						foreach ($posts as $post_obj):
							$post_id = (int) $post_obj->ID;
							$image = cwc_blog_card_image_url($post_id, $placeholder);
							$title = get_the_title($post_id);
							$excerpt = cwc_blog_card_excerpt($post_obj, 24);
							$date = get_the_date('M j, Y', $post_id);
							$url = (string) get_permalink($post_id);
							?>
							<article class="cwc-all-blogs__card">
								<a class="cwc-all-blogs__card-image-link" href="<?php echo esc_url($url); ?>"
									aria-label="<?php echo esc_attr($title); ?>">
									<?php if ('' !== $image): ?>
										<span class="cwc-all-blogs__card-image" role="img"
											aria-label="<?php echo esc_attr($title); ?>"
											style="background-image:url('<?php echo esc_url($image); ?>');"></span>
									<?php endif; ?>
								</a>

								<div class="cwc-all-blogs__card-body">
									<h3 class="cwc-all-blogs__card-title">
										<a href="<?php echo esc_url($url); ?>"><?php echo esc_html($title); ?></a>
									</h3>

									<?php if ('' !== $excerpt): ?>
										<p class="cwc-all-blogs__card-excerpt"><?php echo esc_html($excerpt); ?></p>
									<?php endif; ?>

									<?php if ('' !== $date): ?>
										<time class="cwc-all-blogs__card-date"
											datetime="<?php echo esc_attr(get_the_date(DATE_W3C, $post_id)); ?>">
											<?php echo esc_html($date); ?>
										</time>
									<?php endif; ?>

									<a class="cwc-all-blogs__card-cta" href="<?php echo esc_url($url); ?>">
										<?php echo esc_html($read_more_label); ?>
									</a>
								</div>
							</article>
							<?php
						endforeach;
						?>
					</div>

					<?php
					echo cwc_render_blog_pagination( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						(int) $query->max_num_pages,
						$current_page,
						$active_cat
					);
					?>
				<?php endif; ?>
			</div>
</section>