<?php
/**
 * Single FAQ Template
 */

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

<style>
/* Basic FAQ Template Styles */
.alynt-faq-wrapper {
    padding: 40px 20px;
    min-height: 100vh;
}

.alynt-faq-container {
    margin: 0 auto;
    max-width: 960px;
    padding: 20px;
}

.alynt-faq-header {
    margin-bottom: 30px;
    text-align: center;
}

.alynt-faq-collections {
    margin-bottom: 20px;
}

.alynt-faq-collection-name {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    background: #f0f0f0;
    border-radius: 3px;
    font-size: 0.875rem;
    margin: 0 0.25rem;
}

.alynt-faq-title {
    font-size: 2.5rem;
    line-height: 1.2;
    margin-bottom: 20px;
}

.alynt-faq-meta {
    display: flex;
    justify-content: center;
    gap: 20px;
    font-size: 1.1rem;
}

.alynt-faq-content {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 0 2px rgba(0, 0, 0, 0.1);
    margin-bottom: 40px;
    padding: 30px;
}

.alynt-faq-content-wrap {
    margin: auto;
    max-width: 640px;
}

.alynt-faq-navigation {
    margin-top: 60px;
}

.alynt-faq-nav-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.alynt-faq-nav-item {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    padding: 15px;
    text-decoration: none;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.alynt-faq-nav-item:hover {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    transform: translateY(-3px);
}

.alynt-faq-nav-item .nav-label {
    display: block;
    font-size: 0.875rem;
    margin-bottom: 5px;
}

.alynt-faq-nav-item h3 {
    margin: 0;
    font-size: 1.1rem;
}

@media (max-width: 999px) {
    .alynt-faq-wrapper {
        padding: 50px 0;
    }

    .alynt-faq-container {
        padding: 0;
    }

    .alynt-faq-header {
        padding: 0 20px;
    }
    
    .alynt-faq-title {
        font-size: 1.7rem;
    }

    .alynt-faq-content {
        border-radius: 0;
        margin-left: 0;
        margin-right: 0;
        padding: 30px 20px;
    }
}

@media (max-width: 768px) {
    .alynt-faq-nav-grid {
        grid-template-columns: 1fr;
    }

    .alynt-faq-meta {
        flex-direction: column;
        gap: 10px;
    }
}

@media (max-width: 480px) {
    .alynt-faq-content {
        padding: 20px;
    }
}
</style>

<?php get_footer(); ?>