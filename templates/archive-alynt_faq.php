<?php
/**
 * Archive Template for FAQ Posts
 */

get_header(); ?>

<main class="afaq-wrapper">
    <div class="afaq-container">
        <!-- Archive Header -->
        <header class="afaq-archive-header">
            <h1 class="afaq-archive-title">Frequently Asked Questions</h1>
        </header>

        <!-- FAQ Posts Grid -->
        <div class="afaq-grid">
            <?php
            if (have_posts()) :
                while (have_posts()) : the_post();
                    ?>
                    <a href="<?php the_permalink(); ?>" class="afaq-card">
                        <h2 class="afaq-card-title"><?php the_title(); ?></h2>
                        <div class="afaq-card-excerpt">
                            <?php 
                            $excerpt = get_the_excerpt();
                            echo wp_trim_words($excerpt, 20, '...'); 
                            ?>
                        </div>
                        <div class="afaq-card-meta">
                            <span class="afaq-card-date"><?php echo get_the_date(); ?></span>
                        </div>
                    </a>
                    <?php
                endwhile;
            else :
                ?>
                <div class="afaq-no-results">
                    <p>No FAQs found.</p>
                </div>
                <?php
            endif;
            ?>
        </div>

        <!-- Pagination -->
        <div class="afaq-pagination" id="afaqPagination">
            <?php
            $total_pages = $wp_query->max_num_pages;
            if ($total_pages > 1) :
                ?>
                <div class="afaq-pagination-numbers">
                    <?php
                    echo paginate_links(array(
                        'total' => $total_pages,
                        'current' => max(1, get_query_var('paged')),
                        'prev_text' => '« Previous',
                        'next_text' => 'Next »',
                        'type' => 'plain',
                    ));
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php get_footer(); ?>