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

use Joomla\Registry\Registry;

/**
 * Class KinoarhivModelSearch
 *
 * @since  3.0
 */
class KinoarhivModelSearch extends JModelLegacy
{
	/**
	 * Get initial data for lists in search form
	 *
	 * @return   object
	 */
	public function getItems()
	{
		$db = $this->getDbo();
		$user = JFactory::getUser();
		$lang = JFactory::getLanguage();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$default_value = array(array('value' => '', 'text' => '-'));
		$items = (object) array(
			'movies' => (object) array(),
			'names'  => (object) array(),
		);

		// Get years for movies
		$query = $db->getQuery(true)
			->select('year')
			->from($db->quoteName('#__ka_movies'))
			->group('year');

		$db->setQuery($query);
		$_from_year = $db->loadObjectList();
		$new_years_arr = array();

		foreach ($_from_year as $key => $years)
		{
			$y = explode('-', str_replace(' ', '', $years->year));

			$new_years_arr[$key]['value'] = $y[0];
			$new_years_arr[$key]['text'] = $y[0];

			if (isset($y[1]) && !empty($y[1]))
			{
				$new_years_arr[$key]['value'] = $y[1];
				$new_years_arr[$key]['text'] = $y[1];
			}
		}

		rsort($new_years_arr);
		$items->movies->from_year = array_merge($default_value, $new_years_arr);
		$items->movies->to_year = &$items->movies->from_year;

		// Get the list of countries
		$query = $db->getQuery(true)
			->select($db->quoteName(array('id', 'name', 'code')))
			->from($db->quoteName('#__ka_countries'))
			->where("state = 1 AND language IN (" . $db->quote($lang->getTag()) . ",'*')")
			->order('name ASC');

		$db->setQuery($query);
		$countries = $db->loadObjectList();

		$items->movies->countries = array_merge(array((object) array('id' => '', 'name' => '', 'code' => '')), $countries);

		// Get the list of names
		$query = $db->getQuery(true)
			->select($db->quoteName(array('id', 'name', 'latin_name')))
			->from($db->quoteName('#__ka_names'))
			->where("state = 1 AND access IN (" . $groups . ") AND language IN (" . $db->quote($lang->getTag()) . ",'*')")
			->order('id ASC');

		$db->setQuery($query);
		$cast = $db->loadObjectList();

		$items->movies->cast = array_merge(array((object) array('id' => '', 'name' => '')), $cast);

		// Get the list of vendors
		$query = $db->getQuery(true)
			->select($db->quoteName(array('id', 'company_name', 'company_name_intl')))
			->from($db->quoteName('#__ka_vendors'))
			->where("state = 1 AND language IN (" . $db->quote($lang->getTag()) . ",'*')");

		$db->setQuery($query);
		$_vendors = $db->loadObjectList();
		$vendors = array();

		foreach ($_vendors as $vendor)
		{
			$text = '';

			if ($vendor->company_name != '')
			{
				$text .= $vendor->company_name;
			}

			if ($vendor->company_name != '' && $vendor->company_name_intl != '')
			{
				$text .= ' / ';
			}

			if ($vendor->company_name_intl != '')
			{
				$text .= $vendor->company_name_intl;
			}

			$vendors[] = array('value' => $vendor->id, 'text' => $text);
		}

		$items->movies->vendors = array_merge(array(array('value' => '', 'text' => '')), $vendors);

		// Get the list of genres
		$query = $db->getQuery(true)
			->select($db->quoteName('id', 'value') . ', ' . $db->quoteName('name', 'text'))
			->from($db->quoteName('#__ka_genres'))
			->where("state = 1 AND access IN (" . $groups . ") AND language IN (" . $db->quote($lang->getTag()) . ",'*')")
			->order('name ASC');

		$db->setQuery($query);
		$items->movies->genres = $db->loadObjectList();

		// MPAA
		$items->movies->mpaa = array(
			array('value' => '', 'text' => '-'),
			array('value' => 'g', 'text' => 'G'),
			array('value' => 'gp', 'text' => 'GP'),
			array('value' => 'pg', 'text' => 'PG'),
			array('value' => 'pg-13', 'text' => 'PG-13'),
			array('value' => 'r', 'text' => 'R'),
			array('value' => 'nc-17', 'text' => 'NC-17')
		);

		// Russian age restict
		$items->movies->age_restrict = array(
			array('value' => '-1', 'text' => '-'),
			array('value' => '0', 'text' => '0+'),
			array('value' => '6', 'text' => '6+'),
			array('value' => '12', 'text' => '12+'),
			array('value' => '16', 'text' => '16+'),
			array('value' => '18', 'text' => '18+')
		);

		// Ukrainian age restict
		$items->movies->ua_rate = array(
			array('value' => '-1', 'text' => '-'),
			array('value' => '0', 'text' => JText::_('COM_KA_SEARCH_ADV_MOVIES_UA_RATE_0')),
			array('value' => '1', 'text' => JText::_('COM_KA_SEARCH_ADV_MOVIES_UA_RATE_1')),
			array('value' => '2', 'text' => JText::_('COM_KA_SEARCH_ADV_MOVIES_UA_RATE_2'))
		);

		// Budgets
		$query = $db->getQuery(true)
			->select($db->quoteName('budget', 'value') . ', ' . $db->quoteName('budget', 'text'))
			->from($db->quoteName('#__ka_movies'))
			->where("budget != '' AND state = 1 AND access IN (" . $groups . ") AND language IN (" . $db->quote($lang->getTag()) . ",'*')")
			->group('budget')
			->order('budget ASC');

		$db->setQuery($query);
		$budgets = $db->loadObjectList();

		$items->movies->from_budget = array_merge(
			array(array('value' => '', 'text' => '-')),
			$budgets
		);
		$items->movies->to_budget = &$items->movies->from_budget;

		$items->movies->tags = (object) array();

		$items->names->gender = array(
			array('value' => '', 'text' => '-'),
			array('value' => '1', 'text' => JText::_('COM_KA_SEARCH_ADV_NAMES_GENDER_M')),
			array('value' => '0', 'text' => JText::_('COM_KA_SEARCH_ADV_NAMES_GENDER_F'))
		);

		$items->names->birthcountry = &$items->movies->countries;

		// Amplua
		$query = $db->getQuery(true)
			->select($db->quoteName('id', 'value') . ', ' . $db->quoteName('title', 'text'))
			->from($db->quoteName('#__ka_names_career'))
			->where("(is_mainpage = 1 OR is_amplua = 1) AND language IN (" . $db->quote($lang->getTag()) . ",'*')");

		$amplua_disabled = $params->get('search_names_amplua_disabled');

		if (!empty($amplua_disabled))
		{
			$query->where('id NOT IN (' . $amplua_disabled . ')');
		}

		$query->group('title')
			->order('ordering ASC, title ASC');

		$db->setQuery($query);
		$amplua = $db->loadObjectList();

		$items->names->amplua = array_merge(
			array(array('value' => '', 'text' => '-')),
			$amplua
		);

		return $items;
	}

	/**
	 * Get the homepage Itemid for movies and names lists
	 *
	 * @return   array
	 */
	public function getHomeItemid()
	{
		$db = $this->getDbo();
		$user = JFactory::getUser();
		$lang = JFactory::getLanguage();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$itemid = array('movies' => 0, 'names' => 0);

		$query = $db->getQuery(true)
			->select('id')
			->from($db->quoteName('#__menu'))
			->where("link = 'index.php?option=com_kinoarhiv&view=movies' AND type = 'component'")
			->where("published = 1 AND access IN (" . $groups . ") AND language IN (" . $db->quote($lang->getTag()) . ",'*')")
			->setLimit(1, 0);

		$db->setQuery($query);
		$itemid['movies'] = $db->loadResult();

		$query = $db->getQuery(true)
			->select('id')
			->from($db->quoteName('#__menu'))
			->where("link = 'index.php?option=com_kinoarhiv&view=names' AND type = 'component'")
			->where("published = 1 AND access IN (" . $groups . ") AND language IN (" . $db->quote($lang->getTag()) . ",'*')")
			->setLimit(1, 0);

		$db->setQuery($query);
		$itemid['names'] = $db->loadResult();

		return $itemid;
	}

	/**
	 * Get the values for inputs
	 *
	 * @return   object
	 */
	public function getActiveFilters()
	{
		$filter = JFilterInput::getInstance();
		$input = JFactory::getApplication()->input;
		$items = new Registry;

		if (array_key_exists('movies', $input->get('filters', array(), 'array')))
		{
			$filters_arr = $input->get('filters', array(), 'array');
			$filters = $filters_arr['movies'];

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

		if (array_key_exists('names', $input->get('filters', array(), 'array')))
		{
			$filters_arr = $input->get('filters', array(), 'array');
			$filters = $filters_arr['names'];
			$vars = array(
				'filters' => array(
					'names' => array(
						'name'         => isset($filters['name']) ? $filter->clean($filters['name'], 'string') : '',
						'gender'       => isset($filters['gender']) ? $filter->clean($filters['gender'], 'alnum') : '',
						'mtitle'       => isset($filters['mtitle']) ? $filter->clean($filters['mtitle'], 'int') : '',
						'birthday'     => isset($filters['birthday']) ? $filter->clean($filters['birthday'], 'string') : '',
						'birthplace'   => isset($filters['birthplace']) ? $filter->clean($filters['birthplace'], 'string') : '',
						'birthcountry' => isset($filters['birthcountry']) ? $filter->clean($filters['birthcountry'], 'int') : '',
						'amplua'       => isset($filters['amplua']) ? $filter->clean($filters['amplua'], 'int') : ''
					)
				)
			);

			$items->loadArray($vars);
		}

		return $items;
	}
}
