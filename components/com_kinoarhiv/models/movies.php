<?php defined('_JEXEC') or die;

class KinoarhivModelMovies extends JModelList {
	protected $context = null;

	public function __construct($config = array()) {
		parent::__construct($config);

		if (empty($this->context)) {
			$this->context = strtolower('com_kinoarhiv.movies.global');
		}
	}

	protected function populateState($ordering = null, $direction = null) {
		$params = JComponentHelper::getParams('com_kinoarhiv');

		parent::populateState($params->get('sort_movielist_field'), strtoupper($params->get('sort_movielist_ord')));
	}

	protected function getListQuery() {
		$db = $this->getDBO();
		$user = JFactory::getUser();
		$groups	= implode(',', $user->getAuthorisedViewLevels());
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');

		$query = $db->getQuery(true);

		$query->select("`m`.`id`, `m`.`parent_id`, `m`.`title`, `m`.`alias`, `m`.`introtext` AS `text`, `m`.`plot`, `m`.`rate_loc`, `m`.`rate_sum_loc`, `m`.`imdb_votesum`, `m`.`imdb_votes`, `m`.`imdb_id`, `m`.`kp_votesum`, `m`.`kp_votes`, `m`.`kp_id`, `m`.`rottentm_id`, `m`.`rate_custom`, `m`.`year`, DATE_FORMAT(`m`.`created`, '%Y-%m-%d') AS `created`, DATE_FORMAT(`m`.`modified`, '%Y-%m-%d') AS `modified`, `m`.`created_by`, `m`.`attribs`, `m`.`state`, `g`.`filename`, `g`.`dimension`");
		$query->from($db->quoteName('#__ka_movies').' AS `m`');
		$query->leftJoin($db->quoteName('#__ka_movies_gallery').' AS `g` ON `g`.`movie_id` = `m`.`id` AND `g`.`type` = 2 AND `g`.`poster_frontpage` = 1 AND `g`.`state` = 1');

		if (!$user->get('guest')) {
			$query->select(' `u`.`favorite`');
			$query->leftJoin($db->quoteName('#__ka_user_marked_movies').' AS `u` ON `u`.`uid` = '.$user->get('id').' AND `u`.`movie_id` = `m`.`id`');
		}

		$query->select(' `user`.`name` AS `username`, `user`.`email` AS `author_email`');
		$query->leftJoin($db->quoteName('#__users').' AS `user` ON `user`.`id` = `m`.`created_by`');

		$where = '`m`.`state` = 1 AND `language` IN ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').') AND `parent_id` = 0 AND `m`.`access` IN ('.$groups.')';

		if ($params->get('use_alphabet') == 1) {
			$letter = $app->input->get('letter', '', 'string');

			if ($letter != '') {
				if ($letter == '0-1') {
					$where .= ' AND (`m`.`title` LIKE "0%" AND `m`.`title` LIKE "1%" AND `m`.`title` LIKE "2%" AND `m`.`title` LIKE "3%" AND `m`.`title` LIKE "4%" AND `m`.`title` LIKE "5%" AND `m`.`title` LIKE "6%" AND `m`.`title` LIKE "7%" AND `m`.`title` LIKE "8%" AND `m`.`title` LIKE "9%")';
				} else {
					preg_match('#\p{L}#u', $letter, $matches); // only any kind of letter from any language.

					$where .= ' AND `m`.`title` LIKE "'.$db->escape(JString::strtoupper($matches[0])).'%"';
				}
			}
		}

		$query->where($where);

		$query->group($db->quoteName('m.id')); // Prevent duplicate records if accidentally have a more than one poster for frontpage.

		$orderCol = $this->state->get('list.ordering', $db->quoteName('m.ordering'));
		$orderDirn = $this->state->get('list.direction', 'DESC');
		$query->order($db->escape($orderCol.' '.$orderDirn));

		return $query;
	}

	public function favorite() {
		$db = $this->getDBO();
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		$action = $app->input->get('action', '', 'cmd');
		$movie_id = $app->input->get('id', 0, 'int');
		$movie_ids = $app->input->get('ids', array(), 'array');

		if (!empty($movie_ids)) {
			JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		}

		$itemid = $app->input->get('Itemid', 0, 'int');
		$success = false;
		$url = '';
		$text = '';

		if (empty($movie_ids)) {
			$db->setQuery("SELECT `favorite` FROM `#__ka_user_marked_movies` WHERE `uid` = ".(int)$user->get('id')." AND `movie_id` = ".(int)$movie_id);
			$query = $db->loadResult();
		}

		if ($action == 'add') {
			if ($query == 1) {
				$message = JText::_('COM_KA_FAVORITE_ERROR');
			} else {
				if (is_null($query)) {
					$db->setQuery("INSERT INTO `#__ka_user_marked_movies` (`uid`, `movie_id`, `favorite`, `watched`) VALUES ('".$user->get('id')."', '".(int)$movie_id."', '1', '0')");
				} elseif ($query == 0) {
					$db->setQuery("UPDATE `#__ka_user_marked_movies` SET `favorite` = '1' WHERE `uid` = ".$user->get('id')." AND `movie_id` = ".(int)$movie_id);
				}

				$r = $db->execute();

				if ($r) {
					$success = true;
					$message = JText::_('COM_KA_FAVORITE_ADDED');
					$url = JRoute::_('index.php?option=com_kinoarhiv&task=favorite&action=delete&Itemid='.$itemid.'&id='.$movie_id, false);
					$text = JText::_('COM_KA_REMOVEFROM_FAVORITE');
				} else {
					$message = JText::_('JERROR_ERROR');
				}
			}
		} elseif ($action == 'delete') {
			if ($query == 1) {
				$db->setQuery("UPDATE `#__ka_user_marked_movies` SET `favorite` = '0' WHERE `uid` = ".$user->get('id')." AND `movie_id` = ".(int)$movie_id);
				$r = $db->execute();

				if ($r) {
					$success = true;
					$message = JText::_('COM_KA_FAVORITE_REMOVED');
					$url = JRoute::_('index.php?option=com_kinoarhiv&task=favorite&action=add&Itemid='.$itemid.'&id='.$movie_id, false);
					$text = JText::_('COM_KA_ADDTO_FAVORITE');
				} else {
					$message = JText::_('JERROR_ERROR');
				}
			} else {
				if (!empty($movie_ids)) {
					$query = true;
					$db->setDebug(true);
					$db->lockTable('#__ka_user_marked_movies');
					$db->transactionStart();

					foreach ($movie_ids as $id) {
						$db->setQuery("UPDATE `#__ka_user_marked_movies` SET `favorite` = '0' WHERE `uid` = ".$user->get('id')." AND `movie_id` = ".(int)$id.";");
						$result = $db->execute();

						if ($result === false) {
							$query = false;
							break;
						}
					}

					if ($query === true) {
						$db->transactionCommit();

						$success = true;
						$message = JText::_('COM_KA_FAVORITE_REMOVED');
						$url = JRoute::_('index.php?option=com_kinoarhiv&task=favorite&action=add&Itemid='.$itemid.'&id='.$movie_id, false);
						$text = JText::_('COM_KA_ADDTO_FAVORITE');
					} else {
						$db->transactionRollback();

						$message = JText::_('JERROR_ERROR');
					}

					$db->unlockTables();
					$db->setDebug(false);
				} else {
					$message = JText::_('JERROR_AN_ERROR_HAS_OCCURRED');
				}
			}
		} else {
			$message = JText::_('JERROR_AN_ERROR_HAS_OCCURRED');
		}

		return array('success'=>$success, 'message'=>$message, 'url'=>$url, 'text'=>$text);
	}

	public function watched() {
		$db = $this->getDBO();
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		$action = $app->input->get('action', '', 'cmd');
		$movie_id = $app->input->get('id', 0, 'int');
		$movie_ids = $app->input->get('ids', array(), 'array');

		if (!empty($movie_ids)) {
			JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		}

		$itemid = $app->input->get('Itemid', 0, 'int');
		$success = false;
		$url = '';
		$text = '';

		if (empty($movie_ids)) {
			$db->setQuery("SELECT `watched` FROM `#__ka_user_marked_movies` WHERE `uid` = ".(int)$user->get('id')." AND `movie_id` = ".(int)$movie_id);
			$query = $db->loadResult();
		}

		if ($action == 'add') {
			if ($query == 1) {
				$message = JText::_('COM_KA_WATCHED_ERROR');
			} else {
				if (is_null($query)) {
					$db->setQuery("INSERT INTO `#__ka_user_marked_movies` (`uid`, `movie_id`, `favorite`, `watched`) VALUES ('".$user->get('id')."', '".(int)$movie_id."', '0', '1')");
				} elseif ($query == 0) {
					$db->setQuery("UPDATE `#__ka_user_marked_movies` SET `watched` = '1' WHERE `uid` = ".$user->get('id')." AND `movie_id` = ".(int)$movie_id);
				}

				$r = $db->execute();

				if ($r) {
					$success = true;
					$message = JText::_('COM_KA_WATCHED_ADDED');
					$url = JRoute::_('index.php?option=com_kinoarhiv&task=watched&action=delete&Itemid='.$itemid.'&id='.$movie_id, false);
					$text = JText::_('COM_KA_REMOVEFROM_WATCHED');
				} else {
					$message = JText::_('JERROR_ERROR');
				}
			}
		} elseif ($action == 'delete') {
			if ($query == 1) {
				$db->setQuery("UPDATE `#__ka_user_marked_movies` SET `watched` = '0' WHERE `uid` = ".$user->get('id')." AND `movie_id` = ".(int)$movie_id);
				$r = $db->execute();

				if ($r) {
					$success = true;
					$message = JText::_('COM_KA_WATCHED_REMOVED');
					$url = JRoute::_('index.php?option=com_kinoarhiv&task=watched&action=add&Itemid='.$itemid.'&id='.$movie_id, false);
					$text = JText::_('COM_KA_ADDTO_WATCHED');
				} else {
					$message = JText::_('JERROR_ERROR');
				}
			} else {
				if (!empty($movie_ids)) {
					$query = true;
					$db->setDebug(true);
					$db->lockTable('#__ka_user_marked_movies');
					$db->transactionStart();

					foreach ($movie_ids as $id) {
						$db->setQuery("UPDATE `#__ka_user_marked_movies` SET `watched` = '0' WHERE `uid` = ".$user->get('id')." AND `movie_id` = ".(int)$id.";");
						$r = $db->execute();

						if ($r === false) {
							$query = false;
							break;
						}
					}

					if ($query === true) {
						$db->transactionCommit();

						$success = true;
						$message = JText::_('COM_KA_WATCHED_REMOVED');
						$url = JRoute::_('index.php?option=com_kinoarhiv&task=watched&action=add&Itemid='.$itemid.'&id='.$movie_id, false);
						$text = JText::_('COM_KA_ADDTO_WATCHED');
					} else {
						$db->transactionRollback();

						$message = JText::_('JERROR_ERROR');
					}

					$db->unlockTables();
					$db->setDebug(false);
				} else {
					$message = JText::_('JERROR_AN_ERROR_HAS_OCCURRED');
				}
			}
		} else {
			$message = JText::_('JERROR_AN_ERROR_HAS_OCCURRED');
		}

		return array('success'=>$success, 'message'=>$message, 'url'=>$url, 'text'=>$text);
	}

	public function getPagination() {
		JLoader::register('KAPagination', JPATH_COMPONENT.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'pagination.php');

		$store = $this->getStoreId('getPagination');

		if (isset($this->cache[$store])) {
			return $this->cache[$store];
		}

		$limit = (int)$this->getState('list.limit') - (int)$this->getState('list.links');
		$page = new KAPagination($this->getTotal(), $this->getStart(), $limit);

		$this->cache[$store] = $page;

		return $this->cache[$store];
	}
}
