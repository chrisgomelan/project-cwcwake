# CWC Wake — Water Sports Page Design Specification

This document details the visual requirements and block structures for the "Water Sports" page. It will serve as the reference for building custom blocks and assembling the page template.

---

## 1. Hero / Banner Section

- **Block**: Reuse the existing Hero/Banner block from the Home Page.
- **Customization**: Update the background image only.
- **Background Image**: `/wp-content/uploads/2026/04/watersports-page-bg-banner.webp`

---

## 2. Pro-Level 6-Point System Section

**Layout**: Two-column — Left: Text content | Right: Overlapping images

### Left Column — Text Content

#### Section Title
- **Font**: `Sora`, Bold, `64px`, LH `100%`, Uppercase
- **Color**: `#1A1A1A`
- **Accent**: The words **"POINT SYSTEM"** are rendered in italic and colored `#0096C7`

#### Section Description
- **Font**: `Archivo`, Medium, `20px`, LH `100%`
- **Color**: `#1A1A1A`

#### Checklist Items
- **Icon**: `themes/child-cwcwake/assets/images/check-mark.svg`
- **Font**: `Sora`, SemiBold, `20px`, LH `100%`
- **Color**: `#1A1A1A`
- **Items**:
  1. High tension cable system
  2. Built for progression and tricks
  3. World-class obstacle layout

### Right Column — Overlapping Images

- **Image 1**: `/wp-content/uploads/2026/04/pro-level-6-1.webp`
- **Image 2**: `/wp-content/uploads/2026/04/pro-level-6-2.webp`
- **Layout**: Overlapping/staggered composition (as seen in screenshot reference)

---

## 3. Inflatable Aqua Adventure Section

- **Block Type**: Full-width image card with overlay
- **Dimensions**: Width `1711px` × Height `476px`
- **Border Radius**: `30px`
- **Background Image**: `/wp-content/uploads/2026/04/inflatable.webp`
- **Overlay**: `#00000066`
- **Opacity**: `1`

### Title — "INFLATABLE AQUA"
- **Font**: `Sora`, Bold, `64px`, LH `100%`, Uppercase
- **Color**: `#F5F1EB`

### Title Accent — "ADVENTURE"
- **Font**: `Archivo`, ExtraBold (`800`), Italic, `64px`, LH `100%`, Uppercase
- **Color**: `#F5F1EB`

### Description
- **Font**: `Archivo`, Medium, `20px`, LH `100%`
- **Color**: `#F5F1EB`

---

## 4. Diverse Water Disciplines Section

**Layout**: Center-aligned header + 2-column card grid below

### Section Title
- **Font**: `Sora`, Bold, `64px`, LH `100%`, Center, Uppercase
- **Color**: `#1A1A1A`
- **Accent**: The word **"WATER"** is colored `#0096C7`

### Section Description
- **Font**: `Archivo`, Medium, `20px`, LH `100%`, Center
- **Color**: `#1A1A1A`

### Cards (2 Columns)

- **Dimensions (each)**: Width `850px` × Height `374px`
- **Border Radius**: `30px`
- **Overlay**: `#00000066`

#### Card 1 — Wakeskating
- **Background Image**: `/wp-content/uploads/2026/04/wakeskating.webp`
- **Title**: `WAKESKATING`
- **Description**: Ride free without bindings and master balance, control, and stylish tricks on the water.

#### Card 2 — Kneeboarding
- **Background Image**: `/wp-content/uploads/2026/04/kneeboarding.webp`
- **Title**: `KNEEBOARDING`
- **Description**: A beginner-friendly ride that's smooth, stable, and perfect for building confidence and fun.

#### Card Title Typography
- **Font**: `Sora`, Bold, `48px`, LH `100%`, Uppercase
- **Color**: `#F5F1EB`

#### Card Description Typography
- **Font**: `Archivo`, Medium, `20px`, LH `100%`
- **Color**: `#F5F1EB`

---

## 5. Premium Rental Equipment Section

- **Block**: Reuse the `cwc-certified` block used on the About page.
- **Customization**: Update only the right-column images with the variant images below.

### Right Column Image Layout

#### First Image (Top-Left)
- **Source**: *(rental equipment image 1)*
- **Dimensions**: Width `344px` × Height `397px`
- **Position**: Top `100px`, Left `596px`

#### Second Image (Center / Middle)
- **Source**: *(rental equipment image 2)*
- **Dimensions**: Width `404px` × Height `500px`
- **Position**: Top `54px`, Left `973px`

#### Third Image (Bottom-Right)
- **Source**: *(rental equipment image 3)*
- **Dimensions**: Width `344px` × Height `397px`
- **Position**: Top `100px` (same row as first), Left offset to right

---

## 6. Beginner-Friendly Coaching Section

**Layout**: Two-column — Left: Image | Right: Text content + Cards

### Left Column — Image

- **Dimensions**: Width `555px` × Height `544px`
- **Position**: Top `3450px`, Left `107px`
- **Object-fit**: `cover`

### Right Column — Content

#### Section Title
- **Font**: `Archivo`, Bold, Italic, `64px`, LH `100%`, Center, Uppercase
- **Color**: `#1A1A1A`
- **Accent**: The words **"BEGINNER-FRIENDLY"** are colored `#0096C7`

#### Section Description
- **Font**: `Archivo`, Medium, `20px`, LH `150%`, Center
- **Color**: `#1A1A1A`

#### Sub-heading — "WHAT YOU'LL EXPERIENCE"
- **Font**: `Sora`, Bold, `32px`, LH `100%`, Uppercase
- **Color**: `#395144`

### Info Cards (Vertical Stack)

- **Dimensions**: Width `589px` × Height `91px`
- **Background**: `#F9FFFF`
- **Box-shadow**: `0px 4px 6px 0px #00000040`
- **Border Radius**: `24px`
- **Padding**: `24px 32px`
- **Gap (Flex)**: `28px`

#### Card Title Typography
- **Font**: `Sora`, Bold, `20px`, LH `100%`
- **Color**: `#0096C7`

#### Card Description Typography
- **Font**: `Archivo`, Regular, `16px`, LH `100%`
- **Color**: `#000000`

#### Card Items
1. **One-on-one expert instruction** — Personalized coaching that adapts to your pace and comfort level.
2. **Dedicated 2-point system setup** — A stable, beginner-friendly system designed to help you balance, control, and progress safely.
3. **Step-by-step skill progression** — Learn at a structured pace — from basic stance to smooth riding transitions.

---

## 7. Before Footer CTA Section

- **Block**: Reuse the `before-footer` block used on the About page.
- **No additional customization required.**
