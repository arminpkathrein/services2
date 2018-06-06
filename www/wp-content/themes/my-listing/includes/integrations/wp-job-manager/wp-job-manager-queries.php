<?php
/**
 * WP Job Manager Queries.
 */

class CASE27_WP_Job_Manager_Queries extends CASE27_Ajax {

	protected static $_instance = null;

	public static function instance()
	{
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	public function __construct()
	{
		$this->register_action('get_listing_quick_view');
		$this->register_action('get_listings');
		$this->register_action('get_listings_by_taxonomy');
		$this->register_action('get_related_listings_by_id');
		$this->register_action('get_listings_by_author');
		$this->register_action('get_listing_type_options_by_id');

		parent::__construct();
	}

	public function get_related_listings_by_id()
	{
		check_ajax_referer( 'c27_ajax_nonce', 'security' );

		if (!isset($_POST['listing_id']) || !$_POST['listing_id']) return;

		$result = [];
		$listing_id = absint((int) $_POST['listing_id']);
		$page = absint( isset($_POST['page']) ? $_POST['page'] : 0 );
		$per_page = absint( isset($_POST['per_page']) ? $_POST['per_page'] : 9 );
		$listing_type = sanitize_text_field(isset($_POST['listing_type']) ? $_POST['listing_type'] : '');

		$meta_query = [[
			'key' => '_related_listing',
			'value' => $listing_id
			]];

		if ($listing_type) {
			$meta_query[] = [
				'key' => '_case27_listing_type',
				'value' => $listing_type,
			];
		}

		$args = [
			'order' => sanitize_text_field( isset($_POST['order']) ? $_POST['order'] : 'DESC' ),
			'offset' => $page * $per_page,
			'orderby' => sanitize_text_field( isset($_POST['orderby']) ? $_POST['orderby'] : 'date' ),
			'posts_per_page' => $per_page,
			'meta_query' => $meta_query,
		];

		$listings = self::get_job_listings($args);

		ob_start();

		$result['found_jobs'] = false;
		// $result['args'] = $args;
		// $result['page'] = $page;
		// $result['sql'] = $listings->request;

		if ( $listings->have_posts() ) : $result['found_jobs'] = true; ?>

			<?php while ( $listings->have_posts() ) : $listings->the_post(); ?>
				<?php global $post; $post->c27_options__wrap_in = 'col-md-4 col-sm-6 col-xs-12 reveal'; ?>

				<?php get_job_manager_template_part( 'content', 'job_listing' ); ?>

			<?php endwhile; ?>

		<?php else : ?>

			<?php get_job_manager_template_part( 'content', 'no-jobs-found' ); ?>

		<?php endif;

		$result['html'] = ob_get_clean();

		// Generate pagination
		$result['pagination'] = get_job_listing_pagination( $listings->max_num_pages, ($page + 1) );

		$result['max_num_pages'] = $listings->max_num_pages;

		$result['found_posts'] = $listings->found_posts;

		wp_send_json( $result );
	}

	public function get_listings_by_author()
	{
		check_ajax_referer( 'c27_ajax_nonce', 'security' );

		if ( empty( $_POST['auth_id'] )) {
			return false;
		}

		$result = [];
		$auth_id = absint( $_POST['auth_id'] );
		$page = absint( isset($_POST['page']) ? $_POST['page'] : 0 );
		$per_page = absint( isset($_POST['per_page']) ? $_POST['per_page'] : 9 );

		$args = [
			'order' => sanitize_text_field( isset($_POST['order']) ? $_POST['order'] : 'DESC' ),
			'offset' => $page * $per_page,
			'orderby' => sanitize_text_field( isset($_POST['orderby']) ? $_POST['orderby'] : 'date' ),
			'posts_per_page' => $per_page,
			'author' => $auth_id,
		];

		$listings = self::get_job_listings($args);

		ob_start();

		$result['found_jobs'] = false;
		// $result['args'] = $args;
		// $result['page'] = $page;
		// $result['sql'] = $listings->request;

		if ( $listings->have_posts() ) : $result['found_jobs'] = true; $counter = 0; ?>

			<?php while ( $listings->have_posts() ) : $listings->the_post(); $counter++; ?>

				<?php global $post; $post->c27_options__wrap_in = 'col-md-4 col-sm-6 col-xs-12 grid-item'; ?>

				<?php get_job_manager_template_part( 'content', 'job_listing' ); ?>

			<?php endwhile; ?>

		<?php else : ?>

			<?php get_job_manager_template_part( 'content', 'no-jobs-found' ); ?>

		<?php endif;

		$result['html'] = ob_get_clean();

		// Generate pagination
		$result['pagination'] = get_job_listing_pagination( $listings->max_num_pages, ($page + 1) );

		$result['max_num_pages'] = $listings->max_num_pages;

		$result['found_posts'] = $listings->found_posts;

		wp_send_json( $result );
	}


	public function get_listing_type_options_by_id()
	{
		$postid = isset($_GET['postid']) ? $_GET['postid'] : false;

		if ( ! $postid || ! current_user_can( 'edit_post', $postid ) ) {
			return false;
		}

		return $this->json([
			'fields' => [
				'available' => $GLOBALS['case27_listing_fields']['job'],
				'used' => unserialize(get_post_meta($postid, 'case27_listing_type_fields', true)),
				],
			'single' => unserialize(get_post_meta($postid, 'case27_listing_type_single_page_options', true)),
			'result' => unserialize(get_post_meta($postid, 'case27_listing_type_result_template', true)),
			'search' => unserialize(get_post_meta($postid, 'case27_listing_type_search_page', true)),
			'settings' => unserialize(get_post_meta($postid, 'case27_listing_type_settings_page', true)),
		]);
	}

	public function get_listings()
	{
		check_ajax_referer( 'c27_ajax_nonce', 'security' );

		global $wp_post_types, $wpdb;

		if (!isset($_POST['form_data']) || !is_array($_POST['form_data'])) return;
		if (!isset($_POST['listing_type']) || !$_POST['listing_type']) return;

		$form_data = $_POST['form_data'];
		$listing_type = $_POST['listing_type'];
		$page = absint( isset($form_data['page']) ? $form_data['page'] : 0 );
		$per_page = absint( isset($form_data['per_page']) ? $form_data['per_page'] : c27()->get_setting('general_explore_listings_per_page', 9));
		$orderby = sanitize_text_field( isset($form_data['orderby']) ? $form_data['orderby'] : 'date' );
		$promoted_args = false;
		$args = [
			'order' => sanitize_text_field( isset($form_data['order']) ? $form_data['order'] : 'DESC' ),
			'offset' => $page * $per_page,
			'orderby' => $orderby,
			'posts_per_page' => $per_page,
			'tax_query' => [],
			'meta_query' => [],
			'__ignore_cache' => false,
		];

		if ( ! ( $listing_type_obj = ( get_page_by_path( $listing_type, OBJECT, 'case27_listing_type' ) ) ) ) {
			return false;
		}

		$listType = new CASE27\Integrations\ListingTypes\ListingType( $listing_type_obj );

		// dd($listType->get_field('multiselect-field'));

		$tax_query_operator = 'all' === get_option( 'job_manager_category_filter_type', 'all' ) ? 'AND' : 'IN';

		if ($orderby && $orderby[0] === '_') {
			$args['meta_query']['c27_orderby_clause'] = [
				'key' => $orderby,
				'compare' => 'EXISTS',
				'type' => 'DECIMAL(10, 2)',
			];
			$args['orderby'] = 'c27_orderby_clause';

			if ($orderby == '_case27_average_rating') {
				$args['__ignore_cache'] = true;
			}
		}

		$search_facets = (array) c27()->get_listing_type_options(
			$listing_type,
			['search']
		)['search']['advanced']['facets'];

		if (!$search_facets) return;

		// Make sure we're only querying listings of the requested listing type.
		$args['meta_query'][] = [
			'key'     => '_case27_listing_type',
			'value'   =>  $listing_type,
			'compare' => '='
		];

		foreach ($search_facets as $facet) {
			// wp-search -> search_keywords
			// location -> search_location
			// text -> facet.show_field
			// proximity -> proximity
			// date -> show_field
			// range -> show_field
			// dropdown -> show_field
			// checkboxes -> show_field

			if ($facet['type'] == 'wp-search' && isset($form_data['search_keywords']) && $form_data['search_keywords']) {
				// dd($form_data['search_keywords']);
				$args['search_keywords'] = sanitize_text_field( stripslashes( $form_data['search_keywords'] ) );
			}

			if ($facet['type'] == 'location' && isset($form_data['search_location']) && $form_data['search_location']) {
				$args['search_location'] = sanitize_text_field( stripslashes( $form_data['search_location'] ) );
			}

			if ($facet['type'] == 'text' && isset($form_data[$facet['show_field']]) && $form_data[$facet['show_field']]) {
				$args['meta_query'][] = [
					'key'     => "_{$facet['show_field']}",
					'value'   => sanitize_text_field( stripslashes( $form_data[$facet['show_field']] ) ),
					'compare' => 'LIKE',
				];
			}

			if ($facet['type'] == 'proximity' && isset($form_data['proximity']) && isset($form_data['search_location_lat']) && isset($form_data['search_location_lng'])) {
				$proximity = absint( $form_data['proximity'] );
				$location = isset($form_data['search_location']) ? sanitize_text_field( stripslashes( $form_data['search_location'] ) ) : false;
				$lat = (float) $form_data['search_location_lat'];
				$lng = (float) $form_data['search_location_lng'];
				$units = isset($form_data['proximity_units']) && $form_data['proximity_units'] == 'mi' ? 'mi' : 'km';

				if ( $lat && $lng && $proximity && $location ) {
					// dump($lat, $lng, $proximity);

					$earth_radius = $units == 'mi' ? 3959 : 6371;

					$sql = $wpdb->prepare("
						SELECT $wpdb->posts.ID,
							( %s * acos(
								cos( radians(%s) ) *
								cos( radians( latitude.meta_value ) ) *
								cos( radians( longitude.meta_value ) - radians(%s) ) +
								sin( radians(%s) ) *
								sin( radians( latitude.meta_value ) )
							) )
							AS distance, latitude.meta_value AS latitude, longitude.meta_value AS longitude
							FROM $wpdb->posts
							INNER JOIN $wpdb->postmeta
								AS latitude
								ON $wpdb->posts.ID = latitude.post_id
							INNER JOIN $wpdb->postmeta
								AS longitude
								ON $wpdb->posts.ID = longitude.post_id
							WHERE 1=1
								AND ($wpdb->posts.post_status = 'publish' )
								AND latitude.meta_key='geolocation_lat'
								AND longitude.meta_key='geolocation_long'
							HAVING distance < %s
							ORDER BY $wpdb->posts.menu_order ASC, distance ASC",
						$earth_radius,
						$lat,
						$lng,
						$lat,
						$proximity
					);

					// dump($sql);

					$post_ids = (array) $wpdb->get_results( $sql, OBJECT_K );

					if (empty($post_ids)) $post_ids = ['none'];

					$args['post__in'] = array_keys( (array) $post_ids );

					// Remove search_location filter when using proximity filter.
					$args['search_location'] = '';
				}
			}

			if ($facet['type'] == 'date') {
				$type = 'exact';
				$format = 'ymd';

				foreach ($facet['options'] as $option) {
					if ($option['name'] == 'type') $type = $option['value'];
					if ($option['name'] == 'format') $format = $option['value'];
				}

				// Exact date search.
				if ($type == 'exact' && isset($form_data[$facet['show_field']]) && $form_data[$facet['show_field']]) {
					// Y-m-d format search.
					if ($format == 'ymd') {
						$date = date('Y-m-d', strtotime( $form_data[$facet['show_field']] ));
						$compare = '=';
					}

					// Year search. The year is converted to a date format, and the query instead runs a 'BETWEEN' comparison,
					// to include the requested year from January 01 to December 31.
					if ($format == 'year') {
						$date = [
							date('Y-01-01', strtotime($form_data[$facet['show_field']] . '-01-01' )),
							date('Y-12-31', strtotime($form_data[$facet['show_field']] . '-12-31')),
						];
						$compare = 'BETWEEN';
					}

					$args['meta_query'][] = [
						'key'     => "_{$facet['show_field']}",
						'value'   => $date,
						'compare' => $compare,
						'type' => 'DATE',
					];
				}

				// Range date search.
				if ($type == 'range') {
					$date_from = false;
					$date_to = false;
					$values = [];

					if (isset($form_data["{$facet['show_field']}_from"]) && $form_data["{$facet['show_field']}_from"]) {
						$date_from = $values['date_from'] = date(($format == 'ymd' ? 'Y-m-d' : 'Y'), strtotime( $form_data["{$facet['show_field']}_from"] ));

						if ($format == 'ymd') {
							$date_from = $values['date_from'] = date('Y-m-d', strtotime($form_data["{$facet['show_field']}_from"]));
						}

						if ($format == 'year') {
							$date_from = $values['date_from'] = date('Y-m-d', strtotime($form_data["{$facet['show_field']}_from"] . '-01-01'));
						}
					}

					if (isset($form_data["{$facet['show_field']}_to"]) && $form_data["{$facet['show_field']}_to"]) {
						if ($format == 'ymd') {
							$date_to = $values['date_to'] = date('Y-m-d', strtotime($form_data["{$facet['show_field']}_to"]));
						}

						if ($format == 'year') {
							$date_to = $values['date_to'] = date('Y-m-d', strtotime($form_data["{$facet['show_field']}_to"] . '-12-31'));
						}
					}

					if (empty($values)) continue;
					if (count($values) == 1) $values = array_pop($values);

					$args['meta_query'][] = [
						'key'     => "_{$facet['show_field']}",
						'value'   => $values,
						'compare' => is_array($values) ? 'BETWEEN' : ($date_from ? '>' : '<'),
						'type' => 'DATE',
					];
				}
			}

			if ($facet['type'] == 'range' && isset($form_data[$facet['show_field']]) && $form_data[$facet['show_field']] && isset($form_data["{$facet['show_field']}_default"])) {
				$type = 'range';
				$range = $form_data[$facet['show_field']];
				$default_range = $form_data["{$facet['show_field']}_default"];

				// In case the range values include the maximum and minimum possible field values,
				// then skip, since the meta query is unnecessary, and would only make the query slower.
				if ($default_range == $range) continue;

				foreach ($facet['options'] as $option) {
					if ($option['name'] == 'type') $type = $option['value'];
				}

				if ($type == 'range' && strpos($range, '::') !== false) {
					$args['meta_query'][] = [
						'key'     => "_{$facet['show_field']}",
						'value'   => array_map('intval', explode('::', $range)),
						'compare' => 'BETWEEN',
						'type'    => 'NUMERIC',
					];
				}

				if ($type == 'simple') {
					$args['meta_query'][] = [
						'key'     => "_{$facet['show_field']}",
						'value'   => intval( $range ),
						'compare' => '<=',
						'type'    => 'NUMERIC',
					];
				}
			}

			if (($facet['type'] == 'dropdown' || $facet['type'] == 'checkboxes') && isset($form_data[$facet['show_field']]) && $form_data[$facet['show_field']]) {
				$dropdown_values = array_filter( array_map('sanitize_text_field', array_map('stripslashes', (array) $form_data[$facet['show_field']] ) ) );

				if (!$dropdown_values) continue;

				// Tax query.
				if (
					$listType->get_field( $facet[ 'show_field' ] ) &&
					! empty( $listType->get_field( $facet[ 'show_field' ] )['taxonomy'] ) &&
					taxonomy_exists( $listType->get_field( $facet[ 'show_field' ] )['taxonomy'] )
				) {
					$args['tax_query'][] = [
						'taxonomy' => $listType->get_field( $facet[ 'show_field' ] )['taxonomy'],
						'field' => 'slug',
						'terms' => $dropdown_values,
						'operator' => $tax_query_operator,
						'include_children' => $tax_query_operator !== 'AND',
					];

					continue;
				}

				// If the meta value is serialized.
				if ( $listType->get_field( $facet[ 'show_field' ] ) && $listType->get_field( $facet[ 'show_field' ] )['type'] == 'multiselect' ) {
					foreach ( $dropdown_values as $dropdown_value) {
						// dd(serialize( $dropdown_value ), serialize( [ 'opt1' => 'opt3', 'rtfg' => 4554563 ] ));
						$args['meta_query'][] = [
							'key'     => "_{$facet['show_field']}",
							'value'   => '"' . $dropdown_value . '"',
							'compare' => 'LIKE',
						];
					}

					continue;
				}

				$args['meta_query'][] = [
					'key'     => "_{$facet['show_field']}",
					'value'   => $dropdown_values,
					'compare' => 'IN',
				];
			}
		}

		if ( c27()->get_setting( 'promotions_enabled', false ) ) {
			$promoted_args = [
				'post_type' => 'job_listing',
				'post_status' => 'publish',
				'posts_per_page' => 1,
				'orderby' => 'rand',
				'meta_query' => [[
					'key'     => '_case27_listing_type',
					'value'   =>  $listing_type,
					'compare' => '='
				]],
			];
			$promoted_args['meta_query'][] = $this->promoted_only_clause();

			// $args['meta_query']['c27_promoted_clause'] = $this->promoted_first_clause();

			// $args['orderby'] = 'c27_promoted_clause_end_date ' . $args['orderby'];
		}

		$results = [];

		$post_type_label   = $wp_post_types['job_listing']->labels->name;

		ob_start();

		$promoted_listings = !empty( $promoted_args ) ? new WP_Query( $promoted_args ) : false;
		$promoted_ids = [];
		// dump($promoted_args, $promoted_listings->request);

		$result['found_jobs'] = false;
		$result['data'] = [];
		$listing_wrap = isset($_POST['listing_wrap']) && $_POST['listing_wrap'] ? sanitize_text_field($_POST['listing_wrap']) : '';

		if ( c27()->get_setting( 'promotions_enabled', false ) && $promoted_listings && $promoted_listings->have_posts() ) :
			while ( $promoted_listings->have_posts() ) : $promoted_listings->the_post();
				// dump(get_the_ID());
				global $post; $post->c27_options__wrap_in = $listing_wrap;
				get_job_manager_template_part( 'content', 'job_listing' );
				$result['data'][] = $post->_c27_marker_data;
				$promoted_ids[] = absint( get_the_ID() );
			endwhile; wp_reset_postdata();
		endif;

		$result['promoted_html'] = ob_get_clean();

		ob_start();

		$listings = self::get_job_listings($args);
		$listing_ids = [];

		// $result['args'] = $args;
		// $result['sql'] = $listings->request;

		if ( $listings->have_posts() ) : $result['found_jobs'] = true;
			while ( $listings->have_posts() ) : $listings->the_post();
				if ( absint( $listings->post_count ) > 3 && in_array( absint( get_the_ID() ), $promoted_ids ) ) {
					continue;
				}

				global $post; $post->c27_options__wrap_in = $listing_wrap; $post->_c27_show_promoted_badge = false;
				get_job_manager_template_part( 'content', 'job_listing' );
				$result['data'][] = $post->_c27_marker_data;
				$listing_ids[] = absint( get_the_ID() );
			endwhile;

			$result['listings_html'] = ob_get_clean();

			if ( absint( $listings->post_count ) <= 3 ) {
				$result['html'] = $result['listings_html'];
			} else {
				$result['html'] = $result['promoted_html'] . $result['listings_html'];
			}

			wp_reset_postdata();
		else:
			get_job_manager_template_part( 'content', 'no-jobs-found' );
			$result['html'] = ob_get_clean();
		endif;

		// $result['showing'] = array();

		// Generate 'showing' text
		$showing_types = array();
		$unmatched     = false;

		// Generate pagination
		$result['pagination'] = get_job_listing_pagination( $listings->max_num_pages, ($page + 1) );

		$result['showing'] = sprintf( __( '%d results', 'my-listing' ), $listings->found_posts);

		if ($listings->found_posts == 1) {
			$result['showing'] = __( 'One result', 'my-listing');
		}

		if ($listings->found_posts < 1) {
			$result['showing'] = __( 'No results', 'my-listing' );
		}

		$result['max_num_pages'] = $listings->max_num_pages;

		wp_send_json( $result );
	}

	public function get_listings_by_taxonomy()
	{
		check_ajax_referer( 'c27_ajax_nonce', 'security' );

		if ( empty( $_REQUEST['form_data'] ) || ! is_array( $_REQUEST['form_data'] ) ) {
			return false;
		}

		if ( empty( $_REQUEST['term'] ) ) {
			return false;
		}

		$taxonomy = ! empty( $_REQUEST['taxonomy'] ) ? sanitize_text_field( $_REQUEST['taxonomy'] ) : 'job_listing_category';

		if ( ! taxonomy_exists( $taxonomy ) ) {
			return false;
		}

		$form_data = $_REQUEST['form_data'];
		$page = absint( isset($form_data['page']) ? $form_data['page'] : 0 );
		$per_page = absint( isset($form_data['per_page']) ? $form_data['per_page'] : c27()->get_setting('general_explore_listings_per_page', 9));
		$term = sanitize_text_field( $_REQUEST['term'] );
		$args = [
			'order' => sanitize_text_field( isset($form_data['order']) ? $form_data['order'] : 'DESC' ),
			'offset' => $page * $per_page,
			'orderby' => sanitize_text_field( isset($form_data['orderby']) ? $form_data['orderby'] : 'date' ),
			'posts_per_page' => $per_page,
			'meta_query' => [],
			'tax_query' => [[
				'taxonomy' => $taxonomy,
				'field' => 'id',
				'terms' => $term,
			]],
		];

		/*if ( ! empty( $_REQUEST['listing_type'] ) ) {
			$args['meta_query'][] = [
				'key' => '_case27_listing_type',
				'value' => sanitize_text_field( $_REQUEST['listing_type'] ),
			];
		}*/

		ob_start();

		// dd($args);
		$listings = self::get_job_listings($args);

		$result['found_jobs'] = false;
		$result['data'] = [];
		$listing_wrap = isset($_POST['listing_wrap']) && $_POST['listing_wrap'] ? sanitize_text_field($_POST['listing_wrap']) : '';

		if ( $listings->have_posts() ) : $result['found_jobs'] = true;
			while ( $listings->have_posts() ) : $listings->the_post();
				global $post; $post->c27_options__wrap_in = $listing_wrap;
				get_job_manager_template_part( 'content', 'job_listing' );
				$result['data'][] = $post->_c27_marker_data;
			endwhile;
		else:
			get_job_manager_template_part( 'content', 'no-jobs-found' );
		endif;

		$result['html']    = ob_get_clean();
		// $result['showing'] = array();

		// Generate pagination
		$result['pagination'] = get_job_listing_pagination( $listings->max_num_pages, ($page + 1) );

		$result['showing'] = sprintf( __( '%d results', 'my-listing' ), $listings->found_posts);

		if ($listings->found_posts == 1) {
			$result['showing'] = __( 'One result', 'my-listing' );
		}

		if ($listings->found_posts < 1) {
			$result['showing'] = __( 'No results', 'my-listing' );
		}

		$result['max_num_pages'] = $listings->max_num_pages;

		wp_send_json( $result );
	}

	public static function get_job_listings($args = []) {
		global $wpdb, $job_manager_keyword;

		$args = wp_parse_args( $args, array(
			'search_location'   => '',
			'search_keywords'   => '',
			'search_categories' => array(),
			'job_types'         => array(),
			'offset'            => 0,
			'posts_per_page'    => 20,
			'orderby'           => 'date',
			'order'             => 'DESC',
			'featured'          => null,
			'filled'            => null,
			'fields'            => 'all',
			'post__in'          => [],
			'post__not_in'      => [],
			'meta_query'        => [],
			'tax_query'         => [],
			'author'            => null,
			'ignore_sticky_posts' => true,
			) );

		// dd($args);

		do_action( 'get_job_listings_init', $args );

		$post_status = false == get_option('job_manager_hide_expired_content', 1) ? ['publish', 'expired'] : 'publish';

		$query_args = array(
			'post_type'              => 'job_listing',
			'post_status'            => $post_status,
			'ignore_sticky_posts'    => $args['ignore_sticky_posts'],
			'offset'                 => absint( $args['offset'] ),
			'posts_per_page'         => intval( $args['posts_per_page'] ),
			'orderby'                => $args['orderby'],
			'order'                  => $args['order'],
			'tax_query'              => $args['tax_query'],
			'meta_query'             => $args['meta_query'],
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'cache_results'          => false,
			'fields'                 => $args['fields'],
			'author'                 => $args['author'],
		);

		// WPML workaround
		if ( ( strstr( $_SERVER['REQUEST_URI'], '/jm-ajax/' ) || ! empty( $_GET['jm-ajax'] ) ) && isset( $_POST['lang'] ) ) {
			do_action( 'wpml_switch_language', sanitize_text_field( $_POST['lang'] ) );
		}

		if ( $args['posts_per_page'] < 0 ) {
			$query_args['no_found_rows'] = true;
		}

		if ( ! empty( $args['search_location'] ) ) {
			$location_meta_keys = ['geolocation_formatted_address', '_job_location', 'geolocation_state_long'];
			$location_search    = ['relation' => 'OR'];
			foreach ( $location_meta_keys as $meta_key ) {
				$location_search[] = [
					'key'     => $meta_key,
					'value'   => $args['search_location'],
					'compare' => 'like'
				];
			}
			$query_args['meta_query'][] = $location_search;
		}

		if ( ! is_null( $args['featured'] ) ) {
			$query_args['meta_query'][] = array(
				'key'     => '_featured',
				'value'   => '1',
				'compare' => $args['featured'] ? '=' : '!='
			);
		}

		if ( ! is_null( $args['filled'] ) || 1 === absint( get_option( 'job_manager_hide_filled_positions' ) ) ) {
			$query_args['meta_query'][] = array(
				'key'     => '_filled',
				'value'   => '1',
				'compare' => $args['filled'] ? '=' : '!='
			);
		}

		if ( ! empty( $args['job_types'] ) ) {
			$query_args['tax_query'][] = array(
				'taxonomy' => 'job_listing_type',
				'field'    => 'slug',
				'terms'    => $args['job_types']
			);
		}

		if (!empty($args['post__in'])) {
			$query_args['post__in'] = $args['post__in'];
		}

		if (!empty($args['post__not_in'])) {
			$query_args['post__not_in'] = $args['post__not_in'];
		}

		if ( 'featured' === $args['orderby'] ) {
			$query_args['orderby'] = array(
				'menu_order' => 'ASC',
				'date'       => 'DESC'
			);
		}

		$job_manager_keyword = sanitize_text_field( $args['search_keywords'] );

		if ( ! empty( $job_manager_keyword ) && strlen( $job_manager_keyword ) >= apply_filters( 'job_manager_get_listings_keyword_length_threshold', 2 ) ) {
			$query_args['s'] = $job_manager_keyword;
			add_filter( 'posts_search', 'get_job_listings_keyword_search' );
		}

		$query_args = apply_filters( 'job_manager_get_listings', $query_args, $args );

		if ( empty( $query_args['meta_query'] ) ) {
			unset( $query_args['meta_query'] );
		}

		if ( empty( $query_args['tax_query'] ) ) {
			unset( $query_args['tax_query'] );
		}

		if ( ! $query_args['author'] ) {
			unset( $query_args['author'] );
		}

		/** This filter is documented in wp-job-manager.php */
		$query_args['lang'] = apply_filters( 'wpjm_lang', null );

		// Filter args
		$query_args = apply_filters( 'get_job_listings_query_args', $query_args, $args );

		// Generate hash
		$to_hash         = json_encode( $query_args ) . apply_filters( 'wpml_current_language', '' );
		$query_args_hash = 'jm_' . md5( $to_hash ) . WP_Job_Manager_Cache_Helper::get_transient_version( 'get_job_listings' );

		do_action( 'before_get_job_listings', $query_args, $args );

		// Cache results
		if ( apply_filters( 'get_job_listings_cache_results', true ) && (!isset($args['__ignore_cache']) || !$args['__ignore_cache']) ) {

			if ( false === ( $result = get_transient( $query_args_hash ) ) ) {
				$result = new WP_Query( $query_args );
				set_transient( $query_args_hash, $result, DAY_IN_SECONDS * 30 );
			}

			// random order is cached so shuffle them
			if ( $query_args[ 'orderby' ] == 'rand' ) {
				shuffle( $result->posts );
			}

		}
		else {
			$result = new WP_Query( $query_args );
		}

		do_action( 'after_get_job_listings', $query_args, $args );

		remove_filter( 'posts_search', 'get_job_listings_keyword_search' );

		return $result;
	}

	public function get_listing_quick_view() {
		if (!isset($_REQUEST['listing_id']) || !$_REQUEST['listing_id']) return;

		$listing = get_post(absint((int) $_REQUEST['listing_id']));

		if (!$listing || $listing->post_type !== 'job_listing') return;

		ob_start();

		c27()->get_partial('listing-quick-view', [
			'listing' => $listing,
			]);

		return $this->json([
			'html' => ob_get_clean(),
		]);
	}

	public function promoted_first_clause() {
		return [
			'relation' => 'OR',
			[
				'key' => '_case27_listing_promotion_end_date',
				'compare' => 'NOT EXISTS',
			],
			[
				'key' => '_case27_listing_promotion_end_date',
				'value' => date('Y-m-d H:i:s'),
				'compare' => '<',
				'type' => 'DATETIME',
			],
			[
				'relation' => 'AND',
				[
					'key' => '_case27_listing_promotion_start_date',
					'value' => date('Y-m-d H:i:s'),
					'compare' => '<=',
					'type' => 'DATETIME',
				],
				'c27_promoted_clause_end_date' => [
					'key' => '_case27_listing_promotion_end_date',
					'value' => date('Y-m-d H:i:s'),
					'compare' => '>=',
					'type' => 'DATETIME',
				],
			],
		];
	}

	public function promoted_only_clause() {
		return [
			'relation' => 'AND',
			[
				'key' => '_case27_listing_promotion_start_date',
				'value' => date('Y-m-d H:i:s'),
				'compare' => '<=',
				'type' => 'DATETIME',
			],
			[
				'key' => '_case27_listing_promotion_end_date',
				'value' => date('Y-m-d H:i:s'),
				'compare' => '>=',
				'type' => 'DATETIME',
			],
		];
	}

	public function hide_promoted_clause() {
		return [
			'relation' => 'OR',
			[
				'key' => '_case27_listing_promotion_start_date',
				'value' => date('Y-m-d H:i:s'),
				'compare' => '>',
				'type' => 'DATETIME',
			],
			[
				'key' => '_case27_listing_promotion_end_date',
				'value' => date('Y-m-d H:i:s'),
				'compare' => '<',
				'type' => 'DATETIME',
			],
			[
				'key' => '_case27_listing_promotion_end_date',
				'compare' => 'NOT EXISTS',
			],
		];
	}
}

new CASE27_WP_Job_Manager_Queries;