<?php defined('_JEXEC') or die;

class KinoarhivViewNames extends JViewLegacy {
	protected $items;
	protected $pagination;
	protected $state;
	protected $form;

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
		$items = $this->get('Items');
		$pagination = $this->get('Pagination');
		$state = $this->get('State');

		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $this->get('Errors')), 500);
			return false;
		}

		if ($this->getLayout() !== 'modal') {
			$this->addToolbar();
		}

		$this->items = &$items;
		$this->pagination = &$pagination;
		$this->state = &$state;

		parent::display($tpl);
	}

	protected function edit($tpl) {
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$lang = JFactory::getLanguage();

		$items = $this->get('Item');
		$form = $this->get('Form');

		if (empty($items['movie']->filename)) {
			$items['movie']->poster = JURI::root().'components/com_kinoarhiv/assets/themes/component/'.$params->get('ka_theme').'/images/no_movie_cover.png';
			$items['movie']->th_poster = JURI::root().'components/com_kinoarhiv/assets/themes/component/'.$params->get('ka_theme').'/images/no_movie_cover.png';
			$items['movie']->y_poster = '';
		} else {
			if (JString::substr($params->get('media_posters_root_www'), 0, 1) == '/') {
				$items['movie']->poster = JURI::root().JString::substr($params->get('media_posters_root_www'), 1).'/'.JString::substr($items['movie']->alias, 0, 1).'/'.$items['movie']->id.'/posters/'.$items['movie']->filename;
				$items['movie']->th_poster = JURI::root().JString::substr($params->get('media_posters_root_www'), 1).'/'.JString::substr($items['movie']->alias, 0, 1).'/'.$items['movie']->id.'/posters/thumb_'.$items['movie']->filename;
			} else {
				$items['movie']->poster = $params->get('media_posters_root_www').'/'.JString::substr($items['movie']->alias, 0, 1).'/'.$items['movie']->id.'/posters/'.$items['movie']->filename;
				$items['movie']->th_poster = $params->get('media_posters_root_www').'/'.JString::substr($items['movie']->alias, 0, 1).'/'.$items['movie']->id.'/posters/thumb_'.$items['movie']->filename;
			}
			$items['movie']->y_poster = 'y-poster';
		}

		$this->items = &$items['movie'];
		$this->form = &$form;
		$this->form_edit_group = 'movie';
		$this->form_attribs_group = 'attribs';
		$this->params = &$params;
		$this->lang = &$lang;

		if ($this->getLayout() !== 'modal') {
			$this->addToolbar($tpl);
		}

		parent::display('edit');
		$app->input->set('hidemainmenu', true);
	}

	protected function addToolbar($task='') {
		$app = JFactory::getApplication();
		$user = JFactory::getUser();

		if ($task == 'add') {
			JToolbarHelper::title(JText::_('COM_KA_MOVIES_ADD_TITLE'), 'play');
			JToolbarHelper::apply('apply');
			JToolbarHelper::save('save');
			JToolbarHelper::save2new('save2new');
			JToolbarHelper::divider();
			JToolbarHelper::cancel();
		} elseif ($task == 'edit') {
			if (!empty($this->items->id)) {
				JToolbarHelper::title(JText::sprintf(JText::_('COM_KA_MOVIES_EDIT_TITLE'), $this->items->title), 'play');
			} else {
				JToolbarHelper::title(JText::_('COM_KA_MOVIES_ADD_TITLE'), 'play');
			}
			JToolbarHelper::apply('apply');
			JToolbarHelper::save('save');
			JToolbarHelper::save2new('save2new');
			JToolbarHelper::divider();
			JToolbarHelper::cancel();
			JToolbarHelper::divider();
			JToolbarHelper::custom('gallery', 'picture', 'picture', JText::_('COM_KA_MOVIES_GALLERY'), false);
			JToolbarHelper::custom('trailers', 'camera', 'camera', JText::_('COM_KA_MOVIES_TRAILERS'), false);
			JToolbarHelper::custom('sounds', 'music', 'music', JText::_('COM_KA_MOVIES_SOUNDS'), false);
		} else {
			JToolbarHelper::title(JText::_('COM_KA_NAMES_TITLE'), 'users');
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
			JToolbarHelper::custom('menu', 'tools', 'tools', JText::_('COM_KA_COUNTRIES_RELATIONS_BUTTON_TITLE'), false);
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
