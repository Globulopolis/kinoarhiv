<?php defined('_JEXEC') or die;

class KinoarhivModelReviews extends JModelList {
	protected $_forms = array();

	public function __construct($config = array()) {
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.id',
				'username', 'u.username',
				'title', 'm.title',
				'state', 'a.state',
				'type', 'a.type',
				'ip', 'a.ip',
				'created', 'a.created',);
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

	protected function populateState($ordering = null, $direction = null) {
		$app = JFactory::getApplication();

		// Adjust the context to support modal layouts.
		if ($layout = $app->input->get('layout')) {
			$this->context .= '.' . $layout;
		}

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		// List state information.
		parent::populateState('a.created', 'desc');
	}

	protected function getStoreId($id = '') {
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');

		return parent::getStoreId($id);
	}

	public function getForm($data = array(), $loadData = true) {
		$form = $this->loadForm('com_kinoarhiv.review', 'review', array('control' => 'form', 'load_data' => $loadData));

		if (empty($form)) {
			return false;
		}

		return $form;
	}

	protected function loadFormData() {
		return $this->getItem();
	}

	protected function getListQuery() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$query = $db->getQuery(true);
		$task = $app->input->get('task', '', 'cmd');
		$uid = $app->input->get('uid', 0, 'int');
		$mid = $app->input->get('mid', 0, 'int');

		$query->select('`a`.`id`, `a`.`uid`, `a`.`movie_id`, `a`.`review`, `a`.`created`, `a`.`type`, `a`.`ip`, `a`.`state`');
		$query->from('#__ka_reviews AS `a`');

		$query->select(' `u`.`username` AS `username`')
			->join('LEFT', $db->quoteName('#__users') . ' AS `u` ON `u`.`id` = `a`.`uid`');

		$query->select(' `m`.`title` AS `movie`')
			->join('LEFT', $db->quoteName('#__ka_movies') . ' AS `m` ON `m`.`id` = `a`.`movie_id`');

		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('a.state = ' . (int) $published);
		} elseif ($published === '') {
			$query->where('(a.state = 0 OR a.state = 1)');
		}

		// Filter by search string.
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = ' . (int) substr($search, 3));
			} elseif (stripos($search, 'movie:') === 0) {
				$search = $db->quote('%' . $db->escape(trim(substr($search, 6)), true) . '%');
				$query->where('m.title LIKE ' . $search);
			} elseif (stripos($search, 'user:') === 0) {
				$search = $db->quote('%' . $db->escape(trim(substr($search, 5)), true) . '%');
				$query->where('u.username LIKE ' . $search);
			} elseif (stripos($search, 'ip:') === 0) {
				$search = $db->quote('%' . $db->escape(trim(substr($search, 3)), true) . '%');
				$query->where('a.ip LIKE ' . $search);
			} elseif (stripos($search, 'type:') === 0) {
				$query->where('a.type = ' . (int) substr($search, 5));
			} elseif (stripos($search, 'date:') === 0) {
				$search = $db->quote('%' . $db->escape(trim(substr($search, 5)), true) . '%');
				$query->where('a.created LIKE ' . $search);
			} else {
				$search = $db->quote('%' . $db->escape($search, true) . '%');
				$query->where('(a.review LIKE ' . $search . ')');
			}
		}

		if (!empty($uid) && is_numeric($uid)) {
			$query->where('u.id = ' . (int) $uid);
		}

		if (!empty($mid) && is_numeric($mid)) {
			$query->where('m.id = ' . (int) $mid);
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering', 'a.created');
		$orderDirn = $this->state->get('list.direction', 'desc');

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

	public function publish($isUnpublish) {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$ids = $app->input->get('id', array(), 'array');
		$state = $isUnpublish ? 0 : 1;

		$db->setQuery("UPDATE ".$db->quoteName('#__ka_reviews')." SET `state` = '".(int)$state."' WHERE `id` IN (".implode(',', $ids).")");

		try {
			$db->execute();

			return true;
		} catch(Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}
	}

	public function remove() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$ids = $app->input->get('id', array(), 'array');

		$db->setQuery("DELETE FROM ".$db->quoteName('#__ka_reviews')." WHERE `id` IN (".implode(',', $ids).")");

		try {
			$db->execute();

			return true;
		} catch(Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}
	}

	public function getItem() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$task = $app->input->get('task', '', 'cmd');
		$id = $app->input->get('id', array(), 'array');

		$db->setQuery("SELECT `id`, `uid`, `movie_id`, `review`, `created`, `type`, `ip`, `state`"
			. "\n FROM ".$db->quoteName('#__ka_reviews')
			. "\n WHERE `id` = ".(int)$id[0]);
		$result = $db->loadObject();

		return $result;
	}

	public function save($data) {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$id = $app->input->post->get('id', null, 'int');

		$db->setQuery("UPDATE ".$db->quoteName('#__ka_reviews')
			. "\n SET `uid` = '".(int)$data['uid']."', `movie_id` = '".(int)$data['movie_id']."', `review` = '".$db->escape($data['review'])."', `created` = '".$data['created']."', `type` = '".(int)$data['type']."', `ip` = '".(string)$data['ip']."', `state` = '".(int)$data['state']."'"
			. "\n WHERE `id` = ".(int)$id);

		try {
			$db->execute();

			return true;
		} catch(Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}
	}
}
