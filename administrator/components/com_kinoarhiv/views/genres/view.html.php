<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('_JEXEC') or die;

/**
 * View class for a list of genres.
 *
 * @since  3.0
 */
class KinoarhivViewGenres extends JViewLegacy
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

		$this->canEdit = $user->authorise('core.edit.genre', 'com_kinoarhiv');
		$this->canEditState = $user->authorise('core.edit.state.genre', 'com_kinoarhiv');
		$this->canUpdateStat = $user->authorise('core.recount.genre', 'com_kinoarhiv');

		parent::display($tpl);
	}

	protected function edit($tpl)
	{
		$app = JFactory::getApplication();
		$user = JFactory::getUser();

		if (!$user->authorise('core.create.genre', 'com_kinoarhiv') && !$user->authorise('core.edit.genre', 'com_kinoarhiv'))
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
		$app = JFactory::getApplication();
		$user = JFactory::getUser();

		if ($app->input->get('type', 'movie', 'word') == 'music')
		{
			$title = 'COM_KA_GENRES_MUSIC_TITLE';
		}
		else
		{
			$title = 'COM_KA_GENRES_TITLE';
		}

		if ($task == 'add')
		{
			JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_($title) . ': ' . JText::_('COM_KA_NEW')), 'smiley-2');
			JToolbarHelper::apply('genres.apply');
			JToolbarHelper::save('genres.save');
			JToolbarHelper::save2new('genres.save2new');
			JToolbarHelper::divider();
			JToolbarHelper::cancel('genres.cancel');
		}
		elseif ($task == 'edit')
		{
			JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_($title) . ': ' . $this->form->getValue('name')), 'smiley-2');
			JToolbarHelper::apply('genres.apply');
			JToolbarHelper::save('genres.save');
			JToolbarHelper::save2new('genres.save2new');

			if ($this->form->getValue('id') != 0)
			{
				JToolbarHelper::custom('relations', 'link', 'link', JText::_('COM_KA_TABLES_RELATIONS'), false);
			}

			JToolbarHelper::divider();
			JToolbarHelper::cancel('genres.cancel');
		}
		else
		{
			JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_($title)), 'smiley-2');

			if ($user->authorise('core.create.genre', 'com_kinoarhiv'))
			{
				JToolbarHelper::addNew('genres.add');
			}

			if ($user->authorise('core.edit.genre', 'com_kinoarhiv'))
			{
				JToolbarHelper::editList('genres.edit');
				JToolbarHelper::divider();
			}

			if ($user->authorise('core.edit.state.genre', 'com_kinoarhiv'))
			{
				JToolbarHelper::publishList('genres.publish');
				JToolbarHelper::unpublishList('genres.unpublish');
			}

			if ($user->authorise('core.delete.genre', 'com_kinoarhiv'))
			{
				JToolbarHelper::deleteList(JText::_('COM_KA_DELETE_SELECTED'), 'genres.remove');
				JToolbarHelper::divider();
			}

			if ($user->authorise('core.recount.genre', 'com_kinoarhiv'))
			{
				JToolbarHelper::custom('genres.updateStat', 'chart', 'chart', JText::_('COM_KA_GENRES_STATS_UPDATE'), true);
			}

			JToolbarHelper::custom('relations', 'link', 'link', JText::_('COM_KA_TABLES_RELATIONS'), false);
			JToolbarHelper::divider();

			if ($user->authorise('core.create.genre', 'com_kinoarhiv')
				&& $user->authorise('core.edit.genre', 'com_kinoarhiv')
				&& $user->authorise('core.edit.state.genre', 'com_kinoarhiv'))
			{
				$title = JText::_('JTOOLBAR_BATCH');
				$layout = new JLayoutFile('joomla.toolbar.batch');

				$dhtml = $layout->render(array('title' => $title));
				JToolbar::getInstance('toolbar')->appendButton('Custom', $dhtml, 'batch');
			}
		}
	}
}
