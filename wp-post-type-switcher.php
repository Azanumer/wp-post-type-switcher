<?php
/**
 * Plugin Name:       Post Type Switcher
 * Description:       Convert posts, pages, and custom post types into another post type from Tools, without losing content, meta, or shared taxonomies.
 * Version:           1.0.0
 * Author:            Azan Umer
 * License:           MIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post types a user may switch FROM and TO.
 *
 * Only public, UI-visible types are offered, and attachments are excluded
 * (converting media items would break their file handling).
 *
 * @return string[] Post type names keyed by slug.
 */
function wpts_switchable_types() {
	$types = get_post_types( array( 'public' => true, 'show_ui' => true ), 'objects' );
	unset( $types['attachment'] );
	$out = array();
	foreach ( $types as $slug => $obj ) {
		$out[ $slug ] = $obj->labels->singular_name;
	}
	/**
	 * Filter the post types offered by Post Type Switcher.
	 *
	 * @param string[] $out Type labels keyed by slug.
	 */
	return apply_filters( 'wpts_switchable_types', $out );
}

add_action( 'admin_menu', 'wpts_register_page' );
function wpts_register_page() {
	add_management_page(
		'Post Type Switcher',
		'Post Type Switcher',
		'edit_posts',
		'wpts-switcher',
		'wpts_render_page'
	);
}

function wpts_render_page() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( esc_html__( 'You do not have permission to switch post types.' ) );
	}

	$message = '';
	if ( isset( $_POST['wpts_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpts_nonce'] ) ), 'wpts_switch' ) ) {
		$ids_raw = isset( $_POST['wpts_ids'] ) ? sanitize_text_field( wp_unslash( $_POST['wpts_ids'] ) ) : '';
		$target  = isset( $_POST['wpts_target'] ) ? sanitize_key( wp_unslash( $_POST['wpts_target'] ) ) : '';
		$types   = wpts_switchable_types();

		if ( '' === $ids_raw || ! isset( $types[ $target ] ) ) {
			$message = 'Please enter at least one post ID and pick a valid target type.';
		} else {
			$ids      = array_filter( array_map( 'absint', preg_split( '/[\s,]+/', $ids_raw ) ) );
			$switched = 0;
			$skipped  = 0;
			foreach ( $ids as $id ) {
				$post = get_post( $id );
				// Skip missing posts, already-correct types, and posts the
				// user cannot edit; set_post_type() itself is the only
				// write, so nothing is lost on a skipped item.
				if ( ! $post || $post->post_type === $target || ! current_user_can( 'edit_post', $id ) ) {
					$skipped++;
					continue;
				}
				if ( set_post_type( $id, $target ) ) {
					$switched++;
				} else {
					$skipped++;
				}
			}
			$message = sprintf( '%d item(s) switched to %s. %d skipped (missing, already that type, or not editable).', $switched, $types[ $target ], $skipped );
		}
	}

	$types = wpts_switchable_types();
	?>
	<div class="wrap">
		<h1>Post Type Switcher</h1>
		<?php if ( $message ) : ?>
			<div class="notice notice-success"><p><?php echo esc_html( $message ); ?></p></div>
		<?php endif; ?>
		<p>Enter post IDs (comma- or space-separated) and pick the type to convert them to.
		Content, custom fields, and taxonomies shared by both types are preserved.
		Taxonomies registered only for the old type stay in the database but are hidden until the post returns to that type.</p>
		<form method="post">
			<?php wp_nonce_field( 'wpts_switch', 'wpts_nonce' ); ?>
			<table class="form-table">
				<tr>
					<th scope="row"><label for="wpts_ids">Post IDs</label></th>
					<td><input type="text" id="wpts_ids" name="wpts_ids" class="regular-text" placeholder="e.g. 12, 45, 78" required></td>
				</tr>
				<tr>
					<th scope="row"><label for="wpts_target">Convert to</label></th>
					<td>
						<select id="wpts_target" name="wpts_target">
							<?php foreach ( $types as $slug => $label ) : ?>
								<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
			</table>
			<?php submit_button( 'Switch Post Type' ); ?>
		</form>
	</div>
	<?php
}
