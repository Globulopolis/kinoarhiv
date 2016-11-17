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
	protected function populateState($ordering = 'p.premiere_date', $direction = 'ASC')
	{
		if ($this->context)
		{
			$app = JFactory::getApplication();
			$params = JComponentHelper::getParams('com_kinoarhiv');

			$value = $app->getUserStateFromRequest($this->context . '.list.limit', 'limit', $params->get('list_limit'), 'uint');
			$limit = $value;
			$this->setState('list.limit', $limit);

			$value = $app->getUserStateFromRequest($this->context . '.limitstart', 'limitstart', 0, 'uint');
			$limitstart = ($limit != 0 ? (floor($value / $limit) * $limit) : 0);
			$this->setState('list.start', $limitstart);
			$this->setState('list.ordering', $ordering);
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
		$db = $this->getDbo();
		$user = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$app = JFactory::getApplication();
		$lang = JFactory::getLanguage();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$null_date = $db->quote($db->getNullDate());

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
			->join('LEFT', $db->quoteName('#__ka_movies_gallery', 'g') . ' ON g.movie_id = m.id AND g.type = 2 AND g.frontpage = 1 AND g.state = 1');

		$query->select('p.premiere_date, p.vendor_id')
			->join('LEFT', $db->quoteName('#__ka_premieres', 'p') . ' ON p.movie_id = m.id AND p.country_id != 0');

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

		$query->where('p.premiere_date != ' . $null_date);
		$query->group('m.id');
		$query->order($this->getState('list.ordering', 'p.premiere_date') . ' ' . $this->getState('list.direction', 'ASC'));

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
