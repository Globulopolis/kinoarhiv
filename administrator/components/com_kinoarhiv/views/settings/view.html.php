<?php defined('_JEXEC') or die;

class KinoarhivViewSettings extends JViewLegacy {
	protected $form;

	public function display($tpl = null) {
		$user = JFactory::getUser();

		if (!$user->authorise('core.admin', 'com_kinoarhiv')) {
			JError::raiseError(403, JText::_('COM_KA_NO_ACCESS_RIGHTS'));
			return false;
		}

		$form = $this->get('Form');
		$data = $this->get('Settings');
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		$lang = JFactory::getLanguage();

		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		if ($form && $data->params) {
			$form->bind($data->params);
		}

		$this->form = &$form;
		$this->lang = &$lang;

		$this->userIsSuperAdmin = $user->authorise('core.admin');
		$this->return = $app->input->get('return', '', 'base64');

		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar() {
		JToolbarHelper::title(JText::_('COM_KA_SETTINGS_TITLE'), 'cpanel.png');
		JToolbarHelper::apply('apply');
		JToolbarHelper::save('save');
		JToolbarHelper::divider();
		JToolbarHelper::cancel('cancel');
		JToolbarHelper::divider();
	}
}
