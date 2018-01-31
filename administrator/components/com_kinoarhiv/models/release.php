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
 * Class KinoarhivModelRelease
 *
 * @since  3.0
 */
class KinoarhivModelRelease extends JModelForm
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
		$form = $this->loadForm('com_kinoarhiv.release', 'release', array('control' => 'form', 'load_data' => $loadData));

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
		$data = JFactory::getApplication()->getUserState('com_kinoarhiv.releases.' . JFactory::getUser()->id . '.edit_data', array());

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

		$query->select(
			$db->quoteName(
				array('r.id', 'r.country_id', 'r.vendor_id', 'r.movie_id', 'r.media_type', 'r.release_date', 'r.desc', 'r.language', 'r.ordering')
			)
		)
			->select($db->quoteName('c.code') . ',' . $db->quoteName('c.name', 'title'))
			->from($db->quoteName('#__ka_releases', 'r'))
			->join('LEFT', $db->quoteName('#__ka_countries', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('r.country_id'))
			->where($db->quoteName('r.id') . ' = ' . (int) $id[0]);

		$db->setQuery($query);
		$result = $db->loadObject();

		return $result;
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

		if (empty($data['id']))
		{
			$query = $db->getQuery(true);

			$query->insert($db->quoteName('#__ka_releases'))
				->columns(
					$db->quoteName(array('id', 'country_id', 'vendor_id', 'movie_id', 'media_type', 'release_date', 'desc', 'language', 'ordering'))
				)
				->values("'','" . (int) $data['country_id'] . "','" . (int) $data['vendor_id'] . "','" . (int) $data['movie_id'] . "','" . (int) $data['media_type'] . "','" . $db->escape($data['release_date']) . "','" . $db->escape($data['desc']) . "','" . $db->escape($data['language']) . "','" . (int) $data['ordering'] . "'");
		}
		else
		{
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__ka_releases'))
				->set($db->quoteName('country_id') . " = '" . (int) $data['country_id'] . "'")
				->set($db->quoteName('vendor_id') . " = '" . (int) $data['vendor_id'] . "'")
				->set($db->quoteName('movie_id') . " = '" . (int) $data['movie_id'] . "'")
				->set($db->quoteName('media_type') . " = '" . (int) $data['media_type'] . "'")
				->set($db->quoteName('release_date') . " = '" . $data['release_date'] . "'")
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
				$sessionData = $app->getUserState('com_kinoarhiv.releases.' . $user->id . '.edit_data');
				$sessionData['id'] = $db->insertid();
				$app->setUserState('com_kinoarhiv.releases.' . $user->id . '.edit_data', $sessionData);
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
	 * Removes release(s).
	 *
	 * @param   array  $ids  Array of items.
	 *
	 * @return  boolean
	 *
	 * @since   3.0
	 */
	public function remove($ids)
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		$query->delete($db->quoteName('#__ka_releases'))
			->where($db->quoteName('id') . ' IN (' . implode(',', $ids) . ')');

		$db->setQuery($query);

		try
		{
			$db->execute();

			return true;
		}
		catch (Exception $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			return false;
		}
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
				if (empty($data['movie_id']))
				{
					$this->setError(JText::sprintf('JLIB_FORM_VALIDATE_FIELD_REQUIRED', JText::_('COM_KA_FIELD_MOVIE_LABEL')));
				}

				if (empty($data['vendor_id']))
				{
					$this->setError(JText::sprintf('JLIB_FORM_VALIDATE_FIELD_REQUIRED', JText::_('COM_KA_FIELD_RELEASE_VENDOR')));
				}

				if (empty($data['country_id']))
				{
					$this->setError(JText::sprintf('JLIB_FORM_VALIDATE_FIELD_REQUIRED', JText::_('COM_KA_FIELD_RELEASE_COUNTRY')));
				}

				$this->setError($message);
			}

			return false;
		}

		return $data;
	}
}
