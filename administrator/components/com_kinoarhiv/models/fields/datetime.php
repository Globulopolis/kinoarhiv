<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2018 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for the Kinoarhiv.
 *
 * Provides a pop up date and time picker linked to a button.
 * Optionally may be filtered to use user's or server's time zone.
 *
 * @since  3.1
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
	 * @since  3.1
	 */
	protected $maxLength;

	/**
	 * Method to get the field input.
	 *
	 * @return  string  The field input.
	 *
	 * @since   3.1
	 */
	protected function getInput()
	{
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$framework = (string) $this->element['framework'];
		$attributes = ' ';

		if (!empty($this->size))
		{
			$attributes .= 'size="' . $this->size . '" ';
		}

		if (!empty($this->maxLength))
		{
			$attributes .= 'maxlength="' . $this->maxlength . '" ';
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

		if ($framework == 'bootstrap')
		{
			JHtml::_('jquery.framework');
			JHtml::_('bootstrap.framework');
			JHtml::_('stylesheet', 'media/com_kinoarhiv/css/bootstrap-datetimepicker.min.css');
			JHtml::_('script', 'media/com_kinoarhiv/js/bootstrap-datetimepicker.min.js');
			KAComponentHelper::getScriptLanguage('bootstrap-datetimepicker.', 'media/com_kinoarhiv/js/i18n/bootstrap-datetimepicker/');
			JHtml::_('script', 'media/com_kinoarhiv/js/backend.min.js');

			$dateformat = !empty($this->element['dateformat']) ? (string) $this->element['dateformat'] : '';
			$timeformat = !empty($this->element['timeformat']) ? (string) $this->element['timeformat'] : '';
			$html = '<div class="hasDatetime date input-append" data-framework="' . $framework . '"'
				. ' data-date="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"'
				. ' data-date-format="' . $dateformat . ' ' . $timeformat . '">
				<input type="text" name="' . $this->name . '" id="' . $this->id . '" class="' . $this->class . '"'
					. ' value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '" ' . $attributes . '/>
				<span class="add-on"><i class="icon-calendar"></i></span>
			</div>';
		}
		else
		{
			JHtml::_('jquery.framework');
			JHtml::_('stylesheet', 'media/com_kinoarhiv/jqueryui/' . $params->get('ui_theme') . '/jquery-ui.css');
			JHtml::_('script', 'media/com_kinoarhiv/js/jquery-ui.min.js');
			KAComponentHelper::getScriptLanguage('datepicker-', 'media/com_kinoarhiv/js/i18n/datepicker/');

			if (!empty($this->element['datatype']) && $this->element['datatype'] != 'date')
			{
				JHtml::_('script', 'media/com_kinoarhiv/js/jquery-ui-timepicker-addon.min.js');
				KAComponentHelper::getScriptLanguage('jquery-ui-timepicker-', 'media/com_kinoarhiv/js/i18n/timepicker/');
			}

			JHtml::_('script', 'media/com_kinoarhiv/js/backend.min.js');

			if (!empty($this->element['datatype']))
			{
				$attributes .= 'data-type="' . $this->element['datatype'] . '" ';
			}
			else
			{
				$attributes .= 'data-type="datetime" ';
			}

			$formats = '';

			if (!empty($this->element['dateformat']))
			{
				$formats .= 'data-date-format="' . $this->element['dateformat'] . '" ';
			}

			if (!empty($this->element['timeformat']))
			{
				$formats .= 'data-time-format="' . $this->element['timeformat'] . '" ';
			}

			$html = '<div class="hasDatetime date input-append" data-framework="' . $framework . '"'
				. ' data-date="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '" ' . $formats . '>
				<input type="text" name="' . $this->name . '" id="' . $this->id . '" class="' . $this->class . '"'
					. ' value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '" ' . $attributes . '/>
			</div>';
		}

		return $html;
	}
}
