# Room Landing Page — Design Spec

Applies to:
- `/accommodations/villas/`
- `/accommodations/cabanas/`
- `/accommodations/dwell/`
- `/accommodations/cabins/`

> For the shared banner and breadcrumbs spec, see `gallery-accommodations-banner.md`.

---

## 1. Breadcrumbs & Back Navigation

The breadcrumbs follow the same pattern defined in `gallery-accommodations-banner.md`.

**Trail:** `Home › Rooms › [Room Name]`

### Back to Rooms Link

Positioned at the top-right of the page, above the image grid.

| Property       | Value                           |
| -------------- | ------------------------------- |
| Text           | `← Back to Rooms`               |
| Font Family    | `Sora`                          |
| Font Weight    | `400`                           |
| Font Style     | Regular                         |
| Font Size      | `20px`                          |
| Line Height    | `150%`                          |
| Letter Spacing | `0%`                            |
| Color          | `#000000CC` (80% opacity black) |

---

## 2. Room Image Grid

A three-column asymmetric image grid placed below the breadcrumbs/back link.

### Image 1 — Large Left

| Property      | Value   |
| ------------- | ------- |
| Width         | `805px` |
| Height        | `680px` |
| Top           | `264px` |
| Left          | `100px` |
| Border Radius | `15px`  |
| Rotation      | `0deg`  |
| Opacity       | `1`     |

### Image 2 — Center Column

| Property      | Value   |
| ------------- | ------- |
| Width         | `390px` |
| Height        | `680px` |
| Top           | `264px` |
| Left          | `938px` |
| Border Radius | `15px`  |
| Rotation      | `0deg`  |
| Opacity       | `1`     |

### Image 3 — Right Column, Top

| Property      | Value    |
| ------------- | -------- |
| Width         | `463px`  |
| Height        | `330px`  |
| Top           | `264px`  |
| Left          | `1358px` |
| Border Radius | `15px`   |
| Rotation      | `0deg`   |
| Opacity       | `1`      |

### Image 4 — Right Column, Bottom

| Property      | Value    |
| ------------- | -------- |
| Width         | `463px`  |
| Height        | `312px`  |
| Top           | `632px`  |
| Left          | `1358px` |
| Border Radius | `15px`   |
| Rotation      | `0deg`   |
| Opacity       | `1`      |

> **Note:** A **"See All Images"** button/link is overlaid on Image 1 (bottom-left corner).

---

## 3. Room Container Info

The main card wrapping the room's title, description, amenities, pricing, and policies.

### Container

| Property      | Value                 |
| ------------- | --------------------- |
| Width         | `1721px`              |
| Height        | `1352px`              |
| Border Radius | `15px`                |
| Rotation      | `0deg`                |
| Opacity       | `1`                   |
| Border Width  | `1px`                 |
| Background    | `#F8F9FA`             |
| Border        | `1px solid #00000080` |

### Top Accent Bar (Blue Strip)

A solid color bar at the very top of the Room Container.

| Property   | Value     |
| ---------- | --------- |
| Background | `#0096C7` |

---

### 3.1 Room Title

| Property       | Value     |
| -------------- | --------- |
| Font Family    | `Sora`    |
| Font Weight    | `700`     |
| Font Style     | Bold      |
| Font Size      | `72px`    |
| Line Height    | `100%`    |
| Letter Spacing | `0%`      |
| Color          | `#0096C7` |

---

### 3.2 Room Description

#### Label

| Property       | Value        |
| -------------- | ------------ |
| Font Family    | `Sora`       |
| Font Weight    | `700`        |
| Font Style     | Bold         |
| Font Size      | `48px`       |
| Line Height    | `100%`       |
| Letter Spacing | `0%`         |
| Text Transform | `capitalize` |
| Color          | `#395144`    |

> **Note:** The same label style applies to **Room Amenities** and **Policies** section labels.

#### Body Text

| Property       | Value        |
| -------------- | ------------ |
| Font Family    | `Archivo`    |
| Font Weight    | `500`        |
| Font Style     | Medium       |
| Font Size      | `20px`       |
| Line Height    | `150%`       |
| Letter Spacing | `0%`         |
| Text Transform | `capitalize` |
| Color          | `#1A1A1A`    |

---

### 3.3 Room Amenities

#### Section Label

Uses the same style as the **Room Description Label** above (`Sora 700, 48px, #395144`).

#### Amenity Chip / Tag Text

| Property       | Value     |
| -------------- | --------- |
| Font Family    | `Sora`    |
| Font Weight    | `600`     |
| Font Style     | SemiBold  |
| Font Size      | `24px`    |
| Line Height    | `24px`    |
| Letter Spacing | `0%`      |
| Color          | `#2B3037` |

---

### 3.4 Per Night & Capacity Pricing Box

#### Container

| Property     | Value                 |
| ------------ | --------------------- |
| Width        | `430px`               |
| Height       | `262px`               |
| Rotation     | `0deg`                |
| Opacity      | `1`                   |
| Border Width | `1px`                 |
| Background   | `#F8F9FA`             |
| Border       | `1px solid #00000033` |

#### Price Label

| Property       | Value        |
| -------------- | ------------ |
| Font Family    | `Sora`       |
| Font Weight    | `700`        |
| Font Style     | Bold         |
| Font Size      | `32px`       |
| Line Height    | `100%`       |
| Letter Spacing | `0%`         |
| Text Transform | `capitalize` |
| Color          | `#000000`    |

#### "per night / capacity" Sub-label

| Property       | Value     |
| -------------- | --------- |
| Font Family    | `Archivo` |
| Font Weight    | `400`     |
| Font Style     | Italic    |
| Font Size      | `24px`    |
| Line Height    | `100%`    |
| Letter Spacing | `0%`      |
| Color          | `#000000` |

#### Book Button

| Property      | Value     |
| ------------- | --------- |
| Width         | `353px`   |
| Height        | `64px`    |
| Top           | `163px`   |
| Left          | `37px`    |
| Border Radius | `30px`    |
| Rotation      | `0deg`    |
| Opacity       | `1`       |
| Gap           | `10px`    |
| Padding       | `20px`    |
| Background    | `#395144` |

#### Book Button Text

| Property       | Value     |
| -------------- | --------- |
| Font Family    | `Archivo` |
| Font Weight    | `500`     |
| Font Style     | Medium    |
| Font Size      | `22px`    |
| Line Height    | `100%`    |
| Letter Spacing | `0%`      |
| Color          | `#F5F1EB` |

---

## 4. Policies Section

### Section Label

Uses the same style as **Room Description Label** (`Sora 700, 48px, #395144`).

### Sub-label / Intro Text

| Property       | Value     |
| -------------- | --------- |
| Font Family    | `Archivo` |
| Font Weight    | `500`     |
| Font Style     | Medium    |
| Font Size      | `18px`    |
| Line Height    | `24px`    |
| Letter Spacing | `0%`      |
| Color          | `#1A1A1A` |

### Policies Container

| Property      | Value                  |
| ------------- | ---------------------- |
| Width         | `1585px`               |
| Height        | `576px`                |
| Border Radius | `12px`                 |
| Rotation      | `0deg`                 |
| Opacity       | `1`                    |
| Border Width  | `2px`                  |
| Border        | `2px solid #00000066`  |

### Policy Row — Name

| Property       | Value     |
| -------------- | --------- |
| Font Family    | `Sora`    |
| Font Weight    | `600`     |
| Font Style     | SemiBold  |
| Font Size      | `24px`    |
| Line Height    | `32px`    |
| Letter Spacing | `0%`      |
| Color          | `#1F2226` |

### Policy Row — Description

| Property       | Value     |
| -------------- | --------- |
| Font Family    | `Archivo` |
| Font Weight    | `500`     |
| Font Style     | Medium    |
| Font Size      | `20px`    |
| Line Height    | `24px`    |
| Letter Spacing | `0%`      |
| Color          | `#1A1A1A` |

### Standard Policy Rows (all room types)

| Policy Name        | Description Example                                                                                       |
| ------------------ | --------------------------------------------------------------------------------------------------------- |
| Check-in           | From 02:00 PM to 09:00 PM                                                                                 |
| Check-out          | Until 12:00 PM                                                                                            |
| Breakfast          | Breakfast Available (may be included in selected rooms)                                                   |
| Reception Hours    | Open until 09:00 PM                                                                                       |
| Children and beds  | Infants (0–3 yrs): free; Children (4–8 yrs): extra bed charge applies; Guests (9+): considered adults    |
| No age restriction | Guests of all ages are welcome.                                                                           |
| Smoking            | Smoking is not allowed.                                                                                   |

---

## 5. Other Rooms Container

Displayed at the bottom of the page, showcasing the sibling room types.

### Container

| Property      | Value                 |
| ------------- | --------------------- |
| Width         | `1721px`              |
| Height        | `412px`               |
| Border Radius | `15px`                |
| Rotation      | `0deg`                |
| Opacity       | `1`                   |
| Border Width  | `1px`                 |
| Background    | `#F8F9FA`             |
| Border        | `1px solid #00000080` |

### Top Accent Bar (Blue Strip)

Same as the Room Container top accent bar.

| Property   | Value     |
| ---------- | --------- |
| Background | `#0096C7` |

### "Other Rooms" Section Label

| Property       | Value     |
| -------------- | --------- |
| Font Family    | `Sora`    |
| Font Weight    | `700`     |
| Font Style     | Bold      |
| Font Size      | `48px`    |
| Line Height    | `100%`    |
| Letter Spacing | `0%`      |
| Color          | `#0096C7` |

### Room Thumbnail Image

| Property | Value   |
| -------- | ------- |
| Width    | `504px` |
| Height   | `240px` |
| Rotation | `0deg`  |
| Opacity  | `1`     |

### Room Thumbnail Overlay

| Property         | Value                           |
| ---------------- | ------------------------------- |
| Background Color | `#00000033` (20% black overlay) |

### Room Thumbnail Label (inside overlay)

| Property       | Value     |
| -------------- | --------- |
| Font Family    | `Sora`    |
| Font Weight    | `700`     |
| Font Style     | Bold      |
| Font Size      | `36px`    |
| Line Height    | `100%`    |
| Letter Spacing | `0%`      |
| Color          | `#FFFFFF` |

### Thumbnail Mapping (per current page)

| Current Page                  | Thumbnails Shown          |
| ----------------------------- | ------------------------- |
| `/accommodations/villas/`     | Cabana · Dwell · Cabins   |
| `/accommodations/cabanas/`    | Villa · Dwell · Cabins    |
| `/accommodations/dwell/`      | Villa · Cabana · Cabins   |
| `/accommodations/cabins/`     | Villa · Cabana · Dwell    |

---

## 6. Design Tokens Reference

| Token Name    | Hex Value | Usage                              |
| ------------- | --------- | ---------------------------------- |
| `--Primary`   | `#0096C7` | Top accent bars, titles, links     |
| `--Secondary` | `#395144` | Book button background             |
| `--Border`    | `#F8F9FA` | Container & pricing box background |

---

## Notes

- All containers use a **blue top accent bar** (`#0096C7`) as a visual header strip — both the Room Container and the Other Rooms Container share this pattern.
- The **Room Description, Room Amenities, and Policies section labels** all share the same typography (`Sora 700 Bold, 48px, #395144`).
- The **"Back to Rooms"** link is right-aligned, positioned above the image grid.
- The **Other Rooms** section always excludes the current room type and shows the remaining 3 sibling types as thumbnails.
