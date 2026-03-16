# Settings Reference

All database options used by Alynt FAQ Manager.

## Options

| Option Key | Type | Default | Sanitization | Set By | Description |
|------------|------|---------|--------------|--------|-------------|
| `alynt_faq_version` | string | `'0'` | `update_option` (raw) | Plugin activation / `plugins_loaded` hook | Stores the installed plugin version. Used to detect when an upgrade routine should run. |
| `alynt_faq_custom_css` | string | `''` | `wp_strip_all_tags` + harmful-pattern check | FAQs > Custom CSS admin page | User-authored CSS injected into the front-end `<head>` via a `<style>` tag. Validated to contain `{`/`}` pairs and to be free of `expression`, `javascript:`, `behavior:`, `-moz-binding`, `@import`, and `data:` patterns before saving. |

## Notes

- **`alynt_faq_version`** is set with `add_option()` on first activation and updated with `update_option()` on each version bump detected at `plugins_loaded`.
- **`alynt_faq_custom_css`** is saved via the `wp_ajax_alynt_faq_save_custom_css` AJAX action, which requires the `edit_theme_options` capability and verifies the `alynt_faq_custom_css` nonce before writing.
