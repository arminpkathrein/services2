<?php
// Get selected value
if ( isset( $field['value'] ) ) {
	$selected = $field['value'];
} elseif (  ! empty( $field['default'] ) && is_int( $field['default'] ) ) {
	$selected = $field['default'];
} elseif ( ! empty( $field['default'] ) && ( $term = get_term_by( 'slug', $field['default'], $field['taxonomy'] ) ) ) {
	$selected = $term->term_id;
} else {
	$selected = '';
}

$listing_id = ! empty( $_REQUEST[ 'job_id' ] ) ? absint( $_REQUEST[ 'job_id' ] ) : 0;
$listing_type_slug = isset( $_GET['listing_type'] ) ? sanitize_text_field( $_GET['listing_type'] ) : false;
$listing_type_id = 0;

$used_terms = [];
if ($listing_id) {
	$used_terms = array_filter( (array) wp_get_object_terms($listing_id, $field['taxonomy'], ['orderby' => 'term_order', 'order' => 'ASC']) );
	$used_terms = array_column($used_terms, 'term_id');
}

if ( $listing_type_slug && ( $listing_type = get_page_by_path( $listing_type_slug, OBJECT, 'case27_listing_type' ) ) ) {
	$listing_type_id = $listing_type->ID;
}

if ( $listing_id && ( $listing_type = get_page_by_path( get_post_meta( $listing_id, '_case27_listing_type', true ), OBJECT, 'case27_listing_type' ) ) ) {
	$listing_type_id = $listing_type->ID;
}

$args = array(
	'taxonomy'     => $field['taxonomy'],
	'hierarchical' => 1,
	'name'         => isset( $field['name'] ) ? $field['name'] : $key,
	'orderby'      => 'term_order',
	'selected'     => $selected,
	'hide_empty'   => false,
	'echo'		   => false,
	'meta_query' => [
		'relation' => 'OR',
		[
			'key' => 'listing_type',
			'value' => '"' . $listing_type_id . '"',
			'compare' => 'LIKE',
		],
		[
			'key' => 'listing_type',
			'value' => '',
		],
		[
			'key' => 'listing_type',
			'compare' => 'NOT EXISTS',
		]
	],
);

?>
<input type="hidden" name="c27_<?php echo esc_attr( $field['taxonomy'] ) ?>_values" value="<?php echo htmlspecialchars(json_encode($used_terms), ENT_QUOTES, 'UTF-8') ?>">
<?php

if ( isset( $field['placeholder'] ) && ! empty( $field['placeholder'] ) ) $args['placeholder'] = $field['placeholder'];

echo str_replace( '&nbsp;', '', CASE27_WP_Job_Manager_Integration::terms_dropdown( apply_filters( 'job_manager_term_multiselect_field_args', $args ) ) );

if ( ! empty( $field['description'] ) ) : ?><small class="description"><?php echo $field['description']; ?></small><?php endif; ?>
