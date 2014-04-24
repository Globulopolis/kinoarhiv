<?php defined('_JEXEC') or die;

class KinoarhivViewControlPanel extends JViewLegacy {
	public function display($tpl = null) {
		$this->addToolbar();
		
		parent::display($tpl);
	}

	protected function addToolbar() {
		JToolbarHelper::title(JText::_('COM_KINOARHIV_CP'), 'play');
	}
}
