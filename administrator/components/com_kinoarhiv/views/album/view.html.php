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

use Joomla\Registry\Registry;

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
		$app  = JFactory::getApplication();
		$task = $app->input->get('task', '', 'cmd');

		switch ($task)
		{
			case 'editMovieCast':
				$this->editMovieCast($tpl);
				break;
			case 'editMovieAwards':
				$this->editMovieAwards($tpl);
				break;
			case 'editMoviePremieres':
				$this->editMoviePremieres($tpl);
				break;
			case 'editMovieReleases':
				$this->editMovieReleases($tpl);
				break;
			default:
				$this->edit();
				break;
		}
	}

	/**
	 * Display the view for a movie edit.
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

		$params = JComponentHelper::getParams('com_kinoarhiv');
		$form   = $this->get('Form');
		$items  = new Registry;
		$errors = $this->get('Errors');

		if (count($errors))
		{
			throw new Exception(implode("\n", $this->get('Errors')), 500);
		}

		/*if (substr($params->get('media_posters_root_www'), 0, 1) == '/')
		{
			$imgFolder = JUri::root() . substr($params->get('media_posters_root_www'), 1) . '/'
				. urlencode($form->getValue('fs_alias')) . '/' . $form->getValue('id') . '/posters/';
		}
		else
		{
			$imgFolder = $params->get('media_posters_root_www') . '/' . urlencode($form->getValue('fs_alias'))
				. '/' . $form->getValue('id') . '/posters/';
		}

		if ($form->getValue('filename') == '')
		{
			$items->set(
				'poster',
				JUri::root() . 'media/com_kinoarhiv/images/themes/' . $params->get('ka_theme') . '/no_movie_cover.png'
			);
			$items->set(
				'th_poster',
				JUri::root() . 'media/com_kinoarhiv/images/themes/' . $params->get('ka_theme') . '/no_movie_cover.png'
			);
		}
		else
		{
			$items->set('poster', $imgFolder . $form->getValue('filename'));
			$items->set('th_poster', $imgFolder . 'thumb_' . $form->getValue('filename'));
		}

		$items->set('img_folder', $imgFolder);*/
		$this->items  = $items;
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
					JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MUSIC_TITLE') . ': ' . JText::_('COM_KA_EDIT') . ': ' . $this->form->getValue('title')),
					'play'
				);
				JToolbarHelper::apply('albums.apply');
				JToolbarHelper::save('albums.save');
				JToolbarHelper::save2new('albums.save2new');
				JToolbarHelper::divider();
				JToolbarHelper::cancel('albums.cancel', 'JTOOLBAR_CLOSE');
				JToolbarHelper::divider();
				JToolbarHelper::custom('gallery', 'picture', 'picture', JText::_('COM_KA_MOVIES_GALLERY'), false);
				JToolbarHelper::custom('trailers', 'camera', 'camera', JText::_('COM_KA_MOVIES_TRAILERS'), false);
				JToolbarHelper::custom('soundtracks', 'music', 'music', JText::_('COM_KA_MOVIES_SOUNDS'), false);
			}
			else
			{
				JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MUSIC_TITLE') . ': ' . JText::_('COM_KA_NEW')), 'play');
				JToolbarHelper::apply('albums.apply');
				JToolbarHelper::save('albums.save');
				JToolbarHelper::save2new('albums.save2new');
				JToolbarHelper::divider();
				JToolbarHelper::cancel('albums.cancel');
			}
		}
		else
		{
			JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MUSIC_TITLE') . ': ' . JText::_('COM_KA_NEW')), 'play');
			JToolbarHelper::apply('albums.apply');
			JToolbarHelper::save('albums.save');
			JToolbarHelper::save2new('albums.save2new');
			JToolbarHelper::divider();
			JToolbarHelper::cancel('albums.cancel');
		}
	}
}
