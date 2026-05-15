# Installation and environment

[← Back to overview](README.md)

This page mirrors the **setup and sync** instructions from the repository [`README.md`](../README.md) (project root). If one copy is updated later, align the other so collaborators stay in sync.

---

## CWC Wake

WordPress project for CWC Wake — Camsur Watersports Complex.

**This repository tracks EVERYTHING needed to instantly sync a full LocalWP site.**

It tracks the `wp-content/` directory, which includes:

- **Themes** (`themes/child-cwcwake/`)
- **Plugins** (Rank Math, SMTP, etc.)
- **Uploads** (Images, Videos, Media)
- **Database** (`db-sync.sql` — Posts, pages, meta descriptions, and settings)

---

## 🚀 How to Set Up the Site (For Collaborators)

If you are a new developer joining the project, follow these steps to get an exact copy of the live site (including all images, plugins, posts, and SEO meta descriptions) running on your machine.

### 1. Create a Local site

1. Open **LocalWP** → click **+ Create a new site**.
2. Name it `project-cwcwake` (or any name you prefer).
3. Choose **Custom** environment:
   - PHP: **8.1+**
   - Web server: **Nginx** (preferred)
   - MySQL: **8.0+**
4. Finish the wizard and let WordPress install.

### 2. Clone the repository

Open your terminal and navigate into the new site's `wp-content/` directory:

**Windows (PowerShell):**

```powershell
cd "$HOME\Local Sites\project-cwcwake\app\public\wp-content"
```

**macOS / Linux:**

```bash
cd ~/Local\ Sites/project-cwcwake/app/public/wp-content
```

**Delete the default folders** so we can clone the repo cleanly:

- *(PowerShell):* `Remove-Item -Recurse -Force themes, plugins, index.php`
- *(Mac/Linux):* `rm -rf themes plugins index.php`

**Initialize and sync the repository:**

```bash
git init
git remote add origin https://github.com/chrisgomelan/project-cwcwake.git
git fetch
git reset --hard origin/main
```

### 3. Import the database (posts, pages, meta descriptions)

Because Git only tracks *files*, we store the WordPress database in a file called `db-sync.sql`. You must import this to get the posts, pages, and SEO data.

**Where that file is after you clone:** it sits in your cloned repo at **`wp-content/db-sync.sql`** — that is `…/app/public/wp-content/db-sync.sql` on disk (same level as the `themes/` and `plugins/` folders). Cloning only downloads this file; it does **not** load it into MySQL until you run the import command below.

1. In LocalWP, right-click your site `project-cwcwake` and select **Open Site Shell**.
2. A terminal will open. **Ensure you are in the site's public folder** by running this command:

   ```bash
   cd "app/public"
   ```

3. Now run the import command:

   ```bash
   wp db import wp-content/db-sync.sql
   ```

   **💡 Troubleshooting: If you get a "Can't connect to MySQL" error:**
   If the default command fails, find your site's **Port** in the LocalWP **Database** tab and run:
   ```bash
   mysql -u root -proot -h 127.0.0.1 -P YOUR_PORT local < wp-content/db-sync.sql
   ```

3. Wait for the success message.

### 4. Update the Site URLs (Crucial for Images)

Because the database was exported from a different URL, you must update the links to match your local site. **If you skip this, images and logos will not appear.**

1. In your Site Shell, run this command (replace the URLs with your actual ones):
   ```bash
   wp search-replace "original-domain.local" "your-local-domain.local"
   ```
   *Example: `wp search-replace "project-cwc.local" "project-cwc-test.local"`*

2. Your site now has all the exact posts, settings, and media!

### 5. Activate the theme

1. Log in to WordPress admin at `http://project-cwcwake.local/wp-admin/`.
   - *Note: Login credentials will match whatever was exported in the `db-sync.sql` file.*
2. Go to **Appearance → Themes**.
3. Activate the **CWC Wake** child theme.

> The parent theme **Twenty Twenty-Five** must be installed. It ships with WordPress by default.

### 6. Activate the plugins

1. Go to **Plugins → Installed Plugins**.
2. Activate the **CWC Accommodations** plugin.
3. Activate other supporting plugins as needed (Rank Math SEO, WP Mail SMTP, etc.).

### 7. Configure PayMongo keys (optional)

If you need to test the booking and payment flow locally, you must add the PayMongo API test keys to your local `wp-config.php` file (located one folder up from `wp-content/`).

Open `app/public/wp-config.php` and add these lines anywhere before `/* That's all, stop editing! Happy publishing. */`:

```php
define( 'PAYMONGO_PUBLIC_KEY', 'pk_test_your_public_key_here' );
define( 'PAYMONGO_SECRET_KEY', 'sk_test_your_secret_key_here' );
```

*(Ask the lead developer or check the team password manager for the actual test keys).*

### 8. Groq API keys — floating chat assistant (optional)

The **CWC Wake AI Assistant** (child theme `themes/child-cwcwake/inc/chat-assistant.php`) calls the [Groq](https://groq.com/) API from PHP. Add these defines in **`app/public/wp-config.php`** anywhere **before** `/* That's all, stop editing! Happy publishing. */`:

| Constant | Required? | Description |
|----------|-----------|-------------|
| **`CWC_GROQ_API_KEY`** | **Yes** for chat to work | Groq secret API key (starts with `gsk_`). If missing or empty, the chat REST route will not call the model. |
| **`CWC_GROQ_CHAT_MODEL`** | No | Overrides the default chat model. If omitted, the theme uses `llama-3.3-70b-versatile`. |

Example:

```php
define( 'CWC_GROQ_API_KEY', 'gsk_your_secret_key_here' );
// Optional — only if you want a different model id:
// define( 'CWC_GROQ_CHAT_MODEL', 'llama-3.3-70b-versatile' );
```

**Security:** do not commit real keys to Git. Keep them only in each environment’s `wp-config.php` (or another file outside the repo).

---

## 🔄 How to Push Your Changes (To Share with Collaborators)

When you add new posts, upload videos, change SEO meta descriptions, or edit the theme, you need to push **both your files and the database** so your collaborators see exactly what you see.

### Step 1: Export the database

Before you commit, you must export your database to `db-sync.sql` so Git can track the latest posts and settings.

1. In LocalWP, right-click the site and select **Open Site Shell**.
2. Run this command to export the database:

   ```bash
   wp db export wp-content/db-sync.sql
   ```

   *(This overwrites the existing file with your latest data).*

   **💡 Troubleshooting: If you get a "Can't connect to MySQL" error:**
   1. Go to the **Database** tab in LocalWP and find your **Port** (e.g., 10084).
   2. Use this "Force" command instead:
      ```bash
      wp --dbhost="127.0.0.1:YOUR_PORT" db export wp-content/db-sync.sql
      ```
   3. Or use the direct MySQL command (most reliable on Windows):
      ```bash
      mysqldump -u root -proot -h 127.0.0.1 -P YOUR_PORT local > wp-content/db-sync.sql
      ```

### Step 2: Commit and push

Open your regular terminal in the `wp-content` folder and push everything:

```bash
git add .
git commit -m "Add new blog post, updated SEO, and theme changes"
git push origin main
```

*Because our `.gitignore` tracks plugins and uploads, your new images and new plugins will automatically be included in this push!*

---

## 📥 How to Pull Updates (From Collaborators)

When someone else pushes changes, you need to pull the files AND import their new database.

1. Pull the files via Git:

   ```bash
   git pull origin main
   ```

2. In LocalWP, right-click the site and select **Open Site Shell**.
3. Import their database:

   ```bash
   wp db import wp-content/db-sync.sql
   ```

You are now fully synced with their changes!

---

## 🛠️ Common Post-Setup Issues

### 1. Logo or Favicon (Tab Icon) is missing
If the logo in the browser tab is missing after a pull:
- **Cause:** The Site Icon setting is stored as an Attachment ID. If that ID doesn't match or the file was recently added, it may need to be re-saved.
- **Fix:** Go to **Appearance → Customize → Site Identity** and re-select the Site Icon.
- **Developer Note:** Ensure the original site has exported the latest `db-sync.sql` *after* the icon was set.

### 2. Permalinks return 404
If room pages or blog posts return a 404 error:
- **Fix:** Go to **Settings → Permalinks** and simply click **Save Changes**. This flushes the rewrite rules.

---

---

## 📁 Project Structure

```text
wp-content/                          ← Git root
├── db-sync.sql                      ← The WordPress Database (Posts, SEO, Settings)
├── themes/
│   └── child-cwcwake/               ← Custom child theme (parent: Twenty Twenty-Five)
├── plugins/                         ← ALL plugins are tracked (Rank Math, etc.)
├── uploads/                         ← ALL images, videos, and media are tracked
├── designs/                         ← Design assets (mockups, wireframes, exports)
├── .gitignore
├── index.php
└── README.md                        ← You are here
```

---

## Related
- [Client User Guide](CLIENT_USER_GUIDE.md)
- [Developer Handover Guide](DEVELOPER_HANDOVER_GUIDE.md)
