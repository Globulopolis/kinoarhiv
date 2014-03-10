<?php defined('_JEXEC') or die;

class KinoarhivViewPremieres extends JViewLegacy {
	protected $items;
	protected $pagination;
	protected $state;
	protected $form;

	public function display($tpl = null) {
		$user = JFactory::getUser();

		$items = $this->get('Items');
		$pagination = $this->get('Pagination');
		$state = $this->get('State');

		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $this->get('Errors')), 500);
			return false;
		}

		$this->addToolbar();
		$this->canEdit = $user->authorise('core.edit', 'com_kinoarhiv');

		$this->items = &$items;
		$this->pagination = &$pagination;
		$this->state = &$state;

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

		if ($task == 'add') {
			JToolbarHelper::title(JText::_('COM_KA_PREMIERES_ADD_TITLE'), 'calendar');
			JToolbarHelper::apply('apply');
			JToolbarHelper::save('save');
			JToolbarHelper::save2new('save2new');
			JToolbarHelper::divider();
			JToolbarHelper::cancel();
		} elseif ($task == 'edit') {
			if (!empty($this->items->id)) {
				JToolbarHelper::title(JText::sprintf(JText::_('COM_KA_PREMIERES_EDIT_TITLE'), $this->items->title), 'calendar');
			} else {
				JToolbarHelper::title(JText::_('COM_KA_PREMIERES_ADD_TITLE'), 'calendar');
			}
			JToolbarHelper::apply('apply');
			JToolbarHelper::save('save');
			JToolbarHelper::save2new('save2new');
			JToolbarHelper::divider();
			JToolbarHelper::cancel();
		} else {
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
			}

			JToolbarHelper::divider();
			JToolbarHelper::custom('menu', 'tools', 'tools', JText::_('COM_KA_PREMIERES_TABLES_RELATIONS_TITLE'), false);
		}
	}

	protected function getSortFields() {
		return array(
			'p.premiere_date' => JText::_('COM_KA_FIELD_PREMIERE_DATE_LABEL'),
			'm.title' => JText::_('COM_KA_FIELD_MOVIE_LABEL'),
			'c.name' => JText::_('COM_KA_FIELD_PREMIERE_COUNTRY_LABEL'),
			'p.id' => JText::_('JGRID_HEADING_ID')
		);
	}
}
