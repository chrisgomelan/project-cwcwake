# CWC Wake — Project Overview

**CWC Wake** (Camsur Watersports Complex) is a WordPress block theme for a premier wakepark resort in the Philippines. It is built as a **child theme of Twenty Twenty-Five** using the Full Site Editing (FSE) architecture — block templates, template parts, `theme.json`, and server-rendered custom blocks.

## Tech Stack

| Layer | Detail |
|---|---|
| CMS | WordPress 6.4+ (FSE / Block Theme) |
| Parent Theme | Twenty Twenty-Five |
| PHP | 8.1+ |
| Fonts | Google Fonts — **Sora** (headings), **Archivo** (body) |
| Build Tools | None — vanilla CSS & JS, no bundler required |

## Theme Structure

```
themes/child-cwcwake/
├── style.css                  # Theme metadata
├── theme.json                 # Design tokens (colors, typography, spacing, radii, shadows)
├── functions.php              # Enqueue, block registration, page scaffolding
│
├── templates/                 # Full-page block templates (HTML)
│   ├── front-page.html        # Homepage — hero, intro, showcases, accommodations, reviews, social
│   ├── page.html
│   ├── page-activities.html
│   ├── page-accommodations.html
│   ├── page-plan-your-trip.html
│   ├── page-about.html
│   └── page-child.html
│
├── parts/                     # Reusable template parts
│   ├── header.html
│   └── footer.html
│
├── blocks/                    # Custom server-rendered blocks
│   ├── hero-section/          # Full-viewport hero with video background & play/pause toggle
│   ├── intro-section/         # Two-column intro (text + Vimeo/video with bracket shapes)
│   ├── showcase-section/      # Reusable dark section — "cards", "videos", or "social" variant
│   ├── accommodations-section/# Staggered 4-column card grid for lodging
│   └── reviews-section/       # Client review slider (quote + image carousel)
│
└── assets/
    ├── css/
    │   ├── global.css         # Site-wide resets and utilities
    │   └── header.css         # Header styles + scroll transition (wavy flow effect)
    ├── js/
    │   └── header.js          # Scroll class toggling for header
    └── images/                # SVG logos, icons
```

## Custom Blocks

Each block lives under `blocks/<name>/` and contains:

| File | Purpose |
|---|---|
| `block.json` | Block metadata, attributes, supports |
| `render.php` | Server-side PHP render template |
| `style.css` | Frontend styles (auto-enqueued by WordPress) |
| `view.js` | Frontend interactivity (optional, registered via `viewScript`) |

### Block Inventory

| Block | Slug | Variants | Key Features |
|---|---|---|---|
| Hero Section | `cwc/hero-section` | — | MP4 background video, play/pause toggle, overlay, CTA buttons |
| Intro Section | `cwc/intro-section` | — | Two-column grid (2fr / 3fr), Vimeo iframe detection, decorative brackets, tagline |
| Showcase Section | `cwc/showcase-section` | `cards`, `videos`, `social` | GIF hover reveal (cards), carousel pagination (videos), Instagram-style grid (social) |
| Accommodations | `cwc/accommodations-section` | — | Staggered 4-col grid, hover color inversion, image zoom, multi-layer box-shadow |
| Reviews | `cwc/reviews-section` | — | Two-column slider, quote.svg icon, arrows + counter overlay on image |

## Design Tokens (`theme.json`)

### Colors

| Token | Value | Usage |
|---|---|---|
| `primary` | `#0096C7` | CTAs, links, accent highlights |
| `primary-dark` | `#007BA3` | Hover states |
| `secondary` | `#395144` | Moss green — accommodations bg, brackets, headings |
| `accent` | `#FF6B35` | Tangerine — nav hover, emphasis |
| `background` | `#FFFFFF` | Page background |
| `surface` | `#F0F1EB` | Warm ivory — section backgrounds |
| `text` | `#1A1A1A` | Body text |

### Typography

| Token | Size | Usage |
|---|---|---|
| `gigantic` | 4.5rem (fluid) | H1 |
| `huge` | 4rem (fluid) | H2 / section headings |
| `xx-large` | 3rem (fluid) | H3 / card titles |
| `x-large` | 2rem (fluid) | H4 |
| `large` | 1.5rem (fluid) | H5 |
| `medium` | 1.125rem (fluid) | Body text |
| `small` | 0.875rem (fluid) | Captions, small labels |

### Font Families

| Token | Family | Role |
|---|---|---|
| `heading` | Sora | Headings, card titles, buttons |
| `body` | Archivo | Body text, subtitles, descriptions |

## Site Hierarchy

The theme auto-scaffolds pages on activation:

- **Home** (front page)
- **Activities** → Water Sports, Land Activities, Elite Facilities
- **Accommodations** → Villas, Cabanas, Dwell, Cabin
- **Plan Your Trip** → Rates, FAQs, Blogs, Gallery
- **About**

## Local Development

This project uses **Local by Flywheel**. The site root is:

```
~/Local Sites/child-cwcwake/app/public/
```

The theme lives at:

```
wp-content/themes/child-cwcwake/
```

No build step is required. Edit CSS/JS/PHP files directly and refresh the browser.
