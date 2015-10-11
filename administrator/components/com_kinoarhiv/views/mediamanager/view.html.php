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
use Joomla\String\String;

class KinoarhivViewMediamanager extends JViewLegacy
{
	protected $item;

	protected $items;

	protected $pagination;

	protected $state;

	protected $form;

	protected $params;

	public function display($tpl = null)
	{
		$app = JFactory::getApplication();
		$lang = JFactory::getLanguage();
		$this->params = JComponentHelper::getParams('com_kinoarhiv');

		$type = $app->input->get('type', '', 'word');
		$tab = $app->input->get('tab', 0, 'int');
		$section = $app->input->get('section', '', 'word');

		if ($section == 'movie')
		{
			if ($type == 'gallery')
			{
				$items = $this->get('Items');
				$this->pagination = $this->get('Pagination');
				$this->state = $this->get('State');
				$page_title = $this->get('ItemTitle');

				if (count($errors = $this->get('Errors')))
				{
					throw new Exception(implode("\n", $this->get('Errors')), 500);
				}

				JToolbarHelper::title(
					JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MEDIAMANAGER') . ': ' . JText::_('COM_KA_MOVIES_GALLERY') . ' - ' . $page_title),
					'images'
				);

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

				foreach ($items as $item)
				{
					$file_path = JPath::clean($path . DIRECTORY_SEPARATOR . $item->fs_alias . DIRECTORY_SEPARATOR . $item->movie_id . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR);
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

						if (String::substr($path_www, 0, 1) == '/')
						{
							$item->filepath = JURI::root() . String::substr($path_www, 1) . '/' . urlencode($item->fs_alias) . '/' . $item->movie_id . '/' . $folder . '/' . $item->filename;
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
						if (String::substr($path_www, 0, 1) == '/')
						{
							$item->th_filepath = JURI::root() . String::substr($path_www, 1) . '/' . urlencode($item->fs_alias) . '/' . $item->movie_id . '/' . $folder . '/thumb_' . $item->filename;
						}
						else
						{
							$item->th_filepath = $path_www . '/' . urlencode($item->fs_alias) . '/' . $item->movie_id . '/' . $folder . '/thumb_' . $item->filename;
						}
					}
				}

				$this->items = $items;
				$this->addToolbar();

				parent::display($tpl);
			}
			elseif ($type == 'trailers')
			{
				$this->addToolbar();

				if ($app->input->get('task', '', 'cmd') == 'edit')
				{
					JLoader::register('KALanguage', JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'language.php');

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

						if (String::substr($this->params->get('media_trailers_root_www'), 0, 1) == '/')
						{
							$item->set(
								'screenshot_path_www',
								JURI::root() . String::substr($this->params->get('media_trailers_root_www'), 1) . '/'
									. urlencode($this->form->getValue('fs_alias')) . '/' . $movie_id . '/' . $screenshot
							);
							$item->set(
								'screenshot_folder_www',
								JURI::root() . String::substr($this->params->get('media_trailers_root_www'), 1) . '/'
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

					$this->item = $item;

					parent::display('upload_trailer');
					$app->input->set('hidemainmenu', true);
				}
				else
				{
					$this->items = $this->get('Items');
					$this->pagination = $this->get('Pagination');
					$this->state = $this->get('State');
					$page_title = $this->get('ItemTitle');

					if (count($errors = $this->get('Errors')))
					{
						throw new Exception(implode("\n", $this->get('Errors')), 500);
					}

					JToolbarHelper::title(
						JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MEDIAMANAGER') . ': ' . $page_title . ' - ' . JText::_('COM_KA_MOVIES_TRAILERS')),
						'images'
					);

					parent::display($tpl);
				}
			}
		}
		elseif ($section == 'name')
		{
			if ($type == 'gallery')
			{
				$items = $this->get('Items');
				$this->pagination = $this->get('Pagination');
				$this->state = $this->get('State');
				$page_title = $this->get('ItemTitle');

				if (count($errors = $this->get('Errors')))
				{
					throw new Exception(implode("\n", $this->get('Errors')), 500);
				}

				JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MEDIAMANAGER') . ': ' . $page_title), 'images');

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

				foreach ($items as $item)
				{
					$file_path = JPath::clean($path . DIRECTORY_SEPARATOR . $item->fs_alias . DIRECTORY_SEPARATOR . $item->name_id . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR);
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

						if (String::substr($path_www, 0, 1) == '/')
						{
							$item->filepath = JURI::root() . String::substr($path_www, 1) . '/' . urlencode($item->fs_alias) . '/' . $item->name_id . '/' . $folder . '/' . $item->filename;
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
						if (String::substr($path_www, 0, 1) == '/')
						{
							$item->th_filepath = JURI::root() . String::substr($path_www, 1) . '/' . urlencode($item->fs_alias) . '/' . $item->name_id . '/' . $folder . '/thumb_' . $item->filename;
						}
						else
						{
							$item->th_filepath = $path_www . '/' . urlencode($item->fs_alias) . '/' . $item->name_id . '/' . $folder . '/thumb_' . $item->filename;
						}
					}
				}

				$this->items = $items;
				$this->addToolbar();

				parent::display($tpl);
			}
		}
	}

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

	protected function getSortFields()
	{
		$input = JFactory::getApplication()->input;
		$sort_fields = array();

		if ($input->get('type') == 'gallery')
		{
			$sort_fields = array(
				'g.filename'         => JText::_('COM_KA_MOVIES_GALLERY_HEADING_FILENAME'),
				'g.dimension'        => JText::_('COM_KA_MOVIES_GALLERY_HEADING_DIMENSION'),
				'g.poster_frontpage' => JText::_('COM_KA_MOVIES_GALLERY_HEADING_FRONTPAGE'),
				'g.state'            => JText::_('JSTATUS'),
				'g.id'               => JText::_('JGRID_HEADING_ID')
			);
		}
		elseif ($input->get('type') == 'trailers')
		{
			$sort_fields = array(
				'g.filename'  => JText::_('COM_KA_MOVIES_GALLERY_HEADING_FILENAME'),
				'g.access'    => JText::_('JGRID_HEADING_ACCESS'),
				'language'    => JText::_('JGRID_HEADING_LANGUAGE'),
				'g.frontpage' => JText::_('COM_KA_MOVIES_GALLERY_HEADING_FRONTPAGE'),
				'g.state'     => JText::_('JSTATUS'),
				'g.id'        => JText::_('JGRID_HEADING_ID')
			);
		}

		return $sort_fields;
	}
}
