<?php
    $data = c27()->merge_options([
            'listing' => '',
            'options' => [],
            'wrap_in' => '',
        ], $data);

    if ( ! class_exists( 'WP_Job_Manager' ) ) {
        return false;
    }

    // If the listing object isn't provided, return empty.
    if ( ! $data['listing'] ) {
        return false;
    }

    $listing = $data['listing'];
    $listing_obj = new CASE27\Classes\Listing( $data['listing'] );

    // Get the preview template options for the listing type of the current listing.
    $options = $listing_obj->get_preview_options();

    // Finally, in case custom options have been provided through the c27()->get_partial() method,
    // then give those the highest priority, by overwriting the listing type options with those.
    $options = c27()->merge_options($options, (array) $data['options']);

    $listing_meta = get_post_meta($listing->ID);

    $classes = [
        'default' => '',
        'alternate' => 'lf-type-2'
    ];

    // Categories.
    $categories = array_filter( (array) wp_get_object_terms($listing->ID, 'job_listing_category', ['orderby' => 'term_order', 'order' => 'ASC']) );

    $first_category = ( $categories && ! is_wp_error( $categories ) ) ? new CASE27\Classes\Term( $categories[0] ) : false;

    $listing_thumbnail = isset($listing_meta['_job_logo']) ? job_manager_get_resized_image( $listing_meta['_job_logo'][0], 'thumbnail') : false;

    if (is_numeric( $listing->geolocation_lat) ) $listing->geolocation_lat += rand(0, 1000) / 10e6;
    if (is_numeric( $listing->geolocation_long) ) $listing->geolocation_long += rand(0, 1000) / 10e6;

    $listing->_c27_marker_data = [
        'lat' => $listing->geolocation_lat,
        'lng' => $listing->geolocation_long,
        'thumbnail' => $listing_thumbnail,
        'category_icon' => $first_category ? $first_category->get_icon() : null,
        'category_color' => $first_category ? $first_category->get_color() : null,
        'category_text_color' => $first_category ? $first_category->get_text_color() : null,
    ];

    // dump($first_category);

    // Get the number of details, so the height of the listing preview
    // can be reduced if there are many details.
    $detailsCount = 0;
    foreach ((array) $options['footer']['sections'] as $section) {
        if ( $section['type'] == 'details' ) $detailsCount = count($section['details']);
    }

    if ( ! isset( $listing->_c27_show_promoted_badge ) ) {
        $listing->_c27_show_promoted_badge = true;
    }

    $isPromoted = false;
    if (
         $listing->_c27_show_promoted_badge &&
         isset( $listing->_case27_listing_promotion_start_date ) &&
         $listing->_case27_listing_promotion_start_date &&
         strtotime( $listing->_case27_listing_promotion_start_date ) &&
         isset( $listing->_case27_listing_promotion_end_date ) &&
         $listing->_case27_listing_promotion_end_date &&
         strtotime( $listing->_case27_listing_promotion_end_date )
     ) {
        try {
            $startDate = new DateTime( $listing->_case27_listing_promotion_start_date );
            $endDate = new DateTime( $listing->_case27_listing_promotion_end_date );
            $currentDate = new DateTime( date( 'Y-m-d H:i:s' ) );

            if ( $currentDate >= $startDate && $currentDate <= $endDate ) {
                $isPromoted = true;
            }
        } catch (Exception $e) {}
    }
?>

<!-- LISTING ITEM PREVIEW -->
<div class="<?php echo $data['wrap_in'] ? esc_attr( $data['wrap_in'] ) : '' ?>">
<div <?php job_listing_class( esc_attr( " lf-item-container listing-preview {$classes[$options['template']]} type-{$listing->_case27_listing_type} " . ($detailsCount > 2 ? ' lf-small-height ' : '') ), $listing->ID); ?>
     data-id="listing-id-<?php echo esc_attr( $listing->ID ); ?>"
     data-latitude="<?php echo esc_attr( $listing->geolocation_lat ); ?>"
     data-longitude="<?php echo esc_attr( $listing->geolocation_long ); ?>"
     data-category-icon="<?php echo esc_attr( $first_category ? $first_category->get_icon() : '' ) ?>"
     data-category-color="<?php echo esc_attr( $first_category ? $first_category->get_color() : '' ) ?>"
     data-category-text-color="<?php echo esc_attr( $first_category ? $first_category->get_text_color() : '' ) ?>"
     data-thumbnail="<?php echo esc_url( $listing_thumbnail ) ?>"
     >
    <div class="lf-item">
        <a href="<?php echo esc_url( get_permalink($listing) ) ?>">
            <div class="overlay" style="
                background-color: <?php echo esc_attr( c27()->get_setting('listing_preview_overlay_color', '#242429') ); ?>;
                opacity: <?php echo esc_attr( c27()->get_setting('listing_preview_overlay_opacity', '0.5') ); ?>;
                "></div>

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
                    <?php foreach (array_slice($listing_gallery, 0, 3) as $gallery_image): ?>
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
            <?php if ($options['template'] == 'default'): ?>
                <div class="lf-item-info">
                    <h4 class="case27-secondary-text"><?php echo apply_filters( 'the_title', $listing->post_title, $listing->ID ) ?></h4>

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
            <?php endif ?>

            <!-- ALTERNATE TITLE TEMPLATE -->
            <?php if ($options['template'] == 'alternate'): ?>
                <div class="lf-item-info-2">
                    <?php if (isset($listing_meta['_job_logo']) && $listing_meta['_job_logo']): ?>
                        <div
                            class="lf-avatar"
                            style="background-image: url('<?php echo esc_url( job_manager_get_resized_image( $listing_meta['_job_logo'][0], 'thumbnail') ) ?>')">
                        </div>
                    <?php endif ?>

                    <h4><?php echo apply_filters( 'the_title', $listing->post_title, $listing->ID ) ?></h4>

                    <?php if (isset($listing_meta['_job_tagline']) && $listing_meta['_job_tagline'] && $listing_meta['_job_tagline'][0]): ?>
                        <h6><?php echo esc_html( $listing_meta['_job_tagline'][0] ) ?></h6>
                    <?php elseif (isset($listing_meta['_job_description']) && $listing_meta['_job_description']): ?>
                        <h6><?php echo c27()->the_text_excerpt($listing_meta['_job_description'][0], 114) ?></h6>
                    <?php endif ?>

                    <?php if (isset($options['info_fields']) && $options['info_fields']): ?>
                    <ul class="lf-contact">
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
            <?php endif ?>

            <!-- BUTTONS AT TOP LEFT CORNER -->
            <?php if ($options['buttons']): ?>
                <div class="lf-head">

                    <?php if ( $isPromoted ): ?>
                        <div class="lf-head-btn ad-badge">
                            <span>
                                <i class="icon-flash"></i><?php _e( 'Ad', 'my-listing' ) ?>
                            </span>
                        </div>
                    <?php endif ?>

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
                                    <?php echo $btn_content ?>
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

    <?php ob_start() ?>
        <li class="item-preview" data-toggle="tooltip" data-placement="bottom" data-original-title="Quick view">
            <a href="#" type="button" class="c27-toggle-quick-view-modal" data-id="<?php echo esc_attr( $listing->ID ); ?>"><i class="material-icons">zoom_in</i></a>
        </li>
    <?php $quick_view_button = ob_get_clean() ?>

    <?php ob_start() ?>
        <li data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Bookmark">
            <a class="c27-bookmark-button <?php echo CASE27_Integrations_Bookmark::instance()->is_bookmarked($listing->ID, get_current_user_id()) ? 'bookmarked' : '' ?>"
               data-listing-id="<?php echo esc_attr( $listing->ID ) ?>" data-nonce="<?php echo esc_attr( wp_create_nonce('c27_bookmark_nonce') ) ?>">
               <i class="material-icons">favorite_border</i>
            </a>
        </li>
    <?php $bookmark_button = ob_get_clean() ?>

    <!-- FOOTER SECTIONS -->
    <?php if ($options['footer']['sections']): ?>
        <?php foreach ((array) $options['footer']['sections'] as $section): ?>

            <!-- CATEGORIES SECTION -->
            <?php if ($section['type'] == 'categories'):
                $taxonomy = ! empty( $section['taxonomy'] ) ? $section['taxonomy'] : 'job_listing_category';
                $terms = array_filter( (array) wp_get_object_terms($listing->ID, $taxonomy, ['orderby' => 'term_order', 'order' => 'ASC']) );

                // dump($taxonomy);
                ?>
                <div class="listing-details c27-footer-section">
                    <ul class="c27-listing-preview-category-list">

                        <?php if ( ! is_wp_error( $terms ) && count( $terms ) ):
                            $category_count = count($terms);
                            $first_category = array_shift( $terms );
                            $first_ctg = new CASE27\Classes\Term( $first_category );
                            $category_names = array_map(function($category) {
                                return $category->name;
                            }, $terms);
                            $categories_string = join('<br>', $category_names);
                            ?>
                            <li>
                                <a href="<?php echo esc_url( $first_ctg->get_link() ) ?>">
                                    <span class="cat-icon" style="background-color: <?php echo esc_attr( $first_ctg->get_color() ) ?>;">
                                        <i class="<?php echo esc_attr( $first_ctg->get_icon() ) ?>"
                                            style="color: <?php echo esc_attr( $first_ctg->get_text_color() ) ?>"></i>
                                    </span>
                                    <span class="category-name"><?php echo esc_html( $first_ctg->get_name() ) ?></span>
                                </a>
                            </li>

                            <?php if (count($terms)): ?>
                                <li data-toggle="tooltip" data-placement="bottom" data-original-title="<?php echo esc_attr( $categories_string ) ?>" data-html="true">
                                    <div class="categories-dropdown dropdown c27-more-categories">
                                        <a href="#other-categories">
                                            <span class="cat-icon cat-more">+<?php echo $category_count - 1 ?></span>
                                        </a>
                                    </div>
                                </li>
                            <?php endif ?>
                        <?php endif ?>
                    </ul>

                    <div class="ld-info">
                        <ul>
                            <?php if (isset($section['show_quick_view_button']) && $section['show_quick_view_button'] == 'yes'): ?>
                                <?php echo $quick_view_button ?>
                            <?php endif ?>
                            <?php if (isset($section['show_bookmark_button']) && $section['show_bookmark_button'] == 'yes'): ?>
                                <?php echo $bookmark_button ?>
                            <?php endif ?>
                        </ul>
                    </div>
                </div>
            <?php endif ?>

            <!-- RELATED LISTING (HOST) SECTION -->
            <?php if ($section['type'] == 'host' && isset($listing_meta['_related_listing']) && $listing_meta['_related_listing']): ?>
                <?php $host = get_post($listing_meta['_related_listing'][0]) ?>

                <?php if ($host): ?>
                    <div class="event-host c27-footer-section">
                        <a href="<?php echo esc_url( get_permalink( $host ) ) ?>">
                            <div class="avatar">
                                <img src="<?php echo esc_url( job_manager_get_resized_image( $host->_job_logo, 'thumbnail') ) ?>" alt="<?php echo esc_attr( $host->post_title ) ?>">
                            </div>
                            <span class="host-name"><?php echo str_replace('[[listing_name]]', apply_filters( 'the_title', $host->post_title, $host->ID ), $section['label']) ?></span>
                        </a>

                        <div class="ld-info">
                            <ul>
                                <?php if (isset($section['show_quick_view_button']) && $section['show_quick_view_button'] == 'yes'): ?>
                                    <?php echo $quick_view_button ?>
                                <?php endif ?>
                                <?php if (isset($section['show_bookmark_button']) && $section['show_bookmark_button'] == 'yes'): ?>
                                    <?php echo $bookmark_button ?>
                                <?php endif ?>
                            </ul>
                        </div>
                    </div>
                <?php endif ?>
            <?php endif ?>

            <!-- DETAILS SECTION -->
            <?php if ($section['type'] == 'details' && $section['details']): ?>
                <div class="listing-details-3 c27-footer-section">
                    <ul class="details-list">
                        <?php foreach ((array) $section['details'] as $detail):
                            if (!isset($detail['icon'])) $detail['icon'] = '';

                            $detail_val = isset($listing_meta["_{$detail['show_field']}"]) ? $listing_meta["_{$detail['show_field']}"][0] : '';

                            if ( is_serialized( $detail_val ) ) {
                                $detail_val = join( ', ', (array) unserialize( $detail_val ) );
                            }

                            $GLOBALS['c27_active_shortcode_content'] = $detail_val; ?>
                            <li>
                                <i class="<?php echo esc_attr( $detail['icon'] ) ?>"></i>
                                <span><?php echo str_replace('[[field]]', $detail_val, do_shortcode($detail['label'])) ?></span>
                            </li>
                        <?php endforeach ?>
                    </ul>
                </div>
            <?php endif ?>

            <?php if ($section['type'] == 'actions' || $section['type'] == 'details'): ?>
                <?php if (
                    ( isset($section['show_quick_view_button']) && $section['show_quick_view_button'] == 'yes' ) ||
                    ( isset($section['show_bookmark_button']) && $section['show_bookmark_button'] == 'yes' )
                 ): ?>
                    <div class="listing-details actions c27-footer-section">
                        <div class="ld-info">
                            <ul>
                                <?php if (isset($section['show_quick_view_button']) && $section['show_quick_view_button'] == 'yes'): ?>
                                    <?php echo $quick_view_button ?>
                                <?php endif ?>
                                <?php if (isset($section['show_bookmark_button']) && $section['show_bookmark_button'] == 'yes'): ?>
                                    <?php echo $bookmark_button ?>
                                <?php endif ?>
                            </ul>
                        </div>
                    </div>
                <?php endif ?>
            <?php endif ?>
        <?php endforeach ?>
    <?php endif ?>
</div>
</div>