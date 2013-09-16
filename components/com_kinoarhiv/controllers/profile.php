<?php defined('_JEXEC') or die;

class KinoarhivControllerProfile extends JControllerLegacy {
	public function display($cachable = false, $urlparams = array()) {
		$cachable = true;

		$user = JFactory::getUser();

		if ($user->get('guest')) {
			GlobalHelper::showMsg('JERROR_ALERTNOAUTHOR');
			return false;
		}

		if ($this->input->getMethod() == 'POST') {
			$cachable = false;
		}

		parent::display($cachable, $safeurlparams);
		return $this;
	}
}
