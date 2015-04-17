<?php defined('JPATH_PLATFORM') or die;
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url			http://киноархив.com/
 */

//use Joomla\Registry\Registry;

class JFormRuleIp extends JFormRule {
	public function test(SimpleXMLElement $element, $value, $group = null, JRegistry $input = null, JForm $form = null) {
		// If the field is empty and not required, the field is valid.
		$required = ((string) $element['required'] == 'true' || (string) $element['required'] == 'required');

		if (!$required && empty($value)) {
			return true;
		}

		if (!filter_var($value, FILTER_VALIDATE_IP)) {
			return false;
		}
	}
}
