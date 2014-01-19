<?php defined('_JEXEC') or die;

class KinoarhivViewReviews extends JViewLegacy {
	protected $items;
	protected $pagination;
	protected $state;
	protected $form;

	public function display($tpl = null) {
		$app = JFactory::getApplication();
		$task = $app->input->get('task', '', 'cmd');

		switch ($task) {
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

		$this->canEdit = $user->authorise('core.edit', 'com_kinoarhiv');
		$this->canEditState = $user->authorise('core.edit.state', 'com_kinoarhiv');

		$this->items = &$items;
		$this->pagination = &$pagination;
		$this->state = &$state;

		parent::display($tpl);
	}

	protected function edit($tpl) {
		$app = JFactory::getApplication();
		$items = $this->get('Item');
		$form = $this->get('Form');
		$lang = JFactory::getLanguage();

		$this->items = &$items;
		$this->form = &$form;
		$this->lang = &$lang;

		if ($this->getLayout() !== 'modal') {
			$this->addToolbar($tpl);
		}

		parent::display('edit');
		$app->input->set('hidemainmenu', true);
	}

	protected function addToolbar($task='') {
		$app = JFactory::getApplication();

		if ($task == 'edit') {
			JToolbarHelper::title(JText::_('COM_KA_REVIEWS_EDIT_TITLE'), 'comments-2');
			JToolbarHelper::apply('apply');
			JToolbarHelper::save('save');
			JToolbarHelper::divider();
			JToolbarHelper::cancel();
		} else {
			JToolbarHelper::title(JText::_('COM_KA_REVIEWS_TITLE'), 'comments-2');
			JToolbarHelper::editList('edit');
			JToolbarHelper::divider();
			JToolbarHelper::publishList();
			JToolbarHelper::unpublishList();
			JToolbarHelper::deleteList(JText::_('COM_KA_DELETE_SELECTED'), 'remove');
		}
	}

	protected function getSortFields() {
		return array(
			'a.state' => JText::_('JSTATUS'),
			'a.type' => JText::_('COM_KA_REVIEWS_FIELD_TYPE'),
			'a.r_datetime' => JText::_('JGLOBAL_SHOW_PUBLISH_DATE_LABEL'),
			'm.movie' => JText::_('COM_KA_FIELD_MOVIE_LABEL'),
			'u.username' => JText::_('COM_KA_REVIEWS_FIELD_USER'),
			'a.ip' => JText::_('COM_KA_REVIEWS_FIELD_USER_IP'),
			'a.id' => JText::_('JGRID_HEADING_ID')
		);
	}
}
