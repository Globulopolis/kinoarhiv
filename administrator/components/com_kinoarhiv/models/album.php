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
JLoader::register('KAContentHelper', JPath::clean(JPATH_ROOT . '/components/com_kinoarhiv/helpers/content.php'));

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
			case 'editAlbumAward':
			case 'saveAlbumAward':
				$form = $this->loadForm($formName, 'relations_award', $formOpts);
				break;
			case 'editAlbumCrew':
			case 'saveAlbumCrew':
				$form = $this->loadForm($formName, 'relations_album_crew', $formOpts);
				break;
			case 'editAlbumRelease':
			case 'saveAlbumRelease':
				$form = $this->loadForm($formName, 'relations_release', $formOpts);
				break;
			case 'editTrack':
			case 'saveTrack':
			case 'saveTagsToFile':
				$form = $this->loadForm($formName, 'relations_track', $formOpts);
				break;
			case 'showScanFolderTemplate':
				$form = $this->loadForm($formName, 'scan_folder', $formOpts);
				break;
			default:
				$form = $this->loadForm($formName, 'album', $formOpts);
				break;
		}

		if (empty($form))
		{
			return false;
		}

		if ($task == 'editAlbumAward')
		{
			$form->setFieldAttribute('item_id', 'label', 'COM_KA_FIELD_ALBUMS_FIELD_ID_LABEL');
			$form->setValue('type', null, 2);
		}
		elseif ($task == 'editAlbumRelease')
		{
			$form->setFieldAttribute('item_id', 'label', 'COM_KA_FIELD_ALBUMS_FIELD_ID_LABEL');
			$form->setValue('item_type', null, 1);
		}
		elseif ($task == 'editAlbumCrew')
		{
			$form->setValue('item_type', null, $input->getInt('item_type', 0));
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

		$this->preprocessData('com_kinoarhiv.album', $data);

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   string   $task  Task.
	 * @param   integer  $id    Item ID.
	 *
	 * @return  mixed  Object on success, false on failure.
	 *
	 * @since   3.0
	 */
	public function getItem($task = '', $id = 0)
	{
		$app  = JFactory::getApplication();
		$db   = $this->getDbo();
		$task = $task == '' ? $app->input->get('task', '') : $task;
		$id   = !empty($id) ? $id : $app->input->get('id', 0, 'int');

		if ($task == 'editAlbumAward')
		{
			return $this->editAlbumAward();
		}
		elseif ($task == 'editAlbumCrew')
		{
			return $this->editAlbumCrew();
		}
		elseif ($task == 'editAlbumRelease')
		{
			return $this->editAlbumRelease();
		}
		elseif ($task == 'editTrack')
		{
			return $this->editTrack();
		}

		$query = $db->getQuery(true)
			->select(
				$db->quoteName(
					array(
						'a.id', 'a.asset_id', 'a.title', 'a.alias', 'a.fs_alias', 'a.year', 'a.length', 'a.desc',
						'a.rate', 'a.rate_sum', 'a.covers_path', 'a.covers_path_www', 'a.tracks_path',
						'a.tracks_path_www', 'a.tracks_preview_path', 'a.buy_urls', 'a.attribs', 'a.created',
						'a.created_by', 'a.modified', 'a.modified_by', 'a.publish_up', 'a.publish_down', 'a.ordering',
						'a.metakey', 'a.metadesc', 'a.access', 'a.metadata', 'a.language', 'a.state'
					)
				)
			)
			->select($db->quoteName('a.fs_alias', 'fs_alias_orig'))
			->from($db->quoteName('#__ka_music_albums', 'a'))
			->where($db->quoteName('a.id') . ' = ' . (int) $id);

		// Join over the language
		$query->select($db->quoteName('l.title', 'language_title'))
			->leftJoin($db->quoteName('#__languages', 'l') . ' ON ' . $db->quoteName('l.lang_code') . ' = ' . $db->quoteName('a.language'));

		// Join over the gallery item
		$query->select($db->quoteName('g.id', 'image_id') . ',' . $db->quoteName('g.filename') . ',dimension')
			->leftJoin($db->quoteName('#__ka_music_albums_gallery', 'g') . ' ON ' . $db->quoteName('g.item_id') . ' = ' . $db->quoteName('a.id')
				. ' AND ' . $db->quoteName('g.frontpage') . ' = 1'
			);

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

		$vendors = $this->getVendors($id);

		if ($vendors)
		{
			$vendors = implode(',', $vendors['id']);
			$result->vendors = $vendors;
			$result->vendors_orig = $vendors;
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
			? round($data['rate_sum'] / $data['rate']) : 0;
		$introtext            = $this->createIntroText($data, $params);
		$coversPath           = empty($data['covers_path']) ? '' : JPath::clean($data['covers_path']);
		$coversPathWWW        = empty($data['covers_path_www']) ? '' : JPath::clean($data['covers_path_www'], '/');
		$tracksPath           = empty($data['tracks_path']) ? '' : JPath::clean($data['tracks_path']);
		$tracksPathWWW        = empty($data['tracks_path_www']) ? '' : JPath::clean($data['tracks_path_www'], '/');
		$tracksPreviewPath    = empty($data['tracks_preview_path']) ? '' : JPath::clean($data['tracks_preview_path']);
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
				'desc'                => $db->escape($data['desc']),
				'rate'                => (int) $data['rate_loc'],
				'rate_sum'            => (int) $data['rate_sum_loc'],
				'rate_loc_rounded'    => $rateLocalRounded,
				'covers_path'         => $db->escape($coversPath),
				'covers_path_www'     => $db->escape($coversPathWWW),
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
				->set($db->quoteName('desc') . " = '" . $db->escape($data['desc']) . "'")
				->set($db->quoteName('rate') . " = '" . (int) $data['rate'] . "'")
				->set($db->quoteName('rate_sum') . " = '" . (int) $data['rate_sum'] . "'")
				->set($db->quoteName('rate_rounded') . " = '" . $rateLocalRounded . "'")
				->set($db->quoteName('covers_path') . " = '" . $db->escape($coversPath) . "'")
				->set($db->quoteName('covers_path_www') . " = '" . $db->escape($coversPathWWW) . "'")
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
				if (!empty($coversPath) && $data['fs_alias'] != $data['fs_alias_orig'])
				{
					// NOTE! This is not needed. User must change path to folder and rename folder manually.
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
				$assetID = KAContentHelperBackend::saveAccessRules(null, 'com_kinoarhiv.album.' . $insertID, $title, $data['rules']);
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
				KAContentHelperBackend::saveAccessRules($data['id'], 'com_kinoarhiv.album.' . $data['id'], $title, $data['rules']);
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

		// Update labels(vendors).
		if (!empty($data['vendors']) && ($data['vendors_orig'] != $data['vendors'][0]))
		{
			$this->saveGenres($data['id'], $data['vendors'][0]);
		}

		KAContentHelperBackend::updateGenresStat($data['genres_orig'], $data['genres'], '#__ka_music_rel_genres');
		KAContentHelperBackend::updateTagMapping($data['id'], $data['tags'], 'com_kinoarhiv.album');

		// Clear the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Create intro text.
	 *
	 * @param   array    $data    Movie info
	 * @param   object   $params  Component parameters
	 *
	 * @return  string
	 *
	 * @since   3.1
	 */
	private function createIntroText($data, $params)
	{
		jimport('components.com_kinoarhiv.helpers.content', JPATH_ROOT);

		$app            = JFactory::getApplication();
		$db             = $this->getDbo();
		$data['genres'] = array_filter($data['genres']);
		$introtext      = array();

		// Process crew
		if (!empty($data['id']))
		{
			$query = $db->getQuery(true)
				->select($db->quoteName(array('rel.name_id', 'rel.career_id', 'n.name', 'n.latin_name', 'c.title', 'c.is_mainpage')))
				->from($db->quoteName('#__ka_music_rel_names', 'rel'))
				->leftJoin($db->quoteName('#__ka_names', 'n') . ' ON ' . $db->quoteName('n.id') . ' = ' . $db->quoteName('rel.name_id'))
				->leftJoin($db->quoteName('#__ka_names_career', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('rel.career_id'))
				->where('rel.item_id = ' . (int) $data['id'] . ' AND rel.item_type = 0')
				->order($db->quoteName('rel.ordering') . ' ASC');

			if ($params->get('introtext_actors_list_limit') > 0)
			{
				$query->setLimit($params->get('introtext_actors_list_limit'));
			}

			$db->setQuery($query);

			try
			{
				$crew = $db->loadObjectList();

				if (count($crew) > 0)
				{
					$crewArr = array();
					$crewStr  = '';

					// Presorting
					foreach ($crew as $item)
					{
						if ($item->is_mainpage == 1)
						{
							$crewArr[$item->career_id][] = $item;
						}
					}

					foreach ($crewArr as $row)
					{
						$crewStr .= '[names ln=' . $row[0]->title . ']';
						$rows = count($row);

						foreach ($row as $key => $_item)
						{
							$crewStr .= '[name=' . $_item->name_id . ']'
								. KAContentHelper::formatItemTitle($_item->name, $_item->latin_name) . '[/name]';
							$crewStr .= $rows > ($key + 1) ? ', ' : '';
						}

						$crewStr .= '[/names]';
					}

					$introtext[] = '<div class="cr-list">' . $crewStr . '</div>';
				}
			}
			catch (RuntimeException $e)
			{
				$app->enqueueMessage($e->getMessage(), 'error');
			}
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

			try
			{
				$genres = $db->loadObjectList();

				$languageConst = count($genres) > 1 ? 'COM_KA_GENRES' : 'COM_KA_GENRE';
				$genresStr = '';

				foreach ($genres as $genre)
				{
					$genresStr .= StringHelper::strtolower($genre->name) . ', ';
				}

				$introtext[] = '<div class="gn-list">[genres ln=' . $languageConst . ']: '
					. StringHelper::substr($genresStr, 0, -2) . '[/genres]</div>';
			}
			catch (RuntimeException $e)
			{
				$app->enqueueMessage($e->getMessage(), 'error');
			}
		}

		return implode('', $introtext);
	}

	/**
	 * Get list of genres for field.
	 *
	 * @param   integer  $id    Item ID.
	 * @param   integer  $type  Item type.
	 *
	 * @return  mixed    Array with data, false otherwise.
	 *
	 * @since   3.1
	 */
	protected function getGenres($id, $type = 0)
	{
		$app = JFactory::getApplication();
		$db  = $this->getDbo();

		$query = $db->getQuery(true)
			->select($db->quoteName('g.id') . ',' . $db->quoteName('g.name', 'title'))
			->from($db->quoteName('#__ka_music_rel_genres', 'rel'))
			->leftJoin($db->quoteName('#__ka_genres', 'g') . ' ON ' . $db->quoteName('g.id') . ' = ' . $db->quoteName('rel.genre_id'))
			->where($db->quoteName('rel.item_id') . ' = ' . (int) $id)
			->where($db->quoteName('rel.type') . ' = ' . (int) $type)
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
	 * Get list of artist for field.
	 *
	 * @param   integer  $id    Item ID.
	 * @param   integer  $type  Item type.
	 *
	 * @return  mixed    Array with data, false otherwise.
	 *
	 * @since   3.1
	 */
	protected function getNames($id, $type = 0)
	{
		$app = JFactory::getApplication();
		$db  = $this->getDbo();

		$query = $db->getQuery(true)
			->select($db->quoteName(array('n.id', 'n.name', 'n.latin_name')))
			->from($db->quoteName('#__ka_music_rel_names', 'rel'))
			->leftJoin($db->quoteName('#__ka_names', 'n') . ' ON ' . $db->quoteName('n.id') . ' = ' . $db->quoteName('rel.name_id'))
			->where($db->quoteName('rel.item_id') . ' = ' . (int) $id)
			->where($db->quoteName('rel.item_type') . ' = ' . (int) $type)
			->order($db->quoteName('rel.ordering') . ' ASC');

		$db->setQuery($query);

		try
		{
			$_names = $db->loadAssocList();
			$names = array();

			foreach ($_names as $key => $id)
			{
				$names['id'][$key]          = $id['id'];
				$names['title'][$key]       = KAContentHelper::formatItemTitle($id['name'], $id['latin_name']);
				$names['names'][$key]       = $id['name'];
				$names['latin_names'][$key] = $id['latin_name'];
			}
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		return $names;
	}

	/**
	 * Get list of labels(vendors) for field.
	 *
	 * @param   integer  $id    Item ID.
	 * @param   integer  $type  Item type.
	 *
	 * @return  mixed    Array with data, false otherwise.
	 *
	 * @since   3.1
	 */
	protected function getVendors($id, $type = 0)
	{
		$app = JFactory::getApplication();
		$db  = $this->getDbo();

		$query = $db->getQuery(true)
			->select($db->quoteName('v.id') . ',' . $db->quoteName('v.company_name', 'title'))
			->from($db->quoteName('#__ka_music_rel_vendors', 'rel'))
			->leftJoin($db->quoteName('#__ka_vendors', 'v') . ' ON ' . $db->quoteName('v.id') . ' = ' . $db->quoteName('rel.vendor_id'))
			->where($db->quoteName('rel.item_id') . ' = ' . (int) $id)
			->where($db->quoteName('rel.item_type') . ' = ' . (int) $type)
			->order($db->quoteName('rel.ordering') . ' ASC');

		$db->setQuery($query);

		try
		{
			$_vendors = $db->loadAssocList();
			$vendors = array();

			foreach ($_vendors as $key => $id)
			{
				$vendors['id'][$key] = $id['id'];
				$vendors['title'][$key] = $id['title'];
			}
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		return $vendors;
	}

	/**
	 * Save genres to relation table.
	 *
	 * @param   integer  $id      Item ID.
	 * @param   mixed    $genres  Comma separated string with genres ID or array with IDs.
	 * @param   integer  $type    Item type.
	 *
	 * @return  boolean
	 *
	 * @since   3.1
	 */
	protected function saveGenres($id, $genres, $type = 0)
	{
		$app = JFactory::getApplication();
		$db  = $this->getDbo();

		if (!is_array($genres))
		{
			$genres = explode(',', $genres);
		}

		if (count($genres) == 1)
		{
			// Test if genres in format: array(1) { [0]=> string(5) "42,40" };
			$_genres = explode(',', $genres[0]);

			if (count($_genres) > 1)
			{
				$genres = $_genres;
			}
		}

		$genres      = array_filter($genres);
		$queryResult = true;

		$db->lockTable('#__ka_music_rel_genres');
		$db->transactionStart();

		if (!empty($id))
		{
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__ka_music_rel_genres'))
				->where($db->quoteName('item_id') . ' = ' . (int) $id)
				->where($db->quoteName('type') . ' = ' . (int) $type);

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
				->values("'" . (int) $genreID . "', '" . (int) $id . "', '" . (int) $type . "', '" . $key . "'");
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
	 * Save labels(vendors) to relation table.
	 *
	 * @param   integer  $id       Item ID.
	 * @param   string   $vendors  Comma separated string with ID.
	 *
	 * @return  boolean
	 *
	 * @since   3.1
	 */
	protected function saveVendors($id, $vendors)
	{
		$app         = JFactory::getApplication();
		$db          = $this->getDbo();
		$vendors     = explode(',', $vendors);
		$vendors     = array_filter($vendors);
		$queryResult = true;

		$db->lockTable('#__ka_music_rel_vendors');
		$db->transactionStart();

		if (!empty($id))
		{
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__ka_music_rel_vendors'))
				->where($db->quoteName('item_id') . ' = ' . (int) $id)
				->where($db->quoteName('item_type') . ' = 0');

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

		foreach ($vendors as $key => $vendorID)
		{
			$query = $db->getQuery(true);

			$query->insert($db->quoteName('#__ka_music_rel_vendors'))
				->columns($db->quoteName(array('item_type', 'item_id', 'vendor_id', 'ordering')))
				->values("'0', '" . (int) $id . "', '" . (int) $vendorID . "', '" . $key . "'");
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
			$app->enqueueMessage('Failed to update labels!', 'error');
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
	 * Method to get a single record for award edit.
	 *
	 * @return  mixed  Object on success, false on failure.
	 *
	 * @since  3.1
	 */
	private function editAlbumAward()
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
	public function saveAlbumAward($data)
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
					. "'" . $db->escape($data['desc']) . "', '" . (int) $data['year'] . "', '2'"
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
				$sessionData = $app->getUserState('com_kinoarhiv.album.' . $user->id . '.edit_data.aw_id');
				$sessionData['id'] = $db->insertid();
				$app->setUserState('com_kinoarhiv.album.' . $user->id . '.edit_data.aw_id', $sessionData);
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
	 * @since   3.1
	 */
	public function removeAlbumAwards($ids)
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
	 * Method to get a single record for album crew edit.
	 *
	 * @return  mixed  Object on success, false on failure.
	 *
	 * @since  3.1
	 */
	private function editAlbumCrew()
	{
		$app   = JFactory::getApplication();
		$db    = $this->getDbo();
		$id    = $app->input->get('row_id', 0, 'int');
		$query = $db->getQuery(true);

		$query->select(
			$db->quoteName(array('id', 'name_id', 'item_id', 'item_type', 'role', 'career_id', 'ordering', 'desc'))
		)
			->from($db->quoteName('#__ka_music_rel_names'))
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
	 * Removes album crew.
	 *
	 * @param   array    $ids   Form data.
	 * @param   integer  $type  Item type.
	 *
	 * @return  boolean
	 *
	 * @since   3.1
	 */
	public function removeAlbumCrew($ids, $type)
	{
		$app   = JFactory::getApplication();
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->delete($db->quoteName('#__ka_music_rel_names'))
			->where($db->quoteName('item_type') . ' = ' . (int) $type)
			->where($db->quoteName('id') . ' IN (' . implode(',', $ids) . ')');

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

		return true;
	}

	/**
	 * Save crew to relation table.
	 *
	 * @param   array  $data  Form data.
	 *
	 * @return  boolean
	 *
	 * @since   3.1
	 */
	public function saveAlbumCrew($data)
	{
		$app  = JFactory::getApplication();
		$user = JFactory::getUser();
		$db   = $this->getDbo();

		if (empty($data['id']))
		{
			$query = $db->getQuery(true)
				->select('COUNT(id)')
				->from($db->quoteName('#__ka_music_rel_names'))
				->where($db->quoteName('name_id') . ' = ' . (int) $data['name_id'])
				->where($db->quoteName('career_id') . ' = ' . (int) $data['career_id'])
				->where($db->quoteName('item_type') . ' = ' . (int) $data['item_type']);

			$db->setQuery($query);

			try
			{
				$total = $db->loadResult();

				if ($total > 0)
				{
					$app->enqueueMessage('Duplicate entry for this artist with this career and type!', 'error');

					return false;
				}
			}
			catch (RuntimeException $e)
			{
				$app->enqueueMessage($e->getMessage(), 'error');

				return false;
			}
		}

		if (empty($data['id']))
		{
			$values = array(
				'id'        => '',
				'name_id'   => (int) $data['name_id'],
				'item_id'   => $app->input->getInt('item_id', 0),
				'item_type' => (int) $data['item_type'],
				'role'      => $db->escape($data['role']),
				'career_id' => (int) $data['career_id'],
				'ordering'  => (int) $data['ordering'],
				'desc'      => $db->escape($data['desc'])
			);

			$query = $db->getQuery(true)
				->insert($db->quoteName('#__ka_music_rel_names'))
				->columns($db->quoteName(array_keys($values)))
				->values("'" . implode("','", array_values($values)) . "'");
		}
		else
		{
			$query = $db->getQuery(true)
				->update($db->quoteName('#__ka_music_rel_names'))
				->set($db->quoteName('name_id') . ' = ' . $db->quote((int) $data['name_id']))
				->set($db->quoteName('item_id') . ' = ' . $db->quote((int) $data['item_id']))
				->set($db->quoteName('item_type') . ' = ' . $db->quote((int) $data['item_type']))
				->set($db->quoteName('role') . ' = ' . $db->quote($data['role']))
				->set($db->quoteName('career_id') . ' = ' . $db->quote((int) $data['career_id']))
				->set($db->quoteName('ordering') . ' = ' . $db->quote((int) $data['ordering']))
				->set($db->quoteName('desc') . ' = ' . $db->quote($data['desc']))
				->where($db->quoteName('id') . ' = ' . (int) $data['id']);
		}

		$db->setQuery($query);

		try
		{
			$db->execute();

			if (empty($data['id']))
			{
				$insertID = $db->insertid();
				$sessionData = $app->getUserState('com_kinoarhiv.album.' . $user->id . '.edit_data.i_id');
				$sessionData['id'] = $insertID;
				$app->setUserState('com_kinoarhiv.album.' . $user->id . '.edit_data.i_id', $sessionData);
			}
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		return true;
	}

	/**
	 * Method to get a single record for release edit.
	 *
	 * @return  mixed  Object on success, false on failure.
	 *
	 * @since  3.1
	 */
	private function editAlbumRelease()
	{
		$app   = JFactory::getApplication();
		$db    = $this->getDbo();
		$id    = $app->input->get('row_id', 0, 'int');
		$query = $db->getQuery(true);

		$query->select(
			$db->quoteName(
				array(
					'id', 'country_id', 'vendor_id', 'item_id', 'media_type', 'item_type', 'release_date', 'desc',
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
	 * Removes album releases.
	 *
	 * @param   array  $ids  Form data.
	 *
	 * @return  boolean
	 *
	 * @since   3.1
	 */
	public function removeAlbumReleases($ids)
	{
		$app   = JFactory::getApplication();
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->delete($db->quoteName('#__ka_releases'))
			->where($db->quoteName('id') . ' IN (' . implode(',', $ids) . ')');

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

		return true;
	}

	/**
	 * Save release to relation table.
	 *
	 * @param   array  $data  Form data.
	 *
	 * @return  boolean
	 *
	 * @since   3.1
	 */
	public function saveAlbumRelease($data)
	{
		$app  = JFactory::getApplication();
		$user = JFactory::getUser();
		$db   = $this->getDbo();

		// Skip dupe checks, let user do this.
		if (empty($data['id']))
		{
			$values = array(
				'id'           => '',
				'country_id'   => (int) $data['country_id'],
				'vendor_id'    => (int) $data['vendor_id'],
				'item_id'      => $app->input->getInt('item_id', 0),
				'media_type'   => (int) $data['media_type'],
				'item_type'    => (int) $data['item_type'],
				'release_date' => $db->escape($data['release_date']),
				'desc'         => $db->escape($data['desc']),
				'language'     => $db->escape($data['language']),
				'ordering'     => (int) $data['ordering']
			);

			$query = $db->getQuery(true)
				->insert($db->quoteName('#__ka_releases'))
				->columns($db->quoteName(array_keys($values)))
				->values("'" . implode("','", array_values($values)) . "'");
		}
		else
		{
			$query = $db->getQuery(true)
				->update($db->quoteName('#__ka_releases'))
				->set($db->quoteName('country_id') . ' = ' . $db->quote((int) $data['country_id']))
				->set($db->quoteName('vendor_id') . ' = ' . $db->quote((int) $data['vendor_id']))
				->set($db->quoteName('item_id') . ' = ' . $db->quote((int) $data['item_id']))
				->set($db->quoteName('media_type') . ' = ' . $db->quote((int) $data['media_type']))
				->set($db->quoteName('item_type') . ' = ' . $db->quote((int) $data['item_type']))
				->set($db->quoteName('release_date') . ' = ' . $db->quote($data['release_date']))
				->set($db->quoteName('desc') . ' = ' . $db->quote($data['desc']))
				->set($db->quoteName('language') . ' = ' . $db->quote($data['language']))
				->set($db->quoteName('ordering') . ' = ' . $db->quote((int) $data['ordering']))
				->where($db->quoteName('id') . ' = ' . (int) $data['id']);
		}

		$db->setQuery($query);

		try
		{
			$db->execute();

			if (empty($data['id']))
			{
				$insertID = $db->insertid();
				$sessionData = $app->getUserState('com_kinoarhiv.album.' . $user->id . '.edit_data.r_id');
				$sessionData['id'] = $insertID;
				$app->setUserState('com_kinoarhiv.album.' . $user->id . '.edit_data.r_id', $sessionData);
			}
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		return true;
	}

	/**
	 * Method to get a single record for track edit.
	 *
	 * @return  mixed  Object on success, false on failure.
	 *
	 * @since  3.1
	 */
	private function editTrack()
	{
		$app     = JFactory::getApplication();
		$db      = $this->getDbo();
		$albumID = $app->input->get('item_id', 0, 'int');
		$id      = $app->input->get('row_id', 0, 'int');
		$query   = $db->getQuery(true);

		$query->select(
			$db->quoteName(
				array(
					't.id', 't.title', 't.year', 't.publisher', 't.isrc', 't.length', 't.cd_number', 't.track_number',
					't.filename', 't.buy_url', 't.comments', 't.access', 't.state', 'a.tracks_path'
				)
			)
		)
			->from($db->quoteName('#__ka_music', 't'));

		$query->leftJoin($db->quoteName('#__ka_music_albums', 'a') . ' ON a.id = ' . (int) $albumID)
			->where($db->quoteName('t.id') . ' = ' . (int) $id);

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

		$genres = $this->getGenres($id, 1);

		if ($genres)
		{
			$genres = implode(',', $genres['id']);
			$result->genres = $genres;
			$result->genres_orig = $genres;
		}

		$vendors = $this->getVendors($id, 1);

		if ($vendors)
		{
			$vendors = implode(',', $vendors['id']);
			$result->label = $vendors;
		}

		return $result;
	}

	/**
	 * Removes album tracks. Doesn't delete files from filesystem.
	 *
	 * @param   array  $ids  Form data.
	 *
	 * @return  boolean
	 *
	 * @since   3.1
	 */
	public function removeTracks($ids)
	{
		$app   = JFactory::getApplication();
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->delete($db->quoteName('#__ka_music'))
			->where($db->quoteName('id') . ' IN (' . implode(',', $ids) . ')');

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

		return true;
	}

	/**
	 * Save album track to database.
	 *
	 * @param   array  $data  Form data.
	 *
	 * @return  boolean
	 *
	 * @since   3.1
	 */
	public function saveTrack($data)
	{
		$app     = JFactory::getApplication();
		$user    = JFactory::getUser();
		$db      = $this->getDbo();
		$albumID = $app->input->getInt('item_id', 0);

		if (empty($data['id']))
		{
			// Check if track with this title allready exists in database for this album.
			$rowsQuery = $db->getQuery(true)
				->select('COUNT(id)')
				->from($db->quoteName('#__ka_music'))
				->where($db->quoteName('title') . ' LIKE ' . $db->quote('%' . $data['title'] . '%'))
				->where($db->quoteName('id') . ' = ' . (int) $albumID);

			$db->setQuery($rowsQuery);
			$count = $db->loadResult();

			if ($count > 0)
			{
				$app->enqueueMessage(JText::_('COM_KA_MUSIC_TRACK_EXISTS'), 'error');

				return false;
			}

			$values = array(
				'id'           => '',
				'title'        => $db->escape($data['title']),
				'year'         => $db->escape($data['year']),
				'publisher'    => (int) $data['publisher'],
				'label'        => (int) $data['label'],
				'isrc'         => $db->escape($data['isrc']),
				'length'       => $db->escape($data['length']),
				'cd_number'    => $db->escape($data['cd_number']),
				'track_number' => $db->escape($data['track_number']),
				'filename'     => $db->escape($data['filename']),
				'buy_url'      => $db->escape($data['buy_url']),
				'comments'     => $db->escape($data['comments']),
				'access'       => (int) $data['access'],
				'state'        => (int) $data['state']
			);

			$query = $db->getQuery(true)
				->insert($db->quoteName('#__ka_music'))
				->columns($db->quoteName(array_keys($values)))
				->values("'" . implode("','", array_values($values)) . "'");
		}
		else
		{
			$query = $db->getQuery(true)
				->update($db->quoteName('#__ka_music'))
				->set($db->quoteName('title') . ' = ' . $db->quote($data['title']))
				->set($db->quoteName('year') . ' = ' . $db->quote($data['year']))
				->set($db->quoteName('publisher') . ' = ' . $db->quote((int) $data['publisher']))
				->set($db->quoteName('label') . ' = ' . $db->quote((int) $data['label']))
				->set($db->quoteName('isrc') . ' = ' . $db->quote($data['isrc']))
				->set($db->quoteName('length') . ' = ' . $db->quote($data['length']))
				->set($db->quoteName('cd_number') . ' = ' . $db->quote($data['cd_number']))
				->set($db->quoteName('track_number') . ' = ' . $db->quote($data['track_number']))
				->set($db->quoteName('filename') . ' = ' . $db->quote($data['filename']))
				->set($db->quoteName('buy_url') . ' = ' . $db->quote($data['buy_url']))
				->set($db->quoteName('comments') . ' = ' . $db->quote($data['comments']))
				->set($db->quoteName('access') . ' = ' . $db->quote((int) $data['access']))
				->set($db->quoteName('state') . ' = ' . $db->quote((int) $data['state']))
				->where($db->quoteName('id') . ' = ' . (int) $data['id']);
		}

		$db->setQuery($query);

		try
		{
			$db->execute();

			if (empty($data['id']))
			{
				$insertID = $db->insertid();
				$sessionData = $app->getUserState('com_kinoarhiv.album.' . $user->id . '.edit_data.tr_id');
				$sessionData['id'] = $insertID;
				$app->setUserState('com_kinoarhiv.album.' . $user->id . '.edit_data.tr_id', $sessionData);
			}

			// Update genres.
			if (!empty($data['genres']) && ($data['genres_orig'] !== implode(',', $data['genres'])))
			{
				$this->saveGenres($data['id'], $data['genres'], 1);
			}
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		return true;
	}

	/**
	 * Save tags to file.
	 *
	 * @param   array  $data  Form data.
	 *
	 * @return  boolean
	 *
	 * @since   3.1
	 */
	public function saveTrackTags($data)
	{
		$app = JFactory::getApplication();

		if (empty($data['id']))
		{
			$app->enqueueMessage('Empty ID!', 'error');

			return false;
		}

		jimport('components.com_kinoarhiv.libraries.vendor.getid3.getid3.getid3', JPATH_ROOT);

		$getID3 = new getID3;
		$getID3->setOption(array('encoding' => 'UTF-8'));

		jimport('components.com_kinoarhiv.libraries.vendor.getid3.getid3.write', JPATH_ROOT);

		try
		{
			$tagwriter                    = new getid3_writetags;
			$tagwriter->filename          = JPath::clean('D:/2/01. Graceful Exit.mp3'); // TODO For testing only
			$tagwriter->tagformats        = array('id3v1', 'id3v2.3');
			$tagwriter->overwrite_tags    = true;
			$tagwriter->remove_other_tags = false;
			$tagwriter->tag_encoding      = 'UTF-8';

			$genres    = $this->getGenres($data['id'], 1);
			$albumData = $this->getItem('', $data['id']);
			$artists   = $this->getNames($data['id'], 1);


			$tagData   = array(
				'title'        => array($data['title']),
				'artist'       => $artists['latin_names'],
				'album'        => array($albumData->title),
				'year'         => array($data['year']),
				'genre'        => $genres['title'],
				'comment'      => array($data['comments']),
				'track_number' => array($data['track_number'])
			);
			$tagwriter->tag_data = $tagData;

			if ($tagwriter->WriteTags())
			{
				$app->enqueueMessage(JText::_('COM_KA_MUSIC_TRACK_SAVED_TAGS'));

				if (!empty($tagwriter->warnings))
				{
					$app->enqueueMessage('There were some warnings:<br>' . implode('<br><br>', $tagwriter->warnings), 'warning');
				}
			}
			else
			{
				$app->enqueueMessage('Failed to write tags!<br>' . implode('<br><br>', $tagwriter->errors), 'error');

				return false;
			}
		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');

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
	 * @since   3.1
	 */
	public function remove($ids = array())
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();

		// Remove associated awards
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__ka_rel_awards'))
			->where($db->quoteName('item_id') . ' IN (' . implode(',', $ids) . ')')
			->where($db->quoteName('type') . ' = 2');

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
			->delete($db->quoteName('#__ka_music_rel_genres'))
			->where($db->quoteName('item_id') . ' IN (' . implode(',', $ids) . ')')
			// For 'type' value see column description in table.
			->where($db->quoteName('type') . ' = 0');

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
		}

		// Remove associated movies
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__ka_music_rel_movies'))
			->where($db->quoteName('album_id') . ' IN (' . implode(',', $ids) . ')');

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
			->delete($db->quoteName('#__ka_music_rel_names'))
			->where($db->quoteName('item_id') . ' IN (' . implode(',', $ids) . ')')
			->where($db->quoteName('item_type') . ' = 0');

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
			->where($db->quoteName('item_type') . ' = 1');

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
			->where($db->quoteName('item_type') . ' = 1');

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
		}

		// Remove associated movies
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__ka_music_rel_movies'))
			->where($db->quoteName('album_id') . ' IN (' . implode(',', $ids) . ')');

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
		}

		// Remove favorited albums
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__ka_user_marked_albums'))
			->where($db->quoteName('album_id') . ' IN (' . implode(',', $ids) . ')');

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
			->delete($db->quoteName('#__ka_user_votes_albums'))
			->where($db->quoteName('album_id') . ' IN (' . implode(',', $ids) . ')');

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
			->where($db->quoteName('type_alias') . ' = ' . $db->quote('com_kinoarhiv.album'))
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
				->where($db->quoteName('name') . " = 'com_kinoarhiv.album." . (int) $id . "' AND " . $db->quoteName('level') . " = 2");

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

		// Remove album(s) from DB
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__ka_music_albums'))
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
			->delete($db->quoteName('#__ka_music_albums_gallery'))
			->where($db->quoteName('item_id') . ' IN (' . implode(',', $ids) . ')');

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
