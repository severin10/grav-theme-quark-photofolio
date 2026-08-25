# 0.1.8
## 2026-08-25

1. [](#bugfix)
   * Footer's "Support" placeholder section no longer shows on other pages
     (e.g. the contact form page) — it's now specific to the one-pager
     itself, like the photo sections it sits below.

# 0.1.7
## 2026-08-25

1. [](#new)
   * `templates/form.html.twig` — a standalone-page template for a page
     modeled on Grav's own documented forms example (a `form.md` file with
     a `form:` block, no theme-specific setup needed). It shows up in the
     nav automatically, linking to its own URL.
2. [](#improved)
   * Nav now links a non-photo child page (like the new form page) to its
     own URL instead of an in-page anchor; photo-section children keep
     their `#anchor` links as before. Superseds 0.1.6's bubbled-form
     dedup, which assumed the form would stay embedded in the one-pager.
3. [](#bugfix)
   * Removed the "Contact" placeholder slot from the footer
     (`partials/contact.html.twig`) — embedding a form inline in the
     one-pager via Grav's child-to-parent form bubbling did not reliably
     show it; a dedicated page (see above) is the documented, working
     alternative. `partials/support.html.twig` is unaffected.

# 0.1.6
## 2026-08-25

1. [](#bugfix)
   * Nav no longer hardcodes a "Contact" link unconditionally — if a
     `contact`-slugged child page already provides one (e.g. a dedicated
     form page, since Grav bubbles a child page's `form:` up to the
     parent), that one is used instead of duplicating it

# 0.1.5
## 2026-08-23

1. [](#new)
   * Fullscreen viewer: previous/next navigation across all photos on the
     page (wraps at both ends) — Left/Right arrow keys, plus visible
     chevron buttons either side of the photo title (just the two arrows,
     with no gap between them, when a photo has no title). Hidden entirely
     when the page only has one photo.

# 0.1.4
## 2026-08-23

1. [](#bugfix)
   * `.btn` background/text color now fall back to guaranteed-defined
     tokens (`--q2-text-strong` / `--q2-bg`) when no accent color is
     configured, instead of an undefined `--q2-accent` that rendered as
     an invisible/transparent button — affected the form submit button and
     any other `.btn` element
   * JS assets are now only output once, at the very end of the page,
     instead of also in `<head>` — a field's own script (e.g. the basic
     captcha reload button) registers itself when that field renders,
     which happens later, in the footer; the earlier `<head>` output call
     could never have included it

# 0.1.3
## 2026-08-23

1. [](#bugfix)
   * Form submit/reset buttons now get the theme's `.btn` class
     (`form_button_classes`), so they render with real styling instead of a
     bare, unstyled `<button>`

# 0.1.2
## 2026-08-22

1. [](#new)
   * Reference implementation for the contact slot: `partials/contact.html.twig`
     renders whatever `form:` is defined on the current page via Grav's own
     form plugin. Fully generic — no field list, spam-protection choice, or
     recipient address lives in the theme; that stays in page content.

# 0.1.1
## 2026-08-21

1. [](#improved)
   * Contact and Support footer sections are now empty, optional include
     slots (`partials/contact.html.twig`, `partials/support.html.twig`)
     instead of hardcoded placeholder text

# 0.1.0
## 2026-08-21

1. [](#new)
   * Initial release, forked from Quark 2
