---
description: WordPress child theme structure and conventions
globs: ["**/themes/**"]
---

# Theme Development Rules

## Child Theme Structure

```
themes/cwcwake-child/
├── style.css
├── functions.php
├── assets/{css,js,images,fonts}/
├── template-parts/
├── blocks/
├── inc/              # setup.php, enqueue.php, custom-post-types.php, helpers.php
├── acf-json/
└── languages/
```

## Conventions

- Always use a child theme — never modify the parent theme
- Prefix all functions with `cwc_`
- Text domain must match theme slug
- Use `get_template_part()` for reusable template components
- Put business logic in `inc/` files, included from `functions.php`
- Keep `functions.php` as a bootstrap file — it should mostly `require_once` includes

## Asset Enqueuing

- Use `wp_enqueue_style()` and `wp_enqueue_script()` on the `wp_enqueue_scripts` hook
- Use `get_stylesheet_directory_uri()` for child theme assets
- Set version to `wp_get_theme()->get('Version')`
- Load scripts in footer (`in_footer: true`)
- Conditionally load assets only on pages that need them

## Theme Supports

Register in `after_setup_theme`: title-tag, post-thumbnails, html5, editor-styles, wp-block-styles.

## Database Conventions

- Custom tables: `{$wpdb->prefix}cwc_tablename`
- Option names: `cwc_option_name`
- Post meta: `_cwc_meta_key` (underscore prefix = private)
- Transients: `cwc_transient_name`
