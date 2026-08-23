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
