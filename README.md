# Quark Photofolio

A minimal, one-page photography portfolio theme for [Grav CMS](https://getgrav.org),
built on the [Quark 2](https://github.com/getgrav/grav-theme-quark2) / Blades CSS
foundation.

All photos live on a single page, grouped into sections. The photo is always
the focus: a subtle, optional title sits flush over its bottom-right corner,
and clicking a photo opens it fullscreen (Esc or click outside to close).
Every uploaded photo automatically gets two derivatives — an 800px web
thumbnail and a watermarked full-size version — nothing to process by hand.

## Adding photos

Photos are organized one folder per section:

```
user/pages/01.home/
  photofolio.md          <- the one-pager itself (title, optional subtitle/intro)
  01.city/                <- "City" section
  02.nacht/                <- "Nacht" section
  03.mixed/               <- "Mixed" section
```

For each section:

1. Drop your full-resolution photos into that section's folder (or upload
   them via the Media tab in the Admin panel).
2. Open the page in the Admin panel and add each photo to the **Photos**
   list, with an optional title.

The order in the Photos list is the display order. A photo without a title
is shown without the overlay.

Renaming a section (its `title:` field) or reordering the three folders
(the `01.`, `02.`, `03.` prefixes) changes the on-page section
headings/order without touching any template.

## Watermark

Set a watermark logo under **Themes → Quark Photofolio → Watermark** (a
small PNG with transparency works best). Until one is set, the fullscreen
view falls back to the plain, unwatermarked original — upload a logo before
publishing real work.

The watermark is **tiled** across the entire full-size photo (not placed
once), so cropping a small piece out of the image doesn't remove it. Each
tile automatically switches between a white and a black rendition of your
upload depending on how bright the photo is at that spot, so it stays
legible on both dark and light images regardless of the watermark's own
color. Tile size, the gap between tiles (both relative to the photo's own
width), and transparency (0 = fully opaque, higher values fade it out —
100 makes it fully invisible) are all configurable there, as is the
thumbnail's target size (default 800px on the longer edge).

## Placeholders

Two sections are reserved at the bottom of the page and ready to be wired
up:

- **Contact** — `templates/partials/footer.html.twig` has a `TODO` where a
  real Grav form (the `form` plugin is already installed) with spam
  protection (e.g. Grav's built-in honeypot field) belongs.
- **Support** — same file, `TODO` for a "Buy me a coffee" button/widget.

## Credits

Based on [Quark 2](https://github.com/getgrav/grav-theme-quark2) by Team
Grav (MIT License) — the Blades CSS design tokens, fonts, and asset
pipeline are reused as-is; the layout, templates, gallery/lightbox
behaviour, and image-derivative pipeline are new.
