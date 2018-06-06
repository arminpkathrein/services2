<?php

namespace CASE27\Integrations\ListingTypes;

class ListingType {
	private $data, $fields, $single, $preview, $search, $settings;

	public function __construct( \WP_Post $post ) {
		$this->data     = $post;
		$this->fields   = unserialize( $this->data->case27_listing_type_fields );
		$this->single   = unserialize( $this->data->case27_listing_type_single_page_options );
		$this->search   = unserialize( $this->data->case27_listing_type_search_page );
		$this->preview  = unserialize( $this->data->case27_listing_type_result_template );
		$this->settings = unserialize( $this->data->case27_listing_type_settings_page );
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

	public function get_singular_name() {
		return $this->settings['singular_name'] ? : $this->data->post_title;
	}

	public function get_plural_name() {
		return $this->settings['plural_name'] ? : $this->data->post_title;
	}

	public function get_data( $key = null ) {
		if ( $key && isset( $this->data->$key ) ) {
			return $this->data->$key;
		}

		return $this->data;
	}

	public function get_fields() {
		return $this->fields;
	}

	public function get_field( $key = null ) {
		if ( $key && ! empty( $this->fields[ $key ] ) ) {
			return $this->fields[ $key ];
		}

		return false;
	}

	public function get_settings() {
		return $this->settings;
	}

	public function get_preview_options() {
		return (array) $this->preview;
	}

	public function get_search_filters( $form = 'advanced' ) {
		return $this->search[ $form ][ 'facets' ];
	}

	public function get_setting( $key = null ) {
		if ( $key && ! empty( $this->settings[ $key ] ) ) {
			return $this->settings[ $key ];
		}

		return false;
	}

	public function get_image( $size = 'large' ) {
		$image = wp_get_attachment_image_src( get_post_thumbnail_id( $this->data->ID ), $size );

		return $image ? array_shift( $image ) : false;
	}

	public function get_count() {
		// TODO: Find a way to get the post count without querying on every page load.
		// Using transients or something.

		return 0;
	}
}
