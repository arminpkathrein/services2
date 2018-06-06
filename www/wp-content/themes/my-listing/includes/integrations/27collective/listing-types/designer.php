<?php
/**
 * Adds custom functionality to the Admin panel.
 */

namespace CASE27\Integrations\ListingTypes;

class Designer {
    use \CASE27\Traits\Instantiatable;

    public $fields = [];

    public static $store = [];

	public function __construct()
	{
        if ( is_admin() ) {
            add_action( 'load-post.php',     array( $this, 'init_metabox' ) );
            add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );

            self::$store['listing-types'] = get_posts([
                'post_type' => 'case27_listing_type',
                'numberposts' => -1,
                ]);

            self::$store['taxonomies'] = (array) get_taxonomies( [
                'object_type' => [ 'job_listing' ],
                ], 'objects' );

            self::$store['mime-types'] = (array) get_allowed_mime_types();

            $this->include_files();
        }
	}


	public function init_metabox()
	{
        add_action( 'add_meta_boxes', array( $this, 'add_metabox'  )        );
        add_action( 'save_post',      array( $this, 'save_metabox' ), 10, 2 );
	}

	/**
     * Adds the meta box.
     */
    public function add_metabox() {
        add_meta_box(
            'case27-listing-type-options',
            __( 'Listing Type Options', 'my-listing' ),
            array( $this, 'render_metabox' ),
            'case27_listing_type',
            'advanced',
            'high'
        );
    }

    /**
     * Renders the meta box.
     */
    public function render_metabox( $post ) {
        // dump(get_post_meta($post->ID));

        // Add nonce for security and authentication.
        wp_nonce_field( 'custom_nonce_action', 'custom_nonce' );

        require_once CASE27_INTEGRATIONS_DIR . '/27collective/listing-types/views/metabox.php';
    }

    /**
     * Handles saving the meta box.
     */
    public function save_metabox( $post_id, $post ) {
        // Add nonce for security and authentication.
        $nonce_name   = isset( $_POST['custom_nonce'] ) ? $_POST['custom_nonce'] : '';
        $nonce_action = 'custom_nonce_action';

        // Check if nonce is set.
        if ( ! isset( $nonce_name ) ) {
            return;
        }

        // Check if nonce is valid.
        if ( ! wp_verify_nonce( $nonce_name, $nonce_action ) ) {
            return;
        }

        // Check if user has permissions to save data.
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Check if not an autosave.
        if ( wp_is_post_autosave( $post_id ) ) {
            return;
        }

        // Check if not a revision.
        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }

        // Fields TAB
        if (isset($_POST['case27_listing_type_fields'])) {
            $decoded_fields = json_decode(stripslashes($_POST['case27_listing_type_fields']), true);
            $updated_fields = [];

            foreach ((array) $decoded_fields as $i => $field) {
                $field['priority'] = ($i + 1);
                $updated_fields[$field['slug']] = (array) $field;
            }

            update_post_meta($post_id, 'case27_listing_type_fields', serialize($updated_fields));
        }

        // Single Page TAB
        if (isset($_POST['case27_listing_type_single_page_options'])) {
            $options = (array) json_decode(stripslashes($_POST['case27_listing_type_single_page_options']), true);

            foreach ($options['menu_items'] as $key => $menu_item) {
                if (!isset($menu_item['_id'])) {
                    $options['menu_items'][$key]['_id'] = uniqid('menu_item_id__');
                }
            }

            // dd($options);

            update_post_meta($post_id, 'case27_listing_type_single_page_options', serialize($options));
        }

        // Result Template TAB
        if (isset($_POST['case27_listing_type_result_template'])) {
            $result_template = (array) json_decode(stripslashes($_POST['case27_listing_type_result_template']), true);

            update_post_meta($post_id, 'case27_listing_type_result_template', serialize($result_template));
        }

        // Search Forms TAB
        if (isset($_POST['case27_listing_type_search_page'])) {
            $search_forms = (array) json_decode(stripslashes($_POST['case27_listing_type_search_page']), true);

            foreach ($search_forms['advanced']['facets'] as $key => $facet) {
                if (!isset($facet['_id'])) {
                    $search_forms['advanced']['facets'][$key]['_id'] = uniqid('facetid__');
                }
            }

            update_post_meta($post_id, 'case27_listing_type_search_page', serialize($search_forms));
        }

        // Settings TAB
        if (isset($_POST['case27_listing_type_settings_page'])) {
            $settings_page = (array) json_decode(stripslashes($_POST['case27_listing_type_settings_page']), true);

            update_post_meta($post_id, 'case27_listing_type_settings_page', serialize($settings_page));
        }
    }

    public function profile_layout_blocks() {
        return require_once trailingslashit( CASE27_INTEGRATIONS_DIR ) . '27collective/listing-types/blueprints/profile-layout-blocks.php';
    }

    public function include_files() {
        require_once trailingslashit( CASE27_INTEGRATIONS_DIR ) . '27collective/listing-types/fields/field.php';

        $this->fields = [
            'text' => 'TextField',
            'textarea' => 'TextAreaField',
            'wp-editor' => 'WPEditorField',
            'url' => 'URLField',
            'date' => 'DateField',
            'file' => 'FileField',
            'email' => 'EmailField',
            'radio' => 'RadioField',
            'links' => 'LinksField',
            'number' => 'NumberField',
            'select' => 'SelectField',
            'checkbox' => 'CheckboxField',
            'password' => 'PasswordField',
            'location' => 'LocationField',
            'work-hours' => 'WorkHoursField',
            'multiselect' => 'MultiSelectField',
            'term-select' => 'TermSelectField',
            'form-heading' => 'FormHeadingField',
            'term-checklist' => 'TermChecklistField',
            'related-listing' => 'RelatedListingField',
            'select-products' => 'SelectProductsField',
            'term-multiselect' => 'TermMultiselectField',
        ];

        foreach ($this->fields as $field_slug => $field_classname) {
            $namespaced_classname = sprintf( '%s\Fields\%s', __NAMESPACE__, $field_classname );
            require_once trailingslashit( CASE27_INTEGRATIONS_DIR ) . "27collective/listing-types/fields/{$field_slug}.php";
            $this->fields[ $field_slug ] = new $namespaced_classname;
        }
// dd($this->fields);
   //      echo json_encode( new Fields\TextField([
   //      	'slug'      => 'job_title',
			// 'label'     => __( 'Title', 'my-listing' ),
			// 'required'  => true,
			// 'priority'  => 1,
			// 'is_custom' => false,
   //      	]) );
   //      die;

   //      dd();
        // return $fields;
    }

    // public function field_blueprints() {
    //     foreach ( $this->fields as $field_type => $field ) {
    //         return
    //     }
    // }
}

add_action( 'init', function() {
    Designer::instance();
});
// Designer::instance()->getFields();
