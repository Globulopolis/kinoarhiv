<?php defined('_JEXEC') or die;

class KinoarhivModelReview extends JModelForm {
	public function getForm($data = array(), $loadData = true) {
		$form = $this->loadForm('com_kinoarhiv.review', 'review', array('control' => 'form', 'load_data' => $loadData));

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
		$task = $app->input->get('task', '', 'cmd');
		$id = $app->input->get('id', array(), 'array');

		$db->setQuery("SELECT `id`, `uid`, `movie_id`, `review`, `created`, `type`, `ip`, `state`"
			. "\n FROM ".$db->quoteName('#__ka_reviews')
			. "\n WHERE `id` = ".(int)$id[0]);
		$result = $db->loadObject();

		return $result;
	}

	public function save($data) {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$id = $app->input->post->get('id', null, 'int');

		$db->setQuery("UPDATE ".$db->quoteName('#__ka_reviews')
			. "\n SET `uid` = '".(int)$data['uid']."', `movie_id` = '".(int)$data['movie_id']."', `review` = '".$db->escape($data['review'])."', `created` = '".$data['created']."', `type` = '".(int)$data['type']."', `ip` = '".(string)$data['ip']."', `state` = '".(int)$data['state']."'"
			. "\n WHERE `id` = ".(int)$id);

		try {
			$db->execute();

			return true;
		} catch(Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}
	}
}
