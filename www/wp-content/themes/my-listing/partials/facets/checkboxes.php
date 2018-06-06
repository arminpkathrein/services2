<?php
    $data = c27()->merge_options([
            'facet' => '',
            'facetID' => uniqid() . '__facet',
            'options' => [
            	'count' => 8,
            	'multiselect' => false,
            	'hide_empty' => true,
            	'order_by' => 'count',
                'order' => 'DESC',
            ],
            'facet_data' => [
            	'choices' => [],
            ],
            'type' => null,
        ], $data);

    if (!$data['facet']) return;

    foreach((array) $data['facet']['options'] as $option) {
    	if ($option['name'] == 'count') $data['options']['count'] = $option['value'];
    	if ($option['name'] == 'multiselect') $data['options']['multiselect'] = $option['value'];
    	if ($option['name'] == 'hide_empty') $data['options']['hide_empty'] = $option['value'];
    	if ($option['name'] == 'order_by') $data['options']['order_by'] = $option['value'];
    }

    if (
        $data['type'] &&
        ( $facet_field = $data['type']->get_field( $data['facet'][ 'show_field' ] ) ) &&
        ! empty( $facet_field['taxonomy'] ) &&
        taxonomy_exists( $facet_field['taxonomy'] )
    ) {
        $args = [
            'taxonomy' => $facet_field['taxonomy'],
            'hide_empty' => $data['options']['hide_empty'],
            'orderby' => $data['options']['order_by'],
            'number' => $data['options']['count'],
            'order' => $data['options']['order'],
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => 'listing_type',
                    'value' => '"' . $data['type']->get_id() . '"',
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
        ];

        $cache_version = get_option( 'listings_tax_' . $facet_field['taxonomy'] . '_version', 100 );
        // dump($cache_version);
        $categories_hash = 'c27_cats_' . md5( json_encode( $args ) ) . '_v' . $cache_version;
        $terms = get_transient( $categories_hash );

        if ( empty( $terms ) ) {
            $terms = get_terms( $args );
            set_transient( $categories_hash, $terms, HOUR_IN_SECONDS * 6 );
            // dump( 'Loaded via db query' );
        } else {
            // dump( 'Loaded from cache' );
        }


        // dd( $terms );

        if ( ! is_wp_error( $terms ) ) {
            if ( $data['options']['order_by'] == 'name' ) {

                CASE27\Classes\Term::iterate_recursively(
                    function( $term, $depth ) use ( &$data ) {
                        $data['facet_data']['choices'][] = [
                            'value' => $term->slug,
                            'label' => str_repeat( '&mdash;', $depth - 1 ) . ' ' . $term->name,
                            'selected' => false,
                        ];
                    },
                    CASE27\Classes\Term::get_term_tree( $terms )
                );

            } else {
                foreach ((array) $terms as $term) {
                    $term = new CASE27\Classes\Term( $term );
                    $data['facet_data']['choices'][] = [
                        'value' => $term->get_slug(),
                        'label' => $term->get_full_name(),
                        'selected' => false,
                    ];
                }
            }
        }
    } else {
        if (!function_exists('c27_checkboxes_facet_query_group_by_filter')) {
            function c27_checkboxes_facet_query_group_by_filter($groupby) { global $wpdb;
                return $wpdb->postmeta . '.meta_value ';
            }
        }

        if (!function_exists('c27_checkboxes_facet_query_fields_filter')) {
            function c27_checkboxes_facet_query_fields_filter($fields) { global $wpdb;
                return "{$fields}, {$wpdb->postmeta}.meta_value, COUNT(*) as c27_field_count ";
            }
        }

        add_filter('posts_fields', 'c27_checkboxes_facet_query_fields_filter');
        add_filter('posts_groupby', 'c27_checkboxes_facet_query_group_by_filter');

    	$posts = query_posts([
    		'post_type' => 'job_listing',
    		'posts_per_page' => $data['options']['count'],
    		'meta_key'  => "_{$data['facet']['show_field']}",
    		'orderby'   => 'meta_value',
    		'order' => 'DESC',
    		]);

        remove_filter('posts_fields', 'c27_checkboxes_facet_query_fields_filter');
        remove_filter('posts_groupby', 'c27_checkboxes_facet_query_group_by_filter');

    	foreach ((array) $posts as $post) {
            if ( is_serialized( $post->meta_value ) ) {
                foreach ( array_filter( (array) unserialize( $post->meta_value ) ) as $value) {
                    $data['facet_data']['choices'][] = [
                        'value' => $value,
                        'label' => $value,
                    ];
                }

                continue;
            }

    		$data['facet_data']['choices'][] = [
    			'value' => $post->meta_value,
    			'label' => $post->meta_value,
    		];
    	}

        $data['facet_data']['choices'] = array_map( 'unserialize', array_unique( array_map( 'serialize', $data['facet_data']['choices'] ) ) );
    }

    $facet_show_field = $data['facet']['show_field'];
    if ( $facet_show_field == 'job_category' ) {
        $facet_show_field = 'category';
    } elseif ( $facet_show_field == 'job_tags' ) {
        $facet_show_field = 'tag';
    }

    if ( ! empty( $_GET[$data['facet']['show_field']] ) ) {
        $selected = (array) $_GET[$data['facet']['show_field']];
    } elseif ( ( $selected_val = get_query_var( sprintf( 'explore_%s', $facet_show_field ) ) ) ) {
        $selected = (array) $selected_val;
    } else {
        $selected = [];
    }
    $choices_flat = (array) array_column( $data['facet_data']['choices'], 'value' );
    $selected = array_filter( array_filter( $selected, function( $value ) use ( $choices_flat ) {
        return in_array( $value, $choices_flat );
    } ) );

    $GLOBALS['c27-facets-vue-object'][$data['listing_type']][$data['facet']['show_field']] = $selected;

    // dump($selected);
?>

<div class="form-group form-group-tags explore-filter checkboxes-filter">
	<label><?php echo esc_html( $data['facet']['label'] ) ?></label>
	<ul class="tags-nav">
		<?php foreach ((array) $data['facet_data']['choices'] as $choice): $choiceID = uniqid() . '__choiceid'; ?>
			<li>
				<div class="md-checkbox">
					<input id="<?php echo esc_attr( $choiceID ) ?>"
                           type="<?php echo $data['options']['multiselect'] ? 'checkbox' : 'radio' ?>"
                           value="<?php echo esc_attr( $choice['value'] ) ?>"
                           v-model="<?php echo esc_attr( "facets['{$data['listing_type']}']['{$data['facet']['show_field']}']" ) ?>"
                           @change="getListings"
                           >
					<label for="<?php echo esc_attr( $choiceID ) ?>" class=""><?php echo esc_attr( $choice['label'] ) ?></label>
				</div>
			</li>
		<?php endforeach ?>
	</ul>
</div>
