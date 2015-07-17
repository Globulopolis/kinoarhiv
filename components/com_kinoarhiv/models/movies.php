<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Joomla\String\String;

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
		$id .= ':' . $this->getState('filter.title');
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
		$db = $this->getDBO();
		$user = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$searches = $this->getFiltersData();
		$where_id = array();

		// Define null and now dates
		$null_date = $db->quote($db->getNullDate());
		$now_date = $db->quote(JFactory::getDate()->toSql());

		$query = $db->getQuery(true);

		$query->select(
			$this->getState(
				'list.select',
				'm.id, m.parent_id, m.title, m.alias, ' . $db->quoteName('m.introtext', 'text') . ', m.plot, ' .
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
						$query->where('m.title LIKE "' . $db->escape(String::strtoupper($matches[0])) . '%"');
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

		// Filter by title
		$title = $searches->get('filters.movies.title');

		if ($params->get('search_movies_title') == 1 && !empty($title))
		{
			if (String::strlen($title) < $params->get('search_movies_length_min') || String::strlen($title) > $params->get('search_movies_length_max'))
			{
				echo KAComponentHelper::showMsg(
					JText::sprintf('COM_KA_SEARCH_ERROR_SEARCH_MESSAGE', $params->get('search_movies_length_min'), $params->get('search_movies_length_max')),
					array('icon' => 'alert'),
					true
				);
			}
			else
			{
				$query->where("m.title LIKE '" . $db->escape($title) . "%'");
			}
		}

		// Filter by year
		$year = $searches->get('filters.movies.year');

		if ($params->get('search_movies_year') == 1 && !empty($year))
		{
			$query->where("m.year LIKE '" . $db->escape($year) . "%'");
		}
		else
		{
			// Filter by years range
			$from_year = $searches->get('filters.movies.from_year');
			$to_year = $searches->get('filters.movies.to_year');

			if ($params->get('search_movies_year_range') == 1)
			{
				if (!empty($from_year) && !empty($to_year))
				{
					$query->where("m.year BETWEEN '" . $db->escape($from_year) . "' AND '" . $db->escape($to_year) . "'");
				}
				else
				{
					if (!empty($from_year))
					{
						$query->where("m.year REGEXP '^" . $db->escape($from_year) . "'");
					}
					elseif (!empty($to_year))
					{
						$query->where("m.year REGEXP '" . $db->escape($to_year) . "$'");
					}
				}
			}
		}

		// Filter by country
		$country = $searches->get('filters.movies.country');

		if ($params->get('search_movies_country') == 1 && !empty($country))
		{
			$subquery_cn = $db->getQuery(true)
				->select('movie_id')
				->from($db->quoteName('#__ka_rel_countries'))
				->where('country_id = ' . (int) $country);

			$db->setQuery($subquery_cn);
			$movie_ids = $db->loadColumn();

			$where_id = (!empty($movie_ids)) ? array_merge($where_id, $movie_ids) : array(0);
		}

		// Filter by person name
		$cast = $searches->get('filters.movies.cast');

		if ($params->get('search_movies_cast') == 1 && !empty($cast))
		{
			$subquery_cast = $db->getQuery(true)
				->select('movie_id')
				->from($db->quoteName('#__ka_rel_names'))
				->where('name_id = ' . (int) $cast);

			$db->setQuery($subquery_cast);
			$movie_ids = $db->loadColumn();

			$where_id = (!empty($movie_ids)) ? array_merge($where_id, $movie_ids) : array(0);
		}

		// Filter by vendor
		$vendor = $searches->get('filters.movies.vendor');

		if ($params->get('search_movies_vendor') == 1 && !empty($vendor))
		{
			$subquery_vnd = $db->getQuery(true)
				->select('movie_id')
				->from($db->quoteName('#__ka_releases'))
				->where('vendor_id = ' . (int) $vendor)
				->group('movie_id');

			$db->setQuery($subquery_vnd);
			$movie_ids = $db->loadColumn();

			$where_id = (!empty($movie_ids)) ? array_merge($where_id, $movie_ids) : array(0);
		}

		// Filter by genres
		$genres = $searches->get('filters.movies.genre');

		if ($params->get('search_movies_genre') == 1 && !empty($genres))
		{
			$subquery_genre = $db->getQuery(true)
				->select('movie_id')
				->from($db->quoteName('#__ka_rel_genres'))
				->where('genre_id IN (' . implode(',', $genres) . ')')
				->group('movie_id');

			$db->setQuery($subquery_genre);
			$movie_ids = $db->loadColumn();

			$where_id = (!empty($movie_ids)) ? array_merge($where_id, $movie_ids) : array(0);
		}

		// Filter by MPAA
		$mpaa = $searches->get('filters.movies.mpaa');

		if ($params->get('search_movies_mpaa') == 1 && !empty($mpaa))
		{
			$query->where("m.mpaa = '" . $db->escape($mpaa) . "'");
		}

		// Filter by age
		$age_restrict = $searches->get('filters.movies.age_restrict');

		if ($params->get('search_movies_age_restrict') == 1 && (!empty($age_restrict) && $age_restrict != '-1'))
		{
			$query->where("m.age_restrict = '" . $db->escape($age_restrict) . "'");
		}

		// Filter by UA rating
		$ua_rate = $searches->get('filters.movies.ua_rate');

		if ($params->get('search_movies_ua_rate') == 1 && (!empty($ua_rate) && $ua_rate != '-1'))
		{
			$query->where("m.ua_rate = '" . $db->escape($ua_rate) . "'");
		}

		// Filter by site rating
		$rate = $searches->def('filters.movies.rate.enable', 0);

		if ($params->get('search_movies_rate') == 1 && $rate === 1)
		{
			$rate_min = $searches->def('filters.movies.rate.min', 0);
			$rate_max = $searches->def('filters.movies.rate.max', 10);
			$query->where("m.rate_loc_rounded BETWEEN " . (int) $rate_min . " AND " . (int) $rate_max);
		}

		// Filter by imdb rating
		$imdbrate = $searches->def('filters.movies.imdbrate.enable', 0);

		if ($params->get('search_movies_imdbrate') == 1 && $imdbrate === 1)
		{
			$imdbrate_min = $searches->def('filters.movies.imdbrate.min', 6);
			$imdbrate_max = $searches->def('filters.movies.imdbrate.max', 10);
			$query->where("m.rate_imdb_rounded BETWEEN " . (int) $imdbrate_min . " AND " . (int) $imdbrate_max);
		}

		// Filter by kinopoisk rating
		$kprate = $searches->def('filters.movies.kprate.enable', 0);

		if ($params->get('search_movies_kprate') == 1 && $kprate === 1)
		{
			$kprate_min = $searches->def('filters.movies.kprate.min', 6);
			$kprate_max = $searches->def('filters.movies.kprate.max', 10);
			$query->where("m.rate_kp_rounded BETWEEN " . (int) $kprate_min . " AND " . (int) $kprate_max);
		}

		// Filter by rotten tomatoes rating
		$rtrate = $searches->def('filters.movies.rtrate.enable', 0);

		if ($params->get('search_movies_rtrate') == 1 && $rtrate === 1)
		{
			$rtrate_min = $searches->def('filters.movies.rtrate.min', 0);
			$rtrate_max = $searches->def('filters.movies.rtrate.max', 100);
			$query->where("m.rate_fc BETWEEN " . (int) $rtrate_min . " AND " . (int) $rtrate_max);
		}

		// Filter by metacritic rating
		$metacritic = $searches->def('filters.movies.metacritic.enable', 0);

		if ($params->get('search_movies_metacritic') == 1 && $metacritic === 1)
		{
			$metacritic_min = $searches->def('filters.movies.metacritic.min', 0);
			$metacritic_max = $searches->def('filters.movies.metacritic.max', 100);
			$query->where("m.metacritics BETWEEN " . (int) $metacritic_min . " AND " . (int) $metacritic_max);
		}

		// Filter by budget
		$from_budget = $searches->get('filters.movies.from_budget');
		$to_budget = $searches->get('filters.movies.to_budget');

		if ($params->get('search_movies_budget') == 1)
		{
			if (!empty($from_budget) && !empty($to_budget))
			{
				$query->where("m.budget BETWEEN '" . $db->escape($from_budget) . "' AND '" . $db->escape($to_budget) . "'");
			}
			else
			{
				if (!empty($from_budget))
				{
					$query->where("m.budget = '" . $db->escape($from_budget) . "'");
				}
				elseif (!empty($to_budget))
				{
					$query->where("m.budget = '" . $db->escape($to_budget) . "'");
				}
			}
		}

		// Filter by tags
		$tags = $searches->get('filters.movies.tags');

		if ($params->get('search_movies_tags') == 1 && !empty($tags))
		{
			$subquery_tags = $db->getQuery(true)
				->select('content_item_id')
				->from($db->quoteName('#__contentitem_tag_map'))
				->where("type_alias = 'com_kinoarhiv.movie' AND tag_id IN (" . $tags . ")");

			$db->setQuery($subquery_tags);
			$movie_ids = $db->loadColumn();

			$where_id = (!empty($movie_ids)) ? array_merge($where_id, $movie_ids) : array(0);
		}

		if (!empty($country) || !empty($cast) || !empty($vendor) || !empty($genres) || !empty($tags) && !empty($where_id))
		{
			$query->where('m.id IN (' . implode(',', ArrayHelper::arrayUnique($where_id)) . ')');
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
	 */
	public function getFiltersData()
	{
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$filter = JFilterInput::getInstance();
		$input = JFactory::getApplication()->input;
		$items = new Registry;

		if ($params->get('search_movies_enable') != 1)
		{
			return $items;
		}

		if (array_key_exists('movies', $input->get('filters', array(), 'array')))
		{
			$filters_arr = $input->get('filters', array(), 'array');
			$filters = $filters_arr['movies'];

			if (count($filters) < 1)
			{
				return $items;
			}

			// Using input->getArray cause an error when subarrays with no data
			$vars = array(
				'filters' => array(
					'movies' => array(
						'title'        => isset($filters['title']) ? $filter->clean($filters['title'], 'string') : '',
						'year'         => isset($filters['year']) ? $filter->clean($filters['year'], 'string') : '',
						'from_year'    => isset($filters['from_year']) ? $filter->clean($filters['from_year'], 'int') : '',
						'to_year'      => isset($filters['to_year']) ? $filter->clean($filters['to_year'], 'int') : '',
						'country'      => isset($filters['country']) ? $filter->clean($filters['country'], 'int') : 0,
						'cast'         => isset($filters['cast']) ? $filter->clean($filters['cast'], 'int') : 0,
						'vendor'       => isset($filters['vendor']) ? $filter->clean($filters['vendor'], 'int') : '',
						'genre'        => isset($filters['genre']) ? $filter->clean($filters['genre'], 'array') : '',
						'mpaa'         => isset($filters['mpaa']) ? $filter->clean($filters['mpaa'], 'string') : '',
						'age_restrict' => isset($filters['age_restrict']) ? $filter->clean($filters['age_restrict'], 'string') : '-1',
						'ua_rate'      => isset($filters['ua_rate']) ? $filter->clean($filters['ua_rate'], 'int') : '-1',
						'rate'         => array(
							'enable' => isset($filters['rate']['enable']) ? $filter->clean($filters['rate']['enable'], 'int') : 0,
							'min'    => isset($filters['rate']['min']) ? $filter->clean($filters['rate']['min'], 'int') : 0,
							'max'    => isset($filters['rate']['max']) ? $filter->clean($filters['rate']['max'], 'int') : 10
						),
						'imdbrate'     => array(
							'enable' => isset($filters['imdbrate']['enable']) ? $filter->clean($filters['imdbrate']['enable'], 'int') : 0,
							'min'    => isset($filters['imdbrate']['min']) ? $filter->clean($filters['imdbrate']['min'], 'int') : 6,
							'max'    => isset($filters['imdbrate']['max']) ? $filter->clean($filters['imdbrate']['max'], 'int') : 10
						),
						'kprate'       => array(
							'enable' => isset($filters['kprate']['enable']) ? $filter->clean($filters['kprate']['enable'], 'int') : 0,
							'min'    => isset($filters['kprate']['min']) ? $filter->clean($filters['kprate']['min'], 'int') : 6,
							'max'    => isset($filters['kprate']['max']) ? $filter->clean($filters['kprate']['max'], 'int') : 10
						),
						'rtrate'       => array(
							'enable' => isset($filters['rtrate']['enable']) ? $filter->clean($filters['rtrate']['enable'], 'int') : 0,
							'min'    => isset($filters['rtrate']['min']) ? $filter->clean($filters['rtrate']['min'], 'int') : 0,
							'max'    => isset($filters['rtrate']['max']) ? $filter->clean($filters['rtrate']['max'], 'int') : 100
						),
						'metacritic'   => array(
							'enable' => isset($filters['metacritic']['enable']) ? $filter->clean($filters['metacritic']['enable'], 'int') : 0,
							'min'    => isset($filters['metacritic']['min']) ? $filter->clean($filters['metacritic']['min'], 'int') : 0,
							'max'    => isset($filters['metacritic']['max']) ? $filter->clean($filters['metacritic']['max'], 'int') : 100
						),
						'from_budget'  => isset($filters['from_budget']) ? $filter->clean($filters['from_budget'], 'string') : '',
						'to_budget'    => isset($filters['to_budget']) ? $filter->clean($filters['to_budget'], 'string') : '',
						'tags'         => isset($filters['tags']) ? $filter->clean($filters['tags'], 'string') : ''
					)
				)
			);

			$items->loadArray($vars);
		}

		return $items;
	}

	/**
	 * Method to add a movie into favorite list
	 *
	 * @return array
	 *
	 * @throws Exception
	 */
	public function favorite()
	{
		$db = $this->getDBO();
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
					$url = JRoute::_('index.php?option=com_kinoarhiv&task=favorite&action=delete&Itemid=' . $itemid . '&id=' . $movie_id, false);
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
					$url = JRoute::_('index.php?option=com_kinoarhiv&task=favorite&action=add&Itemid=' . $itemid . '&id=' . $movie_id, false);
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
						$url = JRoute::_('index.php?option=com_kinoarhiv&task=favorite&action=add&Itemid=' . $itemid . '&id=' . $movie_id, false);
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
	 */
	public function watched()
	{
		$db = $this->getDBO();
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
		JLoader::register('KAPagination', JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'pagination.php');

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
