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

/**
 * Person data class
 *
 * @since  3.0
 */
class KinoarhivModelName extends JModelList
{
	protected $context = null;

	protected $list_limit;

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
		parent::__construct($config);

		if (empty($this->context))
		{
			$input = JFactory::getApplication()->input;
			$page = $input->get('page', 'global');
			$this->context = strtolower($this->option . '.' . $this->getName() . '.' . $page);
		}
	}

	/**
	 * Get an item data
	 *
	 * @return object
	 */
	public function getData()
	{
		$db = $this->getDBO();
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$lang = JFactory::getLanguage();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$id = $app->input->get('id', 0, 'int');

		$query = $db->getQuery(true)
			->select("n.id, n.name, n.latin_name, n.alias, n.fs_alias, DATE_FORMAT(n.date_of_birth, '%Y') AS date_of_birth, " .
					"n.date_of_birth AS date_of_birth_raw, DATE_FORMAT(n.date_of_death, '%Y') AS date_of_death, " .
					"n.date_of_death AS date_of_death_raw, n.birthplace, n.birthcountry, n.gender, n.height, n.desc, " .
					"n.attribs, n.metakey, n.metadesc, n.metadata, cn.name AS country, cn.code, g.filename, g.dimension")
			->from($db->quoteName('#__ka_names', 'n'));

		$query->join('LEFT', $db->quoteName('#__ka_names_gallery', 'g') . ' ON g.name_id = n.id AND g.type = 3 AND g.photo_frontpage = 1 AND g.state = 1');
		$query->join('LEFT', $db->quoteName('#__ka_countries', 'cn') . ' ON `cn`.`id` = n.birthcountry AND cn.state = 1');

		if (!$user->get('guest'))
		{
			$query->select('u.favorite');
			$query->join('LEFT', $db->quoteName('#__ka_user_marked_names', 'u') . ' ON u.uid = ' . $user->get('id') . ' AND u.name_id = n.id');
		}

		$query->where('n.id = ' . (int) $id . ' AND n.state = 1 AND access IN (' . $groups . ')')
			->where('n.language IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ')');

		$db->setQuery($query);

		try
		{
			$result = $db->loadObject();

			if (count($result) == 0)
			{
				$this->setError('Error');
				$result = (object) array();
			}
			else
			{
				if ($result->date_of_birth_raw != '0000-00-00')
				{
					$result->zodiac = $this->getZodiacSign(substr($result->date_of_birth_raw, 5, 2), substr($result->date_of_birth_raw, 8, 2));
				}
				else
				{
					$result->zodiac = '';
				}
			}
		}
		catch (Exception $e)
		{
			$result = (object) array();
			$this->setError('Error');
			KAComponentHelper::eventLog($e->getMessage());
		}

		if (isset($result->attribs))
		{
			$result->attribs = json_decode($result->attribs);
		}

		// Select career
		$query_career = $db->getQuery(true)
			->select('id, title')
			->from($db->quoteName('#__ka_names_career'));

			$subquery_career = $db->getQuery(true)
				->select('career_id')
				->from($db->quoteName('#__ka_rel_names_career'))
				->where('name_id = ' . (int) $id);

		$query_career->where('id IN (' . $subquery_career . ') AND language IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ')')
			->order('title ASC');

		$db->setQuery($query_career);

		try
		{
			$result->career = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			$result->career = array();
			KAComponentHelper::eventLog($e->getMessage());
		}

		// Select genres
		$query_genres = $db->getQuery(true)
			->select('id, name, alias')
			->from($db->quoteName('#__ka_genres'));

			$subquery_genres = $db->getQuery(true)
				->select('genre_id')
				->from($db->quoteName('#__ka_rel_names_genres'))
				->where('name_id = ' . (int) $id);

		$query_genres->where('id IN (' . $subquery_genres . ') AND state = 1 AND access IN (' . $groups . ')')
			->where('language IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ')')
			->order('name ASC');

		$db->setQuery($query_genres);

		try
		{
			$result->genres = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			$result->genres = array();
			KAComponentHelper::eventLog($e->getMessage());
		}

		// Select movies
		$query_movies = $db->getQuery(true)
			->select('m.id, m.title, m.alias, m.year, r.role')
			->from($db->quoteName('#__ka_movies', 'm'))
			->join('LEFT', $db->quoteName('#__ka_rel_names', 'r') . ' ON r.name_id = ' . (int) $id . ' AND r.movie_id = m.id');

			$subquery_movies = $db->getQuery(true)
				->select('movie_id')
				->from($db->quoteName('#__ka_rel_names'))
				->where('name_id = ' . (int) $id);

		$query_movies->where('m.id IN (' . $subquery_movies . ') AND m.state = 1 AND m.access IN (' . $groups . ')')
			->where('m.language IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ')')
			->order('m.year ASC');

		$db->setQuery($query_movies);

		try
		{
			$result->movies = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			$result->movies = array();
			KAComponentHelper::eventLog($e->getMessage());
		}

		// Get proper Itemid for movies list
		if (count($result->movies) > 0)
		{
			$query_itemid = $db->getQuery(true)
				->select('id')
				->from($db->quoteName('#__menu'))
				->where("link = 'index.php?option=com_kinoarhiv&view=movies'")
				->where("type = 'component' AND parent_id = 1 AND language = " . $db->quote($lang->getTag()));

			$db->setQuery($query_itemid);
			$result->itemid = $db->loadResult();
		}
		else
		{
			$result->itemid = $app->input->get('Itemid', 0, 'int');
		}

		return $result;
	}

	/**
	 * Get the zodiac sign
	 *
	 * @param   integer  $month  Month number
	 * @param   integer  $day    Day number
	 *
	 * @return string
	 */
	public function getZodiacSign($month, $day)
	{
		if ($day > 31 || $day < 0)
		{
			return '';
		}

		if ($month > 12 || $month < 0)
		{
			return '';
		}

		if ($month == 1)
		{
			$zodiac = ($day <= 20) ? 'capricorn' : 'aquarius';
		}
		elseif ($month == 2)
		{
			if ($day > 29)
			{
				return '';
			}

			$zodiac = ($day <= 18) ? 'aquarius' : 'pisces';
		}
		elseif ($month == 3)
		{
			$zodiac = ($day <= 20) ? 'pisces' : 'aries';
		}
		elseif ($month == 4)
		{
			if ($day > 30)
			{
				return '';
			}

			$zodiac = ($day <= 20) ? 'aries' : 'taurus';
		}
		elseif ($month == 5)
		{
			$zodiac = ($day <= 21) ? 'taurus' : 'gemini';
		}
		elseif ($month == 6)
		{
			if ($day > 30)
			{
				return '';
			}

			$zodiac = ($day <= 22) ? 'gemini' : 'cancer';
		}
		elseif ($month == 7)
		{
			$zodiac = ($day <= 22) ? 'cancer' : 'leo';
		}
		elseif ($month == 8)
		{
			$zodiac = ($day <= 21) ? 'leo' : 'virgo';
		}
		elseif ($month == 9)
		{
			if ($day > 30)
			{
				return '';
			}

			$zodiac = ($day <= 23) ? 'virgo' : 'libra';
		}
		elseif ($month == 10)
		{
			$zodiac = ($day <= 23) ? 'libra' : 'scorpio';
		}
		elseif ($month == 11)
		{
			if ($day > 30)
			{
				return '';
			}

			$zodiac = ($day <= 21) ? 'scorpio' : 'sagittarius';
		}
		elseif ($month == 12)
		{
			$zodiac = ($day <= 22) ? 'sagittarius' : 'capricorn';
		}

		return $zodiac;
	}

	/**
	 * Method to get person data
	 *
	 * @return object
	 */
	public function getNameData()
	{
		$db = $this->getDBO();
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$lang = JFactory::getLanguage();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$id = $app->input->get('id', 0, 'int');
		$result = (object) array();

		$query = $db->getQuery(true)
			->select('id, name, latin_name, alias, fs_alias, gender, attribs, metakey, metadesc, metadata')
			->from($db->quoteName('#__ka_names'))
			->where('id = ' . (int) $id . ' AND state = 1 AND access IN (' . $groups . ')')
			->where('language IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ')');

		$db->setQuery($query);

		try
		{
			$result = $db->loadObject();

			if (count($result) == 0)
			{
				$this->setError('Error');
			}
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			KAComponentHelper::eventLog($e->getMessage());
		}

		$result->attribs = json_decode($result->attribs);

		return $result;
	}

	/**
	 * Method to get awards for person
	 *
	 * @return object
	 */
	public function getAwards()
	{
		$db = $this->getDBO();
		$app = JFactory::getApplication();
		$id = $app->input->get('id', 0, 'int');

		$result = $this->getNameData();

		$query = $db->getQuery(true)
			->select('a.desc, a.year, aw.id, aw.title AS aw_title, aw.desc AS aw_desc')
			->from($db->quoteName('#__ka_rel_awards', 'a'))
			->join('LEFT', $db->quoteName('#__ka_awards', 'aw') . ' ON aw.id = a.award_id')
			->where('type = 1 AND item_id = ' . (int) $id)
			->order('year DESC');

		$db->setQuery($query);
		$result->awards = $db->loadObjectList();

		return $result;
	}

	/**
	 * Build list of filters by dimensions for gallery
	 *
	 * @return  array
	 */
	public function getDimensionFilters()
	{
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$id = $app->input->get('id', 0, 'int');
		$page = $app->input->get('page', null, 'cmd');
		$result = array();

		if ($page == 'wallpapers')
		{
			$query = $db->getQuery(true)
				->select("dimension AS value, dimension AS title, SUBSTRING_INDEX(dimension, 'x', 1) AS width")
				->from($db->quoteName('#__ka_names_gallery'))
				->where('name_id = ' . (int) $id . ' AND type = 1 AND state = 1')
				->group('width')
				->order('width DESC');

			$db->setQuery($query);
			$result = $db->loadAssocList();
		}

		return $result;
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
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$id = $app->input->get('id', 0, 'int');
		$page = $app->input->get('page', '', 'cmd');
		$filter = $app->input->get('dim_filter', '0', 'string');
		$query = null;

		if ($page == 'wallpapers')
		{
			$query = $db->getQuery(true)
				->select('id, filename, dimension')
				->from($db->quoteName('#__ka_names_gallery'))
				->where('name_id = ' . (int) $id . ' AND state = 1 AND type = 1');

			if ($filter !== '0')
			{
				$query->where('dimension LIKE ' . $db->quote($db->escape($filter, true) . '%', false));
			}
		}
		elseif ($page == 'posters')
		{
			$query = $db->getQuery(true)
				->select('id, filename, dimension')
				->from($db->quoteName('#__ka_names_gallery'))
				->where('name_id = ' . (int) $id . ' AND state = 1 AND type = 2');
		}
		elseif ($page == 'photos')
		{
			$query = $db->getQuery(true)
				->select('g.id, g.filename, g.dimension, n.gender')
				->from($db->quoteName('#__ka_names_gallery', 'g'))
				->join('LEFT', $db->quoteName('#__ka_names', 'n') . ' ON n.id = g.name_id')
				->where('g.name_id = ' . (int) $id . ' AND g.state = 1 AND g.type = 3');
		}

		return $query;
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

			$value = $app->getUserStateFromRequest($this->context . '.list.limit', 'limit', $app->get('list_limit'), 'uint');
			$limit = $value;
			$this->setState('list.limit', $limit);

			$value = $app->getUserStateFromRequest($this->context . '.limitstart', 'limitstart', 0);
			$limitstart = ($limit != 0 ? (floor($value / $limit) * $limit) : 0);
			$this->setState('list.start', $limitstart);

			$value = $app->getUserStateFromRequest($this->context . '.ordercol', 'filter_order', $ordering);

			if (!in_array($value, $this->filter_fields))
			{
				$value = $ordering;
				$app->setUserState($this->context . '.ordercol', $value);
			}

			$this->setState('list.ordering', $value);

			$value = $app->getUserStateFromRequest($this->context . '.orderdirn', 'filter_order_Dir', $direction);

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
	 * Gets the value of a user state variable and sets it in the session
	 *
	 * This is the same as the method in JApplication except that this also can optionally
	 * force you back to the first page when a filter has changed
	 *
	 * @param   string   $key        The key of the user state variable.
	 * @param   string   $request    The name of the variable passed in a request.
	 * @param   string   $default    The default value for the variable if not found. Optional.
	 * @param   string   $type       Filter for the variable, for valid values see {@link JFilterInput::clean()}. Optional.
	 * @param   boolean  $resetPage  If true, the limitstart in request is set to zero
	 *
	 * @return  mixed
	 *
	 * @since   12.2
	 */
	public function getUserStateFromRequest($key, $request, $default = null, $type = 'none', $resetPage = true)
	{
		$app = JFactory::getApplication();
		$old_state = $app->getUserState($key);
		$cur_state = (!is_null($old_state)) ? $old_state : $default;
		$new_state = $app->input->get($request, null, $type);

		if (($cur_state != $new_state) && ($resetPage))
		{
			$app->input->set('limitstart', 0);
		}

		if ($new_state !== null)
		{
			$app->setUserState($key, $new_state);
		}
		else
		{
			$new_state = $cur_state;
		}

		return $new_state;
	}
}
