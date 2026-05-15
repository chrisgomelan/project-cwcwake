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

1. In LocalWP, right-click your site `project-cwcwake` and select **Open Site Shell**.
2. **Wipe the default database** to ensure a clean import:
   ```bash
   wp --dbhost="127.0.0.1:YOUR_PORT" db reset --yes
   ```
3. **Import the CWC database**:
   ```bash
   mysql -u root -proot -h 127.0.0.1 -P YOUR_PORT local < wp-content/db-sync.sql
   ```

   *(Wait for it to finish. If successful, you will see a new prompt with no errors).*

   **💡 Troubleshooting Connection Errors:**
   If you see "Can't connect to MySQL", go to the **Database** tab in LocalWP and find your **Port**. Use that number in place of `YOUR_PORT` above.

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
   - **Important:** Your initial LocalWP login will stop working after the import. Use the credentials from the sync file:
   - **Username:** `project-cwcwake`
   - **Password:** `123456789`
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

### 3. White Screen or "Fatal Error" (Missing vendor files)
If the site crashes with an error about a missing `vendor` or `action-scheduler.php` file:
- **Cause:** Some plugins depend on internal folders that might have been ignored or not fully pulled.
- **Fix:** Ensure you have the latest code from `git pull`. If it still fails, temporarily rename that plugin's folder in `wp-content/plugins/` to disable it and get the admin back.
- **Developer Note:** Check the `.gitignore` to ensure it is not ignoring `/vendor/` inside plugin subdirectories.

### 4. Images are blank in Media Library or Grid
If you see the image names but the boxes are blank (and they only appear when clicked):
- **Cause:** To keep the repository small, we only track "Original" images. The small "Thumbnail" versions (e.g., `image-300x200.jpg`) are ignored by Git.
- **Fix:** You must generate the thumbnails locally by running this command in your Site Shell:
  ```bash
  wp media regenerate --yes --url="http://your-local-domain.local"
  ```

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
