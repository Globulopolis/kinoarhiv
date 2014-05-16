<?php defined('_JEXEC') or die;

class KinoarhivModelAwards extends JModelList {
	protected function getListQuery() {
		$db = $this->getDBO();
		$query = $db->getQuery(true);

		$query->select('`id`, `title`, `desc`')
			->from($db->quoteName('#__ka_awards'))
			->where('`state` = 1');

		return $query;
	}

	public function getItem() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$id = $app->input->get('id', null, 'int');
		$query = $db->getQuery(true);

		$query->select('`id`, `title`, `desc`')
			->from($db->quoteName('#__ka_awards'))
			->where('`id` = '.(int)$id.' AND `state` = 1');
		$db->setQuery($query);

		try {
			$result = $db->loadObject();
		} catch (Exception $e) {
			$this->setError($e->getMessage());
			GlobalHelper::eventLog($e->getMessage());

			return false;
		}

		return $result;
	}
}
