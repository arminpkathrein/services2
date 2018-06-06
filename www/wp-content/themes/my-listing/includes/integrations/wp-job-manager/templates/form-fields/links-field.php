<div class="repeater social-networks-repeater" data-list="<?php echo htmlspecialchars(json_encode(isset($field['value']) ? $field['value'] : []), ENT_QUOTES, 'UTF-8') ?>">
	<div data-repeater-list="<?php echo esc_attr( (isset($field['name']) ? $field['name'] : $key) ) ?>">
		<div data-repeater-item>
			<select name="network">
				<option value=""><?php _e( 'Select Network', 'my-listing' ) ?></option>
				<option value="Facebook"><?php _e( 'Facebook', 'my-listing' ) ?></option>
				<option value="Twitter"><?php _e( 'Twitter', 'my-listing' ) ?></option>
				<option value="LinkedIn"><?php _e( 'LinkedIn', 'my-listing' ) ?></option>
				<option value="YouTube"><?php _e( 'YouTube', 'my-listing' ) ?></option>
				<option value="Google+"><?php _e( 'Google+', 'my-listing' ) ?></option>
				<option value="Instagram"><?php _e( 'Instagram', 'my-listing' ) ?></option>
				<option value="Tumblr"><?php _e( 'Tumblr', 'my-listing' ) ?></option>
				<option value="Snapchat"><?php _e( 'Snapchat', 'my-listing' ) ?></option>
				<option value="Reddit"><?php _e( 'Reddit', 'my-listing' ) ?></option>
				<option value="DeviantArt"><?php _e( 'DeviantArt', 'my-listing' ) ?></option>
				<option value="Pinterest"><?php _e( 'Pinterest', 'my-listing' ) ?></option>
				<option value="VKontakte"><?php _e( 'VKontakte', 'my-listing' ) ?></option>
				<option value="SoundCloud"><?php _e( 'SoundCloud', 'my-listing' ) ?></option>
				<option value="Website"><?php _e( 'Website', 'my-listing' ) ?></option>
				<option value="Other"><?php _e( 'Other', 'my-listing' ) ?></option>
			</select>
			<input type="text" name="url" placeholder="Enter URL...">
			<button data-repeater-delete type="button" class="buttons button-5 icon-only small"><i class="material-icons delete"></i></button>
		</div>
	</div>
	<input data-repeater-create type="button" value="Add">
</div>
<?php if ( ! empty( $field['description'] ) ) : ?><small class="description"><?php echo $field['description']; ?></small><?php endif; ?>
