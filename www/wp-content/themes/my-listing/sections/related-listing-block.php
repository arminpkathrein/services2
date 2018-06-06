<?php
	$data = c27()->merge_options([
			'icon' => '',
			'icon_style' => 1,
			'title' => '',
			'related_listing' => '',
			'wrapper_class' => 'grid-item reveal',
			'ref' => '',
		], $data);

	$related_listing = $data['related_listing'];

	if (is_numeric($data['related_listing'])) {
		$related_listing = get_post((int) $data['related_listing']);
	}

?>

<div class="<?php echo esc_attr( $data['wrapper_class'] ) ?>">
	<div class="element related-listing-block">
		<div class="pf-head">
			<div class="title-style-1 title-style-<?php echo esc_attr( $data['icon_style'] ) ?>">
				<?php if ($data['icon_style'] != 3): ?>
					<?php echo c27()->get_icon_markup($data['icon']) ?>
				<?php endif ?>
				<h5><?php echo esc_html( $data['title'] ) ?></h5>
			</div>
		</div>
		<div class="pf-body">
			<?php if ($related_listing):
    			$listing_thumbnail = isset($related_listing->_job_logo) && $related_listing->_job_logo ? job_manager_get_resized_image( $related_listing->_job_logo, 'thumbnail') : false;
				?>
				<div class="event-host">
					<a href="<?php echo esc_url( get_permalink( $related_listing ) ) ?>">
						<?php if ($listing_thumbnail): ?>
							<div class="avatar">
								<img src="<?php echo esc_url( $listing_thumbnail ) ?>">
							</div>
						<?php endif ?>
						<span class="host-name"><?php echo apply_filters('the_title', $related_listing->post_title, $related_listing->ID) ?></span>
					</a>
				</div>
			<?php endif ?>
		</div>
	</div>
</div>