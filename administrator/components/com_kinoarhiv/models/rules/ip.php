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

use Joomla\Registry\Registry;

/**
 * IP validation rule class
 *
 * @since  3.0
 */
class JFormRuleIp extends JFormRule
{
	/**
	 * Method to test value
	 *
	 * @param   SimpleXMLElement  $element  XML element
	 * @param   mixed             $value    Value of the input
	 * @param   null              $group    Field group
	 * @param   Registry          $input    Input object
	 * @param   JForm             $form     Form
	 *
	 * @return bool
	 */
	public function test(SimpleXMLElement $element, $value, $group = null, Registry $input = null, JForm $form = null)
	{
		// If the field is empty and not required, the field is valid.
		$required = ((string) $element['required'] == 'true' || (string) $element['required'] == 'required');

		if (!$required && empty($value))
		{
			return true;
		}

		if (!filter_var($value, FILTER_VALIDATE_IP))
		{
			return false;
		}

		return true;
	}
}
