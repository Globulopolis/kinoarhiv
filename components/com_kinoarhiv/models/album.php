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

use Joomla\String\StringHelper;

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
		$langQueryIN = 'language IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ')';

		$query = $db->getQuery(true);

		$query->select("a.id, a.title, a.alias, a.fs_alias, DATE_FORMAT(a.year, '%Y') AS year, "
			. "a.length, a.isrc, a.desc, a.rate, a.rate_sum, a.covers_path, a.covers_path_www, a.tracks_path, "
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
				->select('n.id, n.name, n.latin_name, n.alias, t.career_id')
				->from($db->quoteName('#__ka_names', 'n'))
				->leftJoin($db->quoteName('#__ka_music_rel_names', 't') . ' ON t.name_id = n.id AND t.item_id = ' . (int) $id . ' AND t.item_type = 0');

			$subqueryCrew = $db->getQuery(true)
				->select('name_id')
				->from($db->quoteName('#__ka_music_rel_names'))
				->where('item_id = ' . (int) $id);

			$queryCrew->where('n.id IN (' . $subqueryCrew . ') AND state = 1 AND access IN (' . $groups . ') AND ' . $langQueryIN)
				->order('t.ordering ASC');

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
		$db             = $this->getDbo();
		$app            = JFactory::getApplication();
		$user           = JFactory::getUser();
		$lang           = JFactory::getLanguage();
		$groups         = implode(',', $user->getAuthorisedViewLevels());
		$params         = JComponentHelper::getParams('com_kinoarhiv');
		$id             = $app->input->get('id', 0, 'int');
		$itemid         = $app->input->get('Itemid', 0, 'int');
		$throttleEnable = $params->get('throttle_image_enable', 0);
		$result         = $this->getAlbumData();
		$careers        = array();

		$query = $db->getQuery(true)
			->select('id, title')
			->from($db->quoteName('#__ka_names_career'))
			->order('ordering ASC');

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
				"n.id, n.name, n.latin_name, n.alias, n.fs_alias, n.gender, t.career_id, t.role, " .
				"ac.desc, g.filename AS url_photo"
			)
			->from($db->quoteName('#__ka_names', 'n'))
			->join('LEFT', $db->quoteName('#__ka_music_rel_names', 't') . ' ON t.name_id = n.id AND t.item_id = ' . (int) $id)
			->join('LEFT', $db->quoteName('#__ka_music_rel_names', 'ac') . ' ON ac.name_id = n.id AND ac.item_id = ' . (int) $id)
			->join('LEFT', $db->quoteName('#__ka_names_gallery', 'g') . ' ON g.name_id = n.id AND g.type = 3 AND g.frontpage = 1');

		$subqueryCrew = $db->getQuery(true)
			->select('name_id')
			->from($db->quoteName('#__ka_music_rel_names'))
			->where('item_id = ' . (int) $id);

		$queryCrew->where('n.id IN (' . $subqueryCrew . ') AND n.state = 1 AND n.access IN (' . $groups . ')')
			->where('n.language IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ')')
			->group('n.id')
			->order('t.ordering ASC');

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
echo '<pre>';
print_r($crew);
echo '</pre>';
		$_result = array('crew' => array(), 'cast' => array(), 'dub' => array());
		$_careersCrew = array();

		foreach ($crew as $key => $value)
		{
			foreach (explode(',', $value->type) as $k => $type)
			{
				// Process posters
				if ($throttleEnable == 0)
				{
					// Cast and crew photo
					$checkingPath = JPath::clean(
						$params->get('media_actor_photo_root') . '/' . $value->fs_alias . '/' . $value->id . '/photo/' . $value->url_photo
					);
					$noCover = ($value->gender == 0) ? 'no_name_cover_f' : 'no_name_cover_m';

					if (!is_file($checkingPath))
					{
						$value->poster = JUri::base() . 'media/com_kinoarhiv/images/themes/' . $params->get('ka_theme') . '/' . $noCover . '.png';
					}
					else
					{
						$value->fs_alias = rawurlencode($value->fs_alias);

						// This trick will remove double slash in URL if alias is empty.
						$value->fs_alias = empty($value->fs_alias) ? '' : $value->fs_alias . '/';

						if (StringHelper::substr($params->get('media_actor_photo_root_www'), 0, 1) == '/')
						{
							$value->poster = JUri::base() . StringHelper::substr($params->get('media_actor_photo_root_www'), 1) . '/'
								. $value->fs_alias . $value->id . '/photo/thumb_' . $value->url_photo;
						}
						else
						{
							$value->poster = $params->get('media_actor_photo_root_www') . '/' . $value->fs_alias
								. $value->id . '/photo/thumb_' . $value->url_photo;
						}
					}

					// Dub actors photo
					if (isset($careers[$type]) && $value->is_actors == 1 && $value->voice_artists == 0)
					{
						$checkingPath1 = JPath::clean(
							$params->get('media_actor_photo_root') . '/' . $value->dub_fs_alias . '/' .
							$value->dub_id . '/photo/' . $value->dub_url_photo
						);
						$noCover1 = ($value->dub_gender == 0) ? 'no_name_cover_f' : 'no_name_cover_m';

						if (!is_file($checkingPath1))
						{
							$value->dub_url_photo = JUri::base() . 'media/com_kinoarhiv/images/themes/' .
								$params->get('ka_theme') . '/' . $noCover1 . '.png';
						}
						else
						{
							$value->dub_fs_alias = rawurlencode($value->dub_fs_alias);

							// This trick will remove double slash in URL if alias is empty.
							$value->dub_fs_alias = empty($value->dub_fs_alias) ? '' : $value->dub_fs_alias . '/';

							if (StringHelper::substr($params->get('media_actor_photo_root_www'), 0, 1) == '/')
							{
								$value->dub_url_photo = JUri::base() . StringHelper::substr($params->get('media_actor_photo_root_www'), 1) . '/'
									. $value->dub_fs_alias . $value->dub_id . '/photo/thumb_' . $value->dub_url_photo;
							}
							else
							{
								$value->dub_url_photo = $params->get('media_actor_photo_root_www') . '/' . $value->dub_fs_alias
									. $value->dub_id . '/photo/thumb_' . $value->dub_url_photo;
							}
						}
					}
				}
				else
				{
					$value->poster = JRoute::_(
						'index.php?option=com_kinoarhiv&task=media.view&element=name&content=image&type=3&id=' . $value->id .
						'&fa=' . urlencode($value->fs_alias) . '&fn=' . $value->url_photo . '&format=raw&Itemid=' . $itemid .
						'&thumbnail=1&gender=' . $value->gender
					);

					if (isset($careers[$type]) && $value->is_actors == 1 && $value->voice_artists == 0)
					{
						$value->dub_url_photo = JRoute::_(
							'index.php?option=com_kinoarhiv&task=media.view&element=name&content=image&type=3&id=' . $value->dub_id .
							'&fa=' . urlencode($value->dub_fs_alias) . '&fn=' . $value->dub_url_photo . '&format=raw&Itemid=' . $itemid .
							'&thumbnail=1&gender=' . $value->dub_gender
						);
					}
				}

				// Crew
				if (isset($careers[$type]) && $value->is_actors == 0 && $value->voice_artists == 0)
				{
					$_result['crew'][$type]['career'] = $careers[$type];
					$_careersCrew[] = $careers[$type];
					$_result['crew'][$type]['items'][] = array(
						'id'         => $value->id,
						'name'       => $value->name,
						'latin_name' => $value->latin_name,
						'alias'      => $value->alias,
						'poster'     => $value->poster,
						'gender'     => $value->gender,
						'role'       => $value->role,
						'desc'       => $value->desc
					);
				}

				// Cast
				if (isset($careers[$type]) && $value->is_actors == 1 && $value->voice_artists == 0)
				{
					$_result['cast'][$type]['career'] = $careers[$type];

					// Only one value for actors. So we don't need to build an array of items
					$_careersCast = $careers[$type];
					$_result['cast'][$type]['items'][] = array(
						'id'             => $value->id,
						'name'           => $value->name,
						'latin_name'     => $value->latin_name,
						'alias'          => $value->alias,
						'poster'         => $value->poster,
						'gender'         => $value->gender,
						'role'           => $value->role,
						'dub_id'         => $value->dub_id,
						'dub_name'       => $value->dub_name,
						'dub_latin_name' => $value->dub_latin_name,
						'dub_alias'      => $value->dub_alias,
						'dub_url_photo'  => $value->dub_url_photo,
						'dub_gender'     => $value->dub_gender,
						'dub_role'       => $value->dub_role,
						'desc'           => $value->desc
					);
				}

				// Dub
				if (isset($careers[$type]) && $value->is_actors == 1 && $value->voice_artists == 1)
				{
					$_result['dub'][$type]['career'] = $careers[$type];
					$_careersDub = $careers[$type];
					$_result['dub'][$type]['items'][] = array(
						'id'         => $value->id,
						'name'       => $value->name,
						'latin_name' => $value->latin_name,
						'alias'      => $value->alias,
						'poster'     => $value->poster,
						'gender'     => $value->gender,
						'role'       => $value->dub_role,
						'desc'       => $value->desc
					);
				}
			}
		}

		ksort($_result['crew']);
		$result->crew = $_result['crew'];
		$result->cast = $_result['cast'];
		$result->dub  = $_result['dub'];

		// Create a new array with name career, remove duplicate items and sort it
		$newCareers = array_unique($_careersCrew);

		foreach ($newCareers as $row)
		{
			$result->careers['crew'][] = $row;
		}

		$result->careers['cast'] = isset($_careersCast) ? $_careersCast : '';
		$result->careers['dub']  = isset($_careersDub) ? $_careersDub : '';

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
		$page   = $app->input->get('page', 'reviews');
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
