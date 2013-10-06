<?php defined('_JEXEC') or die;

class KinoarhivViewMediamanager extends JViewLegacy {
	public function display($tpl = null) {
		$params = JComponentHelper::getParams('com_kinoarhiv');

		$this->params = &$params;

		parent::display($tpl);
	}
}
