<?php defined('_JEXEC') or die;
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url			http://киноархив.com/
 */

class KinoarhivModelRelease extends JModelForm {
	public function getForm($data = array(), $loadData = true) {
		$form = $this->loadForm('com_kinoarhiv.release', 'release', array('control' => 'form', 'load_data' => $loadData));

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
		$id = $app->input->get('id', array(0), 'array');

		if (empty($id)) {
			return array();
		}

		$db->setQuery("SELECT `r`.`id`, `r`.`country_id`, `r`.`vendor_id`, `r`.`movie_id`, `r`.`media_type`, `r`.`release_date`, `r`.`ordering`, `c`.`code`, `c`.`name` AS `title`"
			. "\n FROM ".$db->quoteName('#__ka_releases')." AS `r`"
			. "\n LEFT JOIN ".$db->quoteName('#__ka_countries')." AS `c` ON `c`.`id` = `r`.`country_id`"
			. "\n WHERE `r`.`id` = ".(int)$id[0]);
		$result = $db->loadObject();

		return $result;
	}

	public function saveReleaseAjax() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$id = $app->input->get('id', null, 'int');
		$movie_id = $app->input->get('movie_id', null, 'int');
		$data = $app->input->get('form', array(), 'array');
		$is_new = $app->input->get('new', 0, 'int');

		if ((isset($data['r_vendor_id']) && !empty($data['r_vendor_id'])) || (isset($data['r_country_id']) && !empty($data['r_country_id']))) {
			if ($is_new == 1) {
				$db->setQuery("INSERT INTO ".$db->quoteName('#__ka_releases')." (`id`, `country_id`, `vendor_id`, `movie_id`, `media_type`, `release_date`, `desc`, `ordering`)"
					. "\n VALUES ('', '".(int)$data['r_country_id']."', '".(int)$data['r_vendor_id']."', '".$movie_id."', '".(int)$data['r_media_type']."', '".$data['r_release_date']."', '".$db->escape($data['desc'])."', '".(int)$data['r_ordering']."')");
			} else {
				$db->setQuery("UPDATE ".$db->quoteName('#__ka_releases')
					. "\n SET `country_id` = '".(int)$data['r_country_id']."', `vendor_id` = '".(int)$data['r_vendor_id']."', `media_type` = '".(int)$data['r_media_type']."', `release_date` = '".$data['r_release_date']."', `desc` = '".$db->escape($data['desc'])."', `ordering` = '".(int)$data['r_ordering']."'"
					. "\n WHERE `id` = ".(int)$id);
			}

			try {
				$db->execute();
				return array('success'=>true, 'message'=>JText::_('COM_KA_SAVED'));
			} catch(Exception $e) {
				return array('success'=>false, 'message'=>$e->getMessage());
			}
		} else {
			return array('success'=>false, 'message'=>JText::_('COM_KA_REQUIRED'));
		}
	}

	public function saveRelease($data) {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$user = JFactory::getUser();
		$id = $app->input->get('id', 0, 'int');

		if (empty($id)) {
			$db->setQuery("INSERT INTO ".$db->quoteName('#__ka_releases')." (`id`, `country_id`, `vendor_id`, `movie_id`, `media_type`, `release_date`, `desc`, `ordering`)"
				. "\n VALUES ('', '".(int)$data['country_id']."', '".(int)$data['vendor_id']."', '".(int)$data['movie_id']."', '".(int)$data['media_type']."', '".$data['release_date']."', '".$db->escape($data['desc'])."', '".(int)$data['ordering']."')");
		} else {
			$db->setQuery("UPDATE ".$db->quoteName('#__ka_releases')
				. "\n SET `country_id` = '".$data['country_id']."', `vendor_id` = '".(int)$data['vendor_id']."', `movie_id` = '".(int)$data['movie_id']."', `media_type` = '".(int)$data['media_type']."', `release_date` = '".$data['release_date']."', `desc` = '".$db->escape($data['desc'])."', `ordering` = '".(int)$data['ordering']."'"
				. "\n WHERE `id` = ".(int)$id);
		}

		try {
			$db->execute();
			if (empty($id)) {
				$app->setUserState('com_kinoarhiv.releases.data.'.$user->id.'.id', $db->insertid());
			} else {
				$app->setUserState('com_kinoarhiv.releases.data.'.$user->id.'.id', $id);
			}
		} catch(Exception $e) {
			$this->setError($e->getMessage());
			return false;
		}

		return true;
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
		$db->lockTable('#__ka_releases');
		$db->transactionStart();

		foreach ($data as $key=>$value) {
			$db->setQuery("UPDATE ".$db->quoteName('#__ka_releases')." SET `ordering` = '".(int)$key."' WHERE `id` = ".(int)$value." AND `movie_id` = ".(int)$movie_id.";");
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

		$db->setQuery("DELETE FROM ".$db->quoteName('#__ka_releases')." WHERE `id` IN (".implode(',', $ids).")");

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
