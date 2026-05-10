=== Telkari ===
Contributors: tercan
Donate link: http://tercan.net/
Tags: social media, social icons, social links, floating bar, social buttons, cta buttons
Requires at least: 5.9
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 0.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Theme-independent WordPress floating social media links and CTA buttons plugin with multiple design layouts, customizable positions, and per-item colors.

== Description ==

Telkari lets you display floating social media links and CTA buttons on your WordPress site with three distinct design layouts. Each design has its own positioning options and items are rendered through design-specific CSS, with a lightweight toggle script used only for the Orbit layout.
Live demo and documentation: https://tercan.github.io/telkari/

= Design Layouts =

* **Orbit** - Quarter-circle trigger button in a corner. Icons fan out in an arc on click with staggered animation delays. Click to toggle, click outside to close.
* **Ribbon** - Horizontal bar fixed at the bottom of the page with icons displayed in a row.
* **Pillar** - Vertical sidebar strip with icons stacked in a column.

= Supported Platforms =

13 platforms with bundled SVG icons (no CDN dependency):

Instagram, YouTube, Facebook, X (Twitter), LinkedIn, TikTok, GitHub, Pinterest, Telegram, WhatsApp, Discord, Twitch, Spotify

= Social Account Management =

* Add and delete social media accounts
* Drag-and-drop reordering
* Per-account enable/disable toggle
* URL validation on account creation

= CTA Button Management =

* Add WhatsApp, phone, email, and custom URL buttons
* Configure labels, destinations, optional WhatsApp messages, and per-button colors
* Drag-and-drop reordering
* Per-button enable/disable toggle
* Live preview while building or editing CTA buttons

= Appearance Settings =

* Icon size (24-96px) via range slider
* Icon spacing (0-48px) via range slider
* Icon style: Rounded or Square
* Link target: Same Tab or New Tab
* Tooltips: Show or hide platform name on hover

= Color Customization =

* Per-platform brand color overrides with WordPress color picker
* 13 official brand colors included as defaults
* Configurable bar/wrapper background color for Ribbon and Pillar (with transparent option)
* Configurable trigger button color for Orbit design
* Auto-contrast foreground color calculation
* One-click "Reset All Colors" to restore defaults

= Performance =

* Pure CSS frontend rendering (no JavaScript except Orbit click handler)
* Conditional asset loading: CSS only enqueued when enabled accounts exist
* Design-specific CSS loaded per active design
* Bundled SVG icons with static file cache

= Security =

* Nonce verification on all form submissions
* Capability checks on admin pages
* Input sanitization and output escaping
* SVG icon whitelist
* Clean uninstall (single site and multisite)

== Installation ==

1. Upload the `telkari` folder to `/wp-content/plugins/`.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Go to the Telkari menu in the admin sidebar.
4. Select a design layout and position.
5. Add your social media accounts in the Social Accounts tab.
6. Add optional CTA buttons in the CTA Buttons tab.
7. Customize appearance and colors in the Appearance tab.

== Frequently Asked Questions ==

= Does this plugin require any external resources? =

No. All icons are bundled as SVG files and all CSS/JS assets are included. No CDN or external API calls are made.

= Can I use different colors for each platform? =

Yes. The Appearance tab includes a color picker for each platform. You can override the default brand color or reset all colors to defaults with one click.

= Which positions are available? =

It depends on the selected design. Orbit and Pillar support Bottom Left and Bottom Right. Ribbon supports Bottom Left, Bottom Right, and Bottom Center.

= Does the plugin work with any theme? =

Yes. Telkari renders icons via the wp_footer hook and uses its own CSS, so it works independently of your active theme.

= Is the plugin translatable? =

Yes. All UI strings use WordPress i18n functions. Translations for 11 languages are bundled: Arabic, Bengali, Chinese (Simplified), French, German, Hindi, Italian, Portuguese, Russian, Spanish, and Turkish.

= Live demo / documentation URL? =

You can access the live demo and documentation at: https://tercan.github.io/telkari/

== Screenshots ==

1. Orbit design layout in action on a live site, showing the quarter-circle trigger and social icons fanning out in an arc.
2. Design selection settings page with visual previews of Orbit, Ribbon, and Pillar layouts along with position selector.
3. Social media accounts management page with drag-and-drop reordering and add account form.
4. CTA buttons management page with guided builder and live preview.
5. Appearance settings page with icon size and spacing sliders, icon style, link target, tooltip toggles, and color customization.

== Changelog ==

= 0.2.0 =
* Added CTA button management with WhatsApp, phone, email, and custom URL button types.
* Added per-button CTA labels, destinations, optional WhatsApp messages, custom colors, sorting, enable/disable toggles, and live preview support.
* Added independent CTA placement support so CTA buttons can be positioned separately from social accounts.
* Refreshed the admin interface for Design, Social Accounts, CTA Buttons, and Appearance with a shared workspace layout.
* Improved mobile layouts for CTA buttons and social icons across all frontend designs.
* Updated translations and localization templates for the expanded admin and CTA feature set.

= 0.1.2 =
* Moved design-1 toggle inline script to external file loaded via wp_enqueue_script to comply with WordPress plugin directory requirements.
* SVG icon output is now sanitized through wp_kses with a strict SVG element/attribute allowlist.
* Design preview SVG output in admin panel also sanitized via wp_kses at echo point.
* Refactored icon link attribute rendering to use late escaping at every echo point.
* All ternary CSS class outputs in admin templates wrapped with esc_attr() for proper late escaping.
* Removed inline JS function (replaced by enqueued external JS file).
* Removed phpcs:ignore EscapeOutput comments.

= 0.1.1 =
* Documentation updated: social account management wording now reflects add/delete flow
* Removed unused security helper file (`includes/core/security.php`) and unused admin i18n key (`Edit`)

= 0.1.0 =
* Initial release
* 3 design layouts: Orbit, Ribbon, Pillar
* 13 platform support with bundled SVG icons
* Drag-and-drop account management
* Per-platform color customization
* Range sliders and button group controls
* Auto-contrast foreground color calculation
* CSS-only frontend rendering
* Conditional asset loading
* Translations included: Arabic, Bengali, Chinese (Simplified), French, German, Hindi, Italian, Portuguese, Russian, Spanish, Turkish
* Clean uninstall support (single site and multisite)
