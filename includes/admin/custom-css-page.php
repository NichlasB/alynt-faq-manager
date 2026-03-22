<?php
/**
 * FAQ Custom CSS admin page and AJAX save handler.
 *
 * @package    Alynt_FAQ_Manager
 * @subpackage Alynt_FAQ_Manager/includes/admin
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Custom CSS submenu page under the FAQ post type menu.
 *
 * @since 1.0.0
 *
 * @return void
 */
function alynt_faq_add_custom_css_page() {
	add_submenu_page(
		'edit.php?post_type=alynt_faq',
		__( 'Custom CSS', 'alynt-faq' ),
		__( 'Custom CSS', 'alynt-faq' ),
		'manage_options',
		'alynt-faq-custom-css',
		'alynt_faq_render_custom_css_page'
	);
}
add_action( 'admin_menu', 'alynt_faq_add_custom_css_page' );

// Add AJAX handler for custom CSS.
add_action( 'wp_ajax_alynt_faq_save_custom_css', 'alynt_faq_save_custom_css' );

/**
 * Get the maximum supported custom CSS length.
 *
 * @since 1.0.0
 *
 * @return int Maximum CSS length in characters.
 */
function alynt_faq_get_max_custom_css_length() {
	return 50000;
}

/**
 * Validate nonce, capability, and presence of CSS data in the AJAX request.
 *
 * Sends a JSON error and exits on any validation failure.
 *
 * @since 1.0.0
 *
 * @return void
 */
function alynt_faq_validate_custom_css_request() {
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'alynt_faq_custom_css' ) ) {
		wp_send_json_error(
			array(
				'code'    => 'session_expired',
				'message' => __( 'Your session has expired. Please refresh the page and try again.', 'alynt-faq' ),
				'refresh' => true,
			),
			403
		);
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'You do not have permission to edit custom CSS.', 'alynt-faq' ),
			),
			403
		);
	}

	if ( ! isset( $_POST['css'] ) || is_array( $_POST['css'] ) || is_object( $_POST['css'] ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'No CSS content provided.', 'alynt-faq' ),
			),
			400
		);
	}
}

/**
 * Retrieve and sanitize the raw CSS string from the AJAX POST payload.
 *
 * Strips all HTML tags and unescapes slashes introduced by PHP magic quotes.
 *
 * @since 1.0.0
 *
 * @return string Sanitized CSS string.
 */
function alynt_faq_get_sanitized_custom_css() {
	$custom_css_value = filter_input( INPUT_POST, 'css', FILTER_UNSAFE_RAW );
	if ( ! is_string( $custom_css_value ) ) {
		return '';
	}

	return alynt_faq_normalize_custom_css( sanitize_textarea_field( $custom_css_value ) );
}

/**
 * Validate that the CSS string contains safe and well-formed rules.
 *
 * Checks for required curly-brace structure and rejects known harmful CSS
 * patterns such as expression(), @import, and data: URIs.
 *
 * @since 1.0.0
 *
 * @param string $custom_css The sanitized CSS string to validate.
 *
 * @return void
 */
function alynt_faq_validate_custom_css_content( $custom_css ) {
	if ( '' === trim( $custom_css ) ) {
		return;
	}

	$custom_css_length = function_exists( 'mb_strlen' ) ? mb_strlen( $custom_css ) : strlen( $custom_css );

	if ( $custom_css_length > alynt_faq_get_max_custom_css_length() ) {
		wp_send_json_error(
			array(
				'message' => __( 'Custom CSS is too large to save. Please shorten it and try again.', 'alynt-faq' ),
			),
			400
		);
	}

	if ( strpos( $custom_css, '{' ) === false || strpos( $custom_css, '}' ) === false ) {
		wp_send_json_error(
			array(
				'message' => __( 'Please enter valid CSS rules that include both opening and closing braces.', 'alynt-faq' ),
			),
			400
		);
	}

	if ( alynt_faq_has_unsafe_css_patterns( $custom_css ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Please remove unsafe CSS content and try again.', 'alynt-faq' ),
			),
			400
		);
	}
}

/**
 * AJAX handler for saving the custom CSS option.
 *
 * Validates the request, sanitizes input, and saves to the alynt_faq_custom_css option.
 *
 * @since 1.0.0
 *
 * @return void
 */
function alynt_faq_save_custom_css() {
	alynt_faq_validate_custom_css_request();

	$custom_css = alynt_faq_get_sanitized_custom_css();
	alynt_faq_validate_custom_css_content( $custom_css );
	$existing_css      = alynt_faq_get_custom_css_option_value();
	$current_version   = alynt_faq_get_custom_css_version( $existing_css );
	$submitted_version = filter_input( INPUT_POST, 'cssVersion', FILTER_UNSAFE_RAW );
	$submitted_version = is_string( $submitted_version ) ? sanitize_text_field( $submitted_version ) : '';

	if ( '' === $submitted_version ) {
		wp_send_json_error(
			array(
				'message' => __( 'The Custom CSS page is missing version data. Please refresh the page and try again.', 'alynt-faq' ),
				'refresh' => true,
			),
			400
		);
	}

	if ( (string) $existing_css === (string) $custom_css ) {
		wp_send_json_success(
			array(
				'message'    => __( 'Custom CSS is already up to date.', 'alynt-faq' ),
				'cssVersion' => $current_version,
			)
		);
	}

	if ( ! hash_equals( $current_version, $submitted_version ) ) {
		wp_send_json_error(
			array(
				'code'    => 'concurrent_modification',
				'message' => __( 'The custom CSS changed since you loaded this page. Copy your changes if needed, refresh the page, and try again.', 'alynt-faq' ),
				'refresh' => true,
			),
			409
		);
	}

	$result = update_option( 'alynt_faq_custom_css', $custom_css );

	if ( $result === false ) {
		wp_send_json_error(
			array(
				'message' => __( 'Custom CSS could not be saved. Please try again. If the problem continues, contact an administrator.', 'alynt-faq' ),
			),
			500
		);
	}

	wp_send_json_success(
		array(
			'message'    => __( 'Custom CSS saved successfully.', 'alynt-faq' ),
			'cssVersion' => alynt_faq_get_custom_css_version( $custom_css ),
		)
	);
}

/**
 * Output the available CSS classes reference panel on the Custom CSS page.
 *
 * @since 1.0.0
 *
 * @return void
 */
function alynt_faq_render_custom_css_documentation() {
	?>
	<div class="css-documentation">
		<h2><?php esc_html_e( 'Available CSS Classes', 'alynt-faq' ); ?></h2>
		<ul>
			<li><code>.alynt-faq-collection</code> - <?php esc_html_e( 'Main container for FAQ collection.', 'alynt-faq' ); ?></li>
			<li><code>.faq-item</code> - <?php esc_html_e( 'Individual FAQ container.', 'alynt-faq' ); ?></li>
			<li><code>.faq-question</code> - <?php esc_html_e( 'Question button.', 'alynt-faq' ); ?></li>
			<li><code>.faq-answer</code> - <?php esc_html_e( 'Answer container.', 'alynt-faq' ); ?></li>
			<li><code>.icon-plus</code>, <code>.icon-minus</code> - <?php esc_html_e( 'Toggle icons.', 'alynt-faq' ); ?></li>
			<li><code>.question-text</code> - <?php esc_html_e( 'Question text.', 'alynt-faq' ); ?></li>
			<li><code>.answer-content</code> - <?php esc_html_e( 'Answer content.', 'alynt-faq' ); ?></li>
		</ul>

		<h3><?php esc_html_e( 'Example', 'alynt-faq' ); ?></h3>
		<pre>
			.faq-question {
				color: #your-color;
				font-size: 1.2rem;
			}

			.icon-plus, .icon-minus {
				--icon-color: #your-color;
			}</pre>
	</div>
	<?php
}

/**
 * Output the CSS textarea editor and form submit controls.
 *
 * @since 1.0.0
 *
 * @param string $custom_css The currently saved CSS to pre-populate the editor.
 *
 * @return void
 */
function alynt_faq_render_custom_css_editor( $custom_css ) {
	?>
	<table class="form-table" role="presentation">
		<tbody>
			<tr>
				<th scope="row">
					<label for="alynt_faq_custom_css"><?php esc_html_e( 'Custom CSS', 'alynt-faq' ); ?></label>
				</th>
				<td>
					<div class="css-editor">
						<textarea name="alynt_faq_custom_css"
						id="alynt_faq_custom_css"
						rows="20"
						class="large-text code"
						maxlength="<?php echo esc_attr( alynt_faq_get_max_custom_css_length() ); ?>"
						aria-describedby="alynt-faq-css-help alynt-faq-css-validation"
						aria-invalid="false"
						style="font-family: monospace;"><?php echo esc_textarea( $custom_css ); ?></textarea>
						<p id="alynt-faq-css-help" class="description"><?php esc_html_e( 'Add custom FAQ styles here. Changes apply to the plugin frontend without altering plugin functionality.', 'alynt-faq' ); ?></p>
						<p id="alynt-faq-css-validation" class="alynt-faq-field-error" role="alert" style="display: none;"></p>
					</div>
				</td>
			</tr>
		</tbody>
	</table>

	<p class="submit">
		<?php submit_button( esc_html__( 'Save Custom CSS', 'alynt-faq' ), 'primary', 'submit', false ); ?>
		<button type="button" class="button-link-delete" id="reset-css" aria-expanded="false" aria-controls="alynt-faq-reset-confirmation"><?php esc_html_e( 'Reset Custom CSS', 'alynt-faq' ); ?></button>
	</p>

	<div id="alynt-faq-reset-confirmation" class="alynt-faq-inline-confirmation" hidden>
		<p><?php esc_html_e( 'Reset all custom CSS? This permanently removes your saved custom FAQ styles and cannot be undone.', 'alynt-faq' ); ?></p>
		<p>
			<button type="button" class="button-link-delete" id="confirm-reset-css"><?php esc_html_e( 'Reset Custom CSS', 'alynt-faq' ); ?></button>
			<button type="button" class="button" id="cancel-reset-css"><?php esc_html_e( 'Cancel', 'alynt-faq' ); ?></button>
		</p>
	</div>
	<?php
}

/**
 * Render the full Custom CSS admin page.
 *
 * @since 1.0.0
 *
 * @return void
 */
function alynt_faq_render_custom_css_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die(
			esc_html__( 'You do not have permission to manage custom CSS. Ask an administrator for access and try again.', 'alynt-faq' ),
			esc_html__( 'Permission denied', 'alynt-faq' ),
			array(
				'response'  => 403,
				'back_link' => true,
			)
		);
	}

	$custom_css = alynt_faq_get_custom_css_option_value();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'FAQ Custom CSS', 'alynt-faq' ); ?></h1>
		<div id="save-feedback" class="notice" role="status" tabindex="-1" style="display: none;"></div>
		<div id="alynt-faq-announce" class="screen-reader-text" aria-live="polite" aria-atomic="true"></div>
		<hr class="wp-header-end">

		<div class="alynt-faq-css-container">
			<?php alynt_faq_render_custom_css_documentation(); ?>
			<form method="post" action="" id="custom-css-form">
				<?php wp_nonce_field( 'alynt_faq_custom_css', 'alynt_faq_custom_css_nonce' ); ?>
				<input type="hidden" id="alynt_faq_custom_css_version" name="alynt_faq_custom_css_version" value="<?php echo esc_attr( alynt_faq_get_custom_css_version( $custom_css ) ); ?>">
				<?php alynt_faq_render_custom_css_editor( $custom_css ); ?>
			</form>
		</div>
	</div>
	<?php
}
