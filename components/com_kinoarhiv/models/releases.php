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

use Joomla\String\String;

/**
 * Releases list class
 *
 * @since  3.0
 */
class KinoarhivModelReleases extends JModelList
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
		parent::__construct($config);

		if (empty($this->context))
		{
			$this->context = $this->context = strtolower('com_kinoarhiv.releases');
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
			$this->setState('list.limit', $limit);

			$value = $app->getUserStateFromRequest($this->context . '.limitstart', 'limitstart', 0);
			$limitstart = ($limit != 0 ? (floor($value / $limit) * $limit) : 0);
			$this->setState('list.start', $limitstart);
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

		// It's a string because country_id == 0 - all countries
		$country = $app->input->get('country', '', 'word');
		$year = $app->input->get('year', 0, 'int');
		$vendor = $app->input->get('vendor', 0, 'int');
		$month = $app->input->get('month', '', 'string');
		$mediatype = $app->input->get('mediatype', '', 'string');
		$null_date = $db->quote($db->getNullDate());

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

		if ($country != '')
		{
			$subquery = $db->getQuery(true)
				->select('id')
				->from($db->quoteName('#__ka_countries'))
				->where('code = "' . $db->escape($country) . '" AND language IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ')');

			$query->select('r.release_date, r.vendor_id')
				->join('LEFT', $db->quoteName('#__ka_releases', 'r') . ' ON r.movie_id = m.id AND r.country_id = (' . $subquery . ')');

			$query->select('v.company_name, v.company_name_intl, v.company_name_alias')
				->join('LEFT', $db->quoteName('#__ka_vendors', 'v') . ' ON v.id = r.vendor_id AND v.state = 1');
		}
		else
		{
			$query->select('r.release_date, r.vendor_id')
				->join('LEFT', $db->quoteName('#__ka_releases', 'r') . ' ON r.movie_id = m.id AND r.country_id != 0');

			$query->select('v.company_name, v.company_name_intl, v.company_name_alias')
				->join('LEFT', $db->quoteName('#__ka_vendors', 'v') . ' ON v.id = r.vendor_id AND v.state = 1 AND v.language IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ')');
		}

		if (!$user->get('guest'))
		{
			$query->select('u.favorite')
				->leftJoin($db->quoteName('#__ka_user_marked_movies', 'u') . ' ON u.uid = ' . $user->get('id') . ' AND u.movie_id = m.id');
		}

		$query->where('m.state = 1 AND m.language IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ') AND parent_id = 0 AND m.access IN (' . $groups . ')');

		if ($params->get('use_alphabet') == 1)
		{
			$letter = $app->input->get('letter', '', 'string');

			if ($letter != '')
			{
				if ($letter === '0-1')
				{
					$range = range(0, 9);
					$query->where('(m.title LIKE "' . implode('%" OR m.title LIKE "', $range) . '%")');
				}
				else
				{
					// Only any kind of letter from any language.
					if (preg_match('#\p{L}#u', $letter, $matches))
					{
						$query->where('m.title LIKE "' . $db->escape(String::strtoupper($matches[0])) . '%"');
					}
				}
			}
		}

		if ($params->get('filter_release_country') == 1 && $country != '')
		{
			$subquery0 = $db->getQuery(true)
				->select('id')
				->from($db->quoteName('#__ka_countries'))
				->where('code = "' . $db->escape($country) . '" AND language IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ')');

			$subquery1 = $db->getQuery(true)
				->select('movie_id')
				->from($db->quoteName('#__ka_releases'))
				->where('country_id = (' . $subquery0 . ')');

			$query->where('m.id IN (' . $subquery1 . ')');
		}

		if ($params->get('filter_release_year') == 1 && !empty($year))
		{
			$query->where('m.id IN (SELECT movie_id FROM ' . $db->quoteName('#__ka_releases') . ' WHERE release_date LIKE "%' . $year . '%")');
		}

		if ($params->get('filter_release_month') == 1 && $month != '')
		{
			$query->where('m.id IN (SELECT movie_id FROM ' . $db->quoteName('#__ka_releases') . ' WHERE release_date LIKE "%' . $month . '%")');
		}

		if ($params->get('filter_release_vendor') == 1 && !empty($vendor))
		{
			$query->where('m.id IN (SELECT movie_id FROM ' . $db->quoteName('#__ka_releases') . ' WHERE vendor_id = "' . (int) $vendor . '")');
		}

		if ($params->get('filter_release_mediatype') == 1 && $mediatype != '')
		{
			$query->where('r.media_type = ' . (int) $mediatype);
		}

		$query->where('r.release_date != ' . $null_date)
			->group($db->quoteName('m.id'))
			->order($this->getState('list.ordering', 'r.release_date') . ' ' . $this->getState('list.direction', 'DESC'));

		return $query;
	}

	/**
	 * Releases filter
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
		$mediatypes = array();
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
			),
			'mediatype' => array(
				array('value' => '', 'name' => JText::_('JALL'))
			)
		);

		// Countries list
		if ($params->get('filter_release_country') == 1)
		{
			$query = $db->getQuery(true)
				->select('name, code')
				->from($db->quoteName('#__ka_countries'))
				->where('id IN (SELECT country_id FROM ' . $db->quoteName('#__ka_releases') . ' WHERE country_id != 0) AND state = 1')
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
		if ($params->get('filter_release_year') == 1)
		{
			$query = $db->getQuery(true)
				->select("DATE_FORMAT(release_date, '%Y') AS value, DATE_FORMAT(release_date, '%Y') AS name")
				->from($db->quoteName('#__ka_releases'));

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
		if ($params->get('filter_release_month') == 1)
		{
			$query = $db->getQuery(true)
				->select("DATE_FORMAT(release_date, '%Y-%m') AS value, release_date")
				->from($db->quoteName('#__ka_releases'));

			if ($country !== '')
			{
				$subquery = $db->getQuery(true)
					->select('id')
					->from($db->quoteName('#__ka_countries'))
					->where("code = '" . $db->escape($country) . "' AND language IN (" . $db->quote($lang->getTag()) . "," . $db->quote('*') . ")");

				$query->where('country_id = (' . $subquery . ')');

				if (!empty($year))
				{
					$query->where("release_date LIKE '%" . $year . "%'");
				}
			}
			else
			{
				if (!empty($year))
				{
					$query->where("release_date LIKE '%" . $year . "%'");
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
						$months[$key]['name'] = JHTML::_('date', strtotime($month['release_date']), 'F Y');
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
				->from($db->quoteName('#__ka_releases'))
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

		// Media types
		if ($params->get('filter_release_mediatype') == 1)
		{
			for ($i = 0, $n = 20; $i < $n; $i++)
			{
				$mediatypes[] = array('value' => $i, 'name' => JText::_('COM_KA_RELEASES_MEDIATYPE_' . $i));
			}

			$result['mediatype'] = array_merge($result['mediatype'], $mediatypes);
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
