<?php

namespace CASE27\Integrations\ListingTypes\Fields;

class WPEditorField extends Field {

	public function field_props() {
		$this->props['type'] = 'wp-editor';
	}

	public function render() {
		$this->getLabelField();
		$this->getKeyField();
		$this->getPlaceholderField();
		$this->getDescriptionField();

		$this->getDescriptionTypeField();

		$this->getRequiredField();
		$this->getShowInSubmitFormField();
		$this->getShowInAdminField();
	}
}