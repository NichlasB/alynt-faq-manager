# Alynt FAQ Manager

A powerful WordPress FAQ management plugin with collections, custom ordering, and accessible accordion display.

## Features

- 📱 Responsive accordion display system
- ♿ Fully accessible with ARIA labels and keyboard navigation
- 📑 Organize FAQs into collections
- 🔄 Drag-and-drop reordering interface
- 🎨 Theme-integrated styling
- 📱 Mobile-first design
- 🔗 Individual FAQ post pages
- ⌨️ Full keyboard navigation support
- 🔍 SEO-friendly structure

## Installation

1. Download the plugin
2. Upload to `/wp-content/plugins/alynt-faq-manager`
3. Activate through WordPress plugins screen
4. Create collections under FAQs > Collections
5. Add FAQ items under FAQs > Add New

## Usage

### Basic Shortcode

[alynt_faq]

### Shortcode with Options

[alynt_faq collection-columns="2" close-opened="yes" collection="general-faq,company-faq" orderby="abc"]

### Shortcode Attributes

| Attribute | Options | Default | Description |
|-----------|---------|---------|-------------|
| collection-columns | 1, 2 | 1 | Number of columns for layout |
| close-opened | yes, no | no | Close open accordions when opening new one |
| collection | string | "" | Comma-separated collection slugs |
| orderby | menu_order, date, abc | menu_order | Sort order for FAQs |

## Template Customization

### Override Templates

Copy template files from `/templates/` to your theme:

your-theme/alynt-faq/single-alynt_faq.php your-theme/alynt-faq/archive-alynt_faq.php your-theme/alynt-faq/taxonomy-alynt_faq_collection.php

### CSS Variables

Customize appearance using CSS variables:

:root {
    --alynt-faq-icon-color: currentColor;
    --alynt-faq-border-color: #ddd;
    --alynt-faq-transition: all 0.3s ease;
}

## Developer Documentation

### Actions

// Before/After accordion
do_action('alynt_faq_before_accordion');
do_action('alynt_faq_after_accordion');

// Before/After individual questions
do_action('alynt_faq_before_question');
do_action('alynt_faq_after_question');

### Filters

// Modify template location
add_filter('alynt_faq_locate_template', 'your_function', 10, 2);

// Modify shortcode attributes
add_filter('alynt_faq_shortcode_atts', 'your_function', 10, 1);

// Modify collection arguments
add_filter('alynt_faq_collection_args', 'your_function', 10, 1);

// Modify CSS classes
add_filter('alynt_faq_question_classes', 'your_function', 10, 1);
add_filter('alynt_faq_answer_classes', 'your_function', 10, 1);

## Accessibility Features

- ARIA labels and roles
- Keyboard navigation support
- Screen reader optimized
- Focus management
- Skip to content links
- Semantic HTML structure

## Requirements

- WordPress 5.0+
- PHP 7.2+
- JavaScript enabled

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- IE11 (basic support)

## Contributing

- Fork the repository
- Create your feature branch (`git checkout -b feature/amazing-feature`)
- Commit your changes (`git commit -m 'Add amazing feature'`)
- Push to the branch (`git push origin feature/amazing-feature`)
- Open a Pull Request

## License

This project is licensed under the GPL v2 or later - see the LICENSE file for details.

## Changelog

### 1.0.0

- Initial release
- Core FAQ management functionality
- Collection organization system
- Drag-and-drop reordering
- Responsive accordion display
- Accessibility implementation
- Template override system