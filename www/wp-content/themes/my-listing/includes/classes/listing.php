<?php

namespace CASE27\Classes;

use CASE27\Integrations\ListingTypes\ListingType;

class Listing {
	private $data, $schedule, $categories;
	public $type = null;

	public function __construct( \WP_Post $post ) {
		$this->data = $post;
		$this->schedule = new WorkHours( (array) get_post_meta( $this->data->ID, '_work_hours', true ) );

		if ( $listing_type = ( get_page_by_path( $post->_case27_listing_type, OBJECT, 'case27_listing_type' ) ) ) {
			$this->type = new ListingType( $listing_type );
		}
	}

	public function get_id() {
		return $this->data->ID;
	}

	public function get_name() {
		return $this->data->post_title;
	}

	public function get_slug() {
		return $this->data->post_name;
	}

	public function get_data( $key = null ) {
		if ( $key && isset( $this->data->$key ) ) {
			return $this->data->$key;
		}

		return $this->data;
	}

	public function get_link() {
		return get_permalink( $this->data );
	}

	public function get_schedule() {
		return $this->schedule;
	}

	public function get_type() {
		return $this->type;
	}

	public function get_preview_options() {
		// Get the preview template options for the listing type of the current listing.
		$options = $this->type ? $this->type->get_preview_options() : [];

   		// Merge with the default options, in case the listing type options meta returns null.
		return c27()->merge_options([
	        'template' => 'alternate',
	        'background' => [
	            'type' => 'gallery',
	        ],
	        'buttons' => [],
	        'info_fields' => [],
	        'footer' => [
	            'sections' => [],
	        ],
	    ], $options);
	}
}