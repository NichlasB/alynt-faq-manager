<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

$collection = get_queried_object();
?>
<main class="alynt-faq-wrapper">
    <div class="alynt-faq-container">
        <?php if ($collection instanceof WP_Term) : ?>
            <header class="alynt-faq-header">
                <h1 class="alynt-faq-title"><?php echo esc_html(single_term_title('', false)); ?></h1>
                <?php if (!empty($collection->description)) : ?>
                    <div class="alynt-faq-content">
                        <div class="alynt-faq-content-wrap">
                            <?php echo wp_kses_post(wpautop($collection->description)); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </header>

            <?php
            $collection_output = alynt_faq_render_collection($collection, 'menu_order');

            if ('' !== $collection_output) {
                echo $collection_output;
            } else {
                echo '<p class="alynt-faq-no-results">' . esc_html__('No FAQs found in this collection.', 'alynt-faq') . '</p>';
            }
            ?>
        <?php else : ?>
            <p class="alynt-faq-no-results"><?php esc_html_e('The requested FAQ collection could not be loaded.', 'alynt-faq'); ?></p>
        <?php endif; ?>
    </div>
</main>
<?php get_footer(); ?>
