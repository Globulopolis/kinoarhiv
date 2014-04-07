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

			$db->setQuery("SELECT `name_id`, `role`, `dub_id`, `is_actors`, `voice_artists`, `is_directors`, `ordering` AS `r_ordering`, `desc` AS `r_desc`"
				. "\n FROM ".$db->quoteName('#__ka_rel_names')
				. "\n WHERE `name_id` = ".(int)$name_id." AND `movie_id` = ".(int)$movie_id);
			$result = $db->loadObject();
			
			if (!empty($result)) {
				$result->type = $app->input->get('career_id', 0, 'int');
			}
		} elseif ($tmpl == 'awards_edit') {
			$award_id = $app->input->get('award_id', 0, 'int');

			$db->setQuery("SELECT `id` AS `rel_aw_id`, `item_id`, `award_id`, `desc` AS `aw_desc`, `year` AS `aw_year`"
				. "\n FROM ".$db->quoteName('#__ka_rel_awards')
				. "\n WHERE `id` = ".(int)$award_id);
			$result = $db->loadObject();
		} elseif ($tmpl == 'premieres_edit') {
			$premiere_id = $app->input->get('premiere_id', 0, 'int');

			$db->setQuery("SELECT `id` AS `premiere_id`, `vendor_id` AS `p_vendor_id`, `premiere_date` AS `p_premiere_date`, `country_id` AS `p_country_id`, `info` AS `p_info`, `ordering` AS `p_ordering`"
				. "\n FROM ".$db->quoteName('#__ka_premieres')
				. "\n WHERE `id` = ".(int)$premiere_id);
			$result = $db->loadObject();
		} else {
			$result = array('movie'=>(object)array());
			if (count($id) == 0 || empty($id) || empty($id[0])) {
				return $result;
			}

			$db->setQuery("SELECT `m`.`id`, `m`.`parent_id`, `m`.`title`, `m`.`alias`, `m`.`alias` AS `alias_orig`, `m`.`introtext`,
				`m`.`plot`, `m`.`desc`, `m`.`known`, `m`.`year`, `m`.`slogan`, `m`.`budget`, `m`.`age_restrict`,
				`m`.`ua_rate`, `m`.`mpaa`, `m`.`length`, `m`.`rate_loc`, `m`.`rate_sum_loc`, `m`.`imdb_votesum`,
				`m`.`imdb_votes`, `m`.`imdb_id`, `m`.`kp_votesum`, `m`.`kp_votes`, `m`.`kp_id`, `m`.`rate_fc`,
				`m`.`rottentm_id`, `m`.`rate_custom`, `m`.`urls`, `m`.`attribs`, `m`.`created`, `m`.`modified`, `m`.`state`,
				`m`.`ordering`, `m`.`metakey`, `m`.`metadesc`, `m`.`access`, `m`.`metadata`, `m`.`language`,
				`l`.`title` AS `language_title`, `g`.`id` AS `gid`, `g`.`filename`"
				. "\n FROM ".$db->quoteName('#__ka_movies')." AS `m`"
				. "\n LEFT JOIN ".$db->quoteName('#__languages')." AS `l` ON `l`.`lang_code` = `m`.`language`"
				. "\n LEFT JOIN ".$db->quoteName('#__ka_movies_gallery')." AS `g` ON `g`.`movie_id` = `m`.`id` AND `g`.`type` = 2 AND `g`.`poster_frontpage` = 1"
				. "\n WHERE `m`.`id` = ".(int)$id[0]);
			$result['movie'] = $db->loadObject();

			$result['movie']->genres = $this->getGenres();
			$result['movie']->genres_orig = implode(',', $result['movie']->genres['ids']);
			$result['movie']->countries = $this->getCountries();
			$result['movie']->countries_orig = implode(',', $result['movie']->countries['ids']);
			$result['movie']->tags = $this->getTags();
			$result['movie']->tags_orig = !empty($result['movie']->tags['ids']) ? implode(',', $result['movie']->tags['ids']) : '';

			if (!empty($result['movie']->attribs)) {
				$result['attribs'] = json_decode($result['movie']->attribs);
			}
		}

		return $result;
	}

	protected function getCountries() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$id = $app->input->get('id', array(), 'array');
		$result = array('data'=>array(), 'ids'=>array());

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

	protected function getGenres() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$id = $app->input->get('id', array(), 'array');
		$result = array('data'=>array(), 'ids'=>array());

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

	protected function getTags() {
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

		try {
			$db->execute();

			return true;
		} catch(Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}
	}

	public function remove() {
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');

		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$ids = $app->input->get('id', array(), 'array');
		$params = JComponentHelper::getParams('com_kinoarhiv');

		// Remove award relations
		$db->setQuery("DELETE FROM ".$db->quoteName('#__ka_rel_awards')." WHERE `item_id` IN (".implode(',', $ids).") AND `type` = 0");

		try {
			$db->execute();
		} catch(Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}

		// Remove country relations
		$db->setQuery("DELETE FROM ".$db->quoteName('#__ka_rel_countries')." WHERE `movie_id` IN (".implode(',', $ids).")");

		try {
			$db->execute();
		} catch(Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}

		// Remove genre relations
		$db->setQuery("DELETE FROM ".$db->quoteName('#__ka_rel_genres')." WHERE `movie_id` IN (".implode(',', $ids).")");

		try {
			$db->execute();
		} catch(Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}

		// Remove name relations
		$db->setQuery("DELETE FROM ".$db->quoteName('#__ka_rel_names')." WHERE `movie_id` IN (".implode(',', $ids).")");

		try {
			$db->execute();
		} catch(Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}

		// Remove releases
		$db->setQuery("DELETE FROM ".$db->quoteName('#__ka_releases')." WHERE `movie_id` IN (".implode(',', $ids).")");

		try {
			$db->execute();
		} catch(Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}

		// Remove reviews
		$db->setQuery("DELETE FROM ".$db->quoteName('#__ka_reviews')." WHERE `movie_id` IN (".implode(',', $ids).")");

		try {
			$db->execute();
		} catch(Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}

		// Remove favorited and watched movies
		$db->setQuery("DELETE FROM ".$db->quoteName('#__ka_user_marked_movies')." WHERE `movie_id` IN (".implode(',', $ids).")");

		try {
			$db->execute();
		} catch(Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}

		// Remove user votes
		$db->setQuery("DELETE FROM ".$db->quoteName('#__ka_user_votes')." WHERE `movie_id` IN (".implode(',', $ids).")");

		try {
			$db->execute();
		} catch(Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}

		// Remove premieres
		$db->setQuery("DELETE FROM ".$db->quoteName('#__ka_premieres')." WHERE `movie_id` IN (".implode(',', $ids).")");

		try {
			$db->execute();
		} catch(Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}

		// Remove media items
		$db->setQuery("SELECT `id`, SUBSTRING(`alias`, 1, 1) AS `alias` FROM ".$db->quoteName('#__ka_movies')." WHERE `id` IN (".implode(',', $ids).")");
		$items = $db->loadObjectList();

		foreach ($items as $item) {
			// Delete root folders
			if (file_exists($params->get('media_posters_root').DIRECTORY_SEPARATOR.$item->alias.DIRECTORY_SEPARATOR.$item->id)) {
				JFolder::delete($params->get('media_posters_root').DIRECTORY_SEPARATOR.$item->alias.DIRECTORY_SEPARATOR.$item->id);
			}
			if (file_exists($params->get('media_scr_root').DIRECTORY_SEPARATOR.$item->alias.DIRECTORY_SEPARATOR.$item->id)) {
				JFolder::delete($params->get('media_scr_root').DIRECTORY_SEPARATOR.$item->alias.DIRECTORY_SEPARATOR.$item->id);
			}
			if (file_exists($params->get('media_wallpapers_root').DIRECTORY_SEPARATOR.$item->alias.DIRECTORY_SEPARATOR.$item->id)) {
				JFolder::delete($params->get('media_wallpapers_root').DIRECTORY_SEPARATOR.$item->alias.DIRECTORY_SEPARATOR.$item->id);
			}

			if (file_exists($params->get('media_trailers_root').DIRECTORY_SEPARATOR.$item->alias.DIRECTORY_SEPARATOR.$item->id)) {
				JFolder::delete($params->get('media_trailers_root').DIRECTORY_SEPARATOR.$item->alias.DIRECTORY_SEPARATOR.$item->id);
			}

			// Delete rating images
			if (file_exists($params->get('media_rating_image_root').DIRECTORY_SEPARATOR.'imdb'.DIRECTORY_SEPARATOR.$item->id.'_big.png')) {
				JFile::delete($params->get('media_rating_image_root').DIRECTORY_SEPARATOR.'imdb'.DIRECTORY_SEPARATOR.$item->id.'_big.png');
			}
			if (file_exists($params->get('media_rating_image_root').DIRECTORY_SEPARATOR.'kinopoisk'.DIRECTORY_SEPARATOR.$item->id.'_big.png')) {
				JFile::delete($params->get('media_rating_image_root').DIRECTORY_SEPARATOR.'kinopoisk'.DIRECTORY_SEPARATOR.$item->id.'_big.png');
			}
			if (file_exists($params->get('media_rating_image_root').DIRECTORY_SEPARATOR.'rottentomatoes'.DIRECTORY_SEPARATOR.$item->id.'_big.png')) {
				JFile::delete($params->get('media_rating_image_root').DIRECTORY_SEPARATOR.'rottentomatoes'.DIRECTORY_SEPARATOR.$item->id.'_big.png');
			}
		}

		// Remove movie(s) from DB
		$db->setQuery("DELETE FROM ".$db->quoteName('#__ka_movies')." WHERE `id` IN (".implode(',', $ids).")");

		try {
			$db->execute();
		} catch(Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}

		$db->setQuery("DELETE FROM ".$db->quoteName('#__ka_movies_gallery')." WHERE `movie_id`IN (".implode(',', $ids).")");
		$db->execute();

		// Remove trailers
		$db->setQuery("DELETE FROM ".$db->quoteName('#__ka_trailers')." WHERE `movie_id` IN (".implode(',', $ids).")");
		$db->execute();

		return true;
	}

	public function apply($data) {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$user = JFactory::getUser();
		$params = JComponentHelper::getParams('com_kinoarhiv');

		$id = $app->input->get('id', 0, 'int');
		$data = $data['movie'];
		$created_by = $data['created_by'] == 0 ? $user->id : $data['created_by'];
		$metadata = array(
			'tags' => json_decode('['.$data['tags'].']', true),
			'robots' => $data['robots']
		);
		$attribs = json_encode($app->input->post->get('form', array(), 'array')['attribs']);
		$introtext = '';
		$intro_countries = '';
		$intro_genres = '';
		$intro_directors = '';
		$intro_cast = '';

		// Proccess intro text for country IDs and store in relation table
		if (!empty($data['countries'])) {
			$db->setQuery("SELECT `name`, `code` FROM ".$db->quoteName('#__ka_countries')." WHERE `id` IN (".$data['countries'].") AND `language` = '".$data['language']."'");
			$countries = $db->loadObjectList();

			$ln_str = count($countries) > 1 ? 'COM_KA_COUNTRIES' : 'COM_KA_COUNTRY';

			foreach ($countries as $cn) {
				$intro_countries .= '[cn='.$cn->code.']'.$cn->name.'[/cn], ';
			}

			$intro_countries = '[country ln='.$ln_str.']: '.JString::substr($intro_countries, 0, -2).'[/country]<br />';

			$countries_new_arr = explode(',', $data['countries']);
			$query = true;
			$db->lockTable('#__ka_rel_countries');
			$db->transactionStart();

			$db->setQuery("DELETE FROM ".$db->quoteName('#__ka_rel_countries')." WHERE `movie_id` = ".(int)$id);
			$db->execute();

			foreach ($countries_new_arr as $ordering=>$country_id) {
				$db->setQuery("INSERT INTO ".$db->quoteName('#__ka_rel_countries')." (`country_id`,`movie_id`,`ordering`) VALUES ('".(int)$country_id."', '".(int)$id."', '".(int)$ordering."');");
				$result = $db->execute();

				if ($result === false) {
					$query = false;
					break;
				}
			}

			if ($query === false) {
				$db->transactionRollback();
				$this->setError('Commit for "#__ka_rel_countries" failed!');
				$db->unlockTables();
				return false;
			} else {
				$db->transactionCommit();
				$db->unlockTables();
			}
		}

		// Proccess intro text for genres IDs and store in relation table
		if (!empty($data['genres'])) {
			$db->setQuery("SELECT `name` FROM ".$db->quoteName('#__ka_genres')." WHERE `id` IN (".$data['genres'].") AND `language` = '".$data['language']."'");
			$genres = $db->loadObjectList();

			$ln_str = count($genres) > 1 ? 'COM_KA_GENRES' : 'COM_KA_GENRE';

			foreach ($genres as $genre) {
				$intro_genres .= $genre->name.', ';
			}

			$intro_genres = '[genres ln='.$ln_str.']: '.JString::substr($intro_genres, 0, -2).'[/genres]<br />';

			$genres_new_arr = explode(',', $data['genres']);
			$query = true;
			$db->lockTable('#__ka_rel_genres');
			$db->transactionStart();

			$db->setQuery("DELETE FROM ".$db->quoteName('#__ka_rel_genres')." WHERE `movie_id` = ".(int)$id);
			$db->execute();

			foreach ($genres_new_arr as $ordering=>$genre_id) {
				$db->setQuery("INSERT INTO ".$db->quoteName('#__ka_rel_genres')." (`genre_id`,`movie_id`,`ordering`) VALUES ('".(int)$genre_id."', '".(int)$id."', '".(int)$ordering."');");
				$result = $db->execute();

				if ($result === false) {
					$query = false;
					break;
				}
			}

			if ($query === false) {
				$db->transactionRollback();
				$this->setError('Commit for "#__ka_rel_genres" failed!');
				$db->unlockTables();
				return false;
			} else {
				$db->transactionCommit();
				$db->unlockTables();
			}
		}

		// Update statistics on genres
		$this->updateGenresStat($data['genres_orig'], $data['genres']);

		if (!empty($id)) {
			// Start processing intro text for director(s) IDs and store in relation table
			$names_d_limit = ($params->get('introtext_actors_list_limit') == 0) ? "" : "\n LIMIT ".$params->get('introtext_actors_list_limit');
			$db->setQuery("SELECT `rel`.`name_id`, `n`.`name`, `n`.`latin_name`"
				. "\n FROM ".$db->quoteName('#__ka_rel_names')." AS `rel`"
				. "\n LEFT JOIN ".$db->quoteName('#__ka_names')." AS `n` ON `n`.`id` = `rel`.`name_id`"
				. "\n WHERE `rel`.`movie_id` = ".$id." AND `rel`.`is_directors` = 1"
				. "\n ORDER BY `rel`.`ordering`"
				. $names_d_limit);
			$names_d = $db->loadObjectList();

			if (count($names_d) > 0) {
				$intro_directors .= count($names_d == 1) ? '[names ln=COM_KA_DIRECTOR]: ' : '[names ln=COM_KA_DIRECTORS]: ';
				foreach ($names_d as $director) {
					$n = !empty($director->name) ? $director->name : '';
					if (!empty($director->name) && !empty($director->latin_name)) {
						$n .= ' / ';
					}
					$n .= !empty($director->latin_name) ? $director->latin_name : '';
					$intro_directors .= '[name='.$director->name_id.']'.$n.'[/name], ';
				}
				$intro_directors = JString::substr($intro_directors, 0, -2).'[/names]<br />';
			}
			// End

			// Start processing intro text for cast IDs and store in relation table
			$names_limit = ($params->get('introtext_actors_list_limit') == 0) ? "" : "\n LIMIT ".$params->get('introtext_actors_list_limit');
			$db->setQuery("SELECT `rel`.`name_id`, `n`.`name`, `n`.`latin_name`"
				. "\n FROM ".$db->quoteName('#__ka_rel_names')." AS `rel`"
				. "\n LEFT JOIN ".$db->quoteName('#__ka_names')." AS `n` ON `n`.`id` = `rel`.`name_id`"
				. "\n WHERE `rel`.`movie_id` = ".$id." AND `rel`.`is_actors` = 1 AND `rel`.`voice_artists` = 0"
				. "\n ORDER BY `rel`.`ordering`"
				. $names_limit);
			$names = $db->loadObjectList();

			if (count($names) > 0) {
				$intro_cast .= '[names ln=COM_KA_CAST]: ';
				foreach ($names as $name) {
					$n = !empty($name->name) ? $name->name : '';
					if (!empty($name->name) && !empty($name->latin_name)) {
						$n .= ' / ';
					}
					$n .= !empty($name->latin_name) ? $name->latin_name : '';
					$intro_cast .= '[name='.$name->name_id.']'.$n.'[/name], ';
				}
				$intro_cast = JString::substr($intro_cast, 0, -2).'[/names]';
			}
			// End
		}

		$introtext = $intro_countries.$intro_genres.$intro_directors.$intro_cast;
		$alias = empty($data['alias']) ? JFilterOutput::stringURLSafe($data['title']) : JFilterOutput::stringURLSafe($data['alias']);

		if (empty($id)) {
			$db->setQuery("INSERT INTO ".$db->quoteName('#__ka_movies')
				. " (`id`, `asset_id`, `parent_id`, `title`, `alias`, `introtext`, `plot`, `desc`, `known`, `year`, `slogan`, `budget`, `age_restrict`, `ua_rate`, `mpaa`, `length`, `rate_loc`, `rate_sum_loc`, `imdb_votesum`, `imdb_votes`, `imdb_id`, `kp_votesum`, `kp_votes`, `kp_id`, `rate_fc`, `rottentm_id`, `rate_custom`, `urls`, `attribs`, `created`, `created_by`, `modified`, `state`, `ordering`, `metakey`, `metadesc`, `access`, `metadata`, `language`)"
				. "\n VALUES ('', '0', '0', '".$db->escape($data['title'])."', '".$alias."', '".$db->escape($introtext)."', '".$db->escape($data['plot'])."', '".$db->escape($data['desc'])."', '".$db->escape($data['known'])."', '".$data['year']."', '".$db->escape($data['slogan'])."', '".$data['budget']."', '".$data['age_restrict']."', '".$data['ua_rate']."', '".$data['mpaa']."', '".$data['length']."', '".(int)$data['rate_loc']."', '".(int)$data['rate_sum_loc']."', '".$data['imdb_votesum']."', '".(int)$data['imdb_votes']."', '".(int)$data['imdb_id']."', '".$data['kp_votesum']."', '".(int)$data['kp_votes']."', '".(int)$data['kp_id']."', '".(int)$data['rate_fc']."', '".$data['rottentm_id']."', '".$db->escape($data['rate_custom'])."', '".$db->escape($data['urls'])."', '".$attribs."', '".$data['created']."', '".$created_by."', '".$data['modified']."', '".$data['state']."', '".(int)$data['ordering']."', '".$db->escape($data['metakey'])."', '".$db->escape($data['metadesc'])."', '".(int)$data['access']."', '".json_encode($metadata)."', '".$data['language']."')");
		} else {
			$db->setQuery("UPDATE ".$db->quoteName('#__ka_movies')
				. "\n SET `parent_id` = '0', `title` = '".$db->escape($data['title'])."', `alias` = '".$alias."',"
				. " `introtext` = '".$db->escape($introtext)."', `plot` = '".$db->escape($data['plot'])."', `desc` = '".$db->escape($data['desc'])."',"
				. " `known` = '".$db->escape($data['known'])."', `year` = '".$data['year']."', `slogan` = '".$db->escape($data['slogan'])."',"
				. " `budget` = '".$data['budget']."', `age_restrict` = '".$data['age_restrict']."', `ua_rate` = '".$data['ua_rate']."',"
				. " `mpaa` = '".$data['mpaa']."', `length` = '".$data['length']."', `rate_loc` = '".(int)$data['rate_loc']."',"
				. " `rate_sum_loc` = '".(int)$data['rate_sum_loc']."', `imdb_votesum` = '".$data['imdb_votesum']."', `imdb_votes` = '".(int)$data['imdb_votes']."',"
				. " `imdb_id` = '".(int)$data['imdb_id']."', `kp_votesum` = '".$data['kp_votesum']."', `kp_votes` = '".(int)$data['kp_votes']."',"
				. " `kp_id` = '".(int)$data['kp_id']."', `rate_fc` = '".(int)$data['rate_fc']."', `rottentm_id` = '".$data['rottentm_id']."',"
				. " `rate_custom` = '".$db->escape($data['rate_custom'])."', `urls` = '".$db->escape($data['urls'])."', `attribs` = '".$attribs."', `created` = '".$data['created']."',"
				. " `created_by` = '".$created_by."', `modified` = '".$data['modified']."', `state` = '".$data['state']."',"
				. " `ordering` = '".(int)$data['ordering']."', `metakey` = '".$db->escape($data['metakey'])."', `metadesc` = '".$db->escape($data['metadesc'])."',"
				. " `access` = '".(int)$data['access']."', `metadata` = '".json_encode($metadata)."', `language` = '".$data['language']."'"
				. "\n WHERE `id` = ".(int)$id);
		}

		try {
			$db->execute();

			if (empty($id)) {
				$insertid = $db->insertid();
				$app->input->set('id', array($insertid)); // Need to proper redirect to edited item

				// Create access rules
				$db->setQuery("SELECT `id` FROM ".$db->quoteName('#__assets')." WHERE `name` = 'com_kinoarhiv' AND `parent_id` = 1");
				$parent_id = $db->loadResult();

				$db->setQuery("SELECT MAX(`lft`)+2 AS `lft`, MAX(`rgt`)+2 AS `rgt` FROM ".$db->quoteName('#__assets'));
				$lft_rgt = $db->loadObject();

				$db->setQuery("INSERT INTO ".$db->quoteName('#__assets')
					. "\n (`id`, `parent_id`, `lft`, `rgt`, `level`, `name`, `title`, `rules`)"
					. "\n VALUES ('', '".$parent_id."', '".$lft_rgt->lft."', '".$lft_rgt->rgt."', '2', 'com_kinoarhiv.movie.".$insertid."', '".$db->escape($data['title'])."', '{}')");
				$db->execute();
				$asset_id = $db->insertid();

				$db->setQuery("UPDATE ".$db->quoteName('#__ka_movies')
					. "\n SET `asset_id` = '".(int)$asset_id."'"
					. "\n WHERE `id` = ".(int)$insertid);
				$db->execute();
			} else {
				$app->input->set('id', array($id));

				// Alias was changed? Move all linked items into new filesystem location.
				if (JString::substr($alias, 0, 1) != JString::substr($data['alias_orig'], 0, 1)) {
					$this->moveMediaItems($id, $data['alias_orig'], $alias, $params);
				}
			}

			return true;
		} catch(Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}

		return true;
	}

	/**
	 * Method to move all media items which is linked to the movie into a new location, if movie alias was changed.
	 *
	 * @param   int      $id          Movie ID.
	 * @param   string   $old_alias   Old movie alias.
	 * @param   string   $new_alias   New movie alias.
	 * @param   object   $params      Component parameters.
	 *
	 * @return  boolean   True on success
	 *
	*/
	protected function moveMediaItems($id, $old_alias, $new_alias, &$params) {
		if (empty($id) || empty($old_alias) || empty($new_alias)) {
			$this->setError('Movie ID or alias cannot be empty!');

			return false;
		} else {
			jimport('joomla.filesystem.folder');
			JLoader::register('KAFilesystemHelper', JPATH_COMPONENT.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'filesystem.php');
			$fs_helper = new KAFilesystemHelper;

			$error = false;
			$old_alias = JString::substr($old_alias, 0, 1);
			$new_alias = JString::substr($new_alias, 0, 1);

			// Move gallery items
			$path_poster = $params->get('media_posters_root');
			$path_wallpp = $params->get('media_wallpapers_root');
			$path_screen = $params->get('media_scr_root');
			$old_folder_poster = $path_poster.DIRECTORY_SEPARATOR.$old_alias.DIRECTORY_SEPARATOR.$id.DIRECTORY_SEPARATOR.'posters';
			$old_folder_wallpp = $path_wallpp.DIRECTORY_SEPARATOR.$old_alias.DIRECTORY_SEPARATOR.$id.DIRECTORY_SEPARATOR.'wallpapers';
			$old_folder_screen = $path_screen.DIRECTORY_SEPARATOR.$old_alias.DIRECTORY_SEPARATOR.$id.DIRECTORY_SEPARATOR.'screenshots';
			$new_folder_poster = $path_poster.DIRECTORY_SEPARATOR.$new_alias.DIRECTORY_SEPARATOR.$id.DIRECTORY_SEPARATOR.'posters';
			$new_folder_wallpp = $path_wallpp.DIRECTORY_SEPARATOR.$new_alias.DIRECTORY_SEPARATOR.$id.DIRECTORY_SEPARATOR.'wallpapers';
			$new_folder_screen = $path_screen.DIRECTORY_SEPARATOR.$new_alias.DIRECTORY_SEPARATOR.$id.DIRECTORY_SEPARATOR.'screenshots';

			if (!KAFilesystemHelper::move(
				array($old_folder_poster, $old_folder_wallpp, $old_folder_screen),
				array($new_folder_poster, $new_folder_wallpp, $new_folder_screen))
				) {
				$this->setError('Error while moving the files from media folders into new location! See log for more information.');
			}

			// Remove parent folder for posters/wallpapers/screenshots. Delete only if folder(s) is empty.
			if ($fs_helper::getFolderSize($path_poster.DIRECTORY_SEPARATOR.$old_alias.DIRECTORY_SEPARATOR.$id) === 0) {
				if (file_exists($path_poster.DIRECTORY_SEPARATOR.$old_alias.DIRECTORY_SEPARATOR.$id)) {
					JFolder::delete($path_poster.DIRECTORY_SEPARATOR.$old_alias.DIRECTORY_SEPARATOR.$id);
				}
			}
			if ($fs_helper::getFolderSize($path_wallpp.DIRECTORY_SEPARATOR.$old_alias.DIRECTORY_SEPARATOR.$id) === 0) {
				if (file_exists($path_wallpp.DIRECTORY_SEPARATOR.$old_alias.DIRECTORY_SEPARATOR.$id)) {
					JFolder::delete($path_wallpp.DIRECTORY_SEPARATOR.$old_alias.DIRECTORY_SEPARATOR.$id);
				}
			}
			if ($fs_helper::getFolderSize($path_screen.DIRECTORY_SEPARATOR.$old_alias.DIRECTORY_SEPARATOR.$id) === 0) {
				if (file_exists($path_screen.DIRECTORY_SEPARATOR.$old_alias.DIRECTORY_SEPARATOR.$id)) {
					JFolder::delete($path_screen.DIRECTORY_SEPARATOR.$old_alias.DIRECTORY_SEPARATOR.$id);
				}
			}

			// Move trailers and their content
			$path_trailers = $params->get('media_trailers_root');
			$old_folder_trailers = $path_trailers.DIRECTORY_SEPARATOR.$old_alias.DIRECTORY_SEPARATOR.$id;
			$new_folder_trailers = $path_trailers.DIRECTORY_SEPARATOR.$new_alias.DIRECTORY_SEPARATOR.$id;

			if (KAFilesystemHelper::move($old_folder_trailers, $new_folder_trailers, true)) {
				if ($fs_helper::getFolderSize($old_folder_trailers) === 0) {
					if (file_exists($old_folder_trailers)) {
						JFolder::delete($old_folder_trailers);
					}
				}
			} else {
				$this->setError('Error while moving the files from trailer folders into new location! See log for more information.');
			}
		}

		return true;
	}

	/**
	 * Update statistics on genres
	 *
	 * @param   string   $old   Original genres list(before edit).
	 * @param   string   $new   New genres list.
	 *
	 * @return  mixed   True on success, exception otherwise
	 *
	*/
	protected function updateGenresStat($old, $new) {
		$db = $this->getDBO();
		$old_arr = !is_array($old) ? explode(',', $old) : $old;
		$new_arr = !is_array($new) ? explode(',', $new) : $new;
		$all = array_merge($old_arr, $new_arr);

		$query = true;
		$db->setDebug(true);
		$db->lockTable('#__ka_genres');
		$db->transactionStart();

		foreach ($all as $genre_id) {
			$db->setQuery("UPDATE ".$db->quoteName('#__ka_genres')
			. "\n SET `stats` = (SELECT COUNT(`genre_id`) FROM ".$db->quoteName('#__ka_rel_genres')." WHERE `genre_id` = ".(int)$genre_id.")"
			. "\n WHERE `id` = ".(int)$genre_id.";");
			$_query = $db->execute();

			if ($_query === false) {
				$query = false;
				break;
			}
		}

		if ($query === false) {
			$db->transactionRollback();
			$this->setError('Commit failed!');
			return false;
		} else {
			$db->transactionCommit();
		}

		$db->unlockTables();
		$db->setDebug(false);

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
			. "\n LEFT JOIN ".$db->quoteName('#__ka_rel_names')." AS `t` ON `t`.`name_id` = `n`.`id` AND `t`.`movie_id` = ".(int)$id
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
		$limit = $app->input->get('rows', 50, 'int');
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

	public function getPremieres() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$id = $app->input->get('id', null, 'int');
		$orderby = $app->input->get('sidx', '1', 'string');
		$order = $app->input->get('sord', 'asc', 'word');
		$limit = $app->input->get('rows', 50, 'int');
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

		$db->setQuery("SELECT COUNT(`id`)"
			. "\n FROM ".$db->quoteName('#__ka_premieres')
			. "\n WHERE `movie_id` = ".(int)$id);
		$total = $db->loadResult();

		$total_pages = ($total > 0) ? ceil($total / $limit) : 0;
		$page = ($page > $total_pages) ? $total_pages : $page;

		$db->setQuery("SELECT `p`.`id`, `p`.`movie_id`, `p`.`premiere_date`, `p`.`info`, `p`.`ordering`, `v`.`company_name`, `v`.`company_name_intl`, `c`.`name`"
			. "\n FROM ".$db->quoteName('#__ka_premieres')." AS `p`"
			. "\n LEFT JOIN ".$db->quoteName('#__ka_vendors')." AS `v` ON `v`.`id` = `p`.`vendor_id`"
			. "\n LEFT JOIN ".$db->quoteName('#__ka_countries')." AS `c` ON `c`.`id` = `p`.`country_id`"
			. "\n WHERE `movie_id` = ".(int)$id.$where
			. "\n ORDER BY ".$db->quoteName($orderby).' '.strtoupper($order), $limitstart, $limit);
		$rows = $db->loadObjectList();

		$k = 0;
		foreach ($rows as $elem) {
			$result->rows[$k]['id'] = $elem->id.'_'.$elem->movie_id;
			$vendor_0 = !empty($elem->company_name) ? $elem->company_name : '';
			$vendor_1 = (!empty($elem->company_name) && !empty($elem->company_name_intl)) ? ' / ' : '';
			$vendor_2 = !empty($elem->company_name_intl) ? $elem->company_name_intl : '';
			$country = !empty($elem->name) ? $elem->name : JText::_('COM_KA_PREMIERE_WORLD');
			$result->rows[$k]['cell'] = array(
				'id'			=> $elem->id,
				'vendor'		=> $vendor_0.$vendor_1.$vendor_2,
				'premiere_date' => $elem->premiere_date,
				'country'		=> $country,
				'ordering'		=> $elem->ordering
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

	public function saveMovieAccessRules() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$data = $app->input->post->get('form', array(), 'array');
		$id = $app->input->get('id', null, 'int');
		$rules = array();

		if (empty($id)) {
			return array('success'=>false, 'message'=>'Error');
		}

		foreach ($data['movie']['rules'] as $rule=>$groups) {
			foreach ($groups as $group=>$value) {
				if ($value != '') {
					$rules[$rule][$group] = (int)$value;
				} else {
					unset($data['rules'][$rule][$group]);
				}
			}
		}

		$rules = json_encode($rules);

		if (JFactory::getUser()->authorise('core.admin', 'com_kinoarhiv') && JFactory::getUser()->authorise('core.edit.access', 'com_kinoarhiv')) {
			// Get parent id
			$db->setQuery("SELECT `id` FROM ".$db->quoteName('#__assets')." WHERE `name` = 'com_kinoarhiv' AND `parent_id` = 1");
			$parent_id = $db->loadResult();

			$db->setQuery("UPDATE ".$db->quoteName('#__assets')
				. "\n SET `rules` = '".$rules."'"
				. "\n WHERE `name` = 'com_kinoarhiv.movie.".(int)$id."' AND `level` = 2 AND `parent_id` = ".(int)$parent_id);

			try {
				$db->execute();
				return array('success'=>true);
			} catch(Exception $e) {
				return array('success'=>false, 'message'=>$e->getMessage());
			}
		} else {
			return array('success'=>false, 'message'=>JText::_('COM_KA_NO_ACCESS_RULES_SAVE'));
		}
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param   JForm   $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  mixed  Array of filtered data if valid, false otherwise.
	 *
	 * @see     JFormRule
	 * @see     JFilterInput
	 * @since   12.2
	 */
	public function validate($form, $data, $group = null) {
		// Filter and validate the form data.
		$data = $form->filter($data);
		$return = $form->validate($data, $group);

		// Check for an error.
		if ($return instanceof Exception) {
			$this->setError($return->getMessage());
			return false;
		}

		// Check the validation results.
		if ($return === false) {
			// Get the validation messages from the form.
			foreach ($form->getErrors() as $message) {
				$this->setError($message);
			}

			return false;
		}

		return $data;
	}
}
