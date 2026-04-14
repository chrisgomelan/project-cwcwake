---
description: WordPress block theme structure, design tokens, and conventions
globs: ["**/themes/**"]
---

# Theme Development Rules

## Block Theme Structure

This project uses a **block theme** (not classic). Templates are HTML, design tokens live in `theme.json`.

```
themes/child-cwcwake/
├── style.css              # Metadata only (no styling)
├── functions.php          # Bootstrap: enqueue, supports, page setup
├── theme.json             # Design tokens: colors, fonts, spacing, layout
├── assets/
│   ├── css/
│   │   └── global.css     # Component styles (only var() references)
│   ├── js/
│   └── images/
├── templates/             # Full-page block templates (HTML)
│   ├── front-page.html
│   ├── page.html
│   └── page-{slug}.html   # Custom page templates
├── parts/                 # Reusable template parts (HTML)
│   ├── header.html
│   ├── footer.html
│   ├── hero.html
│   └── page-header.html
├── blocks/                # Custom Gutenberg blocks
│   └── block-name/
│       ├── block.json
│       ├── render.php
│       ├── style.css
│       └── view.js
├── inc/                   # PHP includes
├── acf-json/
└── languages/
```

## Design Token Rules

- **All colors, fonts, sizes, spacing** are defined in `theme.json`
- **`global.css` must never contain hardcoded values** — use `var(--wp--preset--color--slug)`, `var(--wp--preset--font-size--slug)`, etc.
- Changing a token in `theme.json` must propagate everywhere automatically
- Custom values go in `settings.custom` → accessed as `var(--wp--custom--key--subkey)`

## Enqueue Chain

Load order: Google Fonts → Parent CSS → `global.css` → `style.css`

```php
// Each depends on the previous
wp_enqueue_style( 'cwc-google-fonts', '...', [], null );
wp_enqueue_style( 'parent-style', '...', [ 'cwc-google-fonts' ], '...' );
wp_enqueue_style( 'cwc-global', '...global.css', [ 'parent-style' ], CWC_VERSION );
wp_enqueue_style( 'cwc-style', get_stylesheet_uri(), [ 'cwc-global' ], CWC_VERSION );
```

Sync editor styles with `add_editor_style()` for Google Fonts + `global.css`.

## Templates & Parts

- Use `<!-- wp:template-part {"slug":"header"} /-->` (not `get_template_part()`)
- Register custom templates in `theme.json` → `customTemplates`
- Register parts in `theme.json` → `templateParts` with correct `area`
- Page-specific templates: `page-{slug}.html` in `templates/`

## Conventions

- Always use a child theme — never modify the parent theme
- Prefix all functions with `cwc_`
- Text domain: `child-cwcwake`
- Keep `functions.php` lean — include complex logic from `inc/`
- Guard one-time operations (page creation) with `get_option()` checks

## Theme Supports

Register in `after_setup_theme`: title-tag, post-thumbnails, html5, editor-styles, wp-block-styles, responsive-embeds.

## Database Conventions

- Custom tables: `{$wpdb->prefix}cwc_tablename`
- Option names: `cwc_option_name`
- Post meta: `_cwc_meta_key` (underscore prefix = private)
- Transients: `cwc_transient_name`
