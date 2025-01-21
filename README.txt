=== Alynt FAQ Manager ===
Contributors: Alynt
Tags: faq, accordion, questions, answers, accessibility, responsive
Requires at least: 5.0
Tested up to: 6.7.1
Stable tag: 1.0.4
Requires PHP: 8.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A powerful and accessible FAQ management system with collections, ordering, and responsive accordion display.

== Description ==

Alynt FAQ Manager is a feature-rich FAQ plugin that helps you organize and display your frequently asked questions in an accessible and mobile-responsive format.

= Key Features =

* Create and manage FAQ collections
* Drag-and-drop reordering of FAQs
* Responsive accordion display
* Full accessibility compliance with ARIA labels and keyboard navigation
* Mobile-first design
* Customizable styling with CSS variables
* Individual FAQ post pages
* SEO-friendly structure
* Secure codebase with proper capability checks
* Performance optimized with smart caching
* Previous/Next navigation within collections
* Expandable/Collapsible accordions
* Automatic updates via GitHub integration

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
6. Optionally, customize the CSS under FAQs > Custom CSS.
7. Updates will be automatically detected and can be installed directly through the WordPress dashboard.

== Frequently Asked Questions ==

= Can I customize the colors? =

Yes, you can customize colors and other styles through:
1. The built-in Custom CSS editor (FAQs > Custom CSS)
2. Your theme's CSS using provided CSS variables
3. Direct CSS overrides in your theme

= Can I reorder my FAQs? =

Yes, go to FAQs > Reorder FAQs to drag and drop your FAQs into the desired order within each collection. The order is preserved and can be different for each collection.

= Can I display specific collections only? =

Yes, use the collection attribute in the shortcode:
[alynt_faq collection="collection-slug"]
You can also specify multiple collections: [alynt_faq collection="collection1,collection2"]

= Is it accessibility-ready? =

Yes, the plugin is fully accessibility compliant with:
* ARIA labels and roles
* Keyboard navigation support
* Screen reader optimizations
* Focus management
* Skip to content links
* Semantic HTML structure
* Proper heading hierarchy

= Is it secure? =

Yes, the plugin implements multiple security measures:
* Proper capability checks for all admin actions
* Nonce verification for forms and AJAX requests
* Input sanitization and validation
* Secure database operations
* XSS prevention through proper escaping

= How do updates work? =

The plugin automatically checks for new versions on GitHub. When an update is available, WordPress will notify you just like with any other plugin update. You can update directly through the WordPress dashboard with a single click.

== Changelog ==

= 1.0.5 =
* Updated WordPress compatibility to version 6.7.1
* Removed compatibility warning messages
* Improved plugin version reporting

= 1.0.4 =
* Included required dependencies in plugin distribution

= 1.0.3 =
* Updated PHP version requirement to 8.0+
* Removed legacy Internet Explorer support
* Added documentation for automatic updates
* Updated installation instructions
* Improved documentation clarity

= 1.0.2 =
* Added automatic update functionality via GitHub
* Plugin can now check for and install updates directly from WordPress dashboard
* Improved plugin version management

= 1.0.0 =
* Initial release with core features
* Secure capability management system
* Performance optimized caching
* Full accessibility compliance
* Mobile-first responsive design
* Custom CSS editor
* Collection management
* FAQ reordering interface

== Upgrade Notice ==

= 1.0.5 =
Compatibility update for WordPress 6.5. Update recommended for all users to remove warning messages.

= 1.0.4 =
* Included required dependencies in plugin distribution

= 1.0.3 =
Documentation improvements and system requirements updated to reflect modern standards. No functional changes.

= 1.0.2 =
Added automatic update functionality. Plugin can now self-update through WordPress dashboard.

= 1.0.0 =
Initial release with complete FAQ management system, including security features and performance optimization.

== Additional Information ==

= Template Overriding =

Override the plugin's templates by copying from `/templates/` to your theme:

* single-alynt_faq.php - Single FAQ post template
* archive-alynt_faq.php - FAQ archive template
* taxonomy-alynt_faq_collection.php - Collection archive template

Copy to either:
* your-theme/alynt-faq/[template-name].php
or
* your-theme/[template-name].php

= Styling Customization =

Customize appearance using CSS variables:

:root {
    --alynt-faq-icon-color: currentColor;
    --alynt-faq-border-color: #ddd;
    --alynt-faq-transition: all 0.3s ease;
    --alynt-faq-bg-color: transparent;
    --alynt-faq-text-color: inherit;
    --alynt-faq-hover-bg: rgba(0, 0, 0, 0.02);
}

= Developer Hooks =

Actions:
* alynt_faq_before_accordion
* alynt_faq_after_accordion
* alynt_faq_before_question
* alynt_faq_after_question
* alynt_faq_before_collection
* alynt_faq_after_collection

Filters:
* alynt_faq_locate_template
* alynt_faq_shortcode_atts
* alynt_faq_collection_args
* alynt_faq_question_classes
* alynt_faq_answer_classes

= Requirements =

* WordPress 5.0 or higher
* PHP 8.0 or higher
* JavaScript enabled
* Modern browser support (Chrome, Firefox, Safari, Edge latest versions)