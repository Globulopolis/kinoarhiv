<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
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

	public function getData()
	{
		$db = $this->getDBO();
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$lang = JFactory::getLanguage();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$id = $app->input->get('id', 0, 'int');

		$query = $db->getQuery(true);

		$query->select("`n`.`id`, `n`.`name`, `n`.`latin_name`, `n`.`alias`, DATE_FORMAT(`n`.`date_of_birth`, '%Y') AS `date_of_birth`, `n`.`date_of_birth` AS `date_of_birth_raw`, DATE_FORMAT(`n`.`date_of_death`, '%Y') AS `date_of_death`, `n`.`date_of_death` AS `date_of_death_raw`, `n`.`birthplace`, `n`.`birthcountry`, `n`.`gender`, `n`.`height`, `n`.`desc`, `n`.`attribs`, `n`.`metakey`, `n`.`metadesc`, `n`.`metadata`, `cn`.`name` AS `country`, `cn`.`code`, `g`.`filename`")
			->from($db->quoteName('#__ka_names') . ' AS `n`');

		$query->leftJoin($db->quoteName('#__ka_names_gallery') . ' AS `g` ON `g`.`name_id` = `n`.`id` AND `g`.`type` = 3 AND `g`.`photo_frontpage` = 1 AND `g`.`state` = 1');
		$query->leftJoin($db->quoteName('#__ka_countries') . ' AS `cn` ON `cn`.`id` = `n`.`birthcountry` AND `cn`.`state` = 1');

		if (!$user->get('guest'))
		{
			$query->select('`u`.`favorite`');
			$query->leftJoin($db->quoteName('#__ka_user_marked_names') . ' AS `u` ON `u`.`uid` = ' . $user->get('id') . ' AND `u`.`name_id` = `n`.`id`');
		}

		$query->where('`n`.`id` = ' . (int) $id . ' AND `n`.`state` = 1 AND `access` IN (' . $groups . ') AND `n`.`language` IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ')');

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
				$result->zodiac = ($result->date_of_birth_raw != '0000-00-00') ? $this->getZodiacSign(substr($result->date_of_birth_raw, 5, 2), substr($result->date_of_birth_raw, 8, 2)) : '';
			}
		}
		catch (Exception $e)
		{
			$result = (object) array();
			$this->setError($e->getMessage());
		}

		if (isset($result->attribs))
		{
			$result->attribs = json_decode($result->attribs);
		}

		// Select career
		$db->setQuery("SELECT `id`, `title`"
			. "\n FROM " . $db->quoteName('#__ka_names_career')
			. "\n WHERE `id` IN (SELECT `career_id` FROM " . $db->quoteName('#__ka_rel_names_career') . " WHERE `name_id` = " . (int) $id . ") AND `language` IN (" . $db->quote($lang->getTag()) . "," . $db->quote('*') . ")"
			. "\n ORDER BY `title` ASC");
		$result->career = $db->loadObjectList();

		// Select genres
		$db->setQuery("SELECT `id`, `name`, `alias`"
			. "\n FROM " . $db->quoteName('#__ka_genres')
			. "\n WHERE `id` IN (SELECT `genre_id` FROM " . $db->quoteName('#__ka_rel_names_genres') . " WHERE `name_id` = " . (int) $id . ") AND `state` = 1 AND `access` IN (" . $groups . ") AND `language` IN (" . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ")"
			. "\n ORDER BY `name` ASC");
		$result->genres = $db->loadObjectList();

		// Select movies
		$db->setQuery("SELECT `m`.`id`, `m`.`title`, `m`.`alias`, `m`.`year`, `r`.`role`"
			. "\n FROM " . $db->quoteName('#__ka_movies') . " AS `m`"
			. "\n LEFT JOIN " . $db->quoteName('#__ka_rel_names') . " AS `r` ON `r`.`name_id` = " . (int) $id . " AND `r`.`movie_id` = `m`.`id`"
			. "\n WHERE `id` IN (SELECT `movie_id` FROM " . $db->quoteName('#__ka_rel_names') . " WHERE `name_id` = " . (int) $id . ") AND `m`.`state` = 1 AND `m`.`access` IN (" . $groups . ") AND `language` IN (" . $db->quote($lang->getTag()) . "," . $db->quote('*') . ")"
			. "\n ORDER BY `year` ASC");
		$result->movies = $db->loadObjectList();

		// Get proper Itemid for movies list
		if (count($result->movies) > 0)
		{
			$db->setQuery("SELECT `id` FROM " . $db->quoteName('#__menu') . " WHERE `link` = 'index.php?option=com_kinoarhiv&view=movies' AND `type` = 'component' AND `parent_id` = 1 AND `language` = " . $db->quote($lang->getTag()) . "");
			$result->itemid = $db->loadResult();
		}
		else
		{
			$result->itemid = $app->input->get('Itemid', 0, 'int');
		}

		return $result;
	}

	public function getZodiacSign($month, $day)
	{
		if ($day > 31 || $day < 0)
		{
			return;
		}

		if ($month > 12 || $month < 0)
		{
			return;
		}

		if ($month == 1)
		{
			$zodiac = ($day <= 20) ? 'capricorn' : 'aquarius';
		}
		elseif ($month == 2)
		{
			if ($day > 29)
			{
				return;
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
				return;
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
				return;
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
				return;
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
				return;
			}

			$zodiac = ($day <= 21) ? 'scorpio' : 'sagittarius';
		}
		elseif ($month == 12)
		{
			$zodiac = ($day <= 22) ? 'sagittarius' : 'capricorn';
		}

		return $zodiac;
	}

	public function getNameData()
	{
		$db = $this->getDBO();
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$lang = JFactory::getLanguage();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$id = $app->input->get('id', 0, 'int');
		$result = (object) array();

		$db->setQuery("SELECT `id`, `name`, `latin_name`, `alias`, `attribs`, `metakey`, `metadesc`, `metadata`"
			. "\n FROM " . $db->quoteName('#__ka_names')
			. "\n WHERE `id` = " . (int) $id . " AND `state` = 1 AND `access` IN (" . $groups . ") AND `language` IN (" . $db->quote($lang->getTag()) . "," . $db->quote('*') . ")");

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

	public function getAwards()
	{
		$db = $this->getDBO();
		$app = JFactory::getApplication();
		$id = $app->input->get('id', 0, 'int');

		$result = $this->getNameData();

		$db->setQuery("SELECT `a`.`desc`, `a`.`year`, `aw`.`id`, `aw`.`title` AS `aw_title`, `aw`.`desc` AS `aw_desc`"
			. "\n FROM " . $db->quoteName('#__ka_rel_awards') . " AS `a`"
			. "\n LEFT JOIN " . $db->quoteName('#__ka_awards') . " AS `aw` ON `aw`.`id` = `a`.`award_id`"
			. "\n WHERE `type` = 1 AND `item_id` = " . (int) $id
			. "\n ORDER BY `year` ASC");
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
		$page = $app->input->get('page', null, 'cmd');
		$result = array();

		if ($page == 'wallpapers')
		{
			$db->setQuery("SELECT `dimension` AS `value`, `dimension` AS `title`, SUBSTRING_INDEX(`dimension`, 'x', 1) AS `width`"
				. "\n FROM " . $db->quoteName('#__ka_names_gallery')
				. "\n WHERE `type` = 1"
				. "\n GROUP BY `width`"
				. "\n ORDER BY `width` DESC");
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

		$query = $db->getQuery(true);

		if ($page == 'wallpapers')
		{
			$query->select('`id`, `filename`, `dimension`');
			$query->from($db->quoteName('#__ka_names_gallery'));

			if ($filter != '0')
			{
				$where = " AND `dimension` LIKE " . $db->quote($db->escape($filter, true) . "%", false);
			}
			else
			{
				$where = "";
			}

			$query->where('`name_id` = ' . (int) $id . ' AND `state` = 1 AND `type` = 1' . $where);
		}
		elseif ($page == 'posters')
		{
			$query->select('`id`, `filename`, `dimension`');
			$query->from($db->quoteName('#__ka_names_gallery'));
			$query->where('`name_id` = ' . (int) $id . ' AND `state` = 1 AND `type` = 2');
		}
		elseif ($page == 'photos')
		{
			$query->select('`g`.`id`, `g`.`filename`, `g`.`dimension`');
			$query->from($db->quoteName('#__ka_names_gallery') . ' AS `g`');
			$query->select(' `n`.`gender`');
			$query->leftJoin($db->quoteName('#__ka_names') . ' AS `n` ON `n`.`id` = `g`.`name_id`');
			$query->where('`name_id` = ' . (int) $id . ' AND `g`.`state` = 1 AND `type` = 3');
		}
		else
		{
			$query = null;
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
