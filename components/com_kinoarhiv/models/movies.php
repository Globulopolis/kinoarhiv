<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Joomla\String\StringHelper;

/**
 * Movies list class
 *
 * @since  3.0
 */
class KinoarhivModelMovies extends JModelList
{
	protected $context = null;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JModelLegacy
	 * @since   3.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			// Setup a list of columns for ORDER BY from 'sort_movielist_field' params from component settings
			$config['filter_fields'] = array('id', 'm.id', 'title', 'year', 'created', 'ordering', 'm.ordering');
		}

		parent::__construct($config);

		if (empty($this->context))
		{
			$this->context = strtolower('com_kinoarhiv.movies');
		}
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		if ($this->context)
		{
			$app = JFactory::getApplication();
			$params = JComponentHelper::getParams('com_kinoarhiv');

			$value = $app->getUserStateFromRequest($this->context . '.list.limit', 'limit', $params->get('list_limit'), 'uint');
			$limit = $value;
			$this->setState('list.limit', $value);

			$value = $app->getUserStateFromRequest($this->context . '.limitstart', 'limitstart', 0);
			$limitstart = ($limit != 0 ? (floor($value / $limit) * $limit) : 0);
			$this->setState('list.start', $limitstart);

			$value = $app->getUserStateFromRequest($this->context . '.ordercol', 'filter_order', $params->get('sort_movielist_field'));

			if (!in_array($value, $this->filter_fields))
			{
				$value = $ordering;
				$app->setUserState($this->context . '.ordercol', $value);
			}

			$this->setState('list.ordering', $value);

			$value = $app->getUserStateFromRequest($this->context . '.orderdirn', 'filter_order_Dir', strtoupper($params->get('sort_movielist_ord')));

			if (!in_array(strtoupper($value), array('ASC', 'DESC', '')))
			{
				$value = $direction;
				$app->setUserState($this->context . '.orderdirn', $value);
			}

			$this->setState('list.direction', $value);
		}
		else
		{
			$this->setState('list.start', 0);
			$this->state->set('list.limit', 0);
		}
	}

	/**
	 * Method to get a store id based on the model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  An identifier string to generate the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   3.0
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('list.limit');
		$id .= ':' . $this->getState('list.ordering');
		$id .= ':' . $this->getState('list.direction');

		return parent::getStoreId($id);
	}

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set from a database.
	 *
	 * @return  JDatabaseQuery   A JDatabaseQuery object to retrieve the data set.
	 *
	 * @since   3.0
	 */
	protected function getListQuery()
	{
		$db = $this->getDbo();
		$user = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');

		// Define null and now dates
		$null_date = $db->quote($db->getNullDate());
		$now_date = $db->quote(JFactory::getDate()->toSql());

		$query = $db->getQuery(true);

		$query->select(
			$this->getState(
				'list.select',
				'm.id, m.parent_id, m.title, m.alias, m.fs_alias, ' . $db->quoteName('m.introtext', 'text') . ', m.plot, ' .
				'm.rate_loc, m.rate_sum_loc, m.imdb_votesum, m.imdb_votes, m.imdb_id, m.kp_votesum, ' .
				'm.kp_votes, m.kp_id, m.rate_fc, m.rottentm_id, m.metacritics, m.metacritics_id, ' .
				'm.rate_custom, m.year, DATE_FORMAT(m.created, "%Y-%m-%d") AS ' . $db->quoteName('created') . ', m.created_by, ' .
				'CASE WHEN m.modified = ' . $null_date . ' THEN m.created ELSE DATE_FORMAT(m.modified, "%Y-%m-%d") END AS modified, ' .
				'CASE WHEN m.publish_up = ' . $null_date . ' THEN m.created ELSE m.publish_up END AS publish_up, ' .
				'm.publish_down, m.attribs, m.state'
			)
		);
		$query->from($db->quoteName('#__ka_movies', 'm'));

		// Join over gallery item
		$query->select($db->quoteName(array('g.filename', 'g.dimension')))
			->join('LEFT', $db->quoteName('#__ka_movies_gallery', 'g') . ' ON g.movie_id = m.id AND g.type = 2 AND g.poster_frontpage = 1 AND g.state = 1');

		// Join over favorited
		if (!$user->get('guest'))
		{
			$query->select($db->quoteName('u.favorite'));
			$query->leftJoin($db->quoteName('#__ka_user_marked_movies', 'u') . ' ON u.uid = ' . $user->get('id') . ' AND u.movie_id = m.id');
		}

		$query->select($db->quoteName('user.name', 'username') . ', ' . $db->quoteName('user.email', 'author_email'));
		$query->leftJoin($db->quoteName('#__users', 'user') . ' ON user.id = m.created_by');

		$query->where('m.state = 1 AND language IN (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ') AND parent_id = 0 AND m.access IN (' . $groups . ')');

		if ($params->get('use_alphabet') == 1)
		{
			$letter = $app->input->get('letter', '', 'string');

			if ($letter != '')
			{
				if ($letter == '0-1')
				{
					$range = range(0, 9);
					$query->where('(m.title LIKE "' . implode('%" OR m.title LIKE "', $range) . '%")');
				}
				else
				{
					if (preg_match('#\p{L}#u', $letter, $matches))
					{
						// Only any kind of letter from any language.
						$query->where('m.title LIKE "' . $db->escape(StringHelper::strtoupper($matches[0])) . '%"');
					}
				}
			}
		}

		// Filter by start and end dates.
		if ((!$user->authorise('core.edit.state', 'com_kinoarhiv.movie')) && (!$user->authorise('core.edit', 'com_content')))
		{
			$query->where('(m.publish_up = ' . $null_date . ' OR m.publish_up <= ' . $now_date . ')')
				->where('(m.publish_down = ' . $null_date . ' OR m.publish_down >= ' . $now_date . ')');
		}

		$filters = $this->getFiltersData();

		if ($filters !== false)
		{
			// Filter by title
			$title = trim($filters->get('movies.title'));

			if ($params->get('search_movies_title') == 1 && !empty($title))
			{
				if (StringHelper::strlen($title) < $params->get('search_movies_length_min')
					|| StringHelper::strlen($title) > $params->get('search_movies_length_max'))
				{
					echo KAComponentHelper::showMsg(
						JText::sprintf('COM_KA_SEARCH_ERROR_SEARCH_MESSAGE', $params->get('search_movies_length_min'), $params->get('search_movies_length_max')),
						array('icon' => 'alert'),
						true
					);
				}
				else
				{
					$exact_match = $app->input->get('exact_match', 0, 'int');
					$filter = StringHelper::strtolower(trim($title));

					if ($exact_match === 1)
					{
						$filter = $db->quote('%' . $db->escape($filter, true) . '%', false);
					}
					else
					{
						$filter = $db->quote($db->escape($filter, true) . '%', false);
					}

					$query->where('m.title LIKE ' . $filter);
				}
			}

			// Filter by year
			$year = $filters->get('movies.year');

			if ($params->get('search_movies_year') == 1 && !empty($year))
			{
				$query->where('m.year LIKE ' . $db->quote($db->escape($year, true) . '%', false));
			}
			else
			{
				// Filter by years range
				$year_range = $filters->get('movies.year_range');

				if ($params->get('search_movies_year_range') == 1)
				{
					if ((array_key_exists(0, $year_range) && !empty($year_range[0])) && (array_key_exists(1, $year_range) && !empty($year_range[1])))
					{
						$query->where("m.year BETWEEN '" . (int) $db->escape($year_range[0]) . "' AND '" . (int) $db->escape($year_range[1]) . "'");
					}
					else
					{
						if (array_key_exists(0, $year_range) && !empty($year_range[0]))
						{
							$query->where("m.year REGEXP '^" . (int) $db->escape($year_range[0]) . "'");
						}
						elseif (array_key_exists(1, $year_range) && !empty($year_range[1]))
						{
							$query->where("m.year REGEXP '" . (int) $db->escape($year_range[1]) . "$'");
						}
					}
				}
			}

			// Filter by country
			$country = $filters->get('movies.country');

			if ($params->get('search_movies_country') == 1 && !empty($country))
			{
				$subquery_cn = $db->getQuery(true)
					->select('movie_id')
					->from($db->quoteName('#__ka_rel_countries'))
					->where('country_id = ' . (int) $country);

				$db->setQuery($subquery_cn);
				$movie_ids = $db->loadColumn();

				if (count($movie_ids) == 0)
				{
					$movie_ids = array(0);
				}

				$query->where('m.id IN (' . implode(',', ArrayHelper::arrayUnique($movie_ids)) . ')');
			}

			// Filter by person name
			$cast = $filters->get('movies.cast');

			if ($params->get('search_movies_cast') == 1 && !empty($cast))
			{
				$subquery_cast = $db->getQuery(true)
					->select('movie_id')
					->from($db->quoteName('#__ka_rel_names'))
					->where('name_id = ' . (int) $cast);

				$db->setQuery($subquery_cast);
				$movie_ids = $db->loadColumn();

				if (count($movie_ids) == 0)
				{
					$movie_ids = array(0);
				}

				$query->where('m.id IN (' . implode(',', ArrayHelper::arrayUnique($movie_ids)) . ')');
			}

			// Filter by vendor
			$vendor = $filters->get('movies.vendor');

			if ($params->get('search_movies_vendor') == 1 && !empty($vendor))
			{
				$subquery_vnd = $db->getQuery(true)
					->select('movie_id')
					->from($db->quoteName('#__ka_releases'))
					->where('vendor_id = ' . (int) $vendor)
					->group('movie_id');

				$db->setQuery($subquery_vnd);
				$movie_ids = $db->loadColumn();

				if (count($movie_ids) == 0)
				{
					$movie_ids = array(0);
				}

				$query->where('m.id IN (' . implode(',', ArrayHelper::arrayUnique($movie_ids)) . ')');
			}

			// Filter by genres
			$genres = $filters->get('movies.genre');

			if ($params->get('search_movies_genre') == 1 && !empty($genres))
			{
				$genres = ArrayHelper::fromObject($genres);

				if (count(array_filter($genres)) > 0)
				{
					$subquery_genre = $db->getQuery(true)
						->select('movie_id')
						->from($db->quoteName('#__ka_rel_genres'))
						->where('genre_id IN (' . implode(',', $genres) . ')')
						->group('movie_id');

					$db->setQuery($subquery_genre);
					$movie_ids = $db->loadColumn();

					if (count($movie_ids) == 0)
					{
						$movie_ids = array(0);
					}

					$query->where('m.id IN (' . implode(',', ArrayHelper::arrayUnique($movie_ids)) . ')');
				}
			}

			// Filter by MPAA
			$mpaa = $filters->get('movies.mpaa');

			if ($params->get('search_movies_mpaa') == 1 && !empty($mpaa))
			{
				$query->where('m.mpaa = ' . $db->quote($db->escape(StringHelper::strtolower($mpaa), true), false));
			}

			// Filter by age
			$age_restrict = $filters->get('movies.age_restrict');

			if ($params->get('search_movies_age_restrict') == 1 && (!empty($age_restrict) && $age_restrict != '-1'))
			{
				$query->where('m.age_restrict = ' . (int) $age_restrict);
			}

			// Filter by Ukrainian Association rating
			$ua_rate = $filters->get('movies.ua_rate');

			if ($params->get('search_movies_ua_rate') == 1 && (!empty($ua_rate) && $ua_rate != '-1'))
			{
				$query->where('m.ua_rate = ' . (int) $ua_rate);
			}

			// Filter by site rating
			if ($params->get('search_movies_rate') == 1)
			{
				$rate_min = $filters->def('movies.rate_min', '');
				$rate_max = $filters->def('movies.rate_max', '');

				if ($rate_min != '' && $rate_max != '')
				{
					$query->where('(m.rate_loc_rounded BETWEEN ' . (int) $rate_min . ' AND ' . (int) $rate_max . ')');
				}
			}

			// Filter by imdb rating
			if ($params->get('search_movies_imdbrate') == 1)
			{
				$imdbrate_min = $filters->def('movies.imdbrate_min', '');
				$imdbrate_max = $filters->def('movies.imdbrate_max', '');

				if ($imdbrate_min != '' && $imdbrate_max != '')
				{
					$query->where('(m.rate_imdb_rounded BETWEEN ' . (int) $imdbrate_min . ' AND ' . (int) $imdbrate_max . ')');
				}
			}

			// Filter by kinopoisk rating
			if ($params->get('search_movies_kprate') == 1)
			{
				$kprate_min = $filters->def('movies.kprate_min', '');
				$kprate_max = $filters->def('movies.kprate_max', '');

				if ($kprate_min != '' && $kprate_max != '')
				{
					$query->where('(m.rate_kp_rounded BETWEEN ' . (int) $kprate_min . ' AND ' . (int) $kprate_max . ')');
				}
			}

			// Filter by rotten tomatoes rating
			if ($params->get('search_movies_rtrate') == 1)
			{
				$rtrate_min = $filters->def('movies.rtrate_min', '');
				$rtrate_max = $filters->def('movies.rtrate_max', '');

				if ($rtrate_min != '' && $rtrate_max != '')
				{
					$query->where('(m.rate_fc BETWEEN ' . (int) $rtrate_min . ' AND ' . (int) $rtrate_max . ')');
				}
			}

			// Filter by metacritic rating
			if ($params->get('search_movies_metacritic') == 1)
			{
				$metacritic_min = $filters->def('movies.metacritic.min', '');
				$metacritic_max = $filters->def('movies.metacritic.max', '');

				if ($metacritic_min != '' && $metacritic_max != '')
				{
					$query->where('(m.metacritics BETWEEN ' . (int) $metacritic_min . ' AND ' . (int) $metacritic_max . ')');
				}
			}

			// Filter by budget
			$budget_range = $filters->get('movies.budget');

			if ($params->get('search_movies_budget') == 1)
			{
				if ((array_key_exists(0, $budget_range) && !empty($budget_range[0])) && (array_key_exists(1, $budget_range) && !empty($budget_range[1])))
				{
					$query->where("m.budget BETWEEN '" . $db->escape(trim($budget_range[0])) . "' AND '" . $db->escape(trim($budget_range[1])) . "'");
				}
				else
				{
					if (array_key_exists(0, $budget_range) && !empty($budget_range[0]))
					{
						$query->where("m.budget = '" . $db->escape(trim($budget_range[0])) . "'");
					}
					elseif (array_key_exists(1, $budget_range) && !empty($budget_range[1]))
					{
						$query->where("m.budget = '" . $db->escape(trim($budget_range[1])) . "'");
					}
				}
			}

			// Filter by tags
			$tags = $filters->get('movies.tags');

			if ($params->get('search_movies_tags') == 1 && !empty($tags))
			{
				$tags = ArrayHelper::fromObject($tags);

				if (count(array_filter($tags)) > 0)
				{
					$subquery_tags = $db->getQuery(true)
						->select('content_item_id')
						->from($db->quoteName('#__contentitem_tag_map'))
						->where("type_alias = 'com_kinoarhiv.movie' AND tag_id IN (" . implode(',', $tags) . ")");

					$db->setQuery($subquery_tags);
					$movie_ids = $db->loadColumn();

					if (count($movie_ids) == 0)
					{
						$movie_ids = array(0);
					}

					$query->where('m.id IN (' . implode(',', ArrayHelper::arrayUnique($movie_ids)) . ')');
				}
			}
		}

		// Prevent duplicate records if accidentally have a more than one poster for frontpage.
		$query->group($db->quoteName('m.id'));
		$query->order($this->getState('list.ordering', 'm.ordering') . ' ' . $this->getState('list.direction', 'ASC'));

		return $query;
	}

	/**
	 * Get the values from search inputs
	 *
	 * @return   object
	 *
	 * @since  3.0
	 */
	public function getFiltersData()
	{
		jimport('models.search', JPATH_COMPONENT);

		$search_model = new KinoarhivModelSearch;

		return $search_model->getActiveFilters();
	}

	/**
	 * Method to add a movie into favorites
	 *
	 * @return array
	 *
	 * @throws Exception
	 *
	 * @since  3.0
	 */
	public function favorite()
	{
		$db = $this->getDbo();
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		$action = $app->input->get('action', '', 'cmd');
		$movie_id = $app->input->get('id', 0, 'int');
		$movie_ids = $app->input->get('ids', array(), 'array');
		$result = '';

		if (!empty($movie_ids))
		{
			JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		}

		$itemid = $app->input->get('Itemid', 0, 'int');
		$success = false;
		$url = '';
		$text = '';

		if (empty($movie_ids))
		{
			$query = $db->getQuery(true)
				->select('favorite')
				->from($db->quoteName('#__ka_user_marked_movies'))
				->where('uid = ' . (int) $user->get('id') . ' AND movie_id = ' . (int) $movie_id);

			$db->setQuery($query);
			$result = $db->loadResult();
		}

		if ($action == 'add')
		{
			if ($result == 1)
			{
				$message = JText::_('COM_KA_FAVORITE_ERROR');
			}
			else
			{
				if (is_null($result))
				{
					$query = $db->getQuery(true)
						->insert($db->quoteName('#__ka_user_marked_movies'))
						->columns($db->quoteName(array('uid', 'movie_id', 'favorite', 'watched')))
						->values("'" . $user->get('id') . "', '" . (int) $movie_id . "', '1', '0')");

					$db->setQuery($query);
				}
				elseif ($result == 0)
				{
					$query = $db->getQuery(true)
						->update($db->quoteName('#__ka_user_marked_movies'))
						->set("favorite = '1'")
						->where('uid = ' . $user->get('id') . ' AND movie_id = ' . (int) $movie_id);

					$db->setQuery($query);
				}

				if ($db->execute())
				{
					$success = true;
					$message = JText::_('COM_KA_FAVORITE_ADDED');
					$url = JRoute::_('index.php?option=com_kinoarhiv&view=movies&task=favorite&action=delete&Itemid=' . $itemid . '&id=' . $movie_id, false);
					$text = JText::_('COM_KA_REMOVEFROM_FAVORITE');
				}
				else
				{
					$message = JText::_('JERROR_ERROR');
				}
			}
		}
		elseif ($action == 'delete')
		{
			if ($result == 1)
			{
				$query = $db->getQuery(true)
					->update($db->quoteName('#__ka_user_marked_movies'))
					->set("favorite = '0'")
					->where('uid = ' . $user->get('id') . ' AND movie_id = ' . (int) $movie_id);

				$db->setQuery($query);

				if ($db->execute())
				{
					$success = true;
					$message = JText::_('COM_KA_FAVORITE_REMOVED');
					$url = JRoute::_('index.php?option=com_kinoarhiv&view=movies&task=favorite&action=add&Itemid=' . $itemid . '&id=' . $movie_id, false);
					$text = JText::_('COM_KA_ADDTO_FAVORITE');
				}
				else
				{
					$message = JText::_('JERROR_ERROR');
				}
			}
			else
			{
				if (!empty($movie_ids))
				{
					$query_result = true;
					$db->setDebug(true);
					$db->lockTable('#__ka_user_marked_movies');
					$db->transactionStart();

					foreach ($movie_ids as $id)
					{
						$query = $db->getQuery(true);

						$query->update($db->quoteName('#__ka_user_marked_movies'))
							->set("favorite = '0'")
							->where('uid = ' . $user->get('id') . ' AND movie_id = ' . (int) $id);

						$db->setQuery($query . ';');

						if ($db->execute() === false)
						{
							$query_result = false;
							break;
						}
					}

					if ($query_result === true)
					{
						$db->transactionCommit();

						$success = true;
						$message = JText::_('COM_KA_FAVORITE_REMOVED');
						$url = JRoute::_('index.php?option=com_kinoarhiv&view=movies&task=favorite&action=add&Itemid=' . $itemid . '&id=' . $movie_id, false);
						$text = JText::_('COM_KA_ADDTO_FAVORITE');
					}
					else
					{
						$db->transactionRollback();

						$message = JText::_('JERROR_ERROR');
					}

					$db->unlockTables();
					$db->setDebug(false);
				}
				else
				{
					$message = JText::_('JERROR_AN_ERROR_HAS_OCCURRED');
				}
			}
		}
		else
		{
			$message = JText::_('JERROR_AN_ERROR_HAS_OCCURRED');
		}

		return array('success' => $success, 'message' => $message, 'url' => $url, 'text' => $text);
	}

	/**
	 * Method to add a movie into watched
	 *
	 * @return array
	 *
	 * @throws Exception
	 *
	 * @since  3.0
	 */
	public function watched()
	{
		$db = $this->getDbo();
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		$action = $app->input->get('action', '', 'cmd');
		$movie_id = $app->input->get('id', 0, 'int');
		$movie_ids = $app->input->get('ids', array(), 'array');
		$result = '';

		if (!empty($movie_ids))
		{
			JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		}

		$itemid = $app->input->get('Itemid', 0, 'int');
		$success = false;
		$url = '';
		$text = '';

		if (empty($movie_ids))
		{
			$query = $db->getQuery(true)
				->select('watched')
				->from($db->quoteName('#__ka_user_marked_movies'))
				->where('uid = ' . (int) $user->get('id') . ' AND movie_id = ' . (int) $movie_id);

			$db->setQuery($query);
			$result = $db->loadResult();
		}

		if ($action == 'add')
		{
			if ($result == 1)
			{
				$message = JText::_('COM_KA_WATCHED_ERROR');
			}
			else
			{
				if (is_null($result))
				{
					$query = $db->getQuery(true)
						->insert($db->quoteName('#__ka_user_marked_movies'))
						->columns($db->quoteName(array('uid', 'movie_id', 'favorite', 'watched')))
						->values("'" . $user->get('id') . "', '" . (int) $movie_id . "', '0', '1')");

					$db->setQuery($query);
				}
				elseif ($result == 0)
				{
					$query = $db->getQuery(true)
						->update($db->quoteName('#__ka_user_marked_movies'))
						->set("watched = '1'")
						->where('uid = ' . $user->get('id') . ' AND movie_id = ' . (int) $movie_id);

					$db->setQuery($query);
				}

				if ($db->execute())
				{
					$success = true;
					$message = JText::_('COM_KA_WATCHED_ADDED');
					$url = JRoute::_('index.php?option=com_kinoarhiv&task=watched&action=delete&Itemid=' . $itemid . '&id=' . $movie_id, false);
					$text = JText::_('COM_KA_REMOVEFROM_WATCHED');
				}
				else
				{
					$message = JText::_('JERROR_ERROR');
				}
			}
		}
		elseif ($action == 'delete')
		{
			if ($result == 1)
			{
				$query = $db->getQuery(true)
					->update($db->quoteName('#__ka_user_marked_movies'))
					->set("watched = '0'")
					->where('uid = ' . $user->get('id') . ' AND movie_id = ' . (int) $movie_id);

				$db->setQuery($query);

				if ($db->execute())
				{
					$success = true;
					$message = JText::_('COM_KA_WATCHED_REMOVED');
					$url = JRoute::_('index.php?option=com_kinoarhiv&task=watched&action=add&Itemid=' . $itemid . '&id=' . $movie_id, false);
					$text = JText::_('COM_KA_ADDTO_WATCHED');
				}
				else
				{
					$message = JText::_('JERROR_ERROR');
				}
			}
			else
			{
				if (!empty($movie_ids))
				{
					$query_result = true;
					$db->setDebug(true);
					$db->lockTable('#__ka_user_marked_movies');
					$db->transactionStart();

					foreach ($movie_ids as $id)
					{
						$query = $db->getQuery(true);

						$query->update($db->quoteName('#__ka_user_marked_movies'))
							->set("watched = '0'")
							->where('uid = ' . $user->get('id') . ' AND movie_id = ' . (int) $id);

						$db->setQuery($query . ';');

						if ($db->execute() === false)
						{
							$query_result = false;
							break;
						}
					}

					if ($query_result === true)
					{
						$db->transactionCommit();

						$success = true;
						$message = JText::_('COM_KA_WATCHED_REMOVED');
						$url = JRoute::_('index.php?option=com_kinoarhiv&task=watched&action=add&Itemid=' . $itemid . '&id=' . $movie_id, false);
						$text = JText::_('COM_KA_ADDTO_WATCHED');
					}
					else
					{
						$db->transactionRollback();

						$message = JText::_('JERROR_ERROR');
					}

					$db->unlockTables();
					$db->setDebug(false);
				}
				else
				{
					$message = JText::_('JERROR_AN_ERROR_HAS_OCCURRED');
				}
			}
		}
		else
		{
			$message = JText::_('JERROR_AN_ERROR_HAS_OCCURRED');
		}

		return array('success' => $success, 'message' => $message, 'url' => $url, 'text' => $text);
	}

	/**
	 * Method to get a KAPagination object for the data set.
	 *
	 * @return  KAPagination  A KAPagination object for the data set.
	 *
	 * @since   3.0
	 */
	public function getPagination()
	{
		JLoader::register('KAPagination', JPath::clean(JPATH_COMPONENT . '/libraries/pagination.php'));

		$store = $this->getStoreId('getPagination');

		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		$limit = (int) $this->getState('list.limit') - (int) $this->getState('list.links');
		$page = new KAPagination($this->getTotal(), $this->getStart(), $limit);

		$this->cache[$store] = $page;

		return $this->cache[$store];
	}
}
