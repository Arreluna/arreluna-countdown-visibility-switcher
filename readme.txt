=== Arreluna – Countdown Visibility Switcher ===
Contributors: arreluna
Tags: countdown, redirect, timer, shortcode, visibility
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://checkout.arreluna.com/acvs-donation/

Create evergreen and fixed-date countdowns that switch content visibility or redirect visitors when they expire.

== Description ==

Countdown Visibility Switcher lets you create reusable countdown timers from the WordPress admin and display them anywhere with a shortcode.

The plugin is designed for landing pages, launches, limited-time offers, webinars, course campaigns, and evergreen funnels. It works with the WordPress block editor and most page builders because it does not force you to build the offer layout inside the plugin.

Instead, each countdown can generate two simple CSS classes:

* A before class for content that should be visible before the countdown expires.
* An after class for content that should be visible after the countdown expires.

For example, you can add the countdown shortcode to a landing page, add the generated before class to an offer button, and add the generated after class to an alternative button or message.

If you do not want to switch content, you can configure the countdown to redirect visitors immediately when the timer expires.

== Features ==

* Create unlimited countdowns from the WordPress admin.
* Display each countdown with a shortcode.
* Evergreen countdowns based on the visitor's browser localStorage.
* Fixed date and time countdowns based on the WordPress site timezone.
* Show or hide content with generated before/after CSS classes.
* Optional immediate redirect when a countdown expires.
* Basic redirect loop protection: if the current page is already the redirect URL, the plugin hides the expired countdown instead of redirecting again.
* Active/inactive status per countdown.
* Option to keep an expired countdown visible at zero or hide it.
* Per-countdown unit settings: days, hours, minutes, seconds.
* Per-countdown labels for each time unit.
* Global frontend style settings.
* Evergreen reset tool that restarts a countdown for everyone by incrementing the internal browser storage version.
* No external services and no visitor tracking.

== How it works ==

1. Go to Countdowns > Add New.
2. Choose Evergreen or Fixed date and time.
3. Configure what happens when the countdown expires.
4. Copy the generated shortcode.
5. Add the shortcode to your page.
6. If you choose show/hide mode, add the generated before/after classes to the content you want to control.
7. If you choose redirect mode, enter the destination URL instead.

Example shortcode:

`[acvs_countdown id="123"]`

Example classes in show/hide mode:

* `acvs-before-123` shows content before the countdown expires.
* `acvs-after-123` shows content after the countdown expires.

Example HTML:

`<div class="acvs-before-123">This content is visible before expiration.</div>`

`<div class="acvs-after-123">This content is visible after expiration.</div>`

In page builders such as Elementor, Divi, Kadence Blocks, or the WordPress block editor, you usually only need to add the class name to the block, section, row, column, or button.

== Countdown types ==

= Evergreen =

Evergreen countdowns start individually for each visitor. The expiration timestamp is stored in the visitor's browser localStorage.

This is useful for evergreen offers, automated funnels, or personal deadlines that start when someone first visits the page.

Because this mode uses browser storage, a visitor can restart the timer by clearing browser data, using a different browser, using private/incognito mode, or changing device.

= Fixed date and time =

Fixed-date countdowns expire at the same date and time for everyone. The fixed deadline uses the WordPress site timezone.

This is useful for launches, live campaigns, webinars, and real deadlines.

== Expiration actions ==

= Show/hide content =

Use this mode when you want to switch page content when the countdown expires. The plugin gives you two classes for each countdown: one for before expiration and one for after expiration.

= Redirect immediately =

Use this mode when you want visitors to be redirected as soon as the countdown expires. There is no delay.

Avoid redirecting to a page that contains the same expired countdown with the same redirect URL. The plugin includes basic loop protection: if the current page is already the redirect URL, it will hide the expired countdown instead of redirecting again.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/` or install the ZIP from Plugins > Add New > Upload Plugin.
2. Activate the plugin.
3. Go to Countdowns > Add New.
4. Create a countdown and copy its shortcode.
5. Add the shortcode to your page.
6. If you choose show/hide mode, add the generated before/after classes to the content you want to control.
7. Optional: go to Countdowns > Styles to customize the global countdown appearance.

== Frequently Asked Questions ==

= Can visitors reset an evergreen countdown? =

Evergreen countdowns are stored in the visitor's browser localStorage. If a visitor clears browser data, changes browser, uses private/incognito mode, or changes device, the countdown can restart.

= Can I reset a countdown for everyone? =

Yes. Edit an evergreen countdown and use the reset button. The plugin increments the internal storage version, creating a new browser storage key for that countdown.

Save any countdown setting changes before using the reset button. Resetting an evergreen countdown does not save unsaved changes on the edit screen.

The reset option is not shown for fixed-date countdowns because fixed-date countdowns expire at a global date and time.

= Does this work with page cache? =

Yes. Countdown state is handled in JavaScript in the visitor's browser, so the plugin is compatible with most static page cache setups.

= Can I use a fixed deadline instead of an evergreen timer? =

Yes. Choose Fixed date and time when editing the countdown. Fixed countdowns use the WordPress site timezone.

= Can I redirect visitors when the countdown expires? =

Yes. Choose Redirect immediately to a URL under Action when expired and enter a Redirect URL. The redirect has no delay. Visibility classes are only needed for the show/hide action.

= What happens if the redirect URL is the current page? =

The plugin will not redirect again. It hides the expired countdown to avoid a redirect loop.

= Can I use this with page builders? =

Yes. The plugin generates simple CSS classes, so it works with builders that allow custom CSS classes on sections, rows, columns, buttons, or blocks.

= Can I customize the countdown design? =

Yes. Go to Countdowns > Styles to configure the global frontend style settings.

= Does the plugin collect data? =

No. The plugin does not send visitor data to external services. Evergreen expiration data is stored locally in the visitor's browser.

== Privacy ==

Countdown Visibility Switcher does not collect personal data, does not use external services, and does not send visitor data to third parties.

For evergreen countdowns, the plugin stores the expiration timestamp in the visitor's browser localStorage. This data stays in the visitor's browser and is used only to determine the countdown state.

== Screenshots ==

1. Countdown edit screen with timer settings, expiration behavior, units, and labels.
2. Shortcode and usage panel with copyable shortcode and generated classes.
3. Global style settings screen.
4. Frontend countdown example.

== Changelog ==

= 1.0.0 =
* Initial public release.

== Upgrade Notice ==

= 1.0.0 =
Initial public release.
