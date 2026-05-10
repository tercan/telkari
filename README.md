# Telkari

Theme-independent WordPress floating social media links and CTA buttons plugin. Display social accounts and action buttons in fixed floating layouts with customizable positions, colors, and mobile behavior.

**Version:** 0.2.0
**Requires WordPress:** 5.9+
**Requires PHP:** 7.4+
**License:** GPLv2 or later

## Features

### Design Layouts

Three built-in design templates, each with its own positioning options:

- **Orbit** -- Quarter-circle trigger button in a corner. Icons fan out in an arc on click. Uses CSS trigonometry-based arc positioning with staggered animation delays. Click to toggle, click outside to close.
- **Ribbon** -- Horizontal bar fixed at the bottom of the page with icons displayed in a row.
- **Pillar** -- Vertical sidebar strip with icons stacked in a column.

### Position System

Each design supports a specific set of positions:

| Design | Available Positions                      |
| ------ | ---------------------------------------- |
| Orbit  | Bottom Left, Bottom Right                |
| Ribbon | Bottom Left, Bottom Right, Bottom Center |
| Pillar | Bottom Left, Bottom Right                |

The position selector updates dynamically based on the selected design.

### Supported Platforms

13 platforms with bundled Font Awesome SVG icons (no CDN dependency):

Instagram, YouTube, Facebook, X (Twitter), LinkedIn, TikTok, GitHub, Pinterest, Telegram, WhatsApp, Discord, Twitch, Spotify

### Social Account Management

- Add and delete social media accounts from the admin panel
- Drag-and-drop reordering via SortableJS
- Per-account enable/disable toggle
- URL validation on account creation

### CTA Button Management

- Add WhatsApp, phone, email, and custom URL buttons
- Configure labels, destinations, optional WhatsApp messages, and per-button colors
- Drag-and-drop reordering with per-button enable/disable controls
- Live preview while building or editing CTA buttons

### Appearance Settings

- **Icon Size** -- Adjustable from 24px to 96px via range slider
- **Icon Spacing** -- Adjustable from 0px to 48px via range slider
- **Icon Style** -- Rounded or Square
- **Link Target** -- Same Tab or New Tab
- **Tooltips** -- Show or hide platform name on hover

### Color Customization

- Per-platform brand color overrides with WordPress color picker
- 13 official brand colors included as defaults
- Configurable bar/wrapper background color for Ribbon and Pillar (with transparent option)
- Configurable trigger button color for Orbit design
- Auto-contrast foreground color calculation (white on dark, dark on light)
- One-click "Reset All Colors" to restore defaults
- CSS custom properties (`--telkari-bg`, `--telkari-fg`) for per-icon theming

### Hover Animations

Scale and shadow hover effects on all design layouts.

## Admin Interface

Tabbed settings panel accessible from the top-level Telkari menu in WordPress admin:

- **Design** -- Visual design selector with SVG previews and integrated position selector
- **Social Accounts** -- Account list with drag-and-drop sorting and add form
- **CTA Buttons** -- CTA list, guided builder, color picker, and live preview
- **Appearance** -- Card-based settings layout for icon size, spacing, style, link target, tooltips, and color customization

Settings link is also available on the Plugins list page.

## Technical Details

### Frontend Rendering

- Icons are rendered via `wp_footer` hook using pure CSS (no frontend JavaScript except the Orbit click handler)
- Design-specific CSS is loaded per active design (`design-1.css`, `design-2.css`, `design-3.css`)
- Conditional asset loading: CSS is only enqueued when at least one enabled social account or CTA button exists
- CSS custom properties for icon size, spacing, and wrapper background are injected as inline styles

### File Structure

```
telkari/
  telkari.php                          Main plugin file
  uninstall.php                        Clean uninstall handler
  includes/
    core/
      cta-buttons.php                  CTA data model and sanitization helpers
      options.php                      Settings defaults, getters, platform definitions
      item-helpers.php                 Shared collection and link helper functions
      sanitization.php                 Input sanitization callbacks
    admin/
      settings-page.php                Admin page registration and tab rendering
      cta-list-table.php               CTA buttons list UI
      design-selector.php              Design cards and SVG previews
      social-list-table.php            Social accounts list UI
    frontend/
      render-icons.php                 Frontend icon rendering and contrast calculation
  assets/
    css/
      admin.css                        Admin panel styles
      design-1.css                     Orbit design styles
      design-2.css                     Ribbon design styles
      design-3.css                     Pillar design styles
    js/
      admin.js                         Admin interactions (sorting, color pickers, design selector)
      sortable.min.js                  SortableJS library (v1.15.6)
    icons/
      *.svg                            Font Awesome SVG icon subset (13 platforms + plugin logo)
  languages/
    telkari.pot                        Translation template
    telkari-*.po / telkari-*.mo        Bundled translation files
```

### Security

- Nonce verification on all form submissions
- Capability checks (`manage_options`) on admin pages
- Input sanitization via dedicated sanitization callbacks
- Output escaping on all rendered content
- SVG icon whitelist: only known filenames from the bundled set are loaded
- Clean uninstall removes all plugin data (single site and multisite)

### Internationalization

- Full i18n support with `telkari` text domain
- 11 translations bundled: Arabic (ar), Bengali (bn_BD), Chinese Simplified (zh_CN), French (fr_FR), German (de_DE), Hindi (hi_IN), Italian (it_IT), Portuguese (pt_PT), Russian (ru_RU), Spanish (es_ES), Turkish (tr_TR)
- All admin UI strings are translatable

## Requirements

- WordPress 5.9 or later
- PHP 7.4 or later
- No external dependencies (all assets are bundled)

## Author

Tercan Keskin -- [tercan.net](https://tercan.net/)
