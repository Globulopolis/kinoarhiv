<?php defined('_JEXEC') or die;

class KinoarhivModelSearch extends JModelForm {
	public function getForm($data = array(), $loadData = true) {
		$form = $this->loadForm('com_kinoarhiv.search', 'search', array('control' => 'form', 'load_data' => $loadData));

		if (empty($form)) {
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 * @since   1.6
	 */
	protected function loadFormData() {
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$id = $app->input->get('id', 0, 'int');
		$itemid = $app->input->get('Itemid', 0, 'int');
		$data = $app->getUserState('com_kinoarhiv.search.'.$id.'.user.'.$user->get('id'));

		if (empty($data)) {
			$data = $this->getFormData();
		} else {
			$data['Itemid'] = $itemid;
			$data['id'] = $id;
		}

		return $data;
	}

	protected function getFormData() {
		$app = JFactory::getApplication();
		$itemid = $app->input->get('Itemid', 0, 'int');
		$id = $app->input->get('id', 0, 'int');

		return array('Itemid'=>$itemid, 'id'=>$id);
	}

	protected function getListQuery() {
		$db = $this->getDBO();
		$user = JFactory::getUser();
		$groups	= implode(',', $user->getAuthorisedViewLevels());
		$app = JFactory::getApplication();

		$query = $db->getQuery(true);

		$query->select("`m`.`id`, `m`.`parent_id`, `m`.`title`, `m`.`alias`, `m`.`introtext` AS `text`, `m`.`plot`, `m`.`rate_loc`, `m`.`rate_sum_loc`, `m`.`imdb_votesum`, `m`.`imdb_votes`, `m`.`imdb_id`, `m`.`kp_votesum`, `m`.`kp_votes`, `m`.`kp_id`, `m`.`rottentm_id`, `m`.`rate_custom`, `m`.`year`, DATE_FORMAT(`m`.`created`, '%Y-%m-%d') AS `created`, DATE_FORMAT(`m`.`modified`, '%Y-%m-%d') AS `modified`, `m`.`created_by`, `m`.`attribs`, `m`.`state`, `g`.`filename`, `g`.`dimension`");
		$query->from($db->quoteName('#__ka_movies').' AS `m`');
		$query->leftJoin($db->quoteName('#__ka_movies_gallery').' AS `g` ON `g`.`movie_id` = `m`.`id` AND `g`.`type` = 2 AND `g`.`poster_frontpage` = 1 AND `g`.`state` = 1');

		if (!$user->get('guest')) {
			$query->select(' `u`.`favorite`');
			$query->leftJoin($db->quoteName('#__ka_user_marked_movies').' AS `u` ON `u`.`uid` = '.$user->get('id').' AND `u`.`movie_id` = `m`.`id`');
		}

		$query->select(' `user`.`name` AS `username`, `user`.`email` AS `author_email`');
		$query->leftJoin($db->quoteName('#__users').' AS `user` ON `user`.`id` = `m`.`created_by`');

		$where = '`m`.`state` = 1 AND `language` IN ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').') AND `parent_id` = 0 AND `m`.`access` IN ('.$groups.')';
		$query->where($where);

		$query->group($db->quoteName('m.id'));

		$orderCol = $this->state->get('list.ordering', $db->quoteName('m.ordering'));
		$orderDirn = $this->state->get('list.direction', 'DESC');
		$query->order($db->escape($orderCol.' '.$orderDirn));

		return $query;
	}
}
