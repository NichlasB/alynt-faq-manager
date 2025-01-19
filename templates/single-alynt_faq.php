<?php
/**
 * Template for displaying single FAQ posts
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header(); ?>

<div class="alynt-faq-single-container">
    <main id="main" class="alynt-faq-single-content">
        <?php while (have_posts()) : the_post(); 
            // Get the collection terms
            $collections = get_the_terms(get_the_ID(), 'alynt_faq_collection');
            ?>
            
            <article id="post-<?php the_ID(); ?>" <?php post_class('alynt-faq-single'); ?>>
                <header class="entry-header">
                    <?php if ($collections && !is_wp_error($collections)) : ?>
                        <div class="faq-collections">
                            <?php foreach ($collections as $collection) : ?>
                                <span class="faq-collection-name">
                                    <?php echo esc_html($collection->name); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <h1 class="entry-title"><?php the_title(); ?></h1>

                    <div class="entry-meta">
                        <span class="published-date">
                            Published: <?php echo get_the_date(); ?>
                        </span>
                        <?php if (get_the_modified_date() !== get_the_date()) : ?>
                            <span class="updated-date">
                                Last Updated: <?php echo get_the_modified_date(); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </header>

                <div class="entry-content">
                    <?php the_content(); ?>
                </div>

                <footer class="entry-footer">
                    <?php
                    // Get previous and next posts in the same collection
                    if ($collections && !is_wp_error($collections)) {
                        $current_collection = $collections[0]; // Use the first collection if multiple exist
                        
                        $args = array(
                            'post_type' => 'alynt_faq',
                            'posts_per_page' => -1,
                            'orderby' => 'menu_order',
                            'order' => 'ASC',
                            'tax_query' => array(
                                array(
                                    'taxonomy' => 'alynt_faq_collection',
                                    'field' => 'term_id',
                                    'terms' => $current_collection->term_id
                                )
                            )
                        );

                        $collection_faqs = new WP_Query($args);
                        $faq_ids = wp_list_pluck($collection_faqs->posts, 'ID');
                        $current_key = array_search(get_the_ID(), $faq_ids);

                        if ($current_key !== false) {
                            $prev_post_id = isset($faq_ids[$current_key - 1]) ? $faq_ids[$current_key - 1] : 0;
                            $next_post_id = isset($faq_ids[$current_key + 1]) ? $faq_ids[$current_key + 1] : 0;

                            echo '<nav class="faq-navigation">';
                            
                            if ($prev_post_id) {
                                printf(
                                    '<div class="nav-previous"><span class="nav-subtitle">Previous FAQ</span><a href="%s">%s</a></div>',
                                    esc_url(get_permalink($prev_post_id)),
                                    esc_html(get_the_title($prev_post_id))
                                );
                            }

                            if ($next_post_id) {
                                printf(
                                    '<div class="nav-next"><span class="nav-subtitle">Next FAQ</span><a href="%s">%s</a></div>',
                                    esc_url(get_permalink($next_post_id)),
                                    esc_html(get_the_title($next_post_id))
                                );
                            }

                            echo '</nav>';
                        }
                        wp_reset_postdata();
                    }
                    ?>
                </footer>
            </article>

        <?php endwhile; ?>
    </main>
</div>

<?php
// Add template-specific styles
add_action('wp_footer', 'alynt_faq_single_styles');
function alynt_faq_single_styles() {
    ?>
    <style>
        .alynt-faq-single-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .alynt-faq-single {
            background: #fff;
            padding: 2rem;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .faq-collections {
            margin-bottom: 1rem;
        }

        .faq-collection-name {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background: #f0f0f0;
            border-radius: 3px;
            font-size: 0.875rem;
            margin-right: 0.5rem;
        }

        .entry-title {
            margin: 0 0 1rem;
            font-size: 2rem;
            line-height: 1.3;
        }

        .entry-meta {
            font-size: 0.875rem;
            color: #666;
            margin-bottom: 2rem;
        }

        .entry-meta span {
            display: inline-block;
            margin-right: 1rem;
        }

        .entry-content {
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .faq-navigation {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #eee;
        }

        .nav-previous,
        .nav-next {
            display: flex;
            flex-direction: column;
        }

        .nav-next {
            text-align: right;
        }

        .nav-subtitle {
            font-size: 0.875rem;
            color: #666;
            margin-bottom: 0.5rem;
        }

        .faq-navigation a {
            color: inherit;
            text-decoration: none;
            font-weight: 500;
        }

        .faq-navigation a:hover {
            text-decoration: underline;
        }

        @media (max-width: 600px) {
            .alynt-faq-single {
                padding: 1rem;
            }

            .entry-title {
                font-size: 1.5rem;
            }

            .faq-navigation {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .nav-next {
                text-align: left;
            }
        }
    </style>
    <?php
}

get_footer();