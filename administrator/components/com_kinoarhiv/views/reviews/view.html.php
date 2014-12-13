<?php defined('_JEXEC') or die;
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url			http://киноархив.com/
 */

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

		$this->canEdit = $user->authorise('core.edit', 'com_kinoarhiv');
		$this->canEditState = $user->authorise('core.edit.state', 'com_kinoarhiv');

		parent::display($tpl);
	}

	protected function edit($tpl) {
		$app = JFactory::getApplication();
		$this->form = $this->get('Form');

		if ($this->getLayout() !== 'modal') {
			$this->addToolbar($tpl);
		}

		parent::display('edit');
		$app->input->set('hidemainmenu', true);
	}

	protected function addToolbar($task='') {
		$user = JFactory::getUser();

		if ($task == 'edit') {
			JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_REVIEWS_FIELD_REVIEW').': '.JText::_('COM_KA_EDIT')), 'comments-2');
			JToolbarHelper::apply('apply');
			JToolbarHelper::save('save');
			JToolbarHelper::divider();
			JToolbarHelper::cancel();
		} else {
			JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_REVIEWS_TITLE')), 'comments-2');
			JToolbarHelper::editList('edit');
			JToolbarHelper::divider();
			JToolbarHelper::publishList();
			JToolbarHelper::unpublishList();
			JToolbarHelper::deleteList(JText::_('COM_KA_DELETE_SELECTED'), 'remove');
			JToolbarHelper::divider();

			if ($user->authorise('core.create', 'com_kinoarhiv') && $user->authorise('core.edit', 'com_kinoarhiv') && $user->authorise('core.edit.state', 'com_kinoarhiv')) {
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
			'a.type' => JText::_('COM_KA_REVIEWS_FIELD_TYPE'),
			'a.created' => JText::_('JGLOBAL_SHOW_PUBLISH_DATE_LABEL'),
			'm.movie' => JText::_('COM_KA_FIELD_MOVIE_LABEL'),
			'u.username' => JText::_('COM_KA_REVIEWS_FIELD_USER'),
			'a.ip' => JText::_('COM_KA_REVIEWS_FIELD_USER_IP'),
			'a.id' => JText::_('JGRID_HEADING_ID')
		);
	}
}
