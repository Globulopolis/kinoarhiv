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

class KinoarhivViewPremieres extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	protected $form;

	public function display($tpl = null)
	{
		$user = JFactory::getUser();

		if ($tpl == 'add' || $tpl == 'edit')
		{
			$this->edit($tpl);

			return;
		}

		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');
		$errors = $this->get('Errors');

		if (count($errors))
		{
			throw new Exception(implode("\n", $this->get('Errors')), 500);
		}

		$this->addToolbar();
		$this->canEdit = $user->authorise('core.edit', 'com_kinoarhiv');

		$this->filterForm = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$this->params = JComponentHelper::getParams('com_kinoarhiv');

		parent::display($tpl);
	}

	protected function edit($tpl)
	{
		$app = JFactory::getApplication();
		$user = JFactory::getUser();

		if (!$user->authorise('core.create', 'com_kinoarhiv') && !$user->authorise('core.edit', 'com_kinoarhiv'))
		{
			throw new Exception(JText::_('COM_KA_NO_ACCESS_RIGHTS'), 403);
		}

		$this->form = $this->get('Form');

		$this->addToolbar($tpl);
		$this->params = JComponentHelper::getParams('com_kinoarhiv');

		parent::display('edit');
		$app->input->set('hidemainmenu', true);
	}

	protected function addToolbar($task = '')
	{
		$user = JFactory::getUser();

		if ($task == 'add' || $task == 'edit')
		{
			if ($task == 'edit')
			{
				JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_PREMIERES_TITLE') . ': ' . JText::_('COM_KA_EDIT')), 'calendar');
			}
			else
			{
				JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_PREMIERES_TITLE') . ': ' . JText::_('COM_KA_NEW')), 'calendar');
			}

			JToolbarHelper::apply('premieres.apply');
			JToolbarHelper::save('premieres.save');
			JToolbarHelper::save2new('premieres.save2new');
			JToolbarHelper::divider();
			JToolbarHelper::cancel('premieres.cancel');
		}
		else
		{
			JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_PREMIERES_TITLE')), 'calendar');

			if ($user->authorise('core.create', 'com_kinoarhiv'))
			{
				JToolbarHelper::addNew('premieres.add');
			}

			if ($user->authorise('core.edit', 'com_kinoarhiv'))
			{
				JToolbarHelper::editList('premieres.edit');
				JToolbarHelper::divider();
			}

			if ($user->authorise('core.delete', 'com_kinoarhiv'))
			{
				JToolbarHelper::deleteList(JText::_('COM_KA_DELETE_SELECTED'), 'premieres.remove');
			}

			if ($user->authorise('core.create', 'com_kinoarhiv')
				&& $user->authorise('core.edit', 'com_kinoarhiv')
				&& $user->authorise('core.edit.state', 'com_kinoarhiv'))
			{
				$title = JText::_('JTOOLBAR_BATCH');
				$layout = new JLayoutFile('joomla.toolbar.batch');

				$dhtml = $layout->render(array('title' => $title));
				JToolbar::getInstance('toolbar')->appendButton('Custom', $dhtml, 'batch');
			}

			if ($user->authorise('core.admin', 'com_kinoarhiv') || $user->authorise('core.options', 'com_kinoarhiv'))
			{
				$uri = (string) JUri::getInstance();
				$return = urlencode(base64_encode($uri));

				// Add a button linking to config for component.
				JToolbar::getInstance('toolbar')->appendButton(
					'Link',
					'options',
					'JToolbar_Options',
					'index.php?option=com_kinoarhiv&amp;view=settings&amp;return=' . $return
				);
			}
		}
	}
}
