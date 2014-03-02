<?php defined('_JEXEC') or die;

class KinoarhivModelVendor extends JModelForm {
	public function getForm($data = array(), $loadData = true) {
		$form = $this->loadForm('com_kinoarhiv.vendor', 'vendor', array('control' => 'form', 'load_data' => $loadData));

		if (empty($form)) {
			return false;
		}

		return $form;
	}

	protected function loadFormData() {
		return $this->getItems();
	}

	public function getItems() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$task = $app->input->get('task', '', 'cmd');

		$_id = $app->input->get('id', array(), 'array');
		$id = !empty($_id) ? $_id[0] : $app->input->get('id', null, 'int');

		$db->setQuery("SELECT `id`, `company_name`, `company_name_intl`, `company_name_alias`, `description`, `language`, `state`"
			. "\n FROM ".$db->quoteName('#__ka_vendors')
			. "\n WHERE `id` = ".(int)$id);
		$result = $db->loadObject();

		return $result;
	}

	public function publish($isUnpublish) {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$ids = $app->input->get('id', array(), 'array');
		$state = $isUnpublish ? 0 : 1;

		$db->setQuery("UPDATE ".$db->quoteName('#__ka_vendors')." SET `state` = '".(int)$state."' WHERE `id` IN (".implode(',', $ids).")");

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

		$db->setQuery("DELETE FROM ".$db->quoteName('#__ka_vendors')." WHERE `id` IN (".implode(',', $ids).")");

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
		$id = $app->input->post->get('id', null, 'int');

		if (empty($data['company_name'])) {
			return false;
		}

		if (empty($data['company_name_alias'])) {
			$data['company_name_alias'] = JFilterOutput::stringURLSafe($data['company_name']);
		}

		if (empty($id)) {
			$db->setQuery("INSERT INTO ".$db->quoteName('#__ka_vendors')." (`id`, `company_name`, `company_name_intl`, `company_name_alias`, `description`, `language`, `state`)"
				. "\n VALUES ('', '".$data['company_name']."', '".$data['company_name_intl']."', '".JFilterOutput::stringURLSafe($data['company_name_alias'])."', '".$db->escape($data['description'])."', '".$data['language']."', '".$data['state']."')");
		} else {
			$db->setQuery("UPDATE ".$db->quoteName('#__ka_vendors')
				. "\n SET `company_name` = '".$data['company_name']."', `company_name_intl` = '".$data['company_name_intl']."', `company_name_alias` = '".JFilterOutput::stringURLSafe($data['company_name_alias'])."', `description` = '".$db->escape($data['description'])."', `language` = '".$data['language']."', `state` = '".$data['state']."'"
				. "\n WHERE `id` = ".(int)$id);
		}

		try {
			$db->execute();

			return true;
		} catch(Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}
	}
}
