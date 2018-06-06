<?php

namespace CASE27\Integrations\ListingTypes\Fields;

class LocationField extends Field {

	public function field_props() {
		$this->props['type'] = 'location';
		// $this->props['reusable'] = false;
	}

	public function render() {
		$this->getLabelField();
		$this->getPlaceholderField();
		$this->getDescriptionField();
		$this->getRequiredField();
		$this->getShowInSubmitFormField();
		$this->getShowInAdminField();
	}
}