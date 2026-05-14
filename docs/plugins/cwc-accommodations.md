# CWC Wake — Accommodations

[← Back to overview](../README.md)

This plugin powers **rooms** on the site: listings, public room pages, booking tools, coupons, email to guests, and shared settings. In the WordPress admin, everything listed below lives under the left sidebar item **Accommodations** (house icon). Submenu order can vary slightly by WordPress version, but the labels match the dashboard.

**Who can see what:** Several screens require **Administrator** (or another role with **manage site options** permission). If a menu item is missing for you, ask an administrator—it is permission-based, not a bug.

---

## Accommodations (top-level menu)

This is the entry point for all room content. Click **Accommodations** to expand the submenu. You do not “configure” the top-level item itself; you open one of the items below.

---

## All Rooms

- **What it is:** The list of every **room** (each row is one accommodation).
- **Use it to:** Find a room by title, open it to edit, see **Draft** vs **Published**, move a room to **Trash**, or restore from trash.
- **Typical flow:** **Accommodations → All Rooms** → click the room **title** → edit screen opens.
- **Tip:** Use the search box on the list screen when you have many rooms.

---

## Add New Room

- **What it is:** Creates a **new** room record from a blank form.
- **Use it to:** Add a new accommodation when marketing opens a new unit or renames a listing.
- **Typical flow:** **Accommodations → Add New Room** → enter **title** → set **Room cover image** and room fields in the meta boxes → **Preview** → **Publish**.
- **Tip:** The public web address is based on the room title/slug and usually looks like `/accommodations/your-room-name/` (see [Editing a room](#editing-a-room)).

---

## Coupons

- **What it is:** A list of **discount coupons** (each coupon is its own entry). The coupon **title** is treated as the **code** guests type at checkout.
- **Use it to:** Create fixed-amount or percentage discounts, set expiry and usage limits, and retire old codes.
- **Typical flow:** **Accommodations → Coupons → Add New Coupon** → set the code as the title → fill **Coupon Settings** in the editor → **Publish**.
- **Caution:** Coupons affect **money** and reporting. Follow your team’s rules for who may create codes and how codes are named.

---

## Amenities & Icons

- **What it is:** A **settings** screen (page title in the admin: **Amenities & Icon Library**). It controls shared catalogues used when editing rooms—not individual room text on its own.
- **Who sees it:** **Administrator** / `manage_options` only.
- **Sections on the screen:**
  - **Icon Library (The Pool)** — Short names (**slugs**) and **SVG icons** (upload or pick from media). Icons can be reused for amenities, bed types, and policy rows that reference icons.
  - **Amenities Catalogue** — The list of amenities that appear as **checkboxes** on each room. Each row has a slug, a **label** (e.g. “Free WiFi”), and an icon chosen from the pool.
  - **Bed Types Catalogue** — Bed types that can be assigned to rooms, each with slug, label, and icon from the pool.
- **Bottom of the screen:** **Save All Settings** saves the catalogues. **Seed Sample Blog Posts** is optional tooling for developers/demo content—do not click unless your team asked you to.
- **Tip:** Change the catalogues only when you understand that **existing rooms** may reference old slugs; coordinate with your team.

---

## Inclusions

- **What it is:** A separate settings screen (page title: **Room Inclusions**). It defines **inclusion** options (slug + display label) that can be turned on per room. On the public site they show as **text-style pills/benefits**, not the same as amenity icons.
- **Who sees it:** **Administrator** / `manage_options` only.
- **Use it to:** Add, edit, or remove inclusion rows, then **Save Inclusions**.
- **Tip:** Match wording to what legal/marketing approves; inclusions are guest-facing promises.

---

## Global Policies

- **What it is:** One place to edit **shared house rules / policies** (icon + name + description rows) that **all** room pages can read from. Updating here updates every room that uses the shared policies—no need to edit each room for the same rule.
- **Who sees it:** **Administrator** / `manage_options` only.
- **Use it to:** Keep cancellation, quiet hours, safety, or resort-wide rules consistent.
- **Tip:** If only one policy line is wrong, edit that row rather than deleting the whole list without a backup plan.

---

## Dashboard

- **What it is:** **Booking Dashboard** — operations view for reservations, money, calendar, rates, and analytics.
- **Who sees it:** **Administrator** / `manage_options` only.
- **Tabs inside the screen** (links along the top of the dashboard page):

| Tab | What it is for (plain language) |
|-----|--------------------------------|
| **Calendar** | Visual overview of bookings on a calendar; default tab when you open the dashboard. |
| **Bookings** | List/search **all bookings**, statuses, and actions your workflow allows (confirm, cancel, notes—follow training). |
| **Payments** | Payment-related view for tracking paid/unpaid and related records (use as your team trained you). |
| **Room Units Tracking** | Operational tracking tied to **room units** and occupancy-style views. |
| **Availability** | See or adjust how availability is presented for planning (follow internal procedure). |
| **Rates Management** | Edit **rate tables** and pricing categories used by the booking system—financially sensitive. |
| **Analytics** | Summaries such as bookings **by room** and other metrics for reporting. |

- **Tip:** Date filters on some tabs limit what you see—set the range, click **Apply**, then read the tables.

---

## Subscribers

- **What it is:** List of **newsletter subscribers** stored by the plugin (emails collected from the site).
- **Who sees it:** **Administrator** / `manage_options` only.
- **Use it to:** Review subscriber emails, remove addresses when someone unsubscribes or requests deletion (follow privacy policy), export if your team has a process.
- **Caution:** Subscriber data is **personal**—do not copy lists to personal devices or share outside approved tools.

---

## Promo Mailer

- **What it is:** **Promo Mailer** — tool to send **bulk email** (announcements, promos) to subscribers and past guests; may reference **coupons** you created.
- **Who sees it:** **Administrator** / `manage_options` only.
- **Use it to:** Compose approved campaigns, send tests first, then send to the intended audience per your communications checklist.
- **Caution:** Mass email affects reputation and spam filters. Always use **test sends**, correct **from** identity, and only content that legal/marketing approved. If delivery fails, see [WP Mail SMTP](wp-mail-smtp.md) and escalate if needed.

---

## Editing a room

1. **Accommodations → All Rooms** → click the room **title**.
2. Set **Room cover image** (featured image for the room).
3. Use the **room meta boxes** (price, capacity, availability, amenities checklist, gallery, bed type, inclusions, etc.)—these drive the public room layout. The big **editor** area below is often for **optional** extras (promos, embedded video) that appear in addition to the standard layout.
4. Click **Preview**, then **Publish** or **Update**.
5. Open the **live** room URL and confirm:  
   `https://yoursite.com/accommodations/room-name/`  
   The middle path segment is **`accommodations`**; the last part comes from the room’s **slug** (usually derived from the title).

---

## If a room page looks blank or broken

The public room layout comes from the active **theme** (CWC Wake child theme and templates). If WordPress shows a notice that the **room data layer is active but the theme does not match**, do **not** edit code—copy the notice, note the URL, and contact technical support.

---

## Related

- [Core workflows](../03-core-workflows.md) — general WordPress pages and media.
- [When something goes wrong](../04-when-something-goes-wrong.md)
- [WP Mail SMTP](wp-mail-smtp.md) — outbound email problems.
- [LiteSpeed Cache](litespeed-cache.md) — stale page after an update.
