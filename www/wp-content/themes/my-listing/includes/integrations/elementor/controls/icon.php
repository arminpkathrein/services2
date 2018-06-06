<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Elementor\Base_Data_Control' ) ) return;

/**
 * A Font Icon select box.
 */
class CASE27_Elementor_Control_Icon extends Base_Data_Control {

	public function get_type() {
		return 'icon';
	}

	public static function get_icons() {
		$icons = [];

		$font_awesome_icons = require CASE27_INTEGRATIONS_DIR . '/27collective/icons/font-awesome.php';
		$material_icons = require CASE27_INTEGRATIONS_DIR . '/27collective/icons/material-icons.php';
		$theme_icons = require CASE27_INTEGRATIONS_DIR . '/27collective/icons/theme-icons.php';

		foreach ($font_awesome_icons as $icon) {
			$icons["fa {$icon}"] = str_replace('fa-', '', $icon);
		}

		foreach ($material_icons as $icon) {
			$icons["material-icons {$icon}"] = $icon;
		}

		foreach ($theme_icons as $icon) {
			$icons[$icon] = str_replace('icon-', '', $icon);
		}

		return $icons;
	}

	protected function get_default_settings() {
		return [
			'icons' => self::get_icons(),
		];
	}

	public function content_template() {
		?>
		<div class="elementor-control-field">
			<label class="elementor-control-title">{{{ data.label }}}</label>
			<div class="elementor-control-input-wrapper">
				<select class="elementor-control-icon" data-setting="{{ data.name }}" data-placeholder="<?php _e( 'Select Icon', 'my-listing' ); ?>">
					<option value=""><?php _e( 'Select Icon', 'my-listing' ); ?></option>
					<# _.each( data.icons, function( option_title, option_value ) { #>
					<option value="{{ option_value }}">{{{ option_title }}}</option>
					<# } ); #>
				</select>
			</div>
		</div>
		<# if ( data.description ) { #>
		<div class="elementor-control-field-description">{{ data.description }}</div>
		<# } #>
		<?php
	}
}


add_action('elementor/controls/controls_registered', function($el) {
	$el->register_control('icon', new CASE27_Elementor_Control_Icon);
});