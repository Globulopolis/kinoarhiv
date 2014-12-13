<?php defined('_JEXEC') or die;
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url			http://киноархив.com/
 */

class KinoarhivModelReviews extends JModelList {
	public function __construct($config = array()) {
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.id',
				'username', 'u.username',
				'title', 'm.title',
				'state', 'a.state',
				'type', 'a.type',
				'ip', 'a.ip',
				'created', 'a.created');
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

		$author_id = $this->getUserStateFromRequest($this->context . '.filter.author_id', 'filter_author_id', '');
		$this->setState('filter.author_id', $author_id);

		$type = $this->getUserStateFromRequest($this->context . '.filter.type', 'filter_type', '');
		$this->setState('filter.type', $type);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		// List state information.
		parent::populateState('a.created', 'desc');
	}

	protected function getStoreId($id = '') {
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.author_id');
		$id .= ':' . $this->getState('filter.type');
		$id .= ':' . $this->getState('filter.published');

		return parent::getStoreId($id);
	}

	protected function getListQuery() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$query = $db->getQuery(true);
		$task = $app->input->get('task', '', 'cmd');
		$uid = $app->input->get('uid', 0, 'int');
		$mid = $app->input->get('mid', 0, 'int');

		$query->select(
			$this->getState(
				'list.select',
				'`a`.`id`, `a`.`uid`, `a`.`movie_id`, `a`.`review`, `a`.`created`, `a`.`type`, `a`.`ip`, `a`.`state`'
			)
		);
		$query->from('#__ka_reviews AS `a`');

		$query->select(' `u`.`name` AS `username`')
			->join('LEFT', $db->quoteName('#__users') . ' AS `u` ON `u`.`id` = `a`.`uid`');

		$query->select(' `m`.`title` AS `movie`')
			->join('LEFT', $db->quoteName('#__ka_movies') . ' AS `m` ON `m`.`id` = `a`.`movie_id`');

		// Filter by author ID
		$author_id = $this->getState('filter.author_id');
		if (is_numeric($author_id)) {
			$query->where('a.uid = ' . (int) $author_id);
		}

		// Filter by type
		$type = $this->getState('filter.type');
		if (is_numeric($type)) {
			$query->where('a.type = ' . (int) $type);
		} elseif ($type === '') {
			$query->where('(a.type = 0 OR a.type = 1 OR a.type = 2 OR a.type = 3)');
		}

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

	public function batch() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$ids = $app->input->post->get('id', array(), 'array');
		$batch_data = $app->input->post->get('batch', array(), 'array');

		if ($batch_data['type'] != '') {
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__ka_reviews'))
				->set("`type` = '".(int)$batch_data['batch-type']."'")
				->where('`id` IN ('.implode(',', $ids).')');

			$db->setQuery($query);
			try {
				$db->execute();
			} catch (Exception $e) {
				$this->setError($e->getMessage());

				return false;
			}
		}

		if ($batch_data['user_id'] != '') {
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__ka_reviews'))
				->set("`uid` = '".(int)$batch_data['user_id']."'")
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
