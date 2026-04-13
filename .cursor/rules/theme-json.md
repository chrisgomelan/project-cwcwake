---
description: theme.json configuration standards for WordPress block themes
globs: ["**/theme.json", "**/styles/*.json"]
---

# theme.json Standards

## Structure

- Use schema version 3: `"$schema": "https://schemas.wp.org/wp/6.7/theme.json"`
- Child theme theme.json merges with parent — only declare overrides

## Typography

- Enable fluid typography: `"fluid": true` in settings.typography
- Define all font sizes with fluid min/max values
- Use font size scale: small, medium, large, x-large, xx-large, huge

### Heading Styles (styles.elements)

| Element | Size Token | Weight | Line Height | Notes |
|---|---|---|---|---|
| h1 | huge | 700 | 1.15 | letter-spacing: -0.02em |
| h2 | xx-large | 700 | 1.2 | letter-spacing: -0.015em |
| h3 | x-large | 600 | 1.25 | |
| h4 | large | 600 | 1.3 | |
| h5 | medium | 600 | 1.4 | uppercase, letter-spacing: 0.05em |
| h6 | small | 600 | 1.4 | uppercase, letter-spacing: 0.05em |
| body/p | medium | 400 | 1.65 | |

- Set heading margins via styles.elements.hX.spacing.margin
- Self-host fonts using fontFace with fontDisplay: swap

## Spacing

- Define fluid spacing scale with slugs 10–70
- Use `var(--wp--preset--spacing--XX)` in all styles, never hardcoded values
- Small spacers (10, 20) can be fixed; larger ones (30+) should be fluid

## Colors

- Set `defaultPalette: false` and `defaultGradients: false` to curate the editor palette
- Define: primary, primary-dark, secondary, background, surface, text, text-muted, border, success, error, warning
- Use `var(--wp--preset--color--slug)` everywhere

## Layout

- Set contentSize (readable width, ~720px) and wideSize (~1200px)

## Custom Properties

- Define border radii, shadows, transitions under settings.custom
- Access via `var(--wp--custom--key--subkey)`

## Rules

- Design tokens (colors, fonts, spacing) belong in theme.json, not CSS
- Complex selectors, pseudo-elements, and animations belong in CSS
- Always reference preset variables, never hardcode values in styles
- Block-level overrides go in styles.blocks, not in separate CSS
