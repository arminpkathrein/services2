<?php
    $data = c27()->merge_options([
            'facet' => [],
            'facetID' => uniqid() . '__facet',
            'facet_data' => [
            	'choices' => [
            		['value' => 'date', 'label' => __( 'Latest', 'my-listing' )],
            		['value' => '_case27_average_rating', 'label' => __( 'Top Rated', 'my-listing' )],
            		['value' => 'rand', 'label' => __( 'Random', 'my-listing' )],
            	],
            ],
        ], $data);

    if (!$data['facet']) return;

    $defaultValue = 'date';

    if ( isset( $data['facet']['options'] ) ) {
        foreach ((array) $data['facet']['options'] as $option) {
            if ( $option['name'] == 'default' ) $defaultValue = $option['value'];
        }
    }

    $value = isset($_GET['order_by']) ? $_GET['order_by' ] : $defaultValue;

    $GLOBALS['c27-facets-vue-object'][$data['listing_type']]['orderby'] = $value;
?>

<div class="form-group location-wrapper explore-filter order-filter">
    <!-- <label for="<?php echo esc_attr( $data['facetID'] ) ?>"><?php echo esc_html( $data['facet']['label'] ) ?></label> -->
    <select2 v-model="<?php echo esc_attr( "facets['{$data['listing_type']}']['orderby']" ) ?>"
    	:choices="<?php echo htmlspecialchars(json_encode($data['facet_data']['choices']), ENT_QUOTES, 'UTF-8'); ?>"
    	:selected="<?php echo htmlspecialchars(json_encode((array) $value), ENT_QUOTES, 'UTF-8'); ?>"
        required="required"
    	@input="getListings"></select2>
</div>