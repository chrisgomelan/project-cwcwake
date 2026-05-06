# CWC Wake

WordPress project for CWC Wake — Camsur Watersports Complex. 

**This repository tracks EVERYTHING needed to instantly sync a full LocalWP site.**
It tracks the `wp-content/` directory, which includes:
- **Themes** (`themes/child-cwcwake/`)
- **Plugins** (Rank Math, SMTP, etc.)
- **Uploads** (Images, Videos, Media)
- **Database** (`db-sync.sql` - Posts, pages, meta descriptions, and settings)

---

## 🚀 How to Set Up the Site (For Collaborators)

If you are a new developer joining the project, follow these steps to get an exact copy of the live site (including all images, plugins, posts, and SEO meta descriptions) running on your machine.

### 1. Create a Local Site
1. Open **LocalWP** → click **+ Create a new site**.
2. Name it `project-cwcwake` (or any name you prefer).
3. Choose **Custom** environment:
   - PHP: **8.1+**
   - Web server: **Nginx** (preferred)
   - MySQL: **8.0+**
4. Finish the wizard and let WordPress install.

### 2. Clone the Repository
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
*(PowerShell)*: `Remove-Item -Recurse -Force themes, plugins, index.php`
*(Mac/Linux)*: `rm -rf themes plugins index.php`

**Clone the repo** directly into the `wp-content/` folder:
```bash
git clone https://github.com/chrisgomelan/project-cwcwake.git .
```
> **Important:** Note the `.` at the end — this ensures you clone directly into the current folder instead of making a subfolder.

### 3. Import the Database (Posts, Pages, Meta Descriptions)
Because Git only tracks *files*, we store the WordPress database in a file called `db-sync.sql`. You must import this to get the posts, pages, and SEO data.

1. In LocalWP, right-click your site `project-cwcwake` and select **Open Site Shell**.
2. A terminal will open. Run this command:
   ```bash
   wp db import wp-content/db-sync.sql
   ```
3. Wait for the success message.
4. Your site now has all the exact posts, settings, and media!

### 4. Activate the Theme
1. Log in to WordPress admin at `http://project-cwcwake.local/wp-admin/`.
   - *Note: Login credentials will match whatever was exported in the `db-sync.sql` file.*
2. Go to **Appearance → Themes**.
3. Activate the **CWC Wake** child theme.
> The parent theme **Twenty Twenty-Five** must be installed. It ships with WordPress by default.

---

## 🔄 How to Push Your Changes (To Share with Collaborators)

When you add new posts, upload videos, change SEO meta descriptions, or edit the theme, you need to push **both your files and the database** so your collaborators see exactly what you see.

### Step 1: Export the Database
Before you commit, you must export your database to `db-sync.sql` so Git can track the latest posts and settings.

1. In LocalWP, right-click the site and select **Open Site Shell**.
2. Run this command to export the database:
   ```bash
   wp db export wp-content/db-sync.sql
   ```
   *(This overwrites the existing file with your latest data).*

### Step 2: Commit and Push
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

## 📁 Project Structure

```
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
