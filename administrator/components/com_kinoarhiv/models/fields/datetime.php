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
 * Form Field class for the Joomla Platform.
 *
 * Provides a pop up date picker linked to a button.
 * Optionally may be filtered to use user's or server's time zone.
 *
 * @since  3.0
 */
class JFormFieldDatetime extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $type = 'Datetime';

	/**
	 * The allowable maxlength of the field.
	 *
	 * @var    integer
	 * @since  3.0
	 */
	protected $maxLength;

	/**
	 * Method to instantiate the form field object.
	 *
	 * @param   JForm  $form  The form to attach to the form field object.
	 *
	 * @since   3.0
	 */
	public function __construct($form = null)
	{
		JHtml::_('jquery.framework');
		JHtml::_('script', JURI::root() . 'components/com_kinoarhiv/assets/js/jquery-ui.min.js');
		KAComponentHelper::getScriptLanguage('datepicker-', true, 'ui', true);
		JHtml::_('script', JURI::base() . 'components/com_kinoarhiv/assets/js/jquery-ui-timepicker.min.js');
		KAComponentHelper::getScriptLanguage('jquery-ui-timepicker-', true, 'timepicker', false);
		JHtml::_('script', JURI::base() . 'components/com_kinoarhiv/assets/js/utils.js');

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
		$attributes = ' ';
		$class = 'hasDatetime ';

		if (!empty($this->size))
		{
			$attributes .= 'size="' . $this->size . '" ';
		}

		if (!empty($this->maxLength))
		{
			$attributes .= 'maxlength="' . $this->maxlength . '" ';
		}

		if (!empty($this->class))
		{
			$class .= $this->class . ' ';
		}

		if ($this->readonly)
		{
			$attributes .= 'readonly ';
		}

		if ($this->disabled)
		{
			$attributes .= 'disabled ';
		}

		if ($this->element['required'])
		{
			$attributes .= 'required aria-required="true" ';
		}

		if (!empty($this->element['dateformat']))
		{
			$attributes .= 'data-date-format="' . $this->element['dateformat'] . '" ';
		}

		if (!empty($this->element['timeformat']))
		{
			$attributes .= 'data-time-format="' . $this->element['timeformat'] . '" ';
		}

		if (!empty($this->element['datatype']))
		{
			$attributes .= 'data-type="' . $this->element['datatype'] . '" ';
		}
		else
		{
			$attributes .= 'data-type="datetime" ';
		}

		$html = '<div class="input-append">
			<input type="text" name="' . $this->name . '" id="' . $this->id . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '" class="' . $class . '" ' . $attributes . ' />
		</div>';

		return $html;
	}
}
