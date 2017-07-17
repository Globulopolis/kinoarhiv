<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *  
 * @copyright   Copyright (C) 2017 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\String\StringHelper;

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
		$task      = JFactory::getApplication()->input->getCmd('task', '');
		$form_name = 'com_kinoarhiv.movie';
		$form_opts = array('control' => 'jform', 'load_data' => $loadData);

		switch ($task)
		{
			case 'editMovieCast':
			case 'saveMovieCast':
				$form = $this->loadForm($form_name, 'relations_cast', $form_opts);
				break;
			case 'editMovieAwards':
			case 'saveMovieAwards':
				$form = $this->loadForm($form_name, 'relations_awards', $form_opts);
				break;
			case 'editMoviePremieres':
			case 'saveMoviePremieres':
				$form = $this->loadForm($form_name, 'relations_premieres', $form_opts);
				break;
			case 'editMovieReleases':
			case 'saveMovieReleases':
				$form = $this->loadForm($form_name, 'relations_releases', $form_opts);
				break;
			default:
				$form = $this->loadForm($form_name, 'movie', $form_opts);
				break;
		}

		if (empty($form))
		{
			return false;
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
		$app  = JFactory::getApplication();
		$data = $app->getUserState('com_kinoarhiv.movies.' . JFactory::getUser()->id . '.edit_data', array());

		if (empty($data))
		{
			$data = $this->getItem();

			if (empty($data) && $app->input->getCmd('task', '') == 'add')
			{
				$filters = (array) $app->getUserState('com_kinoarhiv.movies.filter');
				$data = (object) array(
					'state'    => ((isset($filters['published']) && $filters['published'] !== '') ? $filters['published'] : null),
					'language' => $app->input->getString('language', (!empty($filters['language']) ? $filters['language'] : null)),
					'access'   => $app->input->getInt('access', (!empty($filters['access']) ? $filters['access'] : JFactory::getConfig()->get('access')))
				);
			}
		}

		$this->preprocessData('com_kinoarhiv.movie', $data);

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
		$app  = JFactory::getApplication();
		$db   = $this->getDbo();
		$task = $app->input->get('task', '', 'cmd');
		$id   = $app->input->get('id', 0, 'int');

		if ($task == 'editMovieCast')
		{
			return $this->editMovieCast();
		}
		elseif ($task == 'editMovieAwards')
		{
			return $this->editMovieAwards();
		}
		elseif ($task == 'editMoviePremieres')
		{
			return $this->editMoviePremieres();
		}
		elseif ($task == 'editMovieReleases')
		{
			return $this->editMovieReleases();
		}

		$query = $db->getQuery(true)->select(
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
		->where($db->quoteName('m.id') . ' = ' . (int) $id);

		// Join over the language
		$query->select($db->quoteName('l.title', 'language_title'))
			->join('LEFT', $db->quoteName('#__languages', 'l') . ' ON ' . $db->quoteName('l.lang_code') . ' = ' . $db->quoteName('m.language'));

		// Join over the gallery item
		$query->select($db->quoteName('g.id', 'image_id') . ',' . $db->quoteName('g.filename'))
			->join('LEFT', $db->quoteName('#__ka_movies_gallery', 'g') . ' ON ' . $db->quoteName('g.movie_id') . ' = ' . $db->quoteName('m.id')
				. ' AND ' . $db->quoteName('g.type') . ' = 2'
				. ' AND ' . $db->quoteName('g.frontpage') . ' = 1');

		$db->setQuery($query);

		try
		{
			$result = $db->loadObject();
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');

			return array();
		}

		if (empty($id))
		{
			return $result;
		}

		$genres = $this->getGenres($id);

		if ($genres)
		{
			$genres = implode(',', $genres['id']);
			$result->genres = $genres;
			$result->genres_orig = $genres;
		}

		$countries = $this->getCountries($id);

		if ($countries)
		{
			$countries = implode(',', $countries['id']);
			$result->countries = $countries;
			$result->countries_orig = $countries;
		}

		$registry = new Registry($result->attribs);
		$result->attribs = $registry->toArray();

		if (!empty($result->metadata))
		{
			$metadata = json_decode($result->metadata, true);
			$result = (object) array_merge((array) $result, $metadata);
		}

		return $result;

		/*$app = JFactory::getApplication();
		$lang = JFactory::getLanguage();
		$db = $this->getDbo();
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

		return $result;*/
	}

	/**
	 * Get list of genres for field.
	 *
	 * @param   integer  $id  Item ID.
	 *
	 * @return  mixed    Array with data, false otherwise.
	 *
	 * @since   3.0
	 */
	private function getGenres($id)
	{
		$app = JFactory::getApplication();
		$db  = $this->getDbo();

		$query = $db->getQuery(true)
			->select($db->quoteName('g.id') . ',' . $db->quoteName('g.name', 'title'))
			->from($db->quoteName('#__ka_rel_genres', 'rel'))
			->leftJoin($db->quoteName('#__ka_genres', 'g') . ' ON ' . $db->quoteName('g.id') . ' = ' . $db->quoteName('rel.genre_id'))
			->where($db->quoteName('rel.movie_id') . ' = ' . (int) $id)
			->order($db->quoteName('rel.ordering') . ' ASC');

		$db->setQuery($query);

		try
		{
			$_genres = $db->loadAssocList();
			$genres = array();

			foreach ($_genres as $key => $id)
			{
				$genres['id'][$key] = $id['id'];
				$genres['title'][$key] = $id['title'];
			}
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		return $genres;
	}

	/**
	 * Method to get a list of countries.
	 *
	 * @param   integer  $id  Item ID.
	 *
	 * @return  object
	 *
	 * @since   3.0
	 */
	private function getCountries($id)
	{
		$app = JFactory::getApplication();
		$db  = $this->getDbo();

		$query = $db->getQuery(true)
			->select($db->quoteName(array('c.id', 'c.name')))
			->from($db->quoteName('#__ka_rel_countries', 'rel'))
			->leftJoin($db->quoteName('#__ka_countries', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('rel.country_id'))
			->where($db->quoteName('rel.movie_id') . ' = ' . (int) $id)
			->order($db->quoteName('rel.ordering') . ' ASC');

		$db->setQuery($query);

		try
		{
			$_countries = $db->loadAssocList();
			$countries = array();

			foreach ($_countries as $key => $id)
			{
				$countries['id'][$key] = $id['id'];
				$countries['title'][$key] = $id['name'];
			}
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		return $countries;
	}

	/**
	 * Method to get a single record for cast&crew edit.
	 *
	 * @return  mixed  Object on success, false on failure.
	 *
	 * @since  3.1
	 */
	private function editMovieCast()
	{
		$app        = JFactory::getApplication();
		$db         = $this->getDbo();
		$id         = $app->input->get('row_id', 0, 'int');
		$item_id    = $app->input->get('item_id', 0, 'int');
		$input_name = explode('_', $app->input->getString('input_name', ''));
		$name_id    = !empty($input_name[1]) ? $input_name[1] : 0;
		$query      = $db->getQuery(true);

		$query->select(
			$db->quoteName(
				array(
					'name_id', 'movie_id', 'type', 'role', 'dub_id', 'is_actors', 'voice_artists', 'is_directors',
					'ordering', 'desc'
				)
			)
		)
			->from($db->quoteName('#__ka_rel_names'))
			->where($db->quoteName('name_id') . ' = ' . (int) $name_id)
			->where($db->quoteName('movie_id') . ' = ' . (int) $item_id)
			->where('FIND_IN_SET (' . (int) $id . ', ' . $db->quoteName('type') . ')');

		$db->setQuery($query);

		try
		{
			$result = $db->loadObject();
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		return $result;
	}

	/**
	 * Method to save the form data for cast edit.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  mixed  True on success, False on error, lastInsertID on save.
	 *
	 * @since   3.1
	 */
	public function saveMovieCast($data)
	{
		$app        = JFactory::getApplication();
		$db         = $this->getDbo();
		$user       = JFactory::getUser();
		$id         = $app->input->get('item_id', 0, 'int');
		$input_name = $app->input->getString('input_name', '');
		$ids        = explode('_', $input_name);
		$name_id    = array_key_exists(1, $ids) ? $ids[1] : 0;
		$old_type   = array_key_exists(2, $ids) ? $ids[2] : 0;
		$new_type   = $data['type'];

		if ($old_type != $new_type)
		{
			if (empty($input_name))
			{
				echo 'insert new';
			}
			else
			{
				echo 'update type';
			}
		}
		else
		{
			echo 'update';
		}





		if (empty($input_name))
		{
			// Check if person allready exists in relation table and update field `type`, otherwise insert new record.
			/*$query = $db->getQuery(true)
				->select('COUNT(name_id)')
				->from($db->quoteName('#__ka_rel_names'))
				->where($db->quoteName('name_id') . ' = ' . (int) $data['name_id'] . ' AND ' . $db->quoteName('movie_id') . ' = ' . (int) $id);

			$db->setQuery($query);

			try
			{
				$total = $db->loadResult();
			}
			catch (RuntimeException $e)
			{
				$app->enqueueMessage($e->getMessage(), 'error');

				return false;
			}

			if ($total > 0)
			{
				// Update `type` field
				$query = $db->getQuery(true)
					->select($db->quoteName('type'))
					->from($db->quoteName('#__ka_rel_names'))
					->where($db->quoteName('name_id') . ' = ' . (int) $data['name_id'])
					->where($db->quoteName('movie_id') . ' = ' . (int) $id);

				$db->setQuery($query);

				try
				{
					$result = $db->loadResult();
					$types  = explode(',', $result);

					if (is_array($types))
					{
						foreach ($types as $type)
						{
							if ($data['type'] != $type)
							{
								array_push($types, $data['type']);
							}
						}
					}
				}
				catch (Exception $e)
				{
					$app->enqueueMessage($e->getMessage(), 'error');

					return false;
				}
			}
			else
			{
				// Insert new row
				$query = $db->getQuery(true)
					->insert($db->quoteName('#__ka_rel_names'))
					->columns(
						$db->quoteName(
							array('name_id', 'movie_id', 'type', 'role', 'dub_id', 'is_actors', 'voice_artists', 'is_directors', 'ordering', 'desc')
						)
					)
					->values(
						"'" . (int) $data['name_id'] . "', '" . (int) $id . "', '" . (int) $data['type'] . "',"
						. "'" . $db->escape($data['role']) . "', '" . (int) $data['dub_id'] . "',"
						. "'" . (int) $data['is_actors'] . "', '" . (int) $data['voice_artists'] . "',"
						. "'" . (int) $data['is_directors'] . "', '" . (int) $data['ordering'] . "',"
						. "'" . $db->escape($data['desc']) . "'"
					);
			}*/
		}
		else
		{
			/*$query = $db->getQuery(true)
				->select($db->quoteName('type'))
				->from($db->quoteName('#__ka_rel_names'))
				->where($db->quoteName('name_id') . ' = ' . (int) $name_id)
				->where($db->quoteName('movie_id') . ' = ' . (int) $id);

			$db->setQuery($query);

			try
			{
				$result = $db->loadResult();
				$types  = explode(',', $result);

				if (is_array($types))
				{
					foreach ($types as $type)
					{
						if ($data['type'] != $type)
						{
							array_push($types, $data['type']);
						}
					}
				}
			}
			catch (Exception $e)
			{
				$app->enqueueMessage($e->getMessage(), 'error');

				return false;
			}

			$query = $db->getQuery(true)
				->update($db->quoteName('#__ka_rel_names'))
				->set($db->quoteName('name_id') . " = '" . (int) $data['name_id'] . "'")
				->set($db->quoteName('type') . " = '" . $data['type'] . "'")
				->set($db->quoteName('role') . " = '" . $db->escape($data['role']) . "'")
				->set($db->quoteName('dub_id') . " = '" . (int) $data['dub_id'] . "'")
				->set($db->quoteName('is_actors') . " = '" . (int) $data['is_actors'] . "'")
				->set($db->quoteName('voice_artists') . " = '" . (int) $data['voice_artists'] . "'")
				->set($db->quoteName('is_directors') . " = '" . (int) $data['is_directors'] . "'")
				->set($db->quoteName('ordering') . " = '" . (int) $data['ordering'] . "'")
				->set($db->quoteName('desc') . " = '" . $db->escape($data['desc']) . "'")
				->where($db->quoteName('name_id') . ' = ' . (int) $name_id)
				->where($db->quoteName('movie_id') . ' = ' . (int) $id);*/
		}

		$db->setQuery($query);

		try
		{
			//$db->execute();

			// We need to store LastInsertID in session for later use in controller.
			if (empty($input_name))
			{
				$session_data = $app->getUserState('com_kinoarhiv.movie.' . $user->id . '.edit_data.c_id');
				$session_data['name_id'] = $data['name_id'];
				$session_data['type'] = $_type;
				$app->setUserState('com_kinoarhiv.movie.' . $user->id . '.edit_data.c_id', $session_data);
			}

			return true;
		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}
	}

	/**
	 * Build new string with types for cast.
	 *
	 * @param   integer  $name_id   Person ID.
	 * @param   integer  $movie_id  Movie ID.
	 * @param   integer  $type      Type
	 *
	 * @return  string
	 *
	 * @since  3.1
	 */
	private function updateCastTypeField($name_id, $movie_id, $type)
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select($db->quoteName('type'))
			->from($db->quoteName('#__ka_rel_names'))
			->where($db->quoteName('name_id') . ' = ' . (int) $name_id)
			->where($db->quoteName('movie_id') . ' = ' . (int) $movie_id);

		$db->setQuery($query);

		try
		{
			$result = $db->loadResult();
			$types  = explode(',', $result);

			if (is_array($types))
			{
				foreach ($types as $_type)
				{
					if ($type != $_type)
					{
						array_push($types, $type);
					}
				}
			}
		}
		catch (Exception $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		return implode(',', $types);
	}

	/**
	 * Method to get a single record for award edit.
	 *
	 * @return  mixed  Object on success, false on failure.
	 *
	 * @since  3.1
	 */
	private function editMovieAwards()
	{
		$app   = JFactory::getApplication();
		$db    = $this->getDbo();
		$id    = $app->input->get('row_id', 0, 'int');

		$query = $db->getQuery(true)
			->select($db->quoteName(array('id', 'item_id', 'award_id', 'desc', 'year', 'type')))
			->from($db->quoteName('#__ka_rel_awards'))
			->where($db->quoteName('id') . ' = ' . (int) $id);

		$db->setQuery($query);

		try
		{
			$result = $db->loadObject();
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		return $result;
	}

	/**
	 * Method to save the form data for award edit.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  mixed  True on success, False on error, lastInsertID on save.
	 *
	 * @since   3.1
	 */
	public function saveMovieAwards($data)
	{
		$app  = JFactory::getApplication();
		$db   = $this->getDbo();
		$user = JFactory::getUser();
		$id   = $app->input->get('item_id', 0, 'int');

		if (empty($data['id']))
		{
			$query = $db->getQuery(true)
				->insert($db->quoteName('#__ka_rel_awards'))
				->columns($db->quoteName(array('id', 'item_id', 'award_id', 'desc', 'year', 'type')))
				->values("'', '" . (int) $id . "', '" . (int) $data['award_id'] . "', "
					. "'" . $db->escape($data['desc']) . "', '" . (int) $data['year'] . "', '0'");
		}
		else
		{
			$query = $db->getQuery(true)
				->update($db->quoteName('#__ka_rel_awards'))
				->set($db->quoteName('award_id') . " = '" . (int) $data['award_id'] . "'")
				->set($db->quoteName('desc') . " = '" . $db->escape($data['desc']) . "'")
				->set($db->quoteName('year') . " = '" . (int) $data['year'] . "'")
				->where($db->quoteName('id') . ' = ' . (int) $data['id']);
		}

		$db->setQuery($query);

		try
		{
			$db->execute();

			// We need to store LastInsertID in session for later use in controller.
			if (empty($data['id']))
			{
				$session_data = $app->getUserState('com_kinoarhiv.movie.' . $user->id . '.edit_data.aw_id');
				$session_data['id'] = $db->insertid();
				$app->setUserState('com_kinoarhiv.movie.' . $user->id . '.edit_data.aw_id', $session_data);
			}

			return true;
		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}
	}

	/**
	 * Method to remove award(s) in awards list on 'awards tab'.
	 *
	 * @param   array  $ids  Items ID
	 *
	 * @return  boolean
	 *
	 * @since   3.0
	 */
	public function removeMovieAwards($ids)
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$db->setDebug(true);
		$db->lockTable('#__ka_rel_awards');
		$db->transactionStart();
		$result = true;

		foreach ($ids as $id)
		{
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__ka_rel_awards'))
				->where('id = ' . (int) $id . ';');
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (RuntimeException $e)
			{
				$app->enqueueMessage($e->getMessage(), 'error');
				$result = false;

				break;
			}
		}

		if (!$result)
		{
			try
			{
				$db->transactionRollback();
			}
			catch (RuntimeException $e)
			{
				$app->enqueueMessage($e->getMessage(), 'error');
			}
		}
		else
		{
			$db->transactionCommit();
		}

		$db->unlockTables();
		$db->setDebug(false);

		return $result;
	}

	/**
	 * Method to get a single record for premiere edit.
	 *
	 * @return  mixed  Object on success, false on failure.
	 *
	 * @since  3.1
	 */
	private function editMoviePremieres()
	{
		$app   = JFactory::getApplication();
		$db    = $this->getDbo();
		$id    = $app->input->get('row_id', 0, 'int');
		$query = $db->getQuery(true);

		$query->select(
			$db->quoteName(
				array(
					'id', 'movie_id', 'vendor_id', 'premiere_date', 'country_id', 'info', 'language', 'ordering'
				)
			)
		)
			->from($db->quoteName('#__ka_premieres'))
			->where($db->quoteName('id') . ' = ' . (int) $id);

		$db->setQuery($query);

		try
		{
			$result = $db->loadObject();
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		return $result;
	}

	/**
	 * Method to save the form data for premiere edit.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  mixed  True on success, False on error, lastInsertID on save.
	 *
	 * @since   3.1
	 */
	public function saveMoviePremieres($data)
	{
		$app  = JFactory::getApplication();
		$db   = $this->getDbo();
		$user = JFactory::getUser();
		$id   = $app->input->get('item_id', 0, 'int');

		if (empty($data['id']))
		{
			$query = $db->getQuery(true)
				->insert($db->quoteName('#__ka_premieres'))
				->columns($db->quoteName(array('id', 'movie_id', 'vendor_id', 'premiere_date', 'country_id', 'info', 'language', 'ordering')))
				->values("'', '" . (int) $id . "', '" . (int) $data['vendor_id'] . "', '" . $db->escape($data['premiere_date']) . "'"
					. ", '" . (int) $data['country_id'] . "', '" . $db->escape($data['info']) . "'"
					. ", '" . $db->escape($data['language']) . "', '" . (int) $data['ordering'] . "'");
		}
		else
		{
			$query = $db->getQuery(true)
				->update($db->quoteName('#__ka_premieres'))
				->set($db->quoteName('vendor_id') . " = '" . (int) $data['vendor_id'] . "'")
				->set($db->quoteName('premiere_date') . " = '" . $db->escape($data['premiere_date']) . "'")
				->set($db->quoteName('country_id') . " = '" . (int) $data['country_id'] . "'")
				->set($db->quoteName('info') . " = '" . $db->escape($data['info']) . "'")
				->set($db->quoteName('language') . " = '" . $db->escape($data['language']) . "'")
				->set($db->quoteName('ordering') . " = '" . (int) $data['ordering'] . "'")
				->where($db->quoteName('id') . ' = ' . (int) $data['id']);
		}

		$db->setQuery($query);

		try
		{
			$db->execute();

			// We need to store LastInsertID in session for later use in controller.
			if (empty($data['id']))
			{
				$session_data = $app->getUserState('com_kinoarhiv.movie.' . $user->id . '.edit_data.p_id');
				$session_data['id'] = $db->insertid();
				$app->setUserState('com_kinoarhiv.movie.' . $user->id . '.edit_data.p_id', $session_data);
			}

			return true;
		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}
	}

	/**
	 * Method to get a single record for release edit.
	 *
	 * @return  mixed  Object on success, false on failure.
	 *
	 * @since  3.1
	 */
	private function editMovieReleases()
	{
		$app   = JFactory::getApplication();
		$db    = $this->getDbo();
		$id    = $app->input->get('row_id', 0, 'int');
		$query = $db->getQuery(true);

		$query->select(
			$db->quoteName(
				array(
					'id', 'country_id', 'vendor_id', 'movie_id', 'media_type', 'release_date', 'desc', 'language', 'ordering'
				)
			)
		)
			->from($db->quoteName('#__ka_releases'))
			->where($db->quoteName('id') . ' = ' . (int) $id);

		$db->setQuery($query);

		try
		{
			$result = $db->loadObject();
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		return $result;
	}

	/**
	 * Method to save the form data for release edit.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  mixed  True on success, False on error, lastInsertID on save.
	 *
	 * @since   3.1
	 */
	public function saveMovieReleases($data)
	{
		$app  = JFactory::getApplication();
		$db   = $this->getDbo();
		$user = JFactory::getUser();
		$id   = $app->input->get('item_id', 0, 'int');

		if (empty($data['id']))
		{
			$query = $db->getQuery(true)
				->insert($db->quoteName('#__ka_releases'))
				->columns($db->quoteName(array('id', 'country_id', 'vendor_id', 'movie_id', 'media_type', 'release_date', 'desc', 'language', 'ordering')))
				->values("'', '" . (int) $data['country_id'] . "', '" . (int) $data['vendor_id'] . "', "
					. "'" . (int) $id . "', '" . (int) $data['media_type'] . "', '" . $db->escape($data['release_date']) . "'"
					. ", '" . $db->escape($data['desc']) . "', '" . $db->escape($data['language']) . "', '" . (int) $data['ordering'] . "'");
		}
		else
		{
			$query = $db->getQuery(true)
				->update($db->quoteName('#__ka_releases'))
				->set($db->quoteName('country_id') . " = '" . (int) $data['country_id'] . "'")
				->set($db->quoteName('vendor_id') . " = '" . (int) $data['vendor_id'] . "'")
				->set($db->quoteName('media_type') . " = '" . (int) $data['media_type'] . "'")
				->set($db->quoteName('release_date') . " = '" . $db->escape($data['release_date']) . "'")
				->set($db->quoteName('desc') . " = '" . $db->escape($data['desc']) . "'")
				->set($db->quoteName('language') . " = '" . $db->escape($data['language']) . "'")
				->set($db->quoteName('ordering') . " = '" . (int) $data['ordering'] . "'")
				->where($db->quoteName('id') . ' = ' . (int) $data['id']);
		}

		$db->setQuery($query);

		try
		{
			$db->execute();

			// We need to store LastInsertID in session for later use in controller.
			if (empty($data['id']))
			{
				$session_data = $app->getUserState('com_kinoarhiv.movie.' . $user->id . '.edit_data.r_id');
				$session_data['id'] = $db->insertid();
				$app->setUserState('com_kinoarhiv.movie.' . $user->id . '.edit_data.r_id', $session_data);
			}

			return true;
		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}
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
		$db = $this->getDbo();
		$ids = $app->input->get('id', array(), 'array');
		$state = $isUnpublish ? 0 : 1;

		$query = $db->getQuery(true)
			->update($db->quoteName('#__ka_movies'))
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
		$db = $this->getDbo();
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
			if ($data['alias'] === null)
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
				$data['fs_alias'] = rawurlencode(StringHelper::substr($data['alias'], 0, 1));
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
		$db = $this->getDbo();
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

			$intro_countries = '<span class="cn-list">[country ln=' . $ln_str . ']: ' . StringHelper::substr($intro_countries, 0, -2) . '[/country]</span>';

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

			$intro_genres = '<span class="gn-list">[genres ln=' . $ln_str . ']: ' . StringHelper::substr($intro_genres, 0, -2) . '[/genres]</span>';

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

				$intro_directors = '<span class="dc-list">[names ln=' . $ln_str . ']: ' . StringHelper::substr($intro_directors, 0, -2) . '[/names]</span>';
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

				$intro_cast = '<span class="cast-list">[names ln=COM_KA_CAST]: ' . StringHelper::substr($intro_cast, 0, -2) . '[/names]</span>';
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
		$db = $this->getDbo();

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
				KAComponentHelper::eventLog('Error while moving the files from media folders into new location! See log for more information.');
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
		$db = $this->getDbo();
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
		$db = $this->getDbo();
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
		$db = $this->getDbo();
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
		$db = $this->getDbo();
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
	 * @return  mixed   Array of filtered data if valid, false otherwise.
	 *
	 * @see     JFormRule
	 * @see     JFilterInput
	 * @since   12.2
	 */
	public function validate($form, $data, $group = null)
	{
		// Include the plugins for the delete events.
		JPluginHelper::importPlugin($this->events_map['validate']);

		$dispatcher = JEventDispatcher::getInstance();
		$dispatcher->trigger('onUserBeforeDataValidation', array($form, &$data));

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
