<?php defined('_JEXEC') or die;

JLoader::register('DatabaseHelper', JPATH_COMPONENT.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'database.php');

class KinoarhivModelNames extends JModelList {
	public function __construct($config = array()) {
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.id',
				'name', 'a.name',
				'latin_name', 'a.latin_name',
				'alias', 'a.alias',
				'state', 'a.state',
				'access', 'a.access', 'access_level',
				'ordering', 'a.ordering',
				'language', 'a.language');
		}

		parent::__construct($config);
	}

	protected function loadForm($name, $source = null, $options = array(), $clear = false, $xpath = false) {
		// Handle the optional arguments.
		$options['control'] = JArrayHelper::getValue($options, 'control', false);

		// Create a signature hash.
		$hash = md5($source . serialize($options));

		// Check if we can use a previously loaded form.
		if (isset($this->_forms[$hash]) && !$clear) {
			return $this->_forms[$hash];
		}

		// Get the form.
		JForm::addFormPath(JPATH_COMPONENT . '/models/forms');
		JForm::addFieldPath(JPATH_COMPONENT . '/models/fields');

		try {
			$form = JForm::getInstance($name, $source, $options, false, $xpath);

			if (isset($options['load_data']) && $options['load_data']) {
				// Get the data for the form.
				$data = $this->loadFormData();
			} else {
				$data = array();
			}

			// Allow for additional modification of the form, and events to be triggered.
			// We pass the data because plugins may require it.
			$this->preprocessForm($form, $data);

			// Load the data into the form after the plugins have operated.
			$form->bind($data);

		} catch (Exception $e) {
			$this->setError($e->getMessage());
			return false;
		}

		// Store the form for later.
		$this->_forms[$hash] = $form;

		return $form;
	}

	protected function preprocessForm(JForm $form, $data, $group = 'content') {
		// Import the appropriate plugin group.
		JPluginHelper::importPlugin($group);

		// Get the dispatcher.
		$dispatcher = JEventDispatcher::getInstance();

		// Trigger the form preparation event.
		$results = $dispatcher->trigger('onContentPrepareForm', array($form, $data));

		// Check for errors encountered while preparing the form.
		if (count($results) && in_array(false, $results, true)) {
			// Get the last error.
			$error = $dispatcher->getError();

			if (!($error instanceof Exception)) {
				throw new Exception($error);
			}
		}
	}

	public function getForm($data = array(), $loadData = true) {
		$form = $this->loadForm('com_kinoarhiv.name', 'name', array('control' => 'form', 'load_data' => $loadData));

		if (empty($form)) {
			return false;
		}

		$input = JFactory::getApplication()->input;
		$ids = $input->get('id', array(), 'array');
		$id = (isset($id[0]) && !empty($id[0])) ? $id[0] : 0;
		$user = JFactory::getUser();

		if ($id != 0 && (!$user->authorise('core.edit.state', 'com_kinoarhiv.name.' . (int) $id)) || ($id == 0 && !$user->authorise('core.edit.state', 'com_kinoarhiv'))) {
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('state', 'disabled', 'true');
		}

		return $form;
	}

	protected function loadFormData() {
		$app = JFactory::getApplication();
		$data = $app->getUserState('com_kinoarhiv.edit.name.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}

	protected function populateState($ordering = null, $direction = null) {
		$app = JFactory::getApplication();

		// Adjust the context to support modal layouts.
		if ($layout = $app->input->get('layout')) {
			$this->context .= '.' . $layout;
		}

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', 0, 'int');
		$this->setState('filter.access', $access);

		$level = $this->getUserStateFromRequest($this->context . '.filter.level', 'filter_level', 0, 'int');
		$this->setState('filter.level', $level);

		$language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		// force a language
		$forcedLanguage = $app->input->get('forcedLanguage');
		if (!empty($forcedLanguage))
		{
			$this->setState('filter.language', $forcedLanguage);
			$this->setState('filter.forcedLanguage', $forcedLanguage);
		}

		// List state information.
		parent::populateState('a.name', 'asc');
	}

	protected function getStoreId($id = '') {
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.access');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.language');

		return parent::getStoreId($id);
	}

	protected function getListQuery() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$user = JFactory::getUser();
		$query = $db->getQuery(true);
		$task = $app->input->get('task', '', 'cmd');

		$query->select(
			$this->getState(
				'list.select',
				'a.id, a.name, a.latin_name, a.date_of_birth, a.date_of_death, a.birthplace, a.birthcountry, a.alias, a.state, a.access, a.ordering, a.language'
			)
		);
		$query->from('#__ka_names AS a');

		// Join over the language
		$query->select(' l.title AS language_title')
			->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = a.language');

		// Join over the asset groups.
		$query->select(' ag.title AS access_level')
			->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

		// Filter by access level.
		if ($access = $this->getState('filter.access')) {
			$query->where('a.access = ' . (int) $access);
		}

		// Implement View Level Access
		if (!$user->authorise('core.admin')) {
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN (' . $groups . ')');
		}

		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('a.state = ' . (int) $published);
		} elseif ($published === '') {
			$query->where('(a.state = 0 OR a.state = 1)');
		}

		// Filter by search in title.
		/*$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = ' . (int) substr($search, 3));
			} elseif (stripos($search, 'cdate:') === 0) {
				$search = trim(substr($search, 6));
				$search = $db->quote('%' . $db->escape($search, true) . '%');
				$query->where('a.created LIKE ' . $search);
			} elseif (stripos($search, 'mdate:') === 0) {
				$search = trim(substr($search, 6));
				$search = $db->quote('%' . $db->escape($search, true) . '%');
				$query->where('a.modified LIKE ' . $search);
			} elseif (stripos($search, 'access:') === 0) {
				$search = trim(substr($search, 7));

				if (is_numeric($search)) {
					$query->where('a.access = '.(int)$search);
				} else {
					$search = $db->quote('%' . $db->escape($search, true) . '%');
					$query->where('ag.title LIKE ' . $search);
				}
			} else {
				$search = $db->quote('%' . $db->escape($search, true) . '%');
				$query->where('(a.title LIKE ' . $search . ' OR a.alias LIKE ' . $search . ')');
			}
		}*/

		// Add the list ordering clause.
		/*$orderCol = $this->state->get('list.ordering', 'a.title');
		$orderDirn = $this->state->get('list.direction', 'asc');
		if ($orderCol == 'a.ordering') {
			$orderCol = 'a.title ' . $orderDirn . ', a.ordering';
		}
		//sqlsrv change
		if ($orderCol == 'language') {
			$orderCol = 'l.title';
		}
		if ($orderCol == 'access_level') {
			$orderCol = 'ag.title';
		}
		$query->order($db->escape($orderCol . ' ' . $orderDirn));*/

		return $query;
	}

	public function getItems() {
		$items = parent::getItems();
		$app = JFactory::getApplication();

		if ($app->isSite()) {
			$user = JFactory::getUser();
			$groups = $user->getAuthorisedViewLevels();

			for ($x = 0, $count = count($items); $x < $count; $x++) {
				//Check the access level. Remove articles the user shouldn't see
				if (!in_array($items[$x]->access, $groups)) {
					unset($items[$x]);
				}
			}
		}

		return $items;
	}

	public function quickSave() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();

		// We need set alias for quick save on movie page
		$name = 'n_name';
		$latin_name = 'n_latin_name';
		$date_of_birth = 'n_date_of_birth';
		$ordering = 'n_ordering';
		$language = 'n_language';

		$data = $app->input->getArray(array(
			'form'=>array(
				$name=>'string', $latin_name=>'string', $date_of_birth=>'string', $ordering=>'int', $language=>'string'
			)
		));
		$name = $data['form']['n_name'];
		$latin_name = $data['form']['n_latin_name'];
		$alias = $name != '' ? $name : $latin_name;
		$date_of_birth = (empty($data['form']['n_date_of_birth']) && $data['form']['n_date_of_birth'] == '0000-00-00') ? date('Y-m-d') : $data['form']['n_date_of_birth'];
		$ordering = empty($data['form']['n_ordering']) ? 0 : $data['form']['n_ordering'];
		$metadata = json_encode(array('tags'=>array(), 'robots'=>''));
		$language = empty($data['form']['n_language']) ? '*' : $data['form']['n_language'];

		if (empty($name) && empty($latin_name)) {
			return array('success'=>false, 'message'=>JText::_('COM_KA_REQUIRED'));
		}

		$db->setQuery("INSERT INTO ".$db->quoteName('#__ka_names')." (`id`, `asset_id`, `name`, `latin_name`, `alias`, `url_photo`, "
			. "\n `date_of_birth`, `date_of_death`, `birthplace`, `birthcountry`, `gender`, `height`, `desc`, `ordering`, `state`, "
			. "\n `access`, `metakey`, `metadesc`, `metadata`, `language`)"
			. "\n VALUES ('', '0', '".$db->escape($name)."', '".$db->escape($latin_name)."', '".JFilterOutput::stringURLSafe($alias)."', '', "
			. "\n '".$date_of_birth."', '', '', '', '', '', '', '".(int)$ordering."', '1', '1', '', '', '".$metadata."', '".$language."')");
		$query = $db->execute();

		if ($query !== true) {
			return array('success'=>false, 'message'=>JText::_('JERROR_AN_ERROR_HAS_OCCURRED'));
		} else {
			$insertid = $db->insertid();
			$rules = json_encode((object)array());

			$db->setQuery("SELECT MAX(`rgt`) + 1 FROM ".$db->quoteName('#__assets'));
			$lft = $db->loadResult();

			$db->setQuery("SELECT `id` FROM ".$db->quoteName('#__assets')." WHERE `name` = 'com_kinoarhiv' AND `parent_id` = 1 AND `level` = 1");
			$parent_id = $db->loadResult();

			$db->setQuery("INSERT INTO ".$db->quoteName('#__assets')." (`id`, `parent_id`, `lft`, `rgt`, `level`, `name`, `title`, `rules`)"
				. "\n VALUES ('', '".$parent_id."', '".$lft."', '".($lft+1)."', '1', 'com_kinoarhiv.name.".(int)$insertid."', '".$alias."', '".$rules."')");
			$assets_query = $db->execute();
			$assets_id = $db->insertid();

			$db->setQuery("UPDATE ".$db->quoteName('#__ka_names')." SET `asset_id` = '".$assets_id."' WHERE `id` = ".$insertid);
			$update_query = $db->execute();

			return array(
				'success'	=> true,
				'message'	=> JText::_('COM_KA_ITEMS_SAVE_SUCCESS'),
				'data'		=> array('id'=>$insertid, 'name'=>$name, 'latin_name'=>$latin_name)
			);
		}
	}
}
