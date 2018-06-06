<?php

/*
 * Advanced Custom Fields (ACF) Integration.
 */

class CASE27_Advanced_Custom_Fields_Integration {

	public function __construct()
	{
		add_filter('acf/settings/save_json', [$this, 'save_json']);
		add_filter('acf/settings/load_json', [$this, 'load_json']);

		require_once CASE27_INTEGRATIONS_DIR . '/advanced-custom-fields/acf-icon-picker/acf-icon_picker.php';

		$this->add_options_page();
	}

	public function save_json($path)
	{
    	return CASE27_INTEGRATIONS_DIR . '/advanced-custom-fields/acf-json';
	}


	public function load_json($paths)
	{
		// Remove original path.
    	unset($paths[0]);

    	$paths[] = CASE27_INTEGRATIONS_DIR . '/advanced-custom-fields/acf-json';

	    return $paths;
	}

	public function update_term_listing_type($value, $post_id, $field)
	{
		$term_id = intval(filter_var($post_id, FILTER_SANITIZE_NUMBER_INT));

		if($term_id > 0) update_term_meta($term_id, $field['name'], json_encode($value));

		return $value;
	}

	public function load_term_listing_type($value, $post_id, $field)
	{
		$term_id = intval(filter_var($post_id, FILTER_SANITIZE_NUMBER_INT));

		if($term_id > 0) $value = json_decode(get_term_meta($term_id, $field['name'], true));

		return $value;
	}


	public function add_options_page()
	{
		if( function_exists('acf_add_options_page') ) {
			acf_add_options_page(array(
				'page_title' 	=> __('Theme Options', 'my-listing'),
				'menu_title'	=> __('Theme Options', 'my-listing'),
				'menu_slug' 	=> 'theme-general-settings',
				'capability'	=> 'edit_posts',
				'redirect'		=> false
			));

			acf_add_options_page(array(
				'page_title' 	=> __('Integrations', 'my-listing'),
				'menu_title'	=> __('Integrations', 'my-listing'),
				'menu_slug' 	=> 'theme-integration-settings',
				'capability'	=> 'edit_posts',
				'redirect'		=> false
			));
		}
	}
}

new CASE27_Advanced_Custom_Fields_Integration;