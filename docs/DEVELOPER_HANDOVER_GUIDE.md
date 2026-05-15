# Developer Handover Guide

**Project:** CWC Wake — Camsur Watersports Complex  
**Platform:** WordPress 6.0+, PHP 7.4+  
**Repository:** `https://github.com/chrisgomelan/project-cwcwake.git`  
**Git root:** `wp-content/` (not the WordPress root)  
**Core plugin:** `cwc-accommodations` v1.0.0  
**Theme:** `child-cwcwake` (child of Twenty Twenty-Five)

---

## 1. Project overview

CWC Wake is a booking and information website for a watersports resort. The site does not use WooCommerce or any third-party booking plugin. All booking, availability, coupon, email, and dashboard logic is handled by a single custom plugin (`cwc-accommodations`) and the child theme (`child-cwcwake`).

The plugin manages the data layer (Custom Post Types, metadata, AJAX endpoints). The theme manages the presentation layer (block templates, custom blocks, front-end booking flow, and the AI chat assistant).

---

## 2. Environment setup

Local development uses **LocalWP**. Full setup instructions are in [`docs/02-installation-and-environment.md`](02-installation-and-environment.md) and the repository [`README.md`](../README.md). Summary:

1. Create a LocalWP site with PHP 8.1+, Nginx, MySQL 8.0+.
2. Clone the repo into the `wp-content/` directory.
3. Import the database:
   - Default: `wp db import wp-content/db-sync.sql`
   - Fallback (if connection fails): `mysql -u root -proot -h 127.0.0.1 -P [PORT] local < wp-content/db-sync.sql`
4. Update URLs (for images/links): `wp search-replace "old-domain" "new-domain"`
5. Activate the `child-cwcwake` theme.
6. Activate the `cwc-accommodations` plugin.
7. Add API keys to `wp-config.php` as needed.

### 2a. Key environment constants

These are defined in `wp-config.php`, not in the repository:

| Constant | Required | Purpose |
|---|---|---|
| `PAYMONGO_PUBLIC_KEY` | For payment testing | PayMongo test public key (`pk_test_...`) |
| `PAYMONGO_SECRET_KEY` | For payment testing | PayMongo test secret key (`sk_test_...`) |
| `CWC_GROQ_API_KEY` | For AI chat | Groq API key (`gsk_...`). If missing, the chat endpoint returns nothing. |
| `CWC_GROQ_CHAT_MODEL` | No | Overrides the default model. Default: `llama-3.3-70b-versatile` |

Never commit real keys to Git.

---

## 3. Theme structure

The child theme lives at `themes/child-cwcwake/`. Key files and directories:

| Path | Purpose |
|---|---|
| `style.css` | Theme declaration header (parent: Twenty Twenty-Five) |
| `theme.json` | Design tokens, color palette, typography, spacing, layout constraints |
| `functions.php` | Primary hook file (~66KB). Loads all `inc/*.php` files. Contains the booking form handler, search/checkout logic, and front-end asset enqueuing. |
| `inc/` | Modular PHP includes loaded by `functions.php` — chat assistant, payment integration, and other helpers |
| `blocks/` | Custom Gutenberg blocks registered by the theme (room-info, room-gallery, other-rooms, location/store finder) |
| `parts/` | Block theme template parts (header, footer, sidebar sections) |
| `templates/` | Full-page block templates (single-accommodation, page, etc.) |
| `assets/` | Static assets — SVG icons, images, CSS, JS |
| `PROJECT.md` | Internal project notes |
| `STANDARDS.md` | Coding standards and conventions for this theme |

The theme depends on the `cwc-accommodations` plugin for all room and booking data. If the plugin is deactivated, room pages will render blank. If the theme is switched, the plugin will show an admin notice warning that room pages have no renderer.

---

## 4. Plugin architecture — `cwc-accommodations`

Location: `plugins/cwc-accommodations/`

### 4a. Bootstrap

`cwc-accommodations.php` defines four constants and loads all modules in dependency order:

```
CWC_ACC_VERSION  — '1.0.0'
CWC_ACC_FILE     — __FILE__
CWC_ACC_PATH     — plugin_dir_path(__FILE__)
CWC_ACC_URL      — plugin_dir_url(__FILE__)
```

### 4b. Module load order

Modules are loaded via `require_once` in this sequence:

| Order | File | Responsibility |
|---|---|---|
| 1 | `includes/cpt.php` | Registers the `accommodation` CPT, taxonomies, physical room units, inventory helpers, and shared catalogues (icon pool, amenities, beds, inclusions). All other modules depend on this. |
| 2 | `includes/settings.php` | Admin pages: "Amenities & Icons" and "Inclusions" under the Accommodations menu. Handles save logic for `cwc_icon_pool`, `cwc_dynamic_amenities`, `cwc_dynamic_beds`, `cwc_dynamic_inclusions`. |
| 3 | `includes/blog-seeder.php` | Developer tooling for seeding sample blog posts. Triggered from the Amenities settings page. |
| 4 | `includes/metabox.php` | Room editor meta boxes: price, capacity, gallery, amenity checkboxes, bed type, inclusions, physical unit definitions. |
| 5 | `includes/policies.php` | "Global Policies" admin page. Stores shared house rules as JSON in `wp_options` key `cwc_global_policies`. Public API: `cwc_get_global_policies()`. |
| 6 | `includes/migrate.php` | One-shot migration from legacy theme-based room pages into the CPT structure. Safe to leave in place — runs only when triggered. |
| 7 | `includes/bookings-cpt.php` | Registers the `cwc_booking` CPT (hidden from admin UI), booking reference generator (`CWC-XXXXXX`), transaction ID generator (`TX-00001`), status update AJAX, payment status AJAX, availability checking, email dispatch, and audit logging. |
| 8 | `includes/subscribers.php` | Newsletter subscriber storage and admin list screen. Data stored in `wp_options` key `cwc_newsletter_subscribers`. |
| 9 | `includes/dashboard.php` | The Booking Dashboard admin page (~78KB, ~1987 lines). Tabs: Calendar (FullCalendar 5.11.3), Bookings, Payments, Room Units Tracking, Availability, Rates Management, Analytics. All rendering and AJAX handlers for the dashboard live here. |
| 10 | `includes/coupons.php` | Registers the `cwc_coupon` CPT, coupon meta box (type, amount, expiry, limit, usage count), and the AJAX validation endpoint used by the front-end checkout form. |
| 11 | `includes/mailer.php` | "Promo Mailer" admin page. Bulk email to subscribers and past guests, with optional coupon embed. Sends via `wp_mail` in batches of 5. |

### 4c. Dashboard assets

The dashboard loads its own CSS and JS from `includes/dashboard-assets/`:

- `dashboard.css` — all dashboard styling (stat cards, tables, modals, calendar overrides)
- `dashboard.js` — tab interactions, status modal logic, AJAX calls, FullCalendar initialization

FullCalendar CSS and JS are loaded from the jsDelivr CDN (version 5.11.3). The dashboard JS depends on FullCalendar and is localized with `cwcDash` containing `ajaxUrl`, `nonce`, and `adminUrl`.

---

## 5. Custom Post Types

### 5a. `accommodation`

The primary content type for room listings.

- **Public:** Yes. Permalink structure: `/accommodations/<slug>/`.
- **Supports:** title, editor, thumbnail, page-attributes.
- **Key meta keys:**

| Meta key | Type | Description |
|---|---|---|
| `_cwc_price` | string | Display price |
| `_cwc_capacity` | int | Maximum guest count |
| `_cwc_inventory` | int | Number of bookable units of this room type |
| `_cwc_amenities` | array | Checked amenity slugs |
| `_cwc_bed_type` | string | Bed type slug |
| `_cwc_inclusions` | array | Checked inclusion slugs |
| `_cwc_gallery` | array | Attachment IDs for the room gallery |
| `_cwc_physical_rooms` | JSON | Array of physical unit definitions (`id`, `name`, `status`) |

### 5b. `cwc_booking`

Hidden post type for storing reservation records. Not exposed in the admin menu or REST API — all interaction goes through the Booking Dashboard or AJAX endpoints.

- **Public:** No. `show_ui`, `show_in_menu`, `show_in_rest` are all false.
- **Supports:** title only.
- **Key meta keys:**

| Meta key | Type | Description |
|---|---|---|
| `_cwc_bk_ref` | string | Booking reference (`CWC-XXXXXX`). Auto-generated, unique. |
| `_cwc_bk_transaction_id` | string | Sequential transaction ID (`TX-00001`). |
| `_cwc_bk_name` | string | Guest full name |
| `_cwc_bk_email` | string | Guest email address |
| `_cwc_bk_phone` | string | Guest phone number |
| `_cwc_bk_room` | string | Room title at time of booking |
| `_cwc_bk_room_post_id` | int | Linked accommodation post ID |
| `_cwc_bk_checkin` | string | Check-in date |
| `_cwc_bk_checkout` | string | Check-out date |
| `_cwc_bk_nights` | int | Number of nights |
| `_cwc_bk_price` | string | Display price (formatted) |
| `_cwc_bk_price_num` | float | Numeric price for calculations |
| `_cwc_bk_payment` | string | Payment method |
| `_cwc_bk_status` | string | Booking status: `pending`, `confirmed`, `cancelled`, `completed` |
| `_cwc_bk_payment_status` | string | Payment status: `unpaid`, `paid`, `failed`, `refunded` |
| `_cwc_bk_assigned_room` | string | Physical unit name assigned to this booking |
| `_cwc_bk_assigned_unit_id` | string | Physical unit ID assigned to this booking |
| `_cwc_bk_coupon_code` | string | Coupon code used (if any) |
| `_cwc_bk_discount` | float | Discount amount applied |
| `_cwc_bk_original_price` | float | Price before discount |
| `_cwc_bk_guests` | JSON | Array of additional guest names |
| `_cwc_bk_requests` | string | Special requests from the guest |
| `_cwc_bk_audit_log` | JSON | Array of audit entries (action, admin, timestamp, details) |
| `_cwc_bk_email_log` | JSON | Array of email send records (type, to, sent, timestamp) |

### 5c. `cwc_coupon`

Stores discount codes. The post **title** is the coupon code.

- **Public:** No. `show_ui` is true, shown under the Accommodations menu.
- **Key meta keys:**

| Meta key | Type | Description |
|---|---|---|
| `_cwc_coupon_type` | string | `fixed` or `percent` |
| `_cwc_coupon_amount` | float | Discount value |
| `_cwc_coupon_expiry` | string | Expiration date (Y-m-d) |
| `_cwc_coupon_limit` | int | Maximum uses (empty = unlimited) |
| `_cwc_coupon_count` | int | Times redeemed (auto-incremented) |

---

## 6. Availability and unit assignment

Availability is calculated at query time, not stored as a static flag. The function `cwc_count_overlapping_bookings()` in `bookings-cpt.php` loops through all published `cwc_booking` posts and counts how many have active statuses (`pending` or `confirmed`) with date ranges that overlap the requested period.

A room is fully booked when `overlapping_count >= inventory_count`.

When a booking is confirmed, `cwc_assign_available_unit_to_booking()` assigns a specific physical unit (from `_cwc_physical_rooms`) to the booking and stores the unit ID in `_cwc_bk_assigned_unit_id`. When a booking is cancelled or completed, `cwc_release_legacy_booked_unit_for_booking()` releases the physical unit back to "available."

The front-end booking calendar calls `cwc_get_booked_dates` (AJAX action: `cwc_get_booked_dates`) to get a list of fully-booked dates for the next 365 days, which are then disabled in the date picker.

---

## 7. Email system

### 7a. Booking status emails

Defined in `cwc_send_booking_status_email()` in `bookings-cpt.php`. The system sends HTML emails for four statuses:

| Status | Subject line | Trigger |
|---|---|---|
| `pending` | "Booking Received — CWC Wake Park" | When a new booking is created |
| `confirmed` | "Booking Confirmed! — CWC Wake Park" | When admin confirms the booking |
| `cancelled` | "Booking Cancelled — CWC Wake Park" | When admin cancels the booking |
| `completed` | "Thank You for Staying! — CWC Wake Park" | When admin marks the stay as completed |

Each email includes: guest name, booking reference, room name, assigned unit (if any), check-in/out dates, duration, price (with discount breakdown if applicable), payment status, and booking status. If the admin included a note, it appears in the email body.

Email delivery relies on `wp_mail()`, which should be routed through an SMTP provider via the **WP Mail SMTP** plugin. Without proper SMTP configuration, emails may go to spam or fail silently.

### 7b. Promo Mailer

The bulk email tool in `mailer.php` sends to subscribers and past booking guests. It uses the shared email template from `cwc_get_email_template()` (defined in the theme) and sends in batches of 5 via AJAX action `cwc_mailer_send_batch`.

### 7c. Email and audit logging

Every email send attempt is recorded in the booking's `_cwc_bk_email_log` meta. Every status change, payment change, and email resend is recorded in `_cwc_bk_audit_log`. Both are JSON-encoded arrays. These logs are permanent and cannot be cleared from the admin UI.

---

## 8. AJAX endpoints

All AJAX handlers require the `cwc_dash_nonce` nonce (generated on the dashboard page) and `manage_options` capability unless otherwise noted.

| Action | File | Auth required | Description |
|---|---|---|---|
| `cwc_update_booking_status` | `bookings-cpt.php` | Yes | Change booking status with optional email |
| `cwc_update_payment_status` | `bookings-cpt.php` | Yes | Change payment status |
| `cwc_resend_booking_email` | `bookings-cpt.php` | Yes | Resend the last status email |
| `cwc_toggle_physical_room_status` | `bookings-cpt.php` | Yes | Manually block/release a physical unit |
| `cwc_check_room_availability` | `bookings-cpt.php` | No (public) | Check if a room is available for a date range |
| `cwc_get_booked_dates` | `bookings-cpt.php` | No (public) | Get all fully-booked dates for a room (365-day window) |
| `cwc_validate_coupon` | `coupons.php` | No (public) | Validate a coupon code at checkout |
| `cwc_get_bookings_events` | `dashboard.php` | Yes | Return bookings as FullCalendar event objects |
| `cwc_get_booking_details` | `dashboard.php` | Yes | Fetch full details for one booking (used by the calendar modal) |
| `cwc_mailer_send_batch` | `mailer.php` | Yes | Send a batch of promo emails |

---

## 9. Payment gateway

Payment is handled via **PayMongo** (Philippine payment gateway). The integration lives in the child theme (`themes/child-cwcwake/inc/` and `functions.php`). API keys are defined in `wp-config.php` as `PAYMONGO_PUBLIC_KEY` and `PAYMONGO_SECRET_KEY`.

- **Test mode:** use `pk_test_` / `sk_test_` keys.
- **Live mode:** use `pk_live_` / `sk_live_` keys.

The booking form in the theme collects payment intent from PayMongo, processes it, and on success creates the `cwc_booking` post with all metadata.

---

## 10. AI chat assistant

The theme includes a floating chat widget powered by the **Groq** API. Implementation: `themes/child-cwcwake/inc/chat-assistant.php`.

- Uses the `CWC_GROQ_API_KEY` constant from `wp-config.php`.
- Default model: `llama-3.3-70b-versatile` (overridable via `CWC_GROQ_CHAT_MODEL`).
- The chat endpoint is a WordPress REST route registered by the theme.
- If the API key is missing or empty, the endpoint silently returns nothing — the widget will not function but will not error.

---

## 11. Third-party plugins

| Plugin | Purpose | Configuration notes |
|---|---|---|
| **Rank Math SEO** | Search engine optimization | Configure per-page meta titles and descriptions. See [`docs/plugins/seo-by-rank-math.md`](plugins/seo-by-rank-math.md). |
| **WP Mail SMTP** | Routes `wp_mail()` through a reliable SMTP provider | Must be configured for booking emails to deliver. See [`docs/plugins/wp-mail-smtp.md`](plugins/wp-mail-smtp.md). |
| **LiteSpeed Cache** | Page caching and performance optimization | Purge cache after content updates. See [`docs/plugins/litespeed-cache.md`](plugins/litespeed-cache.md). |
| **WordPress Importer** | One-time content import tool | Used during initial setup. No ongoing configuration needed. |

---

## 12. Database and storage

### 12a. Schema

The plugin uses **no custom database tables**. All data is stored in standard WordPress tables:

- `wp_posts` — accommodation, booking, and coupon records
- `wp_postmeta` — all metadata for the above (see Section 5 for key listings)
- `wp_options` — plugin settings:

| Option key | Contains |
|---|---|
| `cwc_icon_pool` | Icon library (slug → filename/attachment-ID map) |
| `cwc_dynamic_amenities` | Amenities catalogue (slug → label + icon) |
| `cwc_dynamic_beds` | Bed types catalogue |
| `cwc_dynamic_inclusions` | Inclusions catalogue |
| `cwc_global_policies` | JSON-encoded array of house rule rows |
| `cwc_newsletter_subscribers` | Array of subscriber records |
| `cwc_last_tx_id` | Last sequential transaction ID number (for `TX-XXXXX` generation) |

### 12b. Backups

The database sync file `db-sync.sql` lives at the Git root (`wp-content/db-sync.sql`). To export:

```bash
wp db export wp-content/db-sync.sql
```

To import:

```bash
wp db import wp-content/db-sync.sql
```

On production, ensure daily automated backups of the database, particularly `wp_posts` and `wp_postmeta` which contain all booking records and revenue data.

---

## 13. Version control

### 13a. What is tracked

The Git repository root is `wp-content/`. The repository tracks:

- `themes/child-cwcwake/` — the child theme
- `plugins/` — all plugins including `cwc-accommodations`
- `uploads/` — all media files
- `docs/` — this documentation
- `db-sync.sql` — database snapshot
- `designs/` — design assets

### 13b. What is excluded

Check `.gitignore` at `wp-content/.gitignore`. Notable exclusions:

- `litespeed/` — cache files
- Any IDE or editor configuration files

### 13c. Workflow

1. Make changes locally.
2. Export the database: `wp db export wp-content/db-sync.sql`.
3. Commit and push: `git add . && git commit -m "description" && git push origin main`.
4. Collaborators pull and import: `git pull origin main && wp db import wp-content/db-sync.sql`.

---

## 14. Deployment and hosting

### 14a. Server requirements

- PHP 7.4+ (8.1+ recommended)
- MySQL 8.0+
- Web server with LiteSpeed or Nginx
- HTTPS enabled

### 14b. Activation sequence

When deploying to a new environment:

1. Install WordPress.
2. Clone the repository into `wp-content/`.
3. Import the database.
4. Activate the `child-cwcwake` theme. The parent theme (Twenty Twenty-Five) must be present.
5. Activate the `cwc-accommodations` plugin. On activation, it registers the CPT and flushes rewrite rules automatically.
6. Configure `wp-config.php` with the required constants (PayMongo keys, Groq key).
7. Configure WP Mail SMTP with production SMTP credentials.
8. Verify the site: check room pages load, booking form works, dashboard is accessible.

### 14c. Theme dependency

The plugin shows an admin notice if the `child-cwcwake` theme is not active:

> *"CWC Accommodations: The room data layer is active, but the matching theme (child-cwcwake) is not. Single-room pages will render blank until the theme is activated."*

This is a warning, not an error. The plugin's data layer works with any theme, but only `child-cwcwake` knows how to render room pages.

---

## 15. Security notes

- **Nonce verification:** All AJAX endpoints verify a nonce (`cwc_dash_nonce` for dashboard actions, `cwc_mailer_nonce` for the mailer, `cwc_coupon_save` for coupon saves, `cwc_acc_settings_save` for settings, `cwc_global_policies_save` for policies).
- **Capability checks:** Dashboard and admin operations require `manage_options`. Public-facing endpoints (availability check, coupon validation, booked dates) do not require authentication.
- **Input sanitization:** All `$_POST` data is processed through `sanitize_text_field()`, `sanitize_key()`, `absint()`, `sanitize_textarea_field()`, or `wp_kses_post()` before use.
- **No `uninstall.php`:** The plugin intentionally has no uninstall handler. Deactivation does not delete any data. Room and booking records persist in the database after deactivation.
- **API keys:** PayMongo and Groq keys must be stored in `wp-config.php`, never in the database or in version-controlled files.

---

## 16. Maintenance procedures

### 16a. Plugin updates

Update third-party plugins (Rank Math, WP Mail SMTP, LiteSpeed Cache) through the WordPress admin. The `cwc-accommodations` plugin and the `child-cwcwake` theme are updated through Git — do not update them via the WordPress updater.

### 16b. Monitoring

- Check `Tools → Site Health` for critical warnings.
- Monitor the PHP error log on the server for booking-related failures.
- The `plugins/cwc-accommodations/scratch/` directory may contain debug files — review periodically.

### 16c. Periodic tasks

| Task | Frequency | How |
|---|---|---|
| Review Site Health | Monthly | Tools → Site Health |
| Check for plugin updates | Monthly | Dashboard → Updates |
| Verify booking email delivery | After any SMTP config change | Send a test from WP Mail SMTP, then confirm a booking and check the guest inbox |
| Database backup verification | Monthly | Export via `wp db export`, verify the file is complete |
| Audit subscriber list | Quarterly | Accommodations → Subscribers — remove invalid or unsubscribed addresses |

---

## 17. Known limitations and future considerations

- **Availability query performance:** The current availability check loops through all published bookings. At high volume (hundreds of bookings), this query will slow down. A future optimization would move availability data into a dedicated lookup table or add meta-query indexes.
- **No REST API for bookings:** The `cwc_booking` CPT has `show_in_rest` set to false. External integrations (mobile apps, third-party dashboards) would require a custom REST controller.
- **Single-origin booking:** The system assumes one physical location. Multi-property support would require extending the CPT with a location taxonomy.
- **No automated status transitions:** Bookings do not auto-complete after checkout. An admin must manually mark bookings as "Completed." A scheduled event (WP-Cron) could automate this in the future.
- **Chat assistant dependency:** The AI chat widget relies on an external API (Groq). If the API is down or the key expires, the chat silently stops working. There is no fallback or error notification to the admin.

---

## 18. File reference (quick lookup)

| What you need | Where to find it |
|---|---|
| Room CPT registration and helpers | `plugins/cwc-accommodations/includes/cpt.php` |
| Room editor meta boxes | `plugins/cwc-accommodations/includes/metabox.php` |
| Booking CPT, status logic, availability | `plugins/cwc-accommodations/includes/bookings-cpt.php` |
| Booking Dashboard (all tabs) | `plugins/cwc-accommodations/includes/dashboard.php` |
| Dashboard CSS | `plugins/cwc-accommodations/includes/dashboard-assets/dashboard.css` |
| Dashboard JS | `plugins/cwc-accommodations/includes/dashboard-assets/dashboard.js` |
| Coupon CPT and validation | `plugins/cwc-accommodations/includes/coupons.php` |
| Global Policies admin page | `plugins/cwc-accommodations/includes/policies.php` |
| Amenities / Icons / Inclusions settings | `plugins/cwc-accommodations/includes/settings.php` |
| Promo Mailer | `plugins/cwc-accommodations/includes/mailer.php` |
| Subscriber management | `plugins/cwc-accommodations/includes/subscribers.php` |
| Legacy migration tool | `plugins/cwc-accommodations/includes/migrate.php` |
| Blog post seeder | `plugins/cwc-accommodations/includes/blog-seeder.php` |
| Theme functions and booking form | `themes/child-cwcwake/functions.php` |
| Theme design tokens | `themes/child-cwcwake/theme.json` |
| Custom blocks | `themes/child-cwcwake/blocks/` |
| AI chat assistant | `themes/child-cwcwake/inc/chat-assistant.php` |
| Coding standards | `themes/child-cwcwake/STANDARDS.md` |

---

## 19. Troubleshooting and common "gotchas"

### Missing Logo/Favicon (Tab Icon)
If the favicon in the browser tab is missing after a database import:
- **Reason:** The `site_icon` setting stores an **Attachment ID**. If that ID does not match the imported `uploads` folder state, WordPress loses the reference.
- **Fix:** Re-select the icon in **Appearance → Customize → Site Identity**.
- **Prevention:** Always run a fresh `wp db export` on the source site *after* making structural changes like updating logos.

### Permalinks returning 404
If custom routes (like rooms or specific pages) fail after migration:
- **Fix:** Go to **Settings → Permalinks** and click **Save Changes**. This forces WordPress to regenerate its internal `.htaccess` or Nginx rewrite rules.

### "Not a WordPress installation" Error
If running `wp` commands in the Site Shell:
- **Fix:** Ensure you are in the `app/public` folder. Run `cd app/public` before running `wp` or `mysql` commands.

---

**Last updated:** 2026-05-15
