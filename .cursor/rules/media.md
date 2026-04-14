---
description: Media handling conventions — SVG support and image URL paths
alwaysApply: true
---

# Media Conventions

## SVG Support

SVG uploads are enabled in `functions.php` via `cwc_allow_svg_uploads` and `cwc_fix_svg_filetype`. Always treat `.svg` as a valid image format in this project.

## Image URLs (Media Library)

When referencing images stored in the WordPress Media Library, always use **root-relative paths** starting with `/wp-content/uploads/`:

```
✅ /wp-content/uploads/2026/04/cwc-header-logo.svg
✅ /wp-content/uploads/2026/04/hero-banner.jpg

❌ https://child-cwcwake.local/wp-content/uploads/2026/04/cwc-header-logo.svg
❌ ../uploads/2026/04/cwc-header-logo.svg
❌ cwc-header-logo.svg
```

- In block template HTML (`src`, `url`, `href`): use `/wp-content/uploads/YYYY/MM/filename.ext`
- In PHP: prefer `wp_get_attachment_url()` or `wp_get_attachment_image()` when the image has a post ID; use the root-relative path for hardcoded references
- In CSS (`background-image`, etc.): use the root-relative `/wp-content/uploads/...` path

## Theme Assets vs Media Library

- **Theme assets** (logos, icons bundled with the theme): reference from `assets/images/` via `get_stylesheet_directory_uri()`
- **Media Library uploads** (user-uploaded content): reference with `/wp-content/uploads/...` paths
