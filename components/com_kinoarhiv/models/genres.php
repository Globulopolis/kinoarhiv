<?php defined('_JEXEC') or die;

class KinoarhivModelGenres extends JModelList {
	protected function getListQuery() {
		$db = $this->getDBO();
		$user = JFactory::getUser();
		$groups	= implode(',', $user->getAuthorisedViewLevels());

		$query = $db->getQuery(true);

		$query->select('`id`, `name`, `alias`, `stats`');
		$query->from($db->quoteName('#__ka_genres'));

		$query->where('`state` = 1 AND `access` IN ('.$groups.') AND `language` IN ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')');

		return $query;
	}
}
