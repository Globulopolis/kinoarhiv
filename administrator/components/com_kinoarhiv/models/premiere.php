<?php defined('_JEXEC') or die;

class KinoarhivModelPremiere extends JModelForm {
	public function getForm($data = array(), $loadData = true) {
		$form = $this->loadForm('com_kinoarhiv.premiere', 'premiere', array('control' => 'form', 'load_data' => $loadData));

		if (empty($form)) {
			return false;
		}

		return $form;
	}

	protected function loadFormData() {
		return $this->getItem();
	}

	public function getItem() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$id = $app->input->post->get('id', array(), 'array');

		if (empty($id)) {
			return array();
		}

		$db->setQuery("SELECT `id`, `movie_id`, `vendor_id`, `premiere_date`, `country_id`, `info`, `ordering`"
			. "\n FROM ".$db->quoteName('#__ka_premieres')
			. "\n WHERE `id` = ".(int)$id[0]);
		$result = $db->loadObject();

		return $result;
	}

	public function savePremiere() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$id = $app->input->get('id', null, 'int');
		$movie_id = $app->input->get('movie_id', null, 'int');
		$data = $app->input->get('form', array(), 'array');
		$is_new = $app->input->get('new', 0, 'int');

		if (isset($data['p_vendor_id'][0]) && !empty($data['p_vendor_id'][0])) {
			if (count($data['p_country_id']) > 1) {
				$country_id = $data['p_country_id'][1];
			} else {
				$country_id = $data['p_country_id'][0];
			}

			// Prevent duplicates
			$db->setQuery("SELECT COUNT(`id`) FROM ".$db->quoteName('#__ka_premieres')." WHERE `movie_id` = ".$movie_id." AND `vendor_id` = ".$data['p_vendor_id'][0]." AND `country_id` = ".$country_id);
			$c = $db->loadResult();

			if ($c > 0 && $is_new != 0) {
				return array('success'=>false, 'message'=>'Error');
			}

			if ($is_new == 1) {
				$db->setQuery("INSERT INTO ".$db->quoteName('#__ka_premieres')." (`id`, `movie_id`, `vendor_id`, `premiere_date`, `country_id`, `info`, `ordering`)"
					. "\n VALUES ('', '".$movie_id."', '".$data['p_vendor_id'][0]."', '".$data['p_premiere_date']."', '".$country_id."', '".$db->escape($data['p_info'])."', '".$data['p_ordering']."')");
			} else {
				$db->setQuery("UPDATE ".$db->quoteName('#__ka_premieres')
					. "\n SET `vendor_id` = '".$data['p_vendor_id'][0]."', `premiere_date` = '".$data['p_premiere_date']."', `country_id` = '".$country_id."', `info` = '".$db->escape($data['p_info'])."', `ordering` = '".$data['p_ordering']."'"
					. "\n WHERE `id` = ".(int)$id);
			}

			try {
				$db->execute();
				return array('success'=>true, 'message'=>JText::_('COM_KA_SAVED'));
			} catch(Exception $e) {
				return array('success'=>false, 'message'=>$e->getMessage());
			}
		} else {
			return array('success'=>false, 'message'=>JText::_('COM_KA_FIELD_PREMIERE_VENDOR_REQUIRED'));
		}
	}

	public function saveOrder() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$data = $app->input->post->get('ord', array(), 'array');
		$movie_id = $app->input->post->get('movie_id', null, 'int');

		if (count($data) < 2) {
			return array('success'=>false, 'message'=>JText::_('COM_KA_SAVE_ORDER_AT_LEAST_TWO'));
		}

		$query = true;

		$db->setDebug(true);
		$db->lockTable('#__ka_premieres');
		$db->transactionStart();

		foreach ($data as $key=>$value) {
			$db->setQuery("UPDATE ".$db->quoteName('#__ka_premieres')." SET `ordering` = '".(int)$key."' WHERE `id` = ".(int)$value." AND `movie_id` = ".(int)$movie_id.";");
			$result = $db->execute();

			if ($result === false) {
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
			$message = JText::_('COM_KA_SAVED');
		} else {
			$success = false;
			$message = JText::_('COM_KA_SAVE_ORDER_ERROR');
		}

		return array('success'=>$success, 'message'=>$message);
	}

	public function remove() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$ids = $app->input->get('id', array(), 'array');

		$db->setQuery("DELETE FROM ".$db->quoteName('#__ka_premieres')." WHERE `id` IN (".implode(',', $ids).")");

		try {
			$db->execute();

			return true;
		} catch(Exception $e) {
			$this->setError($e->getMessage());

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
