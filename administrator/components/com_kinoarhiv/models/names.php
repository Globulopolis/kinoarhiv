<?php defined('_JEXEC') or die;
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url			http://киноархив.com/
 */

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
				'language', 'a.language',
				'published');
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

		$access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access');
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
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = ' . (int) substr($search, 3));
			} elseif (stripos($search, 'access:') === 0) {
				$search = trim(substr($search, 7));

				if (is_numeric($search)) {
					$query->where('a.access = '.(int)$search);
				} else {
					$search = $db->quote('%' . $db->escape(trim($search), true) . '%');
					$query->where('ag.title LIKE ' . $search);
				}
			} else {
				$search = $db->quote('%' . $db->escape(trim($search), true) . '%');
				$query->where('(a.name LIKE ' . $search . ' OR a.latin_name LIKE ' . $search . ' OR a.alias LIKE ' . $search . ')');
			}
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering', 'a.id');
		$orderDirn = $this->state->get('list.direction', 'desc');

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

	public function publish($isUnpublish) {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$ids = $app->input->get('id', array(), 'array');
		$state = $isUnpublish ? 0 : 1;

		$db->setQuery("UPDATE ".$db->quoteName('#__ka_names')." SET `state` = '".(int)$state."' WHERE `id` IN (".implode(',', $ids).")");

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
		$db->setQuery("DELETE FROM ".$db->quoteName('#__ka_rel_awards')." WHERE `item_id` IN (".implode(',', $ids).") AND `type` = 1");
		try {
			$db->execute();
		} catch(Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}

		// Remove genre relations
		$db->setQuery("DELETE FROM ".$db->quoteName('#__ka_rel_names_genres')." WHERE `name_id` IN (".implode(',', $ids).")");
		try {
			$db->execute();
		} catch(Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}

		// Remove favorited and watched movies
		$db->setQuery("DELETE FROM ".$db->quoteName('#__ka_user_marked_names')." WHERE `name_id` IN (".implode(',', $ids).")");
		try {
			$db->execute();
		} catch(Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}

		// Remove career relations
		$db->setQuery("DELETE FROM ".$db->quoteName('#__ka_rel_names_career')." WHERE `name_id` IN (".implode(',', $ids).")");
		try {
			$db->execute();
		} catch(Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}

		// Remove media items
		$db->setQuery("SELECT `id`, SUBSTRING(`alias`, 1, 1) AS `alias` FROM ".$db->quoteName('#__ka_names')." WHERE `id` IN (".implode(',', $ids).")");
		$items = $db->loadObjectList();

		foreach ($items as $item) {
			// Delete root folders
			if (file_exists($params->get('media_actor_posters_root').DIRECTORY_SEPARATOR.$item->alias.DIRECTORY_SEPARATOR.$item->id)) {
				JFolder::delete($params->get('media_actor_posters_root').DIRECTORY_SEPARATOR.$item->alias.DIRECTORY_SEPARATOR.$item->id);
			}
			if (file_exists($params->get('media_actor_photo_root').DIRECTORY_SEPARATOR.$item->alias.DIRECTORY_SEPARATOR.$item->id)) {
				JFolder::delete($params->get('media_actor_photo_root').DIRECTORY_SEPARATOR.$item->alias.DIRECTORY_SEPARATOR.$item->id);
			}
			if (file_exists($params->get('media_actor_wallpapers_root').DIRECTORY_SEPARATOR.$item->alias.DIRECTORY_SEPARATOR.$item->id)) {
				JFolder::delete($params->get('media_actor_wallpapers_root').DIRECTORY_SEPARATOR.$item->alias.DIRECTORY_SEPARATOR.$item->id);
			}
		}

		// Remove movie(s) from DB
		$db->setQuery("DELETE FROM ".$db->quoteName('#__ka_names')." WHERE `id` IN (".implode(',', $ids).")");
		try {
			$db->execute();
		} catch(Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}

		// Remove gallery items
		$db->setQuery("DELETE FROM ".$db->quoteName('#__ka_names_gallery')." WHERE `name_id` IN (".implode(',', $ids).")");
		try {
			$db->execute();
		} catch(Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}

		// Remove access rules
		foreach ($ids as $id) {
			$db->setQuery("DELETE FROM ".$db->quoteName('#__assets')." WHERE `name` = 'com_kinoarhiv.name.".(int)$id."' AND `level` = 2");
			$db->execute();
		}

		return true;
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
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$ids = $app->input->post->get('id', array(), 'array');
		$batch_data = $app->input->post->get('batch', array(), 'array');

		if (!empty($batch_data['language_id'])) {
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__ka_names'))
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

		if (!empty($batch_data['assetgroup_id'])) {
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__ka_names'))
				->set("`access` = '".(int)$batch_data['assetgroup_id']."'")
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
