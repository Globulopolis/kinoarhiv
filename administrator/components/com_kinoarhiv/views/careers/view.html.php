<?php defined('_JEXEC') or die;

class KinoarhivViewCareers extends JViewLegacy {
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
			throw new Exception(implode("\n", $this->get('Errors')), 500);
			return false;
		}

		if ($this->getLayout() !== 'modal') {
			$this->addToolbar();
		}

		$this->canEdit = $user->authorise('core.edit.career', 'com_kinoarhiv');

		$this->items = &$items;
		$this->pagination = &$pagination;
		$this->state = &$state;

		parent::display($tpl);
	}

	protected function edit($tpl) {
		$app = JFactory::getApplication();
		$user = JFactory::getUser();

		if (!$user->authorise('core.create.career', 'com_kinoarhiv') && !$user->authorise('core.edit.career', 'com_kinoarhiv')) {
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

		if ($task == 'add') {
			JToolbarHelper::title(JText::_('COM_KA_CAREER_ADD_TITLE'), 'address');
			JToolbarHelper::apply('apply');
			JToolbarHelper::save('save');
			JToolbarHelper::save2new('save2new');
			JToolbarHelper::divider();
			JToolbarHelper::cancel();
		} elseif ($task == 'edit') {
			JToolbarHelper::title(JText::sprintf(JText::_('COM_KA_CAREER_EDIT_TITLE'), $this->items->title), 'address');
			JToolbarHelper::apply('apply');
			JToolbarHelper::save('save');
			JToolbarHelper::save2new('save2new');
			if (!empty($this->items->id)) {
				JToolbarHelper::custom('relations', 'tools', 'tools', JText::_('COM_KA_CAREER_RELATIONS_TITLE'), false);
			}
			JToolbarHelper::divider();
			JToolbarHelper::cancel();
		} else {
			JToolbarHelper::title(JText::_('COM_KA_CAREER_TITLE'), 'address');

			if ($user->authorise('core.create.career', 'com_kinoarhiv')) {
				JToolbarHelper::addNew('add');
			}

			if ($user->authorise('core.edit.career', 'com_kinoarhiv')) {
				JToolbarHelper::editList('edit');
				JToolbarHelper::divider();
			}

			if ($user->authorise('core.delete.career', 'com_kinoarhiv')) {
				JToolbarHelper::deleteList(JText::_('COM_KA_DELETE_SELECTED'), 'remove');
				JToolbarHelper::divider();
			}

			JToolbarHelper::custom('relations', 'tools', 'tools', JText::_('COM_KA_CAREER_RELATIONS_TITLE'), false);
		}
	}

	protected function getSortFields() {
		return array(
			'a.title' => JText::_('JGLOBAL_TITLE'),
			'language' => JText::_('JGRID_HEADING_LANGUAGE'),
			'a.id' => JText::_('JGRID_HEADING_ID')
		);
	}
}
