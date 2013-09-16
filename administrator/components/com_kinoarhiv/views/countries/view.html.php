<?php defined('_JEXEC') or die;

class KinoarhivViewCountries extends JViewLegacy {
	protected $items;
	protected $pagination;
	protected $state;
	protected $form;

	public function display($tpl = null) {
		$app = JFactory::getApplication();
		$task = $app->input->get('task', '', 'cmd');

		switch ($task) {
			case 'add': $this->edit($tpl); break;
			case 'edit': $this->edit($tpl); break;
			default: $this->_display($tpl); break;
		}
	}

	protected function _display($tpl) {
		$user = JFactory::getUser();

		$items = $this->get('Items');
		$pagination = $this->get('Pagination');
		$state = $this->get('State');

		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		if ($this->getLayout() !== 'modal') {
			$this->addToolbar();
		}

		$this->canEdit = $user->authorise('core.edit.country', 'com_kinoarhiv');
		$this->canEditState = $user->authorise('core.edit.state.country', 'com_kinoarhiv');

		$this->items = &$items;
		$this->pagination = &$pagination;
		$this->state = &$state;

		parent::display($tpl);
	}

	protected function edit($tpl) {
		$app = JFactory::getApplication();
		$user = JFactory::getUser();

		if (!$user->authorise('core.create.country', 'com_kinoarhiv') && !$user->authorise('core.edit.country', 'com_kinoarhiv')) {
			JError::raiseError(403, JText::_('COM_KA_NO_ACCESS_RIGHTS'));
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

		if ($task == 'add') {
			JToolbarHelper::title(JText::_('COM_KA_COUNTRIES_ADD_TITLE'), 'cpanel.png');
			JToolbarHelper::apply('apply');
			JToolbarHelper::save('save');
			JToolbarHelper::save2new('save2new');
			JToolbarHelper::divider();
			JToolbarHelper::cancel();
		} elseif ($task == 'edit') {
			JToolbarHelper::title(JText::sprintf(JText::_('COM_KA_COUNTRIES_EDIT_TITLE'), $this->items->name), 'cpanel.png');
			JToolbarHelper::apply('apply');
			JToolbarHelper::save('save');
			JToolbarHelper::save2new('save2new');
			if (!empty($this->items->id)) {
				JToolbarHelper::custom('relations', 'tools', 'tools', JText::_('COM_KA_COUNTRIES_RELATIONS_BUTTON_TITLE'), false);
			}
			JToolbarHelper::divider();
			JToolbarHelper::cancel();
		} else {
			JToolbarHelper::title(JText::_('COM_KA_COUNTRIES_TITLE'), 'cpanel.png');

			if ($user->authorise('core.create.country', 'com_kinoarhiv')) {
				JToolbarHelper::addNew('add');
			}

			if ($user->authorise('core.edit.country', 'com_kinoarhiv')) {
				JToolbarHelper::editList('edit');
				JToolbarHelper::divider();
			}

			if ($user->authorise('core.edit.state.country', 'com_kinoarhiv')) {
				JToolbarHelper::publishList();
				JToolbarHelper::unpublishList();
			}

			if ($user->authorise('core.delete.country', 'com_kinoarhiv')) {
				JToolbarHelper::deleteList(JText::_('COM_KA_DELETE_SELECTED'), 'remove');
				JToolbarHelper::divider();
			}

			JToolbarHelper::custom('relations', 'tools', 'tools', JText::_('COM_KA_COUNTRIES_TABLES_RELATIONS_TITLE'), false);
		}
	}

	protected function getSortFields() {
		return array(
			'a.state' => JText::_('JSTATUS'),
			'a.name' => JText::_('JGLOBAL_TITLE'),
			'a.code' => JText::_('COM_KA_COUNTRY_CODE'),
			'language' => JText::_('JGRID_HEADING_LANGUAGE'),
			'a.id' => JText::_('JGRID_HEADING_ID')
		);
	}
}
