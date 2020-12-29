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
 * Class KinoarhivModelName
 *
 * @since  3.0
 */
class KinoarhivModelName extends JModelForm
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
		$input = JFactory::getApplication()->input->getCmd('task', '');

		if ($input == 'editNameAwards' || $input == 'saveNameAwards')
		{
			$form = $this->loadForm('com_kinoarhiv.name', 'relations_awards', array('control' => 'jform', 'load_data' => $loadData));
		}
		else
		{
			$form = $this->loadForm('com_kinoarhiv.name', 'name', array('control' => 'jform', 'load_data' => $loadData));
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
		$data = $app->getUserState('com_kinoarhiv.names.' . JFactory::getUser()->id . '.edit_data', array());

		if (empty($data))
		{
			$data = $this->getItem();

			if (empty($data) && $app->input->getCmd('task', '') == 'add')
			{
				$filters = (array) $app->getUserState('com_kinoarhiv.names.filter');
				$data = (object) array(
					'state'    => ((isset($filters['published']) && $filters['published'] !== '') ? $filters['published'] : null),
					'language' => $app->input->getString('language', (!empty($filters['language']) ? $filters['language'] : null)),
					'access'   => $app->input->getInt('access', (!empty($filters['access']) ? $filters['access'] : JFactory::getConfig()->get('access')))
				);
			}
		}

		$this->preprocessData('com_kinoarhiv.name', $data);

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @return  mixed  Object on success, false on failure.
	 *
	 * @since  3.0
	 */
	public function getItem()
	{
		$app  = JFactory::getApplication();
		$db   = $this->getDbo();
		$task = $app->input->get('task', '', 'cmd');
		$id   = $app->input->get('id', 0, 'int');

		if ($task == 'editNameAwards')
		{
			return $this->editNameAwards();
		}

		$query = $db->getQuery(true)->select(
			$db->quoteName(
				array('n.id', 'n.asset_id', 'n.name', 'n.latin_name', 'n.alias', 'n.fs_alias', 'n.date_of_birth',
					'n.date_of_death', 'n.birthplace', 'n.birthcountry', 'n.gender', 'n.height', 'n.desc', 'n.attribs',
					'n.ordering', 'n.state', 'n.access', 'n.metakey', 'n.metadesc', 'n.metadata', 'n.language'
				)
			)
		)
			->select($db->quoteName('n.fs_alias', 'fs_alias_orig'))
			->from($db->quoteName('#__ka_names', 'n'))
			->where($db->quoteName('n.id') . ' = ' . (int) $id);

		// Join over the language
		$query->select($db->quoteName('l.title', 'language_title'))
			->join('LEFT', $db->quoteName('#__languages', 'l') . ' ON ' . $db->quoteName('l.lang_code') . ' = ' . $db->quoteName('n.language'));

		// Join over the gallery item
		$query->select($db->quoteName('g.id', 'image_id') . ',' . $db->quoteName('g.filename'))
			->join('LEFT', $db->quoteName('#__ka_names_gallery', 'g') . ' ON ' . $db->quoteName('g.name_id') . ' = ' . $db->quoteName('n.id')
				. ' AND ' . $db->quoteName('g.type') . ' = 3'
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

		// Fix for permissions script. See https://github.com/joomla/joomla-cms/issues/15203
		$result->title = KAContentHelper::formatItemTitle($result->name, $result->latin_name);

		$genres = $this->getGenres($id);

		if ($genres)
		{
			$genres = implode(',', $genres['id']);
			$result->genres = $genres;
			$result->genres_orig = $genres;
		}

		$careers = $this->getCareers($id);

		if ($careers)
		{
			$careers = implode(',', $careers['id']);
			$result->careers = $careers;
			$result->careers_orig = $careers;
		}

		$registry = new Registry($result->attribs);
		$result->attribs = $registry->toArray();

		if (!empty($result->metadata))
		{
			$metadata = json_decode($result->metadata, true);
			$result = (object) array_merge((array) $result, $metadata);
		}

		return $result;
	}

	/**
	 * Get list of genres for field.
	 *
	 * @param   integer  $id  Item ID.
	 *
	 * @return  mixed  Array with data, false otherwise.
	 *
	 * @since   3.0
	 */
	private function getGenres($id)
	{
		$app = JFactory::getApplication();
		$db  = $this->getDbo();

		$query = $db->getQuery(true)
			->select($db->quoteName('g.id') . ',' . $db->quoteName('g.name', 'title'))
			->from($db->quoteName('#__ka_rel_names_genres', 'rel'))
			->leftJoin($db->quoteName('#__ka_genres', 'g') . ' ON ' . $db->quoteName('g.id') . ' = ' . $db->quoteName('rel.genre_id'))
			->where($db->quoteName('rel.name_id') . ' = ' . (int) $id)
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
	 * Get list of careers for field.
	 *
	 * @param   integer  $id  Item ID.
	 *
	 * @return  mixed  Array with data, false otherwise.
	 *
	 * @since   3.0
	 */
	private function getCareers($id)
	{
		$app = JFactory::getApplication();
		$db  = $this->getDbo();

		$query = $db->getQuery(true)
			->select($db->quoteName(array('c.id', 'c.title')))
			->from($db->quoteName('#__ka_rel_names_career', 'rel'))
			->leftJoin($db->quoteName('#__ka_names_career', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('rel.career_id'))
			->where($db->quoteName('rel.name_id') . ' = ' . (int) $id)
			->order($db->quoteName('rel.ordering') . ' ASC');
		$db->setQuery($query);

		try
		{
			$_careers = $db->loadAssocList();
			$careers = array();

			foreach ($_careers as $key => $id)
			{
				$careers['id'][$key] = $id['id'];
				$careers['title'][$key] = $id['title'];
			}
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		return $careers;
	}

	/**
	 * Method to get a single record for award edit.
	 *
	 * @return  mixed  Object on success, false on failure.
	 *
	 * @since  3.1
	 */
	private function editNameAwards()
	{
		$app   = JFactory::getApplication();
		$db    = $this->getDbo();
		$id    = $app->input->get('row_id', 0, 'int');
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('id', 'item_id', 'award_id', 'desc', 'year', 'type')))
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
	public function saveNameAwards($data)
	{
		$app  = JFactory::getApplication();
		$db   = $this->getDbo();
		$user = JFactory::getUser();
		$id   = $app->input->get('item_id', 0, 'int');

		if (empty($data['id']))
		{
			$query = $db->getQuery(true);

			$query->insert($db->quoteName('#__ka_rel_awards'))
				->columns($db->quoteName(array('id', 'item_id', 'award_id', 'desc', 'year', 'type')))
				->values("'', '" . (int) $id . "', '" . (int) $data['award_id'] . "', "
					. "'" . $db->escape($data['desc']) . "', '" . (int) $data['year'] . "', '1'"
				);
		}
		else
		{
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__ka_rel_awards'))
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
				$sessionData = $app->getUserState('com_kinoarhiv.name.' . $user->id . '.edit_data.aw_id');
				$sessionData['id'] = $db->insertid();
				$app->setUserState('com_kinoarhiv.name.' . $user->id . '.edit_data.aw_id', $sessionData);
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
	public function removeNameAwards($ids)
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
		$app   = JFactory::getApplication();
		$db    = $this->getDbo();
		$ids   = $app->input->get('id', array(), 'array');
		$state = $isUnpublish ? 0 : 1;
		$query = $db->getQuery(true);

		$query->update($db->quoteName('#__ka_names'))
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
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  mixed  True on success, False on error, lastInsertID on save.
	 *
	 * @since   3.0
	 */
	public function save($data)
	{
		jimport('components.com_kinoarhiv.helpers.content', JPATH_ROOT);

		$app  = JFactory::getApplication();
		$db   = $this->getDbo();
		$user = JFactory::getUser();

		// Automatic handling of alias for empty fields
		if (in_array($app->input->get('task'), array('apply', 'save', 'save2new'))
			&& (!isset($data['id']) || (int) $data['id'] == 0 || $data['alias'] == ''))
		{
			$name = empty($data['latin_name']) ? $data['name'] : $data['latin_name'];

			if (JFactory::getConfig()->get('unicodeslugs') == 1)
			{
				$data['alias'] = JFilterOutput::stringUrlUnicodeSlug($name);
			}
			else
			{
				$data['alias'] = JFilterOutput::stringURLSafe($name);
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

		$name = $db->escape(trim($data['name']));
		$latinName = $db->escape(trim($data['latin_name']));

		if (empty($data['id']))
		{
			// Check if person with this name or latin name allready exists
			$query = $db->getQuery(true);

			$query->select('COUNT(id)')
				->from($db->quoteName('#__ka_names'))
				->where($db->quoteName('name') . " = '" . $name . "'");

			$db->setQuery($query);
			$count = $db->loadResult();

			if ($count > 0)
			{
				$app->enqueueMessage(JText::_('COM_KA_NAMES_EXISTS'), 'error');

				return false;
			}

			$query = $db->getQuery(true);

			$query->insert($db->quoteName('#__ka_names'))
				->columns(
					$db->quoteName(
						array('id', 'asset_id', 'name', 'latin_name', 'alias', 'fs_alias', 'date_of_birth',
							'date_of_death', 'birthplace', 'birthcountry', 'gender', 'height', 'desc', 'attribs',
							'ordering', 'state', 'access', 'metakey', 'metadesc', 'metadata', 'language'
						)
					)
				)
				->values("'', '0', '" . $name . "', '" . $latinName . "', '" . $data['alias'] . "',"
					. "'" . $data['fs_alias'] . "', '" . $data['date_of_birth'] . "', '" . $data['date_of_death'] . "',"
					. "'" . $db->escape(trim($data['birthplace'])) . "', '" . (int) $data['birthcountry'] . "',"
					. "'" . (int) $data['gender'] . "', '" . $db->escape($data['height']) . "',"
					. "'" . $db->escape($data['desc']) . "', '" . $attribs . "', '" . (int) $data['ordering'] . "',"
					. "'" . $data['state'] . "', '" . (int) $data['access'] . "', '" . $db->escape($data['metakey']) . "',"
					. "'" . $db->escape($data['metadesc']) . "', '" . $metadata . "',"
					. "'" . $db->escape($data['language']) . "'"
				);
		}
		else
		{
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__ka_names'))
				->set($db->quoteName('name') . " = '" . $name . "'")
				->set($db->quoteName('latin_name') . " = '" . $latinName . "'")
				->set($db->quoteName('alias') . " = '" . $data['alias'] . "'")
				->set($db->quoteName('fs_alias') . " = '" . $data['fs_alias'] . "'")
				->set($db->quoteName('date_of_birth') . " = '" . $data['date_of_birth'] . "'")
				->set($db->quoteName('date_of_death') . " = '" . $data['date_of_death'] . "'")
				->set($db->quoteName('birthplace') . " = '" . $db->escape($data['birthplace']) . "'")
				->set($db->quoteName('birthcountry') . " = '" . (int) $data['birthcountry'] . "'")
				->set($db->quoteName('gender') . " = '" . (int) $data['gender'] . "'")
				->set($db->quoteName('height') . " = '" . $db->escape($data['height']) . "'")
				->set($db->quoteName('desc') . " = '" . $db->escape($data['desc']) . "'")
				->set($db->quoteName('attribs') . " = '" . $attribs . "'")
				->set($db->quoteName('ordering') . " = '" . (int) $data['ordering'] . "'")
				->set($db->quoteName('state') . " = '" . $data['state'] . "'")
				->set($db->quoteName('access') . " = '" . (int) $data['access'] . "'")
				->set($db->quoteName('metakey') . " = '" . $db->escape($data['metakey']) . "'")
				->set($db->quoteName('metadesc') . " = '" . $db->escape($data['metadesc']) . "'")
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
				$insertid = $db->insertid();
				$sessionData = $app->getUserState('com_kinoarhiv.names.' . $user->id . '.edit_data');
				$sessionData['id'] = $insertid;
				$app->setUserState('com_kinoarhiv.names.' . $user->id . '.edit_data', $sessionData);
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
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		// Update the rules.
		if ($user->authorise('core.edit.access', 'com_kinoarhiv') && isset($data['rules']))
		{
			$title = $db->escape(KAContentHelper::formatItemTitle($name, $latinName));

			if (empty($data['id']))
			{
				$assetID = KAComponentHelperBackend::saveAccessRules(null, 'com_kinoarhiv.name.' . $insertid, $title, $data['rules']);
				$query = $db->getQuery(true)
					->update($db->quoteName('#__ka_names'))
					->set($db->quoteName('asset_id') . ' = ' . (int) $assetID);

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
			}
			else
			{
				KAComponentHelperBackend::saveAccessRules($data['id'], 'com_kinoarhiv.name.' . $data['id'], $title, $data['rules']);
			}
		}

		// Update genres.
		if (!empty($data['genres']) && ($data['genres_orig'] != $data['genres'][0]))
		{
			$this->saveGenres($data['id'], $data['genres'][0]);
		}

		// Update careers.
		if (!empty($data['careers']) && ($data['careers_orig'] != $data['careers'][0]))
		{
			$this->saveCareers($data['id'], $data['careers'][0]);
		}

		return true;
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
		$pathPoster      = $params->get('media_actor_posters_root');
		$pathWallpp      = $params->get('media_actor_wallpapers_root');
		$pathPhoto       = $params->get('media_actor_photo_root');
		$oldFolderPoster = JPath::clean($pathPoster . '/' . $oldAlias . '/' . $id . '/posters');
		$oldFolderWallpp = JPath::clean($pathWallpp . '/' . $oldAlias . '/' . $id . '/wallpapers');
		$oldFolderPhoto  = JPath::clean($pathPhoto . '/' . $oldAlias . '/' . $id . '/photo');
		$newFolderPoster = JPath::clean($pathPoster . '/' . $newAlias . '/' . $id . '/posters');
		$newFolderWallpp = JPath::clean($pathWallpp . '/' . $newAlias . '/' . $id . '/wallpapers');
		$newFolderPhoto  = JPath::clean($pathPhoto . '/' . $newAlias . '/' . $id . '/photo');

		if (!$filesystem->move(
			array($oldFolderPoster, $oldFolderWallpp, $oldFolderPhoto),
			array($newFolderPoster, $newFolderWallpp, $newFolderPhoto)
		))
		{
			$app->enqueueMessage('Error while moving the files from media folders into new location! See log for more information.', 'error');
		}

		// Remove parent folder for posters/wallpapers/photo. Delete only if folder(s) is empty.
		$_pathPoster = JPath::clean($pathPoster . '/' . $oldAlias . '/' . $id);
		$_pathWallpp = JPath::clean($pathWallpp . '/' . $oldAlias . '/' . $id);
		$_pathPhoto  = JPath::clean($pathPhoto . '/' . $oldAlias . '/' . $id);

		if (file_exists($_pathPoster) && $filesystem->getFolderSize($_pathPoster) === 0)
		{
			JFolder::delete($_pathPoster);
		}

		if (file_exists($_pathWallpp) && $filesystem->getFolderSize($_pathWallpp) === 0)
		{
			JFolder::delete($_pathWallpp);
		}

		if (file_exists($_pathPhoto) && $filesystem->getFolderSize($_pathPhoto) === 0)
		{
			JFolder::delete($_pathPhoto);
		}

		return true;
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
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$genres = explode(',', $genres);
		$queryResult = true;

		$db->setDebug(true);
		$db->lockTable('#__ka_rel_names_genres');
		$db->transactionStart();

		if (!empty($id))
		{
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__ka_rel_names_genres'))
				->where($db->quoteName('name_id') . ' = ' . (int) $id);

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

			$query->insert($db->quoteName('#__ka_rel_names_genres'))
				->columns($db->quoteName(array('genre_id', 'name_id', 'ordering')))
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
		$db->setDebug(false);

		return (bool) $queryResult;
	}

	/**
	 * Save careers to relation table.
	 *
	 * @param   integer  $id       Item ID.
	 * @param   string   $careers  Comma separated string with career ID.
	 *
	 * @return  boolean
	 *
	 * @since   3.1
	 */
	protected function saveCareers($id, $careers)
	{
		$app         = JFactory::getApplication();
		$db          = $this->getDbo();
		$careers     = explode(',', $careers);
		$queryResult = true;

		$db->setDebug(true);
		$db->lockTable('#__ka_rel_names_career');
		$db->transactionStart();

		if (!empty($id))
		{
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__ka_rel_names_career'))
				->where($db->quoteName('name_id') . ' = ' . (int) $id);

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

		foreach ($careers as $key => $careerID)
		{
			$query = $db->getQuery(true);

			$query->insert($db->quoteName('#__ka_rel_names_career'))
				->columns($db->quoteName(array('career_id', 'name_id', 'ordering')))
				->values("'" . (int) $careerID . "', '" . (int) $id . "', '" . $key . "'");
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
			$app->enqueueMessage('Failed to update careers!', 'error');
		}
		else
		{
			$db->transactionCommit();
		}

		$db->unlockTables();
		$db->setDebug(false);

		return (bool) $queryResult;
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
			->where($db->quoteName('item_id') . ' IN (' . implode(',', $ids) . ') AND ' . $db->quoteName('type') . ' = 1');

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
			->delete($db->quoteName('#__ka_rel_names_genres'))
			->where($db->quoteName('name_id') . ' IN (' . implode(',', $ids) . ')');

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
		}

		// Remove associated careers
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__ka_rel_names_career'))
			->where($db->quoteName('name_id') . ' IN (' . implode(',', $ids) . ')');

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
		}

		// Remove favorited persons
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__ka_user_marked_names'))
			->where($db->quoteName('name_id') . ' IN (' . implode(',', $ids) . ')');

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
		}

		// Remove access rules
		$queryResult = true;
		$db->setDebug(true);
		$db->lockTable('#__assets');
		$db->transactionStart();

		foreach ($ids as $id)
		{
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__assets'))
				->where($db->quoteName('name') . " = 'com_kinoarhiv.name." . (int) $id . "' AND " . $db->quoteName('level') . " = 2");

			$db->setQuery($query . ';');

			try
			{
				$db->execute();
			}
			catch (RuntimeException $e)
			{
				$queryResult = false;
				$app->enqueueMessage($e->getMessage(), 'error');

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
		$db->setDebug(false);

		// Remove person from DB
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__ka_names'))
			->where($db->quoteName('id') . ' IN (' . implode(',', $ids) . ')');

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
		}

		// Remove gallery items
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__ka_names_gallery'))
			->where($db->quoteName('name_id') . ' IN (' . implode(',', $ids) . ')');

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
