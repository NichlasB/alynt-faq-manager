<?php
/**
 * Theme integration hooks: thumbnails, body classes, sidebars, and archive titles.
 *
 * @package    Alynt_FAQ_Manager
 * @subpackage Alynt_FAQ_Manager/includes/frontend
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add theme support for post thumbnails and register custom FAQ image sizes.
 *
 * @since 1.0.0
 *
 * @return void
 */
function alynt_faq_setup_theme_features() {
	if ( ! current_theme_supports( 'post-thumbnails' ) ) {
		add_theme_support( 'post-thumbnails' );
	}

	// Add custom image sizes.
	add_image_size( 'alynt-faq-thumbnail', 300, 200, true );
	add_image_size( 'alynt-faq-full', 800, 400, false );
}
add_action( 'after_setup_theme', 'alynt_faq_setup_theme_features' );

/**
 * Add contextual body classes on FAQ collection and single FAQ pages.
 *
 * @since 1.0.0
 *
 * @param string[] $classes Array of body CSS class names.
 *
 * @return string[] Modified array of body CSS class names.
 */
function alynt_faq_body_classes( $classes ) {
	if ( is_tax( 'alynt_faq_collection' ) ) {
		$classes[] = 'alynt-faq-collection';
		$classes[] = 'alynt-faq-collection-' . get_queried_object()->slug;
	} elseif ( is_singular( 'alynt_faq' ) ) {
		$classes[] = 'alynt-faq-single';
	}
	return $classes;
}
add_filter( 'body_class', 'alynt_faq_body_classes' );

/**
 * Register the FAQ sidebar widget area.
 *
 * @since 1.0.0
 *
 * @return void
 */
function alynt_faq_register_sidebars() {
	if ( current_theme_supports( 'widgets' ) ) {
		register_sidebar(
			array(
				'name'          => __( 'FAQ Sidebar', 'alynt-faq' ),
				'id'            => 'alynt_faq_sidebar',
				'description'   => __( 'Widgets in this area will be shown on FAQ pages.', 'alynt-faq' ),
				'before_widget' => '<div id="%1$s" class="widget %2$s">',
				'after_widget'  => '</div>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			)
		);
	}
}
add_action( 'widgets_init', 'alynt_faq_register_sidebars' );

/**
 * Replace the archive title prefix with just the collection term name on FAQ collection pages.
 *
 * @since 1.0.0
 *
 * @param string $title The default archive title.
 *
 * @return string Modified archive title.
 */
function alynt_faq_archive_title( $title ) {
	if ( is_tax( 'alynt_faq_collection' ) ) {
		$term  = get_queried_object();
		$title = $term->name;
	}
	return $title;
}
add_filter( 'get_the_archive_title', 'alynt_faq_archive_title' );
