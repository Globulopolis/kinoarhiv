<?php defined('_JEXEC') or die;

class KinoarhivViewRelations extends JViewLegacy {
	protected $item;
	protected $pagination;
	protected $state;
	protected $form;
	protected $task = null;
	protected $user;

	public function display($tpl = null) {
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$this->task = $app->input->get('task', '', 'cmd');
		$this->id = $app->input->get('id', 0, 'int');
		$this->movie_id = $app->input->get('mid', 0, 'int');
		$this->name_id = $app->input->get('nid', 0, 'int');
		$this->user = &$user;

		switch ($this->task) {
			case 'countries':
				JToolbarHelper::custom('menu', 'tools', 'tools', JText::_('COM_KA_COUNTRIES_RELATIONS_BUTTON_TITLE'), false);
				$this->countries($tpl);
				break;
			case 'genres':
				JToolbarHelper::custom('menu', 'tools', 'tools', JText::_('COM_KA_COUNTRIES_RELATIONS_BUTTON_TITLE'), false);
				$this->genres($tpl);
				break;
			case 'awards':
				JToolbarHelper::custom('menu', 'tools', 'tools', JText::_('COM_KA_COUNTRIES_RELATIONS_BUTTON_TITLE'), false);
				$this->awards($tpl);
				break;
			case 'careers':
				JToolbarHelper::custom('menu', 'tools', 'tools', JText::_('COM_KA_COUNTRIES_RELATIONS_BUTTON_TITLE'), false);
				$this->careers($tpl);
				break;
			case 'add':
			case 'edit':
				$this->edit($tpl);
				break;
			default:
				JToolbarHelper::title(JText::_('COM_KINOARHIV_CP').': '.JText::_('COM_KA_COUNTRIES_RELATIONS_BUTTON_TITLE'), 'cpanel.png');
				JToolbarHelper::custom('menu', 'tools', 'tools', JText::_('COM_KA_COUNTRIES_RELATIONS_BUTTON_TITLE'), false);

				parent::display($tpl);
				break;
		}
	}

	protected function countries($tpl) {
		$app = JFactory::getApplication();
		$id = $app->input->get('id', 0, 'int');

		if (!empty($id)) {
			JToolbarHelper::title(JText::_('COM_KA_COUNTRIES_RELATIONS_TITLE').': ID '.$id, 'cpanel.png');
		} else {
			JToolbarHelper::title(JText::_('COM_KA_COUNTRIES_RELATIONS_TITLE'), 'cpanel.png');
		}

		JToolbarHelper::addNew('relations_add');
		JToolbarHelper::custom('relations_edit', 'edit', 'edit', JText::_('JTOOLBAR_EDIT'), false);
		JToolbarHelper::divider();
		JToolbarHelper::custom('relations_remove', 'delete', 'delete', JText::_('JTOOLBAR_REMOVE'), false);
		JToolbarHelper::divider();
		JToolbarHelper::custom('countries', 'tools', 'tools', JText::_('COM_KA_COUNTRIES_TITLE'), false);

		parent::display($tpl);
	}

	protected function genres($tpl) {
		$app = JFactory::getApplication();
		$id = $app->input->get('id', 0, 'int');

		if (!empty($id)) {
			JToolbarHelper::title(JText::_('COM_KA_GENRES_TABLES_RELATIONS_TITLE').': ID '.$id, 'cpanel.png');
		} else {
			JToolbarHelper::title(JText::_('COM_KA_GENRES_TABLES_RELATIONS_TITLE'), 'cpanel.png');
		}

		JToolbarHelper::addNew('relations_add');
		JToolbarHelper::custom('relations_edit', 'edit', 'edit', JText::_('JTOOLBAR_EDIT'), false);
		JToolbarHelper::divider();
		JToolbarHelper::custom('relations_remove', 'delete', 'delete', JText::_('JTOOLBAR_REMOVE'), false);
		JToolbarHelper::divider();
		JToolbarHelper::custom('genres', 'tools', 'tools', JText::_('COM_KA_GENRES_TITLE'), false);

		parent::display($tpl);
	}

	protected function awards($tpl) {
		$app = JFactory::getApplication();
		$id = $app->input->get('id', 0, 'int');
		$award_type = $app->input->get('award_type', 0, 'int');

		if ($award_type == 0) {
			$award_type_title = JText::_('COM_KA_AW_RELATION_TO_MOVIES');
		} elseif ($award_type == 1) {
			$award_type_title = JText::_('COM_KA_AW_RELATION_TO_NAMES');
		} else {
			$award_type_title = '';
		}

		if (!empty($id)) {
			JToolbarHelper::title(JText::_('COM_KA_AW_TABLES_RELATIONS_TITLE').$award_type_title.': ID '.$id, 'cpanel.png');
		} else {
			JToolbarHelper::title(JText::_('COM_KA_AW_TABLES_RELATIONS_TITLE').$award_type_title, 'cpanel.png');
		}

		JToolbarHelper::addNew('relations_add');
		JToolbarHelper::custom('relations_edit', 'edit', 'edit', JText::_('JTOOLBAR_EDIT'), false);
		JToolbarHelper::divider();
		JToolbarHelper::custom('relations_remove', 'delete', 'delete', JText::_('JTOOLBAR_REMOVE'), false);
		JToolbarHelper::divider();
		JToolbarHelper::custom('awards', 'tools', 'tools', JText::_('COM_KA_AW_TITLE'), false);

		$this->award_type = $award_type;

		parent::display($tpl);
	}

	protected function careers($tpl) {
		$app = JFactory::getApplication();
		$id = $app->input->get('id', 0, 'int');

		if (!empty($id)) {
			JToolbarHelper::title(JText::_('COM_KA_CAREER_RELATIONS_TITLE').': ID '.$id, 'cpanel.png');
		} else {
			JToolbarHelper::title(JText::_('COM_KA_CAREER_RELATIONS_TITLE'), 'cpanel.png');
		}

		JToolbarHelper::addNew('relations_add');
		JToolbarHelper::custom('relations_edit', 'edit', 'edit', JText::_('JTOOLBAR_EDIT'), false);
		JToolbarHelper::divider();
		JToolbarHelper::custom('relations_remove', 'delete', 'delete', JText::_('JTOOLBAR_REMOVE'), false);
		JToolbarHelper::divider();
		JToolbarHelper::custom('careers', 'tools', 'tools', JText::_('COM_KA_CAREER_TITLE'), false);

		parent::display($tpl);
	}

	protected function edit($tpl) {
		$app = JFactory::getApplication();
		$param = $app->input->get('param', '', 'cmd');

		JToolbarHelper::title(JText::_('COM_KA_'.strtoupper($param).'_RELATIONS_TITLE').': '.JText::_('COM_KA_EDIT'), 'cpanel.png');
		JToolbarHelper::apply('apply');
		JToolbarHelper::save('save');
		JToolbarHelper::save2new('save2new');
		JToolbarHelper::divider();
		JToolbarHelper::cancel();

		$params = JComponentHelper::getParams('com_kinoarhiv');
		$lang = JFactory::getLanguage();

		$item = $this->get('Item');
		$form = $this->get('Form');

		$this->item = &$item;
		$this->form = &$form;
		$this->params = &$params;
		$this->param = $param;
		$this->lang = &$lang;

		// We need a custom edit template for awards
		if ($param == 'awards') {
			$award_type = $app->input->get('award_type', 0, 'int');
			$this->award_type = $award_type;
			
			parent::display('relations_edit_awards');
			$app->input->set('hidemainmenu', true);
		} else {
			parent::display('relations_edit');
			$app->input->set('hidemainmenu', true);
		}
	}
}
