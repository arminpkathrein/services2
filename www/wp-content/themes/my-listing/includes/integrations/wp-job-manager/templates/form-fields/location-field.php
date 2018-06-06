<?php
$listing_id = ! empty( $_REQUEST[ 'job_id' ] ) ? absint( $_REQUEST[ 'job_id' ] ) : 0;
$latitude = $longitude = $custom_coords = false;
if ($listing_id) {
	$latitude = get_post_meta($listing_id, 'geolocation_lat', true);
	$longitude = get_post_meta($listing_id, 'geolocation_long', true);
	$custom_coords = get_post_meta($listing_id, 'geolocation_custom_coords', true);
	// dump($latitude, $longitude, $custom_coords);
}

$custom_coords_id = esc_attr( isset( $field['name'] ) ? $field['name'] : $key ) . '__custom_coords';
$latitude_id = esc_attr( isset( $field['name'] ) ? $field['name'] : $key ) . '__latitude';
$longitude_id = esc_attr( isset( $field['name'] ) ? $field['name'] : $key ) . '__longitude';


?>

<input type="text" class="input-text" name="<?php echo esc_attr( isset( $field['name'] ) ? $field['name'] : $key ); ?>" id="<?php echo esc_attr( $key ); ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" value="<?php echo isset( $field['value'] ) ? esc_attr( $field['value'] ) : ''; ?>" maxlength="<?php echo ! empty( $field['maxlength'] ) ? esc_attr( $field['maxlength'] ) : ''; ?>" <?php if ( ! empty( $field['required'] ) ) echo 'required'; ?> />
<?php if ( ! empty( $field['description'] ) ) : ?><small class="description"><?php echo $field['description']; ?></small><?php endif; ?>

<div class="md-checkbox">
	<input id="<?php echo esc_attr( $custom_coords_id ) ?>" type="checkbox" name="<?php echo esc_attr( $custom_coords_id ) ?>" value="yes" <?php echo $custom_coords == 'yes' ? 'checked="checked"' : '' ?>>
	<label for="<?php echo esc_attr( $custom_coords_id ) ?>" class=""><?php _e( 'Pick coordinates', 'my-listing' ) ?></label>
</div>

<input type="hidden" name="<?php echo esc_attr( $latitude_id ) ?>" id="<?php echo esc_attr( $latitude_id ) ?>" value="<?php echo esc_attr( $latitude ) ?>">
<input type="hidden" name="<?php echo esc_attr( $longitude_id ) ?>" id="<?php echo esc_attr( $longitude_id ) ?>" value="<?php echo esc_attr( $longitude ) ?>">

<div class="location_picker c27-map" id="location-picker-map"
	 data-latitude-input="#<?php echo esc_attr( $latitude_id ) ?>"
	 data-longitude-input="#<?php echo esc_attr( $longitude_id ) ?>"
	 data-custom-coords-checkbox="#<?php echo esc_attr( $custom_coords_id ) ?>"></div>