---
description: Gutenberg block development standards
globs: ["**/blocks/**"]
---

# Gutenberg Block Standards

## Structure

Every custom block lives in `blocks/block-name/` with:

```
blocks/block-name/
├── block.json      # Metadata (required, canonical source)
├── render.php      # Server-side template
├── style.css       # Frontend + editor styles
├── editor.css      # Editor-only styles (optional)
├── index.js        # Editor script (optional)
└── view.js         # Frontend interactivity (optional)
```

## Rules

- Always use `block.json` for registration — never register purely in PHP or JS
- Prefix block names: `cwc/block-name`
- Default to **dynamic blocks** (server-rendered via `render.php`) — easier to update without migrations
- Use `get_block_wrapper_attributes()` in render.php for proper class names and alignment
- One block = one purpose; use InnerBlocks for composition
- Register blocks via `register_block_type(__DIR__ . '/blocks/block-name')` in the `init` hook

## Block Attributes in Templates

When writing `<!-- wp:cwc/block-name {...} /-->` in `.html` templates, **always format attributes on separate lines** for readability:

```html
<!-- wp:cwc/hero-section {
	"backgroundImage": "/wp-content/uploads/2026/04/hero.jpg",
	"headingLine1": "RIDE THE",
	"headingEmphasis": "BEST WAKEPARK",
	"align": "full"
} /-->
```

Never put all attributes on a single line. Array attributes (`items`) should also have each object on its own lines.

## render.php Pattern

```php
<?php
$class_name = 'wp-block-cwc-block-name';
if (!empty($attributes['className'])) {
    $class_name .= ' ' . esc_attr($attributes['className']);
}
?>
<div <?php echo get_block_wrapper_attributes(['class' => $class_name]); ?>>
    <!-- output here, escape all dynamic data -->
</div>
```

## block.json

- `apiVersion`: 3
- `category`: "theme" for theme-specific blocks
- `textdomain`: must match the theme text domain
- Always set `"html": false` in supports unless raw HTML editing is needed
