<?php
/*
 * Helper methods.
 */

class CASE27_Helpers {

	protected static $instance = null;

	public static function get_instance()
	{
		if (!isset(self::$instance)) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public static function defaults() {
		return [
			'category' => [
				'icon' => 'mi bookmark_border',
				'color' => c27()->get_setting('general_brand_color', '#f24286'),
				'text_color' => '#fff',
			],
		];
	}

    /*
     * Get theme template path, with the given $path appended to it.
     */
	public function template_path($path)
	{
		return get_template_directory() . "/$path";
	}


    /*
     * Get theme template uri, with the given $uri appended to it.
     */
	public function template_uri($uri = '')
	{
		return get_template_directory_uri() . "/$uri";
	}


    /*
     * URI to asset folder.
     */
	public function asset($asset)
	{
		return $this->template_uri("assets/$asset");
	}


    /*
     * URI to images folder.
     */
	public function image($image)
	{
		return $this->asset("images/$image");
	}


    /*
     * Retrieve the featured_image url for the given post, on the given size.
     */
	public function featured_image($postID, $size = 'large' )
	{
		$image = wp_get_attachment_image_src( get_post_thumbnail_id( $postID ), $size );

		if (!$image) return false;

		return array_shift($image);
	}


    /*
     * Get post terms from the given taxonomy.
     */
	public function get_terms($postID, $taxonomy = 'category')
	{
		$raw_terms = (array) wp_get_post_terms( $postID, $taxonomy );

		$terms = array();

		if (isset($raw_terms['errors']) && !empty($raw_terms['errors'])) {
			return $terms;
		}

		foreach ($raw_terms as $raw_term) {
			$terms[] = array(
				'name' => $raw_term->name,
				'link' => get_term_link($raw_term)
			);
		}

		return $terms;
	}


    /*
     * Get the 'likes' status for the given post.
     */
	public function get_likes($postID)
	{
		return (object) array(
			'count' => (int) get_post_meta($postID, 'c27-like-count', true),
			'is_liked' => isset($_COOKIE["c27-post-$postID"]) && $_COOKIE["c27-post-$postID"] == 'liked',
		);
	}


    /*
     * Helper method to get the youtube video ID from the given $videoURL.
     */
	public function get_youtube_video_id($videoURL)
	{
		$parts = parse_url($videoURL);

		if(isset($parts['query'])) {
			parse_str($parts['query'], $segment);
			if(isset($segment['v'])){
				return $segment['v'];
			}else if(isset($segment['vi'])){
				return $segment['vi'];
			}
		}

		if(isset($parts['path'])) {
			$path = explode('/', trim($parts['path'], '/'));
			return $path[count($path) - 1];
		}

		return false;
	}


    /*
     * Debugging helper for displaying data in a formatted way.
     */
	public function dump() {
		echo "<pre>";

		array_map(function($x) {
			var_dump($x);
			echo "<br>";
		}, func_get_args());

		echo "</pre>";
	}


    /*
     * Set a global variable.
     */
	public function set_global($key, $value)
	{
		return $GLOBALS[$key] = $value;
	}


    /*
     * Get a global variable.
     */
	public function get_global($key)
	{
		return $GLOBALS[$key];
	}


    /*
     * Get the latest sticky posts, on the given amount.
     */
	public function latest_sticky_posts($amount = 5) {

		// Get sticky posts ids
		$sticky = get_option( 'sticky_posts' );

		// Sort with the newest posts first
		rsort( $sticky );

		// Get a subset of the newest sticky posts
		$sticky = array_slice( $sticky, 0, $amount );

		if (empty($sticky)) return false;

		return new WP_Query( array( 'post__in' => $sticky, 'ignore_sticky_posts' => 1 ) );
	}


    /*
     * Get the ID of the latest sticky post.
     */
	public function latest_sticky_post_id() {
		// Get sticky posts ids
		$sticky = get_option( 'sticky_posts' );

		// Sort with the newest posts first
		rsort( $sticky );

		return isset($sticky[0]) ? $sticky[0] : false;
	}


    /*
     * Print the post excerpt, limiting it to a given number of characters.
     */
	function the_excerpt($charlength, $after = "&hellip;") {
		$excerpt = get_the_excerpt();
		$charlength++;

		if ( mb_strlen( $excerpt ) > $charlength ) {
			$subex = mb_substr( $excerpt, 0, $charlength - 5 );
			$exwords = explode( ' ', $subex );
			$excut = - ( mb_strlen( $exwords[ count( $exwords ) - 1 ] ) ) - 1;
			if ( $excut < 0 ) {
				echo mb_substr( $subex, 0, $excut );
			} else {
				echo $subex;
			}
			echo $after;
		} else {
			echo $excerpt;
		}
	}

	public function the_text_excerpt($text, $charlength, $after = "&hellip;") {
		$charlength++;

		if ( mb_strlen( $text ) > $charlength ) {
			$subex = mb_substr( $text, 0, $charlength - 5 );
			$exwords = explode( ' ', $subex );
			$excut = - ( mb_strlen( $exwords[ count( $exwords ) - 1 ] ) ) - 1;
			if ( $excut < 0 ) {
				echo mb_substr( $subex, 0, $excut );
			} else {
				echo $subex;
			}
			echo $after;
		} else {
			echo $text;
		}
	}

	public function merge_options($defaults, $options)
	{
		return array_replace_recursive($defaults, $options);
	}

	public function get_partial($template, $data = [])
	{
		if (!locate_template("partials/{$template}.php")) return;

		require locate_template("partials/{$template}.php");
	}

	public function get_section($template, $data = [])
	{
		if (!locate_template("sections/{$template}.php")) return;

		require locate_template("sections/{$template}.php");
	}

	public function get_job_listing_type($postID)
	{
		return get_post_meta($postID, '_case27_listing_type', true);
	}

	public function get_listing_type_options($listing_type, $options = ['fields', 'single', 'result', 'search', 'settings'])
	{
		$return_data = [];

		$listing_type = get_posts([
			'name' => $listing_type,
			'post_type' => 'case27_listing_type',
			'post_status' => 'publish',
			]);

		if (!$listing_type) {
			return false;
		}

		foreach ($options as $option) {
			if ($option == 'fields') {
				$return_data['fields'] = unserialize(get_post_meta($listing_type[0]->ID, 'case27_listing_type_fields', true));
			}

			if ($option == 'single') {
				$return_data['single'] = unserialize(get_post_meta($listing_type[0]->ID, 'case27_listing_type_single_page_options', true));
			}

			if ($option == 'result') {
				$return_data['result'] = unserialize(get_post_meta($listing_type[0]->ID, 'case27_listing_type_result_template', true));
			}

			if ($option == 'search') {
				$return_data['search'] = unserialize(get_post_meta($listing_type[0]->ID, 'case27_listing_type_search_page', true));
			}

			if ($option == 'settings') {
				$return_data['settings'] = unserialize(get_post_meta($listing_type[0]->ID, 'case27_listing_type_settings_page', true));
			}
		}

		return $return_data;
	}


	public function get_terms_dropdown_array($args = [], $key = 'term_id', $value = 'name')
	{
		$options = [];
		$terms = get_terms($args);

		if (is_wp_error($terms)) {
			return [];
		}

		foreach ((array) $terms as $term) {
			$options[$term->{$key}] = $term->{$value};
		}

		return $options;
	}


	public function get_posts_dropdown_array($args = [], $key = 'ID', $value = 'post_title')
	{
		$options = [];
		$posts = get_posts($args);

		foreach ((array) $posts as $term) {
			$options[$term->{$key}] = $term->{$value};
		}

		return $options;
	}


	public function get_readable_text_color( $hex, $light = '#fff', $dark = '#333' ) {
		$hex = str_replace( '#', '', $hex );
		if ( strlen( $hex ) == 3 ) {
			$hex = str_repeat( substr( $hex, 0, 1 ), 2 ) . str_repeat( substr( $hex, 1, 1 ), 2 ) . str_repeat( substr( $hex, 2, 1), 2 );
		}

		$color_parts = str_split( $hex, 2 );

		$brightness = ( hexdec( $color_parts[0] ) * 0.299 ) + ( hexdec( $color_parts[1] ) * 0.587 ) + ( hexdec( $color_parts[2] ) * 0.114 );

		if ( $brightness > 128 ) {
			return $dark; // Dark Color, Light Background.
		} else {
			return $light; // Light Color, Dark Background.
		}
	}

	public function get_icon_markup($icon_string)
	{
		// For icon fonts that require the icon name to be the contents of the <i> tag,
		// provide a string that can be exploded into two parts by '://', and use the
		// first part as the tag's class name, and the second part as the contents
		// of the tag. Example: material-icons://view_headline
		if (strpos($icon_string, '://') !== false) {
			$icon_arr = explode('://', $icon_string);

			return "<i class=\"{$icon_arr[0]}\">{$icon_arr[1]}</i>";
		}

		return "<i class=\"{$icon_string}\"></i>";
	}


	public function get_setting($setting, $default = '')
	{
		return function_exists('get_field') && get_field($setting, 'option') !== null ? get_field($setting, 'option') : $default;
	}


	public function get_site_logo()
	{
		if ($logo_obj = c27()->get_setting('general_site_logo')) {
			return $logo_obj['sizes']['medium'];
		}

		return '';
	}


	public function upload_file($file, $allowed_mime_types = [])
	{
		include_once( ABSPATH . 'wp-admin/includes/file.php' );
		include_once( ABSPATH . 'wp-admin/includes/media.php' );
		include_once( ABSPATH . 'wp-admin/includes/image.php' );

		$uploaded_file = new stdClass();

		if ( ! in_array( $file['type'], $allowed_mime_types ) ) {
			return new WP_Error( 'upload', sprintf( __( 'Uploaded files need to be one of the following file types: %s', 'my-listing' ), implode( ', ', array_keys( $allowed_mime_types ) ) ) );
		}

		$upload = wp_handle_upload($file, ['test_form' => false]);

		if ( ! empty( $upload['error'] ) ) {
			return new WP_Error( 'upload', $upload['error'] );
		}

		$wp_filetype = wp_check_filetype($upload['file']);
		$attach_id = wp_insert_attachment([
			'post_mime_type' => $wp_filetype['type'],
			'post_title' => sanitize_file_name($upload['file']),
			'post_content' => '',
			'post_status' => 'inherit'
			], $upload['file']);

		$attach_data = wp_generate_attachment_metadata($attach_id, $upload['file']);
		wp_update_attachment_metadata( $attach_id, $attach_data );

		return $attach_id;
	}

	public function get_gradients()
	{
		return [
	    		'gradient1' => ['from' => '#7dd2c7', 'to' => '#f04786'],
				'gradient2' => ['from' => '#71d68b', 'to' => '#00af9c'],
				'gradient3' => ['from' => '#FF5F6D', 'to' => '#FFC371'],
				'gradient4' => ['from' => '#EECDA3', 'to' => '#EF629F'],
				'gradient5' => ['from' => '#114357', 'to' => '#F29492'],
				'gradient6' => ['from' => '#52EDC7', 'to' => '#F29492'],
				'gradient7' => ['from' => '#C644FC', 'to' => '#5856D6'],
	    	];
	}

	public function get_listing_short_address($listing)
	{
		if (!isset($listing->_job_location) || !$listing->_job_location) return false;

		$parts = explode(',', $listing->_job_location);

		return $parts[0];
	}

	public function get_map_skins() {
		return [
			'skin1' => __( 'Skin 1', 'my-listing' ),
			'skin2' => __( 'Skin 2', 'my-listing' ),
			'skin3' => __( 'Skin 3', 'my-listing' ),
			'skin4' => __( 'Skin 4', 'my-listing' ),
			'skin5' => __( 'Skin 5', 'my-listing' ),
			'skin6' => __( 'Skin 6', 'my-listing' ),
			'skin7' => __( 'Skin 7', 'my-listing' ),
			'skin8' => __( 'Skin 8', 'my-listing' ),
			'skin9' => __( 'Skin 9', 'my-listing' ),
			'skin10' => __( 'Skin 10', 'my-listing' ),
			'skin11' => __( 'Skin 11', 'my-listing' ),
		];
	}

	public function new_admin_page( $type = 'menu', $args = [] ) {
		if ( ! in_array( $type, [ 'menu', 'submenu', 'theme' ] ) ) return;

		call_user_func_array('add_' . $type . '_page', $args);
	}

	public function hexToRgb( $hex, $alpha = 1 ) {
		$rgb = [];

		if ( strpos( $hex, 'rgb' ) !== false ) {
			$hex = str_replace( ['rgba', 'rgb', '(', ')', ' '], '', $hex );
			$hexArr = explode( ',', $hex );

			$rgb['r'] = isset( $hexArr[0] ) ? absint( $hexArr[0] ) : 0;
			$rgb['g'] = isset( $hexArr[1] ) ? absint( $hexArr[1] ) : 0;
			$rgb['b'] = isset( $hexArr[2] ) ? absint( $hexArr[2] ) : 0;
			$rgb['a'] = isset( $hexArr[3] ) ? (float) $hexArr[3] : 1;

			return $rgb;
		}

		$hex      = str_replace( '#', '', $hex );
		$length   = strlen( $hex );
		$rgb['r'] = hexdec( $length == 6 ? substr( $hex, 0, 2 ) : ( $length == 3 ? str_repeat( substr( $hex, 0, 1 ), 2 ) : 0 ) );
		$rgb['g'] = hexdec( $length == 6 ? substr( $hex, 2, 2 ) : ( $length == 3 ? str_repeat( substr( $hex, 1, 1 ), 2 ) : 0 ) );
		$rgb['b'] = hexdec( $length == 6 ? substr( $hex, 4, 2 ) : ( $length == 3 ? str_repeat( substr( $hex, 2, 1 ), 2 ) : 0 ) );
		$rgb['a'] = $alpha;

		return $rgb;
	}

	public function getVideoEmbedUrl( $url )
	{
		// Check if youtube
		$rx = '~^(?:https?://)?(?:www\.)?(?:youtube\.com|youtu\.be)/watch\?v=(?<id>[^&]+)~x';
		preg_match($rx, $url, $matches);
		if (isset($matches['id']) && trim($matches['id']) != "") {
			return ['url' => "https://www.youtube.com/embed/{$matches['id']}?origin=*", 'type' => 'external', 'service' => 'youtube', 'video_id' => $matches['id']];
		}

		// Check if vimeo
		$rx = "/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/([a-z]*\/)*(?<id>[0-9]{6,11})[?]?.*/";
		preg_match($rx, $url, $matches);
		if (isset($matches['id']) && trim($matches['id']) != "") {
			return ['url' => "https://player.vimeo.com/video/{$matches['id']}?api=1&player_id=".$matches['id'], 'type' => 'external', 'service' => 'vimeo', 'video_id' => $matches['id']];
		}

		// Check if dailymotion
		$rx = "/^.+dailymotion.com\/(video|hub)\/(?<id>[^_]+)[^#]*(#video=(?<id2>[^_&]+))?/";
		preg_match($rx, $url, $matches);
		if (isset($matches['id']) && trim($matches['id']) != "") {
			return ['url' => "https://www.dailymotion.com/embed/video/{$matches['id']}", 'type' => 'external', 'service' => 'dailymotion', 'video_id'=>$matches['id']];
		}

		return false;
	}
}

/*
 * Create c27() shorthand method to call CASE27_Helpers class methods.
 */
function c27( $className = null ) {
	if ( $className && class_exists( "CASE27_{$className}" ) ) {
		if ( ( $cls = "CASE27_{$className}" ) && method_exists( $cls, 'instance' ) ) {
			return $cls::instance();
		}
	}

	return CASE27_Helpers::get_instance();
}

/*
 * Create dump() shorthand to call c27()->dump($value).
 */
if (!function_exists('dump')) {
	function dump() {
		foreach (func_get_args() as $arg) {
			c27()->dump($arg);
		}
	}
}

if (!function_exists('dd')) {
	function dd() {
		foreach (func_get_args() as $arg) {
			c27()->dump($arg);
		}

		die;
	}
}

