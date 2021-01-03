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

use Joomla\Registry\Registry;
use Joomla\String\StringHelper;

/**
 * Persons list class
 *
 * @since  3.0
 */
class KinoarhivModelNames extends JModelList
{
	/**
	 * Context string for the model type.  This is used to handle uniqueness
	 * when dealing with the getStoreId() method and caching data structures.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $context = 'com_kinoarhiv.names';

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
			// Setup a list of columns for ORDER BY from 'sort_namelist_field' params from component settings
			$config['filter_fields'] = array('id', 'n.id', 'name', 'n.name', 'latin_name', 'n.latin_name', 'ordering', 'n.ordering');
		}

		parent::__construct($config);
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
		parent::populateState($ordering, $direction);

		$app    = JFactory::getApplication();
		$params = new Registry;

		if ($menu = $app->getMenu()->getActive())
		{
			$params->loadString($menu->params);
		}

		$this->setState('params', $params);

		$limit = $params->get('list_limit');

		// Override default limit settings and respect user selection if 'show_pagination_limit' is set to Yes.
		if ($params->get('show_pagination_limit'))
		{
			$limit = $app->getUserStateFromRequest('list.limit', 'limit', $params->get('list_limit'), 'uint');
		}

		$this->setState('list.limit', $limit);

		$limitstart = $app->input->getUInt('limitstart', 0);
		$this->setState('list.start', $limitstart);

		$this->setState('list.ordering', $params->get('orderby'));
		$this->setState('list.direction', $params->get('ordering'));
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
		$id .= ':' . $this->getState('filter.name');
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
		$lang   = JFactory::getLanguage();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$app    = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$query  = $db->getQuery(true);

		$query->select(
			$this->getState(
				'list.select',
				"n.id, n.name, n.latin_name, n.alias, n.fs_alias, " . $db->quoteName('n.introtext', 'text') . ", " .
				"DATE_FORMAT(n.date_of_birth, '%Y') AS date_of_birth, DATE_FORMAT(n.date_of_death, '%Y') AS date_of_death, " .
				"n.birthplace, n.gender, n.attribs, cn.name AS country, cn.code"
			)
		)
			->from($db->quoteName('#__ka_names', 'n'))
			->leftJoin($db->quoteName('#__ka_countries', 'cn') . ' ON cn.id = n.birthcountry AND cn.language IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ') AND cn.state = 1');

		// Join over gallery item
		$query->select($db->quoteName(array('gal.filename', 'gal.dimension')))
			->leftJoin($db->quoteName('#__ka_names_gallery', 'gal') . ' ON gal.name_id = n.id AND gal.type = 3 AND gal.frontpage = 1 AND gal.state = 1');

		if (!$user->get('guest'))
		{
			$query->select($db->quoteName('u.favorite'));
			$query->leftJoin($db->quoteName('#__ka_user_marked_names', 'u') . ' ON u.uid = ' . $user->get('id') . ' AND u.name_id = n.id');
		}

		$query->where('n.state = 1')
			->where('n.language IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ')')
			->where('n.access IN (' . $groups . ')');

		$filters = $this->getFiltersData();

		if ($filters !== false)
		{
			// Filter by name
			$name = trim($filters->get('names.name'));

			if ($params->get('search_names_name') == 1 && !empty($name))
			{
				if (StringHelper::strlen($name) < $params->get('search_movies_length_min')
					|| StringHelper::strlen($name) > $params->get('search_movies_length_max'))
				{
					$this->setError(
						JText::sprintf(
							'COM_KA_SEARCH_ERROR_SEARCH_MESSAGE',
							$params->get('search_movies_length_min'),
							$params->get('search_movies_length_max')
						)
					);
				}
				else
				{
					$exactMatch = $app->input->get('exact_match', 0, 'int');
					$filter = $params->get('use_alphabet') ? StringHelper::strtoupper($name) : StringHelper::strtolower($name);

					if ($exactMatch === 1)
					{
						$filter = $db->quote('%' . $db->escape($filter, true) . '%', false);
					}
					else
					{
						$filter = $db->quote($db->escape($filter, true) . '%', false);
					}

					$query->where('(n.name LIKE ' . $filter . ' OR n.latin_name LIKE ' . $filter . ')');
				}
			}

			// Filter by birthday
			$birthday = $filters->get('names.birthday');

			if ($params->get('search_names_birthday') == 1 && !empty($birthday))
			{
				$query->where('n.date_of_birth LIKE ' . $db->quote('%' . $db->escape($birthday, true) . '%', false));
			}

			// Filter by gender
			$gender = $filters->get('names.gender');

			if ($params->get('search_names_gender') == 1 && ($gender === 0 || $gender === 1))
			{
				$query->where('n.gender = ' . (int) $gender);
			}

			// Filter by genre
			$genre = $filters->get('names.genre');

			if ($params->get('search_names_genre') == 1 && !empty($genre))
			{
				$subqueryGenre = $db->getQuery(true)
					->select($db->quoteName('name_id'))
					->from($db->quoteName('#__ka_rel_names_genres'))
					->where('genre_id IN (' . implode(',', $genre) . ')');

				$query->where('n.id IN (' . $subqueryGenre . ')');
			}

			// Filter by movie title
			$mtitle = $filters->get('names.title');

			if ($params->get('search_names_mtitle') == 1 && !empty($mtitle))
			{
				$subqueryTitle = $db->getQuery(true)
					->select($db->quoteName('name_id'))
					->from($db->quoteName('#__ka_rel_names'))
					->where('movie_id = ' . (int) $mtitle);

				$query->where('n.id IN (' . $subqueryTitle . ')');
			}

			// Filter by birthplace
			$birthplace = trim($filters->get('names.birthplace'));

			if ($params->get('search_names_birthplace') == 1 && !empty($birthplace))
			{
				$query->where('n.birthplace LIKE ' . $db->quote('%' . $db->escape($birthplace, true) . '%', false));
			}

			// Filter by country
			$country = $filters->get('names.birthcountry');

			if ($params->get('search_names_birthcountry') == 1 && !empty($country))
			{
				$query->where('n.birthcountry = ' . (int) $country);
			}

			// Filter by amplua
			$amplua = $filters->get('names.amplua');

			if ($params->get('search_names_amplua') == 1 && !empty($amplua))
			{
				$subqueryAmplua = $db->getQuery(true)
					->select($db->quoteName('name_id'))
					->from($db->quoteName('#__ka_rel_names_career'))
					->where('career_id = ' . (int) $amplua);

				$query->where('n.id IN (' . $subqueryAmplua . ')');
			}
		}

		$query->group($db->quoteName('n.id'));

		$orderCol = $this->state->get('list.ordering', $db->quoteName('n.ordering'));
		$orderDirn = $this->state->get('list.direction', 'DESC');
		$query->order($db->escape('n.' . $orderCol . ' ' . $orderDirn));

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
	 * Method to add a person into favorites
	 *
	 * @param   integer  $id  Person ID.
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

		// Check if any record with person ID exists in database.
		$query = $db->getQuery(true)
			->select($db->quoteName(array('uid', 'favorite')))
			->from($db->quoteName('#__ka_user_marked_names'))
			->where($db->quoteName('uid') . ' = ' . (int) $userID)
			->where($db->quoteName('name_id') . ' = ' . (int) $id);

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
				->insert($db->quoteName('#__ka_user_marked_names'))
				->columns($db->quoteName(array('uid', 'name_id', 'favorite', 'favorite_added')))
				->values("'" . (int) $userID . "', '" . (int) $id . "', '1', NOW()");

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
				->update($db->quoteName('#__ka_user_marked_names'))
				->set($db->quoteName('favorite') . " = '1', " . $db->quoteName('favorite_added') . " = NOW()")
				->where($db->quoteName('uid') . ' = ' . (int) $userID)
				->where($db->quoteName('name_id') . ' = ' . (int) $id);

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
	 * Removes person(s) from favorites.
	 *
	 * @param   mixed  $id  Person ID or array of IDs.
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
				->update($db->quoteName('#__ka_user_marked_names'))
				->set($db->quoteName('favorite') . " = '0'")
				->where($db->quoteName('uid') . ' = ' . (int) $userID)
				->where($db->quoteName('name_id') . ' = ' . (int) $id);

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
			$db->lockTable('#__ka_user_marked_names');
			$db->transactionStart();

			foreach ($id as $_id)
			{
				$query = $db->getQuery(true);

				$query->update($db->quoteName('#__ka_user_marked_names'))
					->set($db->quoteName('favorite') . " = '0'")
					->where($db->quoteName('uid') . ' = ' . (int) $userID)
					->where($db->quoteName('name_id') . ' = ' . (int) $_id);

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
