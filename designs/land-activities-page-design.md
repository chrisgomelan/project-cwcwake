# CWC Wake — Land Activities Page Design Specification

This document details the visual requirements and block structures for the "Land Activities" page. It will serve as the reference for building custom blocks and assembling the page template.

---

## 1. Hero / Banner Section

- **Block**: Reuse the existing Hero/Banner block from the Home Page.
- **Customization**: Update the background image and text content.
- **Background Image**: `/wp-content/uploads/2026/04/land-activites-hero-bg.webp`
- **Breadcrumbs**: Include breadcrumbs below the hero content.

---

## 2. Professional Skate & BMX Section

**Block Type**: New Custom Block (Two-column Split)

### Container Layout
- **Dimensions**: Width `1721px` × Height `436px`
- **Positioning**: Top `1170px`, Left `100px`
- **Opacity**: `1`

### Left Side — Text & Content
- **Dimensions**: Width `642px` × Height `436px`
- **Background**: Overlay `var(--Text, #1A1A1A)` with `/wp-content/uploads/2026/04/doodle-bg.webp`
- **Title**: `PROFESSIONAL SKATE & BMX`
  - **Font**: `Sora`, Bold, `64px`, LH `100%`, Uppercase
  - **Color**: `#F5F1EB`
  - **Accent**: The word **"SKATE"** is rendered in **Italic** and colored `#86B59C`
- **Description**:
  - **Font**: `Archivo`, Medium (`500`), `20px`, LH `100%`
  - **Color**: `#F5F1EB`
- **Icon (Bottom)**: `themes/child-cwcwake/assets/images/skate-park.svg`

### Right Side — Image
- **Dimensions**: Width `1079px` × Height `435px`
- **Image Source**: *(Specified in block content)*

---

## 3. Diverse Land Activities Section

**Block Type**: `cards-section` (Static Variant)

### Section Header
- **Heading Secondary Color**: `#395144`

### Item Cards
- **Card Dimensions**: Width `415px` × Height `470px`
- **Title Typography**:
  - **Font**: `Sora`, Bold, `32px`, LH `100%`, Center
  - **Color**: `#FFFFFF`
- **Subtitle Typography**:
  - **Font**: `Archivo`, Medium (`500`), `20px`, LH `100%`, Center
  - **Color**: `#FFFFFF`

---

## 4. Pickleball & Court Sports Section

**Block Type**: New Custom Block (Header + Multi-Image)

### Header Layout
- **Flex Layout**: `justify-content: space-between` (Title on left, Description on right)

### Title — "PICKLEBALL & COURT SPORTS"
- **Font**: `Archivo`, Bold (`700`), Italic, `64px`, LH `100%`, Uppercase
- **Accent**: The word **"PICKLEBALL"** is colored `#395144` and rendered in **Italic**.

### Description (Right Side)
- **Font**: `Archivo`, Medium (`500`), `20px`, LH `100%`
- **Color**: `#1A1A1A`

### Media Content
- **Images**: Two images positioned underneath the header.
- **Image Dimensions (each)**: Width `845px` × Height `436px`
- **Positioning Reference**: Top `200px`, Left `5px`

---

## 5. Master the Dirt Section

- **Block Type**: `feature-banner` (as used for Inflatable Aqua Adventure)
- **Background Image**: `/wp-content/uploads/2026/04/master-the-dirt-bg.webp`

---

## 6. Epic Team & Community Section

- **Block Type**: Reuse **Professional Skate & BMX** block structure.
- **Variation**: **Reversed Layout** (Text on the right side).
- **Image Source**: `/wp-content/uploads/2026/04/epic-team-bg.webp`

---

## 7. Before Footer CTA Section

### Visual Specification
- **Background**:
  - **Linear Gradient**: `45deg, #f2ede4 0%, #9e968a 40%, #ff9922 100%`
  - **Background Image**: `/wp-content/uploads/2026/04/land-activities-before-footer-bg.webp`

- **Block**: Reuse the `before-footer` block logic with the above styling updates.
