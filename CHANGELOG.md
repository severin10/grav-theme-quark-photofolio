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
