<?php global $post;

    if (!class_exists('WP_Job_Manager')) return;

    // Get listing meta.
    $meta = get_post_meta(get_the_ID());

    $listing = new CASE27\Classes\Listing( $post );

    // Get the preview template options for the listing type of the current listing.
    $options = c27()->get_listing_type_options(
                    c27()->get_job_listing_type(get_the_ID()),
                    ['single', 'fields']
                );

    // Get the layout blocks for the single listing page.
    $layout = $options['single'];
    $fields = $options['fields'];

    // Categories and Tags.
    $categories = array_filter( (array) wp_get_object_terms($post->ID, 'job_listing_category', ['orderby' => 'term_order', 'order' => 'ASC']) );
    $tags = array_filter( (array) wp_get_object_terms($post->ID, 'case27_job_listing_tags', ['orderby' => 'term_order', 'order' => 'ASC']) );

    // Possible cover button styles.
    $button_styles = [
        'primary' => 'button-primary',
        'secondary' => 'button-secondary',
        'outline' => 'button-outlined',
        'plain' => 'button-plain',
        'none' => 'button-plain',
    ];
?>

<!-- SINGLE LISTING PAGE -->
<div class="single-job-listing" id="c27-single-listing">
    <input type="hidden" id="case27-post-id" value="<?php echo esc_attr( get_the_ID() ) ?>">
    <input type="hidden" id="case27-author-id" value="<?php echo esc_attr( get_the_author_meta('ID') ) ?>">

    <!-- LISTING COVER IMAGE -->
    <?php if ($layout['cover']['type'] == 'image'): ?>
        <?php $image = isset($meta['_job_cover']) && $meta['_job_cover'] ? job_manager_get_resized_image($meta['_job_cover'][0], 'full') : '' ?>
        <section class="featured-section profile-cover parallax-bg"
                 style="background-image: url('<?php echo esc_url( $image ) ?>')"
                 data-bg="<?php echo esc_url( $image ) ?>">
            <div class="overlay" style="
                background-color: <?php echo esc_attr( c27()->get_setting('single_listing_cover_overlay_color', '#242429') ); ?>;
                opacity: <?php echo esc_attr( c27()->get_setting('single_listing_cover_overlay_opacity', '0.5') ); ?>;
                "></div>
    <?php endif ?>

    <!-- LISTING COVER GALLERY -->
    <?php if ($layout['cover']['type'] == 'gallery'): ?>
        <section class="featured-section profile-cover featured-section-gallery">

        <?php $gallery = isset($meta['_job_gallery']) && $meta['_job_gallery'] ? (array) unserialize($meta['_job_gallery'][0]) : false ?>
        <?php if ($gallery): ?>
            <div class="header-gallery-carousel owl-carousel zoom-gallery">
                <?php foreach ($gallery as $gallery_image): ?>
                	<?php if ($image = job_manager_get_resized_image($gallery_image, 'large')): ?>
                		<?php list($width, $height) = getimagesize($image) ?>
                		<a class="item" data-width="<?php echo esc_attr( $width ) ?>" data-height="<?php echo esc_attr( $height ) ?>"
                			href="<?php echo esc_url( $image ) ?>"
                			style="background-image: url(<?php echo esc_url( $image ) ?>);">
                			<div class="overlay" style="
                                background-color: <?php echo esc_attr( c27()->get_setting('single_listing_cover_overlay_color', '#242429') ); ?>;
                                opacity: <?php echo esc_attr( c27()->get_setting('single_listing_cover_overlay_opacity', '0.5') ); ?>;
                                "></div>
                		</a>
                	<?php endif ?>
                <?php endforeach ?>
            </div>
        <?php endif ?>
    <?php endif ?>

    <!-- LISTING NO COVER -->
    <?php if ($layout['cover']['type'] == 'none'): ?>
        <section class="featured-section profile-cover profile-cover-no-img">
            <div class="overlay" style="
                background-color: <?php echo esc_attr( c27()->get_setting('single_listing_cover_overlay_color', '#242429') ); ?>;
                opacity: <?php echo esc_attr( c27()->get_setting('single_listing_cover_overlay_opacity', '0.5') ); ?>;
                "></div>
    <?php endif ?>

    <!-- COVER BUTTONS -->
        <div class="profile-cover-content reveal">
            <div class="container">
                <div class="cover-buttons">
                    <ul v-pre>
                        <?php if ($layout['buttons']): ?>
                            <?php foreach ($layout['buttons'] as $button):
                                $buttonID = uniqid() . '__cover_button';
                                $button_classes = '';
                                $button_style = isset($button['style']) && in_array($button['style'], array_keys($button_styles)) ? $button_styles[$button['style']] : 'button-outlined';
                                if (isset($button['label']) && isset($button['icon']) && $button['icon']) {
                                    $button['label'] = c27()->get_icon_markup($button['icon']) . '<span class="button-label">' . $button['label'] . '</span>';
                                }
                                ?>

                                <?php if ($button['action'] == 'custom-field' && isset($meta["_{$button['custom_field']}"]) && $meta["_{$button['custom_field']}"]): ?>
                                    <li>
                                        <?php
                                        $meta_value = $meta["_{$button['custom_field']}"][0];

                                        if ($button['custom_field'] == 'job_location') {
                                            $meta_value = c27()->get_listing_short_address($post);
                                        }

                                        if ( is_serialized( $meta_value ) ) {
                                            $meta_value = join( ', ', (array) unserialize( $meta_value ) );
                                        }

                                        $GLOBALS['c27_active_shortcode_content'] = $meta["_{$button['custom_field']}"][0];
                                        $btn_content = str_replace('[[field]]', $meta_value, do_shortcode($button['label']));

                                        if (has_shortcode($button['label'], '27-format')) {
                                            $button_classes.= ' formatted ';

                                            preg_match('/\[27-format.*type="(?<format_type>[^"]+)"/', $button['label'], $matches);

                                            if (isset($matches['format_type']) && $matches['format_type']) {
                                                $button_classes .= ' ' . $matches['format_type'] . ' ';
                                            }
                                        }
                                        ?>

                                        <?php if (trim($meta_value) && trim($btn_content)): ?>
                                            <div class="buttons medium <?php echo esc_attr( $button_style ) ?> <?php echo esc_attr( $button_classes ) ?>">
                                                <?php echo $btn_content ?>
                                            </div>
                                        <?php endif ?>
                                    </li>
                                <?php endif ?>

                                <?php if ($button['action'] == 'display-rating' && ($listing_rating = CASE27_Integrations_Review::get_listing_rating_optimized(get_the_ID())) ): ?>
                                     <li>
                                         <div class="inside-rating listing-rating <?php echo esc_attr( $button_style ) ?>">
                                             <span class="value"><?php echo esc_html( $listing_rating ) ?></span>
                                             <sup class="out-of">/10</sup>
                                         </div>
                                     </li>
                                <?php endif ?>

                                <?php if ($button['action'] == 'bookmark'): ?>
                                    <li>
                                        <a href="#" data-listing-id="<?php echo esc_attr( get_the_ID() ) ?>" data-nonce="<?php echo esc_attr( wp_create_nonce('c27_bookmark_nonce') ) ?>"
                                           class="buttons <?php echo esc_attr( $button_style ) ?> medium bookmark c27-bookmark-button <?php echo CASE27_Integrations_Bookmark::instance()->is_bookmarked(get_the_ID(), get_current_user_id()) ? 'bookmarked' : '' ?>">
                                            <?php echo do_shortcode($button['label']) ?>
                                        </a>
                                    </li>
                                <?php endif ?>

                                <?php if ($button['action'] == 'book'): ?>
                                    <li>
                                        <a href="#book-now" class="buttons <?php echo esc_attr( $button_style ) ?> medium book-now c27-book-now">
                                            <?php echo do_shortcode($button['label']) ?>
                                        </a>
                                    </li>
                                <?php endif ?>

                                <?php if ($button['action'] == 'add-review'): ?>
                                   <li>
                                       <a href="#add-review" class="buttons <?php echo esc_attr( $button_style ) ?> medium add-review c27-add-listing-review">
                                           <?php echo do_shortcode($button['label']) ?>
                                       </a>
                                   </li>
                                <?php endif ?>

                                <?php if ($button['action'] == 'share'): ?>
                                    <?php $links = c27('Integrations_Share')->get_links([
                                        'permalink' => get_permalink(),
                                        'image' => job_manager_get_resized_image( $meta['_job_logo'][0], 'large'),
                                        'title' => get_the_title(),
                                        'description' => get_the_content(),
                                        ]) ?>

                                        <?php if ($links): ?>
                                            <li class="dropdown">
                                                <a href="#" class="buttons <?php echo esc_attr( $button_style ) ?> medium show-dropdown sn-share" type="button" id="<?php echo esc_attr( $buttonID ) ?>" data-toggle="dropdown">
                                                    <?php echo do_shortcode( $button['label'] ) ?>
                                                </a>
                                                <ul class="i-dropdown share-options dropdown-menu" aria-labelledby="<?php echo esc_attr( $buttonID ) ?>">
                                                    <?php foreach ($links as $link): ?>
                                                        <li><?php c27('Integrations_Share')->print_link( $link ) ?></li>
                                                    <?php endforeach ?>
                                                </ul>
                                            </li>
                                        <?php endif ?>
                                <?php endif ?>
                            <?php endforeach ?>
                        <?php endif ?>

                        <li class="dropdown">
                            <a href="#" class="buttons button-outlined medium show-dropdown c27-listing-actions" type="button" id="more-actions" data-toggle="dropdown">
                                <i class="mi more_vert"></i>
                            </a>
                            <ul class="i-dropdown share-options dropdown-menu" aria-labelledby="more-actions">
                                <?php
                                if ( job_manager_user_can_edit_job( $post->ID ) && function_exists( 'wc_get_account_endpoint_url' ) ) :
                                    $endpoint = wc_get_account_endpoint_url( 'my-listings' );
                                    $edit_link = add_query_arg([
                                        'action' => 'edit',
                                        'job_id' => $post->ID
                                        ], $endpoint);
                                    ?>
                                    <li><a href="<?php echo esc_url( $edit_link ) ?>"><?php _e( 'Edit Listing', 'my-listing' ) ?></a></li>
                                <?php endif ?>
                                <li><a href="#" data-toggle="modal" data-target="#report-listing-modal"><?php _e( 'Report this Listing', 'my-listing' ) ?></a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <div class="profile-header">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div v-pre>
                        <?php if (isset($meta['_job_logo']) && $meta['_job_logo'] && $listing_logo = job_manager_get_resized_image( $meta['_job_logo'][0], 'medium')):
                            $listing_logo_large = job_manager_get_resized_image( $meta['_job_logo'][0], 'large');
                            list($width, $height) = getimagesize($listing_logo_large);
                            ?>
                            <a class="profile-avatar open-photo-swipe"
                               href="<?php echo esc_url( $listing_logo_large ) ?>"
                               style="background-image: url('<?php echo esc_url( $listing_logo ) ?>')"
                               data-width="<?php echo esc_attr( $width ) ?>"
                               data-height="<?php echo esc_attr( $height ) ?>"
                               >
                            </a>
                        <?php endif ?>
                    </div>
                    <div class="profile-name" v-pre>
                        <h1 class="case27-primary-text"><?php the_title() ?></h1>
                        <?php if (isset($meta['_job_tagline']) && $meta['_job_tagline'] && $meta['_job_tagline'][0]): ?>
                            <h2><?php echo esc_html( $meta['_job_tagline'][0] ) ?></h2>
                        <?php elseif (isset($meta['_job_description']) && $meta['_job_description']): ?>
                            <h2><?php echo c27()->the_text_excerpt($meta['_job_description'][0], 77) ?></h2>
                        <?php endif ?>
                    </div>
                    <div class="cover-details" v-pre>
                        <ul></ul>
                    </div>
                    <div class="profile-menu">
                        <ul role="tablist">
                            <?php $i = 0;
                            foreach ((array) $layout['menu_items'] as $key => $menu_item): $i++;

                                if (
                                    $menu_item['page'] == 'bookings' &&
                                    $menu_item['provider'] == 'timekit' &&
                                    ( empty( $meta["_{$menu_item['field']}"] ) || empty( $meta["_{$menu_item['field']}"][0] ) )
                                ) { continue; }

                                ?><li class="<?php echo ($i == 1) ? 'active' : '' ?>">
                                    <a href="<?php echo "#_tab_{$i}" ?>" aria-controls="<?php echo esc_attr( "_tab_{$i}" ) ?>" data-section-id="<?php echo esc_attr( "_tab_{$i}" ) ?>"
                                       role="tab" class="tab-reveal-switch <?php echo esc_attr( "toggle-tab-type-{$menu_item['page']}" ) ?>">
                                        <?php echo esc_html( $menu_item['label'] ) ?>

                                        <?php if ($menu_item['page'] == 'comments'): ?>
                                            <span class="items-counter"><?php echo get_comments_number() ?></span>
                                        <?php endif ?>

                                        <?php if (in_array($menu_item['page'], ['related_listings', 'store'])):
                                            $vue_data_keys = ['related_listings' => 'related_listings', 'store' => 'products'];
                                            ?>
                                            <span class="items-counter" v-if="<?php echo esc_attr( $vue_data_keys[$menu_item['page']] ) ?>['_tab_<?php echo esc_attr( $i ) ?>'].loaded" v-cloak>
                                                {{ <?php echo $vue_data_keys[$menu_item['page']] ?>['_tab_<?php echo $i ?>'].count }}
                                            </span>
                                            <span v-else class="c27-tab-spinner">
                                                <i class="fa fa-circle-o-notch fa-spin"></i>
                                            </span>
                                        <?php endif ?>
                                    </a>
                                </li><?php
                            endforeach; ?>
                            <div id="border-bottom"></div>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-content">
        <?php $i = 0; ?>
        <?php foreach ((array) $layout['menu_items'] as $key => $menu_item): $i++; ?>
            <section class="tab-pane profile-body <?php echo ($i == 1) ? 'active' : '' ?> <?php echo esc_attr( "tab-type-{$menu_item['page']}" ) ?>" id="<?php echo esc_attr( "_tab_{$i}" ) ?>" role="tabpanel">

                <?php if ($menu_item['page'] == 'main' || $menu_item['page'] == 'custom'): ?>
                    <div class="container" v-pre>
                        <div class="row grid reveal">

                            <?php foreach ($menu_item['layout'] as $block):
                                $block_wrapper_class = 'col-md-6 col-sm-12 col-xs-12 grid-item';

                                if (
                                    $listing->type && ! empty( $block['show_field'] ) &&
                                    ! empty( $meta["_{$block['show_field']}"] ) &&
                                    $listing->type->get_field( $block['show_field'] )
                                ) {
                                    $field = $listing->type->get_field( $block['show_field'] );
                                } else {
                                    $field = null;
                                }

                                // Text Block.
                                if ($block['type'] == 'text' && isset($meta["_{$block['show_field']}"]) && $meta["_{$block['show_field']}"]) {
                                    if ($block_content = $meta["_{$block['show_field']}"][0]) {
                                        c27()->get_section('content-block', [
                                            'ref' => 'single-listing',
                                            'icon' => 'material-icons://view_headline',
                                            'title' => $block['title'],
                                            'content' => $block_content,
                                            'wrapper_class' => $block_wrapper_class,
                                            'escape_html' => $field && $field['type'] !== 'wp-editor',
                                            ]);
                                    }
                                }


                                // Gallery Block.
                                if ($block['type'] == 'gallery' && isset($meta["_{$block['show_field']}"]) && $meta["_{$block['show_field']}"]) {
                                    $gallery_type = 'carousel';
                                    foreach ((array) $block['options'] as $option) {
                                        if ($option['name'] == 'gallery_type') $gallery_type = $option['value'];
                                    }

                                    if ( is_serialized( $meta["_{$block['show_field']}"][0] ) ) {
                                        $gallery_items = (array) unserialize($meta["_{$block['show_field']}"][0]);
                                    } else {
                                        $gallery_items = (array) $meta["_{$block['show_field']}"][0];
                                    }

                                    // dump($gallery_items);

                                    if (array_filter($gallery_items)) {
                                        c27()->get_section('gallery-block', [
                                            'ref' => 'single-listing',
                                            'icon' => 'material-icons://insert_photo',
                                            'title' => $block['title'],
                                            'gallery_type' => $gallery_type,
                                            'wrapper_class' => $block_wrapper_class,
                                            'gallery_items' => array_filter($gallery_items),
                                            'gallery_item_interface' => 'CASE27_JOB_MANAGER_ARRAY',
                                            ]);
                                    }
                                }


                                // Categories Block.
                                if ($block['type'] == 'categories' && ! is_wp_error($categories) && count($categories)) {
                                    c27()->get_section('listing-categories-block', [
                                        'ref' => 'single-listing',
                                        'icon' => 'material-icons://view_module',
                                        'title' => $block['title'],
                                        'terms' => $categories,
                                        'wrapper_class' => $block_wrapper_class,
                                        ]);
                                }

                                // Tags Block.
                                if ($block['type'] == 'tags' && ! is_wp_error($tags) && count($tags)) {
                                    c27()->get_section('list-block', [
                                        'ref' => 'single-listing',
                                        'icon' => 'material-icons://view_module',
                                        'title' => $block['title'],
                                        'items' => $tags,
                                        'item_interface' => 'WP_TERM',
                                        'wrapper_class' => $block_wrapper_class,
                                        ]);
                                }

                                if ( $block['type'] == 'terms' ) {
                                    $taxonomy = 'job_listing_category';
                                    $template = 'listing-categories-block';

                                    if ( isset( $block['options'] ) ) {
                                        foreach ((array) $block['options'] as $option) {
                                            if ($option['name'] == 'taxonomy') $taxonomy = $option['value'];
                                            if ($option['name'] == 'style') $template = $option['value'];
                                        }
                                    }

                                    $terms = array_filter( (array) wp_get_object_terms($post->ID, $taxonomy, ['orderby' => 'term_order', 'order' => 'ASC']) );

                                    if ( ! is_wp_error( $terms ) && count( $terms ) ) {
                                        if ( $template == 'list-block' ) {
                                            c27()->get_section('list-block', [
                                                'ref' => 'single-listing',
                                                'icon' => 'material-icons://view_module',
                                                'title' => $block['title'],
                                                'items' => $terms,
                                                'item_interface' => 'WP_TERM',
                                                'wrapper_class' => $block_wrapper_class,
                                            ]);
                                        } else {
                                            c27()->get_section('listing-categories-block', [
                                                'ref' => 'single-listing',
                                                'icon' => 'material-icons://view_module',
                                                'title' => $block['title'],
                                                'terms' => $terms,
                                                'wrapper_class' => $block_wrapper_class,
                                            ]);
                                        }
                                    }
                                }


                                // Location Block.
                                if ($block['type'] == 'location' && isset($meta["_{$block['show_field']}"]) && $meta["_{$block['show_field']}"]) {
                                    $block_location = $meta["_{$block['show_field']}"][0];
                                    $listing_logo = c27()->image('27.jpg');
                                    if (isset($meta['_job_logo']) && $meta['_job_logo']) {
                                        $listing_logo = job_manager_get_resized_image($meta['_job_logo'][0], 'thumbnail');
                                    }

                                    $location_arr = [
                                        'address' => $block_location,
                                        'marker_image' => ['url' => $listing_logo],
                                    ];

                                    if ( $block['show_field'] == 'job_location' && ! empty( $meta['geolocation_lat'] ) && ! empty( $meta['geolocation_long'] ) ) {
                                        $location_arr = [
                                            'marker_lat' => $meta['geolocation_lat'][0],
                                            'marker_lng' => $meta['geolocation_long'][0],
                                            'marker_image' => ['url' => $listing_logo],
                                        ];
                                    }

                                    $map_skin = 'skin1';
                                    if ( ! empty( $block['options'] ) ) {
                                        foreach ((array) $block['options'] as $option) {
                                            if ($option['name'] == 'map_skin') $map_skin = $option['value'];
                                        }
                                    }

                                    if ($block_location) {
                                        c27()->get_section('map', [
                                            'ref' => 'single-listing',
                                            'icon' => 'material-icons://map',
                                            'title' => $block['title'],
                                            'wrapper_class' => $block_wrapper_class,
                                            'template' => 'block',
                                            'options' => [
                                                'locations' => [ $location_arr ],
                                                'zoom' => 11,
                                                'draggable' => false,
                                                'skin' => $map_skin,
                                            ],
                                        ]);
                                    }
                                }


                                // Contact Form Block.
                                if ($block['type'] == 'contact_form') {
                                    $contact_form_id = false;
                                    $email_to = ['job_email'];
                                    $recipients = [];
                                    foreach ((array) $block['options'] as $option) {
                                        if ($option['name'] == 'contact_form_id') $contact_form_id = $option['value'];
                                        if ($option['name'] == 'email_to') $email_to = $option['value'];
                                    }

                                    foreach ($email_to as $email) {
                                        if (isset($meta["_{$email}"]) && $meta["_{$email}"] && is_email($meta["_{$email}"][0])) {
                                            $recipients[] = $meta["_{$email}"][0];
                                        }
                                    }

                                    if ($contact_form_id && count($recipients)) {
                                        c27()->get_section('content-block', [
                                            'ref' => 'single-listing',
                                            'icon' => 'material-icons://email',
                                            'title' => $block['title'],
                                            'content' => str_replace('%case27_recipients%', join(',', $recipients), do_shortcode("[contact-form-7 id=\"{$contact_form_id}\"]")),
                                            'wrapper_class' => $block_wrapper_class,
                                            'escape_html' => false,
                                            ]);
                                    }
                                }


                                // Host Block.
                                if ($block['type'] == 'related_listing' && isset($meta['_related_listing']) && $meta['_related_listing']) {
                                    if ($related_listing = (int) $meta['_related_listing'][0]) {
                                        c27()->get_section('related-listing-block', [
                                            'ref' => 'single-listing',
                                            'icon' => 'material-icons://layers',
                                            'title' => $block['title'],
                                            'related_listing' => $related_listing,
                                            'wrapper_class' => $block_wrapper_class,
                                            ]);
                                    }
                                }


                                // Countdown Block.
                                if ($block['type'] == 'countdown' && isset($meta["_{$block['show_field']}"]) && $meta["_{$block['show_field']}"]) {
                                    if ($countdown_date = $meta["_{$block['show_field']}"][0]) {
                                        c27()->get_section('countdown-block', [
                                            'ref' => 'single-listing',
                                            'icon' => 'material-icons://av_timer',
                                            'title' => $block['title'],
                                            'countdown_date' => $countdown_date,
                                            'wrapper_class' => $block_wrapper_class,
                                            ]);
                                    }
                                }

                                // Video Block.
                                if ($block['type'] == 'video' && isset($meta["_{$block['show_field']}"]) && $meta["_{$block['show_field']}"]) {
                                    if ( $video_url = $meta["_{$block['show_field']}"][0] ) {
                                        c27()->get_section('video-block', [
                                            'ref' => 'single-listing',
                                            'icon' => 'material-icons://videocam',
                                            'title' => $block['title'],
                                            'video_url' => $video_url,
                                            'wrapper_class' => $block_wrapper_class,
                                            ]);
                                    }
                                }

                                if (in_array($block['type'], ['table', 'accordion', 'tabs', 'details'])) {
                                    $rows = [];

                                    foreach ((array) $block['options'] as $option) {
                                        if ($option['name'] == 'rows') {
                                            foreach ((array) $option['value'] as $row) {
                                                if (!is_array($row) || !isset($row['show_field']) || !$row['show_field']) continue;

                                                if (!isset($meta["_{$row['show_field']}"]) || !$meta["_{$row['show_field']}"]) continue;

                                                $rows[] = [
                                                    'title' => $row['label'],
                                                    'content' => str_replace( '[[field]]', $meta["_{$row['show_field']}"][0], $row['content'] ),
                                                    'icon' => isset($row['icon']) ? $row['icon'] : '',
                                                ];
                                            }
                                        }
                                    }
                                }

                                // Table Block.
                                if ($block['type'] == 'table' && count($rows)) {
                                    c27()->get_section('table-block', [
                                        'ref' => 'single-listing',
                                        'icon' => 'material-icons://view_module',
                                        'title' => $block['title'],
                                        'rows' => $rows,
                                        'wrapper_class' => $block_wrapper_class,
                                        ]);
                                }


                                // Details Block.
                                if ($block['type'] == 'details' && count($rows)) {
                                    c27()->get_section('list-block', [
                                        'ref' => 'single-listing',
                                        'icon' => 'material-icons://view_module',
                                        'title' => $block['title'],
                                        'item_interface' => 'CASE27_DETAILS_ARRAY',
                                        'items' => $rows,
                                        'wrapper_class' => $block_wrapper_class,
                                        ]);
                                }


                                // Accordion Block.
                                if ($block['type'] == 'accordion' && count($rows)) {
                                    c27()->get_section('accordion-block', [
                                        'ref' => 'single-listing',
                                        'icon' => 'material-icons://view_module',
                                        'title' => $block['title'],
                                        'rows' => $rows,
                                        'wrapper_class' => $block_wrapper_class,
                                        ]);
                                }

                                // Tabs Block.
                                if ($block['type'] == 'tabs' && count($rows)) {
                                    c27()->get_section('tabs-block', [
                                        'ref' => 'single-listing',
                                        'icon' => 'material-icons://view_module',
                                        'title' => $block['title'],
                                        'rows' => $rows,
                                        'wrapper_class' => $block_wrapper_class,
                                        ]);
                                }


                                // Work Hours Block.
                                if ($block['type'] == 'work_hours' && isset($meta["_work_hours"]) && $meta["_work_hours"]) {
                                    if($work_hours = (array) unserialize($meta["_work_hours"][0])) {
                                        c27()->get_section('work-hours-block', [
                                            'wrapper_class' => $block_wrapper_class . ' open-now sl-zindex',
                                            'ref' => 'single-listing',
                                            'title' => $block['title'],
                                            'icon' => 'material-icons://alarm',
                                            'hours' => $work_hours,
                                            ]);
                                    }
                                }

                                // Social Networks (Links) Block.
                                if ($block['type'] == 'social_networks' && isset($meta["_links"]) && $meta["_links"]) {
                                    if($links = (array) unserialize($meta["_links"][0])) {
                                        c27()->get_section('list-block', [
                                            'ref' => 'single-listing',
                                            'icon' => 'material-icons://view_module',
                                            'title' => $block['title'],
                                            'item_interface' => 'CASE27_LINK_ARRAY',
                                            'items' => array_filter( array_map(function($link) {
                                                if ( ! is_array( $link ) || empty( $link['network'] ) || empty( $link['url'] ) ) {
                                                    return false;
                                                }

                                                return ['title' => $link['network'], 'content' => $link['url']];
                                                }, $links) ),
                                            'wrapper_class' => $block_wrapper_class,
                                        ]);
                                    }
                                }

                                // Author Block.
                                if ($block['type'] == 'author') {
                                    c27()->get_section('author-block', [
                                        'icon' => 'material-icons://account_circle',
                                        'ref' => 'single-listing',
                                        'author_id' => get_the_author_meta( 'ID' ),
                                        'title' => $block['title'],
                                        'wrapper_class' => $block_wrapper_class,
                                    ]);
                                }

                                // Raw content block.
                                if ( $block['type'] == 'raw' ) {
                                    $content = '';
                                    foreach ((array) $block['options'] as $option) {
                                        if ($option['name'] == 'content') $content = $option['value'];
                                    }

                                    if ( $content ) {
                                        c27()->get_section('raw-block', [
                                            'icon' => 'material-icons://view_module',
                                            'ref' => 'single-listing',
                                            'title' => $block['title'],
                                            'wrapper_class' => $block_wrapper_class,
                                            'content' => $content,
                                        ]);
                                    }
                                }

                                do_action( "case27/listing/blocks/{$block['type']}", $block );

                            endforeach ?>
                        </div>
                    </div>
                <?php endif ?>

                <?php if ($menu_item['page'] == 'comments'): ?>
                    <div v-pre>
                        <?php $GLOBALS['case27_reviews_allow_rating'] = $menu_item['allow_rating'] ? true : false ?>
                        <?php comments_template() ?>
                    </div>
                <?php endif ?>

                <?php if ($menu_item['page'] == 'related_listings'): ?>
                    <input type="hidden" class="case27-related-listing-type" value="<?php echo esc_attr( $menu_item['related_listing_type'] ) ?>">
                    <div class="container c27-related-listings-wrapper reveal">
                        <div class="row listings-loading" v-show="related_listings['<?php echo esc_attr( "_tab_{$i}" ) ?>'].loading">
                            <div class="loader-bg">
                                <?php c27()->get_partial('spinner', [
                                    'color' => '#777',
                                    'classes' => 'center-vh',
                                    'size' => 28,
                                    'width' => 3,
                                    ]); ?>
                            </div>
                        </div>
                        <div class="row section-body i-section" v-show="!related_listings['<?php echo esc_attr( "_tab_{$i}" ) ?>'].loading">
                            <div class="c27-related-listings" v-html="related_listings['<?php echo esc_attr( "_tab_{$i}" ) ?>'].html" :style="!related_listings['<?php echo esc_attr( "_tab_{$i}" ) ?>'].show ? 'opacity: 0;' : ''"></div>
                        </div>
                        <div class="row">
                            <div class="c27-related-listings-pagination" v-html="related_listings['<?php echo esc_attr( "_tab_{$i}" ) ?>'].pagination"></div>
                        </div>
                    </div>
                <?php endif ?>

                <?php if ($menu_item['page'] == 'store'):
                    $selected_ids = isset($menu_item['field']) && isset($meta["_{$menu_item['field']}"]) ? unserialize($meta["_{$menu_item['field']}"][0]) : [];
                    ?>
                    <input type="hidden" class="case27-store-products-ids" value="<?php echo json_encode(array_map('absint', (array) $selected_ids)) ?>">
                    <div class="container c27-products-wrapper woocommerce reveal">
                        <div class="row listings-loading" v-show="products['<?php echo esc_attr( "_tab_{$i}" ) ?>'].loading">
                            <div class="loader-bg">
                                <?php c27()->get_partial('spinner', [
                                    'color' => '#777',
                                    'classes' => 'center-vh',
                                    'size' => 28,
                                    'width' => 3,
                                    ]); ?>
                            </div>
                        </div>
                        <div class="section-body" v-show="!products['<?php echo esc_attr( "_tab_{$i}" ) ?>'].loading">
                            <ul class="c27-products products" v-html="products['<?php echo esc_attr( "_tab_{$i}" ) ?>'].html" :style="!products['<?php echo esc_attr( "_tab_{$i}" ) ?>'].show ? 'opacity: 0;' : ''"></ul>
                        </div>
                        <div class="row">
                            <div class="c27-products-pagination" v-html="products['<?php echo esc_attr( "_tab_{$i}" ) ?>'].pagination"></div>
                        </div>
                    </div>
                <?php endif ?>

                <?php if ($menu_item['page'] == 'bookings'): ?>
                    <div class="container" v-pre>
                        <div class="row">
                            <?php // Contact Form Block.
                            if ($menu_item['provider'] == 'basic-form') {
                                $contact_form_id = $menu_item['contact_form_id'];
                                $email_to = [$menu_item['field']];
                                $recipients = [];

                                foreach ($email_to as $email) {
                                    if (isset($meta["_{$email}"]) && $meta["_{$email}"] && is_email($meta["_{$email}"][0])) {
                                        $recipients[] = $meta["_{$email}"][0];
                                    }
                                }

                                c27()->get_section('content-block', [
                                    'ref' => 'single-listing',
                                    'icon' => 'material-icons://email',
                                    'title' => 'Book now',
                                    'content' => str_replace('%case27_recipients%', join(',', $recipients), do_shortcode("[contact-form-7 id=\"{$contact_form_id}\"]")),
                                    'wrapper_class' => 'col-md-6 col-md-push-3 col-sm-8 col-sm-push-2 col-xs-12 grid-item',
                                    'escape_html' => false,
                                    ]);
                            }
                            ?>

                            <?php // TimeKit Widget.
                            if ($menu_item['provider'] == 'timekit' && isset($meta["_{$menu_item['field']}"]) && $meta["_{$menu_item['field']}"]):
                                $timekitID = $meta["_{$menu_item['field']}"][0] ?>

                                <div class="col-md-8 col-md-push-2 c27-timekit-wrapper">
                                    <iframe src="https://my.timekit.io/<?php echo esc_attr( $timekitID ) ?>" frameborder="0"></iframe>
                                </div>

                            <?php endif ?>

                        </div>
                    </div>
                <?php endif ?>

            </section>
        <?php endforeach; ?>
    </div>

    <?php c27()->get_partial('report-modal', ['listing' => $post]) ?>
</div>
