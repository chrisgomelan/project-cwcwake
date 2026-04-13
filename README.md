# CWC Wake

WordPress project for CWC Wake. This repository tracks the `wp-content/` directory only — custom themes, custom plugins, and configuration. Everything reinstallable (core, third-party plugins, uploads) is excluded via `.gitignore`.

## Prerequisites

- [Local by Flywheel](https://localwp.com/) installed
- Git installed
- GitHub access to this repository

## Setup for New Developers

### 1. Create a Local Site

1. Open **Local** → click **+ Create a new site**.
2. Name it `child-cwcwake` (or any name you prefer).
3. Choose **Custom** environment:
   - PHP: **8.1+**
   - Web server: **Nginx** (preferred)
   - MySQL: **8.0+**
4. Finish the wizard and let WordPress install.

### 2. Clone the Repository

Open a terminal and navigate to the new site's `wp-content/` directory:

```bash
cd ~/Local\ Sites/child-cwcwake/app/public/wp-content
```

Remove the default content that Local created (back up anything you need first):

```bash
rm -rf themes plugins index.php
```

Clone this repo directly into the `wp-content/` folder:

```bash
git clone https://github.com/chrisgomelan/project-cwcwake.git .
```

> **Important:** Note the `.` at the end — this clones into the current directory instead of creating a subfolder.

### 3. Install Plugins

Plugins are **not tracked** in this repo (they're `.gitignore`-d). Install them manually:

1. Log in to WordPress admin at `https://child-cwcwake.local/wp-admin/`.
2. Go to **Plugins → Add New** and install any required plugins.

<!-- If using Composer, uncomment:
```bash
cd themes/your-child-theme
composer install
```
-->

### 4. Install Dependencies (if applicable)

If the theme uses npm for build tools:

```bash
cd themes/your-child-theme
npm install
npm run build
```

### 5. Configure wp-config.php

Add these constants to your `wp-config.php` (located at `~/Local Sites/child-cwcwake/app/public/wp-config.php`) for local development:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
define('SCRIPT_DEBUG', true);
define('WP_ENVIRONMENT_TYPE', 'local');
```

## Pulling Updates

When other developers push changes:

```bash
cd ~/Local\ Sites/child-cwcwake/app/public/wp-content
git pull origin main
```

If you have local changes that conflict:

```bash
git stash
git pull origin main
git stash pop
```

## Branching & Commits

- **Never push directly to `main`** — create a feature branch and open a PR.
- Branch naming: `feature/short-description`, `fix/bug-description`, `chore/task-description`
- Commit format: `<Type>: <summary>` (e.g., `Add responsive header navigation`)
- Types: Add, Update, Fix, Remove, Refactor, Style, Docs, Chore, Perf

```bash
git checkout -b feature/your-feature
# ... make changes ...
git add .
git commit -m "Add your feature description"
git push -u origin feature/your-feature
# Then open a PR on GitHub
```

## What's Tracked vs Ignored

| Tracked (in repo) | Ignored (install locally) |
|---|---|
| Custom child theme(s) | WordPress core |
| Custom plugins you build | Third-party plugins |
| `.gitignore`, `README.md` | `uploads/` (media files) |
| `index.php` safety files | `node_modules/`, `vendor/` |
| `.cursor/rules/` (AI conventions) | Cache, logs, backups |

## Project Structure

```
wp-content/                ← Git root
├── .cursor/rules/         ← Cursor AI rules (auto-loaded)
├── themes/
│   └── your-child-theme/  ← Custom child theme
├── plugins/
│   └── .gitkeep           ← Keeps directory in repo
├── .gitignore
├── index.php
└── README.md              ← You are here
```

## Troubleshooting

**"Detached HEAD" or wrong branch after cloning:**
```bash
git checkout main
```

**Local site can't find theme after cloning:**
Go to WP Admin → Appearance → Themes and activate the child theme.

**Permission errors on Linux:**
```bash
sudo chown -R $USER:$USER ~/Local\ Sites/child-cwcwake/app/public/wp-content
```

**Plugin dependency missing:**
Check the project documentation or ask the team which plugins are required for this project.
