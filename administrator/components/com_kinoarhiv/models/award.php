<?php defined('_JEXEC') or die;
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url			http://киноархив.com/
 */

class KinoarhivModelAward extends JModelForm {
	public function getForm($data = array(), $loadData = true) {
		$form = $this->loadForm('com_kinoarhiv.award', 'award', array('control' => 'form', 'load_data' => $loadData));

		if (empty($form)) {
			return false;
		}

		return $form;
	}

	protected function loadFormData() {
		$app = JFactory::getApplication();
		$data = $app->getUserState('com_kinoarhiv.awards.'.JFactory::getUser()->id.'.edit_data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}

	public function getItem() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$_id = $app->input->get('id', array(), 'array');
		$id = !empty($_id) ? $_id[0] : $app->input->get('id', null, 'int');
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('id', 'title', 'desc', 'language', 'state')))
			->from($db->quoteName('#__ka_awards'))
			->where($db->quoteName('id').' = '.(int)$id);

		$db->setQuery($query);
		$result = $db->loadObject();

		return $result;
	}

	public function publish($isUnpublish) {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$ids = $app->input->get('id', array(), 'array');
		$state = $isUnpublish ? 0 : 1;
		$query = $db->getQuery(true);

		$query->update($db->quoteName('#__ka_awards'))
			->set($db->quoteName('state').' = '.(int)$state)
			->where($db->quoteName('id').' IN ('.implode(',', $ids).')');

		$db->setQuery($query);

		try {
			$db->execute();

			return true;
		} catch(Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}
	}

	public function remove() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$ids = $app->input->get('id', array(), 'array');
		$query = $db->getQuery(true);

		$query->delete($db->quoteName('#__ka_awards'))
			->where($db->quoteName('id').' IN ('.implode(',', $ids).')');

		$db->setQuery($query);

		try {
			$db->execute();

			return true;
		} catch(Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}
	}

	public function save($data) {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$user = JFactory::getUser();
		$id = $app->input->post->get('id', null, 'int');
		$title = trim($data['title']);

		if (empty($title)) {
			$this->setError(JText::_('COM_KA_REQUIRED'));

			$app->setUserState('com_kinoarhiv.awards.'.$user->id.'.data', array(
				'success' => false,
				'message' => JText::_('COM_KA_REQUIRED')
			));

			return false;
		}

		if (empty($id)) {
			// Check if award with this title allready exists
			$query = $db->getQuery(true);

			$query->select('COUNT(id)')
				->from($db->quoteName('#__ka_awards'))
				->where($db->quoteName('title')." = '".$db->escape($title)."'");

			$db->setQuery($query);
			$count = $db->loadResult();

			if ($count > 0) {
				$this->setError(JText::_('COM_KA_AW_EXISTS'));

				$app->setUserState('com_kinoarhiv.awards.'.$user->id.'.data', array(
					'success' => false,
					'message' => JText::_('COM_KA_AW_EXISTS')
				));

				return false;
			}

			$query = $db->getQuery(true);

			$query->insert($db->quoteName('#__ka_awards'))
				->columns($db->quoteName(array('title', 'desc', 'state', 'language')))
				->values("'".$db->escape($title)."','".$db->escape($data['desc'])."','".$data['state']."','".$db->escape($data['language'])."'");
		} else {
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__ka_awards'))
				->set($db->quoteName('title')." = '".$db->escape($title)."'")
				->set($db->quoteName('desc')." = '".$db->escape($data['desc'])."'")
				->set($db->quoteName('state')." = '".$data['state']."'")
				->set($db->quoteName('language')." = '".$db->escape($data['language'])."'")
				->where($db->quoteName('id').' = '.(int)$id);
		}

		try {
			$db->setQuery($query);
			$db->execute();

			if (empty($id)) {
				$id = $db->insertid();
			}

			$app->setUserState('com_kinoarhiv.awards.'.$user->id.'.data', array(
				'success' => true,
				'message' => JText::_('COM_KA_ITEMS_SAVE_SUCCESS'),
				'data'    => array('id' => $id, 'title' => $title)
			));

			return true;
		} catch(Exception $e) {
			$this->setError($e->getMessage());

			$app->setUserState('com_kinoarhiv.awards.'.$user->id.'.data', array(
				'success' => false,
				'message' => JText::_('JERROR_AN_ERROR_HAS_OCCURRED')
			));

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
	 * @return  mixed  Array of filtered data if valid, false otherwise.
	 *
	 * @see     JFormRule
	 * @see     JFilterInput
	 * @since   12.2
	 */
	public function validate($form, $data, $group = null) {
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
				$this->setError($message);
			}

			return false;
		}

		return $data;
	}
}
