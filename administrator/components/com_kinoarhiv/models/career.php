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
 * Class KinoarhivModelCareer
 *
 * @since  3.0
 */
class KinoarhivModelCareer extends JModelForm
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
		$form = $this->loadForm('com_kinoarhiv.career', 'career', array('control' => 'form', 'load_data' => $loadData));

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
		$data = JFactory::getApplication()->getUserState('com_kinoarhiv.careers.' . JFactory::getUser()->id . '.edit_data', array());

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
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$id = $app->input->get('id', null, 'array');
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('id', 'title', 'is_mainpage', 'is_amplua', 'ordering', 'language')))
			->from($db->quoteName('#__ka_names_career'))
			->where($db->quoteName('id') . ' = ' . (int) $id[0]);

		$db->setQuery($query);
		$result = $db->loadObject();

		return $result;
	}

	/**
	 * Method to post an item on movie frontpage in cast&crew list.
	 *
	 * @param   integer  $offmainpage  Post on frontpage if == 1
	 *
	 * @return  boolean
	 *
	 * @since  3.0
	 */
	public function onmainpage($offmainpage)
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$ids = $app->input->get('id', array(), 'array');
		$state = $offmainpage ? 0 : 1;
		$query = $db->getQuery(true);

		$query->update($db->quoteName('#__ka_names_career'))
			->set($db->quoteName('is_mainpage') . ' = ' . (int) $state)
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
	 * Removes career(s).
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

		$query->delete($db->quoteName('#__ka_names_career'))
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
	 * @return  boolean
	 *
	 * @since   3.0
	 */
	public function save($data)
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$user = JFactory::getUser();
		$title = trim($data['title']);

		// Create a new record, update existing otherwise.
		if (empty($data['id']))
		{
			// Check if career with this title allready exists.
			$query = $db->getQuery(true);

			$query->select('COUNT(id)')
				->from($db->quoteName('#__ka_names_career'))
				->where($db->quoteName('title') . " = '" . $db->escape($title) . "'");

			$db->setQuery($query);
			$count = $db->loadResult();

			if ($count > 0)
			{
				$app->enqueueMessage(JText::_('COM_KA_CAREER_EXISTS'), 'error');

				return false;
			}

			$query = $db->getQuery(true);

			$query->insert($db->quoteName('#__ka_names_career'))
				->columns($db->quoteName(array('id', 'title', 'is_mainpage', 'is_amplua', 'ordering', 'language')))
				->values("'','" . $db->escape($title) . "','" . (int) $data['is_mainpage'] . "','" . (int) $data['is_amplua'] . "','" . (int) $data['ordering'] . "','" . $db->escape($data['language']) . "'");
		}
		else
		{
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__ka_names_career'))
				->set($db->quoteName('title') . " = '" . $db->escape($title) . "'")
				->set($db->quoteName('is_mainpage') . " = '" . (int) $data['is_mainpage'] . "'")
				->set($db->quoteName('is_amplua') . " = '" . (int) $data['is_amplua'] . "'")
				->set($db->quoteName('ordering') . " = '" . (int) $data['ordering'] . "'")
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
				$sessionData = $app->getUserState('com_kinoarhiv.careers.' . $user->id . '.edit_data');
				$sessionData['id'] = $db->insertid();
				$app->setUserState('com_kinoarhiv.careers.' . $user->id . '.edit_data', $sessionData);
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
