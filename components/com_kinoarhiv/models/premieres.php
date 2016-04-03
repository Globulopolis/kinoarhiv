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

use Joomla\String\StringHelper;

/**
 * Premieres list class
 *
 * @since  3.0
 */
class KinoarhivModelPremieres extends JModelList
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
		// Add filter fields. If it's not set when the active filters will be hidden.
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array('title', 'm.title', 'country', 'vendor', 'year', 'month');
		}

		parent::__construct($config);

		if (empty($this->context))
		{
			$this->context = strtolower('com_kinoarhiv.premieres');
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
	protected function populateState($ordering = 'p.premiere_date', $direction = 'DESC')
	{
		parent::populateState($ordering, $direction);

		if ($this->context)
		{
			$app = JFactory::getApplication();
			$params = JComponentHelper::getParams('com_kinoarhiv');

			$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string');
			$this->setState('filter.search', trim($search));

			$country = $app->getUserStateFromRequest($this->context . '.filter.country', 'filter_country', '', 'uint');
			$this->setState('filter.country', $country);

			$vendor = $app->getUserStateFromRequest($this->context . '.filter.vendor', 'filter_vendor', '', 'uint');
			$this->setState('filter.vendor', $vendor);

			$year = $app->getUserStateFromRequest($this->context . '.filter.year', 'filter_year', '', 'string');
			$this->setState('filter.year', $year);

			$month = $app->getUserStateFromRequest($this->context . '.filter.month', 'filter_month', '', 'string');
			$this->setState('filter.month', $month);

			$value = $app->getUserStateFromRequest($this->context . '.list.limit', 'limit', $params->get('list_limit'), 'uint');
			$limit = $value;
			$this->setState('list.limit', $limit);

			$value = $app->getUserStateFromRequest($this->context . '.limitstart', 'limitstart', 0, 'uint');
			$limitstart = ($limit != 0 ? (floor($value / $limit) * $limit) : 0);
			$this->setState('list.start', $limitstart);

			$this->setState('list.ordering', $ordering);
			$this->setState('list.fullordering', 'p.premiere_date DESC');

			$listOrder = $app->getUserStateFromRequest($this->context . '.list.direction', 'direction', $direction, 'cmd');
			$this->setState('list.direction', $listOrder);
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
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.country');
		$id .= ':' . $this->getState('filter.vendor');
		$id .= ':' . $this->getState('filter.year');
		$id .= ':' . $this->getState('filter.month');
		$id .= ':' . $this->getState('list.limit');

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
		$lang = JFactory::getLanguage();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$null_date = $db->quote($db->getNullDate());
		$ids = array();

		$query = $db->getQuery(true);

		$query->select(
			$this->getState(
				'list.select',
				'm.id, m.parent_id, m.title, m.alias, m.fs_alias, m.introtext AS text, m.plot, m.rate_loc, m.rate_sum_loc, ' .
				'm.imdb_votesum, m.imdb_votes, m.imdb_id, m.kp_votesum, m.kp_votes, m.kp_id, m.rate_fc, m.rottentm_id, ' .
				'm.metacritics, m.metacritics_id, m.rate_custom, m.year, ' .
				'DATE_FORMAT(m.created, "%Y-%m-%d") AS ' . $db->quoteName('created') . ', m.created_by, ' .
				'CASE WHEN m.modified = ' . $null_date . ' THEN m.created ELSE DATE_FORMAT(m.modified, "%Y-%m-%d") END AS modified, ' .
				'CASE WHEN m.publish_up = ' . $null_date . ' THEN m.created ELSE m.publish_up END AS publish_up, ' .
				'm.publish_down, m.attribs, m.state'
			)
		);
		$query->from($db->quoteName('#__ka_movies', 'm'));

		// Join over gallery item
		$query->select('g.filename, g.dimension')
			->join('LEFT', $db->quoteName('#__ka_movies_gallery', 'g') . ' ON g.movie_id = m.id AND g.type = 2 AND g.poster_frontpage = 1 AND g.state = 1');

		$country = $this->getState('filter.country');

		if ($country != '')
		{
			$query->select('p.premiere_date, p.vendor_id')
				->join('LEFT', $db->quoteName('#__ka_premieres', 'p') . ' ON p.movie_id = m.id AND p.country_id = ' . (int) $country);
		}
		else
		{
			$query->select('p.premiere_date, p.vendor_id')
				->join('LEFT', $db->quoteName('#__ka_premieres', 'p') . ' ON p.movie_id = m.id AND p.country_id != 0');
		}

		$query->select('v.company_name, v.company_name_intl, v.company_name_alias')
			->join('LEFT', $db->quoteName('#__ka_vendors', 'v') . ' ON v.id = p.vendor_id AND v.state = 1');

		if (!$user->get('guest'))
		{
			$query->select('u.favorite')
				->join('LEFT', $db->quoteName('#__ka_user_marked_movies', 'u') . ' ON u.uid = ' . $user->get('id') . ' AND u.movie_id = m.id');
		}

		$query->where('m.state = 1 AND m.language IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ')')
			->where('parent_id = 0 AND m.access IN (' . $groups . ')');

		if ($params->get('use_alphabet') == 1)
		{
			$letter = $app->input->get('letter', '', 'string');

			if ($letter !== '')
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

		// Filter by title
		$search = $this->getState('filter.search');

		if ($params->get('filter_premieres_search') == 1 && !empty($search))
		{
			$query->where("m.title LIKE '%" . $db->escape($search) . "%'");
		}

		// Filter by country
		if ($params->get('filter_premieres_country') == 1 && is_numeric($country))
		{
			$subquery = $db->getQuery(true)
				->select('movie_id')
				->from($db->quoteName('#__ka_premieres'))
				->where('country_id = ' . (int) $country);

			$db->setQuery($subquery);
			$ids[] = $db->loadColumn();
		}

		// Filter by vendor
		$vendor = $this->getState('filter.vendor');

		if ($params->get('filter_release_vendor') == 1 && is_numeric($vendor))
		{
			$subquery = $db->getQuery(true)
				->select('movie_id')
				->from($db->quoteName('#__ka_premieres'))
				->where('vendor_id = ' . (int) $vendor);

			$db->setQuery($subquery);
			$ids[] = $db->loadColumn();
		}

		// Filter by year
		$year = $this->getState('filter.year');

		if ($params->get('filter_release_year') == 1 && is_numeric($year))
		{
			$subquery = $db->getQuery(true)
				->select('movie_id')
				->from($db->quoteName('#__ka_premieres'))
				->where("premiere_date LIKE '%" . $db->escape($year) . "%'");

			$db->setQuery($subquery);
			$ids[] = $db->loadColumn();
		}

		// Filter by month
		$month = $this->getState('filter.month');

		if ($params->get('filter_release_month') == 1 && !empty($month))
		{
			$subquery = $db->getQuery(true)
				->select('movie_id')
				->from($db->quoteName('#__ka_premieres'))
				->where("premiere_date LIKE '%" . $db->escape($month) . "%'");

			$db->setQuery($subquery);
			$ids[] = $db->loadColumn();
		}

		if (count($ids) > 0)
		{
			$where_ids = call_user_func_array('array_merge', $ids);
			$where_ids = \Joomla\Utilities\ArrayHelper::arrayUnique($where_ids);

			$query->where('m.id IN (' . implode(',', $where_ids) . ')');
		}

		$query->where('p.premiere_date != ' . $null_date);
		$query->group('m.id');
		$query->order($this->getState('list.ordering', 'p.premiere_date') . ' ' . $this->getState('list.direction', 'ASC'));

		return $query;
	}

	/**
	 * Premieres filter
	 *
	 * @return  array
	 *
	 * @since   3.0
	 */
	public function getSelectList()
	{
		$db = $this->getDBO();
		$app = JFactory::getApplication();
		$lang = JFactory::getLanguage();
		$params = JComponentHelper::getParams('com_kinoarhiv');

		// It's a string because country_id == 0 it'a world premiere
		$country = $app->input->get('country', '', 'word');
		$year = $app->input->get('year', 0, 'int');
		$result = array(
			'countries' => array(
				array('name' => JText::_('JALL'), 'code' => '')
			),
			'years'     => array(
				array('value' => 0, 'name' => JText::_('JALL'))
			),
			'months'    => array(
				array('value' => '', 'name' => JText::_('JALL'))
			),
			'vendors'   => array(
				array('value' => '', 'name' => JText::_('JALL'))
			)
		);

		// Countries list
		if ($params->get('filter_premieres_country') == 1)
		{
			$subquery = $db->getQuery(true)
				->select('country_id')
				->from($db->quoteName('#__ka_premieres'))
				->where('country_id != 0');

			$query = $db->getQuery(true)
				->select('name, code')
				->from($db->quoteName('#__ka_countries'))
				->where('id IN (' . $subquery . ') AND state = 1')
				->group('code');

			$db->setQuery($query);

			try
			{
				$countries = $db->loadAssocList();

				if (count($countries) > 0)
				{
					$result['countries'] = array_merge($result['countries'], $countries);
				}
			}
			catch (Exception $e)
			{
				KAComponentHelper::eventLog($e->getMessage());
			}
		}

		// Years list
		if ($params->get('filter_premieres_year') == 1)
		{
			$query = $db->getQuery(true)
				->select("DATE_FORMAT(premiere_date, '%Y') AS value, DATE_FORMAT(premiere_date, '%Y') AS name")
				->from($db->quoteName('#__ka_premieres'));

			if ($country !== '')
			{
				$subquery = $db->getQuery(true)
					->select('id')
					->from($db->quoteName('#__ka_countries'))
					->where("code = '" . $db->escape($country) . "' AND language IN (" . $db->quote($lang->getTag()) . "," . $db->quote('*') . ")");

				$query->where('country_id = (' . $subquery . ')');
			}

			$query->group('value');
			$db->setQuery($query);

			try
			{
				$years = $db->loadAssocList();

				if (count($years) > 0)
				{
					$result['years'] = array_merge($result['years'], $years);
				}
			}
			catch (Exception $e)
			{
				KAComponentHelper::eventLog($e->getMessage());
			}
		}

		// Months list
		if ($params->get('filter_premieres_month') == 1)
		{
			$query = $db->getQuery(true)
				->select("DATE_FORMAT(premiere_date, '%Y-%m') AS value, premiere_date")
				->from($db->quoteName('#__ka_premieres'));

			if ($country !== '')
			{
				$subquery = $db->getQuery(true)
					->select('id')
					->from($db->quoteName('#__ka_countries'))
					->where("code = '" . $db->escape($country) . "' AND language IN (" . $db->quote($lang->getTag()) . "," . $db->quote('*') . ")");

				$query->where('country_id = (' . $subquery . ')');

				if (!empty($year))
				{
					$query->where("premiere_date LIKE '%" . $year . "%'");
				}
			}
			else
			{
				if (!empty($year))
				{
					$query->where("premiere_date LIKE '%" . $year . "%'");
				}
			}

			$query->group('value');
			$db->setQuery($query);

			try
			{
				$months = $db->loadAssocList();

				if (count($months) > 0)
				{
					foreach ($months as $key => $month)
					{
						$months[$key]['name'] = JHTML::_('date', strtotime($month['premiere_date']), 'F Y');
					}

					$result['months'] = array_merge($result['months'], $months);
				}
			}
			catch (Exception $e)
			{
				KAComponentHelper::eventLog($e->getMessage());
			}
		}

		// Distributors list
		if ($params->get('filter_release_vendor') == 1)
		{
			$subquery = $db->getQuery(true)
				->select('vendor_id')
				->from($db->quoteName('#__ka_premieres'))
				->where("vendor_id != 0 AND language IN (" . $db->quote($lang->getTag()) . "," . $db->quote('*') . ")");

			$query = $db->getQuery(true)
				->select('id AS value, company_name AS name, company_name_intl')
				->from($db->quoteName('#__ka_vendors'))
				->where('id IN (' . $subquery . ') AND state = 1');

			$db->setQuery($query);

			try
			{
				$vendors = $db->loadAssocList();

				if (count($vendors) > 0)
				{
					$result['vendors'] = array_merge($result['vendors'], $vendors);
				}
			}
			catch (Exception $e)
			{
				KAComponentHelper::eventLog($e->getMessage());
			}
		}

		return $result;
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
