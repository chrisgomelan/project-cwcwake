---
description: Project-wide conventions for CWC Wake WordPress project
alwaysApply: true
---

# CWC Wake — Project Conventions

## Project Info

- **Project**: CWC Wake (WordPress site)
- **Git root**: `wp-content/` directory
- **Function prefix**: `cwc_` for all PHP functions, hooks, constants
- **Text domain**: matches the child theme slug
- **Documentation vault**: `~/Obsidian/projects/cwcwake/` (Obsidian)

## Commit Messages

Format: `<Type>: <short summary in imperative mood>`

Types: Add, Update, Fix, Remove, Refactor, Style, Docs, Test, Chore, Perf

Rules:
- Imperative mood ("Add feature" not "Added feature")
- Capitalize first word, no period at end
- Keep subject under 72 characters
- Body explains "why", not "what"

## File Naming

- PHP classes: `class-{name}.php`
- PHP includes: `lowercase-hyphenated.php`
- Templates: `{template-name}.php`
- Template parts: `content-{name}.php`
- CSS/JS: `lowercase-hyphenated.ext`
- Block folders: `lowercase-hyphenated/`

## General Rules

- Never commit secrets, API keys, or `wp-config.php`
- Never use `extract()`, `eval()`, `$$variable`, `goto`, or `@` error suppression
- Always check return values from WordPress functions that can fail
- Use `WP_Error` for WordPress-context errors
- Every PHP file must start with `if (!defined('ABSPATH')) exit;` (except the main theme files)
