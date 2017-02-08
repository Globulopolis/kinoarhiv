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

	protected $form_edit_group;

	protected $form_attribs_group;

	protected $user;

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
		$this->user = JFactory::getUser();
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
				$this->_display($tpl);
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
	protected function _display($tpl)
	{
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');
		$this->filterForm = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		if (count($errors = $this->get('Errors')))
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
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   3.0
	 */
	protected function edit($tpl)
	{
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');

		$form = $this->get('Form');
		$items = new Registry;

		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $this->get('Errors')), 500);
		}

		// Build title
		$title = '';

		if ($form->getValue('name', 'name') != '')
		{
			$title .= $form->getValue('name', 'name');
		}

		if ($form->getValue('name', 'name') != '' && $form->getValue('latin_name', 'name') != '')
		{
			$title .= ' / ';
		}

		if ($form->getValue('latin_name', 'name') != '')
		{
			$title .= $form->getValue('latin_name', 'name');
		}

		$items->set('title', $title);

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
			if (substr($params->get('media_actor_photo_root_www'), 0, 1) == '/')
			{
				$items->set(
					'poster',
					JUri::root() . substr($params->get('media_actor_photo_root_www'), 1) . '/' . urlencode($form->getValue('fs_alias', 'name')) . '/' . $form->getValue('id', 'name') . '/photo/' . $form->getValue('filename', 'name')
				);
				$items->set(
					'th_poster',
					JUri::root() . substr($params->get('media_actor_photo_root_www'), 1) . '/' . urlencode($form->getValue('fs_alias', 'name')) . '/' . $form->getValue('id', 'name') . '/photo/thumb_' . $form->getValue('filename', 'name')
				);
			}
			else
			{
				$items->set(
					'poster',
					$params->get('media_actor_photo_root_www') . '/' . urlencode($form->getValue('fs_alias', 'name')) . '/' . $form->getValue('id', 'name') . '/photo/' . $form->getValue('filename', 'name')
				);
				$items->set(
					'th_poster',
					$params->get('media_actor_photo_root_www') . '/' . urlencode($form->getValue('fs_alias', 'name')) . '/' . $form->getValue('id', 'name') . '/photo/thumb_' . $form->getValue('filename', 'name')
				);
			}
		}

		$this->items = $items;
		$this->form = $form;
		$this->form_edit_group = 'name';
		$this->form_attribs_group = 'attribs';
		$this->params = $params;

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
	 * @since   3.0
	 */
	protected function addToolbar($task = '')
	{
		if ($task == 'add')
		{
			JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_NAMES_TITLE') . ': ' . JText::_('COM_KA_NEW')), 'play');
			JToolbarHelper::apply('names.apply');
			JToolbarHelper::save('names.save');
			JToolbarHelper::save2new('names.save2new');
			JToolbarHelper::divider();
			JToolbarHelper::cancel('names.cancel');
		}
		elseif ($task == 'edit')
		{
			if ($this->form->getValue('id', $this->form_edit_group) != 0)
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
		}
		else
		{
			JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_NAMES_TITLE')), 'users');

			if ($this->user->authorise('core.create', 'com_kinoarhiv'))
			{
				JToolbarHelper::addNew('names.add');
			}

			if ($this->user->authorise('core.edit', 'com_kinoarhiv'))
			{
				JToolbarHelper::editList('names.edit');
				JToolbarHelper::divider();
			}

			if ($this->user->authorise('core.edit.state', 'com_kinoarhiv'))
			{
				JToolbarHelper::publishList('names.publish');
				JToolbarHelper::unpublishList('names.unpublish');
			}

			if ($this->user->authorise('core.delete', 'com_kinoarhiv'))
			{
				JToolbarHelper::deleteList(JText::_('COM_KA_DELETE_SELECTED'), 'names.remove');
			}

			JToolbarHelper::divider();
			JToolbarHelper::custom('menu', 'tools', 'tools', JText::_('COM_KA_TABLES_RELATIONS'), false);
			JToolbarHelper::divider();

			if ($this->user->authorise('core.create', 'com_kinoarhiv')
				&& $this->user->authorise('core.edit', 'com_kinoarhiv')
				&& $this->user->authorise('core.edit.state', 'com_kinoarhiv'))
			{
				$title = JText::_('JTOOLBAR_BATCH');
				$layout = new JLayoutFile('joomla.toolbar.batch');

				$dhtml = $layout->render(array('title' => $title));
				JToolbar::getInstance('toolbar')->appendButton('Custom', $dhtml, 'batch');
			}
		}
	}
}
