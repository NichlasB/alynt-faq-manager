<?php
/**
 * Single FAQ Template
 */

// Enqueue the single FAQ styles
wp_enqueue_style(
    'alynt-faq-single', 
    ALYNT_FAQ_PLUGIN_URL . 'assets/css/single-faq.css',
    array(),
    ALYNT_FAQ_VERSION
);

get_header(); ?>

<main class="alynt-faq-wrapper">
    <div class="alynt-faq-container">
        <!-- Title Section -->
        <header class="alynt-faq-header">
            <?php 
            $collections = get_the_terms(get_the_ID(), 'alynt_faq_collection');
            if ($collections && !is_wp_error($collections)) : ?>
                <div class="alynt-faq-collections">
                    <?php foreach ($collections as $collection) : ?>
                        <span class="alynt-faq-collection-name"><?php echo esc_html($collection->name); ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <h1 class="alynt-faq-title"><?php the_title(); ?></h1>
            
            <div class="alynt-faq-meta">
                <span class="alynt-faq-publish-date">Published: <?php echo get_the_date(); ?></span>
                <?php if (get_the_modified_date() !== get_the_date()) : ?>
                <span class="alynt-faq-updated-date">Last Updated: <?php echo get_the_modified_date(); ?></span>
                <?php endif; ?>
            </div>
        </header>

        <!-- Main Content Section -->
        <div class="alynt-faq-content">
            <div class="alynt-faq-content-wrap">
                <?php the_content(); ?>
            </div>
        </div>

        <!-- Navigation Section -->
        <?php
        if ($collections && !is_wp_error($collections)) {
            $current_collection = $collections[0]; // Use first collection if multiple exist
            
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

                if ($prev_post_id || $next_post_id) : ?>
                    <nav class="alynt-faq-navigation">
                        <div class="alynt-faq-nav-grid">
                            <?php if ($prev_post_id) : ?>
                                <a href="<?php echo get_permalink($prev_post_id); ?>" class="alynt-faq-nav-item prev">
                                    <span class="nav-label">Previous FAQ</span>
                                    <h3><?php echo get_the_title($prev_post_id); ?></h3>
                                </a>
                            <?php endif; ?>

                            <?php if ($next_post_id) : ?>
                                <a href="<?php echo get_permalink($next_post_id); ?>" class="alynt-faq-nav-item next">
                                    <span class="nav-label">Next FAQ</span>
                                    <h3><?php echo get_the_title($next_post_id); ?></h3>
                                </a>
                            <?php endif; ?>
                        </div>
                    </nav>
                <?php endif;
            }
            wp_reset_postdata();
        }
        ?>
    </div>
</main>

<?php get_footer(); ?>