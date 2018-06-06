<?php
    // Listing preview default options.
    $defaults = [
        'background' => ['type' => 'gallery'],
        'buttons' => [],
        'info_fields' => [],
        'quick_view' => ['template' => 'default', 'map_skin' => 'skin1'],
    ];

    $data = c27()->merge_options([
            'listing' => '',
            'options' => [],
            'wrap_in' => '',
        ], $data);

    // If the listing object isn't provided, return empty.
    if (!$data['listing']) {
        return false;
    }

    $listing = $data['listing'];

    $listing_obj = new CASE27\Classes\Listing( $listing );

    // Get the preview template options for the listing type of the current listing.
    $options = c27()->get_listing_type_options(
                    c27()->get_job_listing_type($listing->ID),
                    ['result']
                )['result'];

    // Merge with the default options, in case the listing type options meta returns null.
    $options = c27()->merge_options($defaults, (array) $options);

    // Finally, in case custom options have been provided through the c27()->get_partial() method,
    // then give those the highest priority, by overwriting the listing type options with those.
    $options = c27()->merge_options($options, (array) $data['options']);

    $listing_meta = get_post_meta($listing->ID);

    // Categories.
    $categories = array_filter( (array) get_the_terms($listing, 'job_listing_category') );

    $listing_thumbnail = job_manager_get_resized_image( $listing_meta['_job_logo'][0], 'thumbnail');

    $quick_view_template = $options['quick_view']['template'];

    if ( ! $listing->geolocation_lat || ! $listing->geolocation_long ) {
    	$quick_view_template = 'alternate';
    }
?>

<div class="listing-quick-view-container listing-preview <?php echo esc_attr( "quick-view-{$quick_view_template} quick-view type-{$listing->_case27_listing_type}" ) ?>">
	<div class="mc-left">
		<div class="lf-item-container">
			<div class="lf-item">
			    <a href="<?php echo esc_url( get_permalink( $listing ) ) ?>">
		            <div class="overlay"></div>

		            <!-- BACKGROUND IMAGE -->
		            <?php if ($options['background']['type'] == 'image' && isset($listing_meta['_job_cover']) && $listing_meta['_job_cover']): ?>
		                <div
		                    class="lf-background"
		                    style="background-image: url('<?php echo esc_url( job_manager_get_resized_image($listing_meta['_job_cover'][0], 'large') ) ?>');">
		                </div>
		            <?php endif ?>

		            <!-- BACKGROUND GALLERY -->
		            <?php if ($options['background']['type'] == 'gallery' && isset($listing_meta['_job_gallery']) && $listing_meta['_job_gallery']): ?>
		                <?php $listing_gallery = (array) unserialize($listing_meta['_job_gallery'][0]) ?>

		                <?php if ($listing_gallery): ?>
		                    <div class="owl-carousel lf-background-carousel">
		                    <?php foreach ($listing_gallery as $gallery_image): ?>
		                        <div class="item">
		                            <div
		                                class="lf-background"
		                                style="background-image: url('<?php echo esc_url( job_manager_get_resized_image($gallery_image, 'large') ) ?>');">
		                            </div>
		                        </div>
		                    <?php endforeach ?>
		                    </div>
		                <?php endif ?>
		            <?php endif ?>

		            <!-- DEFAULT TITLE TEMPLATE -->
		           	<div class="lf-item-info">
		           	    <h4><?php echo apply_filters( 'the_title', $listing->post_title, $listing->ID ) ?></h4>

		           	    <?php if (isset($options['info_fields']) && $options['info_fields']): ?>
		           	        <ul>
		           	            <?php foreach ((array) $options['info_fields'] as $info_field):
                                	if (!isset($info_field['icon'])) $info_field['icon'] = '';

                                	$field_value = isset($listing_meta["_{$info_field['show_field']}"]) ? $listing_meta["_{$info_field['show_field']}"][0] : '';

                                	if ($info_field['show_field'] == 'job_location') {
                                	    $field_value = c27()->get_listing_short_address($listing);
                                	}

                                	if ( is_serialized( $field_value ) ) {
                                		$field_value = join( ', ', (array) unserialize( $field_value ) );
                                	}

                                	$GLOBALS['c27_active_shortcode_content'] = $field_value;
                                	$field_content = str_replace('[[field]]', $field_value, do_shortcode($info_field['label']));

                                	?>
                                	<?php if (trim($field_value) && trim($field_content)): ?>
                                	    <li>
                                	        <i class="<?php echo esc_attr( $info_field['icon'] ) ?> sm-icon"></i>
                                	        <?php echo esc_html( $field_content ) ?>
                                	    </li>
                                	<?php endif ?>
		           	            <?php endforeach ?>
		           	        </ul>
		           	    <?php endif ?>
		           	</div>

		            <!-- BUTTONS AT TOP LEFT CORNER -->
		            <?php if ($options['buttons']): ?>
		                <div class="lf-head">

		                    <?php foreach ($options['buttons'] as $button): ?>

		                        <?php if ($button['show_field'] == '__listing_rating' && $listing_rating = CASE27_Integrations_Review::get_listing_rating_optimized($listing->ID)): ?>
		                            <div class="lf-head-btn listing-rating">
		                                <span class="value"><?php echo esc_html( $listing_rating ) ?></span>
		                                <sup class="out-of">/10</sup>
		                            </div>
		                        <?php elseif ($button['show_field'] == 'work_hours' && isset($listing_meta["_work_hours"]) && ($hours = unserialize($listing_meta["_work_hours"][0]))):
                           			$open_now = $listing_obj->get_schedule()->get_open_now(); ?>
		                            <div class="lf-head-btn open-status">
		                                <span><?php echo $open_now ? __( 'Open', 'my-listing' ) : __( 'Closed', 'my-listing' ) ?></span>
		                            </div>
		                        <?php else: $button_val = isset($listing_meta["_{$button['show_field']}"]) ? $listing_meta["_{$button['show_field']}"][0] : '';

		                        	if ( is_serialized( $button_val ) ) {
		                        		$button_val = join( ', ', (array) unserialize( $button_val ) );
		                        	}

		                            $GLOBALS['c27_active_shortcode_content'] = $button_val;
		                            $btn_content = str_replace('[[field]]', $button_val, do_shortcode($button['label'])); ?>

		                            <?php if (trim($btn_content)): ?>
		                                <div class="lf-head-btn <?php echo has_shortcode($button['label'], '27-format') ? 'formatted' : '' ?>">
		                                    <?php echo str_replace('[[field]]', $button_val, do_shortcode($button['label'])) ?>
		                                </div>
		                            <?php endif ?>
		                        <?php endif ?>

		                    <?php endforeach ?>
		                </div>
		            <?php endif ?>
		        </a>

		        <!-- BACKGROUND GALLERY NAVIGATION BUTTONS -->
		        <?php if ($options['background']['type'] == 'gallery'): ?>
		        	<div class="gallery-nav">
		        		<ul>
		        			<li>
		        				<a href="#" class="lf-item-prev-btn">
		        					<i class="material-icons">keyboard_arrow_left</i>
		        				</a>
		        			</li>
		        			<li>
		        				<a href="#" class="lf-item-next-btn">
		        					<i class="material-icons">keyboard_arrow_right</i>
		        				</a>
		        			</li>
		        		</ul>
		        	</div>
		        <?php endif ?>
			</div>
		</div>
		<div class="grid-item">
			<div class="element">
				<div class="pf-head">
					<div class="title-style-1">
						<i class="material-icons">view_headline</i>
						<h5><?php _e( 'Description', 'my-listing' ) ?></h5>
					</div>
				</div>
				<div class="pf-body">
					<p>
						<?php echo wp_kses( nl2br( apply_filters('the_content', $listing->post_content) ), ['br' => []] ) ?>
					</p>
				</div>
			</div>
		</div>
		<div class="grid-item">
			<div class="element">
				<div class="pf-head">
					<div class="title-style-1">
						<i class="material-icons">view_module</i>
						<h5><?php _e( 'Categories', 'my-listing' ) ?></h5>
					</div>
				</div>
				<div class="pf-body">
					<div class="listing-details">
						<ul>
							<?php foreach ($categories as $category):
								$term = new CASE27\Classes\Term( $category );
								?>
								<li>
									<a href="<?php echo esc_url( $term->get_link() ) ?>">
										<span class="cat-icon" style="background-color: <?php echo esc_attr ($term->get_color() ) ?>;">
											<i class="<?php echo esc_attr( $term->get_icon() ) ?>"
												style="color: <?php echo esc_attr( $term->get_text_color() ) ?>"></i>
										</span>
										<span class="category-name"><?php echo esc_html( $term->get_name() ) ?></span>
									</a>
								</li>
							<?php endforeach ?>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="mc-right">
		<div class="block-map c27-map" data-options="<?php echo htmlspecialchars(json_encode([
			'items_type' => 'custom-locations',
			'zoom' => 12,
			'skin' => $options['quick_view']['map_skin'],
			'marker_type' => 'basic',
			'locations' => [[
				'marker_lat' => (float) $listing->geolocation_lat,
				'marker_lng' => (float) $listing->geolocation_long,
				'marker_image' => ['url' => $listing_thumbnail],
			]],
		]), ENT_QUOTES, 'UTF-8'); ?>">
		</div>
	</div>
</div>