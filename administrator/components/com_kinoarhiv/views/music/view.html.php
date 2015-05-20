<?php defined('_JEXEC') or die;
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url			http://киноархив.com/
 */

class KinoarhivViewMusic extends JViewLegacy {
	protected $items;
	protected $pagination;
	protected $state;
	protected $form;

	public function display($tpl = null) {
		$app = JFactory::getApplication();
		$type = $app->input->get('type', 'albums', 'word');

		switch ($type) {
			case 'tracks': $this->displayTracks('tracks'); break;
			default: $this->displayAlbums('albums'); break;
		}
	}

	protected function displayAlbums($tpl) {
		$app = JFactory::getApplication();

		if ($app->input->get('task', '', 'cmd') == 'edit') {
			$this->edit(null);
			return;
		}

		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $this->get('Errors')), 500);
			return false;
		}

		if ($this->getLayout() !== 'modal') {
			$this->addToolbar();
		}

		parent::display($tpl);
	}

	protected function displayTracks($tpl) {
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $this->get('Errors')), 500);
			return false;
		}

		if ($this->getLayout() !== 'modal') {
			//$this->addToolbar();
		}

		parent::display($tpl);
	}

	protected function edit($tpl) {
		$form = $this->get('Form');
		$app = JFactory::getApplication();
		$this->params = JComponentHelper::getParams('com_kinoarhiv');

		$items = new JRegistry;

		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $this->get('Errors')), 500);
			return false;
		}

		if ($form->getValue('filename', 'album') == '') {
			$items->set(
				'poster',
				JURI::root().'components/com_kinoarhiv/assets/themes/component/'.$this->params->get('ka_theme').'/images/no_movie_cover.png'
			);
			$items->set(
				'th_poster',
				JURI::root().'components/com_kinoarhiv/assets/themes/component/'.$this->params->get('ka_theme').'/images/no_movie_cover.png'
			);
			$items->set('y_poster', '');
		} else {
			if (JString::substr($this->params->get('media_music_images_root_www'), 0, 1) == '/') {
				$items->set(
					'poster',
					JURI::root().JString::substr($this->params->get('media_music_images_root_www'), 1).'/'.JString::substr($form->getValue('fs_alias', 'album'), 0, 1).'/'.$form->getValue('id', 'album').'/'.$form->getValue('filename', 'album')
				);
				$items->set(
					'th_poster',
					JURI::root().JString::substr($this->params->get('media_music_images_root_www'), 1).'/'.JString::substr($form->getValue('fs_alias', 'album'), 0, 1).'/'.$form->getValue('id', 'album').'/thumb_'.$form->getValue('filename', 'album')
				);
			} else {
				$items->set(
					'poster',
					$this->params->get('media_music_images_root_www').'/'.JString::substr($form->getValue('fs_alias', 'album'), 0, 1).'/'.$form->getValue('id', 'album').'/'.$form->getValue('filename', 'album')
				);
				$items->set(
					'th_poster',
					$this->params->get('media_posters_root_www').'/'.JString::substr($form->getValue('fs_alias', 'album'), 0, 1).'/'.$form->getValue('id', 'album').'/thumb_'.$form->getValue('filename', 'album')
				);
			}
			$items->set('y_poster', 'y-poster');
		}

		$this->items = $items;
		$this->form = $form;
		$this->form_edit_group = 'album';
		$this->form_attribs_group = 'attribs';

		if ($this->getLayout() !== 'modal') {
			$this->addToolbar($app->input->get('task', '', 'cmd'));
		}

		parent::display('edit_album');
		$app->input->set('hidemainmenu', true);
	}

	protected function addToolbar($task='') {
		$app = JFactory::getApplication();
		$user = JFactory::getUser();

		if ($task == 'add') {
			/*JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MUSIC_TITLE').': '.JText::_('COM_KA_NEW')), 'play');
			JToolbarHelper::apply('apply');
			JToolbarHelper::save('save');
			JToolbarHelper::save2new('save2new');
			JToolbarHelper::divider();
			JToolbarHelper::cancel();*/
		} elseif ($task == 'edit') {
			if ($this->form->getValue('id', $this->form_edit_group) != 0) {
				JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MUSIC_TITLE').': '.JText::_('COM_KA_EDIT').': '.$this->form->getValue('title', $this->form_edit_group)), 'play');
			} else {
				JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MUSIC_TITLE').': '.JText::_('COM_KA_NEW')), 'play');
			}
			JToolbarHelper::apply('apply');
			JToolbarHelper::save('save');
			JToolbarHelper::save2new('save2new');
			JToolbarHelper::divider();
			JToolbarHelper::cancel();
		} else {
			if ($app->input->get('type', 'albums', 'word') == 'albums') {
				JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MUSIC_TITLE').': '.JText::_('COM_KA_MUSIC_ALBUMS_TITLE')), 'play');
			} elseif ($app->input->get('type', 'albums', 'word') == 'tracks') {
				JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MUSIC_TITLE').': '.JText::_('COM_KA_MUSIC_TRACKS_TITLE')), 'play');
			}

			if ($user->authorise('core.create', 'com_kinoarhiv')) {
				JToolbarHelper::addNew('add');
			}

			if ($user->authorise('core.edit', 'com_kinoarhiv')) {
				JToolbarHelper::editList('edit');
				JToolbarHelper::divider();
			}

			if ($user->authorise('core.edit.state', 'com_kinoarhiv')) {
				JToolbarHelper::publishList();
				JToolbarHelper::unpublishList();
			}

			if ($user->authorise('core.delete', 'com_kinoarhiv')) {
				JToolbarHelper::deleteList(JText::_('COM_KA_DELETE_SELECTED'), 'remove');
			}

			JToolbarHelper::divider();

			if ($user->authorise('core.create', 'com_kinoarhiv') && $user->authorise('core.edit', 'com_kinoarhiv') && $user->authorise('core.edit.state', 'com_kinoarhiv')) {
				JHtml::_('bootstrap.modal', 'collapseModal');
				$title = JText::_('JTOOLBAR_BATCH');
				$layout = new JLayoutFile('joomla.toolbar.batch');

				$dhtml = $layout->render(array('title' => $title));
				JToolBar::getInstance('toolbar')->appendButton('Custom', $dhtml, 'batch');
			}
		}
	}

	protected function getSortFields() {
		return array(
			'a.state' => JText::_('JSTATUS'),
			'a.title' => JText::_('COM_KA_MUSIC_ALBUMS_HEADING'),
			'a.access' => JText::_('JGRID_HEADING_ACCESS'),
			'language' => JText::_('JGRID_HEADING_LANGUAGE'),
			'a.id' => JText::_('JGRID_HEADING_ID')
		);
	}
}
