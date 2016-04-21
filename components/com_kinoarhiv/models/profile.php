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
 * Class KinoarhivModelProfile
 *
 * @since  3.0
 */
class KinoarhivModelProfile extends JModelList
{
	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   3.0
	 */
	protected function getListQuery()
	{
		$db = $this->getDbo();
		$user = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$app = JFactory::getApplication();
		$page = $app->input->get('page', '', 'cmd');

		if ($page == 'favorite')
		{
			$tab = $app->input->get('tab', '', 'cmd');
			$query = $db->getQuery(true);

			if ($tab == '' || $tab == 'movies')
			{
				$query->select($db->quoteName(array('id', 'title', 'alias', 'year')))
					->from($db->quoteName('#__ka_movies'));

				$subquery = $db->getQuery(true)
					->select('movie_id')
					->from($db->quoteName('#__ka_user_marked_movies'))
					->where('uid = ' . $user->get('id') . ' AND favorite = 1');

				$query->where('state = 1 AND id IN (' . $subquery . ') AND access IN (' . $groups . ')')
					->order($db->quoteName('created') . ' DESC');
			}
			elseif ($tab == 'names')
			{
				$query->select($db->quoteName(array('id', 'name', 'latin_name', 'alias', 'date_of_birth')))
					->from($db->quoteName('#__ka_names'));

				$subquery = $db->getQuery(true)
					->select('name_id')
					->from($db->quoteName('#__ka_user_marked_names'))
					->where('uid = ' . $user->get('id') . ' AND favorite = 1');

				$query->where('state = 1 AND id IN (' . $subquery . ') AND access IN (' . $groups . ')')
					->order($db->quoteName('ordering') . ' DESC');
			}
		}
		elseif ($page == 'watched')
		{
			$query = $db->getQuery(true);

			$query->select($db->quoteName(array('id', 'title', 'alias', 'year')))
				->from($db->quoteName('#__ka_movies'));

			$subquery = $db->getQuery(true)
				->select('movie_id')
				->from($db->quoteName('#__ka_user_marked_movies'))
				->where('uid = ' . $user->get('id') . ' AND watched = 1');

			$query->where('state = 1 AND id IN (' . $subquery . ') AND access IN (' . $groups . ')')
				->order($db->quoteName('created') . ' DESC');
		}
		elseif ($page == 'votes')
		{
			$query = $db->getQuery(true);

			$sel_subquery = $db->getQuery(true)
				->select('COUNT(uid)')
				->from($db->quoteName('#__ka_user_votes'))
				->where('movie_id = m.id');

			$query->select($db->quoteName(array('m.id', 'm.title', 'm.alias', 'm.rate_loc', 'm.rate_sum_loc', 'm.year')))
				->select('(' . $sel_subquery . ') AS total_voted')
				->from($db->quoteName('#__ka_movies', 'm'));

			// Join over user votes
			$query->select('v.vote AS my_vote, v._datetime')
				->join('LEFT', $db->quoteName('#__ka_user_votes', 'v') . ' ON v.uid = ' . (int) $user->get('id') . ' AND v.movie_id = m.id');

			$subquery = $db->getQuery(true)
				->select('movie_id')
				->from($db->quoteName('#__ka_user_votes'))
				->where('uid = ' . $user->get('id'));

			$query->where('state = 1 AND id IN (' . $subquery . ') AND `access` IN (' . $groups . ')')
				->order($db->quoteName('_datetime') . ' DESC');
		}
		elseif ($page == 'reviews')
		{
			$query = $db->getQuery(true);

			$query->select($db->quoteName(array('r.id', 'r.movie_id', 'r.review', 'r.created', 'r.type', 'r.ip', 'r.state', 'm.title', 'm.year')))
				->from($db->quoteName('#__ka_reviews', 'r'))
				->join('LEFT', $db->quoteName('#__ka_movies', 'm') . ' ON m.id = r.movie_id')
				->where('r.uid = ' . (int) $user->get('id') . ' AND m.state = 1')
				->order($db->quoteName('created') . ' DESC');
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
