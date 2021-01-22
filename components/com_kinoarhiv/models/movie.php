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
 * Movie item class
 *
 * @since  3.0
 */
class KinoarhivModelMovie extends JModelForm
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
		$app  = JFactory::getApplication();
		$user = JFactory::getUser();
		$id   = $app->input->get('id', 0, 'int');
		$view = $app->input->getCmd('return', 'movie');

		// Return review form data if error occured while sending form.
		return $app->getUserState('com_kinoarhiv.' . $view . '.reviews.' . $id . '_user_' . $user->get('id') . 'edit');
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

		$query->select("m.id, m.parent_id, m.title, m.alias, m.fs_alias, m.plot, m.desc, m.known, m.slogan, m.budget, "
			. "m.age_restrict, m.ua_rate, m.mpaa, m.rate_loc, m.rate_sum_loc, m.imdb_votesum, m.imdb_votes, "
			. "m.imdb_id, m.kp_votesum, m.kp_votes, m.kp_id, m.rate_fc, m.rottentm_id, m.metacritics, "
			. "m.metacritics_id, m.myshows_votesum, m.myshows_votes, m.myshows_id, m.rate_custom, m.urls, m.buy_urls, "
			. "m.length, m.year, m.created_by, m.metakey, m.metadesc, m.attribs, m.state, m.metadata, "
			. "DATE_FORMAT(m.created, '%Y-%m-%d') AS created, DATE_FORMAT(m.modified, '%Y-%m-%d') AS modified, "
			. "(SELECT COUNT(movie_id) FROM " . $db->quoteName('#__ka_user_votes_movies') . " WHERE movie_id = m.id) AS total_votes"
		)
			->from($db->quoteName('#__ka_movies', 'm'));

		// Join over gallery item
		$query->select($db->quoteName('g.filename'))
			->join('LEFT', $db->quoteName('#__ka_movies_gallery', 'g') . ' ON g.movie_id = m.id AND g.type = 2 AND g.frontpage = 1 AND g.state = 1');

		if (!$user->get('guest'))
		{
			$query->select($db->quoteName(array('u.favorite', 'u.watched')))
				->join('LEFT', $db->quoteName('#__ka_user_marked_movies', 'u') . ' ON u.uid = ' . $user->get('id') . ' AND u.movie_id = m.id');

			$query->select('v.vote AS my_vote, v._datetime')
				->join('LEFT', $db->quoteName('#__ka_user_votes_movies', 'v') . ' ON v.movie_id = m.id AND v.uid = ' . $user->get('id'));
		}

		$query->select('user.name AS username')
			->join('LEFT', $db->quoteName('#__users', 'user') . ' ON user.id = m.created_by')
			->where('m.id = ' . (int) $id . ' AND m.state = 1 AND access IN (' . $groups . ') AND ' . $langQueryIN);

		$db->setQuery($query);

		try
		{
			$result = $db->loadObject();

			if (empty($result))
			{
				$this->setError(JText::_('COM_KA_MOVIE_NOT_FOUND'));

				return false;
			}
		}
		catch (RuntimeException $e)
		{
			// TODO Переделать это г*но.
			$this->setError('');
			KAComponentHelper::eventLog($e->getMessage());

			return false;
		}

		if (isset($result->attribs))
		{
			$result->attribs = json_decode($result->attribs);

			// Get tags
			if ($result->attribs->show_tags == 1)
			{
				$tags = new JHelperTags;
				$tags->getItemTags('com_kinoarhiv.movie', $result->id);
				$result->tags = $tags;
			}
		}

		// Countries
		$queryCountries = $db->getQuery(true)
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
		}

		// Genres
		$queryGenres = $db->getQuery(true)
			->select('g.id, g.name, g.alias, t.ordering')
			->from($db->quoteName('#__ka_genres', 'g'))
			->join('LEFT', $db->quoteName('#__ka_rel_genres', 't') . ' ON t.genre_id = g.id AND t.movie_id = ' . (int) $id);

			$subqueryGenres = $db->getQuery(true)
				->select('genre_id')
				->from($db->quoteName('#__ka_rel_genres'))
				->where('movie_id = ' . (int) $id);

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
		}

		// Premiere dates
		if ($params->get('premieres_list_limit') > 0)
		{
			$queryPremieres = $db->getQuery(true)
				->select('p.id, p.vendor_id, p.premiere_date, p.country_id, p.info, c.name AS country, v.company_name')
				->from($db->quoteName('#__ka_premieres', 'p'))
				->join('LEFT', $db->quoteName('#__ka_vendors', 'v') . ' ON v.id = p.vendor_id')
				->join('LEFT', $db->quoteName('#__ka_countries', 'c') . ' ON c.id = p.country_id')
				->where('movie_id = ' . (int) $id . ' AND p.language IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ')')
				->order('p.ordering ASC')
				->setLimit((int) $params->get('premieres_list_limit'), 0);

			$db->setQuery($queryPremieres);

			try
			{
				$result->premieres = $db->loadObjectList();
			}
			catch (RuntimeException $e)
			{
				$result->premieres = array();
				KAComponentHelper::eventLog($e->getMessage());
			}
		}
		else
		{
			$result->premieres = array();
		}

		// Release dates
		if ($params->get('releases_list_limit') > 0)
		{
			$queryReleases = $db->getQuery(true)
				->select('r.id, r.item_id, r.release_date, r.country_id, c.name AS country, v.company_name, media.title AS media_type')
				->from($db->quoteName('#__ka_releases', 'r'))
				->join('LEFT', $db->quoteName('#__ka_vendors', 'v') . ' ON v.id = r.vendor_id')
				->join('LEFT', $db->quoteName('#__ka_countries', 'c') . ' ON c.id = r.country_id')
				->join('LEFT', $db->quoteName('#__ka_media_types', 'media') . ' ON media.id = r.media_type')
				->where('item_id = ' . (int) $id . ' AND r.language IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ')')
				->order('r.ordering ASC')
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

		$result->trailer = ($params->get('watch_trailer') == 1) ? $this->getTrailer($id, 'trailer') : (object) array();
		$result->movie = ($params->get('watch_movie') == 1) ? $this->getTrailer($id, 'movie') : (object) array();

		// Get Slider items
		if (($result->attribs->slider == '' && $params->get('slider') == 1) || $result->attribs->slider == 1)
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
		}

		return $result;
	}

	/**
	 * Get a short movie info
	 *
	 * @return  object|boolean
	 *
	 * @since   3.0
	 */
	public function getMovieData()
	{
		$db     = $this->getDbo();
		$app    = JFactory::getApplication();
		$user   = JFactory::getUser();
		$lang   = JFactory::getLanguage();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$id     = $app->input->get('id', 0, 'int');

		$query = $db->getQuery(true)
			->select("m.id, m.title, m.alias, m.fs_alias, m.year, DATE_FORMAT(m.created, '%Y-%m-%d') AS created, " .
				"DATE_FORMAT(m.modified, '%Y-%m-%d') AS modified, m.metakey, m.metadesc, m.metadata, m.attribs, user.name AS username"
			)
			->from($db->quoteName('#__ka_movies', 'm'))
			->join('LEFT', $db->quoteName('#__users', 'user') . ' ON user.id = m.created_by');

		// Join over favorited
		if (!$user->get('guest'))
		{
			$query->select($db->quoteName(array('u.favorite', 'u.watched')));
			$query->leftJoin($db->quoteName('#__ka_user_marked_movies', 'u') . ' ON u.uid = ' . $user->get('id') . ' AND u.movie_id = m.id');
		}

		$query->where('m.id = ' . (int) $id . ' AND m.state = 1 AND m.access IN (' . $groups . ')')
			->where('m.language IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ')');

		$db->setQuery($query);

		try
		{
			$result = $db->loadObject();

			if (empty($result))
			{
				$this->setError('');
				KAComponentHelper::eventLog(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'));

				return false;
			}
		}
		catch (RuntimeException $e)
		{
			$this->setError('');
			KAComponentHelper::eventLog($e->getMessage());

			return false;
		}

		$result->attribs = isset($result->attribs) ? json_decode($result->attribs) : "{}";

		return $result;
	}

	/**
	 * Method to get cast and crew for movie
	 *
	 * @return  object|boolean
	 *
	 * @since   3.0
	 */
	public function getCast()
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
		$result         = $this->getMovieData();
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
			->select("n.id, n.name, n.latin_name, n.alias, n.fs_alias, n.gender, t.type, t.role, t.is_actors, " .
				"t.voice_artists, d.id AS dub_id, d.name AS dub_name, d.latin_name AS dub_latin_name, " .
				"d.alias AS dub_alias, d.fs_alias AS dub_fs_alias, d.gender AS dub_gender, " .
				"GROUP_CONCAT(r.role SEPARATOR ', ') AS dub_role, ac.desc, g.filename AS url_photo, " .
				"dg.filename AS dub_url_photo"
			)
			->from($db->quoteName('#__ka_names', 'n'))
			->join('LEFT', $db->quoteName('#__ka_rel_names', 't') . ' ON t.name_id = n.id AND t.movie_id = ' . (int) $id)
			->join('LEFT', $db->quoteName('#__ka_names', 'd') . ' ON d.id = t.dub_id AND d.state = 1 AND d.access IN (' . $groups . ') AND d.language IN (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')')
			->join('LEFT', $db->quoteName('#__ka_rel_names', 'r') . ' ON r.dub_id = n.id AND r.movie_id = ' . (int) $id)
			->join('LEFT', $db->quoteName('#__ka_rel_names', 'ac') . ' ON ac.name_id = n.id AND ac.movie_id = ' . (int) $id)
			->join('LEFT', $db->quoteName('#__ka_names_gallery', 'g') . ' ON g.name_id = n.id AND g.type = 3 AND g.frontpage = 1')
			->join('LEFT', $db->quoteName('#__ka_names_gallery', 'dg') . ' ON dg.name_id = d.id AND dg.type = 3 AND dg.frontpage = 1');

			$subqueryCrew = $db->getQuery(true)
				->select('name_id')
				->from($db->quoteName('#__ka_rel_names'))
				->where('movie_id = ' . (int) $id);

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
		$newCareers = array_unique($_careersCrew, SORT_STRING);

		foreach ($newCareers as $row)
		{
			$result->careers['crew'][] = $row;
		}

		$result->careers['cast'] = isset($_careersCast) ? $_careersCast : '';
		$result->careers['dub']  = isset($_careersDub) ? $_careersDub : '';

		return $result;
	}

	/**
	 * Method to get trailers for movie
	 *
	 * @param   integer  $id  Movie ID
	 *
	 * @return  object|boolean
	 *
	 * @since   3.0
	 */
	public function getTrailers($id = null)
	{
		jimport('joomla.filesystem.file');

		$db                   = $this->getDbo();
		$app                  = JFactory::getApplication();
		$user                 = JFactory::getUser();
		$lang                 = JFactory::getLanguage();
		$groups               = implode(',', $user->getAuthorisedViewLevels());
		$params               = JComponentHelper::getParams('com_kinoarhiv');
		$id                   = is_null($id) ? $app->input->get('id', null, 'int') : $id;
		$itemid               = $app->input->get('Itemid', 0, 'int');
		$result               = $this->getMovieData();
		$result->player_width = $params->get('player_width');
		$throttleImgEnable    = $params->get('throttle_image_enable', 0);
		$throttleVideoEnable  = $params->get('throttle_video_enable', 0);
		$allowedFormats       = array('mp4', 'webm', 'ogv', 'flv');

		$query = $db->getQuery(true)
			->select(
				$db->quoteName(
					array('tr.id', 'tr.title', 'tr.embed_code', 'tr.screenshot', 'tr.urls', 'tr.resolution', 'tr.dar',
						'tr.duration', 'tr.video', 'tr.subtitles', 'tr.chapters', 'tr.is_movie', 'm.alias', 'm.fs_alias'
					)
				)
			)
			->from($db->quoteName('#__ka_trailers', 'tr'))
			->join('LEFT', $db->quoteName('#__ka_movies', 'm') . ' ON m.id = tr.movie_id')
			->where('tr.movie_id = ' . (int) $id . ' AND tr.state = 1 AND tr.access IN (' . $groups . ')')
			->where('tr.language IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ')');

		$db->setQuery($query);

		try
		{
			$result->trailers = $db->loadObjectList();

			if (empty($result->trailers))
			{
				return $result;
			}
		}
		catch (RuntimeException $e)
		{
			$this->setError('');
			KAComponentHelper::eventLog($e->getMessage());

			return false;
		}

		foreach ($result->trailers as $key => $value)
		{
			// Get the data from urls
			if (!empty($value->urls))
			{
				$urlsArr = explode("\n", $value->urls);
				$value->path = '';

				if (count($urlsArr) > 0)
				{
					if ($throttleImgEnable == 0)
					{
						$checkingPath = JPath::clean(
							$params->get('media_trailers_root') . '/' . $value->fs_alias . '/' . $id . '/' . $value->screenshot
						);

						if (!is_file($checkingPath))
						{
							$value->screenshot = JUri::base() . 'media/com_kinoarhiv/images/video_off.png';
						}
						else
						{
							$value->fs_alias = rawurlencode($value->fs_alias);

							if (StringHelper::substr($params->get('media_trailers_root_www'), 0, 1) == '/')
							{
								$value->screenshot = JUri::base() . StringHelper::substr($params->get('media_trailers_root_www'), 1) . '/'
									. $value->fs_alias . '/' . $id . '/' . $value->screenshot;
							}
							else
							{
								$value->screenshot = $params->get('media_trailers_root_www') . '/' . $value->fs_alias .
								'/' . $id . '/' . $value->screenshot;
							}
						}
					}
					else
					{
						$value->screenshot = JRoute::_(
							'index.php?option=com_kinoarhiv&task=media.view&element=trailer&content=image&id=' . $id .
							'&item_id=' . $value->id . '&fa=' . urlencode($value->fs_alias) . '&fn=' . $value->screenshot .
							'&format=raw&Itemid=' . $itemid
						);
					}

					$value->files['video']       = array();
					$value->files['subtitles']   = array();
					$value->files['chapters']    = array();
					$value->files['video_links'] = array();

					foreach ($urlsArr as $v)
					{
						if (preg_match('#\[(url="(?P<url>.+?)")?(\stype="(?P<type>.+?)")?(\splayer="(?P<player>.+?)")?(\sresolution="(?P<resolution>.+?)")?(\skind="(?P<kind>.+?)")?(\ssrclang="(?P<srclang>.+?)")?(\slabel="(?P<label>.+?)")?(\sdefault="(?P<default>.+?)")?\]#i', $v, $m))
						{
							if (isset($m['url']) && !empty($m['url']))
							{
								$url = $m['url'];
								$type = (isset($m['type']) && !empty($m['type'])) ? $m['type'] : '';

								if (isset($m['player']))
								{
									if (!empty($m['player']) && $m['player'] == 'true')
									{
										$inPlayer = true;
									}
									else
									{
										$inPlayer = false;
									}
								}
								else
								{
									$inPlayer = false;
								}

								$resolution = (isset($m['resolution']) && !empty($m['resolution'])) ? $m['resolution'] : '';
								$kind       = (isset($m['kind']) && !empty($m['kind'])) ? $m['kind'] : '';
								$srclang    = (isset($m['srclang']) && !empty($m['srclang'])) ? $m['srclang'] : '';
								$label      = (isset($m['label']) && !empty($m['label'])) ? $m['label'] : '';
								$default    = isset($m['default']) && !empty($m['default']);

								if (!empty($resolution))
								{
									$resolution = $m['resolution'];
								}
								else
								{
									if ($value->resolution != '')
									{
										$resolution = $value->resolution;
									}
									else
									{
										$resolution = '1280x720';
									}
								}

								$trailerResolution    = explode('x', $resolution);
								$trailerHeight        = $trailerResolution[1];
								$value->player_height = floor(($trailerHeight * $result->player_width) / $trailerResolution[0]);

								if ($kind == '')
								{
									if ($inPlayer === true)
									{
										$value->files['video'][] = array(
											'src'        => $url,
											'type'       => $type,
											'resolution' => $resolution
										);
									}
									else
									{
										$value->files['video_links'][] = array(
											'src'        => $url,
											'type'       => $type,
											'resolution' => $resolution
										);
									}
								}

								if ($kind == 'subtitles')
								{
									$value->files['subtitles'][] = array(
										'default'   => $default,
										'lang_code' => $srclang,
										'lang'      => $label,
										'file'      => $url
									);
								}

								if ($kind == 'chapters')
								{
									$value->files['chapters'] = array(
										'file' => $url
									);
								}
							}
						}
					}
				}
				else
				{
					$value->files['video'] = array();
				}
			}
			else
			{
				if ($throttleImgEnable == 0)
				{
					$checkingPath = JPath::clean(
						$params->get('media_trailers_root') . '/' . $value->fs_alias . '/' . $id . '/' . $value->screenshot
					);

					if (!is_file($checkingPath))
					{
						$value->screenshot = JUri::base() . 'media/com_kinoarhiv/images/video_off.png';
					}
					else
					{
						$value->fs_alias = rawurlencode($value->fs_alias);

						if (StringHelper::substr($params->get('media_trailers_root_www'), 0, 1) == '/')
						{
							$value->screenshot = JUri::base() . StringHelper::substr($params->get('media_trailers_root_www'), 1) . '/'
								. $value->fs_alias . '/' . $id . '/' . $value->screenshot;
						}
						else
						{
							$value->screenshot = $params->get('media_trailers_root_www') . '/' . $value->fs_alias .
							'/' . $id . '/' . $value->screenshot;
						}
					}
				}
				else
				{
					$value->screenshot = JRoute::_(
						'index.php?option=com_kinoarhiv&task=media.view&element=trailer&content=image&id=' . $id .
						'&item_id=' . $value->id . '&fa=' . urlencode($value->fs_alias) . '&fn=' . $value->screenshot .
						'&format=raw&Itemid=' . $itemid
					);
				}

				if ($throttleVideoEnable == 0)
				{
					$_fsAlias = rawurlencode($value->fs_alias);

					if (StringHelper::substr($params->get('media_trailers_root_www'), 0, 1) == '/')
					{
						// $value->path is an URL, $path is a root path to the files
						$value->path = JUri::base() . StringHelper::substr($params->get('media_trailers_root_www'), 1) . '/'
							. $_fsAlias . '/' . $id . '/';
						$path = JPATH_ROOT . '/' . StringHelper::substr($params->get('media_trailers_root_www'), 1)
							. '/' . $value->fs_alias . '/' . $id . '/';
					}
					else
					{
						$value->path = $params->get('media_trailers_root_www') . '/' . $_fsAlias . '/' . $id . '/';
						$path = JPATH_ROOT . '/' . $params->get('media_trailers_root_www') . '/' . $value->fs_alias . '/' . $id . '/';
					}
				}
				else
				{
					$value->path = 'index.php?option=com_kinoarhiv&task=media.view&element=trailer&id=' . $id .
						'&item_id=' . $value->id . '&fa=' . urlencode($value->fs_alias) . '&format=raw&Itemid=' . $itemid;
					$path = JPATH_ROOT . '/' . $params->get('media_trailers_root_www') . '/' . $value->fs_alias . '/' . $id . '/';
				}

				$value->files['video'] = json_decode($value->video, true);
				$value->files['video_links'] = array();

				if (count($value->files['video']) > 0)
				{
					foreach ($value->files['video'] as $i => $val)
					{
						if ($throttleVideoEnable == 0)
						{
							$value->files['video'][$i]['src'] = $value->path . $val['src'];

							// Check video extentions
							if (!in_array(strtolower(JFile::getExt($val['src'])), $allowedFormats))
							{
								$value->files['video_links'][] = $value->files['video'][$i];
								unset($value->files['video'][$i]);
							}
						}
						else
						{
							$value->files['video'][$i]['src'] = JRoute::_(
								$value->path . '&content=video&fn=' . $val['src'] . '&' . JSession::getFormToken() . '=1'
							);
						}
					}
				}

				if (isset($value->files['video'][0]['resolution']) && !empty($value->files['video'][0]['resolution']))
				{
					if ($value->files['video'][0]['resolution'] != '' && $value->files['video'][0]['resolution'] != 'x')
					{
						$resolution = $value->files['video'][0]['resolution'];
					}
					else
					{
						$resolution = '1280x720';
					}
				}
				else
				{
					if ($value->resolution != '' && $value->resolution != 'x')
					{
						$resolution = $value->resolution;
					}
					else
					{
						$resolution = '1280x720';
					}
				}

				$trailerResolution    = explode('x', $resolution);
				$trailerHeight        = $trailerResolution[1];
				$value->player_height = floor(($trailerHeight * $result->player_width) / $trailerResolution[0]);

				// Set default aspect ratio if it's not set
				if ($value->dar == '')
				{
					$value->dar = '16:9';
				}

				// Check if subtitle file exists
				$_subtitles = json_decode($value->subtitles, true);

				if (!empty($_subtitles))
				{
					foreach ($_subtitles as $i => $subtitle)
					{
						if (!is_file(JPath::clean($path . $subtitle['file'])))
						{
							unset($_subtitles[$i]);
						}
						else
						{
							if ($throttleVideoEnable == 0)
							{
								$_subtitles[$i]['file'] = $value->path . $subtitle['file'];
							}
							else
							{
								$_subtitles[$i]['file'] = JRoute::_($value->path . '&content=subtitles&fn=' . $subtitle['file']);
							}
						}
					}
				}

				$value->files['subtitles'] = $_subtitles;

				// Check if chapter file exists
				$_chapters = json_decode($value->chapters, true);

				if (!empty($_chapters))
				{
					foreach ($_chapters as $i => $chapter)
					{
						if (!file_exists(JPath::clean($path . $chapter)))
						{
							unset($_chapters[$i]);
						}
						else
						{
							if ($throttleVideoEnable == 0)
							{
								$_chapters[$i] = $value->path . $chapter;
							}
							else
							{
								$_chapters[$i] = JRoute::_($value->path . '&content=chapters&fn=' . $chapter);
							}
						}
					}
				}

				$value->files['chapters'] = $_chapters;
			}
		}

		return $result;
	}

	/**
	 * Method to get one trailer for movie
	 *
	 * @param   integer  $id    Movie ID
	 * @param   string   $type  Trailer type. If set to 'movie' when it's full length movie, trailer otherwise.
	 *
	 * @return object
	 *
	 * @since  3.0
	 */
	public function getTrailer($id = null, $type = null)
	{
		jimport('joomla.filesystem.file');

		$db                  = $this->getDbo();
		$app                 = JFactory::getApplication();
		$user                = JFactory::getUser();
		$lang                = JFactory::getLanguage();
		$groups              = implode(',', $user->getAuthorisedViewLevels());
		$params              = JComponentHelper::getParams('com_kinoarhiv');
		$id                  = is_null($id) ? $app->input->get('id', null, 'int') : $id;
		$type                = is_null($type) ? $type = $app->input->get('type', '') : $type;
		$itemid              = $app->input->get('Itemid', 0, 'int');
		$throttleImgEnable   = $params->get('throttle_image_enable', 0);
		$throttleVideoEnable = $params->get('throttle_video_enable', 0);
		$allowedFormats      = array('mp4', 'webm', 'ogv', 'flv');

		if ($type == 'movie')
		{
			if ($params->get('allow_guest_watch') != 1)
			{
				return (object) array();
			}

			$isMovie = 1;
		}
		else
		{
			$isMovie = 0;
		}

		$query = $db->getQuery(true)
			->select(
				$db->quoteName(
					array('tr.id', 'tr.title', 'tr.embed_code', 'tr.screenshot', 'tr.urls', 'tr.resolution', 'tr.dar',
						'tr.duration', 'tr.video', 'tr.subtitles', 'tr.chapters', 'm.title', 'm.year', 'm.alias',
						'm.fs_alias'
					)
				)
			)
			->from($db->quoteName('#__ka_trailers', 'tr'))
			->join('LEFT', $db->quoteName('#__ka_movies', 'm') . ' ON m.id = tr.movie_id')
			->where('tr.movie_id = ' . (int) $id . ' AND tr.state = 1 AND tr.access IN (' . $groups . ')')
			->where('tr.language IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ') AND tr.is_movie = ' . $isMovie)
			->where('tr.frontpage = 1')
			->setLimit(1, 0);

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
			$this->setError('');
			KAComponentHelper::eventLog($e->getMessage());

			return (object) array();
		}

		$result->player_width = $params->get('player_width');

		// Just empty element
		$result->path = '';

		if (!empty($result->urls))
		{
			$urlsArr = explode("\n", $result->urls);

			if (count($urlsArr) > 0)
			{
				if (file_exists($params->get('media_trailers_root') . '/' . $result->fs_alias . '/' . $id . '/' . $result->screenshot))
				{
					if (StringHelper::substr($params->get('media_trailers_root_www'), 0, 1) == '/')
					{
						$result->screenshot = JUri::base() . StringHelper::substr($params->get('media_trailers_root_www'), 1) . '/' . $result->fs_alias . '/' . $id . '/' . $result->screenshot;
					}
					else
					{
						$result->screenshot = $params->get('media_trailers_root_www') . '/' . $result->fs_alias . '/' . $id . '/' . $result->screenshot;
					}
				}
				else
				{
					$result->screenshot = '';
				}

				$result->files['video']       = array();
				$result->files['subtitles']   = array();
				$result->files['chapters']    = array();
				$result->files['video_links'] = array();

				foreach ($urlsArr as $v)
				{
					if (preg_match('#\[(url="(?P<url>.+?)")?(\stype="(?P<type>.+?)")?(\splayer="(?P<player>.+?)")?(\sresolution="(?P<resolution>.+?)")?(\skind="(?P<kind>.+?)")?(\ssrclang="(?P<srclang>.+?)")?(\slabel="(?P<label>.+?)")?(\sdefault="(?P<default>.+?)")?\]#i', $v, $m))
					{
						if (isset($m['url']) && !empty($m['url']))
						{
							$url = $m['url'];
							$type = (isset($m['type']) && !empty($m['type'])) ? $m['type'] : '';

							if (isset($m['player']))
							{
								if (!empty($m['player']) && $m['player'] == 'true')
								{
									$inPlayer = true;
								}
								else
								{
									$inPlayer = false;
								}
							}
							else
							{
								$inPlayer = false;
							}

							$resolution = (isset($m['resolution']) && !empty($m['resolution'])) ? $m['resolution'] : '';
							$kind       = (isset($m['kind']) && !empty($m['kind'])) ? $m['kind'] : '';
							$srclang    = (isset($m['srclang']) && !empty($m['srclang'])) ? $m['srclang'] : '';
							$label      = (isset($m['label']) && !empty($m['label'])) ? $m['label'] : '';
							$default    = isset($m['default']) && !empty($m['default']);

							if (!empty($resolution))
							{
								$resolution = $m['resolution'];
							}
							else
							{
								if ($result->resolution != '')
								{
									$resolution = $result->resolution;
								}
								else
								{
									$resolution = '1280x720';
								}
							}

							$trailerResolution     = explode('x', $resolution);
							$trailerHeight         = $trailerResolution[1];
							$result->player_height = floor(($trailerHeight * $result->player_width) / $trailerResolution[0]);

							if ($kind == '')
							{
								if ($inPlayer === true)
								{
									$result->files['video'][] = array(
										'src'        => $url,
										'type'       => $type,
										'resolution' => $resolution
									);
								}
								else
								{
									$result->files['video_links'][] = array(
										'src'        => $url,
										'type'       => $type,
										'resolution' => $resolution
									);
								}
							}

							if ($kind == 'subtitles')
							{
								$result->files['subtitles'][] = array(
									'default'   => $default,
									'lang_code' => $srclang,
									'lang'      => $label,
									'file'      => $url
								);
							}

							if ($kind == 'chapters')
							{
								$result->files['chapters'] = array(
									'file' => $url
								);
							}
						}
					}
				}
			}
			else
			{
				$result->files['video'] = array();
			}
		}
		else
		{
			if ($throttleImgEnable == 0)
			{
				$checkingPath = JPath::clean(
					$params->get('media_trailers_root') . '/' . $result->fs_alias . '/' . $id . '/' . $result->screenshot
				);

				if (!is_file($checkingPath))
				{
					$result->screenshot = JUri::base() . 'media/com_kinoarhiv/images/video_off.png';
				}
				else
				{
					$imgFsAlias = rawurlencode($result->fs_alias);

					if (StringHelper::substr($params->get('media_trailers_root_www'), 0, 1) == '/')
					{
						$result->screenshot = JUri::base() . StringHelper::substr($params->get('media_trailers_root_www'), 1) . '/'
							. $imgFsAlias . '/' . $id . '/' . $result->screenshot;
					}
					else
					{
						$result->screenshot = $params->get('media_trailers_root_www') . '/' . $imgFsAlias . '/' . $id . '/' . $result->screenshot;
					}
				}
			}
			else
			{
				$result->screenshot = JRoute::_(
					'index.php?option=com_kinoarhiv&task=media.view&element=trailer&content=image&id=' . $id .
					'&item_id=' . $result->id . '&fa=' . urlencode($result->fs_alias) . '&fn=' . $result->screenshot . '&format=raw&Itemid=' . $itemid
				);
			}

			if ($throttleVideoEnable == 0)
			{
				$videoFsAlias = rawurlencode($result->fs_alias);

				if (StringHelper::substr($params->get('media_trailers_root_www'), 0, 1) == '/')
				{
					// $result->path is an URL, $path is a root path to the files
					$result->path = JUri::base() . StringHelper::substr($params->get('media_trailers_root_www'), 1) . '/'
						. $videoFsAlias . '/' . $id . '/';
					$path = JPATH_ROOT . '/' . StringHelper::substr($params->get('media_trailers_root_www'), 1)
						. '/' . $result->fs_alias . '/' . $id . '/';
				}
				else
				{
					$result->path = $params->get('media_trailers_root_www') . '/' . $videoFsAlias . '/' . $id . '/';
					$path = JPATH_ROOT . '/' . $params->get('media_trailers_root_www')
						. '/' . $result->fs_alias . '/' . $id . '/';
				}
			}
			else
			{
				$result->path = 'index.php?option=com_kinoarhiv&task=media.view&element=trailer&id=' . $id .
					'&item_id=' . $result->id . '&fa=' . urlencode($result->fs_alias) . '&format=raw&Itemid=' . $itemid;
				$path = JPATH_ROOT . '/' . $params->get('media_trailers_root_www')
					. '/' . $result->fs_alias . '/' . $id . '/';
			}

			$result->files['video'] = json_decode($result->video, true);
			$result->files['video_links'] = array();

			// Checking video extentions
			if (count($result->files['video']) > 0)
			{
				foreach ($result->files['video'] as $i => $val)
				{
					if ($throttleVideoEnable == 0)
					{
						$result->files['video'][$i]['src'] = $result->path . $val['src'];

						// Check video extentions
						if (!in_array(strtolower(JFile::getExt($val['src'])), $allowedFormats))
						{
							$result->files['video_links'][] = $result->files['video'][$i];
							unset($result->files['video'][$i]);
						}
					}
					else
					{
						$result->files['video'][$i]['src'] = JRoute::_($result->path . '&content=video&fn=' . $val['src'] . '&' . JSession::getFormToken() . '=1');
					}
				}
			}

			if (isset($result->files['video'][0]['resolution']) && !empty($result->files['video'][0]['resolution']))
			{
				if ($result->files['video'][0]['resolution'] != '' && $result->files['video'][0]['resolution'] != 'x')
				{
					$resolution = $result->files['video'][0]['resolution'];
				}
				else
				{
					$resolution = '1280x720';
				}
			}
			else
			{
				if ($result->resolution != '' && $result->resolution != 'x')
				{
					$resolution = $result->resolution;
				}
				else
				{
					$resolution = '1280x720';
				}
			}

			$trailerResolution     = explode('x', $resolution);
			$trailerHeight         = $trailerResolution[1];
			$result->player_height = floor(($trailerHeight * $result->player_width) / $trailerResolution[0]);

			// Set default aspect ratio if it's not set
			if ($result->dar == '')
			{
				$result->dar = '16:9';
			}

			// Check if subtitle file exists
			$_subtitles = json_decode($result->subtitles, true);

			if (!empty($_subtitles))
			{
				foreach ($_subtitles as $i => $subtitle)
				{
					if (!is_file(JPath::clean($path . $subtitle['file'])))
					{
						unset($_subtitles[$i]);
					}
					else
					{
						if ($throttleVideoEnable == 0)
						{
							$_subtitles[$i]['file'] = $result->path . $subtitle['file'];
						}
						else
						{
							$_subtitles[$i]['file'] = JRoute::_($result->path . '&content=subtitles&fn=' . $subtitle['file']);
						}
					}
				}
			}

			$result->files['subtitles'] = $_subtitles;

			// Check if chapter file exists
			$_chapters = json_decode($result->chapters, true);

			if (!empty($_chapters))
			{
				foreach ($_chapters as $i => $chapter)
				{
					if (!file_exists(JPath::clean($path . $chapter)))
					{
						unset($_chapters[$i]);
					}
					else
					{
						if ($throttleVideoEnable == 0)
						{
							$_chapters[$i] = $result->path . $chapter;
						}
						else
						{
							$_chapters[$i] = JRoute::_($result->path . '&content=chapters&fn=' . $chapter);
						}
					}
				}
			}

			$result->files['chapters'] = $_chapters;
		}

		return $result;
	}

	/**
	 * Check access for trailer.
	 *
	 * @param   integer  $id  Trailer ID.
	 *
	 * @return  boolean
	 *
	 * @since   3.0
	 */
	public function getTrailerAccessLevel($id)
	{
		$db     = $this->getDbo();
		$user   = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());

		$query = $db->getQuery(true)
			->select('COUNT(id)')
			->from($db->quoteName('#__ka_trailers'))
			->where('id = ' . (int) $id . ' AND state = 1 AND access IN (' . $groups . ')');

		$db->setQuery($query);

		try
		{
			$result = $db->loadResult();
		}
		catch (RuntimeException $e)
		{
			return false;
		}

		return $result < 1 ? false : true;
	}

	/**
	 * Get winned awards for movie
	 *
	 * @return  object|boolean
	 *
	 * @since   3.0
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

		$result = $this->getMovieData();

		$query = $db->getQuery(true)
			->select('a.desc, a.year, aw.id, aw.title AS aw_title, aw.desc AS aw_desc')
			->from($db->quoteName('#__ka_rel_awards', 'a'))
			->join('LEFT', $db->quoteName('#__ka_awards', 'aw') . ' ON aw.id = a.award_id')
			->where('type = 0 AND item_id = ' . (int) $id)
			->order('year ASC');

		$db->setQuery($query);

		try
		{
			$result->awards = $db->loadObjectList();
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
	 * Get the list of albums and their tracks for movie
	 *
	 * @return  object|boolean
	 *
	 * @since   3.0
	 */
	public function getSoundtrackAlbums()
	{
		$db      = $this->getDbo();
		$user    = JFactory::getUser();
		$groups  = implode(',', $user->getAuthorisedViewLevels());
		$app     = JFactory::getApplication();
		$movieID = $app->input->get('id', 0, 'int');

		if ($movieID == 0)
		{
			return false;
		}

		$result = $this->getMovieData();

		// Get albums for movie
		$query = $db->getQuery(true)
			->select(
				$db->quoteName(
					array(
						'a.id', 'a.title', 'a.alias', 'a.fs_alias', 'a.composer', 'a.length', 'a.isrc',
						'a.rate', 'a.rate_sum', 'a.cover_filename', 'a.covers_path', 'a.covers_path_www',
						'a.tracks_path', 'a.tracks_preview_path', 'a.buy_urls', 'a.attribs', 'n.name', 'n.latin_name'
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
				->where($db->quoteName('movie_id') . ' = ' . (int) $movieID);

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
				->where($db->quoteName('movie_id') . ' = ' . (int) $movieID);

		$query->where('t.album_id IN (' . $subquery . ')')
			->order('t.track_number ASC');

		$db->setQuery($query);

		try
		{
			$result->tracks = $db->loadObjectList();
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
	 * Build list of filters by dimensions for gallery
	 *
	 * @return  array|boolean
	 *
	 * @since   3.0
	 */
	public function getDimensionFilters()
	{
		$app    = JFactory::getApplication();
		$db     = $this->getDbo();
		$id     = $app->input->get('id', 0, 'int');
		$page   = $app->input->get('page', null, 'cmd');
		$result = array();

		if ($page == 'wallpapers')
		{
			$query = $db->getQuery(true)
				->select("dimension AS value, dimension AS title, SUBSTRING_INDEX(dimension, 'x', 1) AS width")
				->from($db->quoteName('#__ka_movies_gallery'))
				->where('movie_id = ' . (int) $id . ' AND type = 1 AND state = 1')
				->group('width')
				->order('width DESC');

			$db->setQuery($query);

			try
			{
				$result = $db->loadAssocList();
			}
			catch (RuntimeException $e)
			{
				$this->setError('');
				KAComponentHelper::eventLog($e->getMessage());

				return false;
			}
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
				->where('item_id = ' . (int) $id . ' AND item_type = 0 AND rev.state = 1 AND u.id != 0')
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
		parent::populateState($ordering, $direction);

		$app    = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$var    = '';

		$this->setState('params', $params);

		if ($this->context == 'com_kinoarhiv.movie.global')
		{
			$var = 'reviews_';
		}

		$limit = $params->get($var . 'list_limit');

		// Override default limit settings and respect user selection if 'show_pagination_limit' is set to Yes.
		if ($params->get($var . 'show_pagination_limit'))
		{
			$limit = $app->getUserStateFromRequest('list.limit', 'limit', $params->get($var . 'list_limit'), 'uint');
		}

		$this->setState('list.limit', $limit);

		$limitstart = $app->input->getUInt('limitstart', 0);
		$this->setState('list.start', $limitstart);
	}
}
