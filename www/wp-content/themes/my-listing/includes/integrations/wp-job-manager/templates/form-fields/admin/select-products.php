<?php

global $thepostid;

if ( ! isset( $field['value'] ) ) {
	$field['value'] = get_post_meta( $thepostid, $key, true );
}
if ( ! empty( $field['name'] ) ) {
	$name = $field['name'];
} else {
	$name = $key;
}
if ( ! empty( $field['classes'] ) ) {
	$classes = implode( ' ', is_array( $field['classes'] ) ? $field['classes'] : array( $field['classes'] ) );
} else {
	$classes = '';
}

$products = get_posts([
	'post_type' => 'product',
	'posts_per_page' => -1,
	'post_status' => 'publish',
	]);
?>

<p class="form-field">
	<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ) ; ?>: <?php if ( ! empty( $field['description'] ) ) : ?><span class="tips" data-tip="<?php echo esc_attr( $field['description'] ); ?>">[?]</span><?php endif; ?></label>

	<select name="<?php echo esc_attr( (isset( $field['name'] ) ? $field['name'] : $key) . '[]' ); ?>" id="<?php echo esc_attr( $key ); ?>" <?php if ( ! empty( $field['required'] ) ) echo 'required'; ?> multiple="multiple" class="form-control custom-select">
		<?php foreach ((array) $products as $product ) : ?>
			<option
			value="<?php echo esc_attr( $product->ID ); ?>"
			<?php if (isset($field['value']) && in_array($product->ID, (array) $field['value'])) echo 'selected="selected"' ?>
			>
			<?php echo esc_html( $product->post_title ); ?>
			</option>
		<?php endforeach; ?>
	</select>
</p>