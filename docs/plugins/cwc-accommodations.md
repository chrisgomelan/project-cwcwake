# CWC Wake — Accommodations

[← Back to overview](../README.md)

This plugin powers **rooms** on the site: listings, detail pages, booking-related tools, coupons, and related admin screens. Menu labels match the WordPress admin.

## Where to work

In the left sidebar, open **Accommodations**. Under it you will typically see:

| Menu item | Use it to… |
|-----------|------------|
| **All Rooms** | See every room, open one to edit, trash or restore. |
| **Add New Room** | Create a new room from scratch. |
| **Coupons** | Create discount codes used with bookings or mailouts (title = code; extra fields in the editor). |
| **Amenities & Icons** | Manage the **icon library** and labels used for room amenities. |
| **Inclusions** | Manage inclusion options used across rooms (when your team uses this feature). |
| **Global Policies** | Edit shared house rules text that appears across room pages (one place updates all rooms). |
| **Dashboard** | Booking-related overview (tabs such as bookings, guests, availability—use what your team trained you on). |
| **Subscribers** | View or manage newsletter subscribers (admin-level access). |
| **Promo Mailer** | Send announcements or promos to subscribers and past guests (admin-level access). |

Some items appear only for **Administrator** (or similar) accounts.

## Editing a room

1. **Accommodations → All Rooms** → click the room title.
2. Fill **title**, **room cover image**, and the **meta boxes** your team uses (price, capacity, availability, amenities, gallery, etc.). Those fields drive the public room layout; the main editor area is often used only for optional extras (promos, embeds).
3. Use **Preview**, then **Publish** or **Update**.
4. Open the live room link. Public URLs normally look like:  
   `https://yoursite.com/accommodations/room-name/`  
   (the middle segment is **`accommodations`**; the last part comes from the room title or slug).

## If a room page looks blank or broken

The site expects the correct **theme** (child theme) to render room templates. If you see an admin notice that the theme does not match, **do not** try to fix code—note the message and contact technical support.

## Coupons, mailer, subscribers

These affect money and email. Follow internal procedures: who approves sends, how codes are named, and when to archive old coupons. When something fails (email bounce, wrong discount), capture a screenshot and escalate if you cannot fix it from the screen itself.

## Related

- [Core workflows](../03-core-workflows.md) — general WordPress editing.
- [When something goes wrong](../04-when-something-goes-wrong.md)
- [WP Mail SMTP](wp-mail-smtp.md) — if outbound mail misbehaves.
- [LiteSpeed Cache](litespeed-cache.md) — if you updated a room but still see the old version.
