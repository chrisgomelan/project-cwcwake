# Site maintenance handbook

Plain-language guides for people who keep the CWC Wake WordPress site up to date. These docs assume you are comfortable in a web browser and in the WordPress dashboard, not that you write code or manage servers.

**[How this folder is organized](#documentation-map)** — one overview file (this page), a few general chapters, and **one file per plugin** only where maintainers routinely need guidance.

---

## Table of contents

### General

| Guide | What it covers |
|--------|----------------|
| [Orientation](01-orientation.md) | Who this is for, how to log in, roles in simple terms, when to ask technical help |
| [Installation and environment](02-installation-and-environment.md) | LocalWP setup, clone, `db-sync.sql`, theme, optional keys, push/pull — same as repo [`README.md`](../README.md) |
| [Core workflows](03-core-workflows.md) | Pages, posts, media, menus, publishing checks |
| [When something goes wrong](04-when-something-goes-wrong.md) | Safe checks, when to stop, what to send support |
| [Glossary](05-glossary.md) | Short definitions of common WordPress words |

### Plugins (maintainer guides)

Add or remove rows here when you add or retire a `docs/plugins/*.md` file.

| Plugin guide | What it covers |
|----------------|----------------|
| [CWC Wake — Accommodations](plugins/cwc-accommodations.md) | Rooms, bookings area, coupons, mailer, subscribers, amenities, policies |
| [LiteSpeed Cache](plugins/litespeed-cache.md) | When the site looks “stuck” on an old version after a change |
| [WP Mail SMTP](plugins/wp-mail-smtp.md) | Outbound email and test sends |
| [Rank Math SEO](plugins/seo-by-rank-math.md) | Basic SEO checks on important pages |

---

## Documentation map

```text
docs/
├── README.md                 ← You are here (overview + TOC)
├── 01-orientation.md
├── 02-installation-and-environment.md
├── 03-core-workflows.md
├── 04-when-something-goes-wrong.md
├── 05-glossary.md
└── plugins/
    ├── cwc-accommodations.md
    ├── litespeed-cache.md
    ├── wp-mail-smtp.md
    └── seo-by-rank-math.md
```

**Screenshots:** Store images under `docs/images/` and link them from these files when you add visuals.

**Last updated:** create a short note at the bottom of any file you change often so collaborators know it is current.
