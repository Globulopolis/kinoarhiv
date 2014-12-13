<?php defined('_JEXEC') or die;
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url			http://киноархив.com/
 */

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
