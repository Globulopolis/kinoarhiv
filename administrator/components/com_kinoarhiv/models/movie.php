<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('_JEXEC') or die;

use Joomla\String\String;

JLoader::register('KADatabaseHelper', JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'database.php');

/**
 * Movie item class
 *
 * @since  3.0
 */
class KinoarhivModelMovie extends JModelForm
{
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
		$form = $this->loadForm('com_kinoarhiv.movie', 'movie', array('control' => 'form', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		$id = JFactory::getApplication()->input->get('id', array(), 'array');
		$id = (isset($id[0]) && !empty($id[0])) ? $id[0] : 0;
		$user = JFactory::getUser();

		if ($id != 0 && (!$user->authorise('core.edit.state', 'com_kinoarhiv.movie.' . (int) $id))
			|| ($id == 0 && !$user->authorise('core.edit.state', 'com_kinoarhiv')))
		{
			$form->setFieldAttribute('ordering', 'disabled', 'true', 'movie');
			$form->setFieldAttribute('state', 'disabled', 'true', 'movie');
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  array    The default data is an empty array.
	 *
	 * @since   3.0
	 */
	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState('com_kinoarhiv.movies.' . JFactory::getUser()->id . '.edit_data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since   3.0
	 */
	public function getItem()
	{
		$app = JFactory::getApplication();
		$lang = JFactory::getLanguage();
		$db = $this->getDBO();
		$tmpl = $app->input->get('template', '', 'string');
		$id = $app->input->get('id', array(), 'array');

		if ($tmpl == 'crew_edit')
		{
			$movie_id = $app->input->get('movie_id', 0, 'int');
			$name_id = $app->input->get('name_id', 0, 'int');
			$query = $db->getQuery(true);

			$query->select($db->quoteName(array('name_id', 'role', 'dub_id', 'is_actors', 'voice_artists', 'is_directors')))
				->select($db->quoteName('ordering', 'r_ordering') . ',' . $db->quoteName('desc', 'r_desc'))
				->from($db->quoteName('#__ka_rel_names'))
				->where($db->quoteName('name_id') . ' = ' . (int) $name_id . ' AND ' . $db->quoteName('movie_id') . ' = ' . (int) $movie_id);

			$db->setQuery($query);
			$result = $db->loadObject();

			if (!empty($result))
			{
				$result->type = $app->input->get('career_id', 0, 'int');
			}
		}
		elseif ($tmpl == 'awards_edit')
		{
			$award_id = $app->input->get('award_id', 0, 'int');
			$query = $db->getQuery(true);

			$query->select(
				$db->quoteName('id', 'rel_aw_id') . ',' . $db->quoteName('item_id') . ',' . $db->quoteName('award_id') . ',' .
				$db->quoteName('desc', 'aw_desc') . ',' . $db->quoteName('year', 'aw_year')
				)
				->from($db->quoteName('#__ka_rel_awards'))
				->where('id = ' . (int) $award_id);

			$db->setQuery($query);
			$result = $db->loadObject();
		}
		elseif ($tmpl == 'premieres_edit')
		{
			$premiere_id = $app->input->get('premiere_id', 0, 'int');
			$query = $db->getQuery(true);

			$query->select(
				$db->quoteName('p.id', 'premiere_id') . ',' . $db->quoteName('p.vendor_id', 'p_vendor_id') . ',' .
				$db->quoteName('p.premiere_date', 'p_premiere_date') . ',' . $db->quoteName('p.country_id', 'p_country_id') . ',' .
				$db->quoteName('p.info', 'p_info') . ',' . $db->quoteName('p.language', 'p_language') . ',' .
				$db->quoteName('p.ordering', 'p_ordering')
				)
				->from($db->quoteName('#__ka_premieres', 'p'))
				->join('LEFT', $db->quoteName('#__languages', 'l') . ' ON l.lang_code = p.language')
				->where('p.id = ' . (int) $premiere_id);

			$db->setQuery($query);
			$result = $db->loadObject();
		}
		elseif ($tmpl == 'releases_edit')
		{
			$release_id = $app->input->get('release_id', 0, 'int');
			$query = $db->getQuery(true);

			$query->select(
				$db->quoteName('r.id', 'release_id') . ',' . $db->quoteName('r.vendor_id', 'r_vendor_id') . ',' .
				$db->quoteName('r.release_date', 'r_release_date') . ',' . $db->quoteName('r.country_id', 'r_country_id') . ',' .
				$db->quoteName('r.media_type', 'r_media_type') . ',' . $db->quoteName('r.desc', 'r_desc') . ',' .
				$db->quoteName('r.language', 'r_language') . ',' . $db->quoteName('r.ordering', 'r_ordering')
				)
				->from($db->quoteName('#__ka_releases', 'r'))
				->join('LEFT', $db->quoteName('#__languages', 'l') . ' ON l.lang_code = r.language')
				->where('r.id = ' . (int) $release_id);

			$db->setQuery($query);
			$result = $db->loadObject();
		}
		else
		{
			$result = array('movie' => (object) array());

			if (count($id) == 0 || empty($id) || empty($id[0]))
			{
				return $result;
			}

			$query = $db->getQuery(true);

			$query->select(
				$db->quoteName(
					array('m.id', 'm.asset_id', 'm.parent_id', 'm.title', 'm.alias', 'm.fs_alias', 'm.introtext', 'm.plot',
						'm.desc', 'm.known', 'm.year', 'm.slogan', 'm.budget', 'm.age_restrict', 'm.ua_rate', 'm.mpaa',
						'm.length', 'm.rate_loc', 'm.rate_sum_loc', 'm.imdb_votesum', 'm.imdb_votes', 'm.imdb_id',
						'm.kp_votesum', 'm.kp_votes', 'm.kp_id', 'm.rate_fc', 'm.rottentm_id', 'm.metacritics',
						'm.metacritics_id', 'm.rate_custom', 'm.rate_loc_rounded', 'm.rate_imdb_rounded', 'm.rate_kp_rounded',
						'm.urls', 'm.buy_urls', 'm.attribs', 'm.created', 'm.created_by', 'm.modified', 'm.modified_by',
						'm.publish_up', 'm.publish_down', 'm.state', 'm.ordering', 'm.metakey', 'm.metadesc', 'm.access',
						'm.metadata', 'm.language'
					)
				)
			)
			->select($db->quoteName('m.fs_alias', 'fs_alias_orig'))
			->from($db->quoteName('#__ka_movies', 'm'))
			->where('m.id = ' . (int) $id[0]);

			// Join over the language
			$query->select($db->quoteName('l.title', 'language_title'))
				->join('LEFT', $db->quoteName('#__languages', 'l') . ' ON l.lang_code = m.language');

			// Join over gallery item
			$query->select($db->quoteName('g.id', 'gid') . ',' . $db->quoteName('g.filename'))
				->join('LEFT', $db->quoteName('#__ka_movies_gallery', 'g') . ' ON g.movie_id = m.id AND g.type = 2 AND g.poster_frontpage = 1');

			$db->setQuery($query);
			$result['movie'] = $db->loadObject();

			$result['movie']->genres = $this->getGenres();
			$result['movie']->genres_orig = implode(',', $result['movie']->genres['ids']);
			$result['movie']->countries = $this->getCountries();
			$result['movie']->tags = $this->getTags();

			if (!empty($result['movie']->attribs))
			{
				$result['attribs'] = json_decode($result['movie']->attribs);
			}

			if (empty($result['movie']->fs_alias))
			{
				$result['movie']->fs_alias = strtolower($lang->transliterate(String::substr($result['movie']->alias, 0, 1)));
			}
		}

		return $result;
	}

	/**
	 * Method to change the published state of one or more records.
	 *
	 * @param   boolean  $isUnpublish  Action state
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.0
	 */
	public function publish($isUnpublish)
	{
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$ids = $app->input->get('id', array(), 'array');
		$state = $isUnpublish ? 0 : 1;
		$query = $db->getQuery(true);

		$query->update($db->quoteName('#__ka_movies'))
			->set($db->quoteName('state') . ' = ' . (int) $state)
			->where($db->quoteName('id') . ' IN (' . implode(',', $ids) . ')');

		$db->setQuery($query);

		try
		{
			$db->execute();

			return true;
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}
	}

	/**
	 * Method to get a list of countries.
	 *
	 * @return  object
	 *
	 * @since   3.0
	 */
	protected function getCountries()
	{
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$id = $app->input->get('id', array(), 'array');
		$result = array('data' => array(), 'ids' => array());
		$query = $db->getQuery(true);

		$query->select(
			$db->quoteName('c.id') . ',' . $db->quoteName('c.name', 'title') . ',' . $db->quoteName('c.code') . ',' .
			$db->quoteName('t.ordering')
			)
			->from($db->quoteName('#__ka_countries', 'c'))
			->join('LEFT', $db->quoteName('#__ka_rel_countries', 't') . ' ON t.country_id = c.id AND t.movie_id = ' . (int) $id[0]);

		$subquery = $db->getQuery(true)
			->select($db->quoteName('country_id'))
			->from($db->quoteName('#__ka_rel_countries'))
			->where($db->quoteName('movie_id') . ' = ' . (int) $id[0]);

		$query->where($db->quoteName('id') . ' IN (' . $subquery . ')')
			->order($db->quoteName('t.ordering') . ' ASC');

		$db->setQuery($query);
		$result['data'] = $db->loadObjectList();

		foreach ($result['data'] as $value)
		{
			$result['ids'][] = $value->id;
		}

		return $result;
	}

	/**
	 * Method to get a list of genres.
	 *
	 * @return  object
	 *
	 * @since   3.0
	 */
	protected function getGenres()
	{
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$id = $app->input->get('id', array(), 'array');
		$result = array('data' => array(), 'ids' => array());
		$query = $db->getQuery(true);

		$query->select($db->quoteName('g.id') . ',' . $db->quoteName('g.name', 'title') . ',' . $db->quoteName('t.ordering'))
			->from($db->quoteName('#__ka_genres', 'g'))
			->join('LEFT', $db->quoteName('#__ka_rel_genres', 't') . ' ON t.genre_id = g.id AND t.movie_id = ' . (int) $id[0]);

		$subquery = $db->getQuery(true)
			->select($db->quoteName('genre_id'))
			->from($db->quoteName('#__ka_rel_genres'))
			->where($db->quoteName('movie_id') . ' = ' . (int) $id[0]);

		$query->where($db->quoteName('id') . ' IN (' . $subquery . ')')
			->order($db->quoteName('t.ordering') . ' ASC');

		$db->setQuery($query);
		$result['data'] = $db->loadObjectList();

		foreach ($result['data'] as $value)
		{
			$result['ids'][] = $value->id;
		}

		return $result;
	}

	/**
	 * Method to get a list of tags.
	 *
	 * @return  object
	 *
	 * @since   3.0
	 */
	protected function getTags()
	{
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$id = $app->input->get('id', array(), 'array');

		if (!empty($id[0]))
		{
			$query = $db->getQuery(true);

			$query->select($db->quoteName('metadata'))
				->from($db->quoteName('#__ka_movies'))
				->where($db->quoteName('id') . ' = ' . (int) $id[0]);

			$db->setQuery($query);
			$metadata = $db->loadResult();
			$meta_arr = json_decode($metadata);

			if (is_null($meta_arr) || count($meta_arr->tags) == 0)
			{
				return array('data' => array(), 'ids' => '');
			}

			$query = $db->getQuery(true);

			$query->select($db->quoteName(array('id', 'title')))
				->from($db->quoteName('#__tags'))
				->where($db->quoteName('id') . ' IN (' . implode(',', $meta_arr->tags) . ')')
				->order($db->quoteName('lft') . ' ASC');

			$db->setQuery($query);
			$result['data'] = $db->loadObjectList();

			foreach ($result['data'] as $value)
			{
				$result['ids'][] = $value->id;
			}
		}
		else
		{
			$result = array('data' => array(), 'ids' => '');
		}

		return $result;
	}

	/**
	 * Method to save an item data.
	 *
	 * @param   object  $data  Form data
	 *
	 * @return  boolean
	 *
	 * @throws  Exception
	 * @since   3.0
	 */
	public function save($data)
	{
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$user = JFactory::getUser();
		$date = JFactory::getDate();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$id = $app->input->get('id', 0, 'int');
		$attribs = $data['attribs'];
		$data = $data['movie'];
		$title = trim($data['title']);
		$data['modified'] = $date->toSql();

		// Check if movie with this title allready exists
		if (empty($id))
		{
			$query = $db->getQuery(true);

			$query->select('COUNT(id)')
				->from($db->quoteName('#__ka_movies'))
				->where($db->quoteName('title') . " LIKE '" . $db->escape($title) . "%'");

			$db->setQuery($query);
			$count = $db->loadResult();

			if ($count > 0)
			{
				$this->setError(JText::_('COM_KA_MOVIES_EXISTS'));

				$app->setUserState('com_kinoarhiv.movies.' . $user->id . '.data',
					array(
						'success' => false,
						'message' => JText::_('COM_KA_MOVIES_EXISTS')
					)
				);

				return false;
			}

			if (!(int) $data['created'])
			{
				$data['created'] = $date->toSql();
			}

			if (empty($data['created_by']))
			{
				$data['created_by'] = $user->get('id');
			}
		}
		else
		{
			$data['modified_by'] = $user->get('id');
		}

		// Automatic handling of alias for empty fields
		if (in_array($app->input->get('task'), array('apply', 'save', 'save2new')) && (!isset($data['id']) || (int) $data['id'] == 0))
		{
			if ($data['alias'] == null)
			{
				if (JFactory::getConfig()->get('unicodeslugs') == 1)
				{
					$data['alias'] = JFilterOutput::stringURLUnicodeSlug($data['title']);
				}
				else
				{
					$data['alias'] = JFilterOutput::stringURLSafe($data['title']);
				}
			}

			if (empty($data['fs_alias']))
			{
				$data['fs_alias'] = rawurlencode(String::substr($data['alias'], 0, 1));
			}
		}

		$created_by = empty($data['created_by']) ? $user->get('id') : $data['created_by'];
		$modified_by = empty($data['modified_by']) ? $user->get('id') : $data['modified_by'];
		$metadata = array(
			'tags'   => json_decode('[' . $data['tags'] . ']', true),
			'robots' => $data['robots']
		);
		$attribs = json_encode($attribs);
		$year = str_replace(' ', '', $data['year']);
		$rate_loc_rounded = ((int) $data['rate_loc'] > 0 && (int) $data['rate_sum_loc'] > 0) ? round($data['rate_sum_loc'] / $data['rate_loc'], 0) : 0;
		$rate_imdb_rounded = $data['imdb_votesum'] > 0 ? round($data['imdb_votesum'], 0) : 0;
		$rate_kp_rounded = $data['kp_votesum'] > 0 ? round($data['kp_votesum'], 0) : 0;
		$query = $db->getQuery(true);

		if (empty($id))
		{
			$query = $db->getQuery(true);

			$query->insert($db->quoteName('#__ka_movies'))
				->columns(
					$db->quoteName(
						array('id', 'asset_id', 'parent_id', 'title', 'alias', 'fs_alias', 'introtext', 'plot', 'desc',
							'known', 'year', 'slogan', 'budget', 'age_restrict', 'ua_rate', 'mpaa', 'length', 'rate_loc',
							'rate_sum_loc', 'imdb_votesum', 'imdb_votes', 'imdb_id', 'kp_votesum', 'kp_votes', 'kp_id',
							'rate_fc', 'rottentm_id', 'metacritics', 'metacritics_id', 'rate_custom', 'rate_loc_rounded',
							'rate_imdb_rounded', 'rate_kp_rounded', 'urls', 'buy_urls', 'attribs', 'created', 'created_by',
							'modified', 'modified_by', 'publish_up', 'publish_down', 'state', 'ordering', 'metakey',
							'metadesc', 'access', 'metadata', 'language'
						)
					)
				)
				->values("'', '0', '" . (int) $data['parent_id'] . "', '" . $db->escape($title) . "', '" . $data['alias'] . "'")
				->values("'" . $data['fs_alias'] . "', '', '" . $db->escape($data['plot']) . "', '" . $db->escape($data['desc']) . "'")
				->values("'" . $db->escape($data['known']) . "', '" . $db->escape($year) . "', '" . $db->escape($data['slogan']) . "'")
				->values("'" . $data['budget'] . "', '" . $data['age_restrict'] . "', '" . $data['ua_rate'] . "'")
				->values("'" . $data['mpaa'] . "', '" . $data['length'] . "', '" . (int) $data['rate_loc'] . "'")
				->values("'" . (int) $data['rate_sum_loc'] . "', '" . $data['imdb_votesum'] . "', '" . (int) $data['imdb_votes'] . "'")
				->values("'" . (int) $data['imdb_id'] . "', '" . $data['kp_votesum'] . "', '" . (int) $data['kp_votes'] . "'")
				->values("'" . (int) $data['kp_id'] . "', '" . (int) $data['rate_fc'] . "', '" . $data['rottentm_id'] . "'")
				->values("'" . (int) $data['metacritics'] . "', '" . $data['metacritics_id'] . "', '" . $db->escape($data['rate_custom']) . "'")
				->values("'" . $rate_loc_rounded . "', '" . $rate_imdb_rounded . "', '" . $rate_kp_rounded . "'")
				->values("'" . $db->escape($data['urls']) . "', '" . $db->escape($data['buy_urls']) . "', '" . $attribs . "'")
				->values("'" . $data['created'] . "', '" . $created_by . "', '" . $data['modified'] . "', '" . $modified_by . "'")
				->values("'" . $data['publish_up'] . "', '" . $data['publish_down'] . "', '" . $data['state'] . "'")
				->values("'" . (int) $data['ordering'] . "', '" . $db->escape($data['metakey']) . "', '" . $db->escape($data['metadesc']) . "'")
				->values("'" . (int) $data['access'] . "', '" . json_encode($metadata) . "', '" . $data['language'] . "'");
		}
		else
		{
			$query->update($db->quoteName('#__ka_movies'))
				->set($db->quoteName('parent_id') . " = '" . (int) $data['parent_id'] . "'," . $db->quoteName('title') . " = '" . $db->escape($title) . "'")
				->set($db->quoteName('alias') . " = '" . $data['alias'] . "'," . $db->quoteName('fs_alias') . " = '" . $data['fs_alias'] . "'," . $db->quoteName('plot') . " = '" . $db->escape($data['plot']) . "'")
				->set($db->quoteName('desc') . " = '" . $db->escape($data['desc']) . "'," . $db->quoteName('known') . " = '" . $db->escape($data['known']) . "'")
				->set($db->quoteName('year') . " = '" . (int) $year . "'," . $db->quoteName('slogan') . " = '" . $db->escape($data['slogan']) . "'")
				->set($db->quoteName('budget') . " = '" . $data['budget'] . "'," . $db->quoteName('age_restrict') . " = '" . $data['age_restrict'] . "'")
				->set($db->quoteName('ua_rate') . " = '" . $data['ua_rate'] . "'," . $db->quoteName('mpaa') . " = '" . $data['mpaa'] . "'")
				->set($db->quoteName('length') . " = '" . $data['length'] . "'," . $db->quoteName('rate_loc') . " = '" . (int) $data['rate_loc'] . "'")
				->set($db->quoteName('rate_sum_loc') . " = '" . (int) $data['rate_sum_loc'] . "'," . $db->quoteName('imdb_votesum') . " = '" . $data['imdb_votesum'] . "'")
				->set($db->quoteName('imdb_votes') . " = '" . (int) $data['imdb_votes'] . "'," . $db->quoteName('imdb_id') . " = '" . (int) $data['imdb_id'] . "'")
				->set($db->quoteName('kp_votesum') . " = '" . $data['kp_votesum'] . "'," . $db->quoteName('kp_votes') . " = '" . (int) $data['kp_votes'] . "'")
				->set($db->quoteName('kp_id') . " = '" . (int) $data['kp_id'] . "'," . $db->quoteName('rate_fc') . " = '" . (int) $data['rate_fc'] . "'")
				->set($db->quoteName('rottentm_id') . " = '" . $data['rottentm_id'] . "'," . $db->quoteName('metacritics') . " = '" . (int) $data['metacritics'] . "'")
				->set($db->quoteName('metacritics_id') . " = '" . $data['metacritics_id'] . "'," . $db->quoteName('rate_custom') . " = '" . $db->escape($data['rate_custom']) . "'")
				->set($db->quoteName('rate_loc_rounded') . " = '" . $rate_loc_rounded . "'," . $db->quoteName('rate_imdb_rounded') . " = '" . $rate_imdb_rounded . "'")
				->set($db->quoteName('rate_kp_rounded') . " = '" . $rate_kp_rounded . "'," . $db->quoteName('urls') . " = '" . $db->escape($data['urls']) . "'")
				->set($db->quoteName('buy_urls') . " = '" . $db->escape($data['buy_urls']) . "'")
				->set($db->quoteName('attribs') . " = '" . $attribs . "'," . $db->quoteName('created') . " = '" . $data['created'] . "'")
				->set($db->quoteName('created_by') . " = '" . $created_by . "'," . $db->quoteName('modified') . " = '" . $data['modified'] . "'")
				->set($db->quoteName('modified_by') . " = '" . $modified_by . "'," . $db->quoteName('publish_up') . " = '" . $data['publish_up'] . "'")
				->set($db->quoteName('publish_down') . " = '" . $data['publish_down'] . "'")
				->set($db->quoteName('state') . " = '" . $data['state'] . "'," . $db->quoteName('ordering') . " = '" . (int) $data['ordering'] . "'")
				->set($db->quoteName('metakey') . " = '" . $db->escape($data['metakey']) . "'," . $db->quoteName('metadesc') . " = '" . $db->escape($data['metadesc']) . "'")
				->set($db->quoteName('access') . " = '" . (int) $data['access'] . "'," . $db->quoteName('metadata') . " = '" . json_encode($metadata) . "'")
				->set($db->quoteName('language') . " = '" . $db->escape($data['language']) . "'")
				->where($db->quoteName('id') . ' = ' . (int) $id);
		}

		$db->setQuery($query);

		try
		{
			$db->execute();

			if (empty($id))
			{
				$id = $db->insertid();

				// Create access rules
				$query = $db->getQuery(true)
					->select($db->quoteName('id'))
					->from($db->quoteName('#__assets'))
					->where($db->quoteName('name') . " = 'com_kinoarhiv' AND " . $db->quoteName('parent_id') . " = 1");

				$db->setQuery($query);
				$parent_id = $db->loadResult();

				$query = $db->getQuery(true)
					->select('MAX(lft)+2 AS lft, MAX(rgt)+2 AS rgt')
					->from($db->quoteName('#__assets'));

				$db->setQuery($query);
				$lft_rgt = $db->loadObject();

				$query = $db->getQuery(true)
					->insert($db->quoteName('#__assets'))
					->columns($db->quoteName(array('id', 'parent_id', 'lft', 'rgt', 'level', 'name', 'title', 'rules')))
					->values("'', '" . $parent_id . "', '" . $lft_rgt->lft . "', '" . $lft_rgt->rgt . "', '2', 'com_kinoarhiv.movie." . $id . "', '" . $db->escape($data['title']) . "', '{}'");

				$db->setQuery($query);
				$db->execute();
				$asset_id = $db->insertid();

				$query = $db->getQuery(true)
					->update($db->quoteName('#__ka_movies'))
					->set($db->quoteName('asset_id') . " = '" . (int) $asset_id . "'")
					->where($db->quoteName('id') . " = " . (int) $id);

				$db->setQuery($query);
				$db->execute();
			}
			else
			{
				// Alias was changed? Move all linked items into new filesystem location.
				if ($data['fs_alias'] != $data['fs_alias_orig'])
				{
					$this->moveMediaItems($id, $data['fs_alias_orig'], $data['fs_alias'], $params);
				}
			}

			$this->updateTagMapping($data['tags'], $id);
			$this->createIntroText($data, $params, $id);
			$this->updateGenresStat($data['genres_orig'], $data['genres']);

			$app->setUserState('com_kinoarhiv.movies.data.' . $user->id . '.id', $id);
			$app->setUserState('com_kinoarhiv.movies.' . $user->id . '.data',
				array(
					'success' => true,
					'message' => JText::_('COM_KA_ITEMS_SAVE_SUCCESS'),
					'data'    => array('id' => $id, 'title' => $title)
				)
			);
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return true;
	}

	/**
	 * Create intro text for movie
	 *
	 * @param   array    $data    Movie info array
	 * @param   object   $params  Component parameters
	 * @param   integer  $id      Item ID
	 *
	 * @return  boolean
	 */
	protected function createIntroText($data, $params, $id)
	{
		$db = $this->getDBO();
		$query_result = true;
		$intro_countries = '';
		$intro_genres = '';
		$intro_directors = '';
		$intro_cast = '';

		// Proccess intro text for country IDs and store in relation table
		if (!empty($data['countries']))
		{
			$query = $db->getQuery(true)
				->select($db->quoteName(array('name', 'code')))
				->from($db->quoteName('#__ka_countries'))
				->where($db->quoteName('id') . ' IN (' . $data['countries'] . ')');

			$db->setQuery($query);
			$countries = $db->loadObjectList();

			$ln_str = count($countries) > 1 ? 'COM_KA_COUNTRIES' : 'COM_KA_COUNTRY';

			foreach ($countries as $cn)
			{
				$intro_countries .= '[cn=' . $cn->code . ']' . $cn->name . '[/cn], ';
			}

			$intro_countries = '<span class="cn-list">[country ln=' . $ln_str . ']: ' . String::substr($intro_countries, 0, -2) . '[/country]</span>';

			$countries_new_arr = explode(',', $data['countries']);

			$db->setDebug(true);
			$db->lockTable('#__ka_rel_countries');
			$db->transactionStart();

			$query = $db->getQuery(true)
				->delete($db->quoteName('#__ka_rel_countries'))
				->where($db->quoteName('movie_id') . ' = ' . (int) $id);

			$db->setQuery($query);
			$db->execute();

			foreach ($countries_new_arr as $ordering => $country_id)
			{
				$query = $db->getQuery(true);

				$query->insert($db->quoteName('#__ka_rel_countries'))
					->columns($db->quoteName(array('country_id', 'movie_id', 'ordering')))
					->values("'" . (int) $country_id . "', '" . (int) $id . "', '" . (int) $ordering . "'");

				$db->setQuery($query . ';');

				if ($db->execute() === false)
				{
					$query_result = false;
					break;
				}
			}

			if ($query_result === false)
			{
				$db->transactionRollback();
				$this->setError('Commit for "' . $db->getPrefix() . '_ka_rel_countries" failed!');
			}
			else
			{
				$db->transactionCommit();
			}

			$db->unlockTables();
			$db->setDebug(false);
		}

		// Proccess intro text for genres IDs and store in relation table
		if (!empty($data['genres']))
		{
			$query = $db->getQuery(true)
				->select($db->quoteName('name'))
				->from($db->quoteName('#__ka_genres'))
				->where($db->quoteName('id') . ' IN (' . $data['genres'] . ')');

			$db->setQuery($query);
			$genres = $db->loadObjectList();

			$ln_str = count($genres) > 1 ? 'COM_KA_GENRES' : 'COM_KA_GENRE';

			foreach ($genres as $genre)
			{
				$intro_genres .= $genre->name . ', ';
			}

			$intro_genres = '<span class="gn-list">[genres ln=' . $ln_str . ']: ' . String::substr($intro_genres, 0, -2) . '[/genres]</span>';

			$genres_new_arr = explode(',', $data['genres']);

			$db->setDebug(true);
			$db->lockTable('#__ka_rel_genres');
			$db->transactionStart();

			$query = $db->getQuery(true)
				->delete($db->quoteName('#__ka_rel_genres'))
				->where($db->quoteName('movie_id') . ' = ' . (int) $id);

			$db->setQuery($query);
			$db->execute();

			foreach ($genres_new_arr as $ordering => $genre_id)
			{
				$query = $db->getQuery(true);

				$query->insert($db->quoteName('#__ka_rel_genres'))
					->columns($db->quoteName(array('genre_id', 'movie_id', 'ordering')))
					->values("'" . (int) $genre_id . "', '" . (int) $id . "', '" . (int) $ordering . "'");

				$db->setQuery($query . ';');

				if ($db->execute() === false)
				{
					$query_result = false;
					break;
				}
			}

			if ($query_result === false)
			{
				$db->transactionRollback();
				$this->setError('Commit for "' . $db->getPrefix() . '_ka_rel_genres" failed!');
			}
			else
			{
				$db->transactionCommit();
			}

			$db->unlockTables();
			$db->setDebug(false);
		}

		if (!empty($id))
		{
			// Start processing intro text for director(s) IDs
			$query = $db->getQuery(true);

			$query->select($db->quoteName(array('rel.name_id', 'n.name', 'n.latin_name')))
				->from($db->quoteName('#__ka_rel_names', 'rel'))
				->join('LEFT', $db->quoteName('#__ka_names', 'n') . ' ON ' . $db->quoteName('n.id') . ' = ' . $db->quoteName('rel.name_id'))
				->where($db->quoteName('rel.movie_id') . ' = ' . $id . ' AND ' . $db->quoteName('rel.is_directors') . ' = 1')
				->order($db->quoteName('rel.ordering'));

			if ($params->get('introtext_actors_list_limit') > 0)
			{
				$query->setLimit($params->get('introtext_actors_list_limit'), 0);
			}

			$db->setQuery($query);
			$names_d = $db->loadObjectList();

			if (count($names_d) > 0)
			{
				$ln_str = count($names_d) > 1 ? 'COM_KA_DIRECTORS' : 'COM_KA_DIRECTOR';

				foreach ($names_d as $director)
				{
					$n = !empty($director->name) ? $director->name : '';

					if (!empty($director->name) && !empty($director->latin_name))
					{
						$n .= ' / ';
					}

					$n .= !empty($director->latin_name) ? $director->latin_name : '';
					$intro_directors .= '[name=' . $director->name_id . ']' . $n . '[/name], ';
				}

				$intro_directors = '<span class="dc-list">[names ln=' . $ln_str . ']: ' . String::substr($intro_directors, 0, -2) . '[/names]</span>';
			}
			// End

			// Start processing intro text for cast IDs
			$query = $db->getQuery(true);

			$query->select($db->quoteName(array('rel.name_id', 'n.name', 'n.latin_name')))
				->from($db->quoteName('#__ka_rel_names', 'rel'))
				->join('LEFT', $db->quoteName('#__ka_names', 'n') . ' ON ' . $db->quoteName('n.id') . ' = ' . $db->quoteName('rel.name_id'))
				->where('rel.movie_id = ' . $id . ' AND rel.is_actors = 1 AND rel.voice_artists = 0')
				->order($db->quoteName('rel.ordering'));

			if ($params->get('introtext_actors_list_limit') > 0)
			{
				$query->setLimit($params->get('introtext_actors_list_limit'), 0);
			}

			$db->setQuery($query);
			$names = $db->loadObjectList();

			if (count($names) > 0)
			{
				foreach ($names as $name)
				{
					$n = !empty($name->name) ? $name->name : '';

					if (!empty($name->name) && !empty($name->latin_name))
					{
						$n .= ' / ';
					}

					$n .= !empty($name->latin_name) ? $name->latin_name : '';
					$intro_cast .= '[name=' . $name->name_id . ']' . $n . '[/name], ';
				}

				$intro_cast = '<span class="cast-list">[names ln=COM_KA_CAST]: ' . String::substr($intro_cast, 0, -2) . '[/names]</span>';
			}
			// End
		}

		$query = $db->getQuery(true);

		$query->update($db->quoteName('#__ka_movies'))
			->set($db->quoteName('introtext') . " = '" . $db->escape($intro_countries . $intro_genres . $intro_directors . $intro_cast) . "'")
			->where($db->quoteName('id') . ' = ' . (int) $id);

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			$query_result = false;
		}

		return $query_result;
	}

	/**
	 * Method to update tags mapping.
	 *
	 * @param   mixed  $ids       New tags IDs. Array of IDs or string with IDs separated by commas.
	 * @param   int    $movie_id  Movie ID
	 *
	 * @return  boolean   True on success
	 */
	protected function updateTagMapping($ids, $movie_id)
	{
		$db = $this->getDBO();

		if (!empty($ids))
		{
			$ids = (!is_array($ids)) ? explode(',', $ids) : $ids;

			$query = $db->getQuery(true)
				->delete($db->quoteName('#__contentitem_tag_map'))
				->where($db->quoteName('content_item_id') . ' = ' . (int) $movie_id);
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				$this->setError($e->getMessage());

				return false;
			}

			if ((is_array($ids) && empty($ids[0])) || empty($ids))
			{
				return true;
			}

			$query = $db->getQuery(true);
			$query->insert($db->quoteName('#__contentitem_tag_map'))
				->columns($db->quoteName(array('type_alias', 'core_content_id', 'content_item_id', 'tag_id', 'tag_date', 'type_id')));

			foreach ($ids as $tag_id)
			{
				$query->values("'com_kinoarhiv.movie','0','" . (int) $movie_id . "','" . (int) $tag_id . "','" . $query->currentTimestamp() . "','0'");
			}

			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				$this->setError($e->getMessage());

				return false;
			}
		}

		return true;
	}

	/**
	 * Method to move all media items which is linked to the movie into a new location, if movie alias was changed.
	 *
	 * @param   int     $id         Movie ID.
	 * @param   string  $old_alias  Old movie filesystem alias.
	 * @param   string  $new_alias  New movie filesystem alias.
	 * @param   object  $params     Component parameters.
	 *
	 * @return  boolean   True on success
	 */
	protected function moveMediaItems($id, $old_alias, $new_alias, $params)
	{
		if (empty($id) || empty($old_alias) || empty($new_alias))
		{
			$this->setError('Movie ID or alias cannot be empty!');

			return false;
		}
		else
		{
			jimport('joomla.filesystem.folder');
			JLoader::register('KAFilesystemHelper', JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'filesystem.php');

			// Move gallery items
			$path_poster = $params->get('media_posters_root');
			$path_wallpp = $params->get('media_wallpapers_root');
			$path_screen = $params->get('media_scr_root');
			$old_folder_poster = $path_poster . DIRECTORY_SEPARATOR . $old_alias . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . 'posters';
			$old_folder_wallpp = $path_wallpp . DIRECTORY_SEPARATOR . $old_alias . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . 'wallpapers';
			$old_folder_screen = $path_screen . DIRECTORY_SEPARATOR . $old_alias . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . 'screenshots';
			$new_folder_poster = $path_poster . DIRECTORY_SEPARATOR . $new_alias . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . 'posters';
			$new_folder_wallpp = $path_wallpp . DIRECTORY_SEPARATOR . $new_alias . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . 'wallpapers';
			$new_folder_screen = $path_screen . DIRECTORY_SEPARATOR . $new_alias . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . 'screenshots';

			if (!KAFilesystemHelper::move(
				array(JPath::clean($old_folder_poster), JPath::clean($old_folder_wallpp), JPath::clean($old_folder_screen)),
				array(JPath::clean($new_folder_poster), JPath::clean($new_folder_wallpp), JPath::clean($new_folder_screen))
				))
			{
				$this->setError('Error while moving the files from media folders into new location! See log for more information.');
			}

			// Remove parent folder for posters/wallpapers/screenshots. Delete only if folder(s) is empty.
			if (KAFilesystemHelper::getFolderSize($path_poster . DIRECTORY_SEPARATOR . $old_alias . DIRECTORY_SEPARATOR . $id) === 0)
			{
				if (file_exists($path_poster . DIRECTORY_SEPARATOR . $old_alias . DIRECTORY_SEPARATOR . $id))
				{
					JFolder::delete($path_poster . DIRECTORY_SEPARATOR . $old_alias . DIRECTORY_SEPARATOR . $id);
				}
			}

			if (KAFilesystemHelper::getFolderSize($path_wallpp . DIRECTORY_SEPARATOR . $old_alias . DIRECTORY_SEPARATOR . $id) === 0)
			{
				if (file_exists($path_wallpp . DIRECTORY_SEPARATOR . $old_alias . DIRECTORY_SEPARATOR . $id))
				{
					JFolder::delete($path_wallpp . DIRECTORY_SEPARATOR . $old_alias . DIRECTORY_SEPARATOR . $id);
				}
			}

			if (KAFilesystemHelper::getFolderSize($path_screen . DIRECTORY_SEPARATOR . $old_alias . DIRECTORY_SEPARATOR . $id) === 0)
			{
				if (file_exists($path_screen . DIRECTORY_SEPARATOR . $old_alias . DIRECTORY_SEPARATOR . $id))
				{
					JFolder::delete($path_screen . DIRECTORY_SEPARATOR . $old_alias . DIRECTORY_SEPARATOR . $id);
				}
			}

			// Move trailers and their content
			$path_trailers = $params->get('media_trailers_root');
			$old_folder_trailers = $path_trailers . DIRECTORY_SEPARATOR . $old_alias . DIRECTORY_SEPARATOR . $id;
			$new_folder_trailers = $path_trailers . DIRECTORY_SEPARATOR . $new_alias . DIRECTORY_SEPARATOR . $id;

			if (KAFilesystemHelper::move(JPath::clean($old_folder_trailers), JPath::clean($new_folder_trailers), true))
			{
				if (KAFilesystemHelper::getFolderSize($old_folder_trailers) === 0)
				{
					if (file_exists($old_folder_trailers))
					{
						JFolder::delete($old_folder_trailers);
					}
				}
			}
			else
			{
				$this->setError('Error while moving the files from trailer folders into new location! See log for more information.');
			}
		}

		return true;
	}

	/**
	 * Update statistics on genres
	 *
	 * @param   string  $old  Original genres list(before edit).
	 * @param   string  $new  New genres list.
	 *
	 * @return  mixed   True on success, exception otherwise
	 */
	protected function updateGenresStat($old, $new)
	{
		$db = $this->getDBO();
		$old_arr = !is_array($old) ? explode(',', $old) : $old;
		$new_arr = !is_array($new) ? explode(',', $new) : $new;
		$all = array_unique(array_merge($old_arr, $new_arr));

		$query_result = true;
		$db->setDebug(true);
		$db->lockTable('#__ka_genres');
		$db->transactionStart();

		foreach ($all as $genre_id)
		{
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__ka_genres'));

			$subquery = $db->getQuery(true)
				->select('COUNT(genre_id)')
				->from($db->quoteName('#__ka_rel_genres'))
				->where($db->quoteName('genre_id') . ' = ' . (int) $genre_id);

			$query->set($db->quoteName('stats') . ' = (' . $subquery . ')')
				->where($db->quoteName('id') . ' = ' . (int) $genre_id);
			$db->setQuery($query . ';');

			if ($db->execute() === false)
			{
				$query_result = false;
				break;
			}
		}

		if ($query_result === false)
		{
			$db->transactionRollback();
			$this->setError('Commit failed!');
		}
		else
		{
			$db->transactionCommit();
		}

		$db->unlockTables();
		$db->setDebug(false);

		return $query_result;
	}

	/**
	 * Method to get the list of cast and crew for grid.
	 *
	 * @return  object
	 *
	 * @since   3.0
	 */
	public function getCast()
	{
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$id = $app->input->get('id', null, 'int');
		$orderby = $app->input->get('sidx', '1', 'string');
		$order = $app->input->get('sord', 'asc', 'word');
		$page = $app->input->get('page', 0, 'int');
		$search_field = $app->input->get('searchField', '', 'string');
		$search_operand = $app->input->get('searchOper', 'eq', 'cmd');
		$search_string = $app->input->get('searchString', '', 'string');
		$result = (object) array();
		$result->rows = array();
		$careers = array();

		$query = $db->getQuery(true)
			->select($db->quoteName(array('id', 'title')))
			->from($db->quoteName('#__ka_names_career'))
			->order($db->quoteName('ordering') . ' ASC');

		$db->setQuery($query);
		$_careers = $db->loadObjectList();

		foreach ($_careers as $career)
		{
			$careers[$career->id] = $career->title;
		}

		$query = $db->getQuery(true);

		$query->select($db->quoteName('n.id', 'name_id') . ',' . $db->quoteName('n.name') . ',' . $db->quoteName('n.latin_name'))
			->select($db->quoteName(array('t.type', 't.role', 't.ordering')))
			->select($db->quoteName('d.id', 'dub_id') . ',' . $db->quoteName('d.name', 'dub_name') . ',' . $db->quoteName('d.latin_name', 'dub_latin_name'))
			->select("GROUP_CONCAT(" . $db->quoteName('r.role') . " SEPARATOR ', ') AS " . $db->quoteName('dub_role'))
			->from($db->quoteName('#__ka_names', 'n'))
			->join('LEFT', $db->quoteName('#__ka_rel_names', 't') . ' ON t.name_id = n.id AND t.movie_id = ' . (int) $id)
			->join('LEFT', $db->quoteName('#__ka_names', 'd') . ' ON ' . $db->quoteName('d.id') . ' = ' . $db->quoteName('t.dub_id'))
			->join('LEFT', $db->quoteName('#__ka_rel_names', 'r') . ' ON ' . $db->quoteName('r.dub_id') . ' = ' . $db->quoteName('n.id'));

		$where_subquery = $db->getQuery(true)
			->select($db->quoteName('name_id'))
			->from($db->quoteName('#__ka_rel_names'))
			->where($db->quoteName('movie_id') . ' = ' . (int) $id);

		$query->where($db->quoteName('n.id') . ' IN (' . $where_subquery . ')');

		if (!empty($search_string))
		{
			if ($search_field == 'n.name' || $search_field == 'd.name')
			{
				$query->where("(" . KADatabaseHelper::transformOperands($db->quoteName($search_field), $search_operand, $db->escape($search_string)) . " OR " . KADatabaseHelper::transformOperands($db->quoteName('n.latin_name'), $search_operand, $db->escape($search_string)) . ")");
			}
			else
			{
				$query->where(KADatabaseHelper::transformOperands($db->quoteName($search_field), $search_operand, $db->escape($search_string)));
			}
		}

		$query->group($db->quoteName('n.id'));

		// Preventing 'ordering asc/desc, ordering asc/desc' duplication
		if (strpos($orderby, 'ordering') !== false)
		{
			$query->order($db->quoteName('t.ordering') . ' ASC');
		}
		else
		{
			// We need this if grid grouping is used. At the first(0) index - grouping field
			$ord_request = explode(',', $orderby);

			if (count($ord_request) > 1)
			{
				$query->order($db->quoteName(trim($ord_request[1])) . ' ' . strtoupper($order) . ', ' . $db->quoteName('t.ordering') . ' ASC');
			}
			else
			{
				$query->order($db->quoteName(trim($orderby)) . ' ' . strtoupper($order) . ', ' . $db->quoteName('t.ordering') . ' ASC');
			}
		}

		$db->setQuery($query);
		$names = $db->loadObjectList();

		// Presorting based on the type of career person
		$i = 0;
		$_result = array();

		foreach ($names as $value)
		{
			$name = '';

			if (!empty($value->name))
			{
				$name .= $value->name;
			}

			if (!empty($value->name) && !empty($value->latin_name))
			{
				$name .= ' / ';
			}

			if (!empty($value->latin_name))
			{
				$name .= $value->latin_name;
			}

			$dub_name = '';

			if (!empty($value->dub_name))
			{
				$dub_name .= $value->dub_name;
			}

			if (!empty($value->dub_name) && !empty($value->dub_latin_name))
			{
				$dub_name .= ' / ';
			}

			if (!empty($value->dub_latin_name))
			{
				$dub_name .= $value->dub_latin_name;
			}

			foreach (explode(',', $value->type) as $k => $type)
			{
				$_result[$type][$i] = array(
					'name'     => $name,
					'name_id'  => $value->name_id,
					'role'     => $value->role,
					'dub_name' => $dub_name,
					'dub_id'   => $value->dub_id,
					'ordering' => $value->ordering,
					'type'     => $careers[$type],
					'type_id'  => $type
				);

				$i++;
			}
		}

		// The final sorting of the array for the grid
		$k = 0;

		foreach ($_result as $row)
		{
			foreach ($row as $elem)
			{
				$result->rows[$k]['id'] = $elem['name_id'] . '_' . $id . '_' . $elem['type_id'];
				$result->rows[$k]['cell'] = array(
					'name'     => $elem['name'],
					'name_id'  => $elem['name_id'],
					'role'     => $elem['role'],
					'dub_name' => $elem['dub_name'],
					'dub_id'   => $elem['dub_id'],
					'ordering' => $elem['ordering'],
					'type'     => $elem['type']
				);

				$k++;
			}
		}

		$result->page = $page;
		$result->total = 1;
		$result->records = count($result->rows);

		return $result;
	}

	/**
	 * Method to remove cast and crew.
	 *
	 * @return  array
	 *
	 * @since   3.0
	 */
	public function deleteCast()
	{
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$data = $app->input->post->get('data', array(), 'array');
		$query_result = true;

		if (count($data) <= 0)
		{
			return array('success' => false, 'message' => JText::_('JERROR_NO_ITEMS_SELECTED'));
		}

		$db->setDebug(true);
		$db->lockTable('#__ka_rel_names');
		$db->transactionStart();

		foreach ($data as $key => $value)
		{
			$ids = explode('_', $value['name']);
			$query = $db->getQuery(true);

			$query->delete($db->quoteName('#__ka_rel_names'))
				->where('name_id = ' . (int) $ids[3] . ' AND movie_id = ' . (int) $ids[4] . ' AND FIND_IN_SET("' . (int) $ids[5] . '", type)');
			$db->setQuery($query . ';');

			if ($db->execute() === false)
			{
				$query_result = false;
				break;
			}
		}

		if ($query_result === false)
		{
			$db->transactionRollback();
			$success = false;
			$message = JText::_('COM_KA_ITEMS_DELETED_ERROR');
		}
		else
		{
			$db->transactionCommit();
			$success = true;
			$message = JText::_('COM_KA_ITEMS_DELETED_SUCCESS');
		}

		$db->unlockTables();
		$db->setDebug(false);

		return array('success' => $success, 'message' => $message);
	}

	/**
	 * Method to remove awards related to movie.
	 *
	 * @return  array
	 *
	 * @since   3.0
	 */
	public function deleteRelAwards()
	{
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$data = $app->input->post->get('data', array(), 'array');
		$query_result = true;

		if (count($data) <= 0)
		{
			return array('success' => false, 'message' => JText::_('JERROR_NO_ITEMS_SELECTED'));
		}

		$db->setDebug(true);
		$db->lockTable('#__ka_rel_awards');
		$db->transactionStart();

		foreach ($data as $key => $value)
		{
			$ids = explode('_', substr($value['name'], 16));
			$query = $db->getQuery(true);

			$query->delete($db->quoteName('#__ka_rel_awards'))
				->where($db->quoteName('id') . ' = ' . (int) $ids[0]);
			$db->setQuery($query . ';');

			if ($db->execute() === false)
			{
				$query_result = false;
				break;
			}
		}

		if ($query_result === false)
		{
			$db->transactionRollback();
			$success = false;
			$message = JText::_('COM_KA_ITEMS_DELETED_ERROR');
		}
		else
		{
			$db->transactionCommit();
			$success = true;
			$message = JText::_('COM_KA_ITEMS_DELETED_SUCCESS');
		}

		$db->unlockTables();
		$db->setDebug(false);

		return array('success' => $success, 'message' => $message);
	}

	/**
	 * Method to get the list of awards for grid.
	 *
	 * @return  object
	 *
	 * @since   3.0
	 */
	public function getAwards()
	{
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$id = $app->input->get('id', null, 'int');
		$orderby = $app->input->get('sidx', '1', 'string');
		$order = $app->input->get('sord', 'asc', 'word');
		$limit = $app->input->get('rows', 50, 'int');
		$page = $app->input->get('page', 0, 'int');
		$search_field = $app->input->get('searchField', '', 'string');
		$search_operand = $app->input->get('searchOper', 'eq', 'cmd');
		$search_string = $app->input->get('searchString', '', 'string');
		$limitstart = $limit * $page - $limit;
		$limitstart = $limitstart <= 0 ? 0 : $limitstart;
		$result = (object) array('rows' => array());

		$query = $db->getQuery(true)
			->select('COUNT(rel.id)')
			->from($db->quoteName('#__ka_rel_awards', 'rel'))
			->join('LEFT', $db->quoteName('#__ka_awards', 'aw') . ' ON ' . $db->quoteName('aw.id') . ' = ' . $db->quoteName('rel.award_id'))
			->where($db->quoteName('rel.item_id') . ' = ' . (int) $id . ' AND ' . $db->quoteName('rel.type') . ' = 0');

		if (!empty($search_string))
		{
			$query->where(KADatabaseHelper::transformOperands($db->quoteName($search_field), $search_operand, $db->escape($search_string)));
		}

		$db->setQuery($query);
		$total = $db->loadResult();

		$total_pages = ($total > 0) ? ceil($total / $limit) : 0;
		$page = ($page > $total_pages) ? $total_pages : $page;

		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('rel.id', 'rel.item_id', 'rel.award_id', 'rel.desc', 'rel.year', 'rel.type', 'aw.title')))
			->from($db->quoteName('#__ka_rel_awards', 'rel'))
			->join('LEFT', $db->quoteName('#__ka_awards', 'aw') . ' ON ' . $db->quoteName('aw.id') . ' = ' . $db->quoteName('rel.award_id'))
			->where($db->quoteName('rel.item_id') . ' = ' . (int) $id . ' AND ' . $db->quoteName('type') . ' = 0');

		if (!empty($search_string))
		{
			$query->where(KADatabaseHelper::transformOperands($db->quoteName($search_field), $search_operand, $db->escape($search_string)));
		}

		$query->order($db->quoteName($orderby) . ' ' . strtoupper($order))
			->setLimit($limit, $limitstart);

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$k = 0;

		foreach ($rows as $elem)
		{
			$result->rows[$k]['id'] = $elem->id . '_' . $elem->item_id . '_' . $elem->award_id;
			$result->rows[$k]['cell'] = array(
				'id'       => $elem->id,
				'award_id' => $elem->award_id,
				'title'    => $elem->title,
				'year'     => $elem->year,
				'desc'     => $elem->desc
			);

			$k++;
		}

		$result->page = $page;
		$result->total = $total_pages;
		$result->records = $total;

		return $result;
	}

	/**
	 * Method to get the list of premieres for grid.
	 *
	 * @return  object
	 *
	 * @since   3.0
	 */
	public function getPremieres()
	{
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$id = $app->input->get('id', null, 'int');
		$orderby = $app->input->get('sidx', '1', 'string');
		$order = $app->input->get('sord', 'asc', 'word');
		$limit = $app->input->get('rows', 50, 'int');
		$page = $app->input->get('page', 0, 'int');
		$search_field = $app->input->get('searchField', '', 'string');
		$search_operand = $app->input->get('searchOper', 'eq', 'cmd');
		$search_string = trim($app->input->get('searchString', '', 'string'));
		$limitstart = $limit * $page - $limit;
		$limitstart = $limitstart <= 0 ? 0 : $limitstart;
		$result = (object) array('rows' => array());

		$query = $db->getQuery(true)
			->select('COUNT(p.id)')
			->from($db->quoteName('#__ka_premieres', 'p'))
			->join('LEFT', $db->quoteName('#__ka_vendors', 'v') . ' ON ' . $db->quoteName('v.id') . ' = ' . $db->quoteName('p.vendor_id'))
			->join('LEFT', $db->quoteName('#__ka_countries', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('p.country_id'))
			->where($db->quoteName('p.movie_id') . ' = ' . (int) $id);

		if (!empty($search_string))
		{
			if ($search_string == JText::_('COM_KA_PREMIERE_WORLD'))
			{
				$query->where(KADatabaseHelper::transformOperands($db->quoteName('p.country_id'), $search_operand, 0));
			}
			else
			{
				$query->where(KADatabaseHelper::transformOperands($db->quoteName($search_field), $search_operand, $db->escape($search_string)));
			}
		}

		$db->setQuery($query);
		$total = $db->loadResult();

		$total_pages = ($total > 0) ? ceil($total / $limit) : 0;
		$page = ($page > $total_pages) ? $total_pages : $page;

		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('p.id', 'p.movie_id', 'p.premiere_date', 'p.info', 'p.ordering', 'v.company_name', 'v.company_name_intl')))
			->select($db->quoteName('c.name', 'country'))
			->from($db->quoteName('#__ka_premieres', 'p'))
			->join('LEFT', $db->quoteName('#__ka_vendors', 'v') . ' ON ' . $db->quoteName('v.id') . ' = ' . $db->quoteName('p.vendor_id'))
			->join('LEFT', $db->quoteName('#__ka_countries', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('p.country_id'))
			->where($db->quoteName('p.movie_id') . ' = ' . (int) $id);

		if (!empty($search_string))
		{
			if ($search_string == JText::_('COM_KA_PREMIERE_WORLD'))
			{
				$query->where(KADatabaseHelper::transformOperands($db->quoteName('p.country_id'), $search_operand, 0));
			}
			else
			{
				$query->where(KADatabaseHelper::transformOperands($db->quoteName($search_field), $search_operand, $db->escape($search_string)));
			}
		}

		$query->order($db->quoteName($orderby) . ' ' . strtoupper($order))
			->setLimit($limit, $limitstart);

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$k = 0;

		foreach ($rows as $elem)
		{
			$result->rows[$k]['id'] = $elem->id . '_' . $elem->movie_id;
			$country = !empty($elem->country) ? $elem->country : JText::_('COM_KA_PREMIERE_WORLD');
			$result->rows[$k]['cell'] = array(
				'id'                => $elem->id,
				'company_name'      => $elem->company_name,
				'company_name_intl' => $elem->company_name_intl,
				'premiere_date'     => $elem->premiere_date,
				'country'           => $country,
				'ordering'          => $elem->ordering
			);

			$k++;
		}

		$result->page = $page;
		$result->total = $total_pages;
		$result->records = $total;

		return $result;
	}

	/**
	 * Method to get the list of releases for grid.
	 *
	 * @return  object
	 *
	 * @since   3.0
	 */
	public function getReleases()
	{
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$id = $app->input->get('id', null, 'int');
		$orderby = $app->input->get('sidx', '1', 'string');
		$order = $app->input->get('sord', 'asc', 'word');
		$limit = $app->input->get('rows', 50, 'int');
		$page = $app->input->get('page', 0, 'int');
		$search_field = $app->input->get('searchField', '', 'string');
		$search_operand = $app->input->get('searchOper', 'eq', 'cmd');
		$search_string = $app->input->get('searchString', '', 'string');
		$limitstart = $limit * $page - $limit;
		$limitstart = $limitstart <= 0 ? 0 : $limitstart;
		$result = (object) array('rows' => array());

		$query = $db->getQuery(true)
			->select('COUNT(r.id)')
			->from($db->quoteName('#__ka_releases', 'r'))
			->join('LEFT', $db->quoteName('#__ka_vendors', 'v') . ' ON ' . $db->quoteName('v.id') . ' = ' . $db->quoteName('r.vendor_id'))
			->join('LEFT', $db->quoteName('#__ka_countries', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('r.country_id'))
			->join('LEFT', $db->quoteName('#__ka_media_types', 'm') . ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('r.media_type'))
			->where($db->quoteName('r.movie_id') . ' = ' . (int) $id);

		if (!empty($search_string))
		{
			if ($search_string == JText::_('COM_KA_RELEASES_WORLD'))
			{
				$query->where(KADatabaseHelper::transformOperands($db->quoteName('r.country_id'), $search_operand, 0));
			}
			else
			{
				$query->where(KADatabaseHelper::transformOperands($db->quoteName($search_field), $search_operand, $db->escape($search_string)));
			}
		}

		$db->setQuery($query);
		$total = $db->loadResult();

		$total_pages = ($total > 0) ? ceil($total / $limit) : 0;
		$page = ($page > $total_pages) ? $total_pages : $page;

		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('r.id', 'r.movie_id', 'r.release_date', 'r.media_type', 'r.ordering', 'v.company_name', 'v.company_name_intl')))
			->select($db->quoteName('c.name', 'country'))
			->select($db->quoteName('m.title', 'media_type_title'))
			->from($db->quoteName('#__ka_releases', 'r'))
			->join('LEFT', $db->quoteName('#__ka_vendors', 'v') . ' ON ' . $db->quoteName('v.id') . ' = ' . $db->quoteName('r.vendor_id'))
			->join('LEFT', $db->quoteName('#__ka_countries', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('r.country_id'))
			->join('LEFT', $db->quoteName('#__ka_media_types', 'm') . ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('r.media_type'))
			->where($db->quoteName('r.movie_id') . ' = ' . (int) $id);

		if (!empty($search_string))
		{
			if ($search_string == JText::_('COM_KA_RELEASES_WORLD'))
			{
				$query->where(KADatabaseHelper::transformOperands($db->quoteName('r.country_id'), $search_operand, 0));
			}
			else
			{
				$query->where(KADatabaseHelper::transformOperands($db->quoteName($search_field), $search_operand, $db->escape($search_string)));
			}
		}

		$query->order($db->quoteName($orderby) . ' ' . strtoupper($order))
			->setLimit($limit, $limitstart);

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$k = 0;

		foreach ($rows as $elem)
		{
			$result->rows[$k]['id'] = $elem->id . '_' . $elem->movie_id;
			$country = !empty($elem->country) ? $elem->country : 'N/a';
			$result->rows[$k]['cell'] = array(
				'id'                => $elem->id,
				'company_name'      => $elem->company_name,
				'company_name_intl' => $elem->company_name_intl,
				'release_date'      => $elem->release_date,
				'media_type'        => $elem->media_type_title,
				'country'           => $country,
				'ordering'          => $elem->ordering
			);

			$k++;
		}

		$result->page = $page;
		$result->total = $total_pages;
		$result->records = $total;

		return $result;
	}

	/**
	 * Method to save access rules for movie.
	 *
	 * @return  array
	 *
	 * @since   3.0
	 */
	public function saveAccessRules()
	{
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$data = $app->input->post->get('form', array(), 'array');
		$id = $app->input->get('id', null, 'int');
		$rules = array();

		if (empty($id))
		{
			return array('success' => false, 'message' => 'Error');
		}

		foreach ($data['movie']['rules'] as $rule => $groups)
		{
			foreach ($groups as $group => $value)
			{
				if ($value != '')
				{
					$rules[$rule][$group] = (int) $value;
				}
				else
				{
					unset($data['rules'][$rule][$group]);
				}
			}
		}

		$rules = json_encode($rules);

		// Get parent id
		$query = $db->getQuery(true);

		$query->select($db->quoteName('id'))
			->from($db->quoteName('#__assets'))
			->where($db->quoteName('name') . " = 'com_kinoarhiv' AND " . $db->quoteName('parent_id') . " = 1");

		$db->setQuery($query);
		$parent_id = $db->loadResult();

		$query = $db->getQuery(true);

		$query->update($db->quoteName('#__assets'))
			->set($db->quoteName('rules') . " = '" . $rules . "'")
			->where($db->quoteName('#__assets') . " = 'com_kinoarhiv.movie." . (int) $id . "' AND " . $db->quoteName('level') . " = 2 AND " . $db->quoteName('parent_id') . " = " . (int) $parent_id);

		$db->setQuery($query);

		try
		{
			$db->execute();

			return array('success' => true);
		}
		catch (Exception $e)
		{
			return array('success' => false, 'message' => $e->getMessage());
		}
	}

	/**
	 * Method to remove a movie and associated content from database and disk.
	 *
	 * @return  boolean
	 *
	 * @since   3.0
	 */
	public function remove()
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');

		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$ids = $app->input->get('id', array(), 'array');
		$params = JComponentHelper::getParams('com_kinoarhiv');

		// Remove award relations
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__ka_rel_awards'))
			->where($db->quoteName('item_id') . ' IN (' . implode(',', $ids) . ') AND ' . $db->quoteName('type') . ' = 0');
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Remove country relations
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__ka_rel_countries'))
			->where($db->quoteName('movie_id') . ' IN (' . implode(',', $ids) . ')');
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Remove genre relations
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__ka_rel_genres'))
			->where($db->quoteName('movie_id') . ' IN (' . implode(',', $ids) . ')');
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Remove name relations
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__ka_rel_names'))
			->where($db->quoteName('movie_id') . ' IN (' . implode(',', $ids) . ')');
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Remove releases
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__ka_releases'))
			->where($db->quoteName('movie_id') . ' IN (' . implode(',', $ids) . ')');
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Remove reviews
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__ka_reviews'))
			->where($db->quoteName('movie_id') . ' IN (' . implode(',', $ids) . ')');
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Remove favorited and watched movies
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__ka_user_marked_movies'))
			->where($db->quoteName('movie_id') . ' IN (' . implode(',', $ids) . ')');
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Remove user votes
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__ka_user_votes'))
			->where($db->quoteName('movie_id') . ' IN (' . implode(',', $ids) . ')');
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Remove premieres
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__ka_premieres'))
			->where($db->quoteName('movie_id') . ' IN (' . implode(',', $ids) . ')');
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Remove tags mapping
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__contentitem_tag_map'))
			->where($db->quoteName('content_item_id') . ' IN (' . implode(',', $ids) . ')');
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Remove media items
		$query = $db->getQuery(true);
		$query->select('id, fs_alias')
			->from($db->quoteName('#__ka_movies'))
			->where($db->quoteName('id') . ' IN (' . implode(',', $ids) . ')');

		$db->setQuery($query);
		$items = $db->loadObjectList();

		foreach ($items as $item)
		{
			// Delete root folders
			if (file_exists($params->get('media_posters_root') . DIRECTORY_SEPARATOR . $item->fs_alias . DIRECTORY_SEPARATOR . $item->id))
			{
				JFolder::delete($params->get('media_posters_root') . DIRECTORY_SEPARATOR . $item->fs_alias . DIRECTORY_SEPARATOR . $item->id);
			}

			if (file_exists($params->get('media_scr_root') . DIRECTORY_SEPARATOR . $item->fs_alias . DIRECTORY_SEPARATOR . $item->id))
			{
				JFolder::delete($params->get('media_scr_root') . DIRECTORY_SEPARATOR . $item->fs_alias . DIRECTORY_SEPARATOR . $item->id);
			}

			if (file_exists($params->get('media_wallpapers_root') . DIRECTORY_SEPARATOR . $item->fs_alias . DIRECTORY_SEPARATOR . $item->id))
			{
				JFolder::delete($params->get('media_wallpapers_root') . DIRECTORY_SEPARATOR . $item->fs_alias . DIRECTORY_SEPARATOR . $item->id);
			}

			if (file_exists($params->get('media_trailers_root') . DIRECTORY_SEPARATOR . $item->fs_alias . DIRECTORY_SEPARATOR . $item->id))
			{
				JFolder::delete($params->get('media_trailers_root') . DIRECTORY_SEPARATOR . $item->fs_alias . DIRECTORY_SEPARATOR . $item->id);
			}

			// Delete rating images
			if (file_exists($params->get('media_rating_image_root') . DIRECTORY_SEPARATOR . 'imdb' . DIRECTORY_SEPARATOR . $item->id . '_big.png'))
			{
				JFile::delete($params->get('media_rating_image_root') . DIRECTORY_SEPARATOR . 'imdb' . DIRECTORY_SEPARATOR . $item->id . '_big.png');
			}

			if (file_exists($params->get('media_rating_image_root') . DIRECTORY_SEPARATOR . 'kinopoisk' . DIRECTORY_SEPARATOR . $item->id . '_big.png'))
			{
				JFile::delete($params->get('media_rating_image_root') . DIRECTORY_SEPARATOR . 'kinopoisk' . DIRECTORY_SEPARATOR . $item->id . '_big.png');
			}

			if (file_exists($params->get('media_rating_image_root') . DIRECTORY_SEPARATOR . 'rottentomatoes' . DIRECTORY_SEPARATOR . $item->id . '_big.png'))
			{
				JFile::delete($params->get('media_rating_image_root') . DIRECTORY_SEPARATOR . 'rottentomatoes' . DIRECTORY_SEPARATOR . $item->id . '_big.png');
			}
		}

		// Remove movie(s) from DB
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__ka_movies'))
			->where($db->quoteName('id') . ' IN (' . implode(',', $ids) . ')');
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__ka_movies_gallery'))
			->where($db->quoteName('movie_id') . ' IN (' . implode(',', $ids) . ')');
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Remove trailers. It will not remove a media content.
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__ka_trailers'))
			->where($db->quoteName('movie_id') . ' IN (' . implode(',', $ids) . ')');
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Remove access rules
		$query_result = true;
		$db->setDebug(true);
		$db->lockTable('#__assets');
		$db->transactionStart();

		foreach ($ids as $id)
		{
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__assets'))
				->where($db->quoteName('name') . " = 'com_kinoarhiv.movie." . (int) $id . "' AND " . $db->quoteName('level') . " = 2");
			$db->setQuery($query . ';');

			if ($db->execute() === false)
			{
				$query_result = false;
				break;
			}
		}

		if ($query_result === false)
		{
			$db->transactionRollback();
		}
		else
		{
			$db->transactionCommit();
		}

		$db->unlockTables();
		$db->setDebug(false);

		return $query_result;
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param   JForm   $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  mixed  Array of filtered data if valid, false otherwise.
	 *
	 * @see     JFormRule
	 * @see     JFilterInput
	 * @since   12.2
	 */
	public function validate($form, $data, $group = null)
	{
		// Filter and validate the form data.
		$data = $form->filter($data);
		$return = $form->validate($data, $group);

		// Check for an error.
		if ($return instanceof Exception)
		{
			$this->setError($return->getMessage());

			return false;
		}

		// Check the validation results.
		if ($return === false)
		{
			// Get the validation messages from the form.
			foreach ($form->getErrors() as $message)
			{
				$this->setError($message);
			}

			return false;
		}

		return $data;
	}
}
