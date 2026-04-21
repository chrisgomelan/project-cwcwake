# Room Management Transition: Technical Implementation Guide

This guide serves as the definitive reference for refactoring the Room Detail system from a hardcoded seeder to a dynamic, Custom Post Type (CPT) architecture.

## 1. System Philosophy
- **Dynamic Content**: Room details (prices, features, images) are stored in the database as `accommodation` posts.
- **Global Settings**: Shared data (Policies) is stored once in a centralized settings page.
- **Front-Page Independence**: The "STAY With Us" section on the homepage remains **static/manual**. It is NOT affected by the dynamic system to ensure exact design control.

---

## 2. Technical Specification

### A. Custom Post Type: `accommodation`
- **Register Name**: `accommodation`
- **Slug**: `/accommodations/`
- **Admin Capabilities**: Standard post features + custom meta boxes.

### B. Meta Fields (The "Source of Truth")
| Key | Input Type | Use Case |
|-----|------------|----------|
| `_cwc_price` | Text | Base price display (e.g. "PHP 19,500"). |
| `_cwc_price_sub` | Text | Contextual pricing info (e.g. "per night"). |
| `_cwc_capacity` | Number | Max occupancy for filtering. |
| `_cwc_availability` | Dropdown | Status tracking: `available`, `fully-booked`, `maintenance`. |
| `_cwc_amenities` | Checkboxes | Slugs mapping to internal icon library (wifi, pool, etc.). |
| `_cwc_gallery_ids` | Text | Comma-separated Media Gallery IDs for the hero grid. |

### C. Global Policies
- **Key**: `cwc_global_policies`
- **Format**: JSON Array
- **Logic**: All rooms pull from this single source. If updated here, it reflects on all room detail pages simultaneously.

---

## 3. Availability Tracking
To manage availability, the admin uses the `_cwc_availability` field:
1. **Available**: Normal booking behavior.
2. **Fully Booked**: The "Book Now" buttons on the frontend will automatically change to a "Fully Booked" message or an Inquiry link.
3. **Maintenance**: Hides the pricing box and displays a "Coming Soon" or "Under Maintenance" badge.

---

## 4. Frontend Integration
### Block Fallbacks
The blocks (`room-info`, `room-gallery`) are programmed with this sequence:
1. **Check Attributes**: If a user manually typed values in the block in the editor, use those.
2. **Check Dynamic Meta**: If on a Room post and attributes are empty, pull from the fields defined in Section 2.
3. **Default**: Use empty/placeholder if nothing is found.

### Template Mapping
- **Location**: `templates/single-accommodation.html`
- **Structure**:
  - Header Template Part
  - Page Banner (Dynamic Title)
  - Room Gallery Block (Dynamic IDs)
  - Room Info Block (Dynamic Meta + Global Policies)
  - Other Rooms Block (Dynamic Query of other `accommodation` posts)
  - Footer Template Part
