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

	protected function getStoreId($id = '') {
		// Compile the store id.
		$id .= ':' . $this->getState('filter.title');

		return parent::getStoreId($id);
	}

	protected function getListQuery() {
		$db = $this->getDBO();
		$user = JFactory::getUser();
		$groups	= implode(',', $user->getAuthorisedViewLevels());
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$filters = $this->buildFilters($params);

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

		$query->where($where.$filters);

		$query->group($db->quoteName('m.id')); // Prevent duplicate records if accidentally have a more than one poster for frontpage.

		$orderCol = $this->state->get('list.ordering', $db->quoteName('m.ordering'));
		$orderDirn = $this->state->get('list.direction', 'DESC');
		$query->order($db->escape($orderCol.' '.$orderDirn));

		return $query;
	}

	/**
	 * Build WHERE from values from the search inputs
	 *
	 * @param   object   $params     Component parameters.
	 *
	 * @return   string
	 *
	*/
	protected function buildFilters(&$params) {
		$where = "";

		$db = $this->getDBO();
		$where_id = array();
		$searches = $this->getFiltersData();

		// Filter by title
		$title = $searches->get('filters.movies.title');
		if ($params->get('search_movies_title') == 1 && !empty($title)) {
			if (JString::strlen($title) < $params->get('search_movies_length_min') || JString::strlen($title) > $params->get('search_movies_length_max')) {
				echo GlobalHelper::showMsg(JText::sprintf('COM_KA_SEARCH_ERROR_SEARCH_MESSAGE', $params->get('search_movies_length_min'), $params->get('search_movies_length_max')), array('icon'=>'alert'), true);
			} else {
				$where .= " AND `m`.`title` LIKE '".$db->escape($title)."%'";
			}
		}

		// Filter by year
		$year = $searches->get('filters.movies.year');
		if ($params->get('search_movies_year') == 1 && !empty($year)) {
			$where .= " AND `m`.`year` LIKE '".$db->escape($year)."%'";
		} else {
			// Filter by years range
			$from_year = $searches->get('filters.movies.from_year');
			$to_year = $searches->get('filters.movies.to_year');
			if ($params->get('search_movies_year_range') == 1) {
				if (!empty($from_year) && !empty($to_year)) {
					$where .= " AND `m`.`year` BETWEEN '".$db->escape($from_year)."' AND '".$db->escape($to_year)."'";
				} else {
					if (!empty($from_year)) {
						$where .= " AND `m`.`year` REGEXP '^".$db->escape($from_year)."'";
					} elseif (!empty($to_year)) {
						$where .= " AND `m`.`year` REGEXP '".$db->escape($to_year)."$'";
					}
				}
			}
		}

		// Filter by country
		$country = $searches->get('filters.movies.country');
		if ($params->get('search_movies_country') == 1 && !empty($country)) {
			$db->setQuery("SELECT `movie_id` FROM ".$db->quoteName('#__ka_rel_countries')." WHERE `country_id` = ".(int)$country);
			$movie_ids = $db->loadColumn();

			$where_id = array_merge($where_id, $movie_ids);
		}

		// Filter by person name
		$cast = $searches->get('filters.movies.cast');
		if ($params->get('search_movies_cast') == 1 && !empty($cast)) {
			$db->setQuery("SELECT `movie_id` FROM ".$db->quoteName('#__ka_rel_names')." WHERE `name_id` = ".(int)$cast);
			$movie_ids = $db->loadColumn();

			$where_id = array_merge($where_id, $movie_ids);
		}

		// Filter by vendor
		$vendor = $searches->get('filters.movies.vendor');
		if ($params->get('search_movies_vendor') == 1 && !empty($vendor)) {
			$db->setQuery("SELECT `movie_id` FROM ".$db->quoteName('#__ka_releases')." WHERE `vendor_id` = ".(int)$vendor." GROUP BY `movie_id`");
			$movie_ids = $db->loadColumn();

			$where_id = array_merge($where_id, $movie_ids);
		}

		// Filter by genres
		$genres = $searches->get('filters.movies.genre');
		if ($params->get('search_movies_genre') == 1 && !empty($genres)) {
			$db->setQuery("SELECT `movie_id` FROM ".$db->quoteName('#__ka_rel_genres')." WHERE `genre_id` IN (".implode(',', $genres).") GROUP BY `movie_id`");
			$movie_ids = $db->loadColumn();

			$where_id = array_merge($where_id, $movie_ids);
		}

		// Filter by MPAA
		$mpaa = $searches->get('filters.movies.mpaa');
		if ($params->get('search_movies_mpaa') == 1 && !empty($mpaa)) {
			$where .= " AND `m`.`mpaa` = '".$db->escape($mpaa)."'";
		}

		// Filter by age
		$age_restrict = $searches->get('filters.movies.age_restrict');
		if ($params->get('search_movies_age_restrict') == 1 && (!empty($age_restrict) && $age_restrict != '-1')) {
			$where .= " AND `m`.`age_restrict` = '".$db->escape($age_restrict)."'";
		}

		// Filter by UA rating
		$ua_rate = $searches->get('filters.movies.ua_rate');
		if ($params->get('search_movies_ua_rate') == 1 && (!empty($ua_rate) && $ua_rate != '-1')) {
			$where .= " AND `m`.`ua_rate` = '".$db->escape($ua_rate)."'";
		}

		// Filter by site rating
		$rate = $searches->def('filters.movies.rate.enable', 0);
		if ($params->get('search_movies_rate') == 1 && $rate === 1) {
			$rate_min = $searches->def('filters.movies.rate.min', 0);
			$rate_max = $searches->def('filters.movies.rate.max', 10);
			$where .= " AND `m`.`rate_loc_rounded` BETWEEN ".(int)$rate_min." AND ".(int)$rate_max;
		}

		// Filter by imdb rating
		$imdbrate = $searches->def('filters.movies.imdbrate.enable', 0);
		if ($params->get('search_movies_imdbrate') == 1 && $imdbrate === 1) {
			$imdbrate_min = $searches->def('filters.movies.imdbrate.min', 6);
			$imdbrate_max = $searches->def('filters.movies.imdbrate.max', 10);
			$where .= " AND `m`.`rate_imdb_rounded` BETWEEN ".(int)$imdbrate_min." AND ".(int)$imdbrate_max;
		}

		// Filter by kinopoisk rating
		$kprate = $searches->def('filters.movies.kprate.enable', 0);
		if ($params->get('search_movies_kprate') == 1 && $kprate === 1) {
			$kprate_min = $searches->def('filters.movies.kprate.min', 6);
			$kprate_max = $searches->def('filters.movies.kprate.max', 10);
			$where .= " AND `m`.`rate_kp_rounded` BETWEEN ".(int)$kprate_min." AND ".(int)$kprate_max;
		}

		// Filter by rotten tomatoes rating
		$rtrate = $searches->def('filters.movies.rtrate.enable', 0);
		if ($params->get('search_movies_rtrate') == 1 && $rtrate === 1) {
			$rtrate_min = $searches->def('filters.movies.rtrate.min', 0);
			$rtrate_max = $searches->def('filters.movies.rtrate.max', 100);
			$where .= " AND `m`.`rate_fc` BETWEEN ".(int)$rtrate_min." AND ".(int)$rtrate_max;
		}

		// Filter by budget
		$from_budget = $searches->get('filters.movies.from_budget');
		$to_budget = $searches->get('filters.movies.to_budget');
		if ($params->get('search_movies_budget') == 1) {
			if (!empty($from_budget) && !empty($to_budget)) {
				$where .= " AND `m`.`budget` BETWEEN '".$db->escape($from_budget)."' AND '".$db->escape($to_budget)."'";
			} else {
				if (!empty($from_budget)) {
					$where .= " AND `m`.`budget` = '".$db->escape($from_budget)."'";
				} elseif (!empty($to_budget)) {
					$where .= " AND `m`.`budget` = '".$db->escape($to_budget)."'";
				}
			}
		}

		// Filter by tags
		$tags = $searches->get('filters.movies.tags');
		if ($params->get('search_movies_tags') == 1 && !empty($tags)) {
			$db->setQuery("SELECT `content_item_id` FROM ".$db->quoteName('#__contentitem_tag_map')
				. "\n WHERE `type_alias` = 'com_kinoarhiv.movie' AND `tag_id` IN (".$tags.")");
			$movie_ids = $db->loadColumn();

			$where_id = array_merge($where_id, $movie_ids);
		}

		if (!empty($country) || !empty($cast) || !empty($vendor) || !empty($genres) || !empty($tags) && !empty($where_id)) {
			$where .= " AND `m`.`id` IN (".implode(',', JArrayHelper::arrayUnique($where_id)).")";
		}

		return $where;
	}

	/**
	 * Get the values from search inputs
	 *
	 * @return   object
	 *
	*/
	public function getFiltersData() {
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$filter = JFilterInput::getInstance();
		$input = JFactory::getApplication()->input;
		$items = new JRegistry;

		if ($params->get('search_movies_enable') != 1) {
			return $items;
		}

		if (array_key_exists('movies', $input->get('filters', array(), 'array'))) {
			$filters = $input->get('filters', array(), 'array')['movies'];

			if (count($filters) < 1) {
				return $items;
			}

			// Using input->getArray cause an error when subarrays with no data
			$vars = array(
				'filters' => array(
					'movies' => array(
						'title'			=> isset($filters['title']) ? $filter->clean($filters['title'], 'string') : '',
						'year'			=> isset($filters['year']) ? $filter->clean($filters['year'], 'string') : '',
						'from_year'		=> isset($filters['from_year']) ? $filter->clean($filters['from_year'], 'int') : '',
						'to_year'		=> isset($filters['to_year']) ? $filter->clean($filters['to_year'], 'int') : '',
						'country'		=> isset($filters['country']) ? $filter->clean($filters['country'], 'int') : 0,
						'cast'			=> isset($filters['cast']) ? $filter->clean($filters['cast'], 'int') : 0,
						'vendor'		=> isset($filters['vendor']) ? $filter->clean($filters['vendor'], 'int') : '',
						'genre'			=> isset($filters['genre']) ? $filter->clean($filters['genre'], 'array') : '',
						'mpaa' 			=> isset($filters['mpaa']) ? $filter->clean($filters['mpaa'], 'string') : '',
						'age_restrict'	=> isset($filters['age_restrict']) ? $filter->clean($filters['age_restrict'], 'string') : '-1',
						'ua_rate'		=> isset($filters['ua_rate']) ? $filter->clean($filters['ua_rate'], 'int') : '-1',
						'rate'			=> array(
							'enable'=> isset($filters['rate']['enable']) ? $filter->clean($filters['rate']['enable'], 'int') : 0,
							'min'	=> isset($filters['rate']['min']) ? $filter->clean($filters['rate']['min'], 'int') : 0,
							'max'	=> isset($filters['rate']['max']) ? $filter->clean($filters['rate']['max'], 'int') : 10
						),
						'imdbrate'		=> array(
							'enable'=> isset($filters['imdbrate']['enable']) ? $filter->clean($filters['imdbrate']['enable'], 'int') : 0,
							'min'	=> isset($filters['imdbrate']['min']) ? $filter->clean($filters['imdbrate']['min'], 'int') : 6,
							'max'	=> isset($filters['imdbrate']['max']) ? $filter->clean($filters['imdbrate']['max'], 'int') : 10
						),
						'kprate'		=> array(
							'enable'=> isset($filters['kprate']['enable']) ? $filter->clean($filters['kprate']['enable'], 'int') : 0,
							'min'	=> isset($filters['kprate']['min']) ? $filter->clean($filters['kprate']['min'], 'int') : 6,
							'max'	=> isset($filters['kprate']['max']) ? $filter->clean($filters['kprate']['max'], 'int') : 10
						),
						'rtrate'		=> array(
							'enable'=> isset($filters['rtrate']['enable']) ? $filter->clean($filters['rtrate']['enable'], 'int') : 0,
							'min'	=> isset($filters['rtrate']['min']) ? $filter->clean($filters['rtrate']['min'], 'int') : 0,
							'max'	=> isset($filters['rtrate']['max']) ? $filter->clean($filters['rtrate']['max'], 'int') : 100
						),
						'from_budget'	=> isset($filters['from_budget']) ? $filter->clean($filters['from_budget'], 'string') : '',
						'to_budget'		=> isset($filters['to_budget']) ? $filter->clean($filters['to_budget'], 'string') : '',
						'tags'			=> isset($filters['tags']) ? $filter->clean($filters['tags'], 'string') : ''
					)
				)
			);

			$items->loadArray($vars);
		}

		return $items;
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
