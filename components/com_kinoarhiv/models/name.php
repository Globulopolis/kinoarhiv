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

/**
 * Person data class
 *
 * @since  3.0
 */
class KinoarhivModelName extends JModelList
{
	/**
	 * Context string for the model type.  This is used to handle uniqueness
	 * when dealing with the getStoreId() method and caching data structures.
	 *
	 * @var    string
	 * @since  1.6
	 */
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
	 * @return  object|boolean  Object with data or false on error.
	 *
	 * @since   3.0
	 */
	public function getData()
	{
		$db     = $this->getDbo();
		$app    = JFactory::getApplication();
		$user   = JFactory::getUser();
		$lang   = JFactory::getLanguage();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$id     = $app->input->get('id', 0, 'int');

		$query = $db->getQuery(true)
			->select("n.id, n.name, n.latin_name, n.alias, n.fs_alias, DATE_FORMAT(n.date_of_birth, '%Y') AS date_of_birth, " .
				"n.date_of_birth AS date_of_birth_raw, DATE_FORMAT(n.date_of_death, '%Y') AS date_of_death, " .
				"n.date_of_death AS date_of_death_raw, n.birthplace, n.birthcountry, n.gender, n.height, n.desc, " .
				"n.attribs, n.metakey, n.metadesc, n.metadata, cn.name AS country, cn.code, g.filename, g.dimension"
			);
		$query->from($db->quoteName('#__ka_names', 'n'));

		$query->join('LEFT', $db->quoteName('#__ka_names_gallery', 'g') . ' ON g.name_id = n.id AND g.type = 3 AND g.frontpage = 1 AND g.state = 1');
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

			if (empty($result))
			{
				KAComponentHelper::eventLog(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'));

				return false;
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
		catch (RuntimeException $e)
		{
			KAComponentHelper::eventLog($e->getMessage());

			return false;
		}

		if (isset($result->attribs))
		{
			$result->attribs = json_decode($result->attribs);
		}

		// Select career
		$queryCareer = $db->getQuery(true)
			->select('id, title')
			->from($db->quoteName('#__ka_names_career'));

			$subqueryCareer = $db->getQuery(true)
				->select('career_id')
				->from($db->quoteName('#__ka_rel_names_career'))
				->where('name_id = ' . (int) $id);

		$queryCareer->where('id IN (' . $subqueryCareer . ') AND language IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ')')
			->order('title ASC');

		$db->setQuery($queryCareer);

		try
		{
			$result->career = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			$result->career = array();
			KAComponentHelper::eventLog($e->getMessage());
		}

		// Select genres
		$queryGenres = $db->getQuery(true)
			->select('id, name, alias')
			->from($db->quoteName('#__ka_genres'));

			$subqueryGenres = $db->getQuery(true)
				->select('genre_id')
				->from($db->quoteName('#__ka_rel_names_genres'))
				->where('name_id = ' . (int) $id);

		$queryGenres->where('id IN (' . $subqueryGenres . ') AND state = 1 AND access IN (' . $groups . ')')
			->where('language IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ')')
			->order('name ASC');

		$db->setQuery($queryGenres);

		try
		{
			$result->genres = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			$result->genres = array();
			KAComponentHelper::eventLog($e->getMessage());
		}

		// Select movies
		$queryMovies = $db->getQuery(true)
			->select('m.id, m.title, m.alias, m.year, r.role')
			->from($db->quoteName('#__ka_movies', 'm'))
			->join('LEFT', $db->quoteName('#__ka_rel_names', 'r') . ' ON r.name_id = ' . (int) $id . ' AND r.movie_id = m.id');

			$subqueryMovies = $db->getQuery(true)
				->select('movie_id')
				->from($db->quoteName('#__ka_rel_names'))
				->where('name_id = ' . (int) $id);

		$queryMovies->where('m.id IN (' . $subqueryMovies . ') AND m.state = 1 AND m.access IN (' . $groups . ')')
			->where('m.language IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ')')
			->order('m.year ASC');

		$db->setQuery($queryMovies);

		try
		{
			$result->movies = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			$result->movies = array();
			KAComponentHelper::eventLog($e->getMessage());
		}

		// Get proper Itemid for movies list
		if (count($result->movies) > 0)
		{
			$queryItemid = $db->getQuery(true)
				->select('id')
				->from($db->quoteName('#__menu'))
				->where("link = 'index.php?option=com_kinoarhiv&view=movies'")
				->where("type = 'component' AND parent_id = 1 AND language = " . $db->quote($lang->getTag()));

			$db->setQuery($queryItemid);

			try
			{
				$result->itemid = $db->loadResult();
			}
			catch (RuntimeException $e)
			{
				KAComponentHelper::eventLog($e->getMessage());
				$result->itemid = 0;
			}
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
	 * @return  string
	 *
	 * @since   3.0
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
	 * @return  object|boolean
	 *
	 * @since   3.0
	 */
	public function getNameData()
	{
		$db     = $this->getDbo();
		$app    = JFactory::getApplication();
		$user   = JFactory::getUser();
		$lang   = JFactory::getLanguage();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$id     = $app->input->get('id', 0, 'int');

		$query = $db->getQuery(true)
			->select($db->quoteName(array('id', 'name', 'latin_name', 'alias', 'fs_alias', 'gender', 'attribs', 'metakey', 'metadesc', 'metadata')))
			->from($db->quoteName('#__ka_names'))
			->where('id = ' . (int) $id . ' AND state = 1 AND access IN (' . $groups . ')')
			->where('language IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ')');

		$db->setQuery($query);

		try
		{
			$result = $db->loadObject();

			if (empty($result))
			{
				KAComponentHelper::eventLog(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'));

				return false;
			}
		}
		catch (RuntimeException $e)
		{
			KAComponentHelper::eventLog($e->getMessage());

			return false;
		}

		$result->attribs = json_decode($result->attribs);

		return $result;
	}

	/**
	 * Method to get awards for person
	 *
	 * @return object
	 *
	 * @since  3.0
	 */
	public function getAwards()
	{
		$db  = $this->getDbo();
		$app = JFactory::getApplication();
		$id  = $app->input->get('id', 0, 'int');

		$query = $db->getQuery(true)
			->select('a.desc, a.year, aw.id, aw.title AS aw_title, aw.desc AS aw_desc')
			->from($db->quoteName('#__ka_rel_awards', 'a'))
			->join('LEFT', $db->quoteName('#__ka_awards', 'aw') . ' ON aw.id = a.award_id')
			->where($db->quoteName('type') . ' = 1')
			->where($db->quoteName('item_id') . ' = ' . (int) $id)
			->order($db->quoteName('year') . ' DESC');

		$db->setQuery($query);

		try
		{
			$result = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			KAComponentHelper::eventLog($e->getMessage());
			$result = array();
		}

		return $result;
	}

	/**
	 * Build list of filters by dimensions for gallery
	 *
	 * @return  array
	 *
	 * @since  3.0
	 */
	public function getDimensionFilters()
	{
		$app    = JFactory::getApplication();
		$db     = $this->getDbo();
		$id     = $app->input->get('id', 0, 'int');
		$page   = $app->input->get('page', null, 'cmd');
		$result = array();

		if ($page == 'wallpapers')
		{
			$query = $db->getQuery(true)
				->select("dimension AS value, dimension AS title, SUBSTRING_INDEX(dimension, 'x', 1) AS width")
				->from($db->quoteName('#__ka_names_gallery'))
				->where($db->quoteName('name_id') . ' = ' . (int) $id)
				->where($db->quoteName('type') . ' = 1')
				->where($db->quoteName('state') . ' = 1')
				->group($db->quoteName('width'))
				->order($db->quoteName('width') . ' DESC');

			$db->setQuery($query);

			try
			{
				$result = $db->loadAssocList();
			}
			catch (RuntimeException $e)
			{
				KAComponentHelper::eventLog($e->getMessage());
				$result = array();
			}
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
		$app    = JFactory::getApplication();
		$db     = $this->getDbo();
		$id     = $app->input->get('id', 0, 'int');
		$page   = $app->input->get('page', '', 'cmd');
		$filter = $app->input->get('dim_filter', '0', 'string');
		$query  = null;

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
