---
description: CSS/SCSS coding standards
globs: ["**/*.css", "**/*.scss"]
---

# CSS / SCSS Standards

## Naming

- Use **BEM**: `.block__element--modifier`
- Custom properties: `--kebab-case` (e.g., `--color-primary`)
- State classes: `.is-active`, `.has-error`
- JS hook classes: `.js-toggle` (never style these)
- Utility classes: `.u-text-center`

## Formatting

- One property per line
- No ID selectors for styling
- Alphabetize properties or group by type (positioning, box model, typography, visual)

## Responsive Design

- Mobile-first: base styles for mobile, `min-width` media queries for larger screens
- Breakpoints: 768px (tablet), 1024px (desktop), 1280px (wide)
- Use relative units (`rem`, `em`, `%`) over `px` where appropriate

## Design Tokens

Define colors, fonts, spacing as CSS custom properties on `:root`. Use variables instead of hardcoded values.

## Avoid

- `!important` (only for third-party overrides as last resort)
- Deeply nested selectors (max 3 levels in SCSS)
- Bare element selectors for styling (`.card p` is fragile)
- Magic numbers without explanation
