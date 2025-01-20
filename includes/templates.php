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
        // Move template filters to init to ensure proper loading
        add_action('init', array($this, 'init_template_filters'));
    }

    /**
     * Initialize template filters
     */
    public function init_template_filters() {
        add_filter('template_include', array($this, 'template_loader'));
        add_filter('single_template', array($this, 'load_single_template'));
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
            $template = $this->locate_template($default_file, $template);
        } elseif (is_tax('alynt_faq_collection')) {
            $default_file = 'taxonomy-alynt_faq_collection.php';
            $template = $this->locate_template($default_file, $template);
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
            $default_file = 'single-alynt_faq.php';
            $template = $this->locate_template($default_file, $template);
        }
        
        return $template;
    }

    /**
     * Locate a template and return the path for inclusion.
     *
     * @param string $template_name Template to load.
     * @param string $default_path Default path to template files.
     * @return string
     */
    public function locate_template($template_name, $default_path = '') {
        // Look within passed path within the theme - this is priority.
        $template = locate_template(
            array(
                "alynt-faq/{$template_name}",
                $template_name,
            )
        );

        // Get default template from plugin.
        if (!$template && $default_path) {
            $template = $default_path;
        }

        // If we still don't have a template, get the plugin default.
        if (!$template) {
            $template = ALYNT_FAQ_PLUGIN_DIR . 'templates/' . $template_name;
        }

        // Return what we found.
        return apply_filters('alynt_faq_locate_template', $template, $template_name);
    }
}

// Initialize template loader on init with lower priority
function alynt_faq_init_template_system() {
    global $alynt_faq_template_loader;
    if (!isset($alynt_faq_template_loader)) {
        $alynt_faq_template_loader = new Alynt_FAQ_Template_Loader();
    }
}
add_action('init', 'alynt_faq_init_template_system', 5);

// Register theme supports and features on after_setup_theme
function alynt_faq_setup_theme_features() {
    // Add post thumbnail support if needed
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
 * Add wrapper div to FAQ content
 */
function alynt_faq_content_wrapper($content) {
    if (is_singular('alynt_faq')) {
        $content = '<div class="alynt-faq-content">' . $content . '</div>';
    }
    return $content;
}
add_filter('the_content', 'alynt_faq_content_wrapper');

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