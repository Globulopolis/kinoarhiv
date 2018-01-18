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
	 * @throws  Exception
	 *
	 * @since   3.0
	 */
	protected function getListQuery()
	{
		$app  = JFactory::getApplication();
		$page = $app->input->get('page', '', 'word');
		$tab  = $app->input->get('tab', '', 'word');

		switch ($page)
		{
			case 'favorite':
				$query = $this->getFavorited($tab);
				break;
			case 'watched':
				$query = $this->getWatched();
				break;
			case 'votes':
				$query = $this->getVoted($tab);
				break;
			case 'reviews':
				$query = $this->getReviewed();
				break;
			default:
				$query = null;
		}

		return $query;
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @param   string  $content  Content type. Can be 'movies', 'names', 'albums'.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   3.1
	 */
	protected function getFavorited($content)
	{
		$db     = $this->getDbo();
		$user   = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$query  = $db->getQuery(true);

		if ($content == '' || $content == 'movies')
		{
			$query->select(array('m.id', 'm.title', 'm.alias', 'm.year', 'f.favorite_added', 'f.watched_added'))
				->from($db->quoteName('#__ka_movies', 'm'))
				->leftJoin($db->quoteName('#__ka_user_marked_movies', 'f') . ' ON f.movie_id = m.id');

			$subquery = $db->getQuery(true)
				->select('movie_id')
				->from($db->quoteName('#__ka_user_marked_movies'))
				->where('uid = ' . $user->get('id') . ' AND favorite = 1');

			$query->where('state = 1 AND id IN (' . $subquery . ') AND access IN (' . $groups . ')')
				->group('id')
				->order($db->quoteName('created') . ' DESC');
		}
		elseif ($content == 'names')
		{
			$query->select(array('n.id', 'n.name', 'n.latin_name', 'n.alias', 'n.date_of_birth', 'f.favorite_added'))
				->from($db->quoteName('#__ka_names', 'n'))
				->leftJoin($db->quoteName('#__ka_user_marked_names', 'f') . ' ON f.name_id = n.id');

			$subquery = $db->getQuery(true)
				->select('name_id')
				->from($db->quoteName('#__ka_user_marked_names'))
				->where('uid = ' . $user->get('id') . ' AND favorite = 1');

			$query->where('state = 1 AND id IN (' . $subquery . ') AND access IN (' . $groups . ')')
				->group('id')
				->order($db->quoteName('ordering') . ' DESC');
		}
		elseif ($content == 'albums')
		{
			$query->select(array('a.id', 'a.title', 'a.alias', 'a.year', 'f.favorite_added'))
				->from($db->quoteName('#__ka_music_albums', 'a'))
				->leftJoin($db->quoteName('#__ka_user_marked_albums', 'f') . ' ON f.album_id = a.id');

			$subquery = $db->getQuery(true)
				->select('album_id')
				->from($db->quoteName('#__ka_user_marked_albums'))
				->where('uid = ' . $user->get('id') . ' AND favorite = 1');

			$query->where('state = 1 AND id IN (' . $subquery . ') AND access IN (' . $groups . ')')
				->group('id')
				->order($db->quoteName('created') . ' DESC');
		}

		return $query;
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   3.1
	 */
	protected function getWatched()
	{
		$db     = $this->getDbo();
		$user   = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$query  = $db->getQuery(true);

		$query->select(array('m.id', 'm.title', 'm.alias', 'm.year', 'w.watched_added'))
			->from($db->quoteName('#__ka_movies', 'm'))
			->leftJoin($db->quoteName('#__ka_user_marked_movies', 'w') . ' ON w.movie_id = m.id');

		$subquery = $db->getQuery(true)
			->select('movie_id')
			->from($db->quoteName('#__ka_user_marked_movies'))
			->where('uid = ' . $user->get('id') . ' AND watched = 1');

		$query->where('state = 1 AND id IN (' . $subquery . ') AND access IN (' . $groups . ')')
			->group('id')
			->order($db->quoteName('created') . ' DESC');

		return $query;
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @param   string  $content  Content type. Can be 'movies', 'albums'.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   3.1
	 */
	protected function getVoted($content)
	{
		$db     = $this->getDbo();
		$user   = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$query  = $db->getQuery(true);

		if ($content == '' || $content == 'movies')
		{
			$query->select($db->quoteName(array('m.id', 'm.title', 'm.alias', 'm.rate_loc', 'm.rate_sum_loc', 'm.year')))
				->from($db->quoteName('#__ka_movies', 'm'));

			// Join over user votes
			$query->select('v.vote AS my_vote, v._datetime')
				->join('LEFT', $db->quoteName('#__ka_user_votes_movies', 'v') . ' ON v.uid = ' . (int) $user->get('id') . ' AND v.movie_id = m.id');

			$subquery = $db->getQuery(true)
				->select('movie_id')
				->from($db->quoteName('#__ka_user_votes_movies'))
				->where('uid = ' . $user->get('id'));

			$query->where('state = 1 AND id IN (' . $subquery . ') AND `access` IN (' . $groups . ')')
				->where("v.vote != 0 AND v._datetime != '" . $db->getNullDate() . "'")
				->order($db->quoteName('_datetime') . ' DESC');
		}
		elseif ($content == 'albums')
		{
			$query->select($db->quoteName(array('a.id', 'a.title', 'a.alias', 'a.rate', 'a.rate_sum', 'a.year')))
				->from($db->quoteName('#__ka_music_albums', 'a'));

			// Join over user votes
			$query->select('v.vote AS my_vote, v._datetime')
				->join('LEFT', $db->quoteName('#__ka_user_votes_albums', 'v') . ' ON v.uid = ' . (int) $user->get('id') . ' AND v.album_id = a.id');

			$subquery = $db->getQuery(true)
				->select('album_id')
				->from($db->quoteName('#__ka_user_votes_albums'))
				->where('uid = ' . $user->get('id'));

			$query->where('state = 1 AND id IN (' . $subquery . ') AND `access` IN (' . $groups . ')')
				->where("v.vote != 0 AND v._datetime != '" . $db->getNullDate() . "'")
				->order($db->quoteName('_datetime') . ' DESC');
		}

		return $query;
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   3.1
	 */
	protected function getReviewed()
	{
		$db    = $this->getDbo();
		$user  = JFactory::getUser();
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('r.id', 'r.movie_id', 'r.review', 'r.created', 'r.type', 'r.ip', 'r.state', 'm.title', 'm.year')))
			->from($db->quoteName('#__ka_reviews', 'r'))
			->join('LEFT', $db->quoteName('#__ka_movies', 'm') . ' ON m.id = r.movie_id')
			->where('r.uid = ' . (int) $user->get('id') . ' AND m.state = 1')
			->order($db->quoteName('created') . ' DESC');

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
