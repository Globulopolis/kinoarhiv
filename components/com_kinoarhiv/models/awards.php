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
 * Awards list class
 *
 * @since  3.0
 */
class KinoarhivModelAwards extends JModelList
{
	/**
	 * Context string for the model type.  This is used to handle uniqueness
	 * when dealing with the getStoreId() method and caching data structures.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $context = 'com_kinoarhiv.awards';

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

		$limit = $params->get('list_limit');

		// Override default limit settings and respect user selection if 'show_pagination_limit' is set to Yes.
		if ($params->get('show_pagination_limit'))
		{
			$limit = $app->getUserStateFromRequest('list.limit', 'limit', $params->get('list_limit'), 'uint');
		}

		$this->setState('list.limit', $limit);

		$limitstart = $app->input->getUInt('limitstart', 0);
		$this->setState('list.start', $limitstart);

		$this->setState('list.ordering', $params->get('orderby'));
		$this->setState('list.direction', $params->get('ordering'));
	}

	/**
	 * Method to get a store id based on the model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  An identifier string to generate the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   3.0
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('list.limit');

		return parent::getStoreId($id);
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
		$db    = $this->getDbo();
		$lang  = JFactory::getLanguage();
		$query = $db->getQuery(true);

		$query->select($this->getState('list.select', $db->quoteName(array('id', 'title', 'desc'))))
			->from($db->quoteName('#__ka_awards'))
			->where('state = 1 AND language IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ')');

		$query->order($db->quote($this->getState('list.ordering', 'title')) . ' ' . $this->getState('list.direction', 'ASC'));

		return $query;
	}

	/**
	 * Method to get a single record.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since   3.0
	 */
	public function getItem()
	{
		$app   = JFactory::getApplication();
		$db    = $this->getDbo();
		$id    = $app->input->get('id', null, 'int');
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('id', 'title', 'desc')))
			->from($db->quoteName('#__ka_awards'))
			->where($db->quoteName('id') . ' = ' . (int) $id . ' AND ' . $db->quoteName('state') . ' = 1');
		$db->setQuery($query);

		try
		{
			$result = $db->loadObject();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			KAComponentHelper::eventLog($e->getMessage());

			return false;
		}

		return $result;
	}

	/**
	 * Method to get a KAPagination object for the data set.
	 *
	 * @return  KAPagination  A KAPagination object for the data set.
	 *
	 * @since   3.0
	 */
	public function getPagination()
	{
		JLoader::register('KAPagination', JPath::clean(JPATH_COMPONENT . '/libraries/pagination.php'));

		$store = $this->getStoreId('getPagination');

		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		$limit = (int) $this->getState('list.limit') - (int) $this->getState('list.links');
		$page = new KAPagination($this->getTotal(), $this->getStart(), $limit);

		$this->cache[$store] = $page;

		return $this->cache[$store];
	}
}
