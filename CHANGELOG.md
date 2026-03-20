# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## [1.0.6]

### Changed
- Synced release metadata and maintenance files with the current plugin version.
- Refreshed GitHub release infrastructure for the updater-compatible distribution flow.

## [1.0.5] - 2024-03-01

### Changed
- Updated WordPress compatibility to version 6.7.1
- Improved plugin version reporting

### Removed
- Compatibility warning messages

## [1.0.4] - 2024-02-01

### Added
- Included required vendor dependencies in plugin distribution

## [1.0.3] - 2024-01-15

### Changed
- Updated PHP version requirement to 8.0+
- Updated installation instructions and documentation clarity

### Removed
- Legacy Internet Explorer support

### Added
- Documentation for automatic GitHub-based updates

## [1.0.2] - 2024-01-01

### Added
- Automatic update functionality via GitHub
- Plugin can now check for and install updates directly from the WordPress dashboard
- Improved plugin version management

## [1.0.0] - 2023-12-01

### Added
- Initial release with core FAQ management features
- Custom `alynt_faq` post type with `alynt_faq_collection` taxonomy
- Drag-and-drop FAQ reorder interface (FAQs > Reorder FAQs)
- Custom CSS editor (FAQs > Custom CSS)
- `[alynt_faq]` shortcode with collection, ordering, and column options
- Accessible accordion display with ARIA labels and keyboard navigation
- Mobile-first responsive design
- Theme-overridable templates (`single-alynt_faq.php`, `taxonomy-alynt_faq_collection.php`)
- Secure capability management for the `alynt_faq` post type
- Transient-based performance caching for collection queries
- FAQ Sidebar widget area
