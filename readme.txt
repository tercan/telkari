=== Telkari ===
Contributors: tercan
Donate link: https://tercan.net/
Tags: social media, social links, floating bar, social buttons, call to action
Requires at least: 5.9
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Theme-independent floating social media links and CTA buttons with multiple design layouts, customizable positions, and per-item colors.

== Description ==

Telkari displays floating social media links and CTA buttons on your WordPress site. You can show Social Icons, CTA Buttons, or both, then control their layout, placement, colors, and mobile behavior from a dedicated admin page.

= Key Features =

* Social Icons and CTA Buttons can be shown or hidden independently.
* Social Icons and CTA Buttons have separate placement controls.
* Ribbon and Pillar layouts prevent social and CTA groups from overlapping when both are visible.
* CTA buttons support WhatsApp, phone, email, and custom URL destinations.
* Mobile layouts are compact and keep CTA labels on one line with controlled truncation.
* All icons, CSS, and JavaScript assets are bundled with the plugin.

= Design Layouts =

* **Orbit** - Corner trigger button with social icons opening in a quarter-circle arc. CTA buttons can be displayed with the same design without changing the Orbit toggle behavior.
* **Ribbon** - Bottom horizontal layout. Social icons stay in one row, and only one display group can use the center placement at a time.
* **Pillar** - Side layout. When both groups are visible, Social Icons and CTA Buttons are kept on opposite sides.

= Social Icons =

Telkari includes bundled SVG icons for 13 platforms:

Instagram, YouTube, Facebook, X (Twitter), LinkedIn, TikTok, GitHub, Pinterest, Telegram, WhatsApp, Discord, Twitch, Spotify

Social accounts can be added, deleted, enabled, disabled, reordered by drag and drop, and styled with per-platform colors.

= CTA Buttons =

CTA buttons support:

* WhatsApp number and optional message
* Phone number
* Email address
* Custom URL
* Custom label
* Per-button color
* Drag-and-drop ordering
* Enable/disable toggle

= Appearance =

The Appearance tab includes separate settings for:

* Social icon size and spacing
* CTA button size, spacing, and width
* Icon style: Rounded or Square
* Link target: Same Tab or New Tab
* Tooltips
* Platform colors, wrapper background, and Orbit trigger color

= Performance =

Telkari only loads frontend assets when at least one visible, enabled Social Icon or CTA Button exists. Design-specific CSS is loaded for the active layout, and the Orbit toggle script is loaded only when the Orbit social trigger is rendered.

= Privacy and External Resources =

Telkari does not track users and does not call external APIs. No frontend assets are loaded from a CDN.

Site owners may configure social and CTA links that point to external destinations, but Telkari does not transmit site data to those destinations.

= Documentation and Source =

Documentation and demo: https://tercan.github.io/telkari/

Source code: https://github.com/tercan/telkari

The bundled admin sorting helper uses SortableJS under the MIT license. SortableJS source: https://github.com/SortableJS/Sortable

== Installation ==

1. Upload the `telkari` folder to `/wp-content/plugins/`.
2. Activate Telkari from the Plugins screen in WordPress.
3. Open the Telkari menu in the WordPress admin sidebar.
4. Choose a design layout in the Design tab.
5. Configure which display groups should be visible.
6. Add social accounts in the Social Accounts tab.
7. Add optional CTA buttons in the CTA Buttons tab.
8. Adjust sizing, spacing, colors, tooltips, and link behavior in the Appearance tab.

== Frequently Asked Questions ==

= Does Telkari require any external service? =

No. Telkari does not require an external account, API key, remote service, or CDN.

= Does Telkari track users? =

No. Telkari does not track users or send analytics data to a third-party service.

= Can I show only Social Icons or only CTA Buttons? =

Yes. The Design tab includes visibility controls for Social Icons and CTA Buttons, so either group can be shown or hidden independently.

= Can Social Icons and CTA Buttons use different placements? =

Yes. Telkari includes separate placement controls for Social Icons and CTA Buttons. Ribbon and Pillar automatically prevent invalid same-side combinations when both groups are visible.

= Why do Ribbon icons stay on one line? =

Ribbon is designed as a horizontal bar. Social icons remain in a single row to preserve the layout, while mobile sizing is tightened to reduce horizontal space.

= Which CTA button types are supported? =

WhatsApp, phone, email, and custom URL buttons are supported.

= Can I customize colors? =

Yes. You can use the WordPress color picker to customize platform colors, CTA button colors, the wrapper background, and the Orbit trigger color.

= Is Telkari translatable? =

Yes. Telkari uses WordPress internationalization functions and includes translation files for Arabic, Bengali, Chinese (Simplified), French, German, Hindi, Italian, Portuguese, Russian, Spanish, and Turkish.

== Screenshots ==

1. Frontend floating layout with configured social icons and CTA buttons.
2. Design tab with Display Groups, placement controls, and visual layout previews.
3. Social Accounts tab with account rows, drag-and-drop ordering, and add form.
4. CTA Buttons and Appearance controls for button setup, sizing, colors, and link behavior.

== Changelog ==

= 1.0.0 =
* Added CTA button management with WhatsApp, phone, email, and custom URL button types.
* Added independent Social Icons and CTA Buttons visibility controls.
* Added independent CTA placement support so CTA buttons can be positioned separately from social accounts.
* Added separate CTA appearance controls for button size, spacing, and width.
* Refreshed the admin interface for Design, Social Accounts, CTA Buttons, and Appearance with a shared workspace layout.
* Improved Ribbon and Pillar placement rules to prevent social and CTA group overlap.
* Improved mobile layouts for CTA buttons and social icons across all frontend designs.
* Updated WordPress.org readme metadata, FAQ, privacy notes, screenshots, and source documentation.
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
* Documentation updated: social account management wording now reflects add/delete flow.
* Removed unused security helper file (`includes/core/security.php`) and unused admin i18n key (`Edit`).

= 0.1.0 =
* Initial release.
* Added Orbit, Ribbon, and Pillar design layouts.
* Added support for 13 bundled social platform SVG icons.
* Added drag-and-drop social account management.
* Added per-platform color customization.
* Added configurable icon size, spacing, style, link target, and tooltips.
* Added CSS-only frontend rendering with conditional asset loading.
* Added clean uninstall support for single site and multisite.
* Added bundled translations.

== Upgrade Notice ==

= 1.0.0 =
Adds CTA buttons, independent display group visibility, separate CTA placement, and updated mobile layouts. Review the Design and Appearance tabs after updating.
