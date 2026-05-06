# CWC Wake — About Page Design Specification

This document details the visual requirements and block structures for the "About CWC Wake" page. It will serve as the reference for building custom blocks and assembling the page template.

---

## 1. Hero Section
- **Description**: Reuses the exact layout and block structure as the Home Page Hero section.
- **Customization**: Update the background video.
- **Video Source**: `/wp-content/uploads/2026/04/9953670-uh`

---

## 2. Legacy of Firsts (Timeline Section)
- **General Styling**: The section Title and Description use the exact same typography and layout logic as the `why-stay` block.
- **Title Accent**: The word "LEGACY" receives the primary accent color.

### Timeline Layout & Components
- **Layout**: Alternating rows (Left Text / Right Image -> Left Image / Right Text).
- **Connector**: A vertical line `1px solid rgba(0, 0, 0, 0.2)` (`#00000033`) runs down the center of the viewport, with a gradient dot at each milestone.
- **Milestone Dot**: 
  - **Dimensions**: `69px` × `69px`
  - **Background**: `linear-gradient(180deg, #0096C7 0%, #395144 100%)`
  - **Shape**: Rounded (`border-radius: 50%`)

#### Year Container (Text Side)
- **Dimensions**: Width `725px` × Height `291px`
- **Background**: `var(--Border, #F8F9FA)`
- **Border Decoration**: A left-side floating blue bar:
  - Width `19px`, Height `297px`, Top Offset `-6px`. Background: `var(--Primary, #0096C7)`

#### Typography
- **Year Text**: 
  - Font: `Sora`, Bold, `48px`, LH `100%`
  - Color: `#395144`
- **Milestone Title**: 
  - Font: `Sora`, Bold, `48px`, LH `100%`
  - Color: `#0096C7`
- **Milestone Description**: 
  - Font: `Archivo`, Medium, `20px`, LH `100%`
  - Color: `#000000`

#### Image Side
- **Dimensions**: Width `715px` × Height `291px`
- **Object-fit**: `cover`

---

## 3. Home of World Champions
- **Section Title**: 
  - Font: `Sora`, Bold, `64px`, LH `100%`
  - Accent: The word "CHAMPIONS" is `italic` and colored `#0096C7`.
- **Section Description**: 
  - Font: `Archivo`, Medium, `20px`, LH `100%`, Color `#1A1A1A`.

### Interactive 3D Carousel
- **Display**: A curved, 3D-style image carousel.
- **Text Overlay (Center Screen)**: An autoplaying typographical animation that cycles through phrases.
- **Overlay Text Styling**:
  - Font: `Sora`, Bold, `48px`, LH `100%`, Center Align.
  - Background: `var(--Primary, #0096C7)` (Behind the text).
- **Phrases to Cycle**:
  1. *WWA World Series*
  2. *WWA Wake Park World Championships*
  3. *Asian Wakeboard Championships*
  4. *Philippine Wakeboard Nationals*

---

## 4. Certified Safe. Built for Performance.
- **Background**: `/wp-content/uploads/2026/04/doodle-bg.webp`
- **Section Title**: 
  - Font: `Sora`, Bold, `64px`, LH `100%`
  - Accents: Specific words like "SAFE" and "PERFORMANCE" are highlighted.
- **Section Description**: 
  - Font: `Archivo`, Medium, `20px`, LH `100%`, Color `#F8F9FA`.

### Card Grid (3 Columns)
- **Dimensions (each)**: Width `378px` × Height `462px`
- **Border**: `1px solid rgba(255, 255, 255, 0.5)` (`#FFFFFF80`)
- **Card Title**: 
  - Font: `Sora`, Bold, `48px`, LH `100%`, Center Align, Color: `#6EDBFF`.
- **Card Description**: 
  - Font: `Archivo`, Medium, `20px`, LH `100%`, Center Align, Color: `#F8F9FA`.
- **Icons**:
  - `assets/images/camsur-pass-protocol.svg`
  - `assets/images/certified-coaching.svg`
  - `assets/images/precise-maintain.svg`

---

## 5. Empowering the Region
- **Section Title & Description**: Matches the styling logic of the "Legacy of Firsts" section.
- **Title Accent**: The word "Empowering" receives the primary accent color.

### Layout
- **Left Side (Image)**: Reuses the organic "blobs/shape" wrapper design found in the `<!-- wp:cwc/intro-section -->` on the Front Page.
- **Right Side (Cards List)**: A vertical stack of info cards.

#### Info Cards (List Items)
- **Container**:
  - Width `721px` × Height `154px`
  - Background: `#F9FFFF`
  - Box-shadow: `0px 4px 6px 0px rgba(0, 0, 0, 0.25)` (`#00000040`)
  - Border-radius: `24px`
  - Padding: `24px 32px`
  - Gap (Flex): `28px`
- **Icon Wrapping Circle**:
  - Width/Height: `100px` × `100px`
  - Background: `#00AFB933` (20% Opacity Teal)
  - Layout: Flex center to house the SVG.
- **Typography - Title**:
  - Font: `Sora`, Bold, `24px`, LH `100%`, Color: `#000000`
- **Typography - Description**:
  - Font: `Archivo`, Regular, `20px`, LH `100%`, Color: `#000000`
- **Icons**:
  - `assets/images/sustainable-tourism.svg`
  - `assets/images/local-employment.svg`
  - `assets/images/youth-sports-dev.svg`

---

## 6. Before Footer CTA Section
- **Dimensions**: Full width (`1919px` max) × Height `616px`
- **Background Image**: `/wp-content/uploads/2026/04/before-footer-bg.webp`
- **Background Overlay**: 
  - `linear-gradient(135deg, #2b4cf2 0%, #38bdf8 50%, #e0f2fe 100%)`
- **Layout**: Content centered vertically and horizontally.

### Center Content
- **Title**: 
  - Font: `Sora`, Bold, `64px`, LH `100%`, Center Align, Color: `#1A1A1A`.
- **Description**: 
  - Font: `Archivo`, Medium, `30px`, LH `100%`, Center Align, Color: `#FFFFFF`.

### CTA Button ("Book Now")
- **Dimensions**: Width `212px` × Height `64px`
- **Background**: `var(--Accent, #FF6B35)`
- **Border Radius**: `30px`
- **Padding**: `20px`
- **Display**: Flex Center, `gap: 10px`
- **Text**: 
  - Font: `Archivo`, Bold, `22px`, LH `100%`, Color: `#1A1A1A`.
