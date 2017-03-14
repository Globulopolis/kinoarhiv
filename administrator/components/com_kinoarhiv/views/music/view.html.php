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

class KinoarhivViewMusic extends JViewLegacy
{
	protected $items;

	protected $item;

	protected $pagination;

	protected $state;

	protected $form;

	protected $params;

	protected $form_edit_group;

	protected $form_attribs_group;

	public function display($tpl = null)
	{
		$app = JFactory::getApplication();
		$type = $app->input->get('type', 'albums', 'word');

		switch ($type)
		{
			case 'tracks':
				$this->displayTracks();
				break;
			default:
				$this->displayAlbums();
				break;
		}
	}

	protected function displayAlbums()
	{
		$app = JFactory::getApplication();

		if ($app->input->get('task', '', 'cmd') == 'edit')
		{
			$this->edit(null);

			return;
		}

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

		parent::display('albums');
	}

	protected function displayTracks()
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
			// $this->addToolbar();
		}

		parent::display();
	}

	protected function edit()
	{
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
		$app = JFactory::getApplication();
		$this->params = JComponentHelper::getParams('com_kinoarhiv');
		$errors = $this->get('Errors');
		jimport('joomla.filesystem.folder');
		JLoader::register('KAMediaHelper', JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'media.php');

		$items = new Registry;

		if (count($errors))
		{
			throw new Exception(implode("\n", $this->get('Errors')), 500);
		}

		$posters = KAMediaHelper::getAlbumCover(
			(object) array(
				'id'              => $this->item['album']->id,
				'fs_alias'        => $this->item['album']->fs_alias,
				'filename'        => $this->item['album']->filename,
				'covers_path'     => $this->item['album']->covers_path,
				'covers_path_www' => $this->item['album']->covers_path_www,
				'cover_filename'  => $this->item['album']->cover_filename
			)
		);
		$items->set('poster', $posters['poster']);
		$items->set('th_poster', $posters['th_poster']);

		$this->items = $items;
		$this->form_edit_group = 'album';
		$this->form_attribs_group = 'attribs';

		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar($app->input->get('task', '', 'cmd'));
		}

		parent::display('edit_album');
		$app->input->set('hidemainmenu', true);
	}

	protected function addToolbar($task = '')
	{
		$app  = JFactory::getApplication();
		$user = JFactory::getUser();

		if ($task == 'add')
		{
			/*JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MUSIC_TITLE').': '.JText::_('COM_KA_NEW')), 'play');
			JToolbarHelper::apply('apply');
			JToolbarHelper::save('save');
			JToolbarHelper::save2new('save2new');
			JToolbarHelper::divider();
			JToolbarHelper::cancel();*/
		}
		elseif ($task == 'edit')
		{
			if ($this->form->getValue('id', $this->form_edit_group) != 0)
			{
				JToolbarHelper::title(
					JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MUSIC_TITLE') . ': ' . JText::_('COM_KA_EDIT') . ': ' . $this->form->getValue('title', $this->form_edit_group)),
					'play'
				);
			}
			else
			{
				JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MUSIC_TITLE') . ': ' . JText::_('COM_KA_NEW')), 'play');
			}

			JToolbarHelper::apply('music.apply');
			JToolbarHelper::save('music.save');
			JToolbarHelper::save2new('music.save2new');
			JToolbarHelper::divider();
			JToolbarHelper::cancel('music.cancel');
		}
		else
		{
			if ($app->input->get('type', 'albums', 'word') == 'albums')
			{
				JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MUSIC_TITLE') . ': ' . JText::_('COM_KA_MUSIC_ALBUMS_TITLE')), 'play');
			}
			elseif ($app->input->get('type', 'albums', 'word') == 'tracks')
			{
				JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MUSIC_TITLE') . ': ' . JText::_('COM_KA_MUSIC_TRACKS_TITLE')), 'play');
			}

			if ($user->authorise('core.create', 'com_kinoarhiv'))
			{
				JToolbarHelper::addNew('music.add');
			}

			if ($user->authorise('core.edit', 'com_kinoarhiv'))
			{
				JToolbarHelper::editList('music.edit');
				JToolbarHelper::divider();
			}

			if ($user->authorise('core.edit.state', 'com_kinoarhiv'))
			{
				JToolbarHelper::publishList('music.publish');
				JToolbarHelper::unpublishList('music.unpublish');
			}

			if ($user->authorise('core.delete', 'com_kinoarhiv'))
			{
				JToolbarHelper::deleteList(JText::_('COM_KA_DELETE_SELECTED'), 'music.remove');
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
