# Changelog

## [0.1.0] - 2026-02-02 18:55:00

### Added

- Initial release
- 3 design layouts: Orbit (arc trigger), Ribbon (horizontal bar), Pillar (vertical sidebar)
- Orbit design with CSS trigonometry-based arc positioning, staggered animations, click toggle, and outside-click-to-close
- Dynamic angle clamping via atan2 to keep Orbit icons inside viewport edges
- Design-dependent position system (bottom-left, bottom-right, bottom-center)
- Position selector integrated into Design tab, updates dynamically based on selected design
- Social media account management with drag-and-drop reordering (SortableJS)
- Support for 13 platforms: Instagram, YouTube, Facebook, X, LinkedIn, TikTok, GitHub, Pinterest, Telegram, WhatsApp, Discord, Twitch, Spotify
- Font Awesome SVG icon subset (bundled, no CDN)
- Admin settings panel with tabbed interface (Design, Social Accounts, Appearance)
- Card-based layout for Appearance settings
- Appearance settings: icon size, spacing, style (rounded/square), link target, tooltips
- Range sliders for icon size and spacing controls
- Button group toggles for icon style, link target, tooltips, and position selection
- Per-platform brand background colors (13 official brand colors)
- Color picker UI to override default brand colors per platform
- Configurable Orbit trigger button color and wrapper/bar background color with transparent option
- Auto-contrast foreground color (white on dark backgrounds, dark on light backgrounds)
- "Reset All Colors" button to restore all platform colors to defaults
- CSS custom properties (--telkari-bg, --telkari-fg) for per-icon color theming
- Scale and shadow hover animations on all designs
- CSS-only frontend rendering via wp_footer hook
- Conditional asset loading (CSS only when enabled accounts exist)
- Top-level admin menu with dashicons-share icon
- Settings link on the plugins list page
- Security: nonce verification, capability checks, input sanitization, output escaping
- Clean uninstall via uninstall.php (single site and multisite)
- i18n support with Turkish (tr_TR) translation
- README.md with full plugin documentation
