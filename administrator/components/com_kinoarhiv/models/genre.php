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

/**
 * Class KinoarhivModelGenre
 *
 * @since  3.0
 */
class KinoarhivModelGenre extends JModelForm
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
		$form = $this->loadForm('com_kinoarhiv.genre', 'genre', array('control' => 'form', 'load_data' => $loadData));

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
		$data = JFactory::getApplication()->getUserState('com_kinoarhiv.genres.' . JFactory::getUser()->id . '.edit_data', array());

		if (empty($data))
		{
			$data = $this->getItem();

			if (empty($data))
			{
				$filters = (array) $app->getUserState('com_kinoarhiv.names.filter');
				$data = (object) array(
					'state'    => ((isset($filters['published']) && $filters['published'] !== '') ? $filters['published'] : null),
					'language' => $app->input->getString('language', (!empty($filters['language']) ? $filters['language'] : null)),
					'access'   => $app->input->getInt('access', (!empty($filters['access']) ? $filters['access'] : JFactory::getConfig()->get('access')))
				);
			}
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
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$id = $app->input->get('id', null, 'array');

		if ($app->input->get('type', 'movie', 'word') == 'music')
		{
			$table = '#__ka_music_genres';
		}
		else
		{
			$table = '#__ka_genres';
		}

		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('id', 'name', 'alias', 'stats', 'state', 'access', 'language')))
			->from($db->quoteName($table))
			->where($db->quoteName('id') . ' = ' . (int) $id[0]);

		$db->setQuery($query);
		$result = $db->loadObject();

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
		$db = $this->getDbo();
		$ids = $app->input->get('id', array(), 'array');
		$state = $isUnpublish ? 0 : 1;

		if ($app->input->get('type', 'movie', 'word') == 'music')
		{
			$table = '#__ka_music_genres';
		}
		else
		{
			$table = '#__ka_genres';
		}

		$query = $db->getQuery(true);

		$query->update($db->quoteName($table))
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

	public function remove()
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$ids = $app->input->get('id', array(), 'array');

		if ($app->input->get('type', 'movie', 'word') == 'music')
		{
			$table = '#__ka_music_genres';
		}
		else
		{
			$table = '#__ka_genres';
		}

		$query = $db->getQuery(true);

		$query->delete($db->quoteName($table))
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
	 * @return  boolean  True on success.
	 *
	 * @since   3.0
	 */
	public function save($data)
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$user = JFactory::getUser();

		if ($app->input->get('type', 'movie', 'word') == 'music')
		{
			$table = '#__ka_music_genres';
		}
		else
		{
			$table = '#__ka_genres';
		}

		$name = trim($data['name']);

		// Automatic handling of alias for empty fields
		if (in_array($app->input->get('task'), array('genres.apply', 'genres.save', 'genres.save2new')) && (int) $app->input->get('id') == 0)
		{
			if ($data['alias'] === null)
			{
				if (JFactory::getConfig()->get('unicodeslugs') == 1)
				{
					$data['alias'] = JFilterOutput::stringUrlUnicodeSlug($name);
				}
				else
				{
					$data['alias'] = JFilterOutput::stringURLSafe($name);
				}
			}
		}

		if (empty($data['id']))
		{
			// Check if genre with this name allready exists
			$query = $db->getQuery(true);

			$query->select('COUNT(id)')
				->from($db->quoteName($table))
				->where($db->quoteName('name') . " = '" . $db->escape($name) . "'");

			$db->setQuery($query);
			$count = $db->loadResult();

			if ($count > 0)
			{
				$app->enqueueMessage(JText::_('COM_KA_COUNTRY_EXISTS'), 'error');

				return false;
			}

			$query = $db->getQuery(true);

			$query->insert($db->quoteName($table))
				->columns($db->quoteName(array('id', 'name', 'alias', 'stats', 'state', 'access', 'language')))
				->values("'','" . $db->escape($name) . "','" . $data['alias'] . "','" . (int) $data['stats'] . "','" . $data['state'] . "','" . (int) $data['access'] . "','" . $db->escape($data['language']) . "'");
		}
		else
		{
			$query = $db->getQuery(true);

			$query->update($db->quoteName($table))
				->set($db->quoteName('name') . " = '" . $db->escape($name) . "'")
				->set($db->quoteName('alias') . " = '" . $data['alias'] . "'")
				->set($db->quoteName('stats') . " = '" . (int) $data['stats'] . "'")
				->set($db->quoteName('state') . " = '" . $data['state'] . "'")
				->set($db->quoteName('access') . " = '" . (int) $data['access'] . "'")
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
				$session_data = $app->getUserState('com_kinoarhiv.genres.' . $user->id . '.edit_data');
				$session_data['id'] = $db->insertid();
				$app->setUserState('com_kinoarhiv.genres.' . $user->id . '.edit_data', $session_data);
			}
		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		// Clear the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to update stats for genres.
	 *
	 * @return  mixed   Total numbers of items, false otherwise.
	 *
	 * @since   3.0
	 */
	public function updateStats()
	{
		$app = JFactory::getApplication();

		if ($app->input->get('type', 'movie', 'word') == 'music')
		{
			$result = $this->updateMusicGenresStat();
		}
		elseif ($app->input->get('type', 'movie', 'word') == 'movie')
		{
			$result = $this->updateMovieGenresStat();
		}
		else
		{
			$result = false;
		}

		return $result;
	}

	/**
	 * Method to update stats for movie genres.
	 *
	 * @return  mixed   Total numbers of items, false otherwise.
	 *
	 * @since   3.1
	 */
	private function updateMovieGenresStat()
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$gid = $app->input->get('id', array(), 'array');
		$boxchecked = $app->input->get('boxchecked', 0, 'int');
		$total = 0;

		// Check if control number is the same with selected.
		if (count($gid) != $boxchecked)
		{
			return false;
		}

		$db->setDebug(true);
		$db->lockTable('#__ka_genres');
		$db->transactionStart();

		foreach ($gid as $genre_id)
		{
			$query = $db->getQuery(true)
				->update($db->quoteName('#__ka_genres'));

				$subquery = $db->getQuery(true)
					->select('COUNT(genre_id)')
					->from($db->quoteName('#__ka_rel_genres'))
					->where($db->quoteName('genre_id') . ' = ' . (int) $genre_id);

			$query->set($db->quoteName('stats') . " = (" . $subquery . ")")
				->where($db->quoteName('id') . ' = ' . (int) $genre_id . ';');

			$db->setQuery($query);
			$query = $db->execute();

			if ($query === false)
			{
				break;
			}
		}

		if ($query === false)
		{
			$db->transactionRollback();
			$app->enqueueMessage('Commit failed!', 'error');
		}
		else
		{
			$db->transactionCommit();

			// Count total genres for genre ID. Required for single row update.
			if (count($gid) == 1)
			{
				$query = $db->getQuery(true)
					->select($db->quoteName('stats'))
					->from($db->quoteName('#__ka_genres'))
					->where($db->quoteName('id') . ' = ' . (int) $gid[0]);

				$db->setQuery($query);
				$total = $db->loadResult();
			}
		}

		$db->unlockTables();
		$db->setDebug(false);

		if ($query === false)
		{
			return false;
		}

		return (int) $total;
	}

	/**
	 * Method to update stats for music genres.
	 *
	 * @return  mixed   Total numbers of items, false otherwise.
	 *
	 * @since   3.1
	 */
	private function updateMusicGenresStat()
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$gid = $app->input->get('id', array(), 'array');
		$boxchecked = $app->input->get('boxchecked', 0, 'int');
		$total = 0;

		// Check if control number is the same with selected.
		if (count($gid) != $boxchecked)
		{
			return false;
		}

		$db->setDebug(true);
		$db->lockTable('#__ka_music_genres');
		$db->transactionStart();

		foreach ($gid as $genre_id)
		{
			$query = $db->getQuery(true)
				->update($db->quoteName('#__ka_music_genres'));

				$subquery = $db->getQuery(true)
					->select('COUNT(genre_id)')
					->from($db->quoteName('#__ka_music_rel_genres'))
					->where($db->quoteName('genre_id') . ' = ' . (int) $genre_id . ' AND ' . $db->quoteName('type') . ' = 0');

			$query->set($db->quoteName('stats') . " = (" . $subquery . ")")
				->where($db->quoteName('id') . ' = ' . (int) $genre_id . ';');

			$db->setQuery($query);
			$query = $db->execute();

			if ($query === false)
			{
				break;
			}
		}

		if ($query === false)
		{
			$db->transactionRollback();
			$app->enqueueMessage('Commit failed!', 'error');
		}
		else
		{
			$db->transactionCommit();

			// Count total genres for genre ID. Required for single row update.
			if (count($gid) == 1)
			{
				$query = $db->getQuery(true)
					->select($db->quoteName('stats'))
					->from($db->quoteName('#__ka_music_genres'))
					->where($db->quoteName('id') . ' = ' . (int) $gid[0]);

				$db->setQuery($query);
				$total = $db->loadResult();
			}
		}

		$db->unlockTables();
		$db->setDebug(false);

		if ($query === false)
		{
			return false;
		}

		return (int) $total;
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
