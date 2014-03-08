<?php defined('_JEXEC') or die;

class KinoarhivViewPremieres extends JViewLegacy {
	protected $items;
	protected $pagination;
	protected $state;
	protected $form;

	public function display($tpl = null) {
		$user = JFactory::getUser();
		$lang = JFactory::getLanguage();

		$items = $this->get('Items');

		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $this->get('Errors')), 500);
			return false;
		}

		$this->addToolbar();
		$this->canEdit = $user->authorise('core.edit', 'com_kinoarhiv');

		$this->items = &$items;
		$this->lang = &$lang;

		parent::display($tpl);
	}

	protected function edit($tpl) {
		$app = JFactory::getApplication();
		$user = JFactory::getUser();

		if (!$user->authorise('core.create.genre', 'com_kinoarhiv') && !$user->authorise('core.edit.genre', 'com_kinoarhiv')) {
			throw new Exception(JText::_('COM_KA_NO_ACCESS_RIGHTS'), 403);
			return false;
		}

		$items = $this->get('Items');
		$form = $this->get('Form');

		$this->items = &$items;
		$this->form = &$form;

		if ($this->getLayout() !== 'modal') {
			$this->addToolbar($tpl);
		}

		parent::display('edit');
		$app->input->set('hidemainmenu', true);
	}

	protected function addToolbar($task='') {
		$app = JFactory::getApplication();
		$user = JFactory::getUser();

		JToolbarHelper::title(JText::_('COM_KA_PREMIERES_TITLE'), 'calendar');

		if ($user->authorise('core.create', 'com_kinoarhiv')) {
			JToolbarHelper::addNew('add');
		}

		if ($user->authorise('core.edit', 'com_kinoarhiv')) {
			JToolbarHelper::editList('edit');
			JToolbarHelper::divider();
		}

		if ($user->authorise('core.delete', 'com_kinoarhiv')) {
			JToolbarHelper::deleteList(JText::_('COM_KA_DELETE_SELECTED'), 'remove');
			JToolbarHelper::divider();
		}

		JToolbarHelper::custom('relations', 'tools', 'tools', JText::_('COM_KA_PREMIERES_TABLES_RELATIONS_TITLE'), false);
	}
}
