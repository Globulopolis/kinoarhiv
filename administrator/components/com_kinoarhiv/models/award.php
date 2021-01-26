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
 * Class KinoarhivModelAward
 *
 * @since  3.0
 */
class KinoarhivModelAward extends JModelForm
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
		$form = $this->loadForm('com_kinoarhiv.award', 'award', array('control' => 'form', 'load_data' => $loadData));

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
		$data = JFactory::getApplication()->getUserState('com_kinoarhiv.awards.' . JFactory::getUser()->id . '.edit_data', array());

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
	 * @since  3.0
	 */
	public function getItem()
	{
		$app    = JFactory::getApplication();
		$db     = $this->getDbo();
		$id     = $app->input->get('id', 0, 'int');
		$query  = $db->getQuery(true);
		$result = array();

		$query->select($db->quoteName(array('id', 'title', 'desc', 'language', 'state')))
			->from($db->quoteName('#__ka_awards'))
			->where($db->quoteName('id') . ' = ' . (int) $id);

		$db->setQuery($query);

		try
		{
			$result = $db->loadObject();
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());
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
		$db = $this->getDbo();
		$ids = $app->input->get('id', array(), 'array');
		$state = $isUnpublish ? 0 : 1;
		$query = $db->getQuery(true);

		$query->update($db->quoteName('#__ka_awards'))
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
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}
	}

	/**
	 * Removes award(s).
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.0
	 */
	public function remove()
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$ids = $app->input->get('id', array(), 'array');
		$query = $db->getQuery(true);

		$query->delete($db->quoteName('#__ka_awards'))
			->where($db->quoteName('id') . ' IN (' . implode(',', $ids) . ')');

		$db->setQuery($query);

		try
		{
			$db->execute();

			return true;
		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');

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
		$title = trim($data['title']);

		if (empty($data['id']))
		{
			// Check if award with this title allready exists
			$query = $db->getQuery(true);

			$query->select('COUNT(id)')
				->from($db->quoteName('#__ka_awards'))
				->where($db->quoteName('title') . " = '" . $db->escape($title) . "'");

			$db->setQuery($query);
			$count = $db->loadResult();

			if ($count > 0)
			{
				$app->enqueueMessage(JText::_('COM_KA_AW_EXISTS'), 'error');

				return false;
			}

			$query = $db->getQuery(true);

			$query->insert($db->quoteName('#__ka_awards'))
				->columns($db->quoteName(array('id', 'title', 'desc', 'state', 'language')))
				->values("'','" . $db->escape($title) . "','" . $db->escape($data['description']) . "','" . $data['state'] . "','" . $db->escape($data['language']) . "'");
		}
		else
		{
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__ka_awards'))
				->set($db->quoteName('title') . " = '" . $db->escape($title) . "'")
				->set($db->quoteName('desc') . " = '" . $db->escape($data['description']) . "'")
				->set($db->quoteName('state') . " = '" . $data['state'] . "'")
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
				$sessionData = $app->getUserState('com_kinoarhiv.awards.' . $user->id . '.edit_data');
				$sessionData['id'] = $db->insertid();
				$app->setUserState('com_kinoarhiv.awards.' . $user->id . '.edit_data', $sessionData);
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
}
