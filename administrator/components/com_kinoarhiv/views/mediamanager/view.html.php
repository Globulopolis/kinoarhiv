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
use Joomla\String\StringHelper;

/**
 * View class for a list of items such as images, trailers, soundtracks in mediamanager.
 *
 * @since  3.0
 */
class KinoarhivViewMediamanager extends JViewLegacy
{
	protected $item;

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
	 * @throws  Exception
	 *
	 * @since   3.0
	 */
	public function display($tpl = null)
	{
		$input   = JFactory::getApplication()->input;
		$section = $input->get('section', '', 'word');
		$type    = $input->get('type', '', 'word');
		$task    = $input->get('task', '', 'cmd');

		if ($section == 'movie' && $type == 'gallery')
		{
			$this->listMovieImages($tpl);
		}
		elseif ($section == 'movie' && $type == 'trailers')
		{
			if ($task == 'edit')
			{
				$this->editMovieTrailer();
			}
			else
			{
				$this->listMovieTrailers($tpl);
			}
		}
		elseif ($section == 'name' && $type == 'gallery')
		{
			$this->listNameImages($tpl);
		}
		else
		{
			throw new Exception('Wrong \'section\' ot \'type\' variables from URL', 500);
		}
	}

	/**
	 * Display the view for a movie images gallery
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   3.0
	 */
	protected function listMovieImages($tpl)
	{
		$app  = JFactory::getApplication();
		$tab  = $app->input->get('tab', 0, 'int');

		$this->params        = JComponentHelper::getParams('com_kinoarhiv');
		$items               = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$page_title          = $this->get('ItemTitle');

		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $this->get('Errors')), 500);
		}

		if ($tab == 1)
		{
			$path = $this->params->get('media_wallpapers_root');
			$path_www = $this->params->get('media_wallpapers_root_www');
			$folder = 'wallpapers';
		}
		elseif ($tab == 2)
		{
			$path = $this->params->get('media_posters_root');
			$path_www = $this->params->get('media_posters_root_www');
			$folder = 'posters';
		}
		elseif ($tab == 3)
		{
			$path = $this->params->get('media_scr_root');
			$path_www = $this->params->get('media_scr_root_www');
			$folder = 'screenshots';
		}
		else
		{
			throw new Exception('Wrong \'section\' ot \'type\' variables from URL', 500);
		}

		foreach ($items as $item)
		{
			$file_path = JPath::clean(
				$path . DIRECTORY_SEPARATOR . $item->fs_alias . DIRECTORY_SEPARATOR . $item->movie_id
				. DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR
			);
			$item->error = '';

			if (!file_exists($file_path . $item->filename))
			{
				$item->filepath = 'javascript:void(0);';
				$item->folderpath = '';
				$item->error .= JText::_('COM_KA_MOVIES_GALLERY_ERROR_FILENOTFOUND');
			}
			else
			{
				$item->folderpath = $file_path;

				if (StringHelper::substr($path_www, 0, 1) == '/')
				{
					$item->filepath = JUri::root() . StringHelper::substr($path_www, 1) . '/' . urlencode($item->fs_alias)
						. '/' . $item->movie_id . '/' . $folder . '/' . $item->filename;
				}
				else
				{
					$item->filepath = $path_www . '/' . urlencode($item->fs_alias) . '/' . $item->movie_id . '/' . $folder . '/' . $item->filename;
				}
			}

			if (!file_exists($file_path . 'thumb_' . $item->filename))
			{
				$item->th_filepath = '';
				$item->error .= JText::_('COM_KA_MOVIES_GALLERY_ERROR_THUMB_FILENOTFOUND');
			}
			else
			{
				if (StringHelper::substr($path_www, 0, 1) == '/')
				{
					$item->th_filepath = JUri::root() . StringHelper::substr($path_www, 1) . '/' . urlencode($item->fs_alias)
						. '/' . $item->movie_id . '/' . $folder . '/thumb_' . $item->filename;
				}
				else
				{
					$item->th_filepath = $path_www . '/' . urlencode($item->fs_alias) . '/' . $item->movie_id . '/' . $folder . '/thumb_' . $item->filename;
				}
			}
		}

		JToolbarHelper::title(
			JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MEDIAMANAGER') . ': ' . JText::_('COM_KA_MOVIES_GALLERY') . ' - ' . $page_title),
			'images'
		);

		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();
		}

		// Remove language filter as it is not needed in the image gallery.
		$languageXml = new SimpleXMLElement('<field name="language" type="hidden" default="*" />');
		$this->filterForm->setField($languageXml, 'filter', true);
		unset($this->activeFilters['language']);

		$this->items = $items;

		parent::display($tpl);
	}

	/**
	 * Display the view for a name images gallery
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   3.0
	 */
	protected function listNameImages($tpl)
	{
		$app  = JFactory::getApplication();
		$tab  = $app->input->get('tab', 0, 'int');

		$this->params        = JComponentHelper::getParams('com_kinoarhiv');
		$items               = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$page_title          = $this->get('ItemTitle');

		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $this->get('Errors')), 500);
		}

		if ($tab == 1)
		{
			$path = $this->params->get('media_actor_wallpapers_root');
			$path_www = $this->params->get('media_actor_wallpapers_root_www');
			$folder = 'wallpapers';
		}
		elseif ($tab == 2)
		{
			$path = $this->params->get('media_actor_posters_root');
			$path_www = $this->params->get('media_actor_posters_root_www');
			$folder = 'posters';
		}
		elseif ($tab == 3)
		{
			$path = $this->params->get('media_actor_photo_root');
			$path_www = $this->params->get('media_actor_photo_root_www');
			$folder = 'photo';
		}
		else
		{
			throw new Exception('Wrong \'section\' ot \'type\' variables from URL', 500);
		}

		foreach ($items as $item)
		{
			$file_path = JPath::clean(
				$path . DIRECTORY_SEPARATOR . $item->fs_alias . DIRECTORY_SEPARATOR . $item->name_id
				. DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR
			);
			$item->error = '';

			if (!file_exists($file_path . $item->filename))
			{
				$item->filepath = 'javascript:void(0);';
				$item->folderpath = '';
				$item->error .= JText::_('COM_KA_MOVIES_GALLERY_ERROR_FILENOTFOUND');
			}
			else
			{
				$item->folderpath = $file_path;

				if (StringHelper::substr($path_www, 0, 1) == '/')
				{
					$item->filepath = JUri::root() . StringHelper::substr($path_www, 1) . '/' . urlencode($item->fs_alias)
						. '/' . $item->name_id . '/' . $folder . '/' . $item->filename;
				}
				else
				{
					$item->filepath = $path_www . '/' . urlencode($item->fs_alias) . '/' . $item->name_id . '/' . $folder . '/' . $item->filename;
				}
			}

			if (!file_exists($file_path . 'thumb_' . $item->filename))
			{
				$item->th_filepath = '';
				$item->error .= JText::_('COM_KA_MOVIES_GALLERY_ERROR_THUMB_FILENOTFOUND');
			}
			else
			{
				if (StringHelper::substr($path_www, 0, 1) == '/')
				{
					$item->th_filepath = JUri::root() . StringHelper::substr($path_www, 1) . '/' . urlencode($item->fs_alias)
						. '/' . $item->name_id . '/' . $folder . '/thumb_' . $item->filename;
				}
				else
				{
					$item->th_filepath = $path_www . '/' . urlencode($item->fs_alias) . '/' . $item->name_id . '/' . $folder . '/thumb_' . $item->filename;
				}
			}
		}

		JToolbarHelper::title(
			JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MEDIAMANAGER') . ': ' . JText::_('COM_KA_MOVIES_GALLERY') . ' - ' . $page_title),
			'images'
		);

		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();
		}

		// Remove language filter as it is not needed in the image gallery.
		$languageXml = new SimpleXMLElement('<field name="language" type="hidden" default="*" />');
		$this->filterForm->setField($languageXml, 'filter', true);
		unset($this->activeFilters['language']);

		$this->items = $items;

		parent::display($tpl);
	}

	/**
	 * Display the view for a trailers list
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   3.0
	 */
	protected function listMovieTrailers($tpl)
	{
		$this->params        = JComponentHelper::getParams('com_kinoarhiv');
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$page_title          = $this->get('ItemTitle');

		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $this->get('Errors')), 500);
		}

		JToolbarHelper::title(
			JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MEDIAMANAGER') . ': ' . JText::_('COM_KA_MOVIES_TRAILERS') . ' - ' . $page_title),
			'images'
		);

		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();
		}

		parent::display($tpl);
	}

	/**
	 * Display the view for a trailer edit
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   3.0
	 */
	protected function editMovieTrailer()
	{
		JLoader::register('KALanguage', JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'language.php');

		$app = JFactory::getApplication();
		$user = JFactory::getUser();

		if (!$user->authorise('core.create', 'com_kinoarhiv') && !$user->authorise('core.edit', 'com_kinoarhiv'))
		{
			throw new Exception(JText::_('COM_KA_NO_ACCESS_RIGHTS'), 403);
		}

		$this->params = JComponentHelper::getParams('com_kinoarhiv');
		$this->form = $this->get('Form');
		$item = new Registry;
		$page_title = $this->get('ItemTitle');

		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $this->get('Errors')), 500);
		}

		JToolbarHelper::title(
			JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MEDIAMANAGER') . ': ' . JText::_('COM_KA_MOVIES_TRAILERS') . ' - ' . $page_title),
			'images'
		);

		if (!empty($item))
		{
			$movie_id = $app->input->get('id', 0, 'int');
			$screenshot = $this->form->getValue('screenshot');

			if (StringHelper::substr($this->params->get('media_trailers_root_www'), 0, 1) == '/')
			{
				$item->set(
					'screenshot_path_www',
					JUri::root() . StringHelper::substr($this->params->get('media_trailers_root_www'), 1) . '/'
					. urlencode($this->form->getValue('fs_alias')) . '/' . $movie_id . '/' . $screenshot
				);
				$item->set(
					'screenshot_folder_www',
					JUri::root() . StringHelper::substr($this->params->get('media_trailers_root_www'), 1) . '/'
					. urlencode($this->form->getValue('fs_alias')) . '/' . $movie_id . '/'
				);
			}
			else
			{
				$item->set(
					'screenshot_path_www',
					$this->params->get('media_trailers_root_www') . '/' . urlencode($this->form->getValue('fs_alias'))
					. '/' . $movie_id . '/' . $screenshot
				);
				$item->set(
					'screenshot_folder_www',
					$this->params->get('media_trailers_root_www') . '/' . urlencode($this->form->getValue('fs_alias'))
					. '/' . $movie_id . '/'
				);
			}

			$item->set(
				'screenshot_path',
				$this->params->get('media_trailers_root') . '/' . $this->form->getValue('fs_alias') . '/'
				. $movie_id . '/' . $screenshot
			);
			$item->set('subtitles_lang_list', KALanguage::listOfLanguages());
		}

		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();
		}

		$this->item = $item;

		parent::display('trailer_edit');
		$app->input->set('hidemainmenu', true);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	protected function addToolbar()
	{
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		$task = $app->input->get('task', '', 'cmd');
		$type = $app->input->get('type', '', 'cmd');

		if ($task == 'edit')
		{
			if ($type == 'trailers')
			{
				JToolbarHelper::apply('apply');
				JToolbarHelper::save('save');
				JToolbarHelper::save2new('save2new');
				JToolbarHelper::divider();
				JToolbarHelper::cancel();
			}
		}
		else
		{
			if ($type == 'gallery')
			{
				if ($user->authorise('core.create', 'com_kinoarhiv'))
				{
					JToolbarHelper::custom('upload', 'upload', 'upload', JText::_('JTOOLBAR_UPLOAD'), false);
					JToolbarHelper::custom('copyfrom', 'copy', 'copy', JText::_('JTOOLBAR_COPYFROM'), false);
					JToolbarHelper::divider();
				}

				if ($user->authorise('core.edit.state', 'com_kinoarhiv'))
				{
					JToolbarHelper::publishList();
					JToolbarHelper::unpublishList();
					JToolbarHelper::divider();
				}

				if ($user->authorise('core.delete', 'com_kinoarhiv'))
				{
					JToolbarHelper::deleteList(JText::_('COM_KA_DELETE_SELECTED'), 'remove');
				}
			}
			elseif ($type == 'trailers')
			{
				if ($user->authorise('core.create', 'com_kinoarhiv'))
				{
					JToolbarHelper::custom('add', 'new', 'new', JText::_('JTOOLBAR_NEW'), false);
					JToolbarHelper::editList('edit');
					JToolbarHelper::divider();
				}

				if ($user->authorise('core.edit.state', 'com_kinoarhiv'))
				{
					JToolbarHelper::publishList();
					JToolbarHelper::unpublishList();
					JToolbarHelper::divider();
				}

				if ($user->authorise('core.delete', 'com_kinoarhiv'))
				{
					JToolbarHelper::deleteList(JText::_('COM_KA_DELETE_SELECTED'), 'remove');
				}
			}
		}
	}
}
