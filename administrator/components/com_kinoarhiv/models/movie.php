<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2018 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

JLoader::register('KAContentHelperBackend', JPath::clean(JPATH_COMPONENT_ADMINISTRATOR . '/helpers/content.php'));

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
		$input    = JFactory::getApplication()->input;
		$task     = $input->getCmd('task', '');
		$formName = 'com_kinoarhiv.movie';
		$formOpts = array('control' => 'jform', 'load_data' => $loadData);

		switch ($task)
		{
			case 'editMovieAward':
			case 'saveMovieAward':
				$form = $this->loadForm($formName, 'relations_award', $formOpts);
				break;
			case 'editMovieCast':
			case 'saveMovieCast':
				$form = $this->loadForm($formName, 'relations_cast', $formOpts);
				break;
			case 'editMoviePremiere':
			case 'saveMoviePremiere':
				$form = $this->loadForm($formName, 'relations_premiere', $formOpts);
				break;
			case 'editMovieRelease':
			case 'saveMovieRelease':
				$form = $this->loadForm($formName, 'relations_release', $formOpts);
				break;
			default:
				$form = $this->loadForm($formName, 'movie', $formOpts);
				break;
		}

		if (empty($form))
		{
			return false;
		}

		if ($task === 'editMovieAward')
		{
			$form->setValue('type', null, 0);
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
		elseif ($task == 'editMovieAward')
		{
			return $this->editMovieAward();
		}
		elseif ($task == 'editMoviePremiere')
		{
			return $this->editMoviePremiere();
		}
		elseif ($task == 'editMovieRelease')
		{
			return $this->editMovieRelease();
		}

		$query = $db->getQuery(true)->select(
			$db->quoteName(
				array('m.id', 'm.asset_id', 'm.parent_id', 'm.title', 'm.alias', 'm.fs_alias', 'm.introtext', 'm.plot',
					'm.desc', 'm.known', 'm.year', 'm.slogan', 'm.budget', 'm.age_restrict', 'm.ua_rate', 'm.mpaa',
					'm.length', 'm.rate_loc', 'm.rate_sum_loc', 'm.imdb_votesum', 'm.imdb_votes', 'm.imdb_id',
					'm.kp_votesum', 'm.kp_votes', 'm.kp_id', 'm.rate_fc', 'm.rottentm_id', 'm.metacritics',
					'm.metacritics_id', 'm.myshows_votesum', 'm.myshows_votes', 'm.myshows_id', 'm.rate_custom',
					'm.rate_loc_rounded', 'm.rate_imdb_rounded', 'm.rate_kp_rounded', 'm.urls', 'm.buy_urls',
					'm.attribs', 'm.created', 'm.created_by', 'm.modified', 'm.modified_by', 'm.publish_up',
					'm.publish_down', 'm.state', 'm.ordering', 'm.metakey', 'm.metadesc', 'm.access', 'm.metadata',
					'm.language'
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
				. ' AND ' . $db->quoteName('g.frontpage') . ' = 1'
			);

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

		$registry = new Registry($result->metadata);
		$result->metadata = $registry->toArray();

		if ($id)
		{
			$tags = new JHelperTags;
			$tags->getTagIds($result->id, 'com_kinoarhiv.movie');
			$result->tags = $tags;
		}

		return $result;
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
	 * @return  array
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

			return array();
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
		$app       = JFactory::getApplication();
		$db        = $this->getDbo();
		$id        = $app->input->get('row_id', 0, 'int');
		$itemID    = $app->input->get('item_id', 0, 'int');
		$inputName = explode('_', $app->input->getString('input_name', ''));
		$rowID     = !empty($inputName[1]) ? $inputName[1] : 0;
		$query     = $db->getQuery(true);

		$query->select(
			$db->quoteName(
				array(
					'id', 'name_id', 'movie_id', 'type', 'role', 'dub_id', 'is_actors', 'voice_artists', 'is_directors',
					'ordering', 'desc'
				)
			)
		)->select($db->quoteName('type', 'old_type'))
			->from($db->quoteName('#__ka_rel_names'))
			->where($db->quoteName('id') . ' = ' . (int) $rowID)
			->where($db->quoteName('movie_id') . ' = ' . (int) $itemID)
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
		$app       = JFactory::getApplication();
		$db        = $this->getDbo();
		$user      = JFactory::getUser();
		$movieID   = $app->input->get('item_id', 0, 'int');
		$inputName = $app->input->getString('input_name', '');
		$ids       = explode('_', $inputName);
		$nameID    = array_key_exists(1, $ids) ? $ids[1] : 0;
		$oldType   = array_key_exists(2, $ids) ? $ids[2] : 0;
		$newType   = $data['type'];

		// Check if person allready exists in relation table
		$query = $db->getQuery(true)
			->select('COUNT(id)')
			->from($db->quoteName('#__ka_rel_names'))
			->where($db->quoteName('name_id') . ' = ' . (int) $data['name_id'])
			->where($db->quoteName('movie_id') . ' = ' . (int) $movieID);

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
echo '<pre>';
print_r($data);
		if ($total > 0)
		{
			$query = $db->getQuery(true)
				->select($db->quoteName(array('type', 'dub_id')))
				->from($db->quoteName('#__ka_rel_names'));

			if (empty($data['id']))
			{
				$query->where($db->quoteName('name_id') . ' = ' . (int) $data['name_id'])
					->where($db->quoteName('movie_id') . ' = ' . (int) $movieID);
			}
			else
			{
				$query->where($db->quoteName('id') . ' = ' . (int) $data['id']);
			}

			$db->setQuery($query);
			$rows = $db->loadObjectList();

			$query = $db->getQuery(true)
				->update($db->quoteName('#__ka_rel_names'))
				->set($db->quoteName('type') . " = ''");

			if (empty($data['id']))
			{
				$query->set($db->quoteName('name_id') . " = '" . (int) $data['name_id'] . "'");
				$query->where($db->quoteName('name_id') . ' = ' . (int) $data['name_id'])
					->where($db->quoteName('movie_id') . ' = ' . (int) $movieID);

				if (empty($data['dub_id']))
				{
					$query->where($db->quoteName('dub_id') . ' = 0');
				}
				else
				{
					$query->where($db->quoteName('dub_id') . ' > 0');
				}
			}
			else
			{
				$query->where($db->quoteName('id') . ' = ' . $data['id']);
			}
echo $query;
			/*foreach ($rows as $row)
			{
				if ($row['id'] == $data['id'])
				{
					$types = explode(',', $row['type']);
					break;
				}
				else
				{
					$types = explode(',', $rows[0]['type']);
				}
			}
print_r($types);
			if (!in_array($data['type'], $types) && $data['type'] !== $data['old_type'])
			{
				$types[] = $data['type'];
				$key = array_search($data['old_type'], $types);
				unset($types[$key]);
			}
print_r($types);
			$query = $db->getQuery(true)
				->update($db->quoteName('#__ka_rel_names'))
				->set($db->quoteName('type') . " = ''")
				->where($db->quoteName('id') . ' = ' . $data['id']);
echo $query;
			/*$query = $db->getQuery(true)
				->select($db->quoteName('type'))
				->from($db->quoteName('#__ka_rel_names'))
				->where($db->quoteName('name_id') . ' = ' . (int) $data['name_id'])
				->where($db->quoteName('movie_id') . ' = ' . (int) $id);

			$db->setQuery($query);

			try
			{
				$types = $db->loadResult();
				$typesArr = explode(',', $types);

				// Add a new type to an array of types only if he not exists in this array.
				if (!in_array($data['type'], $typesArr))
				{
					$typesArr[] = $data['type'];
					echo 1;
				}
			}
			catch (RuntimeException $e)
			{
				$app->enqueueMessage($e->getMessage(), 'error');

				return false;
			}*/
		}
		else
		{
			$query = $db->getQuery(true)
				->insert($db->quoteName('#__ka_rel_names'))
				->columns(
					$db->quoteName(
						array('id', 'name_id', 'movie_id', 'type', 'role', 'dub_id', 'is_actors', 'voice_artists', 'is_directors', 'ordering', 'desc')
					)
				)
				->values(
					"'', '" . (int) $data['name_id'] . "', '" . (int) $movieID . "', '" . (int) $data['type'] . "', " .
					"'" . $db->escape($data['role']) . "', '" . (int) $data['dub_id'] . "', '" . (int) $data['is_actors'] . "', " .
					"'" . (int) $data['voice_artists'] . "', '" . (int) $data['is_directors'] . "', " .
					"'" . (int) $data['ordering'] . "', '" . $db->escape($data['desc']) . "'"
				);

			$db->setQuery($query);

			try
			{
				$db->execute();

				$sessionData = $app->getUserState('com_kinoarhiv.movie.' . $user->id . '.edit_data.c_id');
				$sessionData['name_id'] = $data['name_id'];
				$sessionData['type'] = $data['type'];
				$app->setUserState('com_kinoarhiv.movie.' . $user->id . '.edit_data.c_id', $sessionData);
			}
			catch (RuntimeException $e)
			{
				$app->enqueueMessage($e->getMessage(), 'error');

				return false;
			}
		}

		return true;





		if (empty($inputName))
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

		/*$db->setQuery($query);

		try
		{
			//$db->execute();

			// We need to store LastInsertID in session for later use in controller.
			if (empty($input_name))
			{
				$sessionData = $app->getUserState('com_kinoarhiv.movie.' . $user->id . '.edit_data.c_id');
				$sessionData['name_id'] = $data['name_id'];
				$sessionData['type'] = $_type;
				$app->setUserState('com_kinoarhiv.movie.' . $user->id . '.edit_data.c_id', $sessionData);
			}

			return true;
		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}*/
	}

	/**
	 * Method to remove cast and crew.
	 *
	 * @param   array  $ids  Array with IDs.
	 *
	 * @return  boolean
	 *
	 * @since   3.1
	 */
	public function removeMovieCast($ids)
	{
		$app    = JFactory::getApplication();
		$db     = $this->getDbo();
		$rowIDs = array_unique($ids['row_ids']);

		// Get all rows with selected person.
		$query = $db->getQuery(true)
			->select($db->quoteName(array('id', 'type')))
			->from($db->quoteName('#__ka_rel_names'))
			->where($db->quoteName('id') . ' IN (' . implode(',', $rowIDs) . ')');

		$db->setQuery($query);

		try
		{
			$typeRows = $db->loadAssocList();
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		$queryDeleteResult = true;
		$db->lockTable('#__ka_rel_names');
		$db->transactionStart();

		foreach ($typeRows as $_types)
		{
			$typesArr = explode(',', $_types['type']);
			$newArr = array_diff($typesArr, $ids['rows'][$_types['id']]);

			// Update field 'type' with new data else remove row if only one type in field
			if (count($typesArr) > 1 && count($newArr) > 0)
			{
				$query = $db->getQuery(true)
					->update($db->quoteName('#__ka_rel_names'))
					->set($db->quoteName('type') . " = '" . implode(',', $newArr) . "'")
					->where($db->quoteName('id') . ' = ' . (int) $_types['id']);
				$db->setQuery($query . ';');

				if (!$db->execute())
				{
					$queryDeleteResult = false;
					break;
				}
			}
			else
			{
				$query = $db->getQuery(true)
					->delete($db->quoteName('#__ka_rel_names'))
					->where($db->quoteName('id') . ' = ' . (int) $_types['id']);
				$db->setQuery($query . ';');

				if (!$db->execute())
				{
					$queryDeleteResult = false;
					break;
				}
			}
		}

		if ($queryDeleteResult)
		{
			$db->transactionCommit();
		}
		else
		{
			$db->transactionRollback();
		}

		$db->unlockTables();

		return true;
	}

	/**
	 * Method to get a single record for award edit.
	 *
	 * @return  mixed  Object on success, false on failure.
	 *
	 * @since  3.1
	 */
	private function editMovieAward()
	{
		$app = JFactory::getApplication();
		$db  = $this->getDbo();
		$id  = $app->input->get('row_id', 0, 'int');

		$query = $db->getQuery(true)
			->select($db->quoteName(array('id', 'item_id', 'award_id', 'desc', 'year', 'type')))
			->from($db->quoteName('#__ka_rel_awards'))
			->where($db->quoteName('id') . ' = ' . (int) $id);

		$db->setQuery($query);

		try
		{
			$result = $db->loadObject();

			if (empty($result))
			{
				$result = (object) array();
				$result->item_id = $app->input->get('item_id', 0, 'int');
			}
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
	public function saveMovieAward($data)
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
					. "'" . $db->escape($data['desc']) . "', '" . (int) $data['year'] . "', '0'"
				);
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
				$sessionData = $app->getUserState('com_kinoarhiv.movie.' . $user->id . '.edit_data.aw_id');
				$sessionData['id'] = $db->insertid();
				$app->setUserState('com_kinoarhiv.movie.' . $user->id . '.edit_data.aw_id', $sessionData);
			}

			return true;
		}
		catch (RuntimeException $e)
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

		return $result;
	}

	/**
	 * Method to get a single record for premiere edit.
	 *
	 * @return  mixed  Object on success, false on failure.
	 *
	 * @since  3.1
	 */
	private function editMoviePremiere()
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

			if (empty($result))
			{
				$result = (object) array();
				$result->movie_id = $app->input->get('item_id', 0, 'int');
			}
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
	public function saveMoviePremiere($data)
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
					. ", '" . $db->escape($data['language']) . "', '" . (int) $data['ordering'] . "'"
				);
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
				$sessionData = $app->getUserState('com_kinoarhiv.movie.' . $user->id . '.edit_data.p_id');
				$sessionData['id'] = $db->insertid();
				$app->setUserState('com_kinoarhiv.movie.' . $user->id . '.edit_data.p_id', $sessionData);
			}

			return true;
		}
		catch (RuntimeException $e)
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
	private function editMovieRelease()
	{
		$app   = JFactory::getApplication();
		$db    = $this->getDbo();
		$id    = $app->input->get('row_id', 0, 'int');
		$query = $db->getQuery(true);

		$query->select(
			$db->quoteName(
				array(
					'id', 'country_id', 'vendor_id', 'item_id', 'item_type', 'media_type', 'release_date', 'desc',
					'language', 'ordering'
				)
			)
		)
			->from($db->quoteName('#__ka_releases'))
			->where($db->quoteName('id') . ' = ' . (int) $id);

		$db->setQuery($query);

		try
		{
			$result = $db->loadObject();

			if (empty($result))
			{
				$result = (object) array();
				$result->item_id = $app->input->get('item_id', 0, 'int');
			}
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
	public function saveMovieRelease($data)
	{
		$app  = JFactory::getApplication();
		$db   = $this->getDbo();
		$user = JFactory::getUser();
		$id   = $app->input->get('item_id', 0, 'int');

		if (empty($data['id']))
		{
			$query = $db->getQuery(true)
				->insert($db->quoteName('#__ka_releases'))
				->columns(
					$db->quoteName(
						array(
							'id', 'country_id', 'vendor_id', 'item_id', 'media_type', 'item_type', 'release_date',
							'desc', 'language', 'ordering'
						)
					)
				)
				->values("'', '" . (int) $data['country_id'] . "', '" . (int) $data['vendor_id'] . "', "
					. "'" . (int) $id . "', '" . (int) $data['media_type'] . "', '" . (int) $data['item_type'] . "', "
					. "'" . $db->escape($data['release_date']) . "', '" . $db->escape($data['desc']) . "', "
					. "'" . $db->escape($data['language']) . "', '" . (int) $data['ordering'] . "'"
				);
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
				$sessionData = $app->getUserState('com_kinoarhiv.movie.' . $user->id . '.edit_data.r_id');
				$sessionData['id'] = $db->insertid();
				$app->setUserState('com_kinoarhiv.movie.' . $user->id . '.edit_data.r_id', $sessionData);
			}

			return true;
		}
		catch (RuntimeException $e)
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
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}
	}

	/**
	 * Method to save an item data.
	 *
	 * @param   array  $data  Form data
	 *
	 * @return  boolean
	 *
	 * @since   3.0
	 */
	public function save($data)
	{
		$app    = JFactory::getApplication();
		$db     = $this->getDbo();
		$user   = JFactory::getUser();
		$date   = JFactory::getDate();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$title  = trim($data['title']);

		// Automatic handling of alias for empty fields
		if (in_array($app->input->get('task'), array('apply', 'save', 'save2new'))
			&& (!isset($data['id']) || (int) $data['id'] == 0 || $data['alias'] == ''))
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

		// Get attribs
		$attribs = json_encode($data['attribs']);

		// Get metadata
		$metadata = json_encode((object) array('robots' => $data['robots']));

		// Prepare some data
		$year                 = str_replace(' ', '', $data['year']);
		$rateLocalRounded     = ((int) $data['rate_loc'] > 0 && (int) $data['rate_sum_loc'] > 0)
			? round($data['rate_sum_loc'] / $data['rate_loc'], 0) : 0;
		$rateImdbRounded      = $data['imdb_votesum'] > 0 ? round($data['imdb_votesum'], 0) : 0;
		$rateKPRounded        = $data['kp_votesum'] > 0 ? round($data['kp_votesum'], 0) : 0;
		$introtext            = $this->createIntroText($data, $params, $data['id']);
		$createdBy            = empty($data['created_by']) ? $user->get('id') : $data['created_by'];
		$modifiedBy           = empty($data['modified_by']) ? $user->get('id') : $data['modified_by'];
		$data['created']      = (empty($data['created']) || $data['created'] == $db->getNullDate()) ? $date->toSql() : $data['created'];
		$data['publish_up']   = (empty($data['publish_up']) || $data['publish_up'] == $db->getNullDate()) ? $date->toSql() : $data['publish_up'];
		$data['publish_down'] = ($data['publish_down'] == $db->getNullDate()) ? $date->toSql() : $data['publish_down'];
		$data['modified']     = $date->toSql();

		if (empty($data['id']))
		{
			// Check if movie with this title allready exists
			$query = $db->getQuery(true);

			$query->select('COUNT(id)')
				->from($db->quoteName('#__ka_movies'))
				->where($db->quoteName('title') . " = '" . $db->escape($title) . "'");

			$db->setQuery($query);
			$count = $db->loadResult();

			if ($count > 0)
			{
				$app->enqueueMessage(JText::_('COM_KA_MOVIES_EXISTS'), 'error');

				return false;
			}

			$values = array(
				'id'                => '',
				'asset_id'          => 0,
				'parent_id'         => (int) $data['parent_id'],
				'title'             => $db->escape($title),
				'alias'             => $data['alias'],
				'fs_alias'          => $data['fs_alias'],
				'introtext'         => $db->escape($introtext),
				'plot'              => $db->escape($data['plot']),
				'desc'              => $db->escape($data['desc']),
				'known'             => $db->escape($data['known']),
				'year'              => $db->escape($year),
				'slogan'            => $db->escape($data['slogan']),
				'budget'            => $data['budget'],
				'age_restrict'      => $data['age_restrict'],
				'ua_rate'           => $data['ua_rate'],
				'mpaa'              => $data['mpaa'],
				'length'            => $data['length'],
				'rate_loc'          => (int) $data['rate_loc'],
				'rate_sum_loc'      => (int) $data['rate_sum_loc'],
				'imdb_votesum'      => $data['imdb_votesum'],
				'imdb_votes'        => (int) $data['imdb_votes'],
				'imdb_id'           => $data['imdb_id'],
				'kp_votesum'        => $data['kp_votesum'],
				'kp_votes'          => (int) $data['kp_votes'],
				'kp_id'             => (int) $data['kp_id'],
				'rate_fc'           => (int) $data['rate_fc'],
				'rottentm_id'       => $data['rottentm_id'],
				'metacritics'       => (int) $data['metacritics'],
				'metacritics_id'    => $data['metacritics_id'],
				'myshows_votesum'   => $data['myshows_votesum'],
				'myshows_votes'     => (int) $data['myshows_votes'],
				'myshows_id'        => (int) $data['myshows_id'],
				'rate_custom'       => $db->escape($data['rate_custom']),
				'rate_loc_rounded'  => $rateLocalRounded,
				'rate_imdb_rounded' => $rateImdbRounded,
				'rate_kp_rounded'   => $rateKPRounded,
				'urls'              => $db->escape($data['urls']),
				'buy_urls'          => $db->escape($data['buy_urls']),
				'attribs'           => $attribs,
				'created'           => $data['created'],
				'created_by'        => $createdBy,
				'modified'          => $data['modified'],
				'modified_by'       => $modifiedBy,
				'publish_up'        => $data['publish_up'],
				'publish_down'      => $data['publish_down'],
				'state'             => $data['state'],
				'ordering'          => (int) $data['ordering'],
				'metakey'           => $db->escape($data['metakey']),
				'metadesc'          => $db->escape($data['metadesc']),
				'access'            => (int) $data['access'],
				'metadata'          => $metadata,
				'language'          => $data['language']
			);

			$query = $db->getQuery(true)
				->insert($db->quoteName('#__ka_movies'))
				->columns($db->quoteName(array_keys($values)))
				->values("'" . implode("','", array_values($values)) . "'");
		}
		else
		{
			$query = $db->getQuery(true)
				->update($db->quoteName('#__ka_movies'))
				->set($db->quoteName('parent_id') . " = '" . (int) $data['parent_id'] . "'")
				->set($db->quoteName('title') . " = '" . $db->escape($title) . "'")
				->set($db->quoteName('alias') . " = '" . $data['alias'] . "'")
				->set($db->quoteName('fs_alias') . " = '" . $data['fs_alias'] . "'")
				->set($db->quoteName('introtext') . " = '" . $db->escape($introtext) . "'")
				->set($db->quoteName('plot') . " = '" . $db->escape($data['plot']) . "'")
				->set($db->quoteName('desc') . " = '" . $db->escape($data['desc']) . "'")
				->set($db->quoteName('known') . " = '" . $db->escape($data['known']) . "'")
				->set($db->quoteName('year') . " = '" . (int) $year . "'")
				->set($db->quoteName('slogan') . " = '" . $db->escape($data['slogan']) . "'")
				->set($db->quoteName('budget') . " = '" . $data['budget'] . "'")
				->set($db->quoteName('age_restrict') . " = '" . $data['age_restrict'] . "'")
				->set($db->quoteName('ua_rate') . " = '" . $data['ua_rate'] . "'")
				->set($db->quoteName('mpaa') . " = '" . $data['mpaa'] . "'")
				->set($db->quoteName('length') . " = '" . $data['length'] . "'")
				->set($db->quoteName('rate_loc') . " = '" . (int) $data['rate_loc'] . "'")
				->set($db->quoteName('rate_sum_loc') . " = '" . (int) $data['rate_sum_loc'] . "'")
				->set($db->quoteName('imdb_votesum') . " = '" . $data['imdb_votesum'] . "'")
				->set($db->quoteName('imdb_votes') . " = '" . (int) $data['imdb_votes'] . "'")
				->set($db->quoteName('imdb_id') . " = '" . $data['imdb_id'] . "'")
				->set($db->quoteName('kp_votesum') . " = '" . $data['kp_votesum'] . "'")
				->set($db->quoteName('kp_votes') . " = '" . (int) $data['kp_votes'] . "'")
				->set($db->quoteName('kp_id') . " = '" . (int) $data['kp_id'] . "'")
				->set($db->quoteName('rate_fc') . " = '" . (int) $data['rate_fc'] . "'")
				->set($db->quoteName('rottentm_id') . " = '" . $data['rottentm_id'] . "'")
				->set($db->quoteName('metacritics') . " = '" . (int) $data['metacritics'] . "'")
				->set($db->quoteName('metacritics_id') . " = '" . $data['metacritics_id'] . "'")
				->set($db->quoteName('myshows_votesum') . " = '" . $data['myshows_votesum'] . "'")
				->set($db->quoteName('myshows_votes') . " = '" . (int) $data['myshows_votes'] . "'")
				->set($db->quoteName('myshows_id') . " = '" . (int) $data['myshows_id'] . "'")
				->set($db->quoteName('rate_custom') . " = '" . $db->escape($data['rate_custom']) . "'")
				->set($db->quoteName('rate_loc_rounded') . " = '" . $rateLocalRounded . "'")
				->set($db->quoteName('rate_imdb_rounded') . " = '" . $rateImdbRounded . "'")
				->set($db->quoteName('rate_kp_rounded') . " = '" . $rateKPRounded . "'")
				->set($db->quoteName('urls') . " = '" . $db->escape($data['urls']) . "'")
				->set($db->quoteName('buy_urls') . " = '" . $db->escape($data['buy_urls']) . "'")
				->set($db->quoteName('attribs') . " = '" . $attribs . "'")
				->set($db->quoteName('created') . " = '" . $data['created'] . "'")
				->set($db->quoteName('created_by') . " = '" . $createdBy . "'")
				->set($db->quoteName('modified') . " = '" . $data['modified'] . "'")
				->set($db->quoteName('modified_by') . " = '" . $modifiedBy . "'")
				->set($db->quoteName('publish_up') . " = '" . $data['publish_up'] . "'")
				->set($db->quoteName('publish_down') . " = '" . $data['publish_down'] . "'")
				->set($db->quoteName('state') . " = '" . $data['state'] . "'")
				->set($db->quoteName('ordering') . " = '" . (int) $data['ordering'] . "'")
				->set($db->quoteName('metakey') . " = '" . $db->escape($data['metakey']) . "'")
				->set($db->quoteName('metadesc') . " = '" . $db->escape($data['metadesc']) . "'")
				->set($db->quoteName('access') . " = '" . (int) $data['access'] . "'")
				->set($db->quoteName('metadata') . " = '" . $metadata . "'")
				->set($db->quoteName('language') . " = '" . $db->escape($data['language']) . "'")
				->where($db->quoteName('id') . ' = ' . (int) $data['id']);
		}

		$db->setQuery($query);

		try
		{
			$db->execute();

			// We need to store LastInsertID in session for later use in controller.
			if (empty($data['id']))
			{
				$insertID = $db->insertid();
				$sessionData = $app->getUserState('com_kinoarhiv.movies.' . $user->id . '.edit_data');
				$sessionData['id'] = $insertID;
				$app->setUserState('com_kinoarhiv.movies.' . $user->id . '.edit_data', $sessionData);
			}
			else
			{
				// Alias was changed? Move all linked items into new filesystem location.
				if ($data['fs_alias'] != $data['fs_alias_orig'])
				{
					$this->moveMediaItems($data['id'], $data['fs_alias_orig'], $data['fs_alias']);
				}
			}
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		// Update the rules.
		if ($user->authorise('core.edit.access', 'com_kinoarhiv') && isset($data['rules']))
		{
			$title = $db->escape($title);

			if (empty($data['id']))
			{
				$assetID = KAContentHelperBackend::saveAccessRules(null, 'com_kinoarhiv.movie.' . $insertID, $title, $data['rules']);
				$query = $db->getQuery(true)
					->update($db->quoteName('#__ka_movies'))
					->set($db->quoteName('asset_id') . ' = ' . (int) $assetID);

				$db->setQuery($query);

				try
				{
					$db->execute();
				}
				catch (RuntimeException $e)
				{
					$app->enqueueMessage($e->getMessage(), 'error');

					return false;
				}
			}
			else
			{
				KAContentHelperBackend::saveAccessRules($data['id'], 'com_kinoarhiv.movie.' . $data['id'], $title, $data['rules']);
			}
		}

		if (empty($data['id']))
		{
			$data['id'] = $insertID;
		}

		// Update countries.
		if (!empty($data['countries']) && ($data['countries_orig'] != $data['countries'][0]))
		{
			$this->saveCountries($data['id'], $data['countries'][0]);
		}

		// Update genres.
		if (!empty($data['genres']) && ($data['genres_orig'] != $data['genres'][0]))
		{
			$this->saveGenres($data['id'], $data['genres'][0]);
		}

		KAContentHelperBackend::updateGenresStat($data['genres_orig'], $data['genres'], '#__ka_rel_genres');
		KAContentHelperBackend::updateTagMapping($data['id'], $data['tags'], 'com_kinoarhiv.movie');

		// Clear the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Create intro text.
	 *
	 * @param   array    $data    Movie info
	 * @param   object   $params  Component parameters
	 * @param   integer  $id      Item ID
	 *
	 * @return  string
	 *
	 * @since   3.1
	 */
	private function createIntroText($data, $params, $id)
	{
		jimport('components.com_kinoarhiv.helpers.content', JPATH_ROOT);

		$db                = $this->getDbo();
		$data['countries'] = array_filter($data['countries']);
		$data['genres']    = array_filter($data['genres']);
		$introtext         = array();

		// Process intro text for countries
		if (!empty($data['countries']))
		{
			$_countries = implode(',', $data['countries']);
			$query = $db->getQuery(true)
				->select($db->quoteName(array('name', 'code')))
				->from($db->quoteName('#__ka_countries'))
				->where($db->quoteName('id') . ' IN (' . $_countries . ')')
				// Preserve row ordering
				->order('FIELD (' . $db->quoteName('id') . ', ' . $_countries . ')');

			$db->setQuery($query);
			$countries = $db->loadObjectList();

			$languageConst = count($countries) > 1 ? 'COM_KA_COUNTRIES' : 'COM_KA_COUNTRY';
			$countriesStr = '';

			foreach ($countries as $cn)
			{
				$countriesStr .= '[cn=' . $cn->code . ']' . $cn->name . '[/cn], ';
			}

			$introtext[] = '<span class="cn-list">[country ln=' . $languageConst . ']: ' . StringHelper::substr($countriesStr, 0, -2) . '[/country]</span>';
		}

		// Process intro text for genres
		if (!empty($data['genres']))
		{
			$_genres = implode(',', $data['genres']);
			$query = $db->getQuery(true)
				->select($db->quoteName('name'))
				->from($db->quoteName('#__ka_genres'))
				->where($db->quoteName('id') . ' IN (' . $_genres . ')')
				// Preserve row ordering
				->order('FIELD (' . $db->quoteName('id') . ', ' . $_genres . ')');

			$db->setQuery($query);
			$genres = $db->loadObjectList();

			$languageConst = count($genres) > 1 ? 'COM_KA_GENRES' : 'COM_KA_GENRE';
			$genresStr = '';

			foreach ($genres as $genre)
			{
				$genresStr .= StringHelper::strtolower($genre->name) . ', ';
			}

			$introtext[] = '<span class="gn-list">[genres ln=' . $languageConst . ']: ' . StringHelper::substr($genresStr, 0, -2) . '[/genres]</span>';
		}

		// Process directors and cast
		if (!empty($id))
		{
			// Start processing intro text for director(s) IDs
			$query = $db->getQuery(true)
				->select($db->quoteName(array('rel.name_id', 'n.name', 'n.latin_name')))
				->from($db->quoteName('#__ka_rel_names', 'rel'))
				->join('LEFT', $db->quoteName('#__ka_names', 'n') . ' ON ' . $db->quoteName('n.id') . ' = ' . $db->quoteName('rel.name_id'))
				->where($db->quoteName('rel.movie_id') . ' = ' . $id . ' AND ' . $db->quoteName('rel.is_directors') . ' = 1')
				->order($db->quoteName('rel.ordering'));

			if ($params->get('introtext_actors_list_limit') > 0)
			{
				$query->setLimit($params->get('introtext_actors_list_limit'), 0);
			}

			$db->setQuery($query);
			$directors = $db->loadObjectList();

			if (count($directors) > 0)
			{
				$languageConst = count($directors) > 1 ? 'COM_KA_DIRECTORS' : 'COM_KA_DIRECTOR';
				$directorsStr  = '';

				foreach ($directors as $director)
				{
					$directorsStr .= '[name=' . $director->name_id . ']' . KAContentHelper::formatItemTitle($director->name, $director->latin_name) . '[/name], ';
				}

				$introtext[] = '<span class="dc-list">[names ln=' . $languageConst . ']: ' . StringHelper::substr($directorsStr, 0, -2) . '[/names]</span>';
			}

			// End

			// Start processing intro text for cast
			$query = $db->getQuery(true)
				->select($db->quoteName(array('rel.name_id', 'n.name', 'n.latin_name')))
				->from($db->quoteName('#__ka_rel_names', 'rel'))
				->join('LEFT', $db->quoteName('#__ka_names', 'n') . ' ON ' . $db->quoteName('n.id') . ' = ' . $db->quoteName('rel.name_id'))
				->where('rel.movie_id = ' . $id . ' AND rel.is_actors = 1 AND rel.voice_artists = 0')
				->order($db->quoteName('rel.ordering'));

			if ($params->get('introtext_actors_list_limit') > 0)
			{
				$query->setLimit($params->get('introtext_actors_list_limit'), 0);
			}

			$db->setQuery($query);
			$cast = $db->loadObjectList();

			if (count($cast) > 0)
			{
				$castStr  = '';

				foreach ($cast as $name)
				{
					$castStr .= '[name=' . $name->name_id . ']' . KAContentHelper::formatItemTitle($name->name, $name->latin_name) . '[/name], ';
				}

				$introtext[] = '<span class="cast-list">[names ln=COM_KA_CAST]: ' . StringHelper::substr($castStr, 0, -2) . '[/names]</span>';
			}

			// End
		}

		return implode('', $introtext);
	}

	/**
	 * Save countries to relation table.
	 *
	 * @param   integer  $id         Item ID.
	 * @param   string   $countries  Comma separated string with countries ID.
	 *
	 * @return  boolean
	 *
	 * @since   3.1
	 */
	protected function saveCountries($id, $countries)
	{
		$app         = JFactory::getApplication();
		$db          = $this->getDbo();
		$countries   = explode(',', $countries);
		$queryResult = true;

		$db->lockTable('#__ka_rel_countries');
		$db->transactionStart();

		if (!empty($id))
		{
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__ka_rel_countries'))
				->where($db->quoteName('movie_id') . ' = ' . (int) $id);

			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (RuntimeException $e)
			{
				$app->enqueueMessage($e->getMessage(), 'error');

				return false;
			}
		}

		foreach ($countries as $key => $countryID)
		{
			$query = $db->getQuery(true);

			$query->insert($db->quoteName('#__ka_rel_countries'))
				->columns($db->quoteName(array('country_id', 'movie_id', 'ordering')))
				->values("'" . (int) $countryID . "', '" . (int) $id . "', '" . $key . "'");
			$db->setQuery($query . ';');

			if ($db->execute() === false)
			{
				$queryResult = false;
				break;
			}
		}

		if ($queryResult === false)
		{
			$db->transactionRollback();
			$app->enqueueMessage('Failed to update countries!', 'error');
		}
		else
		{
			$db->transactionCommit();
		}

		$db->unlockTables();

		return (bool) $queryResult;
	}

	/**
	 * Save genres to relation table.
	 *
	 * @param   integer  $id      Item ID.
	 * @param   string   $genres  Comma separated string with genre ID.
	 *
	 * @return  boolean
	 *
	 * @since   3.1
	 */
	protected function saveGenres($id, $genres)
	{
		$app         = JFactory::getApplication();
		$db          = $this->getDbo();
		$genres      = explode(',', $genres);
		$queryResult = true;

		$db->lockTable('#__ka_rel_genres');
		$db->transactionStart();

		if (!empty($id))
		{
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__ka_rel_genres'))
				->where($db->quoteName('movie_id') . ' = ' . (int) $id);

			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (RuntimeException $e)
			{
				$app->enqueueMessage($e->getMessage(), 'error');

				return false;
			}
		}

		foreach ($genres as $key => $genreID)
		{
			$query = $db->getQuery(true);

			$query->insert($db->quoteName('#__ka_rel_genres'))
				->columns($db->quoteName(array('genre_id', 'movie_id', 'ordering')))
				->values("'" . (int) $genreID . "', '" . (int) $id . "', '" . $key . "'");
			$db->setQuery($query . ';');

			if ($db->execute() === false)
			{
				$queryResult = false;
				break;
			}
		}

		if ($queryResult === false)
		{
			$db->transactionRollback();
			$app->enqueueMessage('Failed to update genres!', 'error');
		}
		else
		{
			$db->transactionCommit();
		}

		$db->unlockTables();

		return (bool) $queryResult;
	}

	/**
	 * Method to move all media items to new location, if alias was changed.
	 *
	 * @param   int     $id        Item ID.
	 * @param   string  $oldAlias  Old name alias.
	 * @param   string  $newAlias  New name alias.
	 *
	 * @return  boolean
	 *
	 * @since   3.1
	 */
	private function moveMediaItems($id, $oldAlias, $newAlias)
	{
		$app = JFactory::getApplication();

		if (empty($id) || empty($oldAlias) || empty($newAlias))
		{
			return false;
		}

		jimport('joomla.filesystem.folder');
		jimport('components.com_kinoarhiv.libraries.filesystem', JPATH_ROOT);

		$params          = JComponentHelper::getParams('com_kinoarhiv');
		$filesystem      = KAFilesystem::getInstance();
		$pathPoster      = $params->get('media_posters_root');
		$pathWallpp      = $params->get('media_wallpapers_root');
		$pathScr         = $params->get('media_scr_root');
		$oldFolderPoster = JPath::clean($pathPoster . '/' . $oldAlias . '/' . $id . '/posters');
		$oldFolderWallpp = JPath::clean($pathWallpp . '/' . $oldAlias . '/' . $id . '/wallpapers');
		$oldFolderScr    = JPath::clean($pathScr . '/' . $oldAlias . '/' . $id . '/screenshots');
		$newFolderPoster = JPath::clean($pathPoster . '/' . $newAlias . '/' . $id . '/posters');
		$newFolderWallpp = JPath::clean($pathWallpp . '/' . $newAlias . '/' . $id . '/wallpapers');
		$newFolderScr    = JPath::clean($pathScr . '/' . $newAlias . '/' . $id . '/screenshots');

		if (!$filesystem->move(
			array($oldFolderPoster, $oldFolderWallpp, $oldFolderScr),
			array($newFolderPoster, $newFolderWallpp, $newFolderScr)
		))
		{
			$app->enqueueMessage('Error while moving the files from media folders into new location! See log for more information.', 'error');
		}

		// Remove parent folder for posters/wallpapers/screenshots. Delete only if folder(s) is empty.
		$_posterPath = JPath::clean($pathPoster . '/' . $oldAlias . '/' . $id);
		$_wallppPath = JPath::clean($pathWallpp . '/' . $oldAlias . '/' . $id);
		$_scrPath    = JPath::clean($pathScr . '/' . $oldAlias . '/' . $id);

		if (file_exists($_posterPath) && $filesystem->getFolderSize($_posterPath) === 0)
		{
			JFolder::delete($_posterPath);
		}

		if (file_exists($_wallppPath) && $filesystem->getFolderSize($_wallppPath) === 0)
		{
			JFolder::delete($_wallppPath);
		}

		if (file_exists($_scrPath) && $filesystem->getFolderSize($_scrPath) === 0)
		{
			JFolder::delete($_scrPath);
		}

		// Move trailers into new location
		$pathTrailers      = $params->get('media_trailers_root');
		$oldFolderTrailers = JPath::clean($pathTrailers . '/' . $oldAlias . '/' . $id);
		$newFolderTrailers = JPath::clean($pathTrailers . '/' . $newAlias . '/' . $id);

		if (!$filesystem->move($oldFolderTrailers, $newFolderTrailers))
		{
			$app->enqueueMessage('Error while moving the files from trailer folders into new location! See log for more information.', 'error');

			return false;
		}

		return true;
	}

	/**
	 * Removes an item.
	 *
	 * @param   array  $ids  Array of ID to remove.
	 *
	 * @return  boolean
	 *
	 * @since   3.0
	 */
	public function remove($ids = array())
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();

		// Remove associated awards
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__ka_rel_awards'))
			->where($db->quoteName('item_id') . ' IN (' . implode(',', $ids) . ') AND ' . $db->quoteName('type') . ' = 0');

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
		}

		// Remove associated countries
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__ka_rel_countries'))
			->where($db->quoteName('movie_id') . ' IN (' . implode(',', $ids) . ')');

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
		}

		// Remove associated genres
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__ka_rel_genres'))
			->where($db->quoteName('movie_id') . ' IN (' . implode(',', $ids) . ')');

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
		}

		// Remove associated names
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__ka_rel_names'))
			->where($db->quoteName('movie_id') . ' IN (' . implode(',', $ids) . ')');

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
		}

		// Remove associated releases
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__ka_releases'))
			->where($db->quoteName('item_id') . ' IN (' . implode(',', $ids) . ')')
			->where($db->quoteName('item_type') . ' = 0');

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
		}

		// Remove associated premieres
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__ka_premieres'))
			->where($db->quoteName('movie_id') . ' IN (' . implode(',', $ids) . ')');

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
		}

		// Remove reviews
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__ka_reviews'))
			->where($db->quoteName('item_id') . ' IN (' . implode(',', $ids) . ')')
			->where($db->quoteName('item_type') . ' = 0');

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
		}

		// Remove favorited and watched movies
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__ka_user_marked_movies'))
			->where($db->quoteName('movie_id') . ' IN (' . implode(',', $ids) . ')');

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
		}

		// Remove user votes
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__ka_user_votes_movies'))
			->where($db->quoteName('movie_id') . ' IN (' . implode(',', $ids) . ')');

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
		}

		// Remove tags mapping
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__contentitem_tag_map'))
			->where($db->quoteName('type_alias') . ' = ' . $db->quote('com_kinoarhiv.movie'))
			->where($db->quoteName('content_item_id') . ' IN (' . implode(',', $ids) . ')');

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
		}

		// Remove access rules
		$queryResult = true;
		$db->lockTable('#__assets');
		$db->transactionStart();

		foreach ($ids as $id)
		{
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__assets'))
				->where($db->quoteName('name') . " = 'com_kinoarhiv.movie." . (int) $id . "' AND " . $db->quoteName('level') . " = 2");

			$db->setQuery($query . ';');

			if ($db->execute() === false)
			{
				$queryResult = false;
				break;
			}
		}

		if ($queryResult === false)
		{
			$db->transactionRollback();
		}
		else
		{
			$db->transactionCommit();
		}

		$db->unlockTables();

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
			$app->enqueueMessage($e->getMessage(), 'error');
		}

		// Remove gallery items
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__ka_movies_gallery'))
			->where($db->quoteName('movie_id') . ' IN (' . implode(',', $ids) . ')');

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		return true;
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param   JForm   $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  array|boolean  Array of filtered data if valid, false otherwise.
	 *
	 * @see     JFormRule
	 * @see     JFilterInput
	 * @since   3.7.0
	 */
	public function validate($form, $data, $group = null)
	{
		// Don't allow to change the users if not allowed to access com_users.
		if (JFactory::getApplication()->isClient('administrator') && !JFactory::getUser()->authorise('core.manage', 'com_users'))
		{
			if (isset($data['created_by']))
			{
				unset($data['created_by']);
			}

			if (isset($data['modified_by']))
			{
				unset($data['modified_by']);
			}
		}

		return parent::validate($form, $data, $group);
	}
}
