# CWC Wake — Rates & Schedule Design Specification

This document defines the visual requirements for the Rates & Schedule page, following the site-wide design tokens and tropical theme.

## 1. Page Layout & Background

- **Background**: Use the standard `.cwc-tropical-section` (Tropical background + 70% cream overlay).
- **Navigation**: Integrated Breadcrumbs at the top of the content area.
- **Constraints**: Follow the site-wide `1921px` max-width and fluid horizontal padding (`clamp(1.5rem, 4vw, 4rem)`).

## 2. Components

### A. Sidebar Tabs (Left Side)
Used to switch between categories (e.g., Day Pass, Season Pass, Rentals).

- **Standard State**:
    - **Width**: `425px`
    - **Height**: `55px`
    - **Background**: `#1A1A1A` (Dark)
    - **Border Radius**: `30px`
    - **Padding**: `10px 20px`
    - **Focus**: No outline focus on click.
- **Active State**:
    - **Background**: `var(--wp--preset--color--primary)` (#0096C7)
- **Typography**:
    - **Font**: `Sora` (Heading)
    - **Weight**: `600` (SemiBold)
    - **Size**: `24px`
    - **Color**: `#F5F1EB` (Cream)
    - **Line Height**: `100%`

### B. Rates Content Card (Right Side)
The main information display area.

- **Main Container**:
    - **Width**: `1163px` (Approx 70% of row)
    - **Min-Height**: `872px`
    - **Background**: `#FFFFFF`
    - **Border**: `1px solid rgba(0, 0, 0, 0.3)`
    - **Border Radius**: `30px`
- **Top Accent Bar**:
    - A decorative blue bar spanning the top of the container.
    - **Height**: `16px`
    - **Background**: `#0096C7`
    - **Border Radius**: `30px` (Inherited alignment)
    - **Offset**: Positioned slightly outside-top of the main box (approx `-16px` from top).

### C. Typography (Inside Content)

- **Main Title**:
    - **Font**: `Sora`
    - **Weight**: `700` (Bold)
    - **Size**: `64px`
    - **Color**: `#0096C7`
    - **Line Height**: `100%`
- **Description**:
    - **Font**: `Archivo`
    - **Weight**: `500` (Medium)
    - **Size**: `20px`
    - **Color**: `#000000`
    - **Line Height**: `130%`

### D. Schedule Table

- **Table Wrap**:
    - **Background**: `#363636` (Charcoal)
    - **Border**: `1px solid #5B5B5B`
    - **Border Radius**: `4px`
- **Cells**:
    - **Height**: `72px`
    - **Borders**: `1px solid #5B5B5B` (Grid style)
- **Table Text**:
    - **Font**: `Inter`
    - **Weight**: `600` (SemiBold)
    - **Size**: `24px`
    - **Color**: `#FFFFFF`
    - **Line Height**: `130%`

## 3. Interaction Patterns

- **Tab Switching**: Clicking a sidebar tab should swap the content area via Vanilla JS (fade transition).
- **Sticky Sidebar**: The tab container should remain sticky as the user scrolls through long rate lists.
- **Responsive**: On tablet/mobile, the sidebar tabs should convert to a horizontal scrollable list or a dropdown.
