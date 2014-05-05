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

	protected function edit($tpl) {
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$lang = JFactory::getLanguage();

		$items = $this->get('Item');
		$form = $this->get('Form');

		$title = '';
		if (!empty($items['name']->name)) {
			$title .= $items['name']->name;
		}
		if (!empty($items['name']->name) && !empty($items['name']->latin_name)) {
			$title .= ' / ';
		}
		if (!empty($items['name']->latin_name)) {
			$title .= $items['name']->latin_name;
		}
		$items['name']->title = &$title;

		if (empty($items['name']->filename)) {
			$items['name']->poster = JURI::root().'components/com_kinoarhiv/assets/themes/component/'.$params->get('ka_theme').'/images/no_movie_cover.png';
			$items['name']->th_poster = JURI::root().'components/com_kinoarhiv/assets/themes/component/'.$params->get('ka_theme').'/images/no_movie_cover.png';
			$items['name']->y_poster = '';
		} else {
			if (JString::substr($params->get('media_posters_root_www'), 0, 1) == '/') {
				$items['name']->poster = JURI::root().JString::substr($params->get('media_posters_root_www'), 1).'/'.JString::substr($items['name']->alias, 0, 1).'/'.$items['name']->id.'/posters/'.$items['name']->filename;
				$items['name']->th_poster = JURI::root().JString::substr($params->get('media_posters_root_www'), 1).'/'.JString::substr($items['name']->alias, 0, 1).'/'.$items['name']->id.'/posters/thumb_'.$items['name']->filename;
			} else {
				$items['name']->poster = $params->get('media_posters_root_www').'/'.JString::substr($items['name']->alias, 0, 1).'/'.$items['name']->id.'/posters/'.$items['name']->filename;
				$items['name']->th_poster = $params->get('media_posters_root_www').'/'.JString::substr($items['name']->alias, 0, 1).'/'.$items['name']->id.'/posters/thumb_'.$items['name']->filename;
			}
			$items['name']->y_poster = 'y-poster';
		}

		$this->items = &$items['name'];
		$this->form = &$form;
		$this->form_edit_group = 'name';
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
			JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_NAMES_TITLE').': '.JText::_('COM_KA_NEW')), 'play');
			JToolbarHelper::apply('apply');
			JToolbarHelper::save('save');
			JToolbarHelper::save2new('save2new');
			JToolbarHelper::divider();
			JToolbarHelper::cancel();
		} elseif ($task == 'edit') {
			if (!empty($this->items->id)) {
				JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_NAMES_TITLE').': '.$this->items->title), 'play');
			} else {
				JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_NAMES_TITLE').': '.JText::_('COM_KA_NEW')), 'play');
			}
			JToolbarHelper::apply('apply');
			JToolbarHelper::save('save');
			JToolbarHelper::save2new('save2new');
			JToolbarHelper::divider();
			JToolbarHelper::cancel();
			JToolbarHelper::divider();
			JToolbarHelper::custom('gallery', 'picture', 'picture', JText::_('COM_KA_MOVIES_GALLERY'), false);
			JToolbarHelper::custom('sounds', 'music', 'music', JText::_('COM_KA_MOVIES_SOUNDS'), false);
		} else {
			JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_NAMES_TITLE')), 'users');
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
			'a.name' => JText::_('COM_KA_FIELD_NAME'),
			'a.latin_name' => JText::_('COM_KA_FIELD_NAME_LATIN'),
			'a.access' => JText::_('JGRID_HEADING_ACCESS'),
			'language' => JText::_('JGRID_HEADING_LANGUAGE'),
			'a.id' => JText::_('JGRID_HEADING_ID')
		);
	}
}
