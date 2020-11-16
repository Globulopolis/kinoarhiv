<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2018 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
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
	/**
	 * Context string for the model type.  This is used to handle uniqueness
	 * when dealing with the getStoreId() method and caching data structures.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $context = 'com_kinoarhiv.movies';

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
		$db     = $this->getDbo();
		$user   = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$app    = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');

		// Define null and now dates
		$nullDate = $db->quote($db->getNullDate());
		$nowDate  = $db->quote(JFactory::getDate()->toSql());

		$query = $db->getQuery(true);

		$query->select(
			$this->getState(
				'list.select',
				'm.id, m.parent_id, m.title, m.alias, m.fs_alias, ' . $db->quoteName('m.introtext', 'text') . ', m.plot, ' .
				'm.rate_loc, m.rate_sum_loc, m.imdb_votesum, m.imdb_votes, m.imdb_id, m.kp_votesum, ' .
				'm.kp_votes, m.kp_id, m.rate_fc, m.rottentm_id, m.metacritics, m.metacritics_id, ' .
				'm.myshows_votesum, m.myshows_votes, m.myshows_id, m.rate_custom, m.year, ' .
				'DATE_FORMAT(m.created, "%Y-%m-%d") AS ' . $db->quoteName('created') . ', m.created_by, ' .
				'CASE WHEN m.modified = ' . $nullDate . ' THEN m.created ELSE DATE_FORMAT(m.modified, "%Y-%m-%d") END AS modified, ' .
				'CASE WHEN m.publish_up = ' . $nullDate . ' THEN m.created ELSE m.publish_up END AS publish_up, ' .
				'm.publish_down, m.attribs, m.state'
			)
		);
		$query->from($db->quoteName('#__ka_movies', 'm'));

		// Join over gallery item
		$query->select($db->quoteName(array('g.filename', 'g.dimension')))
			->join('LEFT', $db->quoteName('#__ka_movies_gallery', 'g') . ' ON g.movie_id = m.id AND g.type = 2 AND g.frontpage = 1 AND g.state = 1');

		// Join over favorited
		if (!$user->get('guest'))
		{
			$query->select($db->quoteName('u.favorite'));
			$query->leftJoin($db->quoteName('#__ka_user_marked_movies', 'u') . ' ON u.uid = ' . $user->get('id') . ' AND u.movie_id = m.id');
		}

		$query->select($db->quoteName('user.name', 'username') . ', ' . $db->quoteName('user.email', 'author_email'));
		$query->leftJoin($db->quoteName('#__users', 'user') . ' ON user.id = m.created_by');

		$query->where('m.state = 1 AND m.access IN (' . $groups . ')')
			->where('language IN (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');

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
			$query->where('(m.publish_up = ' . $nullDate . ' OR m.publish_up <= ' . $nowDate . ')')
				->where('(m.publish_down = ' . $nullDate . ' OR m.publish_down >= ' . $nowDate . ')');
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
						JText::sprintf(
							'COM_KA_SEARCH_ERROR_SEARCH_MESSAGE',
							$params->get('search_movies_length_min'),
							$params->get('search_movies_length_max')
						),
						'alert-error',
						true
					);
				}
				else
				{
					$exactMatch = $app->input->get('exact_match', 0, 'int');
					$filter = StringHelper::strtolower(trim($title));

					if ($exactMatch === 1)
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
				$yearRange = $filters->get('movies.year_range');

				if ($params->get('search_movies_year_range') == 1)
				{
					if ((array_key_exists(0, $yearRange) && !empty($yearRange[0])) && (array_key_exists(1, $yearRange) && !empty($yearRange[1])))
					{
						$query->where("m.year BETWEEN '" . (int) $db->escape($yearRange[0]) . "' AND '" . (int) $db->escape($yearRange[1]) . "'");
					}
					else
					{
						if (array_key_exists(0, $yearRange) && !empty($yearRange[0]))
						{
							$query->where("m.year REGEXP '^" . (int) $db->escape($yearRange[0]) . "'");
						}
						elseif (array_key_exists(1, $yearRange) && !empty($yearRange[1]))
						{
							$query->where("m.year REGEXP '" . (int) $db->escape($yearRange[1]) . "$'");
						}
					}
				}
			}

			// Filter by country
			$country = $filters->get('movies.country');

			if ($params->get('search_movies_country') == 1 && !empty($country))
			{
				$subqueryCountries = $db->getQuery(true)
					->select('movie_id')
					->from($db->quoteName('#__ka_rel_countries'))
					->where('country_id = ' . (int) $country);

				$db->setQuery($subqueryCountries);

				try
				{
					$movieIDs = $db->loadColumn();

					if (count($movieIDs) == 0)
					{
						$movieIDs = array(0);
					}

					$query->where('m.id IN (' . implode(',', ArrayHelper::arrayUnique($movieIDs)) . ')');
				}
				catch (RuntimeException $e)
				{
					KAComponentHelper::eventLog($e->getMessage());
				}
			}

			// Filter by person name
			$cast = $filters->get('movies.cast');

			if ($params->get('search_movies_cast') == 1 && !empty($cast))
			{
				$subqueryCast = $db->getQuery(true)
					->select('movie_id')
					->from($db->quoteName('#__ka_rel_names'))
					->where('name_id = ' . (int) $cast);

				$db->setQuery($subqueryCast);

				try
				{
					$movieIDs = $db->loadColumn();

					if (count($movieIDs) == 0)
					{
						$movieIDs = array(0);
					}

					$query->where('m.id IN (' . implode(',', ArrayHelper::arrayUnique($movieIDs)) . ')');
				}
				catch (RuntimeException $e)
				{
					KAComponentHelper::eventLog($e->getMessage());
				}
			}

			// Filter by vendor
			$vendor = $filters->get('movies.vendor');

			if ($params->get('search_movies_vendor') == 1 && !empty($vendor))
			{
				$subqueryVendor = $db->getQuery(true)
					->select('movie_id')
					->from($db->quoteName('#__ka_releases'))
					->where('vendor_id = ' . (int) $vendor)
					->group('movie_id');

				$db->setQuery($subqueryVendor);

				try
				{
					$movieIDs = $db->loadColumn();

					if (count($movieIDs) == 0)
					{
						$movieIDs = array(0);
					}

					$query->where('m.id IN (' . implode(',', ArrayHelper::arrayUnique($movieIDs)) . ')');
				}
				catch (RuntimeException $e)
				{
					KAComponentHelper::eventLog($e->getMessage());
				}
			}

			// Filter by genres
			$genres = $filters->get('movies.genre');

			if ($params->get('search_movies_genre') == 1 && !empty($genres))
			{
				$genres = ArrayHelper::fromObject($genres);

				if (count(array_filter($genres)) > 0)
				{
					$subqueryGenre = $db->getQuery(true)
						->select('movie_id')
						->from($db->quoteName('#__ka_rel_genres'))
						->where('genre_id IN (' . implode(',', $genres) . ')')
						->group('movie_id');

					$db->setQuery($subqueryGenre);

					try
					{
						$movieIDs = $db->loadColumn();

						if (count($movieIDs) == 0)
						{
							$movieIDs = array(0);
						}

						$query->where('m.id IN (' . implode(',', ArrayHelper::arrayUnique($movieIDs)) . ')');
					}
					catch (RuntimeException $e)
					{
						KAComponentHelper::eventLog($e->getMessage());
					}
				}
			}

			// Filter by MPAA
			$mpaa = $filters->get('movies.mpaa');

			if ($params->get('search_movies_mpaa') == 1 && !empty($mpaa))
			{
				$query->where('m.mpaa = ' . $db->quote($db->escape(StringHelper::strtolower($mpaa), true), false));
			}

			// Filter by age
			$ageRestrict = $filters->get('movies.age_restrict');

			if ($params->get('search_movies_age_restrict') == 1 && (!empty($ageRestrict) && $ageRestrict != '-1'))
			{
				$query->where('m.age_restrict = ' . (int) $ageRestrict);
			}

			// Filter by Ukrainian Association rating
			$uaRating = $filters->get('movies.ua_rate');

			if ($params->get('search_movies_ua_rate') == 1 && (!empty($uaRating) && $uaRating != '-1'))
			{
				$query->where('m.ua_rate = ' . (int) $uaRating);
			}

			// Filter by site rating
			if ($params->get('search_movies_rate') == 1)
			{
				$minRating = $filters->def('movies.rate_min', '');
				$maxRating = $filters->def('movies.rate_max', '');

				if ($minRating != '' && $maxRating != '')
				{
					$query->where('(m.rate_loc_rounded BETWEEN ' . (int) $minRating . ' AND ' . (int) $maxRating . ')');
				}
			}

			// Filter by imdb rating
			if ($params->get('search_movies_imdbrate') == 1)
			{
				$minImdbRating = $filters->def('movies.imdbrate_min', '');
				$maxImdbRating = $filters->def('movies.imdbrate_max', '');

				if ($minImdbRating != '' && $maxImdbRating != '')
				{
					$query->where('(m.rate_imdb_rounded BETWEEN ' . (int) $minImdbRating . ' AND ' . (int) $maxImdbRating . ')');
				}
			}

			// Filter by kinopoisk rating
			if ($params->get('search_movies_kprate') == 1)
			{
				$minKinopoiskRating = $filters->def('movies.kprate_min', '');
				$maxKinopoiskRating = $filters->def('movies.kprate_max', '');

				if ($minKinopoiskRating != '' && $maxKinopoiskRating != '')
				{
					$query->where('(m.rate_kp_rounded BETWEEN ' . (int) $minKinopoiskRating . ' AND ' . (int) $maxKinopoiskRating . ')');
				}
			}

			// Filter by rotten tomatoes rating
			if ($params->get('search_movies_rtrate') == 1)
			{
				$minRTRating = $filters->def('movies.rtrate_min', '');
				$maxRTRating = $filters->def('movies.rtrate_max', '');

				if ($minRTRating != '' && $maxRTRating != '')
				{
					$query->where('(m.rate_fc BETWEEN ' . (int) $minRTRating . ' AND ' . (int) $maxRTRating . ')');
				}
			}

			// Filter by metacritic rating
			if ($params->get('search_movies_metacritic') == 1)
			{
				$minMetacriticRating = $filters->def('movies.metacritic.min', '');
				$maxMetacriticRating = $filters->def('movies.metacritic.max', '');

				if ($minMetacriticRating != '' && $maxMetacriticRating != '')
				{
					$query->where('(m.metacritics BETWEEN ' . (int) $minMetacriticRating . ' AND ' . (int) $maxMetacriticRating . ')');
				}
			}

			// Filter by budget
			$budgetRange = $filters->get('movies.budget');

			if ($params->get('search_movies_budget') == 1)
			{
				if ((array_key_exists(0, $budgetRange) && !empty($budgetRange[0])) && (array_key_exists(1, $budgetRange) && !empty($budgetRange[1])))
				{
					$query->where("m.budget BETWEEN '" . $db->escape(trim($budgetRange[0])) . "' AND '" . $db->escape(trim($budgetRange[1])) . "'");
				}
				else
				{
					if (array_key_exists(0, $budgetRange) && !empty($budgetRange[0]))
					{
						$query->where("m.budget = '" . $db->escape(trim($budgetRange[0])) . "'");
					}
					elseif (array_key_exists(1, $budgetRange) && !empty($budgetRange[1]))
					{
						$query->where("m.budget = '" . $db->escape(trim($budgetRange[1])) . "'");
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
					$subqueryTags = $db->getQuery(true)
						->select('content_item_id')
						->from($db->quoteName('#__contentitem_tag_map'))
						->where("type_alias = 'com_kinoarhiv.movie' AND tag_id IN (" . implode(',', $tags) . ")");

					$db->setQuery($subqueryTags);

					try
					{
						$movieIDs = $db->loadColumn();

						if (count($movieIDs) == 0)
						{
							$movieIDs = array(0);
						}

						$query->where('m.id IN (' . implode(',', ArrayHelper::arrayUnique($movieIDs)) . ')');
					}
					catch (RuntimeException $e)
					{
						KAComponentHelper::eventLog($e->getMessage());
					}
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

		$searchModel = new KinoarhivModelSearch;

		return $searchModel->getActiveFilters();
	}

	/**
	 * Method to add a movie into favorites
	 *
	 * @param   integer  $id  Movie ID.
	 *
	 * @return  boolean
	 *
	 * @since   3.1
	 */
	public function favoriteAdd($id)
	{
		$db     = $this->getDbo();
		$app    = JFactory::getApplication();
		$userID = JFactory::getUser()->get('id');

		// Check if any record with movie ID exists in database.
		$query = $db->getQuery(true)
			->select($db->quoteName(array('uid', 'favorite')))
			->from($db->quoteName('#__ka_user_marked_movies'))
			->where($db->quoteName('uid') . ' = ' . (int) $userID)
			->where($db->quoteName('movie_id') . ' = ' . (int) $id);

		$db->setQuery($query);

		try
		{
			$result = $db->loadAssoc();
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
			KAComponentHelper::eventLog($e->getMessage());

			return false;
		}

		if (!$result)
		{
			$query = $db->getQuery(true)
				->insert($db->quoteName('#__ka_user_marked_movies'))
				->columns($db->quoteName(array('uid', 'movie_id', 'favorite', 'favorite_added', 'watched', 'watched_added')))
				->values("'" . (int) $userID . "', '" . (int) $id . "', '1', NOW(), '', ''");

			$db->setQuery($query);
		}
		else
		{
			if ($result['favorite'] == 1)
			{
				$app->enqueueMessage(JText::_('COM_KA_FAVORITE_ERROR'), 'notice');

				return false;
			}

			$query = $db->getQuery(true)
				->update($db->quoteName('#__ka_user_marked_movies'))
				->set($db->quoteName('favorite') . " = '1', " . $db->quoteName('favorite_added') . " = NOW()")
				->where($db->quoteName('uid') . ' = ' . (int) $userID)
				->where($db->quoteName('movie_id') . ' = ' . (int) $id);

			$db->setQuery($query);
		}

		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
			KAComponentHelper::eventLog($e->getMessage());

			return false;
		}

		return true;
	}

	/**
	 * Removes movie(s) from favorites.
	 *
	 * @param   mixed  $id  Movie ID or array of IDs.
	 *
	 * @return  boolean
	 *
	 * @since   3.1
	 */
	public function favoriteRemove($id)
	{
		$db     = $this->getDbo();
		$app    = JFactory::getApplication();
		$userID = JFactory::getUser()->get('id');

		if (!is_array($id))
		{
			$query = $db->getQuery(true)
				->update($db->quoteName('#__ka_user_marked_movies'))
				->set($db->quoteName('favorite') . " = '0'")
				->where($db->quoteName('uid') . ' = ' . (int) $userID)
				->where($db->quoteName('movie_id') . ' = ' . (int) $id);

			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (RuntimeException $e)
			{
				$app->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
				KAComponentHelper::eventLog($e->getMessage());

				return false;
			}
		}
		else
		{
			$queryResult = true;
			$db->lockTable('#__ka_user_marked_movies');
			$db->transactionStart();

			foreach ($id as $_id)
			{
				$query = $db->getQuery(true);

				$query->update($db->quoteName('#__ka_user_marked_movies'))
					->set($db->quoteName('favorite') . " = '0'")
					->where($db->quoteName('uid') . ' = ' . (int) $userID)
					->where($db->quoteName('movie_id') . ' = ' . (int) $_id);

				$db->setQuery($query . ';');

				if ($db->execute() === false)
				{
					$queryResult = false;
					break;
				}
			}

			if ($queryResult === true)
			{
				$db->transactionCommit();
				$db->unlockTables();
			}
			else
			{
				$db->transactionRollback();
				$db->unlockTables();
				$app->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

				return false;
			}
		}

		return true;
	}

	/**
	 * Method to add a movie into watched list.
	 *
	 * @param   integer  $id  Movie ID.
	 *
	 * @return  boolean
	 *
	 * @since   3.1
	 */
	public function watchedAdd($id)
	{
		$db     = $this->getDbo();
		$app    = JFactory::getApplication();
		$userID = JFactory::getUser()->get('id');

		// Check if any record with movie ID exists in database.
		$query = $db->getQuery(true)
			->select($db->quoteName(array('uid', 'watched')))
			->from($db->quoteName('#__ka_user_marked_movies'))
			->where($db->quoteName('uid') . ' = ' . (int) $userID)
			->where($db->quoteName('movie_id') . ' = ' . (int) $id);

		$db->setQuery($query);

		try
		{
			$result = $db->loadAssoc();
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
			KAComponentHelper::eventLog($e->getMessage());

			return false;
		}

		if (!$result)
		{
			$query = $db->getQuery(true)
				->insert($db->quoteName('#__ka_user_marked_movies'))
				->columns($db->quoteName(array('uid', 'movie_id', 'favorite', 'favorite_added', 'watched', 'watched_added')))
				->values("'" . (int) $userID . "', '" . (int) $id . "', '', '', '1', NOW()");

			$db->setQuery($query);
		}
		else
		{
			if ($result['watched'] == 1)
			{
				$app->enqueueMessage(JText::_('COM_KA_WATCHED_ERROR'), 'notice');

				return false;
			}

			$query = $db->getQuery(true)
				->update($db->quoteName('#__ka_user_marked_movies'))
				->set($db->quoteName('watched') . " = '1', " . $db->quoteName('watched_added') . " = NOW()")
				->where($db->quoteName('uid') . ' = ' . (int) $userID)
				->where($db->quoteName('movie_id') . ' = ' . (int) $id);

			$db->setQuery($query);
		}

		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
			KAComponentHelper::eventLog($e->getMessage());

			return false;
		}

		return true;
	}

	/**
	 * Removes movie(s) from watched list.
	 *
	 * @param   mixed  $id  Movie ID or array of IDs.
	 *
	 * @return  boolean
	 *
	 * @since   3.1
	 */
	public function watchedRemove($id)
	{
		$db     = $this->getDbo();
		$app    = JFactory::getApplication();
		$userID = JFactory::getUser()->get('id');

		if (!is_array($id))
		{
			$query = $db->getQuery(true)
				->update($db->quoteName('#__ka_user_marked_movies'))
				->set($db->quoteName('watched') . " = '0'")
				->where($db->quoteName('uid') . ' = ' . (int) $userID)
				->where($db->quoteName('movie_id') . ' = ' . (int) $id);

			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (RuntimeException $e)
			{
				$app->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
				KAComponentHelper::eventLog($e->getMessage());

				return false;
			}
		}
		else
		{
			$queryResult = true;
			$db->lockTable('#__ka_user_marked_movies');
			$db->transactionStart();

			foreach ($id as $_id)
			{
				$query = $db->getQuery(true);

				$query->update($db->quoteName('#__ka_user_marked_movies'))
					->set($db->quoteName('watched') . " = '0'")
					->where($db->quoteName('uid') . ' = ' . (int) $userID)
					->where($db->quoteName('movie_id') . ' = ' . (int) $_id);

				$db->setQuery($query . ';');

				if ($db->execute() === false)
				{
					$queryResult = false;
					break;
				}
			}

			if ($queryResult === true)
			{
				$db->transactionCommit();
				$db->unlockTables();
			}
			else
			{
				$db->transactionRollback();
				$db->unlockTables();
				$app->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

				return false;
			}
		}

		return true;
	}

	/**
	 * Process user vote.
	 *
	 * @param   integer  $id     Movie ID.
	 * @param   integer  $value  Item rating.
	 *
	 * @return  array
	 *
	 * @since   3.0
	 */
	public function vote($id, $value)
	{
		$db     = $this->getDbo();
		$user   = JFactory::getUser();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$result = array('success' => false, 'message' => JText::_('COM_KA_REQUEST_ERROR'));

		$queryAttribs = $db->getQuery(true)
			->select('attribs')
			->from($db->quoteName('#__ka_movies'))
			->where('id = ' . (int) $id);

		$db->setQuery($queryAttribs);
		$attribs = json_decode($db->loadResult());

		if (($attribs->allow_votes == '' && $params->get('allow_votes') == 1) || $attribs->allow_votes == 1)
		{
			// Update rating and insert or update user vote in #__ka_user_votes_movies
			// Check if value in range from 1 to 'vote_summ_num'
			if ($value >= 1 || $value <= $params->get('vote_summ_num'))
			{
				// At first we check if user allready voted and when just update the rating and vote
				$query = $db->getQuery(true)
					->select('v.vote, r.rate_loc, r.rate_sum_loc')
					->from($db->quoteName('#__ka_user_votes_movies', 'v'))
					->join('LEFT', $db->quoteName('#__ka_movies', 'r') . ' ON r.id = v.movie_id')
					->where('movie_id = ' . (int) $id . ' AND uid = ' . $user->get('id'));

				$db->setQuery($query);
				$voteResult = $db->loadObject();

				if (!empty($voteResult->vote))
				{
					// User allready voted
					$rateSumLocal = ($voteResult->rate_sum_loc - $voteResult->vote) + $value;
					$rateLocalRounded = round($rateSumLocal / $voteResult->rate_loc, 0);

					try
					{
						$query = $db->getQuery(true)
							->update($db->quoteName('#__ka_movies'))
							->set("rate_sum_loc = '" . (int) $rateSumLocal . "', rate_loc_rounded = '" . (int) $rateLocalRounded . "'")
							->where('id = ' . (int) $id);

						$db->setQuery($query);
						$queryMovies = $db->execute();

						$query = $db->getQuery(true)
							->update($db->quoteName('#__ka_user_votes_movies'))
							->set("vote = '" . (int) $value . "', _datetime = NOW()")
							->where('movie_id = ' . (int) $id . ' AND uid = ' . $user->get('id'));

						$db->setQuery($query);
						$queryVotes = $db->execute();

						if ($queryMovies && $queryVotes)
						{
							$result = array('success' => true, 'message' => JText::_('COM_KA_RATE_RATED'));
						}
					}
					catch (RuntimeException $e)
					{
						KAComponentHelper::eventLog($e->getMessage());
					}
				}
				else
				{
					$query = $db->getQuery(true)
						->select('rate_loc, rate_sum_loc')
						->from($db->quoteName('#__ka_movies'))
						->where('id = ' . (int) $id);

					$db->setQuery($query);
					$voteResult = $db->loadObject();

					$rateLocal = (int) $voteResult->rate_loc + 1;
					$rateSumLocal = (int) $voteResult->rate_sum_loc + (int) $value;
					$rateLocalRounded = round($rateSumLocal / $rateLocal, 0);

					try
					{
						$query = $db->getQuery(true)
							->update($db->quoteName('#__ka_movies'))
							->set("rate_loc = '" . (int) $rateLocal . "', rate_sum_loc = '" . (int) $rateSumLocal . "'")
							->set("rate_loc_rounded = '" . (int) $rateLocalRounded . "'")
							->where('id = ' . (int) $id);

						$db->setQuery($query);
						$queryMovies = $db->execute();

						$query = $db->getQuery(true)
							->insert($db->quoteName('#__ka_user_votes_movies'))
							->columns($db->quoteName(array('uid', 'movie_id', 'vote', '_datetime')))
							->values("'" . $user->get('id') . "', '" . $id . "', '" . (int) $value . "', NOW()");

						$db->setQuery($query);
						$queryVotes = $db->execute();

						if ($queryMovies && $queryVotes)
						{
							$result = array('success' => true, 'message' => JText::_('COM_KA_RATE_RATED'));
						}
					}
					catch (RuntimeException $e)
					{
						KAComponentHelper::eventLog($e->getMessage());
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Removes user votes.
	 *
	 * @param   array  $ids  Array of IDs
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	public function votesRemove($ids)
	{
		$db         = $this->getDbo();
		$user       = JFactory::getUser();
		$params     = JComponentHelper::getParams('com_kinoarhiv');
		$allowedIDs = array();

		// Get attributes to check if user can change vote.
		$queryAttribs = $db->getQuery(true)
			->select(array('id', 'attribs'))
			->from($db->quoteName('#__ka_movies'))
			->where('id IN (' . implode(',', $ids) . ')');

		$db->setQuery($queryAttribs);
		$attribsObjects = $db->loadObjectList();

		foreach ($attribsObjects as $attribs)
		{
			$movieAttribs = json_decode($attribs->attribs);

			if (($movieAttribs->allow_votes == '' && $params->get('allow_votes') == 1) || $movieAttribs->allow_votes == 1)
			{
				$allowedIDs[] = $attribs->id;
			}
		}

		if (empty($allowedIDs))
		{
			return array('success' => false, 'message' => JText::_('COM_KA_REQUEST_ERROR'));
		}

		$queryVote = $db->getQuery(true)
			->select('m.id, m.rate_loc, m.rate_sum_loc, v.vote')
			->from($db->quoteName('#__ka_user_votes_movies', 'v'))
			->join('LEFT', $db->quoteName('#__ka_movies', 'm') . ' ON m.id = v.movie_id')
			->where('movie_id IN (' . implode(',', $allowedIDs) . ') AND uid = ' . $user->get('id'));

		$db->setQuery($queryVote);
		$votes = $db->loadObjectList();

		// Check if user has votes at least for one movie.
		if (empty($votes))
		{
			return array('success' => false, 'message' => JText::_('COM_KA_REQUEST_ERROR'));
		}

		$queryResult = true;
		$db->lockTable('#__ka_movies')
			->lockTable('#__ka_user_votes_movies');
		$db->transactionStart();

		foreach ($votes as $voteObject)
		{
			if (!empty($voteObject->vote))
			{
				$rateLocal = $voteObject->rate_loc - 1;
				$rateSumLocal = $voteObject->rate_sum_loc - $voteObject->vote;
				$rateLocalRounded = round($voteObject->rate_sum_loc / $voteObject->rate_loc, 0);

				$query = $db->getQuery(true)
					->update($db->quoteName('#__ka_movies'))
					->set("rate_loc = '" . (int) $rateLocal . "', rate_sum_loc = '" . (int) $rateSumLocal . "'")
					->set("rate_loc_rounded = '" . (int) $rateLocalRounded . "'")
					->where('id = ' . (int) $voteObject->id);
				$db->setQuery($query . ';');

				if ($db->execute() === false)
				{
					$queryResult = false;
					break;
				}
			}
		}

		if (!$queryResult)
		{
			$db->transactionRollback();
		}
		else
		{
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__ka_user_votes_movies'))
				->where('movie_id IN (' . implode(',', $ids) . ') AND uid = ' . $user->get('id'));
			$db->setQuery($query);

			if ($db->execute())
			{
				$db->transactionCommit();
			}
			else
			{
				$db->transactionRollback();
				$queryResult = false;
			}
		}

		$db->unlockTables();

		if (!$queryResult)
		{
			return array('success' => false, 'message' => JText::_('JERROR_AN_ERROR_HAS_OCCURRED'));
		}

		return array('success' => true, 'message' => (count($ids) > 1) ? JText::_('COM_KA_RATES_REMOVED') : JText::_('COM_KA_RATE_REMOVED'));
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

		// Get a storage key.
		$store = $this->getStoreId('getPagination');

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		$limit = (int) $this->getState('list.limit') - (int) $this->getState('list.links');

		// Create the pagination object and add the object to the internal cache.
		$page = new KAPagination($this->getTotal(), $this->getStart(), $limit);

		$this->cache[$store] = $page;

		return $this->cache[$store];
	}
}
