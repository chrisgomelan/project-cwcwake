# CWC Wake — FAQ Page Design Specification

This document defines the visual requirements for the FAQ page, adhering to the site-wide design tokens, typography, and standard layout wrappers.

## 1. Page Layout & Foundation

- **Background & Wrapper**: Uses the standard `.cwc-tropical-section` (Tropical background + 70% cream overlay).
- **Header Structure**: Integrates the standard Page Banner (`cwc/page-banner`) and Breadcrumbs (`cwc/breadcrumbs`) at the top, matching the "Rates" and "Blogs" pages.
- **Two-Column Grid**: 
  - **Left Sidebar**: Search bar and category tabs.
  - **Divider**: A 1px vertical line separating the sidebar from the content.
  - **Right Content**: The FAQ title and accordion items.

---

## 2. Sidebar Components (Left Column)

### A. Search Input
- **Container Size**: Width `346px` × Height `52px`
- **Background**: `#F5F1EB`
- **Border**: `2px solid rgba(0, 0, 0, 0.3)` (`#0000004D`)
- **Border Radius**: `100px` (Pill shape)
- **Placeholder Text**:
  - **Font**: `Archivo`
  - **Weight**: `500` (Medium)
  - **Size**: `16px`
  - **Line Height**: `100%`
  - **Color**: `rgba(0, 0, 0, 0.7)` (`#000000B2`)

### B. Category Tabs
- **Container Size**: Width `337px` × Height `62px`
- **Padding**: `10px`
- **Gap**: `10px`
- **Default State**: Background is transparent.
- **Active State**: Background is `var(--wp--preset--color--primary)` (`#0096C7`).

### C. Vertical Divider Line
- **Line Style**: `1px solid rgba(0, 0, 0, 0.2)` (`#00000033`)
- **Positioning**: Spans vertically between the sidebar tabs and the right-side content containers.

---

## 3. FAQ Content Area (Right Column)

### A. Section Title
- **Font**: `Sora`
- **Weight**: `600` (SemiBold)
- **Size**: `48px`
- **Line Height**: `42px`
- **Letter Spacing**: `-0.3px`
- **Color**: `#0096C7`
- **Alignment**: Vertical-align Middle.

### B. FAQ Accordion Item (Default State)
- **Container Size**: Width `1194px` × Height `73px`
- **Background**: `var(--Border, #F8F9FA)` (Light grey)
- **Border Radius**: `30px`
- **Question Text**:
  - **Font**: `Sora`
  - **Weight**: `600` (SemiBold)
  - **Size**: `24px`
  - **Line Height**: `42px`
  - **Letter Spacing**: `-0.3px`
  - **Color**: `#1A1A1A`

### C. FAQ Accordion Item (Active / Open State)
- **Question Text Change**: Color shifts to `#0096C7`.
- **Icons**: 
  - Uses `themes\child-cwcwake\assets\images\plus-icon.svg` (closed)
  - Uses `themes\child-cwcwake\assets\images\minus.svg` (open)
- **Answer Dropdown Container**:
  - **Container Size**: Width `1194px` × Height `98px` (approx, should be fluid based on content).
  - **Top Offset**: `68px` (Designed to slightly overlap or tuck underneath the question container).
  - **Border Radius**: `30px`
- **Answer Text**:
  - **Font**: `Archivo`
  - **Weight**: `500` (Medium)
  - **Size**: `22px`
  - **Line Height**: `150%`
  - **Letter Spacing**: `-0.3px`
  - **Color**: `#F8F9FA` (Off-white, implying the active answer dropdown has a dark/colored background).

---

## 4. Content Payload (Getting Started)

1. **Do I need prior experience to try wakeboarding at CWC?**
   No, beginners are welcome. CWC provides basic instruction and guidance, making it easy for first-timers to get started.

2. **What should I wear for wakeboarding?**
   Wear comfortable swimwear or athletic gear. Rash guards are recommended for added protection, and don't forget sunscreen.

3. **Is equipment included or do I bring my own?**
   CWC offers rental equipment such as wakeboards, helmets, and life vests. You can bring your own gear if you prefer.

4. **Are there instructors available for beginners?**
   Yes, trained instructors are available to assist and guide you through the basics before you hit the water.

5. **What is the first step when I arrive?**
   Start by registering at the front desk, choose your activity or package, rent equipment if needed, and attend a quick orientation before riding.
