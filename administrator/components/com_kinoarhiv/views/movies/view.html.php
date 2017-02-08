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
 * View to edit a movie.
 *
 * @since  3.0
 */
class KinoarhivViewMovies extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	protected $form;

	protected $params;

	protected $form_edit_group;

	protected $form_attribs_group;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 *
	 * @since   1.6
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
		$this->params = JComponentHelper::getParams('com_kinoarhiv');
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

	protected function edit($tpl)
	{
		$this->user = JFactory::getUser();
		$this->params = JComponentHelper::getParams('com_kinoarhiv');
		$this->form = $this->get('Form');
		$items = new Registry;
		$errors = $this->get('Errors');

		if (count($errors))
		{
			throw new Exception(implode("\n", $this->get('Errors')), 500);
		}

		if ($this->form->getValue('filename', 'movie') == '')
		{
			$items->set(
				'poster',
				JUri::root() . 'components/com_kinoarhiv/assets/themes/component/' . $this->params->get('ka_theme') . '/images/no_movie_cover.png'
			);
			$items->set(
				'th_poster',
				JUri::root() . 'components/com_kinoarhiv/assets/themes/component/' . $this->params->get('ka_theme') . '/images/no_movie_cover.png'
			);
		}
		else
		{
			if (substr($this->params->get('media_posters_root_www'), 0, 1) == '/')
			{
				$items->set(
					'poster',
					JUri::root() . substr($this->params->get('media_posters_root_www'), 1) . '/' . urlencode($this->form->getValue('fs_alias', 'movie'))
						. '/' . $this->form->getValue('id', 'movie') . '/posters/' . $this->form->getValue('filename', 'movie')
				);
				$items->set(
					'th_poster',
					JUri::root() . substr($this->params->get('media_posters_root_www'), 1) . '/' . urlencode($this->form->getValue('fs_alias', 'movie'))
						. '/' . $this->form->getValue('id', 'movie') . '/posters/thumb_' . $this->form->getValue('filename', 'movie')
				);
			}
			else
			{
				$items->set(
					'poster',
					$this->params->get('media_posters_root_www') . '/' . urlencode($this->form->getValue('fs_alias', 'movie'))
						. '/' . $this->form->getValue('id', 'movie') . '/posters/' . $this->form->getValue('filename', 'movie')
				);
				$items->set(
					'th_poster',
					$this->params->get('media_posters_root_www') . '/' . urlencode($this->form->getValue('fs_alias', 'movie'))
						. '/' . $this->form->getValue('id', 'movie') . '/posters/thumb_' . $this->form->getValue('filename', 'movie')
				);
			}
		}

		$this->items = $items;
		$this->form_edit_group = 'movie';
		$this->form_attribs_group = 'attribs';

		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar($tpl);
		}

		parent::display('edit');
		JFactory::getApplication()->input->set('hidemainmenu', true);
	}

	protected function addToolbar($task = '')
	{
		$user = JFactory::getUser();

		if ($task == 'add')
		{
			JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MOVIES_TITLE') . ': ' . JText::_('COM_KA_NEW')), 'play');
			JToolbarHelper::apply('movies.apply');
			JToolbarHelper::save('movies.save');
			JToolbarHelper::save2new('movies.save2new');
			JToolbarHelper::divider();
			JToolbarHelper::cancel('movies.cancel');
		}
		elseif ($task == 'edit')
		{
			if ($this->form->getValue('id', $this->form_edit_group) != 0)
			{
				JToolbarHelper::title(
					JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MOVIES_TITLE') . ': ' . JText::_('COM_KA_EDIT') . ': ' . $this->form->getValue('title', $this->form_edit_group)),
					'play'
				);
			}
			else
			{
				JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MOVIES_TITLE') . ': ' . JText::_('COM_KA_NEW')), 'play');
			}

			JToolbarHelper::apply('movies.apply');
			JToolbarHelper::save('movies.save');
			JToolbarHelper::save2new('movies.save2new');
			JToolbarHelper::divider();
			JToolbarHelper::cancel('movies.cancel');
			JToolbarHelper::divider();

			if ($this->form->getValue('id', $this->form_edit_group) != 0)
			{
				JToolbarHelper::custom('gallery', 'picture', 'picture', JText::_('COM_KA_MOVIES_GALLERY'), false);
				JToolbarHelper::custom('trailers', 'camera', 'camera', JText::_('COM_KA_MOVIES_TRAILERS'), false);
				JToolbarHelper::custom('soundtracks', 'music', 'music', JText::_('COM_KA_MOVIES_SOUNDS'), false);
			}
		}
		else
		{
			JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MOVIES_TITLE')), 'play');

			if ($user->authorise('core.create', 'com_kinoarhiv'))
			{
				JToolbarHelper::addNew('movies.add');
			}

			if ($user->authorise('core.edit', 'com_kinoarhiv'))
			{
				JToolbarHelper::editList('movies.edit');
				JToolbarHelper::divider();
			}

			if ($user->authorise('core.edit.state', 'com_kinoarhiv'))
			{
				JToolbarHelper::publishList('movies.publish');
				JToolbarHelper::unpublishList('movies.unpublish');
			}

			if ($user->authorise('core.delete', 'com_kinoarhiv'))
			{
				JToolbarHelper::deleteList(JText::_('COM_KA_DELETE_SELECTED'), 'movies.remove');
			}

			JToolbarHelper::divider();
			JToolbarHelper::custom('menu', 'tools', 'tools', JText::_('COM_KA_TABLES_RELATIONS'), false);
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
