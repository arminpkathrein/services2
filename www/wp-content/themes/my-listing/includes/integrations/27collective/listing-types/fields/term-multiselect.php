<?php

namespace CASE27\Integrations\ListingTypes\Fields;

class TermMultiSelectField extends Field {

	public function field_props() {
		$this->props['type'] = 'term-multiselect';
		$this->props['taxonomy'] = '';

		add_filter( 'case27/listingtypes/profile_layout_blocks', [ $this, 'add_terms_layout_blocks' ] );
	}

	public function render() {
		$this->getLabelField();
		$this->getKeyField();
		$this->getPlaceholderField();
		$this->getDescriptionField();
		$this->getTaxonomyField();
		$this->getRequiredField();
		$this->getShowInSubmitFormField();
		$this->getShowInAdminField();
	}

	public function add_terms_layout_blocks( $blocks ) {
		// The block type is named 'categories' instead of 'terms' to maintain
		// compatibility with earlier versions of MyListing.
		$taxonomies = array_column( array_map( function( $tax ) {
				return [ 'name' => $tax->label, 'slug' => $tax->name ];
			}, self::$store['taxonomies'] ), 'name', 'slug' );

		$blocks[] = [
			'type' => 'terms',
			'icon' => 'view_module',
			'title' => 'Terms',
			'title_l10n' => ['locale' => 'en_US'],
			'options' => [
				[
					'label'   => __( 'Taxonomy', 'my-listing' ),
					'name'    => 'taxonomy',
					'type'    => 'select',
					'choices' => $taxonomies,
					'value'   => 'job_listing_category',
				],
				[
					'label'   => __( 'Style', 'my-listing' ),
					'name'    => 'style',
					'type'    => 'select',
					'choices' => [
						'listing-categories-block' => __( 'Colored Icons', 'my-listing' ),
						'list-block' => __( 'Outlined Icons', 'my-listing' ),
					],
					'value'   => 'listing-categories-block',
				]
			],
		];

		return $blocks;
	}
}