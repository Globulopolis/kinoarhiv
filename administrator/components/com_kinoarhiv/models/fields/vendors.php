<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('JPATH_PLATFORM') or die;

/**
 * Form Field to load a vendor or list of vendors
 *
 * @since  3.0
 */
class JFormFieldVendors extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $type = 'Vendors';

	/**
	 * Method to instantiate the form field object.
	 *
	 * @param   JForm  $form  The form to attach to the form field object.
	 *
	 * @since   3.0
	 */
	public function __construct($form = null)
	{
		$params = JComponentHelper::getParams('com_kinoarhiv');

		JHtml::_('jquery.framework');
		JHtml::_('stylesheet', JUri::root() . 'components/com_kinoarhiv/assets/themes/component/' . $params->get('ka_theme') . '/css/select.css');
		JHtml::_('script', JUri::root() . 'components/com_kinoarhiv/assets/js/select2.min.js');
		KAComponentHelper::getScriptLanguage('select2_locale_', true, 'select', true);

		parent::__construct();
	}

	/**
	 * Method to get the field input.
	 *
	 * @return  string  The field input.
	 *
	 * @since   3.0
	 */
	protected function getInput()
	{
		$class = 'hasAutocomplete ';

		// Initialize some field attributes.
		$size = $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$maxLength = $this->element['maxlength'] ? ' maxlength="' . (int) $this->element['maxlength'] . '"' : '';
		$class .= $this->element['class'] ? (string) $this->element['class'] : '';
		$readonly = ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
		$disabled = ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$required = $this->required ? ' required aria-required="true"' : '';
		$data_type = $this->element['data-ac-type'] ? ' data-ac-type="' . (string) $this->element['data-ac-type'] . '"' : '';
		$data_allow_clear = $this->element['data-allow-clear'] ? ' data-allow-clear="true"' : '';
		$data_multiple = (bool) $this->element['data-multiple'] ? ' data-multiple="true"' : '';
		$data_sel_size = isset($this->element['data-sel-size']) ? ' data-sel-size="' . $this->element['data-sel-size'] . '"' : '';
		$data_sortable = (bool) $this->element['data-sortable'] ? ' data-sortable="true"' : '';

		if (isset($this->value['ids']) && is_array($this->value['ids']))
		{
			$value = implode(',', $this->value['ids']);
		}
		else
		{
			$value = $this->value;
		}

		// Initialize JavaScript field attributes.
		$onchange = $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		return '<input type="hidden" name="' . $this->name . '" id="' . $this->id . '" value="' . $value . '" class="' . $class . '"' . $size . $disabled . $readonly . $onchange . $maxLength . $required . $data_type . $data_allow_clear . $data_multiple . $data_sel_size . $data_sortable . ' />';
	}
}
