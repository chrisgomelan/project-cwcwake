# Gallery — Design Spec

Applies to: **Gallery** page

---

## Gallery Image Card

| Property      | Value                    |
| ------------- | ------------------------ |
| Width         | `796px`                  |
| Height        | `494px`                  |
| Top offset    | `100px`                  |
| Left offset   | `6px`                    |
| Rotation      | `0deg`                   |
| Opacity       | `1`                      |
| Border Radius | `30px`                   |
| Overlay       | `black` at `10%` opacity |

> **Corner accents:** Each gallery card uses the same decorative corner brackets as the banner. Apply identical corner SVG/pseudo-element treatment to all four corners of the card.

---

## Gallery Category Name

Positioned over the card image (bottom-left area).

| Property       | Value     |
| -------------- | --------- |
| Font Family    | `Sora`    |
| Font Weight    | `700`     |
| Font Style     | Bold      |
| Font Size      | `48px`    |
| Line Height    | `100%`    |
| Letter Spacing | `0%`      |
| Color          | `#0096C7` |

---

## Album Count

Positioned alongside the category name (bottom-right area of the card).

| Property       | Value       |
| -------------- | ----------- |
| Font Family    | `Inter`     |
| Font Weight    | `600`       |
| Font Style     | Semi Bold   |
| Font Size      | `24px`      |
| Line Height    | `100%`      |
| Letter Spacing | `0%`        |
| Color          | `#000000B2` |

> **Color note:** `#000000B2` is black at approximately **70% opacity** (hex `B2` ≈ `178/255`).
