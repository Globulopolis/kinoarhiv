<?php defined('_JEXEC') or die;

/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */
class KinoarhivModelRelease extends JModelForm
{
	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm('com_kinoarhiv.release', 'release', array('control' => 'form', 'load_data' => $loadData));

		if (empty($form)) {
			return false;
		}

		return $form;
	}

	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState('com_kinoarhiv.releases.' . JFactory::getUser()->id . '.edit_data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}

	public function getItem()
	{
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$_id = $app->input->get('id', array(), 'array');
		$id = !empty($_id) ? $_id[0] : $app->input->get('id', null, 'int');
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('r.id', 'r.country_id', 'r.vendor_id', 'r.movie_id', 'r.media_type', 'r.release_date', 'r.desc', 'r.language', 'r.ordering')))
			->select($db->quoteName('c.code') . ',' . $db->quoteName('c.name', 'title'))
			->from($db->quoteName('#__ka_releases', 'r'))
			->join('LEFT', $db->quoteName('#__ka_countries', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('r.country_id'))
			->where($db->quoteName('r.id') . ' = ' . (int)$id);

		$db->setQuery($query);
		$result = $db->loadObject();

		return $result;
	}

	public function save($data)
	{
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$user = JFactory::getUser();
		$id = $app->input->post->get('id', null, 'int');

		if (empty($id)) {
			$query = $db->getQuery(true);

			$query->insert($db->quoteName('#__ka_releases'))
				->columns($db->quoteName(array('id', 'country_id', 'vendor_id', 'movie_id', 'media_type', 'release_date', 'desc', 'language', 'ordering')))
				->values("'','" . (int)$data['country_id'] . "','" . (int)$data['vendor_id'] . "','" . (int)$data['movie_id'] . "','" . (int)$data['media_type'] . "','" . $db->escape($data['release_date']) . "','" . $db->escape($data['desc']) . "','" . $db->escape($data['language']) . "','" . (int)$data['ordering'] . "'");
		} else {
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__ka_releases'))
				->set($db->quoteName('country_id') . " = '" . (int)$data['country_id'] . "'")
				->set($db->quoteName('vendor_id') . " = '" . (int)$data['vendor_id'] . "'")
				->set($db->quoteName('movie_id') . " = '" . (int)$data['movie_id'] . "'")
				->set($db->quoteName('media_type') . " = '" . (int)$data['media_type'] . "'")
				->set($db->quoteName('release_date') . " = '" . $data['release_date'] . "'")
				->set($db->quoteName('desc') . " = '" . $db->escape($data['desc']) . "'")
				->set($db->quoteName('language') . " = '" . $db->escape($data['language']) . "'")
				->set($db->quoteName('ordering') . " = '" . (int)$data['ordering'] . "'")
				->where($db->quoteName('id') . ' = ' . (int)$id);
		}

		try {
			$db->setQuery($query);
			$db->execute();

			if (empty($id)) {
				$id = $db->insertid();
			}

			$app->setUserState('com_kinoarhiv.releases.' . $user->id . '.data', array(
				'success' => true,
				'message' => JText::_('COM_KA_ITEMS_SAVE_SUCCESS'),
				'data'    => array('id' => $id)
			));

			return true;
		} catch (Exception $e) {
			$this->setError($e->getMessage());

			$app->setUserState('com_kinoarhiv.releases.' . $user->id . '.data', array(
				'success' => false,
				'message' => JText::_('JERROR_AN_ERROR_HAS_OCCURRED')
			));

			return false;
		}
	}

	public function remove()
	{
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$ids = $app->input->get('id', array(), 'array');
		$query = $db->getQuery(true);

		$query->delete($db->quoteName('#__ka_releases'))
			->where($db->quoteName('id') . ' IN (' . implode(',', $ids) . ')');

		$db->setQuery($query);

		try {
			$db->execute();

			return true;
		} catch (Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}
	}

	// TODO Should be removed in the feature releases
	public function deleteReleases()
	{
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$data = $app->input->post->get('data', array(), 'array');
		$query = true;

		$db->setDebug(true);
		$db->lockTable('#__ka_releases');
		$db->transactionStart();

		foreach ($data as $key => $value) {
			$_name = explode('_', $value['name']);
			$item_id = $_name[3];

			$query = $db->getQuery(true);

			$query->delete($db->quoteName('#__ka_releases'))
				->where($db->quoteName('id') . ' = ' . (int)$item_id);
			$db->setQuery($query . ';');

			if ($db->execute() === false) {
				$query = false;
				break;
			}
		}

		if ($query === false) {
			$db->transactionRollback();
		} else {
			$db->transactionCommit();
		}

		$db->unlockTables();
		$db->setDebug(false);

		if ($query) {
			$success = true;
			$message = JText::_('COM_KA_ITEMS_DELETED_SUCCESS');
		} else {
			$success = false;
			$message = JText::_('COM_KA_ITEMS_DELETED_ERROR');
		}

		return array('success' => $success, 'message' => $message);
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param   JForm  $form  The form to validate against.
	 * @param   array  $data  The data to validate.
	 * @param   string $group The name of the field group to validate.
	 *
	 * @return  mixed  Array of filtered data if valid, false otherwise.
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
		if ($return instanceof Exception) {
			$this->setError($return->getMessage());

			return false;
		}

		// Check the validation results.
		if ($return === false) {
			// Get the validation messages from the form.
			foreach ($form->getErrors() as $message) {
				if (empty($data['movie_id'])) {
					$this->setError(JText::sprintf('JLIB_FORM_VALIDATE_FIELD_REQUIRED', JText::_('COM_KA_FIELD_MOVIE_LABEL')));
				}
				if (empty($data['vendor_id'])) {
					$this->setError(JText::sprintf('JLIB_FORM_VALIDATE_FIELD_REQUIRED', JText::_('COM_KA_FIELD_RELEASE_VENDOR')));
				}
				if (empty($data['country_id'])) {
					$this->setError(JText::sprintf('JLIB_FORM_VALIDATE_FIELD_REQUIRED', JText::_('COM_KA_FIELD_RELEASE_COUNTRY')));
				}
				$this->setError($message);
			}

			return false;
		}

		return $data;
	}
}
