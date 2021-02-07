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

/**
 * View to edit an album.
 *
 * @since  3.0
 */
class KinoarhivViewAlbum extends JViewLegacy
{
	protected $items;

	/**
	 * The JForm object
	 *
	 * @var  JForm
	 *
	 * @since 3.0
	 */
	protected $form;

	protected $params;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function display($tpl = null)
	{
		switch (JFactory::getApplication()->input->get('task', '', 'cmd'))
		{
			case 'editAlbumAward':
				$this->editAlbumAward();
				break;
			case 'editAlbumCrew':
				$this->editAlbumCrew();
				break;
			case 'editAlbumRelease':
				$this->editAlbumRelease();
				break;
			case 'editTrack':
				$this->editTrack();
				break;
			default:
				$this->edit();
				break;
		}
	}

	/**
	 * Display the view for a album edit.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   3.1
	 */
	protected function edit()
	{
		jimport('components.com_kinoarhiv.helpers.content', JPATH_ROOT);

		$params = JComponentHelper::getParams('com_kinoarhiv');
		$form   = $this->get('Form');
		$item   = new StdClass;
		$errors = $this->get('Errors');

		if (count($errors))
		{
			throw new Exception(implode("\n", $this->get('Errors')), 500);
		}

		$_path = $form->getValue('covers_path_www');

		if (!empty($_path))
		{
			if (substr($_path, 0, 1) == '/')
			{
				$imgFolder = JUri::root() . substr($_path, 1) . '/';
			}
			else
			{
				$imgFolder = JUri::root() . $_path . '/';
			}
		}
		else
		{
			if (substr($params->get('media_music_images_root_www'), 0, 1) == '/')
			{
				$imgFolder = JUri::root() . substr($params->get('media_music_images_root_www'), 1) . '/'
					. urlencode($form->getValue('fs_alias')) . '/' . $form->getValue('id') . '/';
			}
			else
			{
				$imgFolder = $params->get('media_music_images_root_www') . '/' . urlencode($form->getValue('fs_alias'))
					. '/' . $form->getValue('id') . '/';
			}
		}

		if ($form->getValue('filename') == '')
		{
			$item->poster = JUri::root() . 'media/com_kinoarhiv/images/themes/' . $params->get('ka_theme') . '/no_album_cover.png';
			$item->th_poster = JUri::root() . 'media/com_kinoarhiv/images/themes/' . $params->get('ka_theme') . '/no_album_cover.png';
		}
		else
		{
			$item->poster = $imgFolder . $form->getValue('filename');
			$item->th_poster = $imgFolder . 'thumb_' . $form->getValue('filename');
		}

		$item->img_folder = $imgFolder;
		$this->item   = $item;
		$this->form   = $form;
		$this->params = $params;
		$this->lang   = JFactory::getLanguage();

		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar('edit');
		}

		parent::display();
		JFactory::getApplication()->input->set('hidemainmenu', true);
	}

	/**
	 * Display the layout for an award edit.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   3.1
	 */
	protected function editAlbumAward()
	{
		$this->form = $this->get('Form');
		$errors = $this->get('Errors');

		if (count($errors))
		{
			throw new Exception(implode("\n", $this->get('Errors')), 500);
		}

		if ($this->getLayout() !== 'modal')
		{
			JToolbarHelper::title(
				JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MUSIC_ALBUM_TITLE') . ': ' . JText::_('COM_KA_MOVIES_AW_LAYOUT_ADD_TITLE')),
				'play'
			);

			JToolbarHelper::apply('albums.saveAlbumAward');
			JToolbarHelper::divider();
			JToolbarHelper::cancel('cancel', 'JTOOLBAR_CLOSE');
		}

		echo JLayoutHelper::render('layouts.edit.relations', array('form' => $this->form), JPATH_COMPONENT_ADMINISTRATOR);

		JFactory::getApplication()->input->set('hidemainmenu', true);
	}

	/**
	 * Display the layout for a crew edit.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   3.1
	 */
	protected function editAlbumCrew()
	{
		$this->form = $this->get('Form');
		$errors = $this->get('Errors');

		if (count($errors))
		{
			throw new Exception(implode("\n", $this->get('Errors')), 500);
		}

		if ($this->getLayout() !== 'modal')
		{
			JToolbarHelper::title(
				JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MUSIC_ALBUM_TITLE') . ': ' . JText::_('COM_KA_MOVIES_NAMES_LAYOUT_ADD_FIELD_NAME')),
				'play'
			);

			JToolbarHelper::apply('albums.saveAlbumCrew');
			JToolbarHelper::divider();
			JToolbarHelper::cancel('cancel', 'JTOOLBAR_CLOSE');
		}

		echo JLayoutHelper::render('layouts.edit.relations', array('form' => $this->form), JPATH_COMPONENT_ADMINISTRATOR);

		JFactory::getApplication()->input->set('hidemainmenu', true);
	}

	/**
	 * Display the layout for a release edit.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   3.1
	 */
	protected function editAlbumRelease()
	{
		$this->form = $this->get('Form');
		$errors = $this->get('Errors');

		if (count($errors))
		{
			throw new Exception(implode("\n", $this->get('Errors')), 500);
		}

		if ($this->getLayout() !== 'modal')
		{
			$rowId = JFactory::getApplication()->input->getInt('row_id');
			$title = !empty($rowId) ? JText::_('COM_KA_MOVIES_RELEASE_LAYOUT_EDIT_TITLE') : JText::_('COM_KA_MOVIES_RELEASE_LAYOUT_ADD_TITLE');

			JToolbarHelper::title(
				JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MUSIC_ALBUM_TITLE') . ': ' . $title),
				'play'
			);

			JToolbarHelper::apply('albums.saveAlbumRelease');
			JToolbarHelper::divider();
			JToolbarHelper::cancel('cancel', 'JTOOLBAR_CLOSE');
		}

		echo JLayoutHelper::render('layouts.edit.relations', array('form' => $this->form), JPATH_COMPONENT_ADMINISTRATOR);

		JFactory::getApplication()->input->set('hidemainmenu', true);
	}

	/**
	 * Display the layout for a track edit.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   3.1
	 */
	protected function editTrack()
	{
		$this->form = $this->get('Form');
		$errors = $this->get('Errors');

		if (count($errors))
		{
			throw new Exception(implode("\n", $this->get('Errors')), 500);
		}

		if ($this->getLayout() !== 'modal')
		{
			JToolbarHelper::title(
				JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MUSIC_ALBUM_TITLE') . ': ' . JText::_('COM_KA_MOVIES_RELEASE_LAYOUT_EDIT_TITLE')),
				'play'
			);

			JToolbarHelper::apply('albums.saveAlbumTrack');
			JToolbarHelper::divider();
			JToolbarHelper::cancel('cancel', 'JTOOLBAR_CLOSE');
		}

		echo JLayoutHelper::render('layouts.edit.relations', array('form' => $this->form), JPATH_COMPONENT_ADMINISTRATOR);

		JFactory::getApplication()->input->set('hidemainmenu', true);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @param   string  $task  Task
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function addToolbar($task = '')
	{
		if ($task == 'edit')
		{
			if ($this->form->getValue('id') != 0)
			{
				JToolbarHelper::title(
					JText::sprintf(
						'COM_KINOARHIV',
						JText::_('COM_KA_MUSIC_ALBUM_TITLE') . ': ' . JText::_('COM_KA_EDIT') . ': ' . $this->form->getValue('title')
					),
					'play'
				);
				JToolbarHelper::apply('albums.apply');
				JToolbarHelper::save('albums.save');
				JToolbarHelper::save2new('albums.save2new');
				JToolbarHelper::divider();
				JToolbarHelper::cancel('albums.cancel', 'JTOOLBAR_CLOSE');
				JToolbarHelper::divider();
				JToolbarHelper::custom('gallery', 'picture', 'picture', JText::_('COM_KA_MOVIES_GALLERY'), false);
			}
			else
			{
				JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MUSIC_ALBUM_TITLE') . ': ' . JText::_('COM_KA_NEW')), 'play');
				JToolbarHelper::apply('albums.apply');
				JToolbarHelper::save('albums.save');
				JToolbarHelper::save2new('albums.save2new');
				JToolbarHelper::divider();
				JToolbarHelper::cancel('albums.cancel');
			}
		}
		else
		{
			JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MUSIC_ALBUM_TITLE') . ': ' . JText::_('COM_KA_NEW')), 'play');
			JToolbarHelper::apply('albums.apply');
			JToolbarHelper::save('albums.save');
			JToolbarHelper::save2new('albums.save2new');
			JToolbarHelper::divider();
			JToolbarHelper::cancel('albums.cancel');
		}
	}
}
