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

use Joomla\Registry\Registry;
use Joomla\String\StringHelper;

/**
 * Class KinoarhivModelAlbum
 *
 * @since  3.1
 */
class KinoarhivModelAlbum extends JModelForm
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
		$formName = 'com_kinoarhiv.album';
		$formOpts = array('control' => 'jform', 'load_data' => $loadData);

		switch ($task)
		{
			case 'editTracks':
			case 'saveTracks':
				$form = $this->loadForm($formName, 'relations_tracks', $formOpts);
				break;
			default:
				$form = $this->loadForm($formName, 'album', $formOpts);
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
		$data = JFactory::getApplication()->getUserState('com_kinoarhiv.music.albums.' . JFactory::getUser()->id . '.edit_data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @return  mixed  Object on success, false on failure.
	 *
	 * @since   3.0
	 */
	public function getItem()
	{
		$app  = JFactory::getApplication();
		$db   = $this->getDbo();
		$task = $app->input->get('task', '', 'cmd');
		$id   = $app->input->get('id', 0, 'int');

		if ($task == 'editTracks')
		{
			return $this->editTracks();
		}

		$query = $db->getQuery(true)
			->select(
				$db->quoteName(
					array('a.id', 'a.asset_id', 'a.title', 'a.alias', 'a.fs_alias', 'a.year', 'a.length', 'a.isrc',
						'a.desc', 'a.rate', 'a.rate_sum', 'a.covers_path', 'a.covers_path_www', 'a.cover_filename',
						'a.tracks_path', 'a.tracks_path_www', 'a.tracks_preview_path', 'a.buy_urls', 'a.attribs',
						'a.created', 'a.created_by', 'a.modified', 'a.modified_by', 'a.publish_up', 'a.publish_down',
						'a.ordering', 'a.metakey', 'a.metadesc', 'a.access', 'a.metadata', 'a.language', 'a.state'
					)
				)
			)
			->select($db->quoteName('a.fs_alias', 'fs_alias_orig'))
			->from($db->quoteName('#__ka_music_albums', 'a'))
			->where($db->quoteName('a.id') . ' = ' . (int) $id);

		// Join over the language
		$query->select($db->quoteName('l.title', 'language_title'))
			->leftJoin($db->quoteName('#__languages', 'l') . ' ON ' . $db->quoteName('l.lang_code') . ' = ' . $db->quoteName('a.language'));

		$db->setQuery($query);

		try
		{
			$result = $db->loadObject();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

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

		$registry = new Registry($result->attribs);
		$result->attribs = $registry->toArray();

		$registry = new Registry($result->metadata);
		$result->metadata = $registry->toArray();

		if ($id)
		{
			$tags = new JHelperTags;
			$tags->getTagIds($result->id, 'com_kinoarhiv.album');
			$result->tags = $tags;
		}

		return $result;
	}

	/**
	 * Method to save an item data.
	 *
	 * @param   array  $data  Form data
	 *
	 * @return  boolean
	 *
	 * @since   3.1
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
		$rateLocalRounded     = ((int) $data['rate'] > 0 && (int) $data['rate_sum'] > 0)
			? round($data['rate_sum'] / $data['rate'], 0) : 0;
		$introtext            = $this->createIntroText($data, $params, $data['id']);
		$coversPath           = JPath::clean($data['covers_path']);
		$coversPathWWW        = JPath::clean($data['covers_path_www']);
		$coverFilename        = JPath::clean($data['cover_filename']);
		$tracksPath           = JPath::clean($data['tracks_path']);
		$tracksPathWWW        = JPath::clean($data['tracks_path_www']);
		$tracksPreviewPath    = JPath::clean($data['tracks_preview_path']);
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
				->from($db->quoteName('#__ka_music_albums'))
				->where($db->quoteName('title') . " = '" . $db->escape($title) . "'");

			$db->setQuery($query);
			$count = $db->loadResult();

			if ($count > 0)
			{
				$app->enqueueMessage(JText::_('COM_KA_MUSIC_ALBUMS_EXISTS'), 'error');

				return false;
			}

			$values = array(
				'id'                  => '',
				'asset_id'            => 0,
				'title'               => $db->escape($title),
				'alias'               => $data['alias'],
				'fs_alias'            => $data['fs_alias'],
				'introtext'           => $db->escape($introtext),
				'year'                => $data['year'],
				'length'              => $data['length'],
				'isrc'                => $db->escape($data['isrc']),
				'desc'                => $db->escape($data['desc']),
				'rate'                => (int) $data['rate_loc'],
				'rate_sum'            => (int) $data['rate_sum_loc'],
				'rate_loc_rounded'    => $rateLocalRounded,
				'covers_path'         => $db->escape($coversPath),
				'covers_path_www'     => $db->escape($coversPathWWW),
				'cover_filename'      => $db->escape($coverFilename),
				'tracks_path'         => $db->escape($tracksPath),
				'tracks_path_www'     => $db->escape($tracksPathWWW),
				'tracks_preview_path' => $db->escape($tracksPreviewPath),
				'buy_urls'            => $db->escape($data['buy_urls']),
				'attribs'             => $attribs,
				'created'             => $data['created'],
				'created_by'          => $createdBy,
				'modified'            => $data['modified'],
				'modified_by'         => $modifiedBy,
				'publish_up'          => $data['publish_up'],
				'publish_down'        => $data['publish_down'],
				'ordering'            => (int) $data['ordering'],
				'metakey'             => $db->escape($data['metakey']),
				'metadesc'            => $db->escape($data['metadesc']),
				'access'              => (int) $data['access'],
				'metadata'            => $metadata,
				'language'            => $data['language'],
				'state'               => $data['state']
			);

			$query = $db->getQuery(true)
				->insert($db->quoteName('#__ka_music_albums'))
				->columns($db->quoteName(array_keys($values)))
				->values("'" . implode("','", array_values($values)) . "'");
		}
		else
		{
			$query = $db->getQuery(true)
				->update($db->quoteName('#__ka_music_albums'))
				->set($db->quoteName('title') . " = '" . $db->escape($title) . "'")
				->set($db->quoteName('alias') . " = '" . $data['alias'] . "'")
				->set($db->quoteName('fs_alias') . " = '" . $data['fs_alias'] . "'")
				->set($db->quoteName('introtext') . " = '" . $db->escape($introtext) . "'")
				->set($db->quoteName('year') . " = '" . $data['year'] . "'")
				->set($db->quoteName('length') . " = '" . $data['length'] . "'")
				->set($db->quoteName('isrc') . " = '" . $db->escape($data['isrc']) . "'")
				->set($db->quoteName('desc') . " = '" . $db->escape($data['desc']) . "'")
				->set($db->quoteName('rate') . " = '" . (int) $data['rate'] . "'")
				->set($db->quoteName('rate_sum') . " = '" . (int) $data['rate_sum'] . "'")
				->set($db->quoteName('rate_rounded') . " = '" . $rateLocalRounded . "'")
				->set($db->quoteName('covers_path') . " = '" . $db->escape($coversPath) . "'")
				->set($db->quoteName('covers_path_www') . " = '" . $db->escape($coversPathWWW) . "'")
				->set($db->quoteName('cover_filename') . " = '" . $db->escape($coverFilename) . "'")
				->set($db->quoteName('tracks_path') . " = '" . $db->escape($tracksPath) . "'")
				->set($db->quoteName('tracks_path_www') . " = '" . $db->escape($tracksPathWWW) . "'")
				->set($db->quoteName('tracks_preview_path') . " = '" . $db->escape($tracksPreviewPath) . "'")
				->set($db->quoteName('buy_urls') . " = '" . $db->escape($data['buy_urls']) . "'")
				->set($db->quoteName('attribs') . " = '" . $attribs . "'")
				->set($db->quoteName('created') . " = '" . $data['created'] . "'")
				->set($db->quoteName('created_by') . " = '" . $createdBy . "'")
				->set($db->quoteName('modified') . " = '" . $data['modified'] . "'")
				->set($db->quoteName('modified_by') . " = '" . $modifiedBy . "'")
				->set($db->quoteName('publish_up') . " = '" . $data['publish_up'] . "'")
				->set($db->quoteName('publish_down') . " = '" . $data['publish_down'] . "'")
				->set($db->quoteName('ordering') . " = '" . (int) $data['ordering'] . "'")
				->set($db->quoteName('metakey') . " = '" . $db->escape($data['metakey']) . "'")
				->set($db->quoteName('metadesc') . " = '" . $db->escape($data['metadesc']) . "'")
				->set($db->quoteName('access') . " = '" . (int) $data['access'] . "'")
				->set($db->quoteName('metadata') . " = '" . $metadata . "'")
				->set($db->quoteName('language') . " = '" . $db->escape($data['language']) . "'")
				->set($db->quoteName('state') . " = '" . $data['state'] . "'")
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
				$sessionData = $app->getUserState('com_kinoarhiv.albums.' . $user->id . '.edit_data');
				$sessionData['id'] = $insertID;
				$app->setUserState('com_kinoarhiv.albums.' . $user->id . '.edit_data', $sessionData);
			}
			else
			{
				// Alias was changed? Move all linked items into new filesystem location.
				if ($data['fs_alias'] != $data['fs_alias_orig'])
				{
					// TODO Required if upload will be implemented.
					//$this->moveMediaItems($data['id'], $data['fs_alias_orig'], $data['fs_alias']);
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
				$assetID = KAComponentHelperBackend::saveAccessRules(null, 'com_kinoarhiv.album.' . $insertID, $title, $data['rules']);
				$query = $db->getQuery(true)
					->update($db->quoteName('#__ka_music_albums'))
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
				KAComponentHelperBackend::saveAccessRules($data['id'], 'com_kinoarhiv.album.' . $data['id'], $title, $data['rules']);
			}
		}

		if (empty($data['id']))
		{
			$data['id'] = $insertID;
		}

		// Update genres.
		if (!empty($data['genres']) && ($data['genres_orig'] != $data['genres'][0]))
		{
			$this->saveGenres($data['id'], $data['genres'][0]);
		}

		KAComponentHelperBackend::updateGenresStat($data['genres_orig'], $data['genres'], '#__ka_music_rel_genres');
		KAComponentHelperBackend::updateTagMapping($data['id'], $data['tags'], 'com_kinoarhiv.album');

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
		//jimport('components.com_kinoarhiv.helpers.content', JPATH_ROOT);

		$db = $this->getDbo();
		$data['genres'] = array_filter($data['genres']);
		$introtext = array();

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
		/*if (!empty($id))
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
		}*/

		return implode('', $introtext);
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
	protected function getGenres($id)
	{
		$app = JFactory::getApplication();
		$db  = $this->getDbo();

		$query = $db->getQuery(true)
			->select($db->quoteName('g.id') . ',' . $db->quoteName('g.name', 'title'))
			->from($db->quoteName('#__ka_music_rel_genres', 'rel'))
			->leftJoin($db->quoteName('#__ka_genres', 'g') . ' ON ' . $db->quoteName('g.id') . ' = ' . $db->quoteName('rel.genre_id'))
			->where($db->quoteName('rel.item_id') . ' = ' . (int) $id)
			->where($db->quoteName('rel.type') . ' = 0')
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

		$db->lockTable('#__ka_music_rel_genres');
		$db->transactionStart();

		if (!empty($id))
		{
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__ka_music_rel_genres'))
				->where($db->quoteName('item_id') . ' = ' . (int) $id)
				->where($db->quoteName('type') . ' = 0');

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

			$query->insert($db->quoteName('#__ka_music_rel_genres'))
				->columns($db->quoteName(array('genre_id', 'item_id', 'type', 'ordering')))
				->values("'" . (int) $genreID . "', '" . (int) $id . "', '0', '" . $key . "'");
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

	public function publish($isUnpublish)
	{
		$app   = JFactory::getApplication();
		$db    = $this->getDbo();
		$ids   = $app->input->get('id', array(), 'array');
		$state = $isUnpublish ? 0 : 1;
		$query = $db->getQuery(true);

		$query->update($db->quoteName('#__ka_music_albums'))
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
	 * Method to get a single record for track edit.
	 *
	 * @return  mixed  Object on success, false on failure.
	 *
	 * @since  3.1
	 */
	private function editTracks()
	{
		$app   = JFactory::getApplication();
		$db    = $this->getDbo();
		$id    = $app->input->get('row_id', 0, 'int');
		$query = $db->getQuery(true);

		$query->select(
			$db->quoteName(
				array(
					'id', 'album_id', 'artist_id', 'title', 'genre_rel_id', 'xgenre_id', 'year', 'composer',
					'publisher', 'performer', 'label', 'isrc', 'length', 'cd_number', 'track_number', 'filename',
					'buy_url', 'access', 'state'
				)
			)
		)
			->from($db->quoteName('#__ka_music'))
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

	// TODO Refactor
	public function getComposers()
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

		$query = $db->getQuery(true)
			->select($db->quoteName(array('id', 'title')))
			->from($db->quoteName('#__ka_names_career'))
			->order($db->quoteName('ordering') . ' ASC');

		$db->setQuery($query);
		$_careers = $db->loadObjectList();
		$careers = array();

		foreach ($_careers as $career)
		{
			$careers[$career->id] = $career->title;
		}

		$query = $db->getQuery(true);

		$query->select($db->quoteName('n.id', 'name_id') . ',' . $db->quoteName('n.name') . ',' . $db->quoteName('n.latin_name'))
			->select($db->quoteName(array('t.type', 't.role', 't.ordering')))
			->from($db->quoteName('#__ka_names', 'n'))
			->join('LEFT', $db->quoteName('#__ka_music_rel_composers', 't') . ' ON ' . $db->quoteName('t.name_id') . ' = ' . $db->quoteName('n.id') . ' AND ' . $db->quoteName('t.album_id') . ' = ' . (int) $id);

		$where_subquery = $db->getQuery(true)
			->select($db->quoteName('name_id'))
			->from($db->quoteName('#__ka_music_rel_composers'))
			->where($db->quoteName('album_id') . ' = ' . (int) $id);

		$where = "";

		if (!empty($search_string))
		{
			if ($search_field == 'n.name')
			{
				$where .= " AND (" . KADatabaseHelper::transformOperands($db->quoteName($search_field), $search_operand, $db->escape($search_string)) . " OR " . KADatabaseHelper::transformOperands($db->quoteName('n.latin_name'), $search_operand, $db->escape($search_string)) . ")";
			}
			else
			{
				$where .= " AND " . KADatabaseHelper::transformOperands($db->quoteName($search_field), $search_operand, $db->escape($search_string));
			}
		}

		$query->where($db->quoteName('n.id') . ' IN (' . $where_subquery . ')' . $where);
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

			foreach (explode(',', $value->type) as $k => $type)
			{
				$_result[$type][$i] = array(
					'name'     => $name,
					'name_id'  => $value->name_id,
					'role'     => $value->role,
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

	// TODO Refactor
	public function deleteComposers()
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
		$db->lockTable('#__ka_music_rel_composers');
		$db->transactionStart();

		foreach ($data as $key => $value)
		{
			$ids = explode('_', $value['name']);
			$query = $db->getQuery(true);

			$query->delete($db->quoteName('#__ka_music_rel_composers'))
				->where($db->quoteName('name_id') . ' = ' . (int) $ids[3] . ' AND ' . $db->quoteName('album_id') . ' = ' . (int) $ids[4] . ' AND FIND_IN_SET("' . (int) $ids[5] . '", ' . $db->quoteName('type') . ')');
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
