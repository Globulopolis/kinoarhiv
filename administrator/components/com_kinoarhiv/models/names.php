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

		$language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		// Force a language
		$forcedLanguage = $app->input->get('forcedLanguage');
		if (!empty($forcedLanguage))
		{
			$this->setState('filter.language', $forcedLanguage);
			$this->setState('filter.forcedLanguage', $forcedLanguage);
		}

		// List state information.
		parent::populateState('a.id', 'DESC');
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
		$orderCol = $this->state->get('list.ordering', 'a.id');
		$orderDirn = $this->state->get('list.direction', 'desc');
		if ($orderCol == 'a.ordering') {
			$orderCol = 'a.title ' . $orderDirn . ', a.ordering';
		}
		// SQL server change
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

	public function saveOrder() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$data = $app->input->post->get('ord', array(), 'array');

		if (count($data) < 2) {
			return array('success'=>false, 'message'=>JText::_('COM_KA_SAVE_ORDER_AT_LEAST_TWO'));
		}

		$query = true;

		$db->setDebug(true);
		$db->lockTable('#__ka_names');
		$db->transactionStart();

		foreach ($data as $key=>$value) {
			$db->setQuery("UPDATE ".$db->quoteName('#__ka_names')." SET `ordering` = '".(int)$key."' WHERE `id` = ".(int)$value.";");
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

	public function batch() {
		/*$app = JFactory::getApplication();
		$db = $this->getDBO();
		$ids = $app->input->post->get('id', array(), 'array');
		$batch_data = $app->input->post->get('batch', array(), 'array');

		if (!empty($batch_data['language_id'])) {
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__ka_awards'))
				->set("`language` = '".$db->escape((string)$batch_data['language_id'])."'")
				->where('`id` IN ('.implode(',', $ids).')');

			$db->setQuery($query);
			try {
				$db->execute();
			} catch (Exception $e) {
				$this->setError($e->getMessage());

				return false;
			}
		}*/

		return true;
	}
}
