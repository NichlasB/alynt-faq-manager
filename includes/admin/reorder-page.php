<?php
/**
 * FAQ reorder admin page and AJAX handler.
 *
 * @package    Alynt_FAQ_Manager
 * @subpackage Alynt_FAQ_Manager/includes/admin
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Add admin menu item.
add_action( 'admin_menu', 'alynt_faq_add_reorder_menu' );

/**
 * Register the Reorder FAQs submenu page under the FAQ post type menu.
 *
 * @since 1.0.0
 *
 * @return void
 */
function alynt_faq_add_reorder_menu() {
	add_submenu_page(
		'edit.php?post_type=alynt_faq',
		__( 'Reorder FAQs', 'alynt-faq' ),
		__( 'Reorder FAQs', 'alynt-faq' ),
		'edit_others_alynt_faqs',
		'alynt-faq-order',
		'alynt_faq_reorder_page'
	);
}

/**
 * Get FAQ counts for all collections in a single lookup.
 *
 * Uses the taxonomy term count that WordPress maintains automatically,
 * avoiding a separate WP_Query per collection.
 *
 * @since 1.0.6
 *
 * @param WP_Term[] $collections Array of collection term objects.
 *
 * @return array<int,int> Map of term_id => post count.
 */
function alynt_faq_get_collection_counts( $collections ) {
	$counts = array();
	foreach ( $collections as $collection ) {
		$counts[ $collection->term_id ] = (int) $collection->count;
	}
	return $counts;
}

/**
 * Get the maximum number of FAQ items allowed in one reorder request.
 *
 * @since 1.0.0
 *
 * @return int Maximum item count.
 */
function alynt_faq_get_max_reorder_items() {
	return 500;
}

/**
 * Get the ordered FAQ post IDs for a collection.
 *
 * @since 1.0.0
 *
 * @param int $collection_id Collection term ID.
 *
 * @return int[] Ordered FAQ post IDs.
 */
function alynt_faq_get_collection_ordered_post_ids( $collection_id ) {
	$post_ids = get_posts(
		array(
			'post_type'        => 'alynt_faq',
			'numberposts'      => -1,
			'post_status'      => 'publish',
			'fields'           => 'ids',
			'orderby'          => array(
				'menu_order' => 'ASC',
				'ID'         => 'ASC',
			),
			'suppress_filters' => false,
			'tax_query'        => array(
				array(
					'taxonomy' => 'alynt_faq_collection',
					'field'    => 'term_id',
					'terms'    => $collection_id,
				),
			),
		)
	);

	if ( ! is_array( $post_ids ) ) {
		return array();
	}

	return array_map( 'absint', $post_ids );
}

/**
 * Get a version hash representing the current FAQ order for a collection.
 *
 * @since 1.0.0
 *
 * @param int $collection_id Collection term ID.
 *
 * @return string Order version hash.
 */
function alynt_faq_get_collection_order_version( $collection_id ) {
	return md5( wp_json_encode( alynt_faq_get_collection_ordered_post_ids( $collection_id ) ) );
}

/**
 * Render the Reorder FAQs admin page.
 *
 * Displays a collection selector and a sortable list of FAQs for the selected collection.
 *
 * @since 1.0.0
 *
 * @return void
 */
function alynt_faq_reorder_page() {
	if ( ! current_user_can( 'edit_others_alynt_faqs' ) ) {
		wp_die(
			esc_html__( 'You do not have permission to reorder FAQs. Ask an administrator for access and try again.', 'alynt-faq' ),
			esc_html__( 'Permission denied', 'alynt-faq' ),
			array(
				'response'  => 403,
				'back_link' => true,
			)
		);
	}

	$collections         = get_terms(
		array(
			'taxonomy'   => 'alynt_faq_collection',
			'hide_empty' => false,
		)
	);
	$selected_collection = filter_input( INPUT_GET, 'collection', FILTER_VALIDATE_INT );
	if ( false === $selected_collection || null === $selected_collection ) {
		$selected_collection = 0;
	}
	?>
	<div class="wrap alynt-faq-reorder">
		<h1><?php esc_html_e( 'Reorder FAQs', 'alynt-faq' ); ?></h1>
		<div id="save-feedback" class="notice" role="status" tabindex="-1" style="display: none;"></div>
		<div id="alynt-faq-announce" class="screen-reader-text" aria-live="polite" aria-atomic="true"></div>

		<?php if ( is_wp_error( $collections ) ) : ?>
			<div class="notice notice-error"><p><?php esc_html_e( 'FAQ collections could not be loaded. Please refresh the page and try again. If the problem continues, contact an administrator.', 'alynt-faq' ); ?></p></div>
		<?php elseif ( empty( $collections ) ) : ?>
			<div class="notice notice-info"><p><?php esc_html_e( 'No FAQ collections are available yet. Create your first FAQ or add a collection to start reordering items.', 'alynt-faq' ); ?></p></div>
		<?php endif; ?>

		<hr class="wp-header-end">

		<?php if ( empty( $collections ) ) : ?>
			<div class="alynt-faq-empty-state">
				<h2><?php esc_html_e( 'No FAQ Collections Yet', 'alynt-faq' ); ?></h2>
				<p><?php esc_html_e( 'Create your first FAQ or add a collection to start organizing and reordering items.', 'alynt-faq' ); ?></p>
				<p>
					<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=alynt_faq' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Add New FAQ', 'alynt-faq' ); ?></a>
					<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=alynt_faq_collection&post_type=alynt_faq' ) ); ?>" class="button"><?php esc_html_e( 'Manage Collections', 'alynt-faq' ); ?></a>
				</p>
			</div>
		<?php elseif ( ! is_wp_error( $collections ) ) : ?>
			<div class="collection-selector">
				<label for="collection-dropdown"><?php esc_html_e( 'Select Collection', 'alynt-faq' ); ?></label>
				<select id="collection-dropdown" name="collection">
					<option value=""><?php esc_html_e( 'Select a Collection', 'alynt-faq' ); ?></option>
					<?php
					$collection_counts = alynt_faq_get_collection_counts( $collections );
					foreach ( $collections as $collection ) :
						$faq_count = isset( $collection_counts[ $collection->term_id ] ) ? $collection_counts[ $collection->term_id ] : 0;
						?>
						<option value="<?php echo esc_attr( $collection->term_id ); ?>"
							<?php selected( $selected_collection, $collection->term_id ); ?>>
							<?php echo esc_html( $collection->name ); ?>
							(<?php echo esc_html( $faq_count ); ?>)
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<div id="faq-items-container" class="faq-items-container">
				<?php if ( $selected_collection ) : ?>
					<?php alynt_faq_display_sortable_items( $selected_collection ); ?>
				<?php else : ?>
					<p class="description"><?php esc_html_e( 'Please select a collection to reorder FAQs.', 'alynt-faq' ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Output the sortable FAQ list for a given collection.
 *
 * @since 1.0.0
 *
 * @param int $collection_id Term ID of the collection whose FAQs to display.
 *
 * @return array Results describing updated and failed reorder operations.
 */
function alynt_faq_display_sortable_items( $collection_id ) {
	$collection = get_term( $collection_id, 'alynt_faq_collection' );

	if ( ! $collection || is_wp_error( $collection ) ) {
		echo '<p class="description">' . esc_html__( 'The selected collection could not be loaded. Please choose another collection and try again.', 'alynt-faq' ) . '</p>';
		return;
	}

	$args = array(
		'post_type'              => 'alynt_faq',
		'posts_per_page'         => -1,
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
		'orderby'                => array(
			'menu_order' => 'ASC',
			'ID'         => 'ASC',
		),
		'tax_query'              => array(
			array(
				'taxonomy' => 'alynt_faq_collection',
				'field'    => 'term_id',
				'terms'    => $collection_id,
			),
		),
	);

	$faqs = new WP_Query( $args );

	if ( $faqs->have_posts() ) :
		?>
		<ul id="sortable-faq-list" class="sortable-list" data-order-version="<?php echo esc_attr( alynt_faq_get_collection_order_version( $collection_id ) ); ?>">
			<?php
			while ( $faqs->have_posts() ) :
				$faqs->the_post();
				?>
				<?php
				$faq_item_aria_label = sprintf(
					/* translators: %s: FAQ title. */
					esc_attr__( 'FAQ: %s. Press Up or Down arrow key to reorder.', 'alynt-faq' ),
					get_the_title()
				);
				?>
				<li class="faq-item" data-post-id="<?php echo esc_attr( get_the_ID() ); ?>" tabindex="0" aria-label="<?php echo esc_attr( $faq_item_aria_label ); ?>">
					<div class="faq-handle dashicons dashicons-menu" aria-hidden="true"></div>
					<div class="faq-title"><?php echo esc_html( get_the_title() ); ?></div>
					<div class="faq-order"><?php echo esc_html( get_post_field( 'menu_order', get_the_ID() ) ); ?></div>
				</li>
			<?php endwhile; ?>
		</ul>
	<?php else : ?>
		<div class="alynt-faq-empty-state">
			<h2><?php esc_html_e( 'No FAQs in This Collection', 'alynt-faq' ); ?></h2>
			<p><?php esc_html_e( 'This collection does not contain any FAQs yet. Add a new FAQ or assign existing FAQs to this collection to reorder them here.', 'alynt-faq' ); ?></p>
			<p>
				<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=alynt_faq' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Add New FAQ', 'alynt-faq' ); ?></a>
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=alynt_faq' ) ); ?>" class="button"><?php esc_html_e( 'View FAQs', 'alynt-faq' ); ?></a>
			</p>
		</div>
		<?php
	endif;
	wp_reset_postdata();
}

// Handle AJAX reordering.
add_action( 'wp_ajax_alynt_faq_update_order', 'alynt_faq_update_order' );

/**
 * Retrieve and sanitize the ordered post ID array from the AJAX request.
 *
 * @since 1.0.0
 *
 * @return int[] Array of sanitized post IDs in the new order.
 */
function alynt_faq_get_reorder_items() {
	$items_raw = filter_input( INPUT_POST, 'postIds', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
	if ( ! is_array( $items_raw ) ) {
		return new WP_Error( 'invalid_items', __( 'The FAQ order request was invalid. Please refresh the page and try again.', 'alynt-faq' ) );
	}

	$items = array_values( array_unique( array_filter( array_map( 'absint', $items_raw ) ) ) );

	if ( count( $items ) > alynt_faq_get_max_reorder_items() ) {
		return new WP_Error(
			'too_many_items',
			sprintf(
				/* translators: %d: Maximum number of FAQs that can be reordered in one request. */
				__( 'You can reorder up to %d FAQs at a time. Please split this collection into smaller groups.', 'alynt-faq' ),
				alynt_faq_get_max_reorder_items()
			)
		);
	}

	return $items;
}

/**
 * Validate that all items in the reorder list are published alynt_faq posts.
 *
 * Sends a JSON error and exits if any item is invalid.
 *
 * @since 1.0.0
 *
 * @param int[] $items Array of post IDs to validate.
 *
 * @return void
 */
function alynt_faq_validate_reorder_items( $items ) {
	_prime_post_caches( $items, false, false );

	foreach ( $items as $post_id ) {
		$post = get_post( $post_id );

		if ( ! $post || $post->post_type !== 'alynt_faq' ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid FAQ item detected.', 'alynt-faq' ),
				),
				400
			);
		}
	}
}

/**
 * Validate the submitted reorder payload against the current collection order.
 *
 * @since 1.0.0
 *
 * @param int[] $items         Submitted FAQ post IDs.
 * @param int   $collection_id Collection term ID.
 *
 * @return void
 */
function alynt_faq_validate_reorder_payload( $items, $collection_id ) {
	$current_items   = alynt_faq_get_collection_ordered_post_ids( $collection_id );
	$submitted_items = $items;

	sort( $current_items );
	sort( $submitted_items );

	if ( $submitted_items !== $current_items ) {
		wp_send_json_error(
			array(
				'code'    => 'concurrent_modification',
				'message' => __( 'This FAQ collection changed while you were reordering it. Please refresh the page and try again.', 'alynt-faq' ),
				'refresh' => true,
			),
			409
		);
	}
}

/**
 * Validate the submitted reorder version hash for optimistic locking.
 *
 * @since 1.0.0
 *
 * @param int $collection_id Collection term ID.
 *
 * @return void
 */
function alynt_faq_validate_reorder_version( $collection_id ) {
	$submitted_version = filter_input( INPUT_POST, 'orderVersion', FILTER_UNSAFE_RAW );
	$submitted_version = is_string( $submitted_version ) ? sanitize_text_field( $submitted_version ) : '';

	if ( '' === $submitted_version ) {
		wp_send_json_error(
			array(
				'message' => __( 'The reorder page is missing version data. Please refresh the page and try again.', 'alynt-faq' ),
				'refresh' => true,
			),
			400
		);
	}

	$current_version = alynt_faq_get_collection_order_version( $collection_id );

	if ( ! hash_equals( $current_version, $submitted_version ) ) {
		wp_send_json_error(
			array(
				'code'    => 'concurrent_modification',
				'message' => __( 'This FAQ order changed since you loaded the page. Please refresh and try again.', 'alynt-faq' ),
				'refresh' => true,
			),
			409
		);
	}
}

/**
 * Validate that all items belong to the specified collection.
 *
 * Sends a JSON error and exits if any item is not assigned to the collection.
 *
 * @since 1.0.6
 *
 * @param int[] $items         Array of post IDs to check.
 * @param int   $collection_id Term ID of the expected collection.
 *
 * @return void
 */
function alynt_faq_validate_reorder_collection( $items, $collection_id ) {
	if ( $collection_id <= 0 ) {
		wp_send_json_error(
			array(
				'message' => __( 'No collection specified for reordering.', 'alynt-faq' ),
			),
			400
		);
	}

	$collection = get_term( $collection_id, 'alynt_faq_collection' );

	if ( ! $collection || is_wp_error( $collection ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'The specified collection does not exist.', 'alynt-faq' ),
			),
			400
		);
	}

	foreach ( $items as $post_id ) {
		if ( ! has_term( $collection_id, 'alynt_faq_collection', $post_id ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'One or more FAQs do not belong to the selected collection.', 'alynt-faq' ),
				),
				400
			);
		}
	}
}

/**
 * Update menu_order for each post to reflect the new sort order.
 *
 * @since 1.0.0
 *
 * @param int[] $items Array of post IDs in the desired order (0-indexed position).
 *
 * @return array Results describing updated and failed reorder operations.
 */
function alynt_faq_persist_reorder_items( $items ) {
	$results         = array(
		'updated_count' => 0,
		'failed_ids'    => array(),
		'rolled_back'   => false,
	);
	$original_orders = array();
	$updated_ids     = array();

	foreach ( $items as $position => $id ) {
		$current_order          = (int) get_post_field( 'menu_order', $id );
		$original_orders[ $id ] = $current_order;

		if ( $current_order === (int) $position ) {
			continue;
		}

		$result = wp_update_post(
			array(
				'ID'         => $id,
				'menu_order' => (int) $position,
			),
			true
		);

		if ( is_wp_error( $result ) ) {
			$results['failed_ids'][] = $id;
			continue;
		}

		$updated_ids[] = $id;
		++$results['updated_count'];
	}

	if ( ! empty( $results['failed_ids'] ) && ! empty( $updated_ids ) ) {
		foreach ( $updated_ids as $updated_id ) {
			$rollback_result = wp_update_post(
				array(
					'ID'         => $updated_id,
					'menu_order' => (int) $original_orders[ $updated_id ],
				),
				true
			);

			if ( is_wp_error( $rollback_result ) ) {
				continue;
			}

			clean_post_cache( $updated_id );
		}

		$results['rolled_back']   = true;
		$results['updated_count'] = 0;
	}

	return $results;
}

/**
 * Clear the post object cache for each reordered item and flush collection transients.
 *
 * @since 1.0.0
 *
 * @param int[] $items Array of post IDs whose caches should be cleared.
 *
 * @return void
 */
function alynt_faq_clear_reorder_item_caches( $items ) {
	foreach ( $items as $post_id ) {
		clean_post_cache( $post_id );
	}

	alynt_faq_clear_collection_cache();
}

/**
 * AJAX handler for saving the new FAQ sort order.
 *
 * Verifies nonce and capability, delegates to helper functions, and returns
 * a JSON success or error response.
 *
 * @since 1.0.0
 *
 * @return void
 */
function alynt_faq_update_order() {
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'alynt_faq_reorder' ) ) {
		wp_send_json_error(
			array(
				'code'    => 'session_expired',
				'message' => __( 'Your session has expired. Please refresh the page and try again.', 'alynt-faq' ),
				'refresh' => true,
			),
			403
		);
	}

	if ( ! current_user_can( 'edit_others_alynt_faqs' ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'You do not have permission to perform this action.', 'alynt-faq' ),
			),
			403
		);
	}

	$items = alynt_faq_get_reorder_items();

	if ( is_wp_error( $items ) ) {
		wp_send_json_error(
			array(
				'message' => $items->get_error_message(),
			),
			400
		);
	}

	if ( empty( $items ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'No items provided for reordering.', 'alynt-faq' ),
			),
			400
		);
	}

	alynt_faq_validate_reorder_items( $items );

	$collection_id = filter_input( INPUT_POST, 'collectionId', FILTER_VALIDATE_INT );
	$collection_id = false !== $collection_id && null !== $collection_id ? absint( $collection_id ) : 0;
	alynt_faq_validate_reorder_collection( $items, $collection_id );
	alynt_faq_validate_reorder_payload( $items, $collection_id );
	alynt_faq_validate_reorder_version( $collection_id );

	$persist_results = alynt_faq_persist_reorder_items( $items );

	if ( $persist_results['updated_count'] > 0 ) {
		alynt_faq_clear_reorder_item_caches( $items );
	}

	if ( ! empty( $persist_results['failed_ids'] ) ) {
		$failed_count  = count( $persist_results['failed_ids'] );
		$updated_count = $persist_results['updated_count'];

		wp_send_json_error(
			array(
				'message'      => $persist_results['rolled_back']
					? __( 'The FAQ order could not be saved, so your changes were not applied. Please refresh the page and try again.', 'alynt-faq' )
					: sprintf(
						/* translators: 1: Number of items that failed to save. 2: Number of items updated before the failure. */
						__( 'Could not save the FAQ order for %1$d item(s). %2$d item(s) were updated. Please refresh the page and try again.', 'alynt-faq' ),
						$failed_count,
						$updated_count
					),
				'refresh'      => true,
				'updatedCount' => $updated_count,
				'failedCount'  => $failed_count,
				'rolledBack'   => $persist_results['rolled_back'],
			),
			500
		);
	}

	wp_send_json_success(
		array(
			'message'      => sprintf(
				/* translators: %d: Number of FAQs successfully reordered. */
				__( 'FAQ order updated successfully for %d item(s).', 'alynt-faq' ),
				count( $items )
			),
			'orderVersion' => alynt_faq_get_collection_order_version( $collection_id ),
			'updatedCount' => count( $items ),
		)
	);
}
