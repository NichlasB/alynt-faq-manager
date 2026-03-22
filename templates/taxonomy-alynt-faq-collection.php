<?php
/**
 * FAQ collection taxonomy template.
 *
 * @package Alynt_FAQ_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$collection = get_queried_object();
?>
<main class="alynt-faq-wrapper">
	<div class="alynt-faq-container">
		<?php if ( $collection instanceof WP_Term ) : ?>
			<header class="alynt-faq-header">
				<h1 class="alynt-faq-title"><?php echo esc_html( single_term_title( '', false ) ); ?></h1>
				<?php if ( ! empty( $collection->description ) ) : ?>
					<div class="alynt-faq-content">
						<div class="alynt-faq-content-wrap">
							<?php echo wp_kses_post( wpautop( $collection->description ) ); ?>
						</div>
					</div>
				<?php endif; ?>
			</header>

			<?php
			$collection_output = alynt_faq_render_collection( $collection, 'menu_order' );

			if ( '' !== $collection_output ) {
				echo wp_kses_post( $collection_output );
			} else {
				echo '<div class="alynt-faq-empty-state"><h2>' . esc_html__( 'No FAQs in This Collection', 'alynt-faq' ) . '</h2><p>' . esc_html__( 'This collection does not have any published FAQs yet.', 'alynt-faq' ) . '</p></div>';
			}
			?>
		<?php else : ?>
			<p class="alynt-faq-no-results"><?php esc_html_e( 'The requested FAQ collection could not be loaded.', 'alynt-faq' ); ?></p>
		<?php endif; ?>
	</div>
</main>
<?php get_footer(); ?>
