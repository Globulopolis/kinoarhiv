<?php defined('_JEXEC') or die;

class KinoarhivViewMediamanager extends JViewLegacy {
	public function display($tpl = null) {
		if ($this->getLayout() !== 'modal') {
			$this->addToolbar($tpl);
		}

		parent::display($tpl);
	}

	protected function addToolbar() {
		$user = JFactory::getUser();

		JToolbarHelper::title(JText::_('COM_KA_MEDIAMANAGER'), 'cpanel.png');

		if ($user->authorise('core.create', 'com_kinoarhiv')) {
			JToolbarHelper::custom('upload', 'upload', 'upload', JText::_('JTOOLBAR_UPLOAD'), false);
			JToolbarHelper::divider();
		}

		if ($user->authorise('core.edit.state', 'com_kinoarhiv')) {
			JToolbarHelper::publishList();
			JToolbarHelper::unpublishList();
			JToolbarHelper::divider();
		}

		if ($user->authorise('core.delete', 'com_kinoarhiv')) {
			JToolbarHelper::deleteList(JText::_('COM_KA_DELETE_SELECTED'), 'remove');
		}
	}
}
