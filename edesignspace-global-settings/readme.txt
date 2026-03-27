=== EDS Global Settings ===
Contributors:       edesignspace
Tags:               elementor, dynamic tags, global settings, site variables, contact info
Requires at least:  5.8
Tested up to:       6.7
Requires PHP:       7.4
Stable tag:         2.4.3
License:            GPLv2 or later
License URI:        https://www.gnu.org/licenses/gpl-2.0.html

Store global website variables — contact info, booking links, social media, and custom data — then insert them anywhere in Elementor using Dynamic Tags.

== Description ==

**EDS Global Settings** is a lightweight, modular WordPress plugin built by [eDesign Space](https://edesignspace.com/) that lets you define reusable site-wide variables and expose them as Elementor Dynamic Tags — compatible with **both Elementor Free and Elementor Pro**.

Never hard-code your phone number, booking link, or social handles directly into widgets again. Save them once in the admin panel, insert them via Dynamic Tags everywhere, and update all instances across your entire site in seconds.

= Features =

* **Contact Information** — Business name, tagline, phone, WhatsApp, emails, address, city, country, Google Maps link, embed URL, business hours, VAT number.
* **Booking & Links** — Main booking URL, consultation, appointment, service links (×3), client portal, primary & secondary CTA (URL + label).
* **Social Media** — Facebook, Instagram, X/Twitter, LinkedIn, YouTube, TikTok, Pinterest, Snapchat, GitHub, Dribbble, Behance.
* **Custom Variables** — Unlimited key/value pairs. Each variable becomes its own selectable option inside the Custom Variable Dynamic Tag.
* **Elementor Dynamic Tags** — Four tag classes: Contact Info, Booking Links, Social Links, Custom Variable. Appear under the "EDS Global Settings" group in Elementor's dynamic tags panel.
* **Works with Elementor Free** — No Pro licence required.
* **Modern admin UI** — Clean sidebar navigation, field-level variable reference, grid layout, smooth UX.
* **Keyboard shortcuts** — Ctrl/Cmd+S to save; Enter on last custom-data row to add a new one.
* **Lightweight** — Admin assets load only on the plugin's own admin page; zero frontend scripts.
* **Developer-friendly** — Modular class structure, WordPress coding standards, full sanitisation and escaping.

= Elementor Dynamic Tag Groups =

| Tag | Use in |
|-----|--------|
| EDS Contact Info | Text, Heading, URL, Button link |
| EDS Booking Links | Button URL, Text, Heading |
| EDS Social Links | Button URL, Icon Box link |
| EDS Custom Variable | Any text / URL field |

== Installation ==

1. Upload the `edesignspace-global-settings` folder to `/wp-content/plugins/`.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Navigate to **Global Settings** in the WordPress admin sidebar.
4. Fill in your values and click **Save Changes**.
5. In Elementor, click the dynamic-tag icon (⚡ or database icon) on any compatible field and select from the **EDS Global Settings** group.

== Frequently Asked Questions ==

= Does this work without Elementor Pro? =
Yes. All four Dynamic Tag types are registered using the base Elementor tag API available in the free version of Elementor.

= Will changes update automatically on my live site? =
Yes. Because the tags read directly from WordPress options on render, saving new values in the admin panel instantly updates every page that uses those tags — no re-publishing needed.

= How do I add unlimited custom variables? =
Go to **Global Settings → Custom Data**, click **Add Variable**, enter a title (shown in Elementor) and a value (shown on the page), then save.

= Is the plugin safe to use? =
Yes. All user inputs are sanitised on save using appropriate WordPress functions (`sanitize_text_field`, `sanitize_email`, `esc_url_raw`, `sanitize_textarea_field`). All outputs are escaped (`esc_html`, `esc_url`, `wp_kses_post`). Nonces are used for all form submissions.

= What happens to my data if I deactivate the plugin? =
Your data is preserved in `wp_options`. Re-activating the plugin will restore all settings. If you wish to fully remove the data, you can manually delete the `eds_contact_info`, `eds_booking_links`, `eds_social_links`, and `eds_custom_data` options from the database.

== Changelog ==

= 1.0.0 =
* Initial release.
* Contact Info, Booking Links, Social Media, and Custom Variables admin tabs.
* Four Elementor Dynamic Tag classes (compatible with Free & Pro).
* Modern admin UI with sidebar navigation and field reference badges.

== Upgrade Notice ==

= 1.0.0 =
Initial release — no upgrade steps required.
