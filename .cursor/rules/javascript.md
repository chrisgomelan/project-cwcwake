---
description: JavaScript coding standards
globs: ["**/*.js"]
---

# JavaScript Standards

## General

- ES6+ syntax: `const`/`let`, arrow functions, template literals, destructuring
- Never use `var`
- Strict equality only (`===` / `!==`)
- Always use semicolons
- Files named `kebab-case.js`

## Naming

- Variables/functions: `camelCase`
- Constants: `UPPER_SNAKE_CASE`
- Classes: `PascalCase`

## DOM

- Use `querySelector` / `querySelectorAll` over jQuery
- Wrap in `DOMContentLoaded` or check element existence
- For Gutenberg blocks, use `view.js` with vanilla JS or the Interactivity API

## WordPress AJAX

- Use `fetch()` API with `FormData`, not jQuery AJAX
- Always include nonce via `wp_localize_script()` from PHP
- Wrap async operations in try/catch
- Never swallow errors silently

## Functions

- Arrow functions for callbacks
- Named functions for top-level declarations
- Single responsibility per function
