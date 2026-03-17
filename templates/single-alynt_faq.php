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
                <span class="alynt-faq-publish-date"><?php echo esc_html__('Published:', 'alynt-faq'); ?> <?php echo esc_html(get_the_date()); ?></span>
                <?php if (get_the_modified_date() !== get_the_date()) : ?>
                <span class="alynt-faq-updated-date"><?php echo esc_html__('Last Updated:', 'alynt-faq'); ?> <?php echo esc_html(get_the_modified_date()); ?></span>
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
            $current_post_id = get_the_ID();
            $current_menu_order = (int) get_post_field('menu_order', get_the_ID());

            $nav_base_args = array(
                'post_type'              => 'alynt_faq',
                'posts_per_page'         => 1,
                'no_found_rows'          => true,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
                'post__not_in'           => array($current_post_id),
                'tax_query'              => array(
                    array(
                        'taxonomy' => 'alynt_faq_collection',
                        'field'    => 'term_id',
                        'terms'    => $current_collection->term_id,
                    ),
                ),
            );

            $prev_args = array_merge($nav_base_args, array(
                'orderby' => array(
                    'menu_order' => 'DESC',
                    'ID' => 'DESC',
                ),
            ));
            add_filter('posts_where', $prev_where_filter = function ($where) use ($current_menu_order, $current_post_id) {
                global $wpdb;
                $where .= $wpdb->prepare(
                    " AND ({$wpdb->posts}.menu_order < %d OR ({$wpdb->posts}.menu_order = %d AND {$wpdb->posts}.ID < %d))",
                    $current_menu_order,
                    $current_menu_order,
                    $current_post_id
                );
                return $where;
            });
            $prev_query = new WP_Query($prev_args);
            remove_filter('posts_where', $prev_where_filter);
            $prev_post_id = $prev_query->have_posts() ? $prev_query->posts[0]->ID : 0;

            $next_args = array_merge($nav_base_args, array(
                'orderby' => array(
                    'menu_order' => 'ASC',
                    'ID' => 'ASC',
                ),
            ));
            add_filter('posts_where', $next_where_filter = function ($where) use ($current_menu_order, $current_post_id) {
                global $wpdb;
                $where .= $wpdb->prepare(
                    " AND ({$wpdb->posts}.menu_order > %d OR ({$wpdb->posts}.menu_order = %d AND {$wpdb->posts}.ID > %d))",
                    $current_menu_order,
                    $current_menu_order,
                    $current_post_id
                );
                return $where;
            });
            $next_query = new WP_Query($next_args);
            remove_filter('posts_where', $next_where_filter);

            $next_post_id = $next_query->have_posts() ? $next_query->posts[0]->ID : 0;

            if ($prev_post_id || $next_post_id) : ?>
                <nav class="alynt-faq-navigation" aria-label="<?php esc_attr_e('FAQ Navigation', 'alynt-faq'); ?>">
                    <div class="alynt-faq-nav-grid">
                        <?php if ($prev_post_id) : ?>
                            <a href="<?php echo esc_url(get_permalink($prev_post_id)); ?>" class="alynt-faq-nav-item prev">
                                <span class="nav-label"><?php esc_html_e('Previous FAQ', 'alynt-faq'); ?></span>
                                <h3><?php echo esc_html(get_the_title($prev_post_id)); ?></h3>
                            </a>
                        <?php endif; ?>

                        <?php if ($next_post_id) : ?>
                            <a href="<?php echo esc_url(get_permalink($next_post_id)); ?>" class="alynt-faq-nav-item next">
                                <span class="nav-label"><?php esc_html_e('Next FAQ', 'alynt-faq'); ?></span>
                                <h3><?php echo esc_html(get_the_title($next_post_id)); ?></h3>
                            </a>
                        <?php endif; ?>
                    </div>
                </nav>
            <?php endif;
            wp_reset_postdata();
        }
        ?>
    </div>
</main>

<?php get_footer(); ?>