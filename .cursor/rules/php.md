---
description: PHP coding standards for WordPress development
globs: ["**/*.php"]
---

# PHP Coding Standards

## Formatting

- Indentation: tabs (not spaces)
- Opening brace on same line as statement
- Line length: aim for 120 chars, hard wrap at 150
- Trailing commas in multi-line arrays and parameter lists

## Naming

- Functions: `cwc_snake_case()` (always prefixed)
- Classes: `CWC_Upper_Snake_Case`
- Methods: `$obj->snake_case()`
- Constants: `CWC_UPPER_SNAKE`
- Variables: `$snake_case`
- Hooks (actions): `cwc_verb_noun` (e.g., `cwc_after_user_save`)
- Hooks (filters): `cwc_noun_context` (e.g., `cwc_post_title_display`)

## Type Declarations

Use type hints and return types on all functions (PHP 7.4+):

```php
function cwc_calculate_total( float $price, int $quantity ): float {
	return $price * $quantity;
}
```

## PHPDoc

Document all functions, classes, and methods with `@since`, `@param`, `@return`.

## Security (MANDATORY)

- **Escape all output**: `esc_html()`, `esc_attr()`, `esc_url()`, `esc_js()`, `esc_textarea()`, `wp_kses_post()`
- **Sanitize all input**: `sanitize_text_field()`, `sanitize_email()`, `absint()`, etc.
- **Nonces on all forms and AJAX**: `wp_nonce_field()` + `wp_verify_nonce()`
- **Capability checks**: `current_user_can()` before any privileged action
- **Prepared queries**: always use `$wpdb->prepare()` with user input

## WordPress Patterns

- Enqueue assets with `wp_enqueue_style()` / `wp_enqueue_script()` — never hardcode tags
- Use `wp_enqueue_scripts` for frontend, `admin_enqueue_scripts` for admin
- Load scripts in footer: `wp_enqueue_script(..., true)`
- Use `get_template_part()` for reusable components, not `include`/`require`
- Register theme supports in `after_setup_theme` hook
- Prefer `WP_Query` / `get_posts()` over raw SQL
