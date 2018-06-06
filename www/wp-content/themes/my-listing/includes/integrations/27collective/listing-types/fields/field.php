<?php

namespace CASE27\Integrations\ListingTypes\Fields;

use \CASE27\Integrations\ListingTypes\Designer;

abstract class Field implements \JsonSerializable {

	protected $props = [
			   'type'                => 'text',
			   'slug'                => 'custom-field',
			   'label'               => 'Custom Field',
			   'default'             => '',
			   'required'            => false,
			   'reusable'            => true,
			   'is_custom'           => true,
			   'label_l10n'          => ['locale' => 'en_US'],
			   'description'         => '',
			   'placeholder'         => '',
			   'default_label'       => 'Custom Field',
			   'show_in_admin'       => true,
			   'description_l10n'    => ['locale' => 'en_US'],
			   'placeholder_l10n'    => ['locale' => 'en_US'],
			   'show_in_submit_form' => true,
			  ];

	public static $store = null;

	public function __construct( $props = [] ) {
		$this->field_props();
		$this->set_props( $props );

		if ( ! self::$store ) {
			self::$store = Designer::$store;
		}
	}

	abstract protected function render();

	abstract protected function field_props();

	final public function print_options() {
		ob_start(); ?>
		<div class="field-settings-wrapper" v-if="field.type == '<?php echo esc_attr( $this->props['type'] ) ?>'">
			<?php $this->render(); ?>
		</div>
		<?php return ob_get_clean();
	}

	public function set_props( $props = [] ) {
		foreach ($props as $name => $value) {
			if ( isset( $this->props[ $name ] ) ) {
				$this->props[ $name ] = $value;
			}
		}
	}

	public function get_props() {
		return $this->props;
	}

	public function jsonSerialize() {
		return $this->props;
	}

	protected function getLabelField() { ?>
		<div class="form-group">
			<label>Label</label>
			<input type="text" v-model="field.label" @input="fieldsTab().setKey(field, field.label)">

			<?php c27()->get_partial('admin/input-language', ['object' => 'field.label_l10n']) ?>
		</div>
	<?php }

	protected function getKeyField() { ?>
		<div class="form-group" v-if="field.is_custom">
			<label>Key</label>
			<input type="text" v-model="field.slug" @input="fieldsTab().setKey(field, field.slug)">
		</div>
	<?php }

	protected function getPlaceholderField() { ?>
		<div class="form-group">
			<label>Placeholder</label>
			<input type="text" v-model="field.placeholder">

			<?php c27()->get_partial('admin/input-language', ['object' => 'field.placeholder_l10n']) ?>
		</div>
	<?php }

	protected function getDescriptionField() { ?>
		<div class="form-group">
			<label>Description</label>
			<input type="text" v-model="field.description">

			<?php c27()->get_partial('admin/input-language', ['object' => 'field.description_l10n']) ?>
		</div>
	<?php }

	protected function getIconField() { ?>
		<div class="form-group">
			<label>Icon</label>
			<iconpicker v-model="field.icon"></iconpicker>
		</div>
	<?php }

	protected function getRequiredField() { ?>
		<div class="form-group full-width">
			<label><input type="checkbox" v-model="field.required"> Required field</label>
		</div>
	<?php }

	protected function getMultipleField() { ?>
		<div class="form-group full-width">
			<label><input type="checkbox" v-model="field.multiple"> Multiple?</label>
		</div>
	<?php }

	protected function getShowInSubmitFormField() { ?>
		<div class="form-group full-width">
			<label><input type="checkbox" v-model="field.show_in_submit_form"> Show in submit form</label>
		</div>
	<?php }

	protected function getShowInAdminField() { ?>
		<div class="form-group full-width">
			<label><input type="checkbox" v-model="field.show_in_admin"> Show in admin edit page</label>
		</div>
	<?php }

	protected function getOptionsField() { ?>
		<div class="form-group full-width options-field">
			<hr>
			<label>Options</label>

			<div class="form-group" v-for="(value, key, index) in field.options" v-show="!state.fields.editingOptions">
				<input type="text" v-model="field.options[key]" disabled="disabled">
			</div>

			<div v-show="!state.fields.editingOptions && !Object.keys(field.options).length">
				<small><em>No options added yet.</em></small>
			</div>

			<textarea
			id="custom_field_options"
			v-show="state.fields.editingOptions"
			placeholder="Add each option in a new line."
			@keyup="fieldsTab().editFieldOptions($event, field)"
			cols="50" rows="7">{{ Object.keys(field.options).map(function(el) { return field.options[el]; }).join('\n') }}</textarea>
			<small v-show="state.fields.editingOptions"><em>Put each option in a new line.</em></small>
			<br><br v-show="state.fields.editingOptions || Object.keys(field.options).length">
			<button @click.prevent="state.fields.editingOptions = !state.fields.editingOptions;" class="btn btn-primary">{{ state.fields.editingOptions ? 'Save Options' : 'Add/Edit Options' }}</button>
		</div>
	<?php }

	protected function getTaxonomyField() { ?>
		<div class="form-group full-width" v-if="field.is_custom">
			<label>Taxonomy</label>
			<div class="select-wrapper">
				<select v-model="field.taxonomy">
					<?php
					foreach ( self::$store['taxonomies'] as $taxonomy ) {
						echo "<option value='{$taxonomy->name}'>{$taxonomy->label}</option>";
					}
					?>
				</select>
			</div>
		</div>
	<?php }

	protected function getAllowedMimeTypesField() { ?>
		<div class="form-group full-width" v-if="['job_logo', 'job_cover', 'job_gallery'].indexOf(field.slug) <= -1">
			<label>Allowed file types</label>
			<select multiple="multiple" v-model="field.allowed_mime_types_arr" @change="fieldsTab().editFieldMimeTypes($event, field)">
				<?php foreach ( self::$store['mime-types'] as $extension => $mime ): ?>
					<option value="<?php echo "{$extension} => {$mime}" ?>"><?php echo $mime ?></option>
				<?php endforeach ?>
			</select>
			<br><br>
			<label><input type="checkbox" v-model="field.multiple"> Allow multiple files?</label>
		</div>
	<?php }

	protected function getListingTypeField() { ?>
		<div class="form-group full-width">
			<label>Related Listing Type</label>
			<div class="select-wrapper">
				<select v-model="field.listing_type">
					<?php foreach ( self::$store['listing-types'] as $listing_type ): ?>
						<option value="<?php echo $listing_type->post_name ?>"><?php echo $listing_type->post_title ?></option>
					<?php endforeach ?>
				</select>
			</div>
		</div>
	<?php }

	protected function getFormatField() { ?>
		<div class="form-group full-width">
			<label>Format</label>
			<div class="select-wrapper">
				<select v-model="field.format">
					<option value="date">Date</option>
					<option value="datetime">Date + Time</option>
				</select>
			</div>
		</div>
	<?php }

	protected function getMinField() { ?>
		<div class="form-group">
			<label>Minimum value</label>
			<input type="number" v-model="field.min">
		</div>
	<?php }

	protected function getMaxField() { ?>
		<div class="form-group">
			<label>Maximum value</label>
			<input type="number" v-model="field.max">
		</div>
	<?php }

	protected function getStepField() { ?>
		<div class="form-group">
			<label>Step size</label>
			<input type="number" v-model="field.step">
		</div>
	<?php }

	protected function getDescriptionTypeField() { ?>
		<div class="form-group" v-if="field.slug == 'job_description'">
			<label>Type</label>
			<div class="select-wrapper">
				<select v-model="field.type">
					<option value="textarea">Textarea</option>
					<option value="wp-editor">WP Editor</option>
				</select>
			</div>
		</div>
	<?php }
}
