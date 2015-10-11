<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('_JEXEC') or die;

/**
 * Class KinoarhivModelGenres
 *
 * @since  3.0
 */
class KinoarhivModelGenres extends JModelList
{
	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set from a database.
	 *
	 * @return  JDatabaseQuery   A JDatabaseQuery object to retrieve the data set.
	 *
	 * @since   3.0
	 */
	protected function getListQuery()
	{
		$db = $this->getDBO();
		$user = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());

		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('id', 'name', 'alias', 'stats')))
			->from($db->quoteName('#__ka_genres'))
			->where($db->quoteName('state') . ' = 1 AND ' . $db->quoteName('access') . ' IN (' . $groups . ') AND ' . $db->quoteName('language') . ' IN (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');

		return $query;
	}
}
