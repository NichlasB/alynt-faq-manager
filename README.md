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
- 🔄 Automatic updates via GitHub

## Installation

1. Download the plugin
2. Upload to `/wp-content/plugins/alynt-faq-manager`
3. Activate through WordPress plugins screen
4. Create collections under FAQs > Collections
5. Add FAQ items under FAQs > Add New
6. Updates will be automatically detected and can be installed through WordPress

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

```css
:root {
    --alynt-faq-icon-color: currentColor;
    --alynt-faq-border-color: #ddd;
    --alynt-faq-transition: all 0.3s ease;
}
```

## Developer Documentation

See [docs/HOOKS.md](docs/HOOKS.md) for the full hook reference with parameter descriptions and examples.

### Actions

```php
// Before/After accordion
do_action('alynt_faq_before_accordion');
do_action('alynt_faq_after_accordion');

// Before/After individual questions
do_action('alynt_faq_before_question');
do_action('alynt_faq_after_question');
```

### Filters

```php
// Modify template location
add_filter('alynt_faq_locate_template', 'your_function', 10, 2);

// Modify shortcode attributes
add_filter('alynt_faq_shortcode_atts', 'your_function', 10, 1);

// Modify collection query arguments
add_filter('alynt_faq_collection_args', 'your_function', 10, 1);

// Modify CSS classes
add_filter('alynt_faq_question_classes', 'your_function', 10, 1);
add_filter('alynt_faq_answer_classes', 'your_function', 10, 1);
```

## Settings

The plugin adds two admin pages under the FAQs menu:

- **Reorder FAQs** — Drag-and-drop interface for setting FAQ display order within each collection.
- **Custom CSS** — In-browser CSS editor for styling the FAQ accordion. CSS is stored in the `alynt_faq_custom_css` database option and output in the front-end `<head>`.

See [docs/SETTINGS.md](docs/SETTINGS.md) for a full reference of all database options.

## FAQ

**How do I display only one collection?**
Use the `collection` attribute with the collection slug: `[alynt_faq collection="general-faq"]`

**How do I display multiple specific collections?**
Pass a comma-separated list of slugs: `[alynt_faq collection="general-faq,company-faq"]`

**How do I override a template?**
Copy the template file from the plugin's `/templates/` directory to `your-theme/alynt-faq/` and edit it there. The plugin checks the theme directory first before falling back to its own templates.

**How do I change the FAQ sort order?**
Use the `orderby` attribute (`menu_order`, `date`, or `abc`), or use **FAQs > Reorder FAQs** for drag-and-drop control when `orderby="menu_order"`.

**How do I add custom CSS without editing theme files?**
Go to **FAQs > Custom CSS** in the WordPress admin and enter your CSS there.

## Accessibility Features

- ARIA labels and roles
- Keyboard navigation support
- Screen reader optimized
- Focus management
- Skip to content links
- Semantic HTML structure

## Requirements

- WordPress 5.0+
- PHP 8.0+
- JavaScript enabled

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## Contributing

- Fork the repository
- Create your feature branch (`git checkout -b feature/amazing-feature`)
- Commit your changes (`git commit -m 'Add amazing feature'`)
- Push to the branch (`git push origin feature/amazing-feature`)
- Open a Pull Request

## License

This project is licensed under the GPL v2 or later - see the LICENSE file for details.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for the full version history.