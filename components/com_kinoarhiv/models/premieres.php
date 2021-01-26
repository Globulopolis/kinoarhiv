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

/**
 * Premieres list class
 *
 * @since  3.0
 */
class KinoarhivModelPremieres extends JModelList
{
	/**
	 * Context string for the model type.  This is used to handle uniqueness
	 * when dealing with the getStoreId() method and caching data structures.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $context = 'com_kinoarhiv.premieres';

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
			$params->loadString($menu->getParams());
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
		$db       = $this->getDbo();
		$user     = JFactory::getUser();
		$groups   = implode(',', $user->getAuthorisedViewLevels());
		$lang     = JFactory::getLanguage();
		$nullDate = $db->quote($db->getNullDate());

		$query = $db->getQuery(true);

		$query->select(
			$this->getState(
				'list.select',
				'm.id, m.parent_id, m.title, m.alias, m.fs_alias, m.introtext AS text, m.plot, m.rate_loc, m.rate_sum_loc, ' .
				'm.imdb_votesum, m.imdb_votes, m.imdb_id, m.kp_votesum, m.kp_votes, m.kp_id, m.rate_fc, m.rottentm_id, ' .
				'm.metacritics, m.metacritics_id, m.rate_custom, m.year, ' .
				'DATE_FORMAT(m.created, "%Y-%m-%d") AS ' . $db->quoteName('created') . ', m.created_by, ' .
				'CASE WHEN m.modified = ' . $nullDate . ' THEN m.created ELSE DATE_FORMAT(m.modified, "%Y-%m-%d") END AS modified, ' .
				'CASE WHEN m.publish_up = ' . $nullDate . ' THEN m.created ELSE m.publish_up END AS publish_up, ' .
				'm.publish_down, m.attribs, m.state'
			)
		);
		$query->from($db->quoteName('#__ka_movies', 'm'));

		// Join over gallery item
		$query->select('g.filename, g.dimension')
			->join('LEFT', $db->quoteName('#__ka_movies_gallery', 'g') . ' ON g.movie_id = m.id AND g.type = 2 AND g.frontpage = 1 AND g.state = 1');

		$query->select('p.premiere_date, p.vendor_id')
			->join('LEFT', $db->quoteName('#__ka_premieres', 'p') . ' ON p.movie_id = m.id AND p.country_id != 0');

		$query->select('v.company_name, v.company_name_alias')
			->join('LEFT', $db->quoteName('#__ka_vendors', 'v') . ' ON v.id = p.vendor_id AND v.state = 1');

		if (!$user->get('guest'))
		{
			$query->select($db->quoteName(array('u.favorite', 'u.watched')))
				->join('LEFT', $db->quoteName('#__ka_user_marked_movies', 'u') . ' ON u.uid = ' . $user->get('id') . ' AND u.movie_id = m.id');
		}

		$query->select('user.name AS username')
			->join('LEFT', $db->quoteName('#__users', 'user') . ' ON user.id = m.created_by');

		$query->where('m.state = 1 AND m.language IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ')')
			->where('parent_id = 0 AND m.access IN (' . $groups . ')');

		$query->where('p.premiere_date != ' . $nullDate);
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
