# Gallery & Accommodations — Banner & Breadcrumbs Design Spec

Applies to: **Gallery** page, **Accommodations** page

---

## Banner Layout

| Property  | Value     |
| --------- | --------- |
| Width     | `1920px`  |
| Height    | `561px`   |
| Top       | `115px`   |
| Rotation  | `0deg`    |
| Opacity   | `1`       |

> **Note:** The `115px` top offset accounts for the fixed navigation bar height. The banner sits directly beneath the nav.

---

## Banner H1

| Property       | Value     |
| -------------- | --------- |
| Font Family    | `Sora`    |
| Font Weight    | `700`     |
| Font Style     | Bold      |
| Font Size      | `72px`    |
| Line Height    | `100%`    |
| Letter Spacing | `0%`      |
| Text Align     | `center`  |
| Color          | `#F5F1EB` |

---

## Banner Description

| Property       | Value     |
| -------------- | --------- |
| Font Family    | `Archivo` |
| Font Weight    | `500`     |
| Font Style     | Medium    |
| Font Size      | `20px`    |
| Line Height    | `100%`    |
| Letter Spacing | `0%`      |
| Text Align     | `center`  |
| Color          | `#FFFFFF` |

---

## Breadcrumbs Container

The breadcrumbs (and all other elements on this section) are nested inside a **constrained layout container** that uses `tropical-bg` as its background image. All inner elements are positioned relative to this container.

| Property         | Value                             |
| ---------------- | --------------------------------- |
| Background       | `tropical-bg` (image asset)       |
| Layout           | Constrained (fixed width/height)  |
| Width            | `1920px`                          |
| Height           | `561px`                           |

---

## Breadcrumbs Typography

### Default / Inactive State

| Property       | Value      |
| -------------- | ---------- |
| Font Family    | `Inter`    |
| Font Weight    | `600`      |
| Font Style     | Semi Bold  |
| Font Size      | `20px`     |
| Line Height    | `100%`     |
| Letter Spacing | `0%`       |
| Color          | `#1A1A1A`  |

### Chevron Separator

| Property | Value     |
| -------- | --------- |
| Color    | `#1A1A1A` |

### Active / Current Page State

| Property       | Value      |
| -------------- | ---------- |
| Font Family    | `Sora`     |
| Font Weight    | `600`      |
| Font Style     | SemiBold   |
| Font Size      | `20px`     |
| Line Height    | `100%`     |
| Letter Spacing | `0%`       |
| Color          | `#0096C7`  |

---

## Notes

- The breadcrumbs sit inside the banner container and should be positioned relative to it.
- The active breadcrumb item uses a different font family (`Sora`) compared to the inactive items (`Inter`).
- The chevron color matches the inactive text color (`#1A1A1A`).
- All other page-specific elements (page title, subtitle, etc.) are also children of this same constrained container.

