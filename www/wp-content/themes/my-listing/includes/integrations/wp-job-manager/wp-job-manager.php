<?php
/**
 * WP Job Manager.
 */

class CASE27_WP_Job_Manager_Integration {

	public function __construct()
	{

		// Handle Custom Queries.
		require_once CASE27_INTEGRATIONS_DIR . '/wp-job-manager/wp-job-manager-queries.php';

		// Default Fields.
		require_once CASE27_INTEGRATIONS_DIR . '/wp-job-manager/listing-fields/default-fields.php';

		// Add submit/edit/admin fields to listing types.
		require_once CASE27_INTEGRATIONS_DIR . '/wp-job-manager/listing-fields/index.php';

		// Rewrite post type slug from 'job' to 'listing'.
		// add_filter('register_post_type_job_listing', [$this, 'register_post_type_job_listing']);

		add_filter( 'pre_option_category_base', function( $base ) {
			if ( ! $base || $base == 'category' ) {
				return 'post-category';
			}

			return $base;
		});

		add_filter( 'pre_option_tag_base', function( $base ) {
			if ( ! $base || $base == 'tag' ) {
				return 'post-tag';
			}

			return $base;
		});

		add_filter( 'pre_option_job_category_base', function( $base ) {
			if ( ! $base || $base == 'listing-category' || $base == 'job-category' ) {
				return 'category';
			}

			return $base;
		});


		// Update WP Job Manager template override folder.
		add_filter( 'job_manager_locate_template', [$this, 'job_manager_locate_template'], 10, 3 );

		// Edit 'Listings' page columns in admin area.
		add_filter( 'manage_edit-job_listing_columns', [$this, 'admin_columns_head'], 10 );

		// Replace the default company name, url, and logo with the listing type and listing logo.
		add_filter( 'the_company_website', '__return_false', 10, 2 );
		add_filter( 'the_company_name', [$this, 'the_company_name'], 10, 2 );
		add_filter( 'job_manager_default_company_logo', [$this, 'job_manager_default_company_logo'], 10, 2 );
		add_filter( 'option_job_manager_enable_categories', '__return_true' );

		add_filter( 'job_manager_update_job_data', [$this, 'save_listing'], 100, 2 );

		// Add support for comments/reviews on listings.
		add_post_type_support('job_listing', 'comments');

		// Remove company name, location, and job type - from the listing permalink structure.
		// This way, only the listing name is left in a shorter and cleaner url format.
		add_filter( 'submit_job_form_prefix_post_name_with_company', '__return_false' );
		add_filter( 'submit_job_form_prefix_post_name_with_location', '__return_false' );
		add_filter( 'submit_job_form_prefix_post_name_with_job_type', '__return_false' );

		add_action('job_manager_update_job_data', [$this, 'job_manager_terms_order'], 10, 2);
		add_filter('submit_job_form_validate_fields', [$this, 'validate_fields'], 10, 3);

		// My Listings Dashboard.
		add_action( 'wp', array( $this, 'my_listings_action_handler' ) );
		add_filter( 'job_manager_my_job_actions', [$this, 'my_listings_actions'], 10, 2 );
		add_filter( 'job_manager_get_dashboard_jobs_args', [$this, 'my_listings_args'] );
		add_filter( 'job_manager_job_dashboard_columns', [$this, 'my_listings_columns'] );
		add_filter( 'job_manager_pagination_args', [$this, 'my_listings_pagination'] );
		add_filter( 'job_manager_job_dashboard_column_listing_type', [$this, 'my_listings_show_listing_type'] );
		add_action( 'job_manager_job_dashboard_column_c27_listing_logo', [$this, 'my_listings_show_listing_logo'] );
		add_filter( 'job_manager_chosen_enabled', '__return_false');
		add_filter( 'list_product_cats', [ $this, 'list_product_cats' ], 10, 2 );
		add_filter( 'pre_option_job_manager_enable_types', function($opt) {
			return "0";
		}, 1050 );

		add_action('job_manager_job_dashboard_do_action_case27_relist', function() {
			if ( ( $listing = get_post( absint( $_REQUEST['job_id'] ) ) ) && ($addListingPage = c27()->get_setting( 'general_add_' . 'listing_page', false ) ) ) {
				if ( $listing->post_status !== 'expired' ) return;

				wp_redirect( add_query_arg([
						'job_id' => $listing->ID,
						// 'listing_type' => get_post_meta( $listing->ID, '_case27_listing_type', true ),
						'_wpnonce' => wp_create_nonce('c27_relist_product'),
						], $addListingPage));
				exit;
			}
		});
	}


	public function my_listings_action_handler()
	{
		global $post;

		if ( $post && has_shortcode($post->post_content, 'woocommerce_my_account' ) ) {
			WP_Job_Manager_Shortcodes::instance()->job_dashboard_handler();
		}
	}


	public function my_listings_actions($actions, $job)
	{
		unset($actions['mark_filled']);
		unset($actions['duplicate']);
		unset($actions['relist']);

		$newActions = [];

		if ($job->post_status == 'publish') {
			if ( c27()->get_setting( 'promotions_enabled', false ) ) {
				$promoted = absint( get_post_meta( $job->ID, '_case27_listing_promotion_key_id', true ) );

				$newActions['case27_promote'] = [
				'label' => $promoted ? __( 'Promoted', 'my-listing' ) : __( 'Promote', 'my-listing' ),
				'nonce' => true,
				];
			}
		}

		if ($job->post_status == 'expired' && ($addListingPage = c27()->get_setting('general_add_' . 'listing_page', false) ) ) {
			$newActions['case27_relist'] = [ 'label' => __( 'Relist', 'my-listing' ), 'nonce' => true ];
		}

		return $newActions + $actions;
	}


	public function my_listings_args($args)
	{
		unset($args['offset']);
		$args['paged'] = isset($_GET['listings_page']) ? absint($_GET['listings_page']) : 1;

		return $args;
	}


	public function my_listings_pagination($args)
	{
		global $post;

		if (is_page() && has_shortcode($post->post_content, 'woocommerce_my_account')) {
			unset($args['base']);
			$args['format'] = '?listings_page=%#%';
			$args['current'] = isset($_GET['listings_page']) ? absint($_GET['listings_page']) : 1;
		}

		return $args;
	}


	public function my_listings_show_listing_type( $listing )
	{
		$listing_type = get_post_meta($listing->ID, '_case27_listing_type', true);

		if ( ! $listing_type ) {
			echo '&mdash;'; return;
		}

		$settings = c27()->get_listing_type_options(
				$listing_type,
				['settings']
			)['settings'];

		if ( is_array( $settings ) && isset( $settings['singular_name'] ) && $settings['singular_name'] ) {
			echo esc_html( $settings['singular_name'] );
		} else {
			echo esc_html( ucwords( str_replace(['-', '_'], ' ', $listing_type) ) );
		}
	}

	public function my_listings_show_listing_logo( $listing )
	{
		$logo = get_post_meta( $listing->ID, '_job_logo', true );

		if ( $logo ) {
			$logoResized = job_manager_get_resized_image( $logo, 'thumbnail' );

			echo '<img src="' . esc_url( $logoResized ) . '" width="32" height="32">';
		}
	}


	public function register_post_type_job_listing($args)
	{
		$args['rewrite']['slug'] = 'listing';

		return $args;
	}


	public function my_listings_columns($columns)
	{
		unset($columns['filled']);

		return [
			'c27_listing_logo' => 'photo',
			'job_title' => $columns['job_title'],
			'listing_type' => __( 'Listing Type', 'my-listing' ),
		] + $columns;
	}


	public function job_manager_locate_template($template, $template_name, $template_path)
	{
		if (
			locate_template( "includes/integrations/wp-job-manager/templates/{$template_name}" )
			&& file_exists( CASE27_INTEGRATIONS_DIR . "/wp-job-manager/templates/{$template_name}" )
		) {
			$template = CASE27_INTEGRATIONS_DIR . "/wp-job-manager/templates/{$template_name}";
		}

		return apply_filters( 'case27_job_manager_locate_template', $template, $template_name, $template_path );
	}


	public function admin_columns_head($defaults)
	{
		unset($defaults['filled']);
		unset($defaults['job_listing_type']);

		return [
			'cb' => $defaults['cb'],
			'job_position' => 'Name',
			'job_location' => '<span class="dashicons dashicons-location"></span> ' . __( 'Location', 'my-listing' ),
			'job_listing_category' => '<span class="dashicons dashicons-paperclip"></span> ' . __( 'Categories', 'my-listing' ),
			'taxonomy-case27_job_listing_tags' => '<span class="dashicons dashicons-tag"></span> ' . __( 'Tags', 'my-listing' ),
			'comments' => '<span class="dashicons dashicons-admin-comments"></span> ' . __( 'Reviews', 'my-listing' ),
			'job_expires' => '<span class="dashicons dashicons-clock"></span> ' . __( 'Expires', 'my-listing' ),
		] + $defaults;
	}


	public function the_company_name($name, $post)
	{
		if ( $post->post_type !== 'job_listing' ) {
			return '';
		}

		return get_post_meta($post->ID, '_case27_listing_type', true);
	}


	public function job_manager_default_company_logo($logo)
	{
		global $post;
		return $post->_job_logo ? job_manager_get_resized_image($post->_job_logo, 'thumbnail') : $logo;
	}

	public function job_manager_terms_order($listing_id, $values)
	{
		global $wpdb;
		$object_id = (int) $listing_id;

		if (isset($values['job']['job_category'])) { $counter = 1;
			foreach ((array) $values['job']['job_category'] as $category) { $cat_id = (int) $category;
				$wpdb->query("UPDATE {$wpdb->term_relationships} SET term_order = '{$counter}' WHERE object_id = '{$object_id}' AND term_taxonomy_id = '{$cat_id}'" );
				$counter++;
			}
		}

		if (isset($values['job']['job_tags'])) { $counter = 1;
			foreach ((array) $values['job']['job_tags'] as $tag) { $tag_id = (int) $tag;
				$wpdb->query("UPDATE {$wpdb->term_relationships} SET term_order = '{$counter}' WHERE object_id = '{$object_id}' AND term_taxonomy_id = '{$tag_id}'" );
				$counter++;
			}
		}
	}

	public function validate_fields($isValid, $fields, $values)
	{
		$values = $values['job'];

		foreach ( $fields['job'] as $key => $field ) {
			if ($field['slug'] == 'job_tagline' && isset($values['job_tagline']) && strlen($values['job_tagline']) > 90) {
				return new WP_Error( 'validation-error', sprintf( __( '%s can\'t be longer than 90 characters.', 'my-listing' ), $field['label'] ) );
			}

			if ($field['type'] == 'number' && isset($values[$field['slug']]) && $values[$field['slug']]) {
				if (!is_numeric($values[$field['slug']])) {
					return new WP_Error( 'validation-error', sprintf( __( '%s must be a number.', 'my-listing' ), $field['label'] ) );
				}

				$min = is_numeric($field['min']) ? (int) $field['min'] : false;
				$max = is_numeric($field['max']) ? (int) $field['max'] : false;

				if (($min !== false) && $values[$field['slug']] < $min) {
					return new WP_Error( 'validation-error', sprintf( __( '%s can\'t be smaller than %s.', 'my-listing' ), $field['label'], $min ) );
				}

				if (($max !== false) && $values[$field['slug']] > $max) {
					return new WP_Error( 'validation-error', sprintf( __( '%s can\'t be bigger than %s.', 'my-listing' ), $field['label'], $max ) );
				}
			}

			if ($field['type'] == 'email' && isset($values[$field['slug']]) && $values[$field['slug']]) {
				if (!filter_var($values[$field['slug']], FILTER_VALIDATE_EMAIL)) {
					return new WP_Error( 'validation-error', sprintf( __( '%s must be a valid email address.', 'my-listing' ), $field['label'] ) );
				}
			}

			if ($field['type'] == 'url' && isset($values[$field['slug']]) && $values[$field['slug']]) {
				if (!filter_var($values[$field['slug']], FILTER_VALIDATE_URL)) {
					return new WP_Error( 'validation-error', sprintf( __( '%s must be a valid url address.', 'my-listing' ), $field['label'] ) );
				}
			}

			if ( in_array( $field['slug'], ['job_category', 'job_tags', 'region'] ) && isset( $values[$field['slug']] ) && $values[$field['slug']] ) {
				$listing_type = get_page_by_path( $values['case27_listing_type'], OBJECT, 'case27_listing_type' );

				foreach ( (array) $values[ $field['slug'] ] as $term_id ) {
					$term_meta = get_term_meta( $term_id, 'listing_type', true );

					if ( is_array( $term_meta ) && ! empty( $term_meta ) && ! in_array( $listing_type->ID, $term_meta ) ) {
						return new WP_Error( 'validation-error', sprintf( __( 'Invalid category.', 'my-listing' ), $field['label'] ) );
					}
				}
			}
		}

		// dd($fields, $values);

		return $isValid;
	}

	public function save_listing($listing_id, $values)
	{
		if (isset($_POST['job_location__custom_coords']) && $_POST['job_location__custom_coords']) {
			$latitude = isset($_POST['job_location__latitude']) && $_POST['job_location__latitude'] ? (float) $_POST['job_location__latitude'] : false;
			$longitude = isset($_POST['job_location__longitude']) && $_POST['job_location__longitude'] ? (float) $_POST['job_location__longitude'] : false;

			if ($latitude && $longitude && ($latitude <= 90) && ($latitude >= -90) && ($longitude <= 180) && ($longitude >= -180)) {
				update_post_meta($listing_id, 'geolocation_lat', $latitude);
				update_post_meta($listing_id, 'geolocation_long', $longitude);
				update_post_meta($listing_id, 'geolocation_custom_coords', 'yes');
			}
		} else {
			update_post_meta($listing_id, 'geolocation_custom_coords', false);
		}
	}

	public function list_product_cats( $name, $object )
	{
		return $this->get_parent_category_name( $object, $name );
	}

	public function get_parent_category_name( $object, $name ) {
		if ( $object->parent && ( $parent = get_term( $object->parent, 'job_listing_category' ) ) ) {
			return $this->get_parent_category_name( $parent, "{$parent->name} &#9656; {$name}" );
		}

		return $name;
	}

	public static function terms_dropdown( $args = '' ) {
		$defaults = array(
			'orderby'         => 'id',
			'order'           => 'ASC',
			'show_count'      => 0,
			'hide_empty'      => 1,
			'child_of'        => 0,
			'exclude'         => '',
			'echo'            => 1,
			'selected'        => 0,
			'hierarchical'    => 0,
			'name'            => 'cat',
			'id'              => '',
			'class'           => 'job-manager-category-dropdown ' . ( is_rtl() ? 'chosen-rtl' : '' ),
			'depth'           => 0,
			'taxonomy'        => 'job_listing_category',
			'value'           => 'id',
			'multiple'        => true,
			'show_option_all' => false,
			'placeholder'     => __( 'Choose a category&hellip;', 'my-listing' ),
			'no_results_text' => __( 'No results match', 'my-listing' ),
			'multiple_text'   => __( 'Select Some Options', 'my-listing' ),
			'meta_query'      => [],
		);

		$r = wp_parse_args( $args, $defaults );

		if ( ! isset( $r['pad_counts'] ) && $r['show_count'] && $r['hierarchical'] ) {
			$r['pad_counts'] = true;
		}

		/** This filter is documented in wp-job-manager.php */
		$r['lang'] = apply_filters( 'wpjm_lang', null );

		extract( $r );


		$categories = get_terms( $taxonomy, array(
			'orderby'         => $r['orderby'],
			'order'           => $r['order'],
			'hide_empty'      => $r['hide_empty'],
			'child_of'        => $r['child_of'],
			'exclude'         => $r['exclude'],
			'hierarchical'    => $r['hierarchical'],
			'meta_query'      => $r['meta_query'],
		));

		$name       = esc_attr( $name );
		$class      = esc_attr( $class );
		$id         = $id ? esc_attr( $id ) : $name;

		$output = "<select name='" . esc_attr( $name ) . "[]' id='" . esc_attr( $id ) . "' class='" . esc_attr( $class ) . "' " . ( $multiple ? "multiple='multiple'" : '' ) . " data-placeholder='" . esc_attr( $placeholder ) . "' data-no_results_text='" . esc_attr( $no_results_text ) . "' data-multiple_text='" . esc_attr( $multiple_text ) . "'>\n";

		if ( $show_option_all ) {
			$output .= '<option value="">' . esc_html( $show_option_all ) . '</option>';
		}

		if ( ! empty( $categories ) ) {
			include_once( JOB_MANAGER_PLUGIN_DIR . '/includes/class-wp-job-manager-category-walker.php' );

			$walker = new WP_Job_Manager_Category_Walker;

			if ( $hierarchical ) {
				$depth = $r['depth'];  // Walk the full depth.
			} else {
				$depth = -1; // Flat.
			}

			$output .= $walker->walk( $categories, $depth, $r );
		}

		$output .= "</select>\n";

		if ( $echo ) {
			echo $output;
		}

		return $output;
	}
}

new CASE27_WP_Job_Manager_Integration;