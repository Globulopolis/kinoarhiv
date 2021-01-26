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
					'access'   => $app->input->getInt(
						'access',
						(!empty($filters['access']) ? $filters['access'] : JFactory::getConfig()->get('access'))
					)
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
		$app   = JFactory::getApplication();
		$db    = $this->getDbo();
		$id    = $app->input->get('id', 0, 'int');
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('id', 'name', 'alias', 'desc', 'type', 'stats', 'state', 'access', 'language')))
			->from($db->quoteName('#__ka_genres'))
			->where($db->quoteName('id') . ' = ' . (int) $id);

		$db->setQuery($query);

		return $db->loadObject();
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

		$query->update($db->quoteName('#__ka_genres'))
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
	 * Removes genres from database.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.0
	 */
	public function remove()
	{
		$app   = JFactory::getApplication();
		$db    = $this->getDbo();
		$ids   = $app->input->get('id', array(), 'array');
		$query = $db->getQuery(true);

		$query->delete($db->quoteName('#__ka_genres'))
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
		$app  = JFactory::getApplication();
		$db   = $this->getDbo();
		$user = JFactory::getUser();
		$name = trim($data['name']);

		// Automatic handling of alias for empty fields
		if (in_array($app->input->get('task'), array('apply', 'save', 'save2new')) && $data['alias'] == '')
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

		if (empty($data['id']))
		{
			// Check if genre with this name allready exists
			$query = $db->getQuery(true);

			$query->select('COUNT(id)')
				->from($db->quoteName('#__ka_genres'))
				->where($db->quoteName('name') . " = '" . $db->escape($name) . "'");

			$db->setQuery($query);
			$count = $db->loadResult();

			if ($count > 0)
			{
				$app->enqueueMessage(JText::_('COM_KA_GENRE_EXISTS'), 'error');

				return false;
			}

			$query  = $db->getQuery(true);
			$values = array(
				'id'       => '',
				'name'     => $db->escape($name),
				'desc'     => $db->escape($data['desc']),
				'type'     => (int) $data['type'],
				'stats'    => (int) $data['stats'],
				'state'    => $data['state'],
				'access'   => (int) $data['access'],
				'language' => $db->escape($data['language'])
			);

			$query->insert($db->quoteName('#__ka_genres'))
				->columns($db->quoteName(array_keys($values)))
				->values("'" . implode("','", array_values($values)) . "'");
		}
		else
		{
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__ka_genres'))
				->set($db->quoteName('name') . " = '" . $db->escape($name) . "'")
				->set($db->quoteName('alias') . " = '" . $data['alias'] . "'")
				->set($db->quoteName('desc') . " = '" . $db->escape($data['desc']) . "'")
				->set($db->quoteName('type') . " = '" . (int) $data['type'] . "'")
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
				$sessionData = $app->getUserState('com_kinoarhiv.genres.' . $user->id . '.edit_data');
				$sessionData['id'] = $db->insertid();
				$app->setUserState('com_kinoarhiv.genres.' . $user->id . '.edit_data', $sessionData);
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
	 * @param   array  $ids    Items.
	 * @param   array  $types  Item type.
	 *
	 * @return  mixed   Total numbers of items, false otherwise.
	 *
	 * @since   3.1
	 */
	public function updateStats($ids, $types)
	{
		$app        = JFactory::getApplication();
		$db         = $this->getDbo();
		$boxchecked = $app->input->get('boxchecked', 0, 'int');
		$total      = 0;
		$queryResut = true;

		// Check if control number is the same with selected.
		if (count($ids) != $boxchecked)
		{
			return false;
		}

		$db->lockTable('#__ka_genres');
		$db->transactionStart();

		foreach ($ids as $genreID)
		{
			$query = $db->getQuery(true)
				->update($db->quoteName('#__ka_genres'));

			$subquery = $db->getQuery(true)
				->select('COUNT(genre_id)');

			if ($types[$genreID] == 0)
			{
				$subquery->from($db->quoteName('#__ka_rel_genres'));
			}
			else
			{
				$subquery->from($db->quoteName('#__ka_music_rel_genres'));
			}

			$subquery->where($db->quoteName('genre_id') . ' = ' . (int) $genreID);

			$query->set($db->quoteName('stats') . " = (" . $subquery . ")")
				->where($db->quoteName('id') . ' = ' . (int) $genreID . ';');

			$db->setQuery($query);
			$queryResut = $db->execute();

			if ($queryResut === false)
			{
				break;
			}
		}

		if ($queryResut === false)
		{
			$db->transactionRollback();
			$app->enqueueMessage('Commit failed!', 'error');
		}
		else
		{
			$db->transactionCommit();

			// Count total genres for genre ID. Required for single row update.
			if (count($ids) == 1)
			{
				$query = $db->getQuery(true)
					->select($db->quoteName('stats'))
					->from($db->quoteName('#__ka_genres'))
					->where($db->quoteName('id') . ' = ' . (int) $ids[0]);

				$db->setQuery($query);
				$total = $db->loadResult();
			}
		}

		$db->unlockTables();

		if ($queryResut === false)
		{
			return false;
		}

		return (int) $total;
	}
}
