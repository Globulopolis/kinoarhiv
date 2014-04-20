<?php defined('_JEXEC') or die;

class KinoarhivModelCareer extends JModelForm {
	public function getForm($data = array(), $loadData = true) {
		$form = $this->loadForm('com_kinoarhiv.career', 'career', array('control' => 'form', 'load_data' => $loadData));

		if (empty($form)) {
			return false;
		}

		$input = JFactory::getApplication()->input;
		$ids = $input->get('id', array(), 'array');
		$id = (isset($id[0]) && !empty($id[0])) ? $id[0] : 0;
		$user = JFactory::getUser();

		if ($id != 0 && (!$user->authorise('core.edit.state', 'com_kinoarhiv.career.' . (int) $id)) || ($id == 0 && !$user->authorise('core.edit.state', 'com_kinoarhiv'))) {
			$form->setFieldAttribute('ordering', 'disabled', 'true');
		}

		return $form;
	}

	protected function loadFormData() {
		$app = JFactory::getApplication();
		$data = $app->getUserState('com_kinoarhiv.edit.career.data', array());

		if (empty($data)) {
			$data = $this->getItems();
		}

		return $data;
	}

	public function getItems() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$task = $app->input->get('task', '', 'cmd');

		$_id = $app->input->get('id', array(), 'array');
		$id = !empty($_id) ? $_id[0] : $app->input->get('id', null, 'int');

		$db->setQuery("SELECT `id`, `title`, `is_mainpage`, `is_amplua`, `ordering`, `language`"
			. "\n FROM ".$db->quoteName('#__ka_names_career')
			. "\n WHERE `id` = ".(int)$id);
		$result = $db->loadObject();

		return $result;
	}

	public function remove() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$ids = $app->input->get('id', array(), 'array');

		$db->setQuery("DELETE FROM ".$db->quoteName('#__ka_names_career')." WHERE `id` IN (".implode(',', $ids).")");

		try {
			$db->execute();

			return true;
		} catch(Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}
	}

	public function save($alias=0) {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$ids = $app->input->get('id', array(), 'array');
		$id = isset($ids[0]) ? $ids[0] : 0;

		// We need set alias for quick save on movie page
		if ($alias == 1) {
			$title = 'c_title';
			$is_mainpage = 'c_is_mainpage';
			$is_amplua = 'c_is_amplua';
			$ordering = 'c_ordering';
			$language = 'c_language';
		} else {
			$title = 'title';
			$is_mainpage = 'is_mainpage';
			$is_amplua = 'is_amplua';
			$ordering = 'ordering';
			$language = 'language';
		}

		$data = $app->input->getArray(array(
			'form'=>array(
				$title=>'string', $ordering=>'int', $language=>'string', $is_mainpage=>'int', $is_amplua=>'int'
			)
		));
		$title = $data['form'][$title];
		$is_mainpage = empty($data['form'][$is_mainpage]) ? 0 : $data['form'][$is_mainpage];
		$is_amplua = empty($data['form'][$is_amplua]) ? 0 : $data['form'][$is_amplua];
		$ordering = empty($data['form'][$ordering]) ? 0 : $data['form'][$ordering];
		$language = empty($data['form'][$language]) ? '*' : $data['form'][$language];

		if (empty($title)) {
			return array('success'=>false, 'message'=>JText::_('COM_KA_REQUIRED'));
		}

		if (empty($id)) {
			$db->setQuery("INSERT INTO ".$db->quoteName('#__ka_names_career')." (`id`, `title`, `is_mainpage`, `is_amplua`, `ordering`, `language`)"
				. "\n VALUES ('', '".$db->escape($title)."', '".(int)$is_mainpage."', '".(int)$is_amplua."', '".(int)$ordering."', '".$language."')");
			$query = $db->execute();
		} else {
			$db->setQuery("UPDATE ".$db->quoteName('#__ka_names_career')." SET `title` = '".$db->escape($title)."', `is_mainpage` = '".(int)$is_mainpage."', `is_amplua` = '".(int)$is_amplua."', `ordering` = '".(int)$ordering."', `language` = '".$language."'"
				. "\n WHERE `id` = ".(int)$id);
			$query = $db->execute();
		}

		if ($query !== true) {
			return array('success'=>false, 'message'=>JText::_('JERROR_AN_ERROR_HAS_OCCURRED'));
		} else {
			return array(
				'success'	=> true,
				'message'	=> JText::_('COM_KA_ITEMS_SAVE_SUCCESS'),
				'data'		=> array('id'=>$db->insertid(), 'title'=>$title)
			);
		}
	}
}
