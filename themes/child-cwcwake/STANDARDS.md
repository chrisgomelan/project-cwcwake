# CWC Wake — Coding Standards

This document defines the conventions every contributor must follow when working on the CWC Wake theme.

## 1. Design Tokens First

All visual values must reference `theme.json` tokens via CSS custom properties. Never hardcode values that already exist as tokens.

```css
/* CORRECT */
color: var(--wp--preset--color--primary);
font-family: var(--wp--preset--font-family--heading);
font-size: var(--wp--preset--font-size--huge);
padding: var(--wp--preset--spacing--40);
border-radius: var(--wp--custom--radius--md);

/* WRONG */
color: #0096C7;
font-family: 'Sora', sans-serif;
font-size: 64px;
padding: 1.5rem;
border-radius: 8px;
```

### Token Reference

| Category | Pattern | Example |
|---|---|---|
| Colors | `var(--wp--preset--color--<slug>)` | `--color--primary`, `--color--text` |
| Font Family | `var(--wp--preset--font-family--<slug>)` | `--font-family--heading` (Sora), `--font-family--body` (Archivo) |
| Font Size | `var(--wp--preset--font-size--<slug>)` | `--font-size--huge` (~64px), `--font-size--xx-large` (~48px) |
| Spacing | `var(--wp--preset--spacing--<slug>)` | `--spacing--40` (~1.5rem), `--spacing--70` (~5rem) |
| Radius | `var(--wp--custom--radius--<slug>)` | `--radius--sm` (4px), `--radius--full` (9999px) |
| Shadow | `var(--wp--custom--shadow--<slug>)` | `--shadow--sm`, `--shadow--lg` |
| Transition | `var(--wp--custom--transition--default)` | `all 0.2s ease-in-out` |

## 2. Fluid Typography

Use `clamp()` for any font size not covered by a `theme.json` token. The pattern is:

```css
font-size: clamp(<min>, <preferred>, <max>);
```

Common sizes:

| Target | Clamp Value |
|---|---|
| 20px body/subtitle | `clamp(1rem, 1.5vw, 1.25rem)` |
| 18px small body | `clamp(0.875rem, 1.25vw, 1.125rem)` |
| 32px subtitle | `clamp(1.5rem, 2.5vw, 2rem)` |

For sizes that map directly to a token, prefer the token:

```css
/* 64px heading — use the token */
font-size: var(--wp--preset--font-size--huge);

/* 48px card title — use the token */
font-size: var(--wp--preset--font-size--xx-large);
```

## 3. Section Layout & Alignment

All full-width sections must align their content with the header. Use this pattern:

```css
.cwc-<section> {
    padding: var(--wp--preset--spacing--70) clamp(1.5rem, 4vw, 4rem);
    margin-block-start: 0 !important;
    overflow: hidden;
}

.cwc-<section>__inner {
    max-width: 1921px;
    margin: 0 auto;
}
```

- **Horizontal padding**: `clamp(1.5rem, 4vw, 4rem)` — matches the header.
- **`margin-block-start: 0 !important`**: Eliminates WordPress block gap between sections.
- **`max-width: 1921px`**: Constrains content width consistently across all sections.

## 4. CSS Architecture

### File Organization

- **Block styles**: `blocks/<name>/style.css` — scoped to that block only.
- **Global styles**: `assets/css/global.css` — resets, utilities, shared patterns.
- **Header styles**: `assets/css/header.css` — header-specific styles and scroll behavior.

### Naming Convention

Follow BEM with a `cwc-` prefix:

```
.cwc-<block>                    → Block
.cwc-<block>__<element>         → Element
.cwc-<block>__<element>--<mod>  → Modifier
```

Examples:

```css
.cwc-hero                       /* Block */
.cwc-hero__video-toggle         /* Element */
.cwc-hero__icon-pause           /* Element */
.cwc-accommodations__card--even /* Modifier */
```

### CSS Property Order

Group properties in this order:

1. Layout (`display`, `grid-*`, `flex-*`, `position`, `inset`, `z-index`)
2. Box model (`width`, `height`, `padding`, `margin`, `border`, `border-radius`)
3. Typography (`font-*`, `line-height`, `letter-spacing`, `color`, `text-*`)
4. Visual (`background`, `box-shadow`, `opacity`, `overflow`)
5. Animation (`transition`, `animation`, `transform`)

### Responsive Breakpoints

| Breakpoint | Target |
|---|---|
| `max-width: 600px` | Mobile |
| `max-width: 900px` | Tablet |
| `max-width: 1024px` | Small desktop |

Use mobile-first where practical. Place `@media` rules at the bottom of each block's CSS file.

## 5. PHP / Block Development

### Block Structure

Every custom block must contain:

```
blocks/<name>/
├── block.json     # Metadata, attributes, supports
├── render.php     # Server-side render template
├── style.css      # Frontend styles
└── view.js        # Frontend JS (optional — register with "viewScript")
```

### Block Registration

Register all blocks in `functions.php` via `register_block_type()`:

```php
register_block_type( get_stylesheet_directory() . '/blocks/<name>' );
```

### PHP Conventions

- Guard every `render.php` with `if ( ! defined( 'ABSPATH' ) ) { exit; }`.
- Use null coalescing for attributes: `$heading = $attributes['heading'] ?? 'Default';`
- Escape all output: `esc_html()`, `esc_attr()`, `esc_url()`.
- Use `get_block_wrapper_attributes()` for the outermost element.

```php
$wrapper_attrs = get_block_wrapper_attributes( [
    'class' => 'cwc-<block>',
] );
```

## 6. JavaScript

- Use IIFEs to avoid global scope pollution.
- No build step — write vanilla ES5-compatible JS.
- Register frontend scripts via `"viewScript": "file:./view.js"` in `block.json`.
- Use `querySelectorAll` to support multiple instances of the same block on a page.

```javascript
( function () {
    document.querySelectorAll( '.cwc-<block>' ).forEach( function ( el ) {
        // block logic
    } );
} )();
```

## 7. Template Files

### Block Templates (`templates/*.html`)

- Use WordPress block markup: `<!-- wp:cwc/<block> { ...JSON attrs... } /-->`.
- Always include `"align": "full"` for full-width sections.
- Validate JSON attributes — a missing quote will silently break the block.

### Template Parts (`parts/*.html`)

- `header.html` and `footer.html` are shared across all templates.
- Reference via `<!-- wp:template-part {"slug":"header"} /-->`.

## 8. Assets & Media

- SVGs are allowed in the Media Library (handled by `cwc_allow_svg_uploads` in `functions.php`).
- Uploaded media lives in `wp-content/uploads/2026/04/` and is referenced by absolute path from the web root.
- Use `loading="lazy"` on all non-critical images.
- Use `preload="metadata"` on videos.

## 9. Accessibility

- All interactive elements must have `aria-label` attributes.
- Decorative elements use `aria-hidden="true"`.
- Focus outlines are intentionally removed on styled interactive elements (nav links, toggle buttons, carousel arrows) but these elements still have visible hover/active states.
- Use semantic HTML: `<section>`, `<nav>`, `<blockquote>`, `<button>`.

## 10. Git Practices

- Commit messages should be concise and describe **why**, not just what.
- Do not commit `node_modules/`, `.env`, or WordPress core files.
- The theme directory (`themes/child-cwcwake/`) is the root of the git repository's tracked content.
