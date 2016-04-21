<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
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
	protected $cache = array();

	protected $context = null;

	protected $filter_fields = array();

	protected $query = array();

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
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$id = $app->input->get('id', 0, 'int');
		$itemid = $app->input->get('Itemid', 0, 'int');
		$data = $app->getUserState('com_kinoarhiv.movie.' . $id . '.user.' . $user->get('id'));

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
	 * @return object
	 */
	public function getData()
	{
		$db = $this->getDbo();
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$lang = JFactory::getLanguage();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$id = $app->input->get('id', 0, 'int');
		$language_in = 'language IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ')';

		$query = $db->getQuery(true);

		$query->select("m.id, m.parent_id, m.title, m.alias, m.fs_alias, m.plot, m.desc, m.known, m.slogan, m.budget, "
					. "m.age_restrict, m.ua_rate, m.mpaa, m.rate_loc, m.rate_sum_loc, m.imdb_votesum, m.imdb_votes, "
					. "m.imdb_id, m.kp_votesum, m.kp_votes, m.kp_id, m.rate_fc, m.rottentm_id, m.metacritics, "
					. "m.metacritics_id, m.rate_custom, m.urls, m.buy_urls, m.length, m.year, m.created_by, m.metakey, "
					. "m.metadesc, m.attribs, m.state, m.metadata, DATE_FORMAT(m.created, '%Y-%m-%d') AS created, "
					. "DATE_FORMAT(m.modified, '%Y-%m-%d') AS modified"
		)
		->from($db->quoteName('#__ka_movies', 'm'));

		// Join over gallery item
		$query->select($db->quoteName('g.filename'))
			->join('LEFT', $db->quoteName('#__ka_movies_gallery', 'g') . ' ON g.movie_id = m.id AND g.type = 2 AND g.poster_frontpage = 1 AND g.state = 1');

		if (!$user->get('guest'))
		{
			$query->select('u.favorite, u.watched')
				->join('LEFT', $db->quoteName('#__ka_user_marked_movies', 'u') . ' ON u.uid = ' . $user->get('id') . ' AND u.movie_id = m.id');

			$query->select('v.vote AS my_vote, v._datetime')
				->join('LEFT', $db->quoteName('#__ka_user_votes', 'v') . ' ON v.movie_id = m.id AND v.uid = ' . $user->get('id'));
		}

		$query->select('user.name AS username')
			->join('LEFT', $db->quoteName('#__users', 'user') . ' ON user.id = m.created_by')
			->where('m.id = ' . (int) $id . ' AND m.state = 1 AND access IN (' . $groups . ') AND ' . $language_in);

		$db->setQuery($query);

		try
		{
			$result = $db->loadObject();

			if (empty($result))
			{
				return false;
			}
		}
		catch (Exception $e)
		{
			$result = (object) array();
			$this->setError($e->getMessage());
			KAComponentHelper::eventLog($e->getMessage());
		}

		if (isset($result->attribs))
		{
			$result->attribs = json_decode($result->attribs);

			// Get tags
			if ($result->attribs->show_tags == 1)
			{
				// Check for an errors
				if (isset($result->metadata) && !empty($result->metadata))
				{
					$metadata = json_decode($result->metadata);
					$result->tags = $this->getTags(implode(',', $metadata->tags));
				}
			}
		}

		// Countries
		$query_countries = $db->getQuery(true)
			->select('c.id, c.name, c.code, t.ordering')
			->from($db->quoteName('#__ka_countries', 'c'))
			->join('LEFT', $db->quoteName('#__ka_rel_countries', 't') . ' ON t.country_id = c.id AND t.movie_id = ' . (int) $id);

			$subquery_countries = $db->getQuery(true)
				->select('country_id')
				->from($db->quoteName('#__ka_rel_countries'))
				->where('movie_id = ' . (int) $id);

		$query_countries->where('id IN (' . $subquery_countries . ') AND state = 1')
			->order('ordering ASC');

		$db->setQuery($query_countries);

		try
		{
			$result->countries = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			$result->countries = array();
			KAComponentHelper::eventLog($e->getMessage());
		}

		// Genres
		$query_genres = $db->getQuery(true)
			->select('g.id, g.name, g.alias, t.ordering')
			->from($db->quoteName('#__ka_genres', 'g'))
			->join('LEFT', $db->quoteName('#__ka_rel_genres', 't') . ' ON t.genre_id = g.id AND t.movie_id = ' . (int) $id);

			$subquery_genres = $db->getQuery(true)
				->select('genre_id')
				->from($db->quoteName('#__ka_rel_genres'))
				->where('movie_id = ' . (int) $id);

		$query_genres->where('id IN (' . $subquery_genres . ') AND state = 1 AND access IN (' . $groups . ') AND ' . $language_in)
			->order('ordering ASC');

		$db->setQuery($query_genres);

		try
		{
			$result->genres = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			$result->genres = array();
			KAComponentHelper::eventLog($e->getMessage());
		}

		// Cast and crew
		$careers = array();
		$query_career = $db->getQuery(true)
			->select('id, title')
			->from($db->quoteName('#__ka_names_career'))
			->where('is_mainpage = 1 AND is_amplua = 0')
			->order('ordering ASC');

		$db->setQuery($query_career);
		$_careers = $db->loadObjectList();

		foreach ($_careers as $career)
		{
			$careers[$career->id] = $career->title;
		}

		$query_crew = $db->getQuery(true)
			->select('n.id, n.name, n.latin_name, n.alias, t.type, t.is_actors, t.is_directors, t.voice_artists')
			->from($db->quoteName('#__ka_names', 'n'))
			->join('LEFT', $db->quoteName('#__ka_rel_names', 't') . ' ON t.name_id = n.id AND t.movie_id = ' . (int) $id);

			$subquery_crew = $db->getQuery(true)
				->select('name_id')
				->from($db->quoteName('#__ka_rel_names'))
				->where('movie_id = ' . (int) $id);

		$query_crew->where('id IN (' . $subquery_crew . ') AND state = 1 AND access IN (' . $groups . ') AND ' . $language_in)
			->order('t.ordering ASC');

		$db->setQuery($query_crew);
		$crew = $db->loadObjectList();

		$_result = array();

		foreach ($crew as $key => $value)
		{
			foreach (explode(',', $value->type) as $k => $type)
			{
				if (isset($careers[$type]) && $value->is_actors == 0 && $value->voice_artists == 0)
				{
					$_result['crew'][$type]['career'] = $careers[$type];
					$_result['crew'][$type]['items'][] = array(
						'id'        => $value->id,
						'name'      => !empty($value->name) ? $value->name : $value->latin_name,
						'alias'     => $value->alias,
						'directors' => $value->is_directors
					);
				}

				if (isset($careers[$type]) && $value->is_actors == 1 && $value->voice_artists == 0)
				{
					$_result['cast'][$type]['career'] = $careers[$type];
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

		// Premiere dates
		if ($params->get('premieres_list_limit') > 0)
		{
			$query_p = $db->getQuery(true)
				->select('p.id, p.vendor_id, p.premiere_date, p.info, c.name AS country, v.company_name, v.company_name_intl')
				->from($db->quoteName('#__ka_premieres', 'p'))
				->join('LEFT', $db->quoteName('#__ka_vendors', 'v') . ' ON v.id = p.vendor_id')
				->join('LEFT', $db->quoteName('#__ka_countries', 'c') . ' ON c.id = p.country_id')
				->where('movie_id = ' . (int) $id . ' AND p.language IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ')')
				->order('p.ordering ASC')
				->setLimit((int) $params->get('premieres_list_limit'), 0);

			$db->setQuery($query_p);

			try
			{
				$result->premieres = $db->loadObjectList();
			}
			catch (Exception $e)
			{
				$result->premieres = (object) array();
				KAComponentHelper::eventLog($e->getMessage());
			}
		}
		else
		{
			$result->premieres = (object) array();
		}

		// Release dates
		if ($params->get('releases_list_limit') > 0)
		{
			$query_r = $db->getQuery(true)
				->select('r.id, r.movie_id, r.release_date, c.name AS country, v.company_name, v.company_name_intl, media.title AS media_type')
				->from($db->quoteName('#__ka_releases', 'r'))
				->join('LEFT', $db->quoteName('#__ka_vendors', 'v') . ' ON v.id = r.vendor_id')
				->join('LEFT', $db->quoteName('#__ka_countries', 'c') . ' ON c.id = r.country_id')
				->join('LEFT', $db->quoteName('#__ka_media_types', 'media') . ' ON media.id = r.media_type')
				->where('movie_id = ' . (int) $id . ' AND r.language IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ')')
				->order('r.ordering ASC')
				->setLimit((int) $params->get('releases_list_limit'), 0);

			$db->setQuery($query_r);

			try
			{
				$result->releases = $db->loadObjectList();
			}
			catch (Exception $e)
			{
				$result->releases = (object) array();
				KAComponentHelper::eventLog($e->getMessage());
			}
		}
		else
		{
			$result->releases = (object) array();
		}

		$result->trailer = ($params->get('watch_trailer') == 1) ? $this->getTrailer($id, 'trailer') : array();
		$result->movie = ($params->get('watch_movie') == 1) ? $this->getTrailer($id, 'movie') : array();

		// Get Slider items
		if (($result->attribs->slider == '' && $params->get('slider') == 1) || $result->attribs->slider == 1)
		{
			$query_slider = $db->getQuery(true)
				->select($db->quoteName(array('id', 'filename', 'dimension')))
				->from($db->quoteName('#__ka_movies_gallery'))
				->where('movie_id = ' . (int) $id . ' AND state = 1 AND type = 3')
				->setLimit((int) $params->get('slider_max_item'), 0);

			$db->setQuery($query_slider);

			try
			{
				$result->slides = $db->loadObjectList();
			}
			catch (Exception $e)
			{
				$result->slides = (object) array();
				KAComponentHelper::eventLog($e->getMessage());
			}
		}

		return $result;
	}

	/**
	 * Get a short movie info
	 *
	 * @return object
	 */
	public function getMovieData()
	{
		$db = $this->getDbo();
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$lang = JFactory::getLanguage();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$id = $app->input->get('id', 0, 'int');

		$query = $db->getQuery(true)
			->select("m.id, m.title, m.alias, m.fs_alias, m.year, DATE_FORMAT(m.created, '%Y-%m-%d') AS created, " .
					"DATE_FORMAT(m.modified, '%Y-%m-%d') AS modified, m.metakey, m.metadesc, m.metadata, m.attribs, user.name AS username")
			->from($db->quoteName('#__ka_movies', 'm'))
			->join('LEFT', $db->quoteName('#__users', 'user') . ' ON user.id = m.created_by')
			->where('m.id = ' . (int) $id . ' AND m.state = 1 AND m.access IN (' . $groups . ')')
			->where('m.language IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ')');

		$db->setQuery($query);

		try
		{
			$result = $db->loadObject();

			if (count($result) == 0)
			{
				$this->setError('Error');
				$result = (object) array();
			}
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			KAComponentHelper::eventLog($e->getMessage());
		}

		$result->attribs = isset($result->attribs) ? json_decode($result->attribs) : "{}";

		return $result;
	}

	/**
	 * Method
	 *
	 * @param   mixed  $ids  Tag ID or array of tags IDs
	 *
	 * @return object
	 */
	protected function getTags($ids)
	{
		$db = $this->getDbo();

		if (empty($ids))
		{
			return array();
		}

		if (is_array($ids))
		{
			$ids = implode(',', $ids);
		}

		$query = $db->getQuery(true)
			->select('id AS tag_id, title AS tag_title, alias AS tag_alias')
			->from($db->quoteName('#__tags'))
			->where('id IN (' . $ids . ')');

		$db->setQuery($query);

		try
		{
			$result = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			$result = array();
		}

		return $result;
	}

	/**
	 * Method to get cast and crew for movie
	 *
	 * @return object
	 */
	public function getCast()
	{
		$db = $this->getDbo();
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$lang = JFactory::getLanguage();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$id = $app->input->get('id', 0, 'int');
		$itemid = $app->input->get('Itemid', 0, 'int');
		$throttle_enable = $params->get('throttle_image_enable', 0);

		$result = $this->getMovieData();

		$careers = array();
		$query = $db->getQuery(true)
			->select('id, title')
			->from($db->quoteName('#__ka_names_career'))
			->order('ordering ASC');

		$db->setQuery($query);
		$_careers = $db->loadObjectList();

		foreach ($_careers as $career)
		{
			$careers[$career->id] = $career->title;
		}

		$query_crew = $db->getQuery(true)
			->select("n.id, n.name, n.latin_name, n.alias, n.fs_alias, n.gender, t.type, t.role, t.is_actors, t.voice_artists, " .
					"d.id AS dub_id, d.name AS dub_name, d.latin_name AS dub_latin_name, d.alias AS dub_alias, d.fs_alias AS dub_fs_alias, " .
					"d.gender AS dub_gender, GROUP_CONCAT(r.role SEPARATOR ', ') AS dub_role, ac.desc, " .
					"g.filename AS url_photo, dg.filename AS dub_url_photo")
			->from($db->quoteName('#__ka_names', 'n'))
			->join('LEFT', $db->quoteName('#__ka_rel_names', 't') . ' ON t.name_id = n.id AND t.movie_id = ' . (int) $id)
			->join('LEFT', $db->quoteName('#__ka_names', 'd') . ' ON d.id = t.dub_id AND d.state = 1 AND d.access IN (' . $groups . ') AND d.language IN (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')')
			->join('LEFT', $db->quoteName('#__ka_rel_names', 'r') . ' ON r.dub_id = n.id AND r.movie_id = ' . (int) $id)
			->join('LEFT', $db->quoteName('#__ka_rel_names', 'ac') . ' ON ac.name_id = n.id AND ac.movie_id = ' . (int) $id)
			->join('LEFT', $db->quoteName('#__ka_names_gallery', 'g') . ' ON g.name_id = n.id AND g.type = 3 AND g.photo_frontpage = 1')
			->join('LEFT', $db->quoteName('#__ka_names_gallery', 'dg') . ' ON dg.name_id = d.id AND dg.type = 3 AND dg.photo_frontpage = 1');

			$subquery_crew = $db->getQuery(true)
				->select('name_id')
				->from($db->quoteName('#__ka_rel_names'))
				->where('movie_id = ' . (int) $id);

		$query_crew->where('n.id IN (' . $subquery_crew . ') AND n.state = 1 AND n.access IN (' . $groups . ')')
			->where('n.language IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ')')
			->group('n.id')
			->order('t.ordering ASC');

		$db->setQuery($query_crew);
		$crew = $db->loadObjectList();

		$_result = array('crew' => array(), 'cast' => array(), 'dub' => array());
		$_careers_crew = array();

		foreach ($crew as $key => $value)
		{
			foreach (explode(',', $value->type) as $k => $type)
			{
				// Process posters
				if ($throttle_enable == 0)
				{
					// Cast and crew photo
					$checking_path = JPath::clean(
						$params->get('media_actor_photo_root') . '/' . $value->fs_alias . '/' . $value->id . '/photo/' . $value->url_photo
					);
					$no_cover = ($value->gender == 0) ? 'no_name_cover_f' : 'no_name_cover_m';

					if (!is_file($checking_path))
					{
						$value->poster = JUri::base() . 'components/com_kinoarhiv/assets/themes/component/' . $params->get('ka_theme')
							. '/images/' . $no_cover . '.png';
					}
					else
					{
						$value->fs_alias = rawurlencode($value->fs_alias);

						if (StringHelper::substr($params->get('media_actor_photo_root_www'), 0, 1) == '/')
						{
							$value->poster = JUri::base() . StringHelper::substr($params->get('media_actor_photo_root_www'), 1) . '/'
								. $value->fs_alias . '/' . $value->id . '/photo/thumb_' . $value->url_photo;
						}
						else
						{
							$value->poster = $params->get('media_actor_photo_root_www') . '/' . $value->fs_alias . '/'
								. $value->id . '/photo/thumb_' . $value->url_photo;
						}
					}

					// Dub actors photo
					if (isset($careers[$type]) && $value->is_actors == 1 && $value->voice_artists == 0)
					{
						$checking_path1 = JPath::clean(
							$params->get('media_actor_photo_root') . '/' . $value->dub_fs_alias . '/' . $value->dub_id . '/photo/' . $value->dub_url_photo
						);
						$no_cover1 = ($value->dub_gender == 0) ? 'no_name_cover_f' : 'no_name_cover_m';

						if (!is_file($checking_path1))
						{
							$value->dub_url_photo = JUri::base() . 'components/com_kinoarhiv/assets/themes/component/' . $params->get('ka_theme')
								. '/images/' . $no_cover1 . '.png';
						}
						else
						{
							$value->dub_fs_alias = rawurlencode($value->dub_fs_alias);

							if (StringHelper::substr($params->get('media_actor_photo_root_www'), 0, 1) == '/')
							{
								$value->dub_url_photo = JUri::base() . StringHelper::substr($params->get('media_actor_photo_root_www'), 1) . '/'
									. $value->dub_fs_alias . '/' . $value->dub_id . '/photo/thumb_' . $value->dub_url_photo;
							}
							else
							{
								$value->dub_url_photo = $params->get('media_actor_photo_root_www') . '/' . $value->dub_fs_alias . '/'
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
					$_careers_crew[] = $careers[$type];
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
					$_careers_cast = $careers[$type];
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
					$_careers_dub = $careers[$type];
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
		$result->dub = $_result['dub'];

		// Create a new array with name career, remove duplicate items and sort it
		$new_careers = array_unique($_careers_crew, SORT_STRING);

		foreach ($new_careers as $row)
		{
			$result->careers['crew'][] = $row;
		}

		$result->careers['cast'] = isset($_careers_cast) ? $_careers_cast : '';
		$result->careers['dub'] = isset($_careers_dub) ? $_careers_dub : '';

		return $result;
	}

	/**
	 * Method to get trailers for movie
	 *
	 * @param   integer  $id    Movie ID
	 *
	 * @return object
	 */
	public function getTrailers($id = null)
	{
		jimport('joomla.filesystem.file');

		$db = $this->getDbo();
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$lang = JFactory::getLanguage();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$id = is_null($id) ? $app->input->get('id', null, 'int') : $id;
		$itemid = $app->input->get('Itemid', 0, 'int');
		$result = $this->getMovieData();
		$result->player_width = $params->get('player_width');
		$throttle_img_enable = $params->get('throttle_image_enable', 0);
		$throttle_video_enable = $params->get('throttle_video_enable', 0);
		$allowed_formats = array('mp4', 'webm', 'ogv', 'flv');

		$query = $db->getQuery(true)
			->select(
				$db->quoteName(
					array('tr.id', 'tr.title', 'tr.embed_code', 'tr.screenshot', 'tr.urls', 'tr.filename', 'tr.resolution',
						'tr.dar', 'tr.duration', 'tr._subtitles', 'tr._chapters', 'tr.is_movie', 'm.alias', 'm.fs_alias'
					)
				)
			)
			->from($db->quoteName('#__ka_trailers', 'tr'))
			->join('LEFT', $db->quoteName('#__ka_movies', 'm') . ' ON m.id = tr.movie_id')
			->where('tr.movie_id = ' . (int) $id . ' AND tr.state = 1 AND tr.access IN (' . $groups . ')')
			->where('tr.language IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ')');

		$db->setQuery($query);
		$result->trailers = $db->loadObjectList();

		if (count($result->trailers) < 1)
		{
			$result->trailers = array();

			return $result;
		}

		foreach ($result->trailers as $key => $value)
		{
			// Get the data from urls
			if (!empty($value->urls))
			{
				$urls_arr = explode("\n", $value->urls);
				$value->path = '';

				if (count($urls_arr) > 0)
				{
					if ($throttle_img_enable == 0)
					{
						$checking_path = JPath::clean(
							$params->get('media_trailers_root') . '/' . $value->fs_alias . '/' . $id . '/' . $value->screenshot
						);

						if (!is_file($checking_path))
						{
							$value->screenshot = JUri::base() . 'components/com_kinoarhiv/assets/themes/component/'
								. $params->get('ka_theme') . '/images/video_off.png';
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
								$value->screenshot = $params->get('media_trailers_root_www') . '/' . $value->fs_alias . '/' . $id . '/' . $value->screenshot;
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

					$value->files['video'] = array();
					$value->files['subtitles'] = array();
					$value->files['chapters'] = array();
					$value->files['video_links'] = array();

					foreach ($urls_arr as $v)
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
										$in_player = true;
									}
									else
									{
										$in_player = false;
									}
								}
								else
								{
									$in_player = false;
								}

								$resolution = (isset($m['resolution']) && !empty($m['resolution'])) ? $m['resolution'] : '';
								$kind = (isset($m['kind']) && !empty($m['kind'])) ? $m['kind'] : '';
								$srclang = (isset($m['srclang']) && !empty($m['srclang'])) ? $m['srclang'] : '';
								$label = (isset($m['label']) && !empty($m['label'])) ? $m['label'] : '';
								$default = (isset($m['default']) && !empty($m['default'])) ? true : false;

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

								$tr_resolution = explode('x', $resolution);
								$tr_height = $tr_resolution[1];
								$value->player_height = floor(($tr_height * $result->player_width) / $tr_resolution[0]);

								if ($kind == '')
								{
									if ($in_player === true)
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
				if ($throttle_img_enable == 0)
				{
					$checking_path = JPath::clean(
						$params->get('media_trailers_root') . '/' . $value->fs_alias . '/' . $id . '/' . $value->screenshot
					);

					if (!is_file($checking_path))
					{
						$value->screenshot = JUri::base() . 'components/com_kinoarhiv/assets/themes/component/'
							. $params->get('ka_theme') . '/images/video_off.png';
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
							$value->screenshot = $params->get('media_trailers_root_www') . '/' . $value->fs_alias . '/' . $id . '/' . $value->screenshot;
						}
					}
				}
				else
				{
					$value->screenshot = JRoute::_(
						'index.php?option=com_kinoarhiv&task=media.view&element=trailer&content=image&id=' . $id .
						'&item_id=' . $value->id . '&fa=' . urlencode($value->fs_alias) . '&fn=' . $value->screenshot . '&format=raw&Itemid=' . $itemid
					);
				}

				if ($throttle_video_enable == 0)
				{
					$_fs_alias = rawurlencode($value->fs_alias);

					if (StringHelper::substr($params->get('media_trailers_root_www'), 0, 1) == '/')
					{
						// $value->path is an URL, $path is a root path to the files
						$value->path = JUri::base() . StringHelper::substr($params->get('media_trailers_root_www'), 1) . '/'
							. $_fs_alias . '/' . $id . '/';
						$path = JPATH_ROOT . '/' . StringHelper::substr($params->get('media_trailers_root_www'), 1)
							. '/' . $value->fs_alias . '/' . $id . '/';
					}
					else
					{
						$value->path = $params->get('media_trailers_root_www') . '/' . $_fs_alias . '/' . $id . '/';
						$path = JPATH_ROOT . '/' . $params->get('media_trailers_root_www') . '/' . $value->fs_alias . '/' . $id . '/';
					}
				}
				else
				{
					$value->path = 'index.php?option=com_kinoarhiv&task=media.view&element=trailer&id=' . $id .
						'&item_id=' . $value->id . '&fa=' . urlencode($value->fs_alias) . '&format=raw&Itemid=' . $itemid;
					$path = JPATH_ROOT . '/' . $params->get('media_trailers_root_www') . '/' . $value->fs_alias . '/' . $id . '/';
				}

				$value->files['video'] = json_decode($value->filename, true);
				$value->files['video_links'] = array();

				if (count($value->files['video']) > 0)
				{
					foreach ($value->files['video'] as $i => $val)
					{
						if ($throttle_video_enable == 0)
						{
							$value->files['video'][$i]['src'] = $value->path . $val['src'];

							// Check video extentions
							if (!in_array(strtolower(JFile::getExt($val['src'])), $allowed_formats))
							{
								$value->files['video_links'][] = $value->files['video'][$i];
								unset($value->files['video'][$i]);
							}
						}
						else
						{
							$value->files['video'][$i]['src'] = JRoute::_($value->path . '&content=video&fn=' . $val['src'] . '&' . JSession::getFormToken() . '=1');
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

				$tr_resolution = explode('x', $resolution);
				$tr_height = $tr_resolution[1];
				$value->player_height = floor(($tr_height * $result->player_width) / $tr_resolution[0]);

				// Set default aspect ratio if it's not set
				if ($value->dar == '')
				{
					$value->dar = '16:9';
				}

				// Check if subtitle file exists
				$_subtitles = json_decode($value->_subtitles, true);

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
							if ($throttle_video_enable == 0)
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
				$_chapters = json_decode($value->_chapters, true);

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
							if ($throttle_video_enable == 0)
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
	 */
	public function getTrailer($id = null, $type = null)
	{
		jimport('joomla.filesystem.file');

		$db = $this->getDbo();
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$lang = JFactory::getLanguage();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$id = is_null($id) ? $app->input->get('id', null, 'int') : $id;
		$type = is_null($type) ? $type = $app->input->get('type', '') : $type;
		$itemid = $app->input->get('Itemid', 0, 'int');
		$throttle_img_enable = $params->get('throttle_image_enable', 0);
		$throttle_video_enable = $params->get('throttle_video_enable', 0);
		$allowed_formats = array('mp4', 'webm', 'ogv', 'flv');

		if ($type == 'movie')
		{
			if ($params->get('allow_guest_watch') != 1)
			{
				return array();
			}

			$is_movie = 1;
		}
		else
		{
			$is_movie = 0;
		}

		$query = $db->getQuery(true)
			->select(
				$db->quoteName(
					array('tr.id', 'tr.title', 'tr.embed_code', 'tr.screenshot', 'tr.urls', 'tr.filename', 'tr.resolution',
						'tr.dar', 'tr.duration', 'tr._subtitles', 'tr._chapters', 'm.title', 'm.year', 'm.alias', 'm.fs_alias'
					)
				)
			)
			->from($db->quoteName('#__ka_trailers', 'tr'))
			->join('LEFT', $db->quoteName('#__ka_movies', 'm') . ' ON m.id = tr.movie_id')
			->where('tr.movie_id = ' . (int) $id . ' AND tr.state = 1 AND tr.access IN (' . $groups . ')')
			->where('tr.language IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ') AND tr.is_movie = ' . $is_movie)
			->where('tr.frontpage = 1')
			->setLimit(1, 0);

		$db->setQuery($query);
		$result = $db->loadObject();

		if (count($result) < 1)
		{
			return array();
		}

		$result->player_width = $params->get('player_width');

		// Just empty element
		$result->path = '';

		if (!empty($result->urls))
		{
			$urls_arr = explode("\n", $result->urls);

			if (count($urls_arr) > 0)
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

				$result->files['video'] = array();
				$result->files['subtitles'] = array();
				$result->files['chapters'] = array();
				$result->files['video_links'] = array();

				foreach ($urls_arr as $v)
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
									$in_player = true;
								}
								else
								{
									$in_player = false;
								}
							}
							else
							{
								$in_player = false;
							}

							$resolution = (isset($m['resolution']) && !empty($m['resolution'])) ? $m['resolution'] : '';
							$kind = (isset($m['kind']) && !empty($m['kind'])) ? $m['kind'] : '';
							$srclang = (isset($m['srclang']) && !empty($m['srclang'])) ? $m['srclang'] : '';
							$label = (isset($m['label']) && !empty($m['label'])) ? $m['label'] : '';
							$default = (isset($m['default']) && !empty($m['default'])) ? true : false;

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

							$tr_resolution = explode('x', $resolution);
							$tr_height = $tr_resolution[1];
							$result->player_height = floor(($tr_height * $result->player_width) / $tr_resolution[0]);

							if ($kind == '')
							{
								if ($in_player === true)
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
			if ($throttle_img_enable == 0)
			{
				$checking_path = JPath::clean(
					$params->get('media_trailers_root') . '/' . $result->fs_alias . '/' . $id . '/' . $result->screenshot
				);

				if (!is_file($checking_path))
				{
					$result->screenshot = JUri::base() . 'components/com_kinoarhiv/assets/themes/component/'
						. $params->get('ka_theme') . '/images/video_off.png';
				}
				else
				{
					$img_fs_alias = rawurlencode($result->fs_alias);

					if (StringHelper::substr($params->get('media_trailers_root_www'), 0, 1) == '/')
					{
						$result->screenshot = JUri::base() . StringHelper::substr($params->get('media_trailers_root_www'), 1) . '/'
							. $img_fs_alias . '/' . $id . '/' . $result->screenshot;
					}
					else
					{
						$result->screenshot = $params->get('media_trailers_root_www') . '/' . $img_fs_alias . '/' . $id . '/' . $result->screenshot;
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

			if ($throttle_video_enable == 0)
			{
				$video_fs_alias = rawurlencode($result->fs_alias);

				if (StringHelper::substr($params->get('media_trailers_root_www'), 0, 1) == '/')
				{
					// $result->path is an URL, $path is a root path to the files
					$result->path = JUri::base() . StringHelper::substr($params->get('media_trailers_root_www'), 1) . '/'
						. $video_fs_alias . '/' . $id . '/';
					$path = JPATH_ROOT . '/' . StringHelper::substr($params->get('media_trailers_root_www'), 1)
						. '/' . $result->fs_alias . '/' . $id . '/';
				}
				else
				{
					$result->path = $params->get('media_trailers_root_www') . '/' . $video_fs_alias . '/' . $id . '/';
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

			$result->files['video'] = json_decode($result->filename, true);
			$result->files['video_links'] = array();

			// Checking video extentions
			if (count($result->files['video']) > 0)
			{
				foreach ($result->files['video'] as $i => $val)
				{
					if ($throttle_video_enable == 0)
					{
						$result->files['video'][$i]['src'] = $result->path . $val['src'];

						// Check video extentions
						if (!in_array(strtolower(JFile::getExt($val['src'])), $allowed_formats))
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

			$tr_resolution = explode('x', $resolution);
			$tr_height = $tr_resolution[1];
			$result->player_height = floor(($tr_height * $result->player_width) / $tr_resolution[0]);

			// Set default aspect ratio if it's not set
			if ($result->dar == '')
			{
				$result->dar = '16:9';
			}

			// Check if subtitle file exists
			$_subtitles = json_decode($result->_subtitles, true);

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
						if ($throttle_video_enable == 0)
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
			$_chapters = json_decode($result->_chapters, true);

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
						if ($throttle_video_enable == 0)
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
	 * @return boolean
	 */
	public function getTrailerAccessLevel($id)
	{
		$db = $this->getDbo();
		$user = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());

		$query = $db->getQuery(true)
			->select('COUNT(id)')
			->from($db->quoteName('#__ka_trailers'))
			->where('id = ' . (int) $id . ' AND state = 1 AND access IN (' . $groups . ')');

		$db->setQuery($query);
		$result = $db->loadResult();

		return $result < 1 ? false : true;
	}

	/**
	 * Get winned awards for movie
	 *
	 * @return object
	 */
	public function getAwards()
	{
		$db = $this->getDbo();
		$app = JFactory::getApplication();
		$id = $app->input->get('id', 0, 'int');

		if ($id == 0)
		{
			return array();
		}

		$result = $this->getMovieData();

		$query = $db->getQuery(true)
			->select('a.desc, a.year, aw.id, aw.title AS aw_title, aw.desc AS aw_desc')
			->from($db->quoteName('#__ka_rel_awards', 'a'))
			->join('LEFT', $db->quoteName('#__ka_awards', 'aw') . ' ON aw.id = a.award_id')
			->where('type = 0 AND item_id = ' . (int) $id)
			->order('year ASC');

		$db->setQuery($query);
		$result->awards = $db->loadObjectList();

		return $result;
	}

	/**
	 * Get the list of albums and their tracks for movie
	 *
	 * @return object
	 */
	public function getSoundtracks()
	{
		$db = $this->getDbo();
		$app = JFactory::getApplication();
		$id = $app->input->get('id', 0, 'int');

		$result = $this->getMovieData();

		$result->soundtracks = array();

		return $result;
	}

	/**
	 * Build list of filters by dimensions for gallery
	 *
	 * @return  array
	 */
	public function getDimensionFilters()
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$id = $app->input->get('id', 0, 'int');
		$page = $app->input->get('page', null, 'cmd');
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
			$result = $db->loadAssocList();
		}

		return $result;
	}

	/**
	 * Method to process user votes
	 *
	 * @return array
	 */
	public function voted()
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$user = JFactory::getUser();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$movie_id = $app->input->get('id', 0, 'int');
		$value = $app->input->get('value', -1, 'int');
		$error_message = array('success' => false, 'message' => JText::_('COM_KA_REQUEST_ERROR'));

		$query_attribs = $db->getQuery(true)
			->select('attribs')
			->from($db->quoteName('#__ka_movies'))
			->where('id = ' . (int) $movie_id);

		$db->setQuery($query_attribs);
		$attribs = json_decode($db->loadResult());

		if (($attribs->allow_votes == '' && $params->get('allow_votes') == 1) || $attribs->allow_votes == 1)
		{
			if ($value == '-1')
			{
				// Something went wrong
				$result = $error_message;
			}
			elseif ($value == 0)
			{
				// Remove vote and update rating
				$query_vote = $db->getQuery(true)
					->select('v.vote, r.rate_loc, r.rate_sum_loc')
					->from($db->quoteName('#__ka_user_votes', 'v'))
					->join('LEFT', $db->quoteName('#__ka_movies', 'r') . ' ON r.id = v.movie_id')
					->where('movie_id = ' . (int) $movie_id . ' AND uid = ' . $user->get('id'));

				$db->setQuery($query_vote);
				$vote_result = $db->loadObject();

				if (!empty($vote_result->vote))
				{
					$rate_loc = $vote_result->rate_loc - 1;
					$rate_sum_loc = $vote_result->rate_sum_loc - $vote_result->vote;
					$rate_loc_rounded = round($vote_result->rate_sum_loc / $vote_result->rate_loc, 0);

					try
					{
						$query = $db->getQuery(true)
							->update($db->quoteName('#__ka_movies'))
							->set("rate_loc = '" . (int) $rate_loc . "', rate_sum_loc = '" . (int) $rate_sum_loc . "'")
							->set("rate_loc_rounded = '" . (int) $rate_loc_rounded . "'")
							->where('id = ' . (int) $movie_id);

						$db->setQuery($query);
						$m_query = $db->execute();

						$query = $db->getQuery(true)
							->delete($db->quoteName('#__ka_user_votes'))
							->where('movie_id = ' . (int) $movie_id . ' AND uid = ' . $user->get('id'));

						$db->setQuery($query);
						$v_query = $db->execute();

						if ($m_query && $v_query)
						{
							$result = array('success' => true, 'message' => JText::_('COM_KA_RATE_REMOVED'));
						}
						else
						{
							$result = $error_message;
						}
					}
					catch (Exception $e)
					{
						$result = $error_message;
						KAComponentHelper::eventLog($e->getMessage());
					}
				}
				else
				{
					$result = array('success' => false, 'message' => JText::_('COM_KA_RATE_NOTRATED'));
				}
			}
			else
			{
				// Update rating and insert or update user vote in #__ka_user_votes
				// Check if value in range from 1 to 'vote_summ_num'
				if ($value >= 1 || $value <= $params->get('vote_summ_num'))
				{
					// At first we check if user allready voted and when just update the rating and vote
					$query = $db->getQuery(true)
						->select('v.vote, r.rate_loc, r.rate_sum_loc')
						->from($db->quoteName('#__ka_user_votes', 'v'))
						->join('LEFT', $db->quoteName('#__ka_movies', 'r') . ' ON r.id = v.movie_id')
						->where('movie_id = ' . (int) $movie_id . ' AND uid = ' . $user->get('id'));

					$db->setQuery($query);
					$vote_result = $db->loadObject();

					if (!empty($vote_result->vote))
					{
						// User allready voted
						$rate_sum_loc = ($vote_result->rate_sum_loc - $vote_result->vote) + $value;
						$rate_loc_rounded = round($rate_sum_loc / $vote_result->rate_loc, 0);

						try
						{
							$query = $db->getQuery(true)
								->update($db->quoteName('#__ka_movies'))
								->set("rate_sum_loc = '" . (int) $rate_sum_loc . "', rate_loc_rounded = '" . (int) $rate_loc_rounded . "'")
								->where('id = ' . (int) $movie_id);

							$db->setQuery($query);
							$m_query = $db->execute();

							$query = $db->getQuery(true)
								->update($db->quoteName('#__ka_user_votes'))
								->set("vote = '" . (int) $value . "', _datetime = NOW()")
								->where('movie_id = ' . (int) $movie_id . ' AND uid = ' . $user->get('id'));

							$db->setQuery($query);
							$v_query = $db->execute();

							if ($m_query && $v_query)
							{
								$result = array('success' => true, 'message' => JText::_('COM_KA_RATE_RATED'));
							}
							else
							{
								$result = $error_message;
							}
						}
						catch (Exception $e)
						{
							$result = $error_message;
							KAComponentHelper::eventLog($e->getMessage());
						}
					}
					else
					{
						$query = $db->getQuery(true)
							->select('rate_loc, rate_sum_loc')
							->from($db->quoteName('#__ka_movies'))
							->where('id = ' . (int) $movie_id);

						$db->setQuery($query);
						$vote_result = $db->loadObject();

						$rate_loc = (int) $vote_result->rate_loc + 1;
						$rate_sum_loc = (int) $vote_result->rate_sum_loc + (int) $value;
						$rate_loc_rounded = round($rate_sum_loc / $rate_loc, 0);

						try
						{
							$query = $db->getQuery(true)
								->update($db->quoteName('#__ka_movies'))
								->set("rate_loc = '" . (int) $rate_loc . "', rate_sum_loc = '" . (int) $rate_sum_loc . "'")
								->set("rate_loc_rounded = '" . (int) $rate_loc_rounded . "'")
								->where('id = ' . (int) $movie_id);

							$db->setQuery($query);
							$m_query = $db->execute();

							$query = $db->getQuery(true)
								->insert($db->quoteName('#__ka_user_votes'))
								->columns($db->quoteName(array('uid', 'movie_id', 'vote', '_datetime')))
								->values("'" . $user->get('id') . "', '" . $movie_id . "', '" . $value . "', NOW()");

							$db->setQuery($query);
							$v_query = $db->execute();

							if ($m_query && $v_query)
							{
								$result = array('success' => true, 'message' => JText::_('COM_KA_RATE_RATED'));
							}
							else
							{
								$result = $error_message;
							}
						}
						catch (Exception $e)
						{
							$result = $error_message;
							KAComponentHelper::eventLog($e->getMessage());
						}
					}
				}
				else
				{
					$result = $error_message;
				}
			}
		}
		else
		{
			$result = $error_message;
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
		$db = $this->getDbo();
		$id = $app->input->get('id', 0, 'int');
		$page = $app->input->get('page', 'reviews', 'cmd');
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
				->select('rev.id, rev.uid, rev.movie_id, rev.review, rev.created, rev.type, rev.state, u.name, u.username')
				->from($db->quoteName('#__ka_reviews', 'rev'))
				->join('LEFT', $db->quoteName('#__users', 'u') . ' ON u.id = rev.uid')
				->where('movie_id = ' . (int) $id . ' AND rev.state = 1 AND u.id != 0')
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

			$value = $app->getUserStateFromRequest($this->context . '.limitstart', 'limitstart', 0);
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
		$app = JFactory::getApplication();
		$old_state = $app->getUserState($key);
		$cur_state = (!is_null($old_state)) ? $old_state : $default;
		$new_state = $app->input->get($request, null, $type);

		if (($cur_state != $new_state) && ($resetPage))
		{
			$app->input->set('limitstart', 0);
		}

		if ($new_state !== null)
		{
			$app->setUserState($key, $new_state);
		}
		else
		{
			$new_state = $cur_state;
		}

		return $new_state;
	}
}
