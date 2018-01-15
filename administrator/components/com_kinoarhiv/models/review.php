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
 * Class KinoarhivModelReview
 *
 * @since  3.0
 */
class KinoarhivModelReview extends JModelForm
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
		$form = $this->loadForm('com_kinoarhiv.review', 'review', array('control' => 'form', 'load_data' => $loadData));

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
		$data = JFactory::getApplication()->getUserState('com_kinoarhiv.reviews.' . JFactory::getUser()->id . '.edit_data', array());

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

		$query->select($db->quoteName(array('id', 'uid', 'movie_id', 'review', 'created', 'type', 'ip', 'state')))
			->from($db->quoteName('#__ka_reviews'))
			->where($db->quoteName('id') . ' = ' . (int) $id[0]);

		$db->setQuery($query);
		$result = $db->loadObject();

		return $result;
	}

	/**
	 * Method to change the state of one or more records.
	 *
	 * @param   array    $ids    Array of item IDs
	 * @param   integer  $state  Item state
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.0
	 */
	public function setItemState($ids, $state)
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		$query->update($db->quoteName('#__ka_reviews'))
			->set($db->quoteName('state') . " = '" . (int) $state . "'")
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
		$query = $db->getQuery(true);

		$query->delete($db->quoteName('#__ka_reviews'))
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
		$review = trim($data['review']);

		if (empty($review))
		{
			$app->enqueueMessage(JText::_('COM_KA_REQUIRED'), 'error');

			return false;
		}

		if (empty($data['id']))
		{
			$app->enqueueMessage(JText::_('ERROR'), 'error');

			return false;
		}

		$query = $db->getQuery(true);

		$query->update($db->quoteName('#__ka_reviews'))
			->set($db->quoteName('uid') . " = '" . (int) $data['uid'] . "'")
			->set($db->quoteName('movie_id') . " = '" . (int) $data['movie_id'] . "'")
			->set($db->quoteName('review') . " = '" . $db->escape($data['review']) . "'")
			->set($db->quoteName('created') . " = '" . $data['created'] . "'")
			->set($db->quoteName('type') . " = '" . (int) $data['type'] . "'")
			->set($db->quoteName('ip') . " = '" . (string) $data['ip'] . "'")
			->set($db->quoteName('state') . " = '" . $data['state'] . "'")
			->where($db->quoteName('id') . ' = ' . (int) $data['id']);

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
