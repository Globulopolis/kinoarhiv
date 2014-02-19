<?php defined('_JEXEC') or die;

class KinoarhivModelMovies extends JModelList {
	protected function populateState($ordering = null, $direction = null) {
		$app = JFactory::getApplication();
		$params = $app->getParams('com_kinoarhiv');

		parent::populateState($params->get('sort_movielist_field'), strtoupper($params->get('sort_movielist_ord')));
	}

	protected function getListQuery() {
		$db = $this->getDBO();
		$user = JFactory::getUser();
		$groups	= implode(',', $user->getAuthorisedViewLevels());
		$app = JFactory::getApplication();
		$params = $app->getParams('com_kinoarhiv');

		// Filter by genre or something else
		$filter_by = $app->input->get('filter_by', array(), 'array');

		$query = $db->getQuery(true);

		$query->select("`m`.`id`, `m`.`parent_id`, `m`.`title`, `m`.`alias`, `m`.`introtext` AS `text`, `m`.`plot`, `m`.`rate_loc`, `m`.`rate_sum_loc`, `m`.`imdb_votesum`, `m`.`imdb_votes`, `m`.`imdb_id`, `m`.`kp_votesum`, `m`.`kp_votes`, `m`.`kp_id`, `m`.`rottentm_id`, `m`.`rate_custom`, `m`.`year`, DATE_FORMAT(`m`.`created`, '%Y-%m-%d') AS `created`, DATE_FORMAT(`m`.`modified`, '%Y-%m-%d') AS `modified`, `m`.`created_by`, `m`.`state`, `g`.`filename`");
		$query->from($db->quoteName('#__ka_movies').' AS `m`');
		$query->leftJoin($db->quoteName('#__ka_movies_gallery').' AS `g` ON `g`.`movie_id` = `m`.`id` AND `g`.`type` = 2 AND `g`.`poster_frontpage` = 1 AND `g`.`state` = 1');

		if (!$user->get('guest')) {
			$query->select('`u`.`favorite`');
			$query->leftJoin($db->quoteName('#__ka_user_marked_movies').' AS `u` ON `u`.`uid` = '.$user->get('id').' AND `u`.`movie_id` = `m`.`id`');
		}

		$where = '`m`.`state` = 1 AND `language` IN ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').') AND `parent_id` = 0 AND `m`.`access` IN ('.$groups.')';
		if (!empty($filter_by)) {
			// Filter by genre if not empty
			if (in_array('genre', $filter_by)) {
				$g_ids = $app->input->get('genre_id', $params->get('filter_genres'), 'array');

				// Check if genre is not contain an 'all' value (0)
				if (in_array(0, $g_ids)) {
					$where .= '';
				} else {
					$where .= ' AND `m`.`id` IN (SELECT `movie_id` FROM '.$db->quoteName('#__ka_rel_genres').' WHERE `genre_id` IN ('.implode(',', $g_ids).'))';
				}
			}
		}

		$query->where($where);

		$orderCol = $this->state->get('list.ordering', 'm.ordering');
		$orderDirn = $this->state->get('list.direction', 'desc');
		$query->order($db->escape($orderCol.' '.$orderDirn));

		return $query;
	}

	public function getGenres() {
		$db = JFactory::getDBO();
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$groups	= implode(',', $user->getAuthorisedViewLevels());
		$genre_id = $app->input->get('genre_id', array(0), 'array');

		$db->setQuery("SELECT `id`, CONCAT(UPPER(SUBSTRING(`name`, 1, 1)), LOWER(SUBSTRING(`name` FROM 2))) AS `name` FROM ".$db->quoteName('#__ka_genres')." WHERE `language` IN (".$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').") AND `state` = 1 AND `access` IN (".$groups.") ORDER BY `name` ASC");
		$result['list'] = $db->loadObjectList();

		$result['selected'] = $genre_id;

		array_push($result['list'],
			(object)array(
				'id'=>'0',
				'name'=>JText::_('JALL')
			)
		);

		return $result;
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
}
