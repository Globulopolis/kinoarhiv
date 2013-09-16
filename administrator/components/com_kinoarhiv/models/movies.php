<?php defined('_JEXEC') or die;

JLoader::register('DatabaseHelper', JPATH_COMPONENT.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'database.php');

class KinoarhivModelMovies extends JModelList {
	public function __construct($config = array()) {
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'alias', 'a.alias',
				'state', 'a.state',
				'access', 'a.access', 'access_level',
				'created', 'a.created',
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
		$form = $this->loadForm('com_kinoarhiv.movie', 'movie', array('control' => 'form', 'load_data' => $loadData));

		if (empty($form)) {
			return false;
		}

		$input = JFactory::getApplication()->input;
		$ids = $input->get('id', array(), 'array');
		$id = (isset($id[0]) && !empty($id[0])) ? $id[0] : 0;
		$user = JFactory::getUser();

		if ($id != 0 && (!$user->authorise('core.edit.state', 'com_kinoarhiv.movie.' . (int) $id)) || ($id == 0 && !$user->authorise('core.edit.state', 'com_kinoarhiv'))) {
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('state', 'disabled', 'true');
		}

		return $form;
	}

	protected function loadFormData() {
		$app = JFactory::getApplication();
		$data = $app->getUserState('com_kinoarhiv.edit.movie.data', array());

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
		parent::populateState('a.title', 'asc');
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
				'a.id, a.title, a.year, a.alias, a.state, a.access, a.created, a.ordering, a.language'
			)
		);
		$query->from('#__ka_movies AS a');

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
		$search = $this->getState('filter.search');
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
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering', 'a.title');
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
		$query->order($db->escape($orderCol . ' ' . $orderDirn));

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

	public function getItem($pk = null) {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$tmpl = $app->input->get('template', '', 'string');
		$id = $app->input->get('id', array(), 'array');

		if ($tmpl == 'names_edit') {
			$movie_id = $app->input->get('movie_id', 0, 'int');
			$name_id = $app->input->get('name_id', 0, 'int');

			$db->setQuery("SELECT `name_id`, `type`, `role`, `dub_id`, `is_actors`, `voice_artists`, `ordering`"
				. "\n FROM ".$db->quoteName('#__ka_rel_names')
				. "\n WHERE `name_id` = ".(int)$name_id." AND `movie_id` = ".(int)$movie_id);
			$result = $db->loadObject();
		} elseif ($tmpl == 'awards_edit') {
			$award_id = $app->input->get('award_id', 0, 'int');

			$db->setQuery("SELECT `id` AS `rel_aw_id`, `item_id`, `award_id`, `desc` AS `aw_desc`, `year` AS `aw_year`"
				. "\n FROM ".$db->quoteName('#__ka_rel_awards')
				. "\n WHERE `id` = ".(int)$award_id);
			$result = $db->loadObject();
		} else {
			if (count($id) == 0) {
				return array();
			}

			$db->setQuery("SELECT `m`.`id`, `m`.`asset_id`, `m`.`parent_id`, `m`.`title`, `m`.`alias`, `m`.`introtext`,
				`m`.`plot`, `m`.`desc`, `m`.`known`, `m`.`year`, `m`.`slogan`, `m`.`budget`, `m`.`age_restrict`,
				`m`.`ua_rate`, `m`.`mpaa`, `m`.`length`, `m`.`rate_loc`, `m`.`rate_sum_loc`, `m`.`imdb_votesum`,
				`m`.`imdb_votes`, `m`.`imdb_id`, `m`.`kp_votesum`, `m`.`kp_votes`, `m`.`kp_id`, `m`.`rate_fc`,
				`m`.`rottentm_id`, `m`.`rate_custom`, `m`.`urls`, `m`.`created`, `m`.`modified`, `m`.`state`,
				`m`.`ordering`, `m`.`metakey`, `m`.`metadesc`, `m`.`access`, `m`.`metadata`, `m`.`language`,
				`l`.`title` AS `language_title`"
				. "\n FROM ".$db->quoteName('#__ka_movies')." AS `m`"
				. "\n LEFT JOIN ".$db->quoteName('#__languages')." AS `l` ON `l`.`lang_code` = `m`.`language`"
				. "\n WHERE `m`.`id` = ".(int)$id[0]);
			$result = $db->loadObject();
		}

		return $result;
	}

	public function getCountries() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$id = $app->input->get('id', array(), 'array');

		$db->setQuery("SELECT `c`.`id`, `c`.`name` AS `title`, `c`.`code`, `t`.`ordering`"
			. "\n FROM ".$db->quoteName('#__ka_countries')." AS `c`"
			. "\n LEFT JOIN ".$db->quoteName('#__ka_rel_countries')." AS `t` ON `t`.`country_id` = `c`.`id` AND `t`.`movie_id` = ".(int)$id[0]
			. "\n WHERE `id` IN (SELECT `country_id` FROM ".$db->quoteName('#__ka_rel_countries')." WHERE `movie_id` = ".(int)$id[0].")"
			. "\n ORDER BY `t`.`ordering` ASC");
		$result['data'] = $db->loadObjectList();

		foreach ($result['data'] as $value) {
			$result['ids'][] = $value->id;
		}

		return $result;
	}

	public function getGenres() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$id = $app->input->get('id', array(), 'array');

		$db->setQuery("SELECT `g`.`id`, `g`.`name` AS `title`, `t`.`ordering`"
			. "\n FROM ".$db->quoteName('#__ka_genres')." AS `g`"
			. "\n LEFT JOIN ".$db->quoteName('#__ka_rel_genres')." AS `t` ON `t`.`genre_id` = `g`.`id` AND `t`.`movie_id` = ".(int)$id[0]
			. "\n WHERE `id` IN (SELECT `genre_id` FROM ".$db->quoteName('#__ka_rel_genres')." WHERE `movie_id` = ".(int)$id[0].")"
			. "\n ORDER BY `t`.`ordering` ASC");
		$result['data'] = $db->loadObjectList();

		foreach ($result['data'] as $value) {
			$result['ids'][] = $value->id;
		}

		return $result;
	}

	public function getTags() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$id = $app->input->get('id', array(), 'array');

		if (!empty($id[0])) {
			$db->setQuery("SELECT `metadata` FROM ".$db->quoteName('#__ka_movies')." WHERE `id` = ".(int)$id[0]);
			$metadata = $db->loadResult();
			$meta_arr = json_decode($metadata);

			if (count($meta_arr->tags) == 0) {
				return array('data'=>array(), 'ids'=>'');
			}

			$db->setQuery("SELECT `id`, `title`"
				. "\n FROM ".$db->quoteName('#__tags')
				. "\n WHERE `id` IN (".implode(',', $meta_arr->tags).")"
				. "\n ORDER BY `lft` ASC");
			$result['data'] = $db->loadObjectList();

			foreach ($result['data'] as $value) {
				$result['ids'][] = $value->id;
			}
		} else {
			$result = array('data'=>array(), 'ids'=>'');
		}

		return $result;
	}

	public function publish($isUnpublish) {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$ids = $app->input->get('id', array(), 'array');
		$state = $isUnpublish ? 0 : 1;

		$db->setQuery("UPDATE ".$db->quoteName('#__ka_movies')." SET `state` = '".(int)$state."' WHERE `id` IN (".implode(',', $ids).")");
		$result = $db->execute();

		return $result ? true : false;
	}

	public function remove() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$ids = $app->input->get('id', array(), 'array');

		$db->setQuery("DELETE FROM ".$db->quoteName('#__ka_movies')." WHERE `id` IN (".implode(',', $ids).")");
		$result = $db->execute();

		return $result ? true : false;
	}

	public function save($data) {
		/*$app = JFactory::getApplication();
		$db = $this->getDBO();
		$id = $app->input->post->get('id', null, 'int');

		$db->setQuery("UPDATE ".$db->quoteName('#__ka_reviews')
			. "\n SET `uid` = '".(int)$data['uid']."', `movie_id` = '".(int)$data['movie_id']."', `review` = '".$db->escape($data['review'])."', `r_datetime` = '".$data['r_datetime']."', `type` = '".(int)$data['type']."', `ip` = '".(string)$data['ip']."', `state` = '".(int)$data['state']."'"
			. "\n WHERE `id` = ".(int)$id);
		$result = $db->execute();

		return ($result === true) ? true : false;*/
		return true;
	}

	public function getCast() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$id = $app->input->get('id', null, 'int');
		$orderby = $app->input->get('sidx', '1', 'string');
		$order = $app->input->get('sord', 'asc', 'word');
		$page = $app->input->get('page', 0, 'int');
		$search_field = $app->input->get('searchField', '', 'string');
		$search_operand = $app->input->get('searchOper', 'eq', 'cmd');
		$search_string = $app->input->get('searchString', '', 'string');
		$result = (object)array();
		$result->rows = array();

		$db->setQuery("SELECT `id`, `title` FROM ".$db->quoteName('#__ka_names_career')." ORDER BY `ordering` ASC");
		$_careers = $db->loadObjectList();

		foreach ($_careers as $career) {
			$careers[$career->id] = $career->title;
		}

		// Preventing 'ordering asc/desc, ordering asc/desc' duplication
		if (strpos($orderby, 'ordering') !== false) {
			$query_order = "\n ORDER BY `t`.`ordering` ASC";
		} else {
			// We need this if grid grouping is used. At the first(0) index - grouping field
			$ord_request = explode(',', $orderby);
			if (count($ord_request) > 1) {
				$query_order = "\n ORDER BY ".$ord_request[1]." ".strtoupper($order).", `t`.`ordering` ASC";
			} else {
				$query_order = "\n ORDER BY ".$db->quoteName($orderby)." ".strtoupper($order).", `t`.`ordering` ASC";
			}
		}

		$where = "\n WHERE `n`.`id` IN (SELECT `name_id` FROM ".$db->quoteName('#__ka_rel_names')." WHERE `movie_id` = ".(int)$id.")";
		if (!empty($search_string)) {
			if ($search_field == 'n.name' || $search_field == 'd.name') {
				$where .= " AND (".DatabaseHelper::transformOperands($db->quoteName($search_field), $search_operand, $db->escape($search_string))." OR ".DatabaseHelper::transformOperands($db->quoteName('n.latin_name'), $search_operand, $db->escape($search_string)).")";
			} else {
				$where .= " AND ".DatabaseHelper::transformOperands($db->quoteName($search_field), $search_operand, $db->escape($search_string));
			}
		}

		$db->setQuery("SELECT `n`.`id` AS `name_id`, `n`.`name`, `n`.`latin_name`, `t`.`type`, `t`.`role`, `d`.`id` AS `dub_id`, `d`.`name` AS `dub_name`, `d`.`latin_name` AS `dub_latin_name`, GROUP_CONCAT(`r`.`role` SEPARATOR ', ') AS `dub_role`, `t`.`ordering`"
			. "\n FROM ".$db->quoteName('#__ka_names')." AS `n`"
			. "\n LEFT JOIN ".$db->quoteName('#__ka_rel_names')." AS `t` ON `t`.`name_id` = `n`.`id`"
			. "\n LEFT JOIN ".$db->quoteName('#__ka_names')." AS `d` ON `d`.`id` = `t`.`dub_id`"
			. "\n LEFT JOIN ".$db->quoteName('#__ka_rel_names')." AS `r` ON `r`.`dub_id` = `n`.`id`"
			. $where
			. "\n GROUP BY `n`.`id`"
			. $query_order);
		$names = $db->loadObjectList();

		// Presorting based on the type of career person
		$i = 0;
		$_result = array();
		foreach ($names as $value) {
			$name = '';
			if (!empty($value->name)) $name .= $value->name;
			if (!empty($value->name) && !empty($value->latin_name)) $name .= ' / ';
			if (!empty($value->latin_name)) $name .= $value->latin_name;

			$dub_name = '';
			if (!empty($value->dub_name)) $dub_name .= $value->dub_name;
			if (!empty($value->dub_name) && !empty($value->dub_latin_name)) $dub_name .= ' / ';
			if (!empty($value->dub_latin_name)) $dub_name .= $value->dub_latin_name;

			foreach (explode(',', $value->type) as $k=>$type) {
				$_result[$type][$i] = array(
					'name'		=> $name,
					'name_id'	=> $value->name_id,
					'role'		=> $value->role,
					'dub_name'	=> $dub_name,
					'dub_id'	=> $value->dub_id,
					'ordering'	=> $value->ordering,
					'type'		=> $careers[$type],
					'type_id'	=> $type
				);

				$i++;
			}
		}

		// The final sorting of the array for the grid
		$k = 0;
		foreach ($_result as $row) {
			foreach ($row as $elem) {
				$result->rows[$k]['id'] = $elem['name_id'].'_'.$id.'_'.$elem['type_id'];
				$result->rows[$k]['cell'] = array(
					'name'		=> $elem['name'],
					'name_id'	=> $elem['name_id'],
					'role'		=> $elem['role'],
					'dub_name'	=> $elem['dub_name'],
					'dub_id'	=> $elem['dub_id'],
					'ordering'	=> $elem['ordering'],
					'type'		=> $elem['type']
				);

				$k++;
			}
		}

		$result->page = $page;
		$result->total = 1;
		$result->records = count($result->rows);

		return $result;
	}

	public function deleteCast() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$data = $app->input->post->get('data', array(), 'array');
		$query = true;

		if (count($data) <= 0) {
			return array('success'=>false, 'message'=>JText::_('JERROR_NO_ITEMS_SELECTED'));
		}

		$db->setDebug(true);
		$db->lockTable('#__ka_rel_names');
		$db->transactionStart();

		foreach ($data as $key=>$value) {
			$name = explode('_', substr($value['name'], 16));

			$db->setQuery("DELETE FROM ".$db->quoteName('#__ka_rel_names')." WHERE `name_id` = ".(int)$name[0]." AND `movie_id` = ".(int)$name[1].";");
			$result = $db->execute();

			if ($result === false) {
				$query = false;
				break;
			}
		}

		if ($query === false) {
			$db->transactionRollback();
			$success = false;
			$message = JText::_('COM_KA_ITEMS_DELETED_ERROR');
		} else {
			$db->transactionCommit();
			$success = true;
			$message = JText::_('COM_KA_ITEMS_DELETED_SUCCESS');
		}

		$db->unlockTables();
		$db->setDebug(false);

		return array('success'=>$success, 'message'=>$message);
	}

	public function deleteRelAwards() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$data = $app->input->post->get('data', array(), 'array');
		$query = true;

		if (count($data) <= 0) {
			return array('success'=>false, 'message'=>JText::_('JERROR_NO_ITEMS_SELECTED'));
		}

		$db->setDebug(true);
		$db->lockTable('#__ka_rel_awards');
		$db->transactionStart();

		foreach ($data as $key=>$value) {
			$ids = explode('_', substr($value['name'], 16));

			$db->setQuery("DELETE FROM ".$db->quoteName('#__ka_rel_awards')." WHERE `id` = ".(int)$ids[0].";");
			$result = $db->execute();

			if ($result === false) {
				$query = false;
				break;
			}
		}

		if ($query === false) {
			$db->transactionRollback();
			$success = false;
			$message = JText::_('COM_KA_ITEMS_DELETED_ERROR');
		} else {
			$db->transactionCommit();
			$success = true;
			$message = JText::_('COM_KA_ITEMS_DELETED_SUCCESS');
		}

		$db->unlockTables();
		$db->setDebug(false);

		return array('success'=>$success, 'message'=>$message);
	}

	public function getAwards() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$id = $app->input->get('id', null, 'int');
		$orderby = $app->input->get('sidx', '1', 'string');
		$order = $app->input->get('sord', 'asc', 'word');
		$limit = $app->input->get('rows', 25, 'int');
		$page = $app->input->get('page', 0, 'int');
		$search_field = $app->input->get('searchField', '', 'string');
		$search_operand = $app->input->get('searchOper', 'eq', 'cmd');
		$search_string = $app->input->get('searchString', '', 'string');
		$limitstart = $limit * $page - $limit;
		$result = (object)array('rows'=>array());
		$where = "";

		if (!empty($search_string)) {
			$where .= " AND ".DatabaseHelper::transformOperands($db->quoteName($search_field), $search_operand, $db->escape($search_string));
		}

		$db->setQuery("SELECT COUNT(`rel`.`id`)"
			. "\n FROM ".$db->quoteName('#__ka_rel_awards')." AS `rel`"
			. "\n WHERE `rel`.`item_id` = ".(int)$id." AND `type` = 0".$where);
		$total = $db->loadResult();

		$total_pages = ($total > 0) ? ceil($total / $limit) : 0;
		$page = ($page > $total_pages) ? $total_pages : $page;

		$db->setQuery("SELECT `rel`.`id`, `rel`.`item_id`, `rel`.`award_id`, `rel`.`desc`, `rel`.`year`, `rel`.`type`, `aw`.`title`"
			. "\n FROM ".$db->quoteName('#__ka_rel_awards')." AS `rel`"
			. "\n LEFT JOIN ".$db->quoteName('#__ka_awards')." AS `aw` ON `aw`.`id` = `rel`.`award_id`"
			. "\n WHERE `rel`.`item_id` = ".(int)$id." AND `type` = 0".$where
			. "\n ORDER BY ".$db->quoteName($orderby).' '.strtoupper($order), $limitstart, $limit);
		$rows = $db->loadObjectList();

		$k = 0;
		foreach ($rows as $elem) {
			$result->rows[$k]['id'] = $elem->id.'_'.$elem->item_id.'_'.$elem->award_id;
			$result->rows[$k]['cell'] = array(
				'id'		=> $elem->id,
				'award_id'	=> $elem->award_id,
				'title'		=> $elem->title,
				'year'		=> $elem->year,
				'desc'		=> $elem->desc
			);

			$k++;
		}

		$result->page = $page;
		$result->total = $total_pages;
		$result->records = $total;

		return $result;
	}

	public function saveOrder() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$data = $app->input->post->get('ord', array(), 'array');

		if (count($data) < 2) {
			return array('success'=>false, 'message'=>JText::_('COM_KA_SAVE_ORDER_AT_LEAST_TWO'));
		}

		$query = true;

		$db->setDebug(true);
		$db->lockTable('#__ka_movies');
		$db->transactionStart();

		foreach ($data as $key=>$value) {
			$db->setQuery("UPDATE ".$db->quoteName('#__ka_movies')." SET `ordering` = '".(int)$key."' WHERE `id` = ".(int)$value.";");
			$result = $db->execute();

			if ($result === false) {
				$query = false;
				break;
			}
		}

		if ($query === false) {
			$db->transactionRollback();
		} else {
			$db->transactionCommit();
		}

		$db->unlockTables();
		$db->setDebug(false);

		if ($query) {
			$success = true;
			$message = JText::_('COM_KA_SAVED');
		} else {
			$success = false;
			$message = JText::_('COM_KA_SAVE_ORDER_ERROR');
		}

		return array('success'=>$success, 'message'=>$message);
	}
}
