<?php defined('JPATH_PLATFORM') or die;

class JFormRuleIp extends JFormRule {
	public function test(SimpleXMLElement $element, $value, $group = null, JRegistry $input = null, JForm $form = null) {
		if (!filter_var($value, FILTER_VALIDATE_IP)) {
			return false;
		}

		return true;
	}
}
