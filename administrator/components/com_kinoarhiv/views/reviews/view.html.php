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

class KinoarhivViewReviews extends JViewLegacy
{
	protected $items;

	protected $pagination;

	/**
	 * The model state
	 *
	 * @var  object
	 *
	 * @since 3.0
	 */
	protected $state;

	/**
	 * The JForm object
	 *
	 * @var  JForm
	 *
	 * @since 3.0
	 */
	protected $form;

	protected $user;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   3.0
	 */
	public function display($tpl = null)
	{
		$task       = JFactory::getApplication()->input->get('task', '', 'cmd');
		$this->user = JFactory::getUser();

		switch ($task)
		{
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
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$errors              = $this->get('Errors');

		if (count($errors))
		{
			throw new Exception(implode("\n", $this->get('Errors')), 500);
		}

		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();
		}

		$this->canCreate    = $this->user->authorise('core.create', 'com_kinoarhiv');
		$this->canEdit      = $this->user->authorise('core.edit', 'com_kinoarhiv');
		$this->canEditState = $this->user->authorise('core.edit.state', 'com_kinoarhiv');

		parent::display($tpl);
	}

	protected function edit($tpl)
	{
		$app = JFactory::getApplication();
		$this->form = $this->get('Form');

		if ($this->form === false)
		{
			return false;
		}

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
	 * @param   string  $task  Task
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar($task = '')
	{
		if ($task == 'edit')
		{
			JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_REVIEWS_FIELD_REVIEW') . ': ' . JText::_('COM_KA_EDIT')), 'comments-2');
			JToolbarHelper::apply('reviews.apply');
			JToolbarHelper::save('reviews.save');
			JToolbarHelper::cancel('reviews.cancel');
		}
		else
		{
			JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_REVIEWS_TITLE')), 'comments-2');

			if ($this->user->authorise('core.edit', 'com_kinoarhiv'))
			{
				JToolbarHelper::editList('reviews.edit');
			}

			if ($this->user->authorise('core.edit.state', 'com_kinoarhiv'))
			{
				JToolbarHelper::publishList('reviews.publish');
				JToolbarHelper::unpublishList('reviews.unpublish');

				if ($this->state->get('filter.published') != 2)
				{
					JToolbarHelper::archiveList('reviews.archive');
				}
			}

			if ($this->user->authorise('core.create', 'com_kinoarhiv')
				&& $this->user->authorise('core.edit', 'com_kinoarhiv')
				&& $this->user->authorise('core.edit.state', 'com_kinoarhiv'))
			{
				$title = JText::_('JTOOLBAR_BATCH');
				$layout = new JLayoutFile('joomla.toolbar.batch');

				$dhtml = $layout->render(array('title' => $title));
				JToolbar::getInstance('toolbar')->appendButton('Custom', $dhtml, 'batch');
			}

			if ($this->state->get('filter.published') == -2 && $this->user->authorise('core.delete', 'com_kinoarhiv'))
			{
				JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'reviews.remove', 'JTOOLBAR_EMPTY_TRASH');
			}
			elseif ($this->user->authorise('core.edit.state', 'com_kinoarhiv'))
			{
				JToolbarHelper::trash('reviews.trash');
			}
		}
	}
}
