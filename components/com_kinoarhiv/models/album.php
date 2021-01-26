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
 * Music album item class
 *
 * @since  3.0
 */
class KinoarhivModelAlbum extends JModelForm
{
	/**
	 * Internal memory based cache array of data.
	 *
	 * @var    array
	 * @since  1.6
	 */
	protected $cache = array();

	/**
	 * Context string for the model type.  This is used to handle uniqueness
	 * when dealing with the getStoreId() method and caching data structures.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $context = null;

	/**
	 * Valid filter fields or ordering.
	 *
	 * @var    array
	 * @since  1.6
	 */
	protected $filter_fields = array();

	/**
	 * An internal cache for the last query used.
	 *
	 * @var    \JDatabaseQuery[]
	 * @since  1.6
	 */
	protected $query = array();

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JModelForm
	 * @since   3.0
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		if (isset($config['filter_fields']))
		{
			$this->filter_fields = $config['filter_fields'];
		}

		if (empty($this->context))
		{
			$input = JFactory::getApplication()->input;
			$page = $input->get('page', 'global');

			$this->context = strtolower($this->option . '.' . $this->getName() . '.' . $page);
		}
	}

	/**
	 * Method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since   3.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm('com_kinoarhiv.reviews', 'reviews', array('control' => 'form', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   1.6
	 */
	protected function loadFormData()
	{
		$app    = JFactory::getApplication();
		$user   = JFactory::getUser();
		$id     = $app->input->get('id', 0, 'int');
		$itemid = $app->input->get('Itemid', 0, 'int');
		$data   = $app->getUserState('com_kinoarhiv.album.' . $id . '.user.' . $user->get('id'));

		if (empty($data))
		{
			$data['Itemid'] = $itemid;
			$data['id'] = $id;
		}

		return $data;
	}

	/**
	 * Get a movie item object
	 *
	 * @return  object|boolean
	 *
	 * @since   3.0
	 */
	public function getData()
	{
		$db          = $this->getDbo();
		$app         = JFactory::getApplication();
		$user        = JFactory::getUser();
		$lang        = JFactory::getLanguage();
		$groups      = implode(',', $user->getAuthorisedViewLevels());
		$params      = JComponentHelper::getParams('com_kinoarhiv');
		$id          = $app->input->get('id', 0, 'int');
		$langQueryIN = 'language IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ')';

		$query = $db->getQuery(true);

		$query->select("a.id, a.title, a.alias, a.fs_alias, DATE_FORMAT(a.year, '%Y') AS year, "
			. "a.length, a.isrc, a.desc, a.rate, a.rate_sum, a.covers_path, a.covers_path_www, a.cover_filename, "
			. "a.tracks_path, a.tracks_path_www, a.tracks_preview_path, a.buy_urls, a.created_by, a.metakey, a.metadesc, "
			. "a.attribs, a.state, a.metadata, "
			. "DATE_FORMAT(a.created, '%Y-%m-%d') AS created, DATE_FORMAT(a.modified, '%Y-%m-%d') AS modified, "
			. "(SELECT COUNT(album_id) FROM " . $db->quoteName('#__ka_user_votes_albums') . " WHERE album_id = a.id) AS total_votes"
		)
			->from($db->quoteName('#__ka_music_albums', 'a'));

		if (!$user->get('guest'))
		{
			$query->select('u.favorite, u.favorite_added')
				->join('LEFT', $db->quoteName('#__ka_user_marked_albums', 'u') . ' ON u.uid = ' . $user->get('id') . ' AND u.album_id = a.id');

			$query->select('v.vote AS my_vote, v._datetime')
				->join('LEFT', $db->quoteName('#__ka_user_votes_albums', 'v') . ' ON v.album_id = a.id AND v.uid = ' . $user->get('id'));
		}

		$query->select('user.name AS username')
			->join('LEFT', $db->quoteName('#__users', 'user') . ' ON user.id = a.created_by')
			->where('a.id = ' . (int) $id . ' AND a.state = 1 AND access IN (' . $groups . ') AND ' . $langQueryIN);

		$db->setQuery($query);

		try
		{
			$result = $db->loadObject();

			if (empty($result))
			{
				return (object) array();
			}
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());
			KAComponentHelper::eventLog($e->getMessage());

			return false;
		}

		if (isset($result->attribs))
		{
			$result->attribs = json_decode($result->attribs);
		}

		// Get tracks for albums
		$query = $db->getQuery(true)
			->select(
				$db->quoteName(
					array(
						't.id', 't.album_id', 't.artist_id', 't.title', 't.year', 't.composer', 't.publisher',
						't.performer', 't.label', 't.isrc', 't.length', 't.cd_number', 't.track_number', 't.filename',
						't.buy_url'
					)
				)
			)
			->from($db->quoteName('#__ka_music', 't'))
			->where('t.album_id = ' . (int) $id)
			->order('t.track_number ASC');

		$db->setQuery($query);

		try
		{
			$result->tracks = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());
			KAComponentHelper::eventLog($e->getMessage());

			return false;
		}

		// Countries
		/*$queryCountries = $db->getQuery(true)
			->select('c.id, c.name, c.code, t.ordering')
			->from($db->quoteName('#__ka_countries', 'c'))
			->join('LEFT', $db->quoteName('#__ka_rel_countries', 't') . ' ON t.country_id = c.id AND t.movie_id = ' . (int) $id);

			$subqueryCountries = $db->getQuery(true)
				->select('country_id')
				->from($db->quoteName('#__ka_rel_countries'))
				->where('movie_id = ' . (int) $id);

		$queryCountries->where('id IN (' . $subqueryCountries . ') AND state = 1')
			->order('ordering ASC');

		$db->setQuery($queryCountries);

		try
		{
			$result->countries = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			$result->countries = array();
			KAComponentHelper::eventLog($e->getMessage());
		}*/

		// Genres
		$queryGenres = $db->getQuery(true)
			->select('g.id, g.name, g.alias, t.ordering')
			->from($db->quoteName('#__ka_genres', 'g'))
			->leftJoin($db->quoteName('#__ka_music_rel_genres', 't') . ' ON t.genre_id = g.id AND t.item_id = ' . (int) $id);

			$subqueryGenres = $db->getQuery(true)
				->select($db->quoteName('genre_id'))
				->from($db->quoteName('#__ka_music_rel_genres'))
				->where($db->quoteName('type') . ' = 0')
				->where($db->quoteName('item_id') . ' = ' . (int) $id);

		$queryGenres->where('id IN (' . $subqueryGenres . ') AND state = 1 AND access IN (' . $groups . ') AND ' . $langQueryIN)
			->order('ordering ASC');

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

		// Cast and crew
		/*$careers = array();
		$queryCareer = $db->getQuery(true)
			->select($db->quoteName(array('id', 'title')))
			->from($db->quoteName('#__ka_names_career'))
			->where('is_mainpage = 1 AND is_amplua = 0')
			->order('ordering ASC');

		$db->setQuery($queryCareer);

		try
		{
			$_careers = $db->loadObjectList();

			foreach ($_careers as $career)
			{
				$careers[$career->id] = $career->title;
			}

			$queryCrew = $db->getQuery(true)
				->select('n.id, n.name, n.latin_name, n.alias, t.type, t.is_actors, t.is_directors, t.voice_artists')
				->from($db->quoteName('#__ka_names', 'n'))
				->join('LEFT', $db->quoteName('#__ka_rel_names', 't') . ' ON t.name_id = n.id AND t.movie_id = ' . (int) $id);

			$subqueryCrew = $db->getQuery(true)
				->select('name_id')
				->from($db->quoteName('#__ka_rel_names'))
				->where('movie_id = ' . (int) $id);

			$queryCrew->where('n.id IN (' . $subqueryCrew . ') AND state = 1 AND access IN (' . $groups . ') AND ' . $langQueryIN)
				->order('t.ordering ASC');

			$db->setQuery($queryCrew);
			$crew = $db->loadObjectList();

			$_result = array();

			foreach ($crew as $key => $value)
			{
				foreach (explode(',', $value->type) as $k => $type)
				{
					if (isset($careers[$type]) && $value->is_actors == 0 && $value->voice_artists == 0)
					{
						$_result['crew'][$type]['career']  = $careers[$type];
						$_result['crew'][$type]['items'][] = array(
							'id'        => $value->id,
							'name'      => !empty($value->name) ? $value->name : $value->latin_name,
							'alias'     => $value->alias,
							'directors' => $value->is_directors
						);
					}

					if (isset($careers[$type]) && $value->is_actors == 1 && $value->voice_artists == 0)
					{
						$_result['cast'][$type]['career']  = $careers[$type];
						$_result['cast'][$type]['items'][] = array(
							'id'    => $value->id,
							'name'  => !empty($value->name) ? $value->name : $value->latin_name,
							'alias' => $value->alias
						);
					}
				}
			}

			if (!empty($_result['crew']))
			{
				ksort($_result['crew']);

				foreach ($_result['crew'] as $row)
				{
					$row['total_items'] = count($row['items']);

					if ($row['total_items'] > 0)
					{
						$row['items'] = array_slice($row['items'], 0, $params->get('person_list_limit'));
					}

					$result->crew[] = $row;
				}
			}

			if (!empty($_result['cast']))
			{
				foreach ($_result['cast'] as $row)
				{
					$row['total_items'] = count($row['items']);

					if ($row['total_items'] > 0)
					{
						$row['items'] = array_slice($row['items'], 0, $params->get('person_list_limit'));
					}

					$result->cast[] = $row;
				}
			}
		}
		catch (RuntimeException $e)
		{
			KAComponentHelper::eventLog($e->getMessage());
		}*/

		// Release dates
		if ($params->get('releases_list_limit') > 0)
		{
			$queryReleases = $db->getQuery(true)
				->select('r.id, r.item_id, r.release_date, c.name AS country, v.company_name, media.title AS media_type')
				->from($db->quoteName('#__ka_releases', 'r'))
				->leftJoin($db->quoteName('#__ka_vendors', 'v') . ' ON v.id = r.vendor_id')
				->leftJoin($db->quoteName('#__ka_countries', 'c') . ' ON c.id = r.country_id')
				->leftJoin($db->quoteName('#__ka_media_types', 'media') . ' ON media.id = r.media_type')
				->where($db->quoteName('item_id') . ' = ' . (int) $id)
				->where($db->quoteName('item_type') . ' = 1')
				->where($db->quoteName('r.language') . ' IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ')')
				->order($db->quoteName('r.ordering') . ' ASC')
				->setLimit((int) $params->get('releases_list_limit'), 0);

			$db->setQuery($queryReleases);

			try
			{
				$result->releases = $db->loadObjectList();
			}
			catch (RuntimeException $e)
			{
				$result->releases = array();
				KAComponentHelper::eventLog($e->getMessage());
			}
		}
		else
		{
			$result->releases = array();
		}

		// Get Slider items
		/*if (($result->attribs->slider == '' && $params->get('slider') == 1) || $result->attribs->slider == 1)
		{
			$querySlider = $db->getQuery(true)
				->select($db->quoteName(array('id', 'filename', 'dimension')))
				->from($db->quoteName('#__ka_movies_gallery'))
				->where('movie_id = ' . (int) $id . ' AND state = 1 AND type = 3')
				->setLimit((int) $params->get('slider_max_item'), 0);

			$db->setQuery($querySlider);

			try
			{
				$result->slides = $db->loadObjectList();
			}
			catch (RuntimeException $e)
			{
				$result->slides = array();
				KAComponentHelper::eventLog($e->getMessage());
			}
		}*/

		return $result;
	}

	/**
	 * Get a short album info
	 *
	 * @return  object|boolean
	 *
	 * @since   3.0
	 */
	public function getAlbumData()
	{
		$db     = $this->getDbo();
		$app    = JFactory::getApplication();
		$user   = JFactory::getUser();
		$lang   = JFactory::getLanguage();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$id     = $app->input->get('id', 0, 'int');

		$query = $db->getQuery(true)
			->select("m.id, m.title, m.alias, m.fs_alias, DATE_FORMAT(m.year, '%Y') AS year, DATE_FORMAT(m.created, '%Y-%m-%d') AS created, " .
				"DATE_FORMAT(m.modified, '%Y-%m-%d') AS modified, m.metakey, m.metadesc, m.metadata, m.attribs, user.name AS username"
			)
			->from($db->quoteName('#__ka_music_albums', 'm'))
			->join('LEFT', $db->quoteName('#__users', 'user') . ' ON user.id = m.created_by')
			->where('m.id = ' . (int) $id . ' AND m.state = 1 AND m.access IN (' . $groups . ')')
			->where('m.language IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ')');

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

		$result->attribs = isset($result->attribs) ? json_decode($result->attribs) : "{}";

		return $result;
	}

	/**
	 * Get winned awards for movie
	 *
	 * @return  object|boolean
	 *
	 * @since   3.1
	 */
	public function getAwards()
	{
		$db  = $this->getDbo();
		$app = JFactory::getApplication();
		$id  = $app->input->get('id', 0, 'int');

		if ($id == 0)
		{
			return false;
		}

		$result = $this->getAlbumData();

		$query = $db->getQuery(true)
			->select('a.desc, a.year, aw.id, aw.title AS aw_title, aw.desc AS aw_desc')
			->from($db->quoteName('#__ka_rel_awards', 'a'))
			->join('LEFT', $db->quoteName('#__ka_awards', 'aw') . ' ON aw.id = a.award_id')
			->where($db->quoteName('type') . ' = 2')
			->where($db->quoteName('item_id') . ' = ' . (int) $id)
			->order($db->quoteName('year') . ' ASC');

		$db->setQuery($query);

		try
		{
			$result->awards = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			KAComponentHelper::eventLog($e->getMessage());

			return false;
		}

		return $result;
	}

	/**
	 * Get the list of albums and their tracks for movie
	 *
	 * @return  object|boolean
	 *
	 * @since   3.0
	 */
	public function getSoundtrackAlbums()
	{
		$db = $this->getDbo();
		$user = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$app = JFactory::getApplication();
		$movie_id = $app->input->get('id', 0, 'int');

		if ($movie_id == 0)
		{
			return false;
		}

		$result = $this->getAlbumData();

		// Get albums for movie
		$query = $db->getQuery(true)
			->select(
				$db->quoteName(
					array(
						'a.id', 'a.title', 'a.alias', 'a.fs_alias', 'a.composer', 'a.length', 'a.isrc',
						'a.rate', 'a.rate_sum', 'a.cover_filename', 'a.covers_path', 'a.covers_path_www',
						'a.tracks_path', 'a.tracks_preview_path', 'a.buy_url', 'a.attribs', 'n.name', 'n.latin_name'
					)
				)
			)
			->select($db->quoteName('a.year', 'date'))
			->select('YEAR(' . $db->quoteName('a.year') . ') AS ' . $db->quoteName('year'))
			->select($db->quoteName('n.id', 'artist_id'))
			->from($db->quoteName('#__ka_music_albums', 'a'));

			$subquery1 = $db->getQuery(true)
				->select($db->quoteName('album_id'))
				->from($db->quoteName('#__ka_music_rel_movies'))
				->where($db->quoteName('movie_id') . ' = ' . (int) $movie_id);

			$subquery2 = $db->getQuery(true)
				->select($db->quoteName('name_id'))
				->from($db->quoteName('#__ka_music_rel_composers'))
				->where('album_id = a.id');

		if (!$user->get('guest'))
		{
			$query->select('v.vote AS my_vote, v._datetime')
				->join('LEFT', $db->quoteName('#__ka_user_votes_albums', 'v') . ' ON v.album_id = a.id AND v.uid = ' . $user->get('id'));
		}

		$query->join('LEFT', $db->quoteName('#__ka_music_rel_movies', 'rel') . ' ON a.id = rel.album_id')
			->join('LEFT', $db->quoteName('#__ka_names', 'n') . ' ON n.id = (' . $subquery2 . ')')
			->where('a.id IN (' . $subquery1 . ') AND a.state = 1 AND a.access IN (' . $groups . ')')
			->order('rel.ordering ASC');

		$db->setQuery($query);
		$result->albums = $db->loadObjectList();

		// Get tracks for albums
		$query = $db->getQuery(true)
			->select(
				$db->quoteName(
					array(
						't.id', 't.album_id', 't.artist_id', 't.title', 't.year', 't.composer', 't.publisher',
						't.performer', 't.label', 't.isrc', 't.length', 't.cd_number', 't.track_number', 't.filename'
					)
				)
			)
			->from($db->quoteName('#__ka_music', 't'));

			$subquery = $db->getQuery(true)
				->select($db->quoteName('album_id'))
				->from($db->quoteName('#__ka_music_rel_movies'))
				->where($db->quoteName('movie_id') . ' = ' . (int) $movie_id);

		$query->where('t.album_id IN (' . $subquery . ')')
			->order('t.track_number ASC');

		$db->setQuery($query);

		try
		{
			$result->tracks = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			KAComponentHelper::eventLog($e->getMessage());

			return false;
		}

		return $result;
	}

	/**
	 * Method to cache the last query constructed.
	 *
	 * This method ensures that the query is constructed only once for a given state of the model.
	 *
	 * @return  JDatabaseQuery  A JDatabaseQuery object
	 *
	 * @since   3.0
	 */
	protected function _getListQuery()
	{
		static $lastStoreId;

		$currentStoreId = $this->getStoreId();

		if ($lastStoreId != $currentStoreId || empty($this->query))
		{
			$lastStoreId = $currentStoreId;
			$this->query = $this->getListQuery();
		}

		return $this->query;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   3.0
	 */
	public function getItems()
	{
		$store = $this->getStoreId();

		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		$query = $this->_getListQuery();

		try
		{
			$items = $this->_getList($query, $this->getStart(), $this->getState('list.limit'));
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		$this->cache[$store] = $items;

		return $this->cache[$store];
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
		$page   = $app->input->get('page', 'reviews', 'cmd');
		$filter = $app->input->get('dim_filter', '0', 'string');

		if ($page == 'wallpapers')
		{
			$query = $db->getQuery(true)
				->select('id, filename, dimension')
				->from($db->quoteName('#__ka_movies_gallery'))
				->where('movie_id = ' . (int) $id . ' AND state = 1 AND type = 1');

			if ($filter !== '0')
			{
				$query->where("dimension LIKE " . $db->quote($db->escape($filter, true) . "%", false));
			}
		}
		elseif ($page == 'posters')
		{
			$query = $db->getQuery(true)
				->select('id, filename, dimension')
				->from($db->quoteName('#__ka_movies_gallery'))
				->where('movie_id = ' . (int) $id . ' AND state = 1 AND type = 2');
		}
		elseif ($page == 'screenshots')
		{
			$query = $db->getQuery(true)
				->select('id, filename, dimension')
				->from($db->quoteName('#__ka_movies_gallery'))
				->where('movie_id = ' . (int) $id . ' AND state = 1 AND type = 3');
		}
		else
		{
			// Select reviews
			$query = $db->getQuery(true)
				->select('rev.id, rev.uid, rev.item_id, rev.review, rev.created, rev.type, rev.state, u.name, u.username')
				->from($db->quoteName('#__ka_reviews', 'rev'))
				->join('LEFT', $db->quoteName('#__users', 'u') . ' ON u.id = rev.uid')
				->where('rev.item_id = ' . (int) $id . ' AND item_type = 1 AND rev.state = 1 AND u.id != 0')
				->order('rev.id DESC');
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

		$app = JFactory::getApplication();
		$store = $this->getStoreId('getPagination');

		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		$limit = (int) $this->getState('list.limit') - (int) $this->getState('list.links');
		$page = new KAPagination($this->getTotal(), $this->getStart(), $limit);

		if ($app->input->get('review'))
		{
			$page->setAdditionalUrlParam('review', 0);
		}

		$this->cache[$store] = $page;

		return $this->cache[$store];
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
		$id .= ':' . $this->getState('list.start');
		$id .= ':' . $this->getState('list.limit');
		$id .= ':' . $this->getState('list.ordering');
		$id .= ':' . $this->getState('list.direction');

		return md5($this->context . ':' . $id);
	}

	/**
	 * Method to get the total number of items for the data set.
	 *
	 * @return  integer  The total number of items available in the data set.
	 *
	 * @since   3.0
	 */
	public function getTotal()
	{
		$store = $this->getStoreId('getTotal');

		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		$query = $this->_getListQuery();

		try
		{
			$total = (int) $this->_getListCount($query);
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		$this->cache[$store] = $total;

		return $this->cache[$store];
	}

	/**
	 * Method to get the starting number of items for the data set.
	 *
	 * @return  integer  The starting number of items available in the data set.
	 *
	 * @see JModelList
	 *
	 * @since   3.0
	 */
	public function getStart()
	{
		$store = $this->getStoreId('getstart');

		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		$start = $this->getState('list.start');
		$limit = $this->getState('list.limit');
		$total = $this->getTotal();

		if ($start > $total - $limit)
		{
			$start = max(0, (int) (ceil($total / $limit) - 1) * $limit);
		}

		$this->cache[$store] = $start;

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

			$value = $app->getUserStateFromRequest($this->context . '.limitstart', 'limitstart', 0, 'int');
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
	 * @see JModelList
	 *
	 * @since   3.0
	 */
	public function getUserStateFromRequest($key, $request, $default = null, $type = 'none', $resetPage = true)
	{
		$app          = JFactory::getApplication();
		$oldState     = $app->getUserState($key);
		$currentState = (!is_null($oldState)) ? $oldState : $default;
		$newState     = $app->input->get($request, null, $type);

		if (($currentState != $newState) && ($resetPage))
		{
			$app->input->set('limitstart', 0);
		}

		if ($newState !== null)
		{
			$app->setUserState($key, $newState);
		}
		else
		{
			$newState = $currentState;
		}

		return $newState;
	}
}
