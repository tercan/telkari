# Telkari

Theme-independent WordPress plugin for floating social media links and CTA buttons. Telkari lets site owners show social icons, CTA buttons, or both in fixed frontend layouts with independent visibility, placement, colors, and responsive mobile behavior.

**Version:** 1.0.0
**Requires WordPress:** 5.9+
**Requires PHP:** 7.4+
**License:** GPLv2 or later

## Features

### Display Groups

- Show or hide Social Icons and CTA Buttons independently from the Design tab
- Configure Social Icons placement separately from CTA Buttons placement
- Automatically normalizes conflicting Ribbon and Pillar placements so the two groups do not overlap
- Supports social-only, CTA-only, and mixed frontend configurations

### Design Layouts

Three built-in design templates, each with its own placement behavior:

- **Orbit** - Quarter-circle trigger button in a corner. Social icons fan out in an arc on click. CTA buttons can be shown with the Orbit layout without changing the toggle behavior.
- **Ribbon** - Horizontal bar fixed at the bottom of the page. Social icons stay in a single row, and only one group can use the center placement at a time.
- **Pillar** - Vertical sidebar strip fixed to the left or right edge. When both groups are visible, Social Icons and CTA Buttons are kept on opposite sides.

### Position System

Each design supports a specific set of placements:

| Design | Available Positions                      |
| ------ | ---------------------------------------- |
| Orbit  | Bottom Left, Bottom Right                |
| Ribbon | Bottom Left, Bottom Right, Bottom Center |
| Pillar | Bottom Left, Bottom Right                |

The placement controls update dynamically based on the selected design and enabled display groups.

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
- Guided builder with live preview while creating or editing CTA buttons

### Appearance Settings

- **Social Icon Size** - Adjustable from 24px to 96px via range slider
- **Social Icon Spacing** - Adjustable from 0px to 48px via range slider
- **CTA Button Size** - Compact, Default, or Large
- **CTA Button Spacing** - Adjustable from 0px to 48px via range slider
- **CTA Button Width** - Content, Fixed, or Full
- **Icon Style** - Rounded or Square
- **Link Target** - Same Tab or New Tab
- **Tooltips** - Show or hide platform name on hover

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

- **Design** - Visual design selector with SVG previews, Display Groups, and relationship-aware placement controls
- **Social Accounts** - Account list with drag-and-drop sorting, status messaging, and add form
- **CTA Buttons** - CTA list, guided builder, color picker, status messaging, and live preview
- **Appearance** - Card-based settings layout for Social Icons, CTA Buttons, link behavior, and color customization

Settings link is also available on the Plugins list page.

## Technical Details

### Frontend Rendering

- Icons and CTA buttons are rendered via the `wp_footer` hook using CSS-first layouts
- Design-specific CSS is loaded per active design (`design-1.css`, `design-2.css`, `design-3.css`)
- Shared frontend CSS is loaded only when at least one visible, enabled Social Icon or CTA Button exists
- The Orbit toggle script is loaded only when the Orbit social trigger is rendered
- CSS custom properties for icon size, spacing, CTA spacing, and wrapper background are injected as inline styles

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
      frontend-shared.css              Shared frontend social and CTA styles
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

### Privacy and External Resources

- Telkari does not track users.
- Telkari does not call external APIs or load frontend assets from a CDN.
- Social and CTA links are configured by the site owner and may point to external destinations, but the plugin does not transmit site data to those destinations.
- SortableJS is bundled for admin drag-and-drop sorting under the MIT license. Source: <https://github.com/SortableJS/Sortable>

### Internationalization

- Full i18n support with `telkari` text domain
- 11 translations bundled: Arabic (ar), Bengali (bn_BD), Chinese Simplified (zh_CN), French (fr_FR), German (de_DE), Hindi (hi_IN), Italian (it_IT), Portuguese (pt_PT), Russian (ru_RU), Spanish (es_ES), Turkish (tr_TR)
- All admin UI strings are translatable

## Requirements

- WordPress 5.9 or later
- PHP 7.4 or later
- No external network dependencies (all assets are bundled)

## Links

- Documentation and demo: <https://tercan.github.io/telkari/>
- Source code: <https://github.com/tercan/telkari>

## Author

Tercan Keskin - [tercan.net](https://tercan.net/)
