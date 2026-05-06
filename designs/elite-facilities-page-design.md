# CWC Wake — Elite Facilities Page Design Specification

This document details the visual requirements and block structures for the "Elite Facilities" page. It will serve as the reference for building custom blocks and assembling the page template.

---

## 1. Hero / Banner Section

- **Block**: Reuse the existing `cwc/hero-section` block.
- **Customization**: Update the background image and text content.
- **Background Image**: `/wp-content/uploads/2026/04/elite-facilities-hero-bg.webp`
- **Breadcrumbs**: Include breadcrumbs below the hero content.

---

## 2. Pro-Standard Facilities Section

**Block Type**: Reuse `cwc/coaching-section` (Beginner-Friendly Coaching block from Water Sports).

### Differences from Water Sports variant

- **Title Accent**: NOT italic — accent word is rendered in normal weight (non-italic).
- **Accent Color**: `#395144` (forest green).
- **Info Card Title Color**: `#1A1A1A` (dark, not the Water Sports blue `#0096C7`).

### Layout

- **Left Column**: Overlapping images.
- **Right Column**: Title, description, sub-heading, and stacked info cards.

### Title

- **Font**: `Archivo`, Bold, Italic, `64px`, LH `100%`, Uppercase
- **Color**: `#1A1A1A`
- **Accent word** (e.g., `"PRO-STANDARD"`): Colored `#395144`. **Non-italic accent** (override font-style to normal for accent `<em>`).

### Info Card Title Typography

- **Font**: `Sora`, Bold, `20px`, LH `100%`
- **Color**: `#1A1A1A`

### Info Card Description Typography

- **Font**: `Archivo`, Regular, `16px`, LH `100%`
- **Color**: `#000000`

### Image

- `/wp-content/uploads/2026/04/pro-standard-pickleball.webp`

---

## 3. Epic Team & Competitions Section

**Block Type**: Reuse `cwc/land-feature-split` (the same block used for **Epic Team & Community** in Land Activities — reversed layout).

- **Variation**: **Reversed Layout** (image on the left, text panel on the right).
- **Image Source**: `/wp-content/uploads/2026/04/pro-standard-pickleball.webp`
- **Title**: e.g., `EPIC TEAM & COMPETITIONS`
- **Background**: Dark doodle panel (same as Land Activities `#1A1A1A` + doodle-bg.webp overlay).

---

## 4. Performance Training Zones Section

**Block Type**: Reuse `cwc/feature-banner` (same block as **Master the Dirt** / **Inflatable Aqua Adventure**).

### Visual Differences

- **Accent word**: `"PERFORMANCE"`
- **Accent Color**: `#ED5B26` (orange-red)

### Item Cards (Below the Banner Header)

This section contains a 4-card grid beneath the header. Each card has:

- **Overlay per card**: `#00000066` (40% dark)
- **Border Radius**: `30px`

#### Card Title Typography

- **Font**: `Sora`, Bold, `32px`, LH `100%`, Uppercase
- **Color**: `#F5F1EB`

#### Card Description Typography

- **Font**: `Archivo`, Medium (`500`), `20px`, LH `100%`
- **Color**: `#F5F1EB`

#### Card Items (in order)

| # | Title | Image |
|---|-------|-------|
| 1 | STRENGTH TRAINING ZONE | `/wp-content/uploads/2026/04/strength-training-zone.webp` |
| 2 | CARDIO & ENDURANCE ZONE | `/wp-content/uploads/2026/04/cardio-endurance-zone.webp` |
| 3 | RECOVERY & MOBILITY AREA | `/wp-content/uploads/2026/04/recovery-mobility-area.webp` |
| 4 | CLIMATE-CONTROLLED TRAINING | `/wp-content/uploads/2026/04/climate-controlled-training.webp` |

---

## 5. FIBA Outdoor Court Section

**Block Type**: Reuse `cwc/about-empowering` (the **Empowering the Region** block from the About page).

### Visual Differences from About Page Variant

- **Bracket / Accent Color**: `#ED5B26` (orange-red) — replaces the default teal/blue.
- **Icon Circle Background**: `#FF6B3580` (50% opacity orange) — replaces `#00AFB933`.

### Image

- `/wp-content/uploads/2026/04/fiba-outdoor-court.webp`

### Info Cards

Same card structure as About's Empowering the Region:
- Background: `#F9FFFF`
- Box-shadow: `0px 4px 6px 0px #00000040`
- Border-radius: `24px`
- Padding: `24px 32px`
- Gap: `28px`

#### Card Title Typography

- **Font**: `Sora`, Bold, `24px`, LH `100%`
- **Color**: `#000000`

#### Card Description Typography

- **Font**: `Archivo`, Regular, `20px`, LH `100%`
- **Color**: `#000000`

---

## 6. Host Your Competition Section

**Block Type**: Reuse `cwc/about-certified` (the **Premium Rental Equipment** block from Water Sports / **Certified Safe** block from About page).

### Visual Differences

- **Accent Color**: `#FF6B3580` (50% opacity orange-red) — replaces default.

### Image Cards

Each card has:

- **Image Overlay**: `#00000066`
- **Border-radius**: `30px`

#### Card Title Typography

- **Font**: `Sora`, Bold, `32px`, LH `100%`, Uppercase
- **Color**: `#F5F1EB`

#### Card Description Typography

- **Font**: `Archivo`, Medium (`500`), `20px`, LH `100%`
- **Color**: `#F5F1EB`

#### Card Items (in order)

| # | Title | Description | Image |
|---|-------|-------------|-------|
| 1 | EVENT HOSTING | Full event management and venue setup. | `/wp-content/uploads/2026/04/event-hosting.webp` |
| 2 | REFEREE SUPPORT | Certified officiating staff for all sports. | `/wp-content/uploads/2026/04/referee-support.webp` |
| 3 | NEARBY ATHLETE | Accommodations steps from the competition floor. | `/wp-content/uploads/2026/04/nearby-athlete.webp` |

---

## 7. Professional Edge Section

**Block Type**: Reuse `cwc/why-stay` (the **Why Stay at CWC?** block from the Accommodations page).

### Visual Differences

- **Heading and Description**: Centered (not left-aligned).
- **Accent word**: `"PROFESSIONAL"` — colored `#ED5B26` (orange-red).
- **Icon Circle Background**: `#FF6B3580` (50% opacity orange-red).
- **Icon and card content**: Centered.

### Icons

| # | Icon Path |
|---|-----------|
| 1 | `themes/child-cwcwake/assets/images/all-weather-access.svg` |
| 2 | `themes/child-cwcwake/assets/images/tournament-grade.svg` |
| 3 | `themes/child-cwcwake/assets/images/pro-shop.svg` |

#### Icon Circle

- **Width / Height**: `100px` × `100px`
- **Background**: `#FF6B3580`
- **Layout**: Flex center

#### Item Title Typography

- **Font**: `Sora`, Bold, `24px`, LH `100%`
- **Color**: `#000000`
- **Alignment**: Center

#### Item Description Typography

- **Font**: `Archivo`, Regular, `20px`, LH `100%`
- **Color**: `#000000`
- **Alignment**: Center

---

## 8. Before Footer CTA Section

- **Block**: Reuse the `cwc/before-footer-cta` block.

### Background

- **Linear Gradient**:
  ```
  180deg,
  #1e293b 0%,    /* Dark Slate / Indigo */
  #0ea5e9 50%,   /* Bright Sky Blue */
  #0891b2 100%   /* Deep Cyan */
  ```
- **Background Image**: `/wp-content/uploads/2026/04/elite-facilities-before-footer-bg.webp`
