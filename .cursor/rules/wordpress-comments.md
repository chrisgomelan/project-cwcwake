---
description: WordPress Core inline documentation standards for PHP and JavaScript
globs: ["**/*.php", "**/*.js"]
---

# WordPress Inline Documentation Standards

All PHP and JS files must follow the [WordPress Core inline documentation standards](https://developer.wordpress.org/coding-standards/inline-documentation-standards/). Comments are mandatory — undocumented functions, classes, hooks, and files are not acceptable.

## 1. PHP File Headers

Every PHP file must start with a file-level docblock immediately after the opening `<?php` tag:

```php
<?php
/**
 * Short description of what the file does.
 *
 * Longer description if useful (optional).
 *
 * @package CWC_Wake
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
```

## 2. PHP Function Docblocks

Every function (including `render.php` helpers) must have a docblock:

```php
/**
 * Short summary in imperative mood (one line, ends with period).
 *
 * Optional longer description spanning one or more
 * paragraphs, separated by a blank `*` line.
 *
 * @since 1.0.0
 *
 * @param string $title    The page title to display.
 * @param array  $crumbs   Optional. List of breadcrumb items. Default empty array.
 * @param bool   $is_compact Whether to render the compact variant.
 * @return string Rendered HTML markup.
 */
function cwc_render_banner( string $title, array $crumbs = [], bool $is_compact = false ): string {
	// ...
}
```

Rules:

- Short description on one line, ends with a period.
- One blank `*` line between sections.
- `@param <type> $name Description.` — type before name, description ends with a period. Align names and descriptions in a single block when practical.
- `@return <type> Description.` — omit only for `void` functions where a return tag adds no value.
- `@since` is mandatory on every function and class.

## 3. PHP Class Docblocks

```php
/**
 * Builds breadcrumb trails for the current request.
 *
 * @since 1.0.0
 */
class CWC_Breadcrumbs {

	/**
	 * Cached crumb list for the current page.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $crumbs = [];
}
```

## 4. PHP Hook Documentation

Every `do_action()` and `apply_filters()` call must be preceded by a docblock describing the hook:

```php
/**
 * Filters the breadcrumb items before rendering.
 *
 * @since 1.0.0
 *
 * @param array $crumbs List of crumb arrays with `label` and `url` keys.
 * @param int   $post_id The current post ID.
 */
$crumbs = apply_filters( 'cwc_breadcrumbs_items', $crumbs, get_the_ID() );
```

## 5. PHP Inline Comments

- Use `//` for single-line inline comments.
- Use `/* ... */` for multi-line comments inside function bodies.
- Always add a `/* translators: ... */` comment immediately above any `__()`, `_e()`, `_x()`, etc. that contains placeholders:

```php
/* translators: %s: Current page title. */
$label = sprintf( __( 'You are on %s.', 'child-cwcwake' ), $title );
```

## 6. JavaScript File Headers

Every JS file must start with a JSDoc file header:

```javascript
/**
 * CWC Wake — Page Banner view script.
 *
 * Handles parallax scroll effect on banner background images.
 *
 * @since 1.0.0
 */
```

## 7. JavaScript Function Docblocks

Every function and method gets a JSDoc block:

```javascript
/**
 * Toggles playback state on a background video element.
 *
 * @since 1.0.0
 *
 * @param {HTMLVideoElement} video  The video element to toggle.
 * @param {HTMLButtonElement} button The toggle button (state stored on `data-playing`).
 * @return {void}
 */
function togglePlayback( video, button ) {
	// ...
}
```

Use `{Type}` (capitalized) for types, `{Type|null}` for nullable, `{Type[]}` for arrays of a type.

## 8. Comments That Are NOT Allowed

Do **not** write comments that simply restate the code:

```php
// BAD — narrates the obvious
$count++; // increment counter
return $value; // return the value

// BAD — comments that explain the diff/change
// Added new check for empty title
if ( empty( $title ) ) {
	return '';
}
```

Comments must explain **intent, trade-offs, edge cases, or non-obvious WordPress behavior** — never just paraphrase the next line.

## 9. Section Banners (Optional)

For long files, group related code with section banner comments:

```php
/* ---------------------------------------------------------
 * Asset Enqueuing
 * --------------------------------------------------------- */
```
