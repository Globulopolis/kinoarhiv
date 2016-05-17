<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('_JEXEC') or die;

/**
 * Class KinoarhivModelPremiere
 *
 * @since  3.0
 */
class KinoarhivModelPremiere extends JModelForm
{
	/**
	 * Abstract method for getting the form from the model.
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
		$form = $this->loadForm('com_kinoarhiv.premiere', 'premiere', array('control' => 'form', 'load_data' => $loadData));

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
		$data = JFactory::getApplication()->getUserState('com_kinoarhiv.premieres.' . JFactory::getUser()->id . '.edit_data', array());

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
	 */
	public function getItem()
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$_id = $app->input->get('id', array(), 'array');
		$id = !empty($_id) ? $_id[0] : $app->input->get('id', null, 'int');
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('id', 'movie_id', 'vendor_id', 'premiere_date', 'country_id', 'info', 'language', 'ordering')))
			->from($db->quoteName('#__ka_premieres'))
			->where($db->quoteName('id') . ' = ' . (int) $id);

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
		$id = $app->input->post->get('id', null, 'int');

		if (empty($id))
		{
			$query = $db->getQuery(true);

			$query->insert($db->quoteName('#__ka_premieres'))
				->columns($db->quoteName(array('id', 'movie_id', 'vendor_id', 'premiere_date', 'country_id', 'info', 'language', 'ordering')))
				->values("'','" . (int) $data['movie_id'] . "','" . (int) $data['vendor_id'] . "','" . $data['premiere_date'] . "','" . (int) $data['country_id'] . "','" . $db->escape($data['info']) . "','" . $db->escape($data['language']) . "','" . (int) $data['ordering'] . "'");
		}
		else
		{
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__ka_premieres'))
				->set($db->quoteName('movie_id') . " = '" . (int) $data['movie_id'] . "'")
				->set($db->quoteName('vendor_id') . " = '" . (int) $data['vendor_id'] . "'")
				->set($db->quoteName('premiere_date') . " = '" . $data['premiere_date'] . "'")
				->set($db->quoteName('country_id') . " = '" . (int) $data['country_id'] . "'")
				->set($db->quoteName('info') . " = '" . $db->escape($data['info']) . "'")
				->set($db->quoteName('language') . " = '" . $db->escape($data['language']) . "'")
				->set($db->quoteName('ordering') . " = '" . (int) $data['ordering'] . "'")
				->where($db->quoteName('id') . ' = ' . (int) $id);
		}

		try
		{
			$db->setQuery($query);
			$db->execute();

			if (empty($id))
			{
				$id = $db->insertid();
			}

			$app->setUserState('com_kinoarhiv.premieres.' . $user->id . '.data',
				array(
					'success' => true,
					'message' => JText::_('COM_KA_ITEMS_SAVE_SUCCESS'),
					'data'    => array('id' => $id)
				)
			);

			return true;
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			$app->setUserState('com_kinoarhiv.premieres.' . $user->id . '.data',
				array(
					'success' => false,
					'message' => JText::_('JERROR_AN_ERROR_HAS_OCCURRED')
				)
			);

			return false;
		}
	}

	public function remove()
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$ids = $app->input->get('id', array(), 'array');
		$query = $db->getQuery(true);

		$query->delete($db->quoteName('#__ka_premieres'))
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

	// TODO Should be removed in the feature releases
	public function deletePremieres()
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$data = $app->input->post->get('data', array(), 'array');
		$query = true;

		$db->setDebug(true);
		$db->lockTable('#__ka_premieres');
		$db->transactionStart();

		foreach ($data as $key => $value)
		{
			$_name = explode('_', $value['name']);
			$item_id = $_name[3];

			$db->setQuery("DELETE FROM " . $db->quoteName('#__ka_premieres') . " WHERE `id` = " . (int) $item_id . ";");
			$result = $db->execute();

			if ($result === false)
			{
				$query = false;
				break;
			}
		}

		if ($query === false)
		{
			$db->transactionRollback();
		}
		else
		{
			$db->transactionCommit();
		}

		$db->unlockTables();
		$db->setDebug(false);

		if ($query)
		{
			$success = true;
			$message = JText::_('COM_KA_ITEMS_DELETED_SUCCESS');
		}
		else
		{
			$success = false;
			$message = JText::_('COM_KA_ITEMS_DELETED_ERROR');
		}

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
				if (empty($data['movie_id']))
				{
					$this->setError(JText::sprintf('JLIB_FORM_VALIDATE_FIELD_REQUIRED', JText::_('COM_KA_FIELD_MOVIE_LABEL')));
				}

				if (empty($data['vendor_id']))
				{
					$this->setError(JText::sprintf('JLIB_FORM_VALIDATE_FIELD_REQUIRED', JText::_('COM_KA_FIELD_PREMIERE_VENDOR')));
				}

				$this->setError($message);
			}

			return false;
		}

		return $data;
	}
}
