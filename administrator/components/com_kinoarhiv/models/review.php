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
		$app      = JFactory::getApplication();
		$itemType = $app->input->get('item_type', '', 'alnum');

		if ($itemType == '' && $data['item_type'] == '')
		{
			$app->enqueueMessage('Wrong item type!', 'error');

			return false;
		}
		else
		{
			if ($itemType == 0)
			{
				$formName = 'movie';
			}
			elseif ($itemType == 1)
			{
				$formName = 'album';
			}

		}

		$form = $this->loadForm('com_kinoarhiv.review', 'review_' . $formName, array('control' => 'form', 'load_data' => $loadData));

		if (empty($form))
		{
			$app->enqueueMessage('Could not load XML form. File not found or something wrong!', 'error');

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
		$app   = JFactory::getApplication();
		$db    = $this->getDbo();
		$id    = $app->input->get('id', 0, 'int');
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('id', 'uid', 'item_id', 'item_type', 'review', 'created', 'type', 'ip', 'state')))
			->from($db->quoteName('#__ka_reviews'))
			->where($db->quoteName('id') . ' = ' . (int) $id);

		$db->setQuery($query);

		return $db->loadObject();
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

	/**
	 * Removes reviews from database.
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
		$app    = JFactory::getApplication();
		$db     = $this->getDbo();
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
			->set($db->quoteName('item_id') . " = '" . (int) $data['item_id'] . "'")
			->set($db->quoteName('item_type') . " = '" . (int) $data['item_type'] . "'")
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
}
