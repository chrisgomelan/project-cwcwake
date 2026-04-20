# Contact Page — Design Spec

Applies to:
- `/contact-page/` (or equivalent location)

> For the shared banner and breadcrumbs spec, see `gallery-accommodations-banner.md`.

---

## 1. Get In Touch & Visit Us Container

This is the main top section containing contact information and a map on the left, with an image on the right.

### Section Container

| Property         | Value                        |
| ---------------- | ---------------------------- |
| Width            | `1639px`                     |
| Height           | `892px`                      |
| Border Radius    | `30px`                       |
| Rotation         | `0deg`                       |
| Opacity          | `1`                          |
| Border Width     | `1px`                        |
| Background       | `#F8F9FA` (`--Border`)       |
| Border           | `1px solid #00000033`        |

### Layout - Right Side Image

| Property      | Value    |
| ------------- | -------- |
| Width         | `740px`  |
| Height        | `894px`  |
| Border Radius | `30px`   |
| Rotation      | `0deg`   |
| Opacity       | `1`      |

---

## 2. Typography & Content

### Main Titles
*Includes: "Get In Touch", "Visit Us", "Send Us a Message"*

| Property       | Value      |
| -------------- | ---------- |
| Font Family    | `Sora`     |
| Font Weight    | `700`      |
| Font Style     | Bold       |
| Font Size      | `64px`     |
| Line Height    | `100%`     |
| Letter Spacing | `0%`       |
| Color          | `#0096C7`  |

### Get In Touch - Description

| Property       | Value      |
| -------------- | ---------- |
| Font Family    | `Archivo`  |
| Font Weight    | `500`      |
| Font Style     | Medium     |
| Font Size      | `20px`     |
| Line Height    | `150%`     |
| Letter Spacing | `0%`       |
| Color          | `#000000`  |

---

## 3. Contact Information Blocks

### Phone Icon Background

| Property      | Value      |
| ------------- | ---------- |
| Width         | `82px`     |
| Height        | `82px`     |
| Top           | `10px`     |
| Left          | `20px`     |
| Opacity       | `1`        |
| Background    | `#0096C733` (20% Opacity Blue) |

---

### Email Section

#### Label ("Email")

| Property       | Value      |
| -------------- | ---------- |
| Font Family    | `Poppins`  |
| Font Weight    | `500`      |
| Font Style     | Medium     |
| Font Size      | `20px`     |
| Leading Trim   | Cap Height |
| Line Height    | `32px`     |
| Letter Spacing | `0%`       |
| Color          | `#1A1A1A`  |

#### Email Address Text
*Style also applies to "Phone (Main Support)" label.*

| Property       | Value      |
| -------------- | ---------- |
| Font Family    | `Archivo`  |
| Font Weight    | `500`      |
| Font Style     | Medium     |
| Font Size      | `20px`     |
| Leading Trim   | Cap Height |
| Line Height    | `100%`     |
| Letter Spacing | `0%`       |
| Color          | `#1A1A1A`  |

#### Phone Number Text ("+63 912 345 6789")

| Property       | Value      |
| -------------- | ---------- |
| Font Family    | `Inter`    |
| Font Weight    | `400`      |
| Font Style     | Regular    |
| Font Size      | `18px`     |
| Leading Trim   | Cap Height |
| Line Height    | `32px`     |
| Letter Spacing | `0%`       |
| Color          | `#1A1A1A`  |

---

### Address Section

| Property       | Value      |
| -------------- | ---------- |
| Font Family    | `Archivo`  |
| Font Weight    | `500`      |
| Font Style     | Medium     |
| Font Size      | `20px`     |
| Line Height    | `150%`     |
| Letter Spacing | `0%`       |
| Color          | `#000000`  |

---

## 4. Map Layout

The embedded map block within the Visit Us section.

| Property      | Value    |
| ------------- | -------- |
| Width         | `775px`  |
| Height        | `275px`  |
| Top           | `540px`  |
| Left          | `86px`   |
| Rotation      | `0deg`   |
| Opacity       | `1`      |

---

## 5. "Send Us a Message" Form Container

The bottom container dedicated to the contact form.

### Container

| Property      | Value                 |
| ------------- | --------------------- |
| Width         | `1629px`              |
| Height        | `780px`               |
| Top           | `1568px`              |
| Left          | `146px`               |
| Border Radius | `30px`                |
| Rotation      | `0deg`                |
| Opacity       | `1`                   |
| Border Width  | `1px`                 |
| Background    | `#F8F9FA` (`--Border`)|
| Border        | `1px solid #2783A1`   |

---

### 5.1 Form Inputs

#### Input Label

| Property       | Value      |
| -------------- | ---------- |
| Font Family    | `Archivo`  |
| Font Weight    | `600`      |
| Font Style     | SemiBold   |
| Font Size      | `22px`     |
| Line Height    | `100%`     |
| Letter Spacing | `0%`       |
| Color          | `#000000`  |

#### Required Asterisks (*)

| Property       | Value      |
| -------------- | ---------- |
| Font Family    | `Archivo`  |
| Font Weight    | `600`      |
| Font Style     | SemiBold   |
| Font Size      | `22px`     |
| Line Height    | `100%`     |
| Color          | `#CA0003` (`--Important`) |

#### Input Field Box

| Property      | Value                 |
| ------------- | --------------------- |
| Width         | `1448px`              |
| Height        | `60px`                |
| Top           | `44px`                |
| Border Radius | `15px`                |
| Background    | `#0096C70D` (5.1% Opacity Blue) |

#### Placeholder Text

| Property       | Value      |
| -------------- | ---------- |
| Font Family    | `Archivo`  |
| Font Weight    | `600`      |
| Font Style     | SemiBold   |
| Font Size      | `22px`     |
| Line Height    | `100%`     |
| Letter Spacing | `0%`       |
| Color          | `#00000080` (50% Opacity Black) |

---

### 5.2 Form Action

#### Send Message Button

| Property      | Value      |
| ------------- | ---------- |
| Width         | `1453px`   |
| Height        | `64px`     |
| Top           | `2253px`   |
| Left          | `230px`    |
| Border Radius | `30px`     |
| Padding       | `20px`     |
| Gap           | `10px`     |
| Background    | `#0096C7` (`--Primary`) |

#### Button Text

| Property       | Value      |
| -------------- | ---------- |
| Font Family    | `Archivo`  |
| Font Weight    | `500`      |
| Font Style     | Medium     |
| Font Size      | `22px`     |
| Line Height    | `100%`     |
| Letter Spacing | `0%`       |
| Color          | `#F5F1EB`  |

---

## 6. Design Tokens Reference

| Token Name    | Hex Value | Usage                              |
| ------------- | --------- | ---------------------------------- |
| `--Primary`   | `#0096C7` | Titles, Icons Background, Buttons  |
| `--Border`    | `#F8F9FA` | Main Container Backgrounds         |
| `--Important` | `#CA0003` | Status indicators and asterisks    |
