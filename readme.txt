=== AMP Landing Pages ===
Contributors: the AMP WP Tools team
Version: 1.0.4
Donate link: https://ampwptools.com/amp-landing-pages/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Tags: AMP, landing pages, landing page template, conversion pages, squeeze page, page builder, landing page builder, Gutenberg
Requires at least: 4.9.5
Tested up to: 4.9.6
Stable tag: 1.0.4
Requires PHP: 5.0

The AMP Landing Pages plugin allows you to easily create native AMP landing pages for lighting fast page loads and high conversions.

== Description ==

The AMP Landing Pages plugin allows you to easily create native AMP landing pages for lighting fast page loads and high conversions. AMP Landing Pages have been are favored by Google Adwords and other ad networks due to their fast page load times and clean Javascript. 

However creating AMP pages is normally a difficult process due to the very stringent requirements for AMP validation.  The AMP Landing Pages plugin is built around the Gutenberg editor blocks but also works with the original WordPress TinyMCE editor. It should work with any theme as every landing page runs in it's own proprietary template to ensure AMP compliance. Note this may not prevent all plugins from interfering with the page output through hooks and filters, which can create AMP invalidation errors even when the page loads fine. 

To test/check your page's compliance using AMP Validator tool, please go to https://validator.ampproject.org

For more information about AMP Landing Pages please go to https://ampwptools.com/amp-landing-pages

== Installation ==

=== From within WordPress ===

1. Visit 'Plugins > Add New'
2. Search for 'AMP Landing Pages'
3. Activate AMP Landing Pages from your Plugins page.
4. Go to "after activation" below.

=== Manually ===

1. Upload the `amp-landing-pages` folder to the `/wp-content/plugins/` directory
2. Activate the AMP Landing Pages plugin through the 'Plugins' menu in WordPress
3. Go to "after activation" below.

=== After activation ===

1. Open or create a page using the Gutenberg editor.
2. Assign the page as an AMP Landing Page.
	a. Open the Document toolbar on the right side of the screen.
	b. Open the Page Attributes tab.
	c. Click on the Template: drop-down field.
	d. Choose "AMP Landing Page" from available templates.
	e. Update the page. If you have Gutenberg activated, or the page does not automatically refresh, you must refresh the page manually to open access to the landing page settings and features.
	f. At this point you may build your page on a clean slate, or we can get you started with an editable page layout by clicking the Import Demo Content button in the AMP Landing Pages meta box (below WYSIWYG if using the classic editor, right sidebar under Extended settings if using Gutenberg). The demo content is built using Gutenberg blocks, so we do recommend it for editing them, but the content HTML is also editable in the classic editor.
	g. You may also enter a custom Hero Banner Title in the AMP Landing Pages meta box (see f.). The banner uses the page title by default, clear this field to revert to using it again.
3. WP Customizer options are available for styling your AMP Landing Pages sitewide with colors, font sizes, logo, menus... etc.
	a. Open the WP Customizer.
	b. You may assign two menus for use in the plugin template. One for the top navbar (AMP Landing Pages Top Menu), and another for the slide-out mobile menu (AMPLP Mobile Menu Override). If none is assigned to the navbar menu, then the navbar will be empty... if no logo is assigned and no top menu is assigned, then the navbar will not be displayed. If none is assigned to the mobile menu, then it will populate the slide-out menu with the assigned navbar menu. 
	c. Open the Amp Landing Pages tab in the Customizer. Use the interface as you would any other Customizer setting. Due to major restrictions on how AMP pages can be handled, the preview window does not reflect all changes made in the interface, and conflict in theme vs plugin styling prevent it from being accurate.

	
== Copyright ==

AMP Landing Pages, Copyright 2018 ampwptools.com
AMP Landing Pages is distributed under the terms of the GNU GPL

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

This plugin uses the following third-party resources:

Font Awesome icons, Copyright Dave Gandy
License: SIL Open Font License, version 1.1.
Source: http://fontawesome.io/

== Changelog ==

= 1.0.4 =
* Released: August 10, 2018

- Fixed error where activation of the latest version of Gutenberg causes a duplication in page content.
- Added support for our "AMP for WooCommerce" plugin.

= 1.0.3 =
* Released: June 26, 2018

- Added custom Testimonial block with image, colors and FontAwesome quote icons.
- Added custom Media Split block with image or video/social media embed and L/C/R alignments for alternating layouts.
- Added Import Demo Content button to flesh out a landing page design with Gutenberg blocks that can be edited as well as a featured image placeholder for hero banner.
- Removed our custom editor CSS adjustments that are no longer helpful in Gutenberg 3.1.0.
- Added a copy of the Gutenberg block stylesheet into plugin for use in styling demo content without Gutenberg installed.
- Added a Customizer setting to allow the hero banner to obey the content max width setting.

= 1.0.2 =
* Released: May 30, 2018

- Added filtering/conversion of non-Gutenberg WP embeds for all supported AMP media and social module providers: Twitter, Youtube, Facebook, Instagram, Vimeo, Dailymotion, Imgur, and Reddit.
- Added support of Pinterest for non-Gutenberg WP embeds... but no embed block is available for Pinterest, and the generic Gutenbuggy embed block failed to handle the URL properly.
- Added Customizer settings for adding analytics tracking modules to landing pages: Facebook, Google Analytics, Google Tag Manager and Google Ad Words.

= 1.0.1 =
* Released: May 24, 2018

- Fixed 404 error in template_include caused by update to WP 4.9.6 and file path change in Gutenberg 9.2. 
- Changed assignment as AMP Landing Page from a meta value to template assignment in Page Attributes Template drop-down field.
- Adjusted stylesheet to further polish the display of Gutenberg blocks.
- Fixed embed block filters to compensate for the_content filter change in Gutenberg 9.2.

= 1.0 =
* Released: May 18, 2018

Initial release
