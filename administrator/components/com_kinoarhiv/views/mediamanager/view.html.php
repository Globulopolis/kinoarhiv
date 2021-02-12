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
		$task    = $input->get('task', '');

		if (strpos($task, '.') !== false)
		{
			list ($type, $task) = explode('.', $task);
		}

		if ($section == 'movie' && $type == 'gallery')
		{
			$this->listMovieImages($tpl);
		}
		elseif ($section == 'movie' && $type == 'trailers')
		{
			if ($task == 'edit' || $task == 'add')
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
		elseif ($section == 'album' && $type == 'gallery')
		{
			$this->listAlbumImages($tpl);
		}
		else
		{
			throw new Exception('Wrong \'section\' or \'type\' variables from URL', 500);
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
		$app = JFactory::getApplication();
		$tab = $app->input->get('tab', 0, 'int');

		$this->params        = JComponentHelper::getParams('com_kinoarhiv');
		$items               = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$pageTitle           = $this->get('ItemTitle');
		$errors              = $this->get('Errors');

		if (count($errors))
		{
			throw new Exception(implode("\n", $this->get('Errors')), 500);
		}

		if ($tab == 1)
		{
			$path = $this->params->get('media_wallpapers_root');
			$pathWWW = $this->params->get('media_wallpapers_root_www');
			$folder = 'wallpapers';
		}
		elseif ($tab == 2)
		{
			$path = $this->params->get('media_posters_root');
			$pathWWW = $this->params->get('media_posters_root_www');
			$folder = 'posters';
		}
		elseif ($tab == 3)
		{
			$path = $this->params->get('media_scr_root');
			$pathWWW = $this->params->get('media_scr_root_www');
			$folder = 'screenshots';
		}
		else
		{
			throw new Exception('Wrong \'section\' ot \'type\' variables from URL', 500);
		}

		foreach ($items as $item)
		{
			$filePath = JPath::clean($path . '/' . $item->fs_alias . '/' . $item->movie_id . '/' . $folder . '/');
			$item->error = '';

			if (!is_file($filePath . $item->filename))
			{
				$item->filepath = 'javascript:void(0);';
				$item->folderpath = '';
				$item->error .= JText::_('COM_KA_MOVIES_GALLERY_ERROR_FILENOTFOUND');
			}
			else
			{
				$item->folderpath = $filePath;

				if (StringHelper::substr($pathWWW, 0, 1) == '/')
				{
					$item->filepath = JUri::root() . StringHelper::substr($pathWWW, 1) . '/' . urlencode($item->fs_alias)
						. '/' . $item->movie_id . '/' . $folder . '/' . $item->filename;
				}
				else
				{
					$item->filepath = $pathWWW . '/' . urlencode($item->fs_alias) . '/' . $item->movie_id . '/' . $folder . '/' . $item->filename;
				}
			}

			if (!is_file($filePath . 'thumb_' . $item->filename))
			{
				$item->th_filepath = '';
				$item->error .= JText::_('COM_KA_MOVIES_GALLERY_ERROR_THUMB_FILENOTFOUND');
			}
			else
			{
				if (StringHelper::substr($pathWWW, 0, 1) == '/')
				{
					$item->th_filepath = JUri::root() . StringHelper::substr($pathWWW, 1) . '/' . urlencode($item->fs_alias)
						. '/' . $item->movie_id . '/' . $folder . '/thumb_' . $item->filename;
				}
				else
				{
					$item->th_filepath = $pathWWW . '/' . urlencode($item->fs_alias) . '/' . $item->movie_id . '/'
						. $folder . '/thumb_' . $item->filename;
				}
			}
		}

		if ($this->getLayout() !== 'modal')
		{
			JToolbarHelper::title(
				JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MEDIAMANAGER') . ': ' . JText::_('COM_KA_MOVIES_GALLERY') . ' - ' . $pageTitle),
				'images'
			);

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
		$app = JFactory::getApplication();
		$tab = $app->input->get('tab', 0, 'int');

		$this->params        = JComponentHelper::getParams('com_kinoarhiv');
		$items               = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$pageTitle           = $this->get('ItemTitle');
		$errors              = $this->get('Errors');

		if (count($errors))
		{
			throw new Exception(implode("\n", $this->get('Errors')), 500);
		}

		if ($tab == 1)
		{
			$path = $this->params->get('media_actor_wallpapers_root');
			$pathWWW = $this->params->get('media_actor_wallpapers_root_www');
			$folder = 'wallpapers';
		}
		elseif ($tab == 2)
		{
			$path = $this->params->get('media_actor_posters_root');
			$pathWWW = $this->params->get('media_actor_posters_root_www');
			$folder = 'posters';
		}
		elseif ($tab == 3)
		{
			$path = $this->params->get('media_actor_photo_root');
			$pathWWW = $this->params->get('media_actor_photo_root_www');
			$folder = 'photo';
		}
		else
		{
			throw new Exception('Wrong \'section\' ot \'type\' variables from URL', 500);
		}

		foreach ($items as $item)
		{
			$filePath = JPath::clean($path . '/' . $item->fs_alias . '/' . $item->name_id . '/' . $folder . '/');
			$item->error = '';

			if (!is_file($filePath . $item->filename))
			{
				$item->filepath = 'javascript:void(0);';
				$item->folderpath = '';
				$item->error .= JText::_('COM_KA_MOVIES_GALLERY_ERROR_FILENOTFOUND');
			}
			else
			{
				$item->folderpath = $filePath;

				if (StringHelper::substr($pathWWW, 0, 1) == '/')
				{
					$item->filepath = JUri::root() . StringHelper::substr($pathWWW, 1) . '/' . urlencode($item->fs_alias)
						. '/' . $item->name_id . '/' . $folder . '/' . $item->filename;
				}
				else
				{
					$item->filepath = $pathWWW . '/' . urlencode($item->fs_alias) . '/' . $item->name_id . '/' . $folder . '/' . $item->filename;
				}
			}

			if (!is_file($filePath . 'thumb_' . $item->filename))
			{
				$item->th_filepath = '';
				$item->error .= JText::_('COM_KA_MOVIES_GALLERY_ERROR_THUMB_FILENOTFOUND');
			}
			else
			{
				if (StringHelper::substr($pathWWW, 0, 1) == '/')
				{
					$item->th_filepath = JUri::root() . StringHelper::substr($pathWWW, 1) . '/' . urlencode($item->fs_alias)
						. '/' . $item->name_id . '/' . $folder . '/thumb_' . $item->filename;
				}
				else
				{
					$item->th_filepath = $pathWWW . '/' . urlencode($item->fs_alias) . '/' . $item->name_id . '/'
						. $folder . '/thumb_' . $item->filename;
				}
			}
		}

		if ($this->getLayout() !== 'modal')
		{
			JToolbarHelper::title(
				JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MEDIAMANAGER') . ': ' . JText::_('COM_KA_MOVIES_GALLERY') . ' - ' . $pageTitle),
				'images'
			);

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
	 * Display the view for a album images gallery
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   3.0
	 */
	protected function listAlbumImages($tpl)
	{
		$app = JFactory::getApplication();
		$tab = $app->input->get('tab', 0, 'int');

		$this->params        = JComponentHelper::getParams('com_kinoarhiv');
		$items               = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$pageTitle           = $this->get('ItemTitle');
		$errors              = $this->get('Errors');

		if (count($errors))
		{
			throw new Exception(implode("\n", $this->get('Errors')), 500);
		}

		if ($tab == 0 || $tab == 1 || $tab == 2 || $tab == 3 || $tab == 4)
		{
			$path = $this->params->get('media_music_images_root');
			$pathWWW = $this->params->get('media_music_images_root_www');
		}
		else
		{
			throw new Exception('Wrong \'section\' ot \'type\' variables from URL', 500);
		}

		foreach ($items as $item)
		{
			if (!empty($item->covers_path))
			{
				$filePath = JPath::clean($item->covers_path . '/');
			}
			else
			{
				$filePath = JPath::clean($path . '/' . $item->fs_alias . '/' . $item->item_id . '/');
			}

			if ($item->covers_path_www !== '')
			{
				$pathWWW = $item->covers_path_www;
			}

			$item->error = '';

			if (!is_file($filePath . $item->filename))
			{
				$item->filepath = 'javascript:void(0);';
				$item->folderpath = '';
				$item->error .= JText::_('COM_KA_MOVIES_GALLERY_ERROR_FILENOTFOUND');
			}
			else
			{
				$item->folderpath = $filePath;

				if (StringHelper::substr($pathWWW, 0, 1) == '/')
				{
					if ($item->covers_path_www !== '')
					{
						$item->filepath = JUri::root() . '/' . $item->covers_path_www . '/' . $item->filename;
					}
					else
					{
						$item->filepath = JUri::root() . StringHelper::substr($pathWWW, 1) . '/'
							. urlencode($item->fs_alias) . '/' . $item->item_id . '/' . $item->filename;
					}
				}
				else
				{
					if ($item->covers_path_www !== '')
					{
						$item->filepath = JUri::root() . $item->covers_path_www . '/' . $item->filename;
					}
					else
					{
						$item->filepath = $pathWWW . '/' . urlencode($item->fs_alias) . '/' . $item->item_id . '/' . $item->filename;
					}
				}
			}

			if (!is_file($filePath . 'thumb_' . $item->filename))
			{
				$item->th_filepath = '';
				$item->error .= JText::_('COM_KA_MOVIES_GALLERY_ERROR_THUMB_FILENOTFOUND');
			}
			else
			{
				if (StringHelper::substr($pathWWW, 0, 1) == '/')
				{
					if ($item->covers_path_www !== '')
					{
						$item->th_filepath = JUri::root() . '/'. $item->covers_path_www . '/thumb_' . $item->filename;
					}
					else
					{
						$item->th_filepath = JUri::root() . StringHelper::substr($pathWWW, 1) . '/'
							. urlencode($item->fs_alias) . '/' . $item->item_id . '/thumb_' . $item->filename;
					}
				}
				else
				{
					if ($item->covers_path_www !== '')
					{
						$item->th_filepath = JUri::root() . '/' . $item->covers_path_www . '/thumb_' . $item->filename;
					}
					else
					{
						$item->th_filepath = $pathWWW . '/' . urlencode($item->fs_alias) . '/' . $item->item_id . '/thumb_' . $item->filename;
					}
				}
			}
		}

		if ($this->getLayout() !== 'modal')
		{
			JToolbarHelper::title(
				JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MEDIAMANAGER') . ': ' . JText::_('COM_KA_MOVIES_GALLERY') . ' - ' . $pageTitle),
				'images'
			);

			$this->addToolbar();

			$layout = new JLayoutFile('joomla.toolbar.batch');
			$dhtml = $layout->render(array('title' => JText::_('JTOOLBAR_BATCH')));
			JToolbar::getInstance()->appendButton('Custom', $dhtml, 'batch');

			$layout = new JLayoutFile('components.com_kinoarhiv.layouts.toolbar.import', JPATH_ADMINISTRATOR);
			$dhtml = $layout->render(array('title' => JText::_('JTOOLBAR_IMPORT')));
			JToolbar::getInstance()->appendButton('Custom', $dhtml, 'import');
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
		$pageTitle           = $this->get('ItemTitle');
		$errors              = $this->get('Errors');

		if (count($errors))
		{
			throw new Exception(implode("\n", $this->get('Errors')), 500);
		}

		JToolbarHelper::title(
			JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MEDIAMANAGER') . ': ' . JText::_('COM_KA_MOVIES_TRAILERS') . ' - ' . $pageTitle),
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
		jimport('administrator.components.com_kinoarhiv.libraries.language', JPATH_ROOT);

		$app = JFactory::getApplication();
		$user = JFactory::getUser();

		if (!$user->authorise('core.create', 'com_kinoarhiv') && !$user->authorise('core.edit', 'com_kinoarhiv'))
		{
			throw new Exception(JText::_('COM_KA_NO_ACCESS_RIGHTS'), 403);
		}

		$this->params          = JComponentHelper::getParams('com_kinoarhiv');
		$this->form            = $this->get('Form');
		$this->lang_list       = KALanguage::listOfLanguages();
		$this->folder_path     = '';
		$this->folder_path_www = '';
		$errors                = $this->get('Errors');

		if (count($errors))
		{
			throw new Exception(implode("\n", $this->get('Errors')), 500);
		}

		if ($this->form->getValue('trailer.item_id'))
		{
			// Path to a folder with file. E.g. /var/www/htdocs/etc...
			$this->folder_path = JPath::clean(
				$this->params->get('media_trailers_root') . '/' . $this->form->getValue('trailer.fs_alias')
					. '/' . $app->input->get('id', 0, 'int') . '/'
			);

			// URL to a screenshot
			if (StringHelper::substr($this->params->get('media_trailers_root_www'), 0, 1) == '/')
			{
				$this->folder_path_www = JUri::root() . StringHelper::substr($this->params->get('media_trailers_root_www'), 1)
					. '/' . urlencode($this->form->getValue('trailer.fs_alias')) . '/' . $app->input->get('id', 0, 'int') . '/';
			}
			else
			{
				$this->folder_path_www = $this->params->get('media_trailers_root_www') . '/' . urlencode($this->form->getValue('trailer.fs_alias'))
					. '/' . $app->input->get('id', 0, 'int') . '/';
			}

			JToolbarHelper::title(
				JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MEDIAMANAGER') . ': ' . JText::_('COM_KA_MOVIES_TRAILERS')
					. ': ' . $this->form->getValue('trailer.title')
				),
				'images'
			);
		}
		else
		{
			JToolbarHelper::title(
				JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MEDIAMANAGER') . ': ' . JText::_('COM_KA_MOVIES_TRAILERS')
					. ' - ' . JText::_('COM_KA_NEW')
				),
				'images'
			);
		}

		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();
		}

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
		$app  = JFactory::getApplication();
		$task = $app->input->get('task', '');
		$type = $app->input->get('type', '');

		if (strpos($task, '.') !== false)
		{
			list ($type, $task) = explode('.', $task);
		}

		if ($task == 'edit' || $task == 'add')
		{
			if ($type == 'trailers')
			{
				JToolbarHelper::apply('mediamanager.apply');
				JToolbarHelper::save('mediamanager.save');
				JToolbarHelper::save2new('mediamanager.save2new');
				JToolbarHelper::divider();
				JToolbarHelper::cancel('mediamanager.cancel');
			}
		}
		else
		{
			if ($type == 'gallery')
			{
				if ($user->authorise('core.create', 'com_kinoarhiv'))
				{
					JToolbarHelper::custom('mediamanager.upload', 'upload', 'upload', JText::_('JTOOLBAR_UPLOAD'), false);

					if ($app->input->get('section', '') !== 'album')
					{
						JToolbarHelper::custom('mediamanager.copyfrom', 'copy', 'copy', JText::_('JTOOLBAR_COPYFROM'), false);
					}

					JToolbarHelper::divider();
				}

				if ($user->authorise('core.edit.state', 'com_kinoarhiv'))
				{
					JToolbarHelper::publishList('mediamanager.publish');
					JToolbarHelper::unpublishList('mediamanager.unpublish');
					JToolbarHelper::divider();
				}

				if ($user->authorise('core.delete', 'com_kinoarhiv'))
				{
					JToolbarHelper::deleteList(JText::_('COM_KA_DELETE_SELECTED'), 'mediamanager.remove');
				}
			}
			elseif ($type == 'trailers')
			{
				if ($user->authorise('core.create', 'com_kinoarhiv'))
				{
					JToolbarHelper::custom('mediamanager.add', 'new', 'new', JText::_('JTOOLBAR_NEW'), false);
					JToolbarHelper::editList('mediamanager.edit');
					JToolbarHelper::divider();
				}

				if ($user->authorise('core.edit.state', 'com_kinoarhiv'))
				{
					JToolbarHelper::publishList('mediamanager.publish');
					JToolbarHelper::unpublishList('mediamanager.unpublish');
					JToolbarHelper::divider();
				}

				if ($user->authorise('core.delete', 'com_kinoarhiv'))
				{
					JToolbarHelper::deleteList(JText::_('COM_KA_DELETE_SELECTED'), 'mediamanager.remove');
					JToolbarHelper::divider();
				}

				if ($user->authorise('core.create', 'com_kinoarhiv')
					&& $user->authorise('core.edit', 'com_kinoarhiv')
					&& $user->authorise('core.edit.state', 'com_kinoarhiv'))
				{
					$title = JText::_('JTOOLBAR_BATCH');
					$layout = new JLayoutFile('joomla.toolbar.batch');

					$dhtml = $layout->render(array('title' => $title));
					JToolbar::getInstance()->appendButton('Custom', $dhtml, 'batch');
				}
			}

			if ($user->authorise('core.admin', 'com_kinoarhiv') || $user->authorise('core.options', 'com_kinoarhiv'))
			{
				$uri = (string) JUri::getInstance();
				$return = urlencode(base64_encode($uri));

				// Add a button linking to config for component.
				JToolbar::getInstance()->appendButton(
					'Link',
					'options',
					'JToolbar_Options',
					'index.php?option=com_kinoarhiv&amp;view=settings&amp;return=' . $return
				);
			}
		}
	}
}
