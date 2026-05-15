# CWC Wake Documentation Hub

This directory contains the formal documentation for the Camsur Watersports Complex (CWC) booking platform. These guides are designed for site administrators (non-technical) and developers maintaining the codebase.

---

## 📘 Primary Guides

| Guide | Audience | What it covers |
|---|---|---|
| **[Client User Guide](CLIENT_USER_GUIDE.md)** | Site Admins / Staff | Bookings, Room management, Coupons, Mailer, and daily operations. |
| **[Developer Handover Guide](DEVELOPER_HANDOVER_GUIDE.md)** | Developers / IT | Architecture, CPTs, Meta keys, AJAX, Payment logic, and Environment setup. |

---

## 🛠️ Specialized Technical Docs

| Guide | What it covers |
|---|---|
| **[Installation & Environment](02-installation-and-environment.md)** | Detailed steps for LocalWP setup, database syncing, and repository configuration. |
| **[LiteSpeed Cache](plugins/litespeed-cache.md)** | Managing page caching and troubleshooting "stale" content. |
| **[WP Mail SMTP](plugins/wp-mail-smtp.md)** | Outbound email configuration and delivery testing. |
| **[Rank Math SEO](plugins/seo-by-rank-math.md)** | Managing search engine visibility and metadata. |

---

## 📂 Documentation Map

```text
docs/
├── README.md                       ← You are here (Overview)
├── CLIENT_USER_GUIDE.md            ← Primary Admin Reference
├── DEVELOPER_HANDOVER_GUIDE.md     ← Primary Technical Reference
├── 02-installation-and-environment.md
└── plugins/
    ├── litespeed-cache.md
    ├── wp-mail-smtp.md
    └── seo-by-rank-math.md
```

**Note to Maintainers:**  
Avoid creating small, fragmented documentation files. When adding new features or workflows, update the **Client User Guide** (for staff actions) or the **Developer Handover Guide** (for code changes).

**Last updated:** 2026-05-15
