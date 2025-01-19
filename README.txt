=== Alynt FAQ Manager ===
Contributors: Alynt
Tags: faq, accordion, questions, answers
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.0
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A powerful and accessible FAQ management system with collections, ordering, and responsive accordion display.

== Description ==

Alynt FAQ Manager is a feature-rich FAQ plugin that helps you organize and display your frequently asked questions in an accessible and mobile-responsive format.

= Key Features =

* Create and manage FAQ collections
* Drag-and-drop reordering of FAQs
* Responsive accordion display
* Accessibility-ready with ARIA labels and keyboard navigation
* Mobile-friendly design
* Customizable styling that inherits from your theme
* Individual FAQ post pages
* Previous/Next navigation within collections
* Expandable/Collapsible accordions

= Shortcode Usage =

Basic usage:
[alynt_faq]

With attributes:
[alynt_faq collection-columns="2" close-opened="yes" collection="general-faq,company-faq" orderby="abc"]

Available attributes:

* collection-columns: "1" or "2" (default: "1")
* close-opened: "yes" or "no" (default: "no")
* collection: comma-separated collection slugs (default: all collections)
* orderby: "menu_order", "date", or "abc" (default: "menu_order")

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/alynt-faq-manager` directory, or install the plugin through the WordPress plugins screen.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Create FAQ collections under FAQs > Collections.
4. Add FAQ items under FAQs > Add New.
5. Use the shortcode [alynt_faq] to display your FAQs.

== Frequently Asked Questions ==

= Can I customize the colors? =

Yes, the plugin inherits colors from your theme and provides CSS variables for further customization.

= Can I reorder my FAQs? =

Yes, go to FAQs > Reorder FAQs to drag and drop your FAQs into the desired order within each collection.

= Can I display specific collections only? =

Yes, use the collection attribute in the shortcode:
[alynt_faq collection="collection-slug"]

= Is it accessibility-ready? =

Yes, the plugin includes:
* ARIA labels
* Keyboard navigation
* Screen reader support
* Skip to content links
* Proper heading structure

== Screenshots ==

1. FAQ accordion display
2. Admin interface
3. Reordering interface
4. Single FAQ post view

== Changelog ==

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.0 =
Initial release

== Additional Information ==

= Template Overriding =

You can override the plugin's templates by copying them from the plugin's `/templates/` directory to your theme:

* single-alynt_faq.php - Single FAQ post template
* archive-alynt_faq.php - FAQ archive template
* taxonomy-alynt_faq_collection.php - Collection archive template

Copy these files to:
* your-theme/alynt-faq/[template-name].php
or
* your-theme/[template-name].php

= Styling Customization =

The plugin uses CSS variables that can be customized in your theme:

:root {
    --alynt-faq-icon-color: currentColor;
    --alynt-faq-border-color: #ddd;
    --alynt-faq-transition: all 0.3s ease;
}
= Developer Hooks =

Actions:

alynt_faq_before_accordion
alynt_faq_after_accordion
alynt_faq_before_question
alynt_faq_after_question
Filters:

alynt_faq_locate_template
alynt_faq_shortcode_atts
alynt_faq_collection_args
alynt_faq_question_classes
alynt_faq_answer_classes
= Requirements =

WordPress 5.0 or higher
PHP 7.2 or higher
JavaScript enabled in the browser