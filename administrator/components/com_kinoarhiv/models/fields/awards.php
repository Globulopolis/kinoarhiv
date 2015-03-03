<?php defined('JPATH_PLATFORM') or die;
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url			http://киноархив.com/
 */

class JFormFieldAwards extends JFormField {
	protected $type = 'Awards';

	public function __construct() {
		$params = JComponentHelper::getParams('com_kinoarhiv');

		JHtml::_('jquery.framework');
		JHtml::_('stylesheet', JURI::root().'components/com_kinoarhiv/assets/themes/component/'.$params->get('ka_theme').'/css/select.css');
		JHtml::_('script', JURI::root().'components/com_kinoarhiv/assets/js/select2.min.js');
		GlobalHelper::getScriptLanguage('select2_locale_', true, 'select', true);

		parent::__construct();
	}

	protected function getInput() {
		$class = 'hasAutocomplete ';

		// Initialize some field attributes.
		$size = $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$maxLength = $this->element['maxlength'] ? ' maxlength="' . (int) $this->element['maxlength'] . '"' : '';
		$class .= $this->element['class'] ? (string)$this->element['class'] : '';
		$readonly = ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
		$disabled = ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$required = $this->required ? ' required aria-required="true"' : '';
		$data_type = $this->element['data-ac-type'] ? ' data-ac-type="'.(string)$this->element['data-ac-type'].'"' : '';
		$data_allow_clear = $this->element['data-allow-clear'] ? ' data-allow-clear="true"' : '';

		if (isset($this->value['ids']) && is_array($this->value['ids'])) {
			$value = implode(',', $this->value['ids']);
		} else {
			$value = $this->value;
		}

		// Initialize JavaScript field attributes.
		$onchange = $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		return '<input type="hidden" name="'.$this->name.'" id="'.$this->id.'" value="'.$value.'" class="'.$class.'"'.$size.$disabled.$readonly.$onchange.$maxLength.$required.$data_type.$data_allow_clear.' />';
	}
}
