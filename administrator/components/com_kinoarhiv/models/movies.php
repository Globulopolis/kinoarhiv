<?php defined('_JEXEC') or die;
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url			http://киноархив.com/
 */

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
				'created_by', 'a.created_by',
				'ordering', 'a.ordering',
				'language', 'a.language',
				'published', 'a.published',
				'author_id',
				'level',
				'tag');
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

		$authorId = $app->getUserStateFromRequest($this->context . '.filter.author_id', 'filter_author_id');
		$this->setState('filter.author_id', $authorId);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$level = $this->getUserStateFromRequest($this->context . '.filter.level', 'filter_level');
		$this->setState('filter.level', $level);

		$language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		$tag = $this->getUserStateFromRequest($this->context . '.filter.tag', 'filter_tag', '');
		$this->setState('filter.tag', $tag);

		// List state information.
		parent::populateState('a.title', 'asc');

		// Force a language
		$forcedLanguage = $app->input->get('forcedLanguage');
		if (!empty($forcedLanguage))
		{
			$this->setState('filter.language', $forcedLanguage);
			$this->setState('filter.forcedLanguage', $forcedLanguage);
		}
	}

	protected function getStoreId($id = '') {
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.access');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.author_id');
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
				'a.id, a.title, a.year, a.alias, a.state, a.access, a.created, a.created_by, a.ordering, a.language'
			)
		);
		$query->from('#__ka_movies AS a');

		// Join over the language
		$query->select(' l.title AS language_title')
			->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = a.language');

		// Join over the users for the author.
		$query->select('ua.name AS author_name')
			->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

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

		// Filter on the level
		$level = $this->getState('filter.level');
		if (is_numeric($level)) {
			$query->where('a.parent_id = ' . (int) $level);
		} elseif ($level === '') {
			$query->where('(a.parent_id = 0 OR a.parent_id = 1)');
		}

		// Filter by author
		$authorId = $this->getState('filter.author_id');
		if (is_numeric($authorId)) {
			$type = $this->getState('filter.author_id.include', true) ? '= ' : '<>';
			$query->where('a.created_by ' . $type . (int) $authorId);
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
				$search = $db->quote('%' . $db->escape(trim($search), true) . '%');
				$query->where('(a.title LIKE ' . $search . ' OR a.alias LIKE ' . $search . ')');
			}
		}

		
		// Filter on the language.
		if ($language = $this->getState('filter.language')) {
			$query->where('a.language = ' . $db->quote($language));
		}

		// Filter by a single tag.
		$tagId = $this->getState('filter.tag');
		if (is_numeric($tagId)) {
			$query->where($db->quoteName('tagmap.tag_id') . ' = ' . (int) $tagId)
				->join(
					'LEFT', $db->quoteName('#__contentitem_tag_map', 'tagmap')
					. ' ON ' . $db->quoteName('tagmap.content_item_id') . ' = ' . $db->quoteName('a.id')
					. ' AND ' . $db->quoteName('tagmap.type_alias') . ' = ' . $db->quote('com_kinoarhiv.movie')
				);
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering', 'a.title');
		$orderDirn = $this->state->get('list.direction', 'asc');
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

		// Remove tags mapping
		$db->setQuery("DELETE FROM ".$db->quoteName('#__contentitem_tag_map')." WHERE `content_item_id` IN (".implode(',', $ids).")");
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

		$db->setQuery("DELETE FROM ".$db->quoteName('#__ka_movies_gallery')." WHERE `movie_id` IN (".implode(',', $ids).")");
		try {
			$db->execute();
		} catch(Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}

		// Remove trailers
		$db->setQuery("DELETE FROM ".$db->quoteName('#__ka_trailers')." WHERE `movie_id` IN (".implode(',', $ids).")");
		try {
			$db->execute();
		} catch(Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}

		// Remove access rules
		foreach ($ids as $id) {
			$db->setQuery("DELETE FROM ".$db->quoteName('#__assets')." WHERE `name` = 'com_kinoarhiv.movie.".(int)$id."' AND `level` = 2");
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

	public function batch() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$ids = $app->input->post->get('id', array(), 'array');
		$batch_data = $app->input->post->get('batch', array(), 'array');

		if (empty($batch_data)) {
			return false;
		}

		$fields = array();

		if (!empty($batch_data['language_id'])) {
			$fields[] = $db->quoteName('language')." = '".$db->escape((string)$batch_data['language_id'])."'";
		}
		if (!empty($batch_data['assetgroup_id'])) {
			$fields[] = $db->quoteName('access')." = '".(int)$batch_data['assetgroup_id']."'";
		}

		if (empty($fields)) {
			return false;
		}

		$query = $db->getQuery(true);

		$query->update($db->quoteName('#__ka_movies'))
			->set(implode(', ', $fields))
			->where($db->quoteName('id').' IN ('.implode(',', $ids).')');

		$db->setQuery($query);

		try {
			$db->execute();
		} catch (Exception $e) {
			$this->setError($e->getMessage());
		
			return false;
		}

		if (!empty($batch_data['tag'])) {
			foreach ($ids as $id) {
				$db->setQuery("SELECT `metadata` FROM ".$db->quoteName('#__ka_movies')." WHERE `id` = ".(int)$id);
				$result = $db->loadObject();
				$obj = json_decode($result->metadata);

				if (is_array($batch_data['tag'])) {
					$obj->tags = array_unique(array_merge($obj->tags, $batch_data['tag']));
				} else {
					if (!in_array($batch_data['tag'], $obj->tags)) {
						$obj->tags[] = (int)$batch_data['tag'];
					}
				}

				$db->setQuery("UPDATE ".$db->quoteName('#__ka_movies')." SET `metadata` = '".json_encode($obj)."' WHERE `id` = ".(int)$id);

				try {
					$db->execute();
				} catch (Exception $e) {
					$this->setError($e->getMessage());

					return false;
				}
			}
		}

		return true;
	}
}
