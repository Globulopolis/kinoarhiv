<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2018 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

/**
 * View class for a list of awards.
 *
 * @since  3.0
 */
class KinoarhivViewAwards extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	protected $form;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   3.0
	 */
	public function display($tpl = null)
	{
		$app = JFactory::getApplication();
		$task = $app->input->get('task', '', 'cmd');

		switch ($task)
		{
			case 'add':
				$this->edit($tpl);
				break;
			case 'edit':
				$this->edit($tpl);
				break;
			default:
				$this->listItems($tpl);
				break;
		}
	}

	protected function listItems($tpl)
	{
		$user = JFactory::getUser();

		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');
		$this->filterForm = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$errors = $this->get('Errors');

		if (count($errors))
		{
			throw new Exception(implode("\n", $this->get('Errors')), 500);
		}

		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();
		}

		$this->canEdit = $user->authorise('core.edit.award', 'com_kinoarhiv');
		$this->canEditState = $user->authorise('core.edit.state.award', 'com_kinoarhiv');

		parent::display($tpl);
	}

	protected function edit($tpl)
	{
		$app = JFactory::getApplication();
		$user = JFactory::getUser();

		if (!$user->authorise('core.create.award', 'com_kinoarhiv') && !$user->authorise('core.edit.award', 'com_kinoarhiv'))
		{
			throw new Exception(JText::_('COM_KA_NO_ACCESS_RIGHTS'), 403);
		}

		$this->form = $this->get('Form');

		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar($tpl);
		}

		parent::display('edit');
		$app->input->set('hidemainmenu', true);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @param   string  $task  Task(not a task from URL).
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	protected function addToolbar($task = '')
	{
		$user = JFactory::getUser();

		if ($task == 'add')
		{
			JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_AWARDS_TITLE') . ': ' . JText::_('COM_KA_NEW')), 'asterisk');
			JToolbarHelper::apply('awards.apply');
			JToolbarHelper::save('awards.save');
			JToolbarHelper::save2new('awards.save2new');
			JToolbarHelper::divider();
			JToolbarHelper::cancel('awards.cancel');
		}
		elseif ($task == 'edit')
		{
			JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_AWARDS_TITLE') . ': ' . $this->form->getValue('title')), 'asterisk');
			JToolbarHelper::apply('awards.apply');
			JToolbarHelper::save('awards.save');
			JToolbarHelper::save2new('awards.save2new');

			if ($this->form->getValue('id') != 0)
			{
				JToolbarHelper::custom('relations', 'link', 'link', JText::_('COM_KA_TABLES_RELATIONS'), false);
			}

			JToolbarHelper::divider();
			JToolbarHelper::cancel('awards.cancel');
		}
		else
		{
			JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_AWARDS_TITLE')), 'asterisk');

			if ($user->authorise('core.create.award', 'com_kinoarhiv'))
			{
				JToolbarHelper::addNew('awards.add');
			}

			if ($user->authorise('core.edit.award', 'com_kinoarhiv'))
			{
				JToolbarHelper::editList('awards.edit');
				JToolbarHelper::divider();
			}

			if ($user->authorise('core.edit.state.award', 'com_kinoarhiv'))
			{
				JToolbarHelper::publishList('awards.publish');
				JToolbarHelper::unpublishList('awards.unpublish');
			}

			if ($user->authorise('core.delete.award', 'com_kinoarhiv'))
			{
				JToolbarHelper::deleteList(JText::_('COM_KA_DELETE_SELECTED'), 'awards.remove');
				JToolbarHelper::divider();
			}

			JToolbarHelper::custom('relations', 'link', 'link', JText::_('COM_KA_TABLES_RELATIONS'), false);
			JToolbarHelper::divider();

			if ($user->authorise('core.create.award', 'com_kinoarhiv')
				&& $user->authorise('core.edit.award', 'com_kinoarhiv')
				&& $user->authorise('core.edit.state.award', 'com_kinoarhiv'))
			{
				$title = JText::_('JTOOLBAR_BATCH');
				$layout = new JLayoutFile('joomla.toolbar.batch');

				$dhtml = $layout->render(array('title' => $title));
				JToolbar::getInstance('toolbar')->appendButton('Custom', $dhtml, 'batch');
			}
		}
	}
}
