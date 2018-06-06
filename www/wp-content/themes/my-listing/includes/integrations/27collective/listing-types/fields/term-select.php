<?php

namespace CASE27\Integrations\ListingTypes\Fields;

class TermSelectField extends Field {

	public function field_props() {
		$this->props['type'] = 'term-select';
		$this->props['taxonomy'] = '';
	}

	public function render() {
		$this->getLabelField();
		$this->getKeyField();
		$this->getPlaceholderField();
		$this->getDescriptionField();
		$this->getTaxonomyField();
		$this->getRequiredField();
		$this->getShowInSubmitFormField();
		$this->getShowInAdminField();
	}
}