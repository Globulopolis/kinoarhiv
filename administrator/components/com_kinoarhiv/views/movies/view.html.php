<?php defined('_JEXEC') or die;
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url			http://киноархив.com/
 */

use Joomla\Registry\Registry;

class KinoarhivViewMovies extends JViewLegacy {
	protected $items;
	protected $pagination;
	protected $state;
	protected $form;
	protected $params;
	protected $form_edit_group;
	protected $form_attribs_group;

	public function display($tpl = null) {
		$app = JFactory::getApplication();
		$task = $app->input->get('task', '', 'cmd');

		switch ($task) {
			case 'add': $this->edit($tpl); break;
			case 'edit': $this->edit($tpl); break;
			default: $this->_display($tpl); break;
		}
	}

	protected function _display($tpl) {
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $this->get('Errors')), 500);
		}

		if ($this->getLayout() !== 'modal') {
			$this->addToolbar();
		}

		parent::display($tpl);
	}

	protected function edit($tpl) {
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');

		$items = new Registry;
		$form = $this->get('Form');

		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $this->get('Errors')), 500);
		}

		if ($form->getValue('filename', 'movie') == '') {
			$items->set(
				'poster',
				JURI::root().'components/com_kinoarhiv/assets/themes/component/'.$params->get('ka_theme').'/images/no_movie_cover.png'
			);
			$items->set(
				'th_poster',
				JURI::root().'components/com_kinoarhiv/assets/themes/component/'.$params->get('ka_theme').'/images/no_movie_cover.png'
			);
			$items->set('y_poster', '');
		} else {
			if (JString::substr($params->get('media_posters_root_www'), 0, 1) == '/') {
				$items->set(
					'poster',
					JURI::root().JString::substr($params->get('media_posters_root_www'), 1).'/'.JString::substr($form->getValue('alias', 'movie'), 0, 1).'/'.$form->getValue('id', 'movie').'/posters/'.$form->getValue('filename', 'movie')
				);
				$items->set(
					'th_poster',
					JURI::root().JString::substr($params->get('media_posters_root_www'), 1).'/'.JString::substr($form->getValue('alias', 'movie'), 0, 1).'/'.$form->getValue('id', 'movie').'/posters/thumb_'.$form->getValue('filename', 'movie')
				);
			} else {
				$items->set(
					'poster',
					$params->get('media_posters_root_www').'/'.JString::substr($form->getValue('alias', 'movie'), 0, 1).'/'.$form->getValue('id', 'movie').'/posters/'.$form->getValue('filename', 'movie')
				);
				$items->set(
					'th_poster',
					$params->get('media_posters_root_www').'/'.JString::substr($form->getValue('alias', 'movie'), 0, 1).'/'.$form->getValue('id', 'movie').'/posters/thumb_'.$form->getValue('filename', 'movie')
				);
			}
			$items->set('y_poster', 'y-poster');
		}

		$this->items = $items;
		$this->form = $form;
		$this->form_edit_group = 'movie';
		$this->form_attribs_group = 'attribs';
		$this->params = $params;

		if ($this->getLayout() !== 'modal') {
			$this->addToolbar($tpl);
		}

		parent::display('edit');
		$app->input->set('hidemainmenu', true);
	}

	protected function addToolbar($task='') {
		$user = JFactory::getUser();

		if ($task == 'add') {
			JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MOVIES_TITLE').': '.JText::_('COM_KA_NEW')), 'play');
			JToolbarHelper::apply('apply');
			JToolbarHelper::save('save');
			JToolbarHelper::save2new('save2new');
			JToolbarHelper::divider();
			JToolbarHelper::cancel();
		} elseif ($task == 'edit') {
			if ($this->form->getValue('id', $this->form_edit_group) != 0) {
				JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MOVIES_TITLE').': '.JText::_('COM_KA_EDIT').': '.$this->form->getValue('title', $this->form_edit_group)), 'play');
			} else {
				JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MOVIES_TITLE').': '.JText::_('COM_KA_NEW')), 'play');
			}
			JToolbarHelper::apply('apply');
			JToolbarHelper::save('save');
			JToolbarHelper::save2new('save2new');
			JToolbarHelper::divider();
			JToolbarHelper::cancel();
			JToolbarHelper::divider();

			if ($this->form->getValue('id', $this->form_edit_group) != 0) {
				JToolbarHelper::custom('gallery', 'picture', 'picture', JText::_('COM_KA_MOVIES_GALLERY'), false);
				JToolbarHelper::custom('trailers', 'camera', 'camera', JText::_('COM_KA_MOVIES_TRAILERS'), false);
				JToolbarHelper::custom('sounds', 'music', 'music', JText::_('COM_KA_MOVIES_SOUNDS'), false);
			}
		} else {
			JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MOVIES_TITLE')), 'play');
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
			JToolbarHelper::custom('menu', 'tools', 'tools', JText::_('COM_KA_TABLES_RELATIONS'), false);
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
			'a.title' => JText::_('COM_KA_FIELD_MOVIE_LABEL'),
			'a.access' => JText::_('JGRID_HEADING_ACCESS'),
			'language' => JText::_('JGRID_HEADING_LANGUAGE'),
			'a.id' => JText::_('JGRID_HEADING_ID')
		);
	}
}
