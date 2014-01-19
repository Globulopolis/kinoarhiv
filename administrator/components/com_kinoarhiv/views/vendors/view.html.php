<?php defined('_JEXEC') or die;

class KinoarhivViewVendors extends JViewLegacy {
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

		$this->canEdit = $user->authorise('core.edit.vendor', 'com_kinoarhiv');
		$this->canEditState = $user->authorise('core.edit.state.vendor', 'com_kinoarhiv');

		$this->items = &$items;
		$this->pagination = &$pagination;
		$this->state = &$state;

		parent::display($tpl);
	}

	protected function edit($tpl) {
		$app = JFactory::getApplication();
		$user = JFactory::getUser();

		if (!$user->authorise('core.create.vendor', 'com_kinoarhiv') && !$user->authorise('core.edit.vendor', 'com_kinoarhiv')) {
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
			JToolbarHelper::title(JText::_('COM_KA_VENDORS_ADD_TITLE'), 'basket');
			JToolbarHelper::apply('apply');
			JToolbarHelper::save('save');
			JToolbarHelper::save2new('save2new');
			JToolbarHelper::divider();
			JToolbarHelper::cancel();
		} elseif ($task == 'edit') {
			JToolbarHelper::title(JText::sprintf(JText::_('COM_KA_VENDORS_EDIT_TITLE'), $this->items->company_name), 'basket');
			JToolbarHelper::apply('apply');
			JToolbarHelper::save('save');
			JToolbarHelper::save2new('save2new');
			JToolbarHelper::divider();
			JToolbarHelper::cancel();
		} else {
			JToolbarHelper::title(JText::_('COM_KA_VENDORS_TITLE'), 'basket');

			if ($user->authorise('core.create.vendor', 'com_kinoarhiv')) {
				JToolbarHelper::addNew('add');
			}

			if ($user->authorise('core.edit.vendor', 'com_kinoarhiv')) {
				JToolbarHelper::editList('edit');
				JToolbarHelper::divider();
			}

			if ($user->authorise('core.edit.state.vendor', 'com_kinoarhiv')) {
				JToolbarHelper::publishList();
				JToolbarHelper::unpublishList();
			}

			if ($user->authorise('core.delete.vendor', 'com_kinoarhiv')) {
				JToolbarHelper::deleteList(JText::_('COM_KA_DELETE_SELECTED'), 'remove');
			}
		}
	}

	protected function getSortFields() {
		return array(
			'v.state' => JText::_('JSTATUS'),
			'v.company_name' => JText::_('COM_KA_VENDORS_FIELD_TITLE'),
			'v.company_name_intl' => JText::_('COM_KA_VENDORS_FIELD_TITLE_INTL'),
			'language' => JText::_('JGRID_HEADING_LANGUAGE'),
			'v.id' => JText::_('JGRID_HEADING_ID')
		);
	}
}
