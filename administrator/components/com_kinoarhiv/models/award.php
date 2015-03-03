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
		return $this->getItems();
	}

	public function getItems() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();

		$_id = $app->input->get('id', array(), 'array');
		$id = !empty($_id) ? $_id[0] : $app->input->get('id', null, 'int');

		$db->setQuery("SELECT `id`, `title`, `desc`, `language`, `state`"
			. "\n FROM ".$db->quoteName('#__ka_awards')
			. "\n WHERE `id` = ".(int)$id);
		$result = $db->loadObject();

		return $result;
	}

	public function publish($isUnpublish) {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$ids = $app->input->get('id', array(), 'array');
		$state = $isUnpublish ? 0 : 1;

		$db->setQuery("UPDATE ".$db->quoteName('#__ka_awards')." SET `state` = '".(int)$state."' WHERE `id` IN (".implode(',', $ids).")");

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

		$db->setQuery("DELETE FROM ".$db->quoteName('#__ka_awards')." WHERE `id` IN (".implode(',', $ids).")");

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

		if (empty($id)) {
			$db->setQuery("INSERT INTO ".$db->quoteName('#__ka_awards')." (`id`, `title`, `desc`, `state`, `language`)"
				. "\n VALUES ('', '".$data['title']."', '".$db->escape($data['desc'])."', '".$data['state']."', '".$data['language']."')");
		} else {
			$db->setQuery("UPDATE ".$db->quoteName('#__ka_awards')
				. "\n SET `title` = '".$data['title']."', `desc` = '".$db->escape($data['desc'])."', `state` = '".$data['state']."', `language` = '".$data['language']."'"
				. "\n WHERE `id` = ".(int)$id);
		}

		try {
			$db->execute();

			if (empty($id)) {
				$insertid = $db->insertid();
				$app->setUserState('com_kinoarhiv.awards.data.'.$user->id.'.id', $insertid);
			} else {
				$app->setUserState('com_kinoarhiv.awards.data.'.$user->id.'.id', $id);
			}

			return true;
		} catch(Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}
	}

	public function quickSave() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();

		// We need set alias for quick save on movie page
		$title = 'a_title';
		$desc = 'a_desc';
		$state = 'a_state';
		$language = 'a_language';

		$data = $app->input->getArray(array(
			'form'=>array(
				$title=>'string', $desc=>'string', $state=>'string', $language=>'string'
			)
		));
		$title = $data['form'][$title];
		$desc = $data['form'][$desc];
		$state = empty($data['form'][$state]) ? 0 : $data['form'][$state];
		$language = empty($data['form'][$language]) ? '*' : $data['form'][$language];

		if (empty($title)) {
			return array('success'=>false, 'message'=>JText::_('COM_KA_REQUIRED'));
		}

		$db->setQuery("INSERT INTO ".$db->quoteName('#__ka_awards')." (`id`, `title`, `desc`, `state`, `language`)"
			. "\n VALUES ('', '".$db->escape($title)."', '".$db->escape($desc)."', '".$state."', '".$language."')");
		$query = $db->execute();

		if ($query !== true) {
			return array('success'=>false, 'message'=>JText::_('JERROR_AN_ERROR_HAS_OCCURRED'));
		} else {
			$insertid = $db->insertid();

			return array(
				'success'	=> true,
				'message'	=> JText::_('COM_KA_ITEMS_SAVE_SUCCESS'),
				'data'		=> array('id'=>$insertid, 'title'=>$title)
			);
		}
	}
}
