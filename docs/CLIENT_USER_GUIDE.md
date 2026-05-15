# Client Website User Guide

**Project:** CWC Wake — Camsur Watersports Complex  
**Platform:** WordPress, custom booking system  
**Plugin:** `cwc-accommodations` (manages rooms, bookings, coupons, emails, and policies)  
**Theme:** `child-cwcwake` (child of Twenty Twenty-Five)

---

## 1. Logging in

1. Open your browser and go to `https://yoursite.com/wp-admin/`.
2. Enter your username and password. Each staff member should have their own account.
3. If you cannot log in, click "Lost your password?" on the login screen. If that does not work, contact your technical support.

Do not share login credentials between staff. If a new person needs access, ask an administrator to create a separate account for them.

---

## 2. The WordPress dashboard

After logging in you will see the main dashboard. The left sidebar is your primary navigation. The areas you will use most often are:

| Sidebar item | What it contains |
|---|---|
| **Accommodations** | All room listings, the Booking Dashboard, coupons, policies, amenities, mailer, and subscribers |
| **Pages** | Static content such as "About Us," "Contact," and legal pages |
| **Posts** | Blog entries, news, and information articles |
| **Media** | All uploaded images, videos, and documents |

The **Accommodations** menu is where most of your daily work happens. It expands into several sub-items described in the sections below.

---

## 3. Managing rooms

### 3a. Viewing all rooms

**Accommodations → All Rooms** shows every room listing on the site. Each row is one accommodation type. You can see its title, status (Draft or Published), and open it for editing.

### 3b. Adding a new room

**Accommodations → Add New Room** opens a blank room form. Fill in:

- **Title** — the name guests will see (e.g. "Deluxe Suite").
- **Room cover image** — the main photo shown on the website. Set this using the Featured Image panel.
- **Room meta boxes** — the fields below the editor where you set price, capacity, amenities (checkboxes), bed type, gallery images, and inclusions.

Click **Preview** to check the page, then **Publish** when ready. The public URL will be `https://yoursite.com/accommodations/your-room-name/`.

### 3c. Editing an existing room

**Accommodations → All Rooms** → click the room title → make changes → **Update**.

After updating, open the live page in a new tab to confirm your changes appear correctly. If the page still shows old content, clear the LiteSpeed cache (see Section 14).

---

## 4. Amenities, inclusions, and bed types

These are shared catalogues that feed into the room editor. Changes here affect all rooms that reference them.

| Screen | Location | What it controls |
|---|---|---|
| **Amenities & Icons** | Accommodations → Amenities & Icons | The icon library and the list of amenities that appear as checkboxes when editing a room |
| **Inclusions** | Accommodations → Inclusions | Text-style benefits shown on the public room page (e.g. "Free Wakeboard for 4 Guests") |
| **Bed Types** | Part of the Amenities & Icons screen | Bed options assignable to each room (e.g. "Queen Bed," "Twin Bed") |

These screens are restricted to **Administrators** only. If you do not see them in the sidebar, your account does not have the required permission — this is not a bug.

When editing these catalogues, coordinate with your team. Changing a slug that existing rooms reference can cause that amenity or inclusion to stop displaying.

---

## 5. Global policies

**Accommodations → Global Policies**

This screen manages house rules that appear on every room page: check-in time, check-out time, smoking policy, children's policy, and similar. Each policy row has an icon, a name, and a description.

Updating a policy here updates it on every room page at once. You do not need to edit each room individually.

The current default policies are:

| Policy | Default value |
|---|---|
| Check-in | From 02:00 PM to 09:00 PM |
| Check-out | Until 12:00 PM |
| Breakfast | Available (may be included in selected rooms) |
| Reception Hours | Open until 09:00 PM |
| Children and beds | Infants (0–3 yrs): free. Children (4–8 yrs): extra bed charge applies. Guests (9+): considered adults. |
| No age restriction | Guests of all ages are welcome. |
| Smoking | Smoking is not allowed. |

---

## 6. The Booking Dashboard

**Accommodations → Dashboard**

This is the central hub for managing reservations. It is restricted to **Administrators** only. The dashboard has seven tabs:

| Tab | Purpose |
|---|---|
| **Calendar** | Visual calendar view of all bookings. Default tab when you open the dashboard. Color-coded: amber = pending, green = confirmed, red = cancelled. |
| **Bookings** | Full list of all bookings with search, status filters, and action buttons (confirm, cancel, complete, resend email). |
| **Payments** | Payment tracking view showing paid/unpaid status for each booking. |
| **Room Units Tracking** | Shows each room type's physical units and which are occupied, available, or manually blocked for a selected date range. |
| **Availability** | Overview of room occupancy percentages and upcoming check-ins within the next 14 days. |
| **Rates Management** | Edit rate tables and pricing categories. Financially sensitive — follow your team's approval process before changing rates. |
| **Analytics** | Summary statistics: bookings by room, total revenue, guest counts. |

### 6a. Updating a booking status

1. Go to the **Bookings** tab.
2. Find the booking by searching for the guest name or reference number (format: `CWC-XXXXXX`).
3. Click the status action button.
4. In the modal that appears, select the new status: **Pending**, **Confirmed**, **Cancelled**, or **Completed**.
5. Optionally check "Send email notification to guest" and add an admin note.
6. Click **Update**.

The system logs every status change with a timestamp, the admin who made it, and any note provided. This audit trail is stored permanently.

### 6b. Updating payment status

On the **Payments** tab, you can change a booking's payment status between: **Unpaid**, **Paid**, **Failed**, or **Refunded**. This change is also logged in the audit trail.

### 6c. Resending an email

If a guest did not receive their confirmation or status email, you can resend it from the **Bookings** tab using the resend button. The system will send the same email template that corresponds to the booking's current status.

---

## 7. What the guest sees (the booking process)

The public booking process works in three steps from the guest's side:

1. **Room selection and dates.** The guest selects a room, a check-in date, and a check-out date. The system checks availability in real time — dates where all units of a room type are occupied are automatically disabled on the calendar.
2. **Guest details.** The guest enters their name, email, phone number, number of guests, and any special requests. If a coupon code is available, they enter it here.
3. **Payment and confirmation.** The guest completes payment (via PayMongo if configured). On success, the system creates the booking record, generates a reference number (`CWC-XXXXXX`), and sends a confirmation email.

After booking, the guest receives emails at each status change (pending, confirmed, cancelled, completed) if the admin enables email notification when updating the status.

---

## 8. Coupons

**Accommodations → Coupons**

Each coupon is its own entry. The coupon **title** is the code that guests type at checkout (e.g. `SUMMER25`).

### 8a. Creating a coupon

1. **Accommodations → Coupons → Add New Coupon**.
2. Set the title to the code you want guests to use.
3. In the **Coupon Settings** meta box, fill in:

| Field | Description |
|---|---|
| **Discount Type** | Fixed amount (in pesos) or percentage |
| **Amount** | The discount value |
| **Expiry Date** | After this date the coupon will not work |
| **Usage Limit** | Maximum number of times this coupon can be used. Leave empty for unlimited. |

4. Click **Publish**.

The **Usage Count** field shows how many times the coupon has been redeemed. You cannot edit this number — it increments automatically each time a guest applies the code.

Coupons affect revenue. Follow your team's rules on who may create or modify codes.

---

## 9. Subscribers

**Accommodations → Subscribers**

This screen lists email addresses collected from the newsletter signup on the public site. Subscriber data is stored in the WordPress option `cwc_newsletter_subscribers`.

You can:
- Review the list of subscribers.
- Remove an address if someone unsubscribes or requests deletion under your privacy policy.
- Export the list if your team has an approved process for external tools.

Subscriber data is personal. Do not copy it to personal devices or share it outside approved channels.

---

## 10. Promo Mailer

**Accommodations → Promo Mailer**

This tool sends bulk email to subscribers and past guests.

### 10a. Composing a campaign

1. Enter a **Subject** line.
2. Enter a **Banner Heading** (appears at the top of the email).
3. Write the **Message Content** using the editor.
4. Optionally select a **Coupon** from the dropdown — the coupon code will be displayed prominently in the email.
5. In the **Recipients** panel, check or uncheck individual recipients. Use the **All** / **None** buttons and the search box to manage the list. Recipients are labeled as "Subscriber" or "Past Guest."
6. Click **Send Promo Emails**.

The system sends in batches of 5 to avoid server timeouts. A progress bar tracks delivery. Always send a test to yourself first before sending to the full list.

Email delivery depends on the SMTP configuration. If emails fail, see the [WP Mail SMTP guide](plugins/wp-mail-smtp.md).

---

## 11. Managing pages

**Pages** in the left sidebar lists all static content: About Us, Contact, Terms of Use, Privacy Policy, and similar.

1. Click a page title to edit it.
2. Use the block editor to update text, images, and layout.
3. Click **Preview** to check, then **Update** to save.

Pages are not time-sensitive content — they do not appear in blog feeds or have categories.

---

## 12. Managing blog posts and information categories

**Posts** in the left sidebar is where you publish news, updates, and informational articles.

1. **Posts → Add New** to create a new article.
2. Select the appropriate **Category** so the post appears in the correct section of the site.
3. Set a **Featured Image** for the post.
4. Write the content, **Preview**, then **Publish**.

Use **Posts → Categories** to manage the category list. Categories organize content for visitors browsing the site.

---

## 13. Media library

**Media → Library** stores all uploaded files — images, videos, PDFs, and documents.

Guidelines:
- Optimize images before uploading. Large uncompressed images slow the site down.
- Use descriptive file names (e.g. `deluxe-suite-exterior.jpg` rather than `IMG_4532.jpg`).
- Reuse existing media where possible instead of uploading duplicates.
- Room photos should be set from the room editor's Gallery section and Featured Image panel, not pasted directly into the content area.

---

## 14. Basic maintenance

### 14a. Plugin and theme updates

Check **Dashboard → Updates** periodically. Update third-party plugins (Rank Math SEO, WP Mail SMTP, LiteSpeed Cache) when WordPress shows available updates. Do not update the `cwc-accommodations` plugin or the `child-cwcwake` theme through this screen — those are managed by your developer.

### 14b. Clearing the cache

If you update content but the public site still shows the old version:

1. Go to **LiteSpeed Cache → Toolbox → Purge**.
2. Click **Purge All**.
3. Refresh the public page.

See the [LiteSpeed Cache guide](plugins/litespeed-cache.md) for details.

### 14c. Site Health

**Tools → Site Health** shows warnings about security, performance, and configuration. Review this screen occasionally. If it shows a critical issue you do not understand, send a screenshot to your technical support.

---

## 15. Common issues

| Symptom | What to try | When to escalate |
|---|---|---|
| Page shows old content after editing | Clear LiteSpeed cache (Section 14b) | If clearing cache does not fix it |
| Room page is blank | Check that the `child-cwcwake` theme is active under Appearance → Themes | If the theme is active but pages are still blank |
| Guest did not receive email | Resend from the Bookings tab (Section 6c). Check WP Mail SMTP test send. | If test send also fails |
| Cannot access a menu item in the sidebar | Your account may not have Administrator permissions | Ask an administrator to check your role |
| White screen or "Critical Error" message | Stop. Do not click anything else. | Send the URL, a screenshot, and what you were doing to technical support immediately |
| Booking calendar shows wrong availability | Verify the room's physical units and inventory count in the room editor | If units and inventory look correct but availability is still wrong |

For all technical issues: note the URL, take a screenshot, describe what you were doing, and send this to your designated developer or support contact.

---

**Last updated:** 2026-05-15
