<?php defined('_JEXEC') or die;

class KinoarhivViewAwards extends JViewLegacy {
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

		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $this->get('Errors')), 500);
			return false;
		}

		if ($this->getLayout() !== 'modal') {
			$this->addToolbar();
		}

		$this->canEdit = $user->authorise('core.edit.country', 'com_kinoarhiv');
		$this->canEditState = $user->authorise('core.edit.state.country', 'com_kinoarhiv');

		parent::display($tpl);
	}

	protected function edit($tpl) {
		$app = JFactory::getApplication();
		$user = JFactory::getUser();

		if (!$user->authorise('core.create.career', 'com_kinoarhiv') && !$user->authorise('core.edit.career', 'com_kinoarhiv')) {
			throw new Exception(JText::_('COM_KA_NO_ACCESS_RIGHTS'), 403);
			return false;
		}

		$this->form = $this->get('Form');

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
			JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_AWARDS_TITLE').': '.JText::_('COM_KA_NEW')), 'asterisk');
			JToolbarHelper::apply('apply');
			JToolbarHelper::save('save');
			JToolbarHelper::save2new('save2new');
			JToolbarHelper::divider();
			JToolbarHelper::cancel();
		} elseif ($task == 'edit') {
			JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_AWARDS_TITLE').': '.$this->form->getValue('title')), 'asterisk');
			JToolbarHelper::apply('apply');
			JToolbarHelper::save('save');
			JToolbarHelper::save2new('save2new');
			if (!empty($this->form->getValue('id'))) {
				JToolbarHelper::custom('relations', 'link', 'link', JText::_('COM_KA_TABLES_RELATIONS'), false);
			}
			JToolbarHelper::divider();
			JToolbarHelper::cancel();
		} else {
			JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_AWARDS_TITLE')), 'asterisk');

			if ($user->authorise('core.create.award', 'com_kinoarhiv')) {
				JToolbarHelper::addNew('add');
			}

			if ($user->authorise('core.edit.award', 'com_kinoarhiv')) {
				JToolbarHelper::editList('edit');
				JToolbarHelper::divider();
			}

			if ($user->authorise('core.edit.state.award', 'com_kinoarhiv')) {
				JToolbarHelper::publishList();
				JToolbarHelper::unpublishList();
			}

			if ($user->authorise('core.delete.award', 'com_kinoarhiv')) {
				JToolbarHelper::deleteList(JText::_('COM_KA_DELETE_SELECTED'), 'remove');
				JToolbarHelper::divider();
			}

			JToolbarHelper::custom('relations', 'link', 'link', JText::_('COM_KA_TABLES_RELATIONS'), false);
			JToolbarHelper::divider();

			if ($user->authorise('core.create.award', 'com_kinoarhiv') && $user->authorise('core.edit.award', 'com_kinoarhiv') && $user->authorise('core.edit.state.award', 'com_kinoarhiv')) {
				JHtml::_('bootstrap.modal', 'collapseModal');
				$title = JText::_('JTOOLBAR_BATCH');
				$layout = new JLayoutFile('joomla.toolbar.batch');

				$dhtml = $layout->render(array('title' => $title));
				JToolBar::getInstance('toolbar')->appendButton('Custom', $dhtml, 'batch');
			}
		}
	}

	protected function getSortFields() {
		return array(
			'a.state' => JText::_('JSTATUS'),
			'a.title' => JText::_('COM_KA_FIELD_AW_LABEL'),
			'language' => JText::_('JGRID_HEADING_LANGUAGE'),
			'a.id' => JText::_('JGRID_HEADING_ID')
		);
	}
}
