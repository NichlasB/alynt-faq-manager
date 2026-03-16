# Hooks Reference

All actions and filters provided by Alynt FAQ Manager for theme and plugin developers.

---

## Filters

### `alynt_faq_locate_template`

Filters the resolved path to a FAQ template file before it is loaded, allowing complete template path overrides by third parties.

**File:** `includes/class-alynt-faq-template-loader.php`
**Since:** 1.0.0

**Parameters:**
- `$template` (string) — Absolute path to the located template file, or empty string if not found.
- `$template_name` (string) — The template filename being located (e.g. `'single-alynt_faq.php'`).

**Return:** (string) Modified absolute path to the template file.

**Example:**
```php
add_filter( 'alynt_faq_locate_template', function( $template, $template_name ) {
    if ( 'taxonomy-alynt_faq_collection.php' === $template_name ) {
        return get_stylesheet_directory() . '/my-custom-faq-taxonomy.php';
    }
    return $template;
}, 10, 2 );
```

---

### `alynt_faq_shortcode_atts`

Filters the fully normalized shortcode attribute array after defaults have been applied and values validated.

**File:** `includes/frontend/shortcode.php`
**Since:** 1.0.0

**Parameters:**
- `$atts` (array) — Normalized attribute array with the following keys:
  - `collection-columns` (int) — `1` or `2`
  - `close-opened` (string) — `'yes'` or `'no'`
  - `collection` (string) — Comma-separated collection slugs, or empty string for all
  - `orderby` (string) — `'menu_order'`, `'date'`, or `'abc'`

**Return:** (array) Modified attribute array.

**Example:**
```php
add_filter( 'alynt_faq_shortcode_atts', function( $atts ) {
    // Force two-column layout site-wide.
    $atts['collection-columns'] = 2;
    return $atts;
} );
```

---

### `alynt_faq_collection_args`

Filters the `WP_Query` arguments array used to fetch FAQ posts for a collection before the query is run.

**File:** `includes/frontend/collection-renderer.php`
**Since:** 1.0.0

**Parameters:**
- `$args` (array) — `WP_Query` arguments including `post_type`, `posts_per_page`, `tax_query`, `orderby`, and `order`.

**Return:** (array) Modified `WP_Query` arguments.

**Example:**
```php
add_filter( 'alynt_faq_collection_args', function( $args ) {
    // Exclude a specific FAQ post.
    $args['post__not_in'] = array( 42 );
    return $args;
} );
```

---

### `alynt_faq_question_classes`

Filters the array of CSS class names applied to the FAQ question `<button>` element.

**File:** `includes/frontend/collection-renderer.php`
**Since:** 1.0.0

**Parameters:**
- `$classes` (string[]) — Array of CSS class names. Default: `array( 'faq-question' )`.

**Return:** (string[]) Modified array of CSS class names.

**Example:**
```php
add_filter( 'alynt_faq_question_classes', function( $classes ) {
    $classes[] = 'my-custom-question-class';
    return $classes;
} );
```

---

### `alynt_faq_answer_classes`

Filters the array of CSS class names applied to the FAQ answer `<div>` element.

**File:** `includes/frontend/collection-renderer.php`
**Since:** 1.0.0

**Parameters:**
- `$classes` (string[]) — Array of CSS class names. Default: `array( 'faq-answer' )`.

**Return:** (string[]) Modified array of CSS class names.

**Example:**
```php
add_filter( 'alynt_faq_answer_classes', function( $classes ) {
    $classes[] = 'my-custom-answer-class';
    return $classes;
} );
```

---

## Actions

The following WordPress core action hooks are used internally by the plugin to clear caches. They are not designed as extension points but are listed here for completeness.

| Hook | Context |
|------|---------|
| `save_post_alynt_faq` | Clears collection transient cache when an FAQ post is saved. |
| `edited_alynt_faq_collection` | Clears collection transient cache when a collection term is edited. |
| `created_alynt_faq_collection` | Clears collection transient cache when a new collection term is created. |
| `deleted_alynt_faq_collection` | Clears collection transient cache when a collection term is deleted. |

### `alynt_faq_before_accordion` *(planned)*

> **Note:** This hook is documented in the README for future use but is not yet implemented in the codebase.

### `alynt_faq_after_accordion` *(planned)*

> **Note:** This hook is documented in the README for future use but is not yet implemented in the codebase.

### `alynt_faq_before_question` *(planned)*

> **Note:** This hook is documented in the README for future use but is not yet implemented in the codebase.

### `alynt_faq_after_question` *(planned)*

> **Note:** This hook is documented in the README for future use but is not yet implemented in the codebase.
