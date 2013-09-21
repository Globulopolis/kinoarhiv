<?php defined('_JEXEC') or die;

class KinoarhivModelMediamanager extends JModelList {
	public function getPath() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$section = $app->input->get('section', '', 'word');
		$type = $app->input->get('type', '', 'word');
		$id = $app->input->get('id', 0, 'int');

		
	}

	public function getListQuery() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$query = $db->getQuery(true);
		$type = $app->input->get('type', 0, 'int');

		$query->select(
			$this->getState(
				'list.select',
				'id, filename, dimension, poster_frontpage, state'
			)
		);
		$query->from('#__ka_movies_gallery');
		$query->where('type = '.$type.' AND (state = 0 OR state = 1)');

		$orderCol = $this->state->get('list.ordering', 'filename');
		$orderDirn = $this->state->get('list.direction', 'asc');
		$query->order($db->escape($orderCol . ' ' . strtoupper($orderDirn)));

		return $query;
	}
}
