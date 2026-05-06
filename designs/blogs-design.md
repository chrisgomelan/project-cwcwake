# Blogs — Design Spec

Applies to: **Blogs** page sections (**Featured**, **Upcoming Events**, **All Blogs**)

---

## 1. Feature Blogs Section

### Section Title Layout
The section title uses balanced typography with a specific italic highlight.

| Property       | Value       |
| -------------- | ----------- |
| Font Family    | `Sora`      |
| Font Weight    | `700`       |
| Font Style     | Bold        |
| Font Size      | `48px`      |
| Line Height    | `100%`      |
| Letter Spacing | `0%`        |
| Color          | `#000000`   |

> **Exception:** Specific words in the title use `Italic` style and color `#0096C7`.

### Section Description
| Property       | Value      |
| -------------- | ---------- |
| Font Family    | `Archivo`  |
| Font Weight    | `500`      |
| Font Style     | Medium     |
| Font Size      | `20px`     |
| Line Height    | `150%`     |
| Letter Spacing | `0%`       |
| Color          | `#1A1A1A`  |

### Featured Blog Cards (Grid Layout)
Featured blogs are arranged in an asymmetrical grid. All cards use an **Overlay background: `#00000066`**.

| Card Type       | Width | Height | Border Radius |
| --------------- | ----- | ------ | ------------- |
| **Card 1** (Large) | `998px` | `475px` | `30px`        |
| **Cards 2 & 3** | `490px` | `279px` | `30px`        |
| **Cards 4 & 5** | `490px` | `279px` | `30px`        |

### Card Typography (Overlay)
| Element         | Font    | Weight | Size   | Color     |
| --------------- | ------- | ------ | ------ | --------- |
| **Blog Title**  | `Sora`  | `600`  | `32px` | `#FFFFFF` |
| **Description** | `Archivo` | `500`  | `20px` | `#FFFFFF` |
| **Date**        | `Archivo` | `300`  | `22px` | `#FFFFFF` |

### Read More Button (Featured)
| Property      | Value               |
| ------------- | ------------------- |
| Width / Height| `272px` / `49px`    |
| Background    | `#0096C7` (Primary) |
| Border Radius | `30px`              |
| Font / Size   | `Archivo` / `22px`  |
| Text Color    | `#F5F1EB`           |

---

## 2. Upcoming Events Section

### Month Typography
| Property       | Value     |
| -------------- | --------- |
| Font Family    | `Sora`    |
| Font Weight    | `600`     |
| Font Size      | `20px`    |
| Color (Active) | `#000000` |
| Color (Other)  | `#000000B2` (70% Opacity) |

### Event Circles (The Day Number)
| Feature         | Active Circle       | Other Circles       |
| --------------- | ------------------- | ------------------- |
| **Size**        | `110x110px`         | `73x73px`           |
| **Background**  | `#0096C7`           | `#1A1A1A`           |
| **Day Font**    | Sora (Bold) `36px`  | Sora (Bold) `24px`  |
| **Text Color**  | `#F8F9FA`           | `#F8F9FA`           |

### Event Content Container
| Property      | Value       |
| ------------- | ----------- |
| Width / Height| `1459px` / `509px` |
| Image Area    | `1408px` / `306px` (30px radius) |
| Background    | `#F8F9FA`   |
| Border Radius | `30px`      |

### Event Typography
| Element         | Font      | Weight | Size   | Color       |
| --------------- | --------- | ------ | ------ | ----------- |
| **Event Title** | `Sora`    | `700`  | `32px` | `#395144`   |
| **Description** | `Archivo` | `500`  | `20px` | `#00000099` |

---

## 3. All Blogs Section

### Filter UI
| Element         | Design Spec                                     |
| --------------- | ----------------------------------------------- |
| **Dropdown**    | BG: `#FFFFFF`, Border: `1px solid #FFFFFF`, Radius: `1px` |
| **Filter Text** | Archivo (Regular), `14px`, Color: `#1A1A1A`     |

### All Blogs Card
| Property      | Value                                           |
| ------------- | ----------------------------------------------- |
| Card Size     | `556px` x `526px`                               |
| Background    | `#F8F9FA` with shadow `-2px 4px 12px #18181814` |
| Image Size    | `524px` x `246px` (4px radius)                  |

### All Blogs Typography
| Element         | Font    | Weight | Size   | Color     |
| --------------- | ------- | ------ | ------ | --------- |
| **Title**       | `Sora`  | `600`  | `24px` | `#181818` |
| **Description** | `Inter` | `400`  | `16px" | `#474747` |
| **Date**        | `Inter` | `400`  | `16px` | `#474747` |

### Read More Button (Standard)
| Property      | Value                                           |
| ------------- | ----------------------------------------------- |
| Background    | `#0096C7`                                       |
| Text          | Sora (SemiBold), `16px`, Color: `#FFFFFF`       |
| Shape         | `524px` x `48px` with `30px` radius             |

### Pagination
| Feature         | Spec                                            |
| --------------- | ----------------------------------------------- |
| **Button**      | `48x48px`, `#FFFFFF` BG, Radius `8px`           |
| **Active State**| Background: `#395144` (Secondary)               |
| **Text**        | Inter (SemiBold), `18px`, Color: `#FFFFFF`      |
