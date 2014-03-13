<?php defined('JPATH_PLATFORM') or die;

class JFormFieldCareers extends JFormField {
	protected $type = 'Careers';

	public function __construct() {
		$lang = JFactory::getLanguage()->getTag();

		JHtml::_('jquery.framework');
		JHtml::_('script', JURI::root().'components/com_kinoarhiv/assets/js/select2.min.js');
		JHtml::_('script', JURI::root().'components/com_kinoarhiv/assets/js/i18n/select/select2_locale_'.substr($lang, 0, 2).'.js');

		parent::__construct();
	}

	protected function getInput() {
		// Initialize some field attributes.
		$size = $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$maxLength = $this->element['maxlength'] ? ' maxlength="' . (int) $this->element['maxlength'] . '"' : '';
		$class = $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		$readonly = ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
		$disabled = ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$required = $this->required ? ' required aria-required="true"' : '';
		$data_type = $this->element['data-ac-type'] ? ' data-ac-type="'.$this->element['data-ac-type'].'"' : '';
		$data_allow_clear = $this->element['data-allow-clear'] ? ' data-allow-clear="1"' : '';

		// Initialize JavaScript field attributes.
		$onchange = $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		return '<input type="text" name="' . $this->name . '" id="' . $this->id . '" value="'
			. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . $class . $size . $disabled . $readonly . $onchange . $maxLength . $required . $data_type . $data_allow_clear . '/>';
	}
}
