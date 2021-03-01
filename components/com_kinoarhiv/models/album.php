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

use Joomla\Utilities\ArrayHelper;

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
	 * @var    JDatabaseQuery[]
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
	 * @since   3.1
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
		$langQueryIN = $db->quoteName('language') . ' IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ')';

		$query = $db->getQuery(true);

		$query->select("a.id, a.title, a.alias, a.fs_alias, DATE_FORMAT(a.year, '%Y') AS year, "
			. "a.length, a.desc, a.rate, a.rate_sum, a.covers_path, a.covers_path_www, a.tracks_path, "
			. "a.tracks_path_www, a.tracks_preview_path, a.buy_urls, a.created_by, a.metakey, a.metadesc, "
			. "a.attribs, a.state, a.metadata, "
			. "DATE_FORMAT(a.created, '%Y-%m-%d') AS created, DATE_FORMAT(a.modified, '%Y-%m-%d') AS modified, "
			. "(SELECT COUNT(album_id) FROM " . $db->quoteName('#__ka_user_votes_albums') . " WHERE album_id = a.id) AS total_votes"
		);
		$query->from($db->quoteName('#__ka_music_albums', 'a'));

		// Join over gallery item
		$query->select($db->quoteName(array('g.filename', 'g.dimension')))
			->leftJoin($db->quoteName('#__ka_music_albums_gallery', 'g') . ' ON g.item_id = a.id AND g.type = 1 AND g.frontpage = 1 AND g.state = 1');

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
				$this->setError(JText::_('COM_KA_NO_DATA'));

				return false;
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

		// Genres
		$queryGenres = $db->getQuery(true)
			->select($db->quoteName(array('g.id', 'g.name', 'g.alias')))
			->from($db->quoteName('#__ka_music_rel_genres', 'rel'))
			->leftJoin($db->quoteName('#__ka_genres', 'g') . ' ON g.id = rel.genre_id')
			->where($db->quoteName('rel.type') . ' = 0')
			->where($db->quoteName('rel.item_id') . ' = ' . (int) $id)
			->order($db->quoteName('ordering') . ' ASC');

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

		// Labels(vendors)
		$queryVendors = $db->getQuery(true)
			->select($db->quoteName(array('v.id', 'v.company_name', 'v.company_name_alias')))
			->from($db->quoteName('#__ka_music_rel_vendors', 'rel'))
			->leftJoin($db->quoteName('#__ka_vendors', 'v') . ' ON v.id = rel.vendor_id AND rel.item_id = ' . (int) $id);

		$queryVendors->where('rel.item_type = 0 AND v.state = 1')
			->order('rel.ordering ASC');

		$db->setQuery($queryVendors);

		try
		{
			$result->vendors = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			$result->vendors = array();
			KAComponentHelper::eventLog($e->getMessage());
		}

		// Crew
		$careers = array();
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
				->select($db->quoteName(array('rel.career_id', 'n.id', 'n.name', 'n.latin_name', 'n.alias')))
				->from($db->quoteName('#__ka_music_rel_names', 'rel'))
				->leftJoin($db->quoteName('#__ka_names', 'n') . ' ON n.id = rel.name_id')
				->where('rel.item_id = 1 AND rel.item_type = 0')
				->order('rel.ordering ASC');

			$db->setQuery($queryCrew);
			$crew = $db->loadObjectList();

			$_result = array();

			foreach ($crew as $key => $value)
			{
				$careerID = $value->career_id;

				if (!empty($careers[$careerID]))
				{
					$_result['crew'][$careerID]['career']  = $careers[$careerID];
					$_result['crew'][$careerID]['items'][] = array(
						'id'    => $value->id,
						'name'  => KAContentHelper::formatItemTitle($value->name, $value->latin_name),
						'alias' => $value->alias,
					);
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
		}
		catch (RuntimeException $e)
		{
			echo $e->getMessage();
			KAComponentHelper::eventLog($e->getMessage());
		}

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
				->setLimit((int) $params->get('releases_list_limit'));

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

		$tracks = $this->getTracks($id);

		if ($tracks)
		{
			$result->tracks   = $tracks->tracks;
			$result->playlist = $tracks->playlist;
		}
		else
		{
			$result->tracks = array();
			$result->playlist = array();
		}

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
			->select(
				$db->quoteName(
					array(
						'm.id', 'm.title', 'm.alias', 'm.fs_alias', 'm.covers_path', 'm.covers_path_www',
						'm.tracks_path', 'm.tracks_path_www', 'm.tracks_preview_path', 'm.metakey', 'm.metadesc',
						'm.metadata', 'm.attribs'
					)
				)
			)
			->select("DATE_FORMAT(m.year, '%Y') AS year")
			->select("DATE_FORMAT(m.created, '%Y-%m-%d') AS created")
			->select("DATE_FORMAT(m.modified, '%Y-%m-%d') AS modified")
			->select($db->quoteName('user.name', 'username'))
			->from($db->quoteName('#__ka_music_albums', 'm'))
			->leftJoin($db->quoteName('#__users', 'user') . ' ON user.id = m.created_by')
			->where($db->quoteName('m.id') . ' = ' . (int) $id)
			->where($db->quoteName('m.state') . ' = 1')
			->where($db->quoteName('m.access') . ' IN (' . $groups . ')')
			->where($db->quoteName('m.language') . ' IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ')');

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
	 * Get winned awards for album
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
	 * Get covers for album
	 *
	 * @return  object|boolean
	 *
	 * @since   3.1
	 */
	public function getCovers()
	{
		$db  = $this->getDbo();
		$app = JFactory::getApplication();
		$id  = $app->input->get('id', 0, 'int');

		if ($id == 0)
		{
			$this->setError('Wrong ID.');

			return false;
		}

		$query = $db->getQuery(true)
			->select($db->quoteName(array('id', 'filename', 'dimension', 'type')))
			->from($db->quoteName('#__ka_music_albums_gallery'))
			->where($db->quoteName('state') . ' = 1')
			->where($db->quoteName('item_id') . ' = ' . (int) $id)
			->order($db->quoteName('type') . ' ASC');

		$db->setQuery($query);

		// Get album data
		try
		{
			$result = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			$this->setError('');

			return false;
		}

		return $result;
	}

	/**
	 * Method to get crew for album
	 *
	 * @return  object|boolean
	 *
	 * @since   3.1
	 */
	public function getCrew()
	{
		$db      = $this->getDbo();
		$app     = JFactory::getApplication();
		$user    = JFactory::getUser();
		$lang    = JFactory::getLanguage();
		$groups  = implode(',', $user->getAuthorisedViewLevels());
		$params  = JComponentHelper::getParams('com_kinoarhiv');
		$id      = $app->input->get('id', 0, 'int');
		$result  = $this->getAlbumData();
		$careers = array();

		$query = $db->getQuery(true)
			->select($db->quoteName(array('id', 'title')))
			->from($db->quoteName('#__ka_names_career'))
			->order($db->quoteName('ordering') . ' ASC');

		$db->setQuery($query);

		try
		{
			$_careers = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			$this->setError('');
			KAComponentHelper::eventLog($e->getMessage());

			return false;
		}

		foreach ($_careers as $career)
		{
			$careers[$career->id] = $career->title;
		}

		$queryCrew = $db->getQuery(true)
			->select(
				$db->quoteName(
					array(
						'n.id', 'n.name', 'n.latin_name', 'n.alias', 'n.fs_alias', 'n.gender', 't.career_id', 't.role',
						'ac.desc'
					)
				)
			)
			->select($db->quoteName(array('g.filename', 'g.dimension')))
			->from($db->quoteName('#__ka_names', 'n'))
			->leftJoin($db->quoteName('#__ka_music_rel_names', 't') . ' ON t.name_id = n.id AND t.item_id = ' . (int) $id)
			->leftJoin($db->quoteName('#__ka_music_rel_names', 'ac') . ' ON ac.name_id = n.id AND ac.item_id = ' . (int) $id)
			->leftJoin($db->quoteName('#__ka_names_gallery', 'g') . ' ON g.name_id = n.id AND g.type = 3 AND g.frontpage = 1');

		$subqueryCrew = $db->getQuery(true)
			->select($db->quoteName('name_id'))
			->from($db->quoteName('#__ka_music_rel_names'))
			->where($db->quoteName('item_id') . ' = ' . (int) $id);

		$queryCrew->where($db->quoteName('n.id') . ' IN (' . $subqueryCrew . ')')
			->where($db->quoteName('n.state') . ' = 1')
			->where($db->quoteName('n.access') . ' IN (' . $groups . ')')
			->where($db->quoteName('n.language') . ' IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ')')
			->group($db->quoteName('n.id'))
			->order($db->quoteName('t.ordering') . ' ASC');

		$db->setQuery($queryCrew);

		try
		{
			$crew = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			$this->setError('');
			KAComponentHelper::eventLog($e->getMessage());

			return false;
		}

		$result->careers = array();
		$result->crew = array();

		foreach ($crew as $value)
		{
			$careerID = $value->career_id;

			// Crew
			if (isset($careers[$careerID]))
			{
				$result->careers[] = $careers[$careerID];
				$result->crew[$careerID]['career'] = $careers[$careerID];
				$result->crew[$careerID]['items'][] = array(
					'id'         => $value->id,
					'name'       => $value->name,
					'latin_name' => $value->latin_name,
					'alias'      => $value->alias,
					'gender'     => $value->gender,
					'role'       => $value->role,
					'desc'       => $value->desc,
					'photo'      => KAContentHelper::getPersonPhoto($value, $params)
				);
			}
		}

		// Remove duplicate items.
		$result->careers = array_unique($result->careers);

		return $result;
	}

	/**
	 * Get tracklist for album.
	 *
	 * @param   integer  $id  Album ID.
	 *
	 * @return  object|boolean
	 *
	 * @since   3.1
	 */
	public function getTracks($id)
	{
		$db = JFactory::getDbo();
		$result = (object) array('tracks' => array(), 'playlist' => array());

		$query = $db->getQuery(true)
			->select(
				$db->quoteName(
					array(
						't.id', 't.title', 't.year', 't.publisher', 't.label', 't.isrc', 't.length', 't.cd_number',
						't.track_number', 't.filename', 't.buy_url', 'a.tracks_path', 'a.tracks_path_www',
						'a.tracks_preview_path', 'rel.album_id', 'rel_names.name_id', 'performer.name',
						'performer.latin_name'
					)
				)
			)
			->select($db->quoteName('a.fs_alias', 'album_fs_alias'))
			->from($db->quoteName('#__ka_music', 't'));

		$query->leftJoin($db->quoteName('#__ka_music_rel_albums', 'rel') . ' ON rel.track_id = t.id')
			->leftJoin($db->quoteName('#__ka_music_rel_names', 'rel_names') . ' ON rel_names.item_id = t.id AND rel_names.item_type = 1 AND rel_names.ordering = 0')
			->leftJoin($db->quoteName('#__ka_music_albums', 'a') . ' ON a.id = rel.album_id')
			->leftJoin($db->quoteName('#__ka_names', 'performer') . ' ON performer.id = rel_names.name_id')
			->where($db->quoteName('rel.album_id') . ' = ' . (int) $id)
			->group($db->quoteName('t.id'))
			->order($db->quoteName('t.track_number') . ' ASC');

		$db->setQuery($query);

		try
		{
			$tracks = $db->loadObjectList();

			if (!empty($tracks))
			{
				foreach ($tracks as $key => $track)
				{
					// TODO Need to change this.
					$src = $track->tracks_path_www . '/' . $track->filename;

					$_track = array(
						'src' => $src,
						'performed_by'    => KAContentHelper::formatItemTitle($track->name, $track->latin_name),
						'performed_by_id' => $track->name_id
					);
					$result->tracks[$key] = (object) array_merge($_track, ArrayHelper::fromObject($track));

					$result->playlist[$key] = array(
						'id'    => $track->id,
						'src'   => $src,
						'title' => $track->title
					);
				}
			}
		}
		catch (RuntimeException $e)
		{
			$this->setError('');
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
		$app = JFactory::getApplication();
		$db  = $this->getDbo();
		$id  = $app->input->get('id', 0, 'int');

		// Select reviews
		return $db->getQuery(true)
			->select('rev.id, rev.uid, rev.item_id, rev.review, rev.created, rev.type, rev.state, u.name, u.username')
			->from($db->quoteName('#__ka_reviews', 'rev'))
			->join('LEFT', $db->quoteName('#__users', 'u') . ' ON u.id = rev.uid')
			->where('rev.item_id = ' . (int) $id . ' AND item_type = 1 AND rev.state = 1 AND u.id != 0')
			->order('rev.id DESC');
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
