<?php defined('_JEXEC') or die;

class KinoarhivViewSettings extends JViewLegacy {
	protected $form;
	protected $data;

	public function display($tpl = null) {
		$user = JFactory::getUser();

		if (!$user->authorise('core.admin', 'com_kinoarhiv')) {
			throw new Exception(JText::_('COM_KA_NO_ACCESS_RIGHTS'), 403);
			return false;
		}

		$form = $this->get('Form');
		$data = $this->get('Settings');
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		$lang = JFactory::getLanguage();

		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $this->get('Errors')), 500);
			return false;
		}

		if ($form && $data->params) {
			$form->bind($data->params);
		}

		$this->form = &$form;
		$this->data = &$data;
		$this->lang = &$lang;

		$this->userIsSuperAdmin = $user->authorise('core.admin');
		$this->return = $app->input->get('return', '', 'base64');
		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar() {
		JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_SETTINGS_TITLE')), 'options');
		JToolbarHelper::apply('apply');
		JToolbarHelper::save('save');
		JToolbarHelper::divider();
		JToolbarHelper::cancel('cancel');
		JToolbarHelper::divider();
	}
}
