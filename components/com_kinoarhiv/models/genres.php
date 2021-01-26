<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2018 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Class KinoarhivModelGenres
 *
 * @since  3.0
 */
class KinoarhivModelGenres extends JModelList
{
	/**
	 * Context string for the model type.  This is used to handle uniqueness
	 * when dealing with the getStoreId() method and caching data structures.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $context = 'com_kinoarhiv.genres';

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		parent::populateState($ordering, $direction);

		$app    = JFactory::getApplication();
		$params = new Registry;

		if ($menu = $app->getMenu()->getActive())
		{
			$params->loadString($menu->getParams());
		}

		$this->setState('params', $params);
		$this->setState('list.start', 0);
		$this->setState('list.limit', 0);
	}

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set from a database.
	 *
	 * @return  JDatabaseQuery   A JDatabaseQuery object to retrieve the data set.
	 *
	 * @since   3.0
	 */
	protected function getListQuery()
	{
		$db     = $this->getDbo();
		$user   = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$query  = $db->getQuery(true);

		$query->select($this->getState('list.select', $db->quoteName(array('id', 'name', 'alias', 'desc', 'type', 'stats'))))
			->from($db->quoteName('#__ka_genres'))
			->where($db->quoteName('state') . ' = 1')
			->where($db->quoteName('access') . ' IN (' . $groups . ')')
			->where($db->quoteName('language') . ' IN (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');

		if ((int) $this->state->get('params')->get('genres_type') !== -1)
		{
			$query->where($db->quoteName('type') . ' = ' . (int) $this->state->get('params')->get('genres_type'));
		}

		$query->order($db->quoteName('name') . ' ASC');

		return $query;
	}
}
