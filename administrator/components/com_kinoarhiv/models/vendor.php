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
 * Class KinoarhivModelVendor
 *
 * @since  3.0
 */
class KinoarhivModelVendor extends JModelForm
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
		$form = $this->loadForm('com_kinoarhiv.vendor', 'vendor', array('control' => 'form', 'load_data' => $loadData));

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
		$data = JFactory::getApplication()->getUserState('com_kinoarhiv.vendors.' . JFactory::getUser()->id . '.edit_data', array());

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
		$id = $app->input->get('id', 0, 'int');
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('id', 'company_name', 'company_name_alias', 'description', 'language', 'state')))
			->from($db->quoteName('#__ka_vendors'))
			->where($db->quoteName('id') . ' = ' . (int) $id);

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
		$query = $db->getQuery(true);

		$query->update($db->quoteName('#__ka_vendors'))
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
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}
	}

	/**
	 * Removes vendors from database.
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

		$query->delete($db->quoteName('#__ka_vendors'))
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
		$companyName = trim($data['company_name']);

		// Automatic handling of alias for empty fields
		if (in_array($app->input->get('task'), array('apply', 'save', 'save2new'))
			&& (int) $app->input->get('id') == 0 || $data['company_name_alias'] == '')
		{
			if (JFactory::getConfig()->get('unicodeslugs') == 1)
			{
				$data['company_name_alias'] = JFilterOutput::stringUrlUnicodeSlug($companyName);
			}
			else
			{
				$data['company_name_alias'] = JFilterOutput::stringURLSafe($companyName);
			}
		}

		if (empty($data['id']))
		{
			// Check if vendor with this company name allready exists
			$query = $db->getQuery(true);

			$query->select('COUNT(id)')
				->from($db->quoteName('#__ka_vendors'))
				->where($db->quoteName('company_name') . " = '" . $db->escape($companyName) . "'");

			$db->setQuery($query);
			$count = $db->loadResult();

			if ($count > 0)
			{
				$app->enqueueMessage(JText::_('COM_KA_VENDORS_EXISTS'), 'error');

				return false;
			}

			$query = $db->getQuery(true);

			$query->insert($db->quoteName('#__ka_vendors'))
				->columns($db->quoteName(array('id', 'company_name', 'company_name_alias', 'description', 'language', 'state')))
				->values("'','" . $db->escape($companyName) . "','" . $data['company_name_alias'] . "','" . $db->escape($data['description']) . "','" . $db->escape($data['language']) . "','" . $data['state'] . "'");
		}
		else
		{
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__ka_vendors'))
				->set($db->quoteName('company_name') . " = '" . $db->escape($companyName) . "'")
				->set($db->quoteName('company_name_alias') . " = '" . $data['company_name_alias'] . "'")
				->set($db->quoteName('description') . " = '" . $db->escape($data['description']) . "'")
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
				$sessionData = $app->getUserState('com_kinoarhiv.vendors.' . $user->id . '.edit_data');
				$sessionData['id'] = $db->insertid();
				$app->setUserState('com_kinoarhiv.vendors.' . $user->id . '.edit_data', $sessionData);
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
