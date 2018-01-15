<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2018 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * Date validation rule class
 *
 * @since  3.0
 */
class JFormRuleDate extends JFormRule
{
	/**
	 * Method to test date.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 * @param   Registry          $input    An optional Registry object with the entire data set to validate against the entire form.
	 * @param   JForm             $form     The form object for which the field is being tested.
	 *
	 * @return  boolean  True if the value is valid, false otherwise.
	 *
	 * @since   3.0
	 */
	public function test(SimpleXMLElement $element, $value, $group = null, Registry $input = null, JForm $form = null)
	{
		// If the field is empty and not required, the field is valid.
		$required = ((string) $element['required'] == 'true' || (string) $element['required'] == 'required');

		if (!$required && empty($value))
		{
			return true;
		}

		$dateformat = isset($element['dateformat']) ? $element['dateformat'] : 'Y-m-d';
		$dateformat = str_replace(array('yy', 'mm', 'dd'), array('Y', 'm', 'd'), strtolower($dateformat));
		$timeformat = isset($element['timeformat']) ? $element['timeformat'] : '';
		$timeformat = str_replace(array('HH', 'mm', 'ss'), array('H', 'i', 's'), $timeformat);
		$concat     = !empty($dateformat) && !empty($timeformat) ? ' ' : '';
		$datetime   = DateTime::createFromFormat($dateformat . $concat . $timeformat, $value);

		if ($datetime instanceof DateTime)
		{
			if ($datetime > DateTime::createFromFormat('Y-m-d', '1800-01-01') && $datetime < DateTime::createFromFormat('Y-m-d', '2100-01-01'))
			{
				return true;
			}
		}
		else
		{
			// A full numeric representation of a year, 4 digits. Otherwise it must be in format Y-m(e.g. 2012-01)
			if (preg_match('#^\d{4}$#', $value, $matches))
			{
				if ($matches[0] > 1800 && $matches[0] < 2100)
				{
					return true;
				}
			}
			elseif (preg_match('#^(\d{4})[-\/](\d{2})$#', $value, $matches))
			{
				if ($matches[1] > 1800 && $matches[1] < 2100)
				{
					return true;
				}
			}
		}

		return false;
	}
}
