<?php defined('JPATH_PLATFORM') or die;

class JFormFieldTags extends JFormField {
	protected $type = 'Tags';

	protected function getInput() {
		// Initialize some field attributes.
		$size = $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$maxLength = $this->element['maxlength'] ? ' maxlength="' . (int) $this->element['maxlength'] . '"' : '';
		$class = $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		$readonly = ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
		$disabled = ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$required = $this->required ? ' required="required" aria-required="true"' : '';
		$data_type = $this->element['data-ac-type'] ? ' data-ac-type="'.$this->element['data-ac-type'].'"' : '';
		$value = is_array($this->value) ? implode(',', $this->value['ids']) : htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8');

		// Initialize JavaScript field attributes.
		$onchange = $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		return '<input type="hidden" name="' . $this->name . '" id="' . $this->id . '" value="'
			. $value . '"' . $class . $size . $disabled . $readonly . $onchange . $maxLength . $required . $data_type . '/>';
	}
}
