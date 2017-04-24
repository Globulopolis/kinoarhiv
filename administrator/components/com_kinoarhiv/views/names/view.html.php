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

use Joomla\Registry\Registry;

/**
 * View class for a name.
 *
 * @since  3.0
 */
class KinoarhivViewNames extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	protected $form;

	protected $params;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
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
			case 'edit':
				$this->edit();
				break;
			case 'editNameAwards':
				$this->editNameAwards('edit_awards');
				break;
			default:
				$this->listItems($tpl);
				break;
		}
	}

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
	protected function listItems($tpl)
	{
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

		parent::display($tpl);
	}

	/**
	 * Display the view for a name edit.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   3.0
	 */
	protected function edit()
	{
		jimport('components.com_kinoarhiv.helpers.content', JPATH_ROOT);

		$app    = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$form   = $this->get('Form');
		$items  = new Registry;
		$errors = $this->get('Errors');

		if (count($errors))
		{
			throw new Exception(implode("\n", $this->get('Errors')), 500);
		}

		// Set title
		$items->set('title', KAContentHelper::formatItemTitle($form->getValue('name', 'name'), $form->getValue('latin_name', 'name')));

		if (substr($params->get('media_actor_photo_root_www'), 0, 1) == '/')
		{
			$img_folder = JUri::root() . substr($params->get('media_actor_photo_root_www'), 1) . '/'
				. urlencode($form->getValue('fs_alias', 'name')) . '/' . $form->getValue('id', 'name') . '/photo/';
		}
		else
		{
			$img_folder = $params->get('media_actor_photo_root_www') . '/' . urlencode($form->getValue('fs_alias', 'name'))
				. '/' . $form->getValue('id', 'name') . '/photo/';
		}

		if ($form->getValue('filename', 'name') == '')
		{
			$items->set(
				'poster',
				JUri::root() . 'components/com_kinoarhiv/assets/themes/component/' . $params->get('ka_theme') . '/images/no_movie_cover.png'
			);
			$items->set(
				'th_poster',
				JUri::root() . 'components/com_kinoarhiv/assets/themes/component/' . $params->get('ka_theme') . '/images/no_movie_cover.png'
			);
		}
		else
		{
			$items->set('poster', $img_folder . $form->getValue('filename', 'name'));
			$items->set('th_poster', $img_folder . 'thumb_' . $form->getValue('filename', 'name'));
		}

		$items->set('img_folder', $img_folder);
		$this->items = $items;
		$this->form = $form;
		$this->params = $params;

		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar('edit');
		}

		parent::display('edit');
		$app->input->set('hidemainmenu', true);
	}

	/**
	 * Display the view for an award edit.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   3.0
	 */
	protected function editNameAwards($tpl)
	{
		$this->form = $this->get('Form');
		$errors = $this->get('Errors');

		if (count($errors))
		{
			throw new Exception(implode("\n", $this->get('Errors')), 500);
		}

		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar('edit_awards');
		}

		parent::display($tpl);
		JFactory::getApplication()->input->set('hidemainmenu', true);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @param   string  $task  Task
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
			JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_NAMES_TITLE') . ': ' . JText::_('COM_KA_NEW')), 'play');
			JToolbarHelper::apply('names.apply');
			JToolbarHelper::save('names.save');
			JToolbarHelper::save2new('names.save2new');
			JToolbarHelper::divider();
			JToolbarHelper::cancel('names.cancel');
			JToolbarHelper::divider();
		}
		elseif ($task == 'edit')
		{
			if ($this->form->getValue('id', 'name') != 0)
			{
				JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_NAMES_TITLE') . ': ' . $this->items->get('title')), 'play');
			}
			else
			{
				JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_NAMES_TITLE') . ': ' . JText::_('COM_KA_NEW')), 'play');
			}

			JToolbarHelper::apply('names.apply');
			JToolbarHelper::save('names.save');
			JToolbarHelper::save2new('names.save2new');
			JToolbarHelper::divider();
			JToolbarHelper::cancel('names.cancel');
			JToolbarHelper::divider();
			JToolbarHelper::custom('gallery', 'picture', 'picture', JText::_('COM_KA_MOVIES_GALLERY'), false);

			$layout = new JLayoutFile('joomla.toolbar.modal');
			$dhtml = $layout->render(
				array('selector' => 'parserModal', 'text' => JText::_('COM_KA_PARSER_TOOLBAR_BUTTON'), 'icon' => 'database')
			);
			JToolbar::getInstance('toolbar')->appendButton('Custom', $dhtml, 'parser');
		}
		elseif ($task == 'edit_awards')
		{
			if ($this->form->getValue('id', 'award') != 0)
			{
				JToolbarHelper::title(
					JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_NAMES_TITLE') . ': ' . JText::_('COM_KA_MOVIES_AWARDS_LAYOUT_EDIT_TITLE')),
					'play'
				);
			}
			else
			{
				JToolbarHelper::title(
					JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_NAMES_TITLE') . ': ' . JText::_('COM_KA_MOVIES_AW_LAYOUT_ADD_TITLE')),
					'play'
				);
			}

			JToolbarHelper::apply('names.saveNameAwards');
			JToolbarHelper::divider();
			JToolbarHelper::cancel('names.cancel', 'JTOOLBAR_CLOSE');
		}
		else
		{
			JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_NAMES_TITLE')), 'users');

			if ($user->authorise('core.create', 'com_kinoarhiv'))
			{
				JToolbarHelper::addNew('names.add');
			}

			if ($user->authorise('core.edit', 'com_kinoarhiv'))
			{
				JToolbarHelper::editList('names.edit');
				JToolbarHelper::divider();
			}

			if ($user->authorise('core.edit.state', 'com_kinoarhiv'))
			{
				JToolbarHelper::publishList('names.publish');
				JToolbarHelper::unpublishList('names.unpublish');
			}

			if ($user->authorise('core.delete', 'com_kinoarhiv'))
			{
				JToolbarHelper::deleteList(JText::_('COM_KA_DELETE_SELECTED'), 'names.remove');
			}

			JToolbarHelper::divider();

			if ($user->authorise('core.create', 'com_kinoarhiv')
				&& $user->authorise('core.edit', 'com_kinoarhiv')
				&& $user->authorise('core.edit.state', 'com_kinoarhiv'))
			{
				$title = JText::_('JTOOLBAR_BATCH');
				$layout = new JLayoutFile('joomla.toolbar.batch');

				$dhtml = $layout->render(array('title' => $title));
				JToolbar::getInstance('toolbar')->appendButton('Custom', $dhtml, 'batch');
			}
		}
	}
}
