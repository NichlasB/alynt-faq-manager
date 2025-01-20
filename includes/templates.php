<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Template loader for Alynt FAQ
 */
class Alynt_FAQ_Template_Loader {
    /**
     * Constructor
     */
    public function __construct() {
        add_filter('template_include', array($this, 'template_loader'));
        add_filter('single_template', array($this, 'load_single_template'), 20);
    }

    /**
     * Load a template.
     *
     * @param string $template Template to load.
     * @return string
     */
    public function template_loader($template) {
        if (is_post_type_archive('alynt_faq')) {
            $default_file = 'archive-alynt_faq.php';
            $new_template = $this->locate_template($default_file);
            return ($new_template) ? $new_template : $template;
        } elseif (is_tax('alynt_faq_collection')) {
            $default_file = 'taxonomy-alynt_faq_collection.php';
            $new_template = $this->locate_template($default_file);
            return ($new_template) ? $new_template : $template;
        }
        return $template;
    }

    /**
     * Load single FAQ template
     *
     * @param string $template Template to load.
     * @return string
     */
    public function load_single_template($template) {
        if (is_singular('alynt_faq')) {
            $plugin_template = ALYNT_FAQ_PLUGIN_DIR . 'templates/single-alynt_faq.php';
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
        }
        return $template;
    }

    /**
     * Locate a template and return the path for inclusion.
     *
     * @param string $template_name Template to load.
     * @return string
     */
    public function locate_template($template_name) {
        $template = '';

        // Check theme directory first
        $theme_template = locate_template(array(
            "alynt-faq/{$template_name}",
            $template_name
        ));

        if ($theme_template) {
            $template = $theme_template;
        } else {
            // Check plugin directory
            $plugin_template = ALYNT_FAQ_PLUGIN_DIR . 'templates/' . $template_name;
            if (file_exists($plugin_template)) {
                $template = $plugin_template;
            }
        }

        return apply_filters('alynt_faq_locate_template', $template, $template_name);
    }
}

// Initialize template loader
$GLOBALS['alynt_faq_template_loader'] = new Alynt_FAQ_Template_Loader();

/**
 * Add theme support for post thumbnails if not already added
 */
function alynt_faq_setup_theme_features() {
    if (!current_theme_supports('post-thumbnails')) {
        add_theme_support('post-thumbnails');
    }
    
    // Add custom image sizes
    add_image_size('alynt-faq-thumbnail', 300, 200, true);
    add_image_size('alynt-faq-full', 800, 400, false);
}
add_action('after_setup_theme', 'alynt_faq_setup_theme_features');

/**
 * Add body classes for FAQ pages
 */
function alynt_faq_body_classes($classes) {
    if (is_post_type_archive('alynt_faq')) {
        $classes[] = 'alynt-faq-archive';
    } elseif (is_tax('alynt_faq_collection')) {
        $classes[] = 'alynt-faq-collection';
        $classes[] = 'alynt-faq-collection-' . get_queried_object()->slug;
    } elseif (is_singular('alynt_faq')) {
        $classes[] = 'alynt-faq-single';
    }
    return $classes;
}
add_filter('body_class', 'alynt_faq_body_classes');

/**
 * Register sidebars for FAQ templates
 */
function alynt_faq_register_sidebars() {
    if (current_theme_supports('widgets')) {
        register_sidebar(array(
            'name'          => 'FAQ Sidebar',
            'id'            => 'alynt_faq_sidebar',
            'description'   => 'Widgets in this area will be shown on FAQ pages.',
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h2 class="widget-title">',
            'after_title'   => '</h2>',
        ));
    }
}
add_action('widgets_init', 'alynt_faq_register_sidebars');

/**
 * Filter the archive title for FAQ archives
 */
function alynt_faq_archive_title($title) {
    if (is_post_type_archive('alynt_faq')) {
        $title = 'Frequently Asked Questions';
    } elseif (is_tax('alynt_faq_collection')) {
        $term = get_queried_object();
        $title = $term->name;
    }
    return $title;
}
add_filter('get_the_archive_title', 'alynt_faq_archive_title');

/**
 * Add custom template variables
 */
function alynt_faq_template_variables() {
    if (is_singular('alynt_faq')) {
        global $alynt_faq_template_vars;
        $alynt_faq_template_vars = array(
            'collections' => get_the_terms(get_the_ID(), 'alynt_faq_collection'),
            'post_date' => get_the_date(),
            'modified_date' => get_the_modified_date(),
        );
    }
}
add_action('template_redirect', 'alynt_faq_template_variables');