<?php defined('_JEXEC') or die;

class KinoarhivModelVendors extends JModelList {
	public function __construct($config = array()) {
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'v.id',
				'company_name', 'v.company_name',
				'company_name_intl', 'v.company_name_intl',
				'state', 'v.state',
				'language', 'v.language',);
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

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

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
		parent::populateState('v.company_name', 'asc');
	}

	protected function getStoreId($id = '') {
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.language');

		return parent::getStoreId($id);
	}

	protected function getListQuery() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$query = $db->getQuery(true);
		$task = $app->input->get('task', '', 'cmd');

		$query->select(
			$this->getState(
				'list.select',
				'`v`.`id`, `v`.`company_name`, `v`.`company_name_intl`, `v`.`company_name_alias`, `v`.`language`, `v`.`state`'
			)
		);
		$query->from('#__ka_vendors AS `v`');

		// Join over the language
		$query->select(' `l`.`title` AS `language_title`')
			->join('LEFT', $db->quoteName('#__languages') . ' AS `l` ON `l`.`lang_code` = `v`.`language`');

		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('v.state = ' . (int) $published);
		} elseif ($published === '') {
			$query->where('(v.state = 0 OR v.state = 1)');
		}

		// Filter by search in title.
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('v.id = ' . (int) substr($search, 3));
			} elseif (stripos($search, 'alias:') === 0) {
				$search = $db->quote('%' . $db->escape(trim(substr($search, 6)), true) . '%');
				$query->where('(v.company_name_alias LIKE ' . $search . ')');
			} else {
				$search = $db->quote('%' . $db->escape(trim($search), true) . '%');
				$query->where('(v.company_name LIKE ' . $search . ' OR v.company_name_intl LIKE ' . $search . ')');
			}
		}

		// Filter on the language.
		if ($language = $this->getState('filter.language')) {
			$query->where('v.language = ' . $db->quote($language));
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering', 'v.company_name');
		$orderDirn = $this->state->get('list.direction', 'asc');

		// SQL server change
		if ($orderCol == 'language') {
			$orderCol = 'l.title';
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

	public function batch() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$ids = $app->input->post->get('id', array(), 'array');
		$batch_data = $app->input->post->get('batch', array(), 'array');
		$query = $db->getQuery(true);

		if (!empty($batch_data['language_id'])) {
			$query->update($db->quoteName('#__ka_vendors'))
				->set("`language` = '".$db->escape((string)$batch_data['language_id'])."'")
				->where('`id` IN ('.implode(',', $ids).')');

			$db->setQuery($query);
			try {
				$db->execute();
			} catch (Exception $e) {
				$this->setError($e->getMessage());

				return false;
			}
		}

		return true;
	}
}
