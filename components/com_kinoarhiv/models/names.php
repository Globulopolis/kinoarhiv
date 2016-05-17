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

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Persons list class
 *
 * @since  3.0
 */
class KinoarhivModelNames extends JModelList
{
	protected $context = null;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JModelLegacy
	 * @since   3.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			// Setup a list of columns for ORDER BY from 'sort_namelist_field' params from component settings
			$config['filter_fields'] = array('id', 'n.id', 'name', 'n.name', 'latin_name', 'n.latin_name', 'ordering', 'n.ordering');
		}

		parent::__construct($config);

		if (empty($this->context))
		{
			$this->context = strtolower('com_kinoarhiv.names');
		}
	}

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
		if ($this->context)
		{
			$app = JFactory::getApplication();
			$params = JComponentHelper::getParams('com_kinoarhiv');

			$value = $app->getUserStateFromRequest($this->context . '.list.limit', 'limit', $params->get('list_limit'), 'uint');
			$limit = $value;
			$this->setState('list.limit', $value);

			$value = $app->getUserStateFromRequest($this->context . '.limitstart', 'limitstart', 0);
			$limitstart = ($limit != 0 ? (floor($value / $limit) * $limit) : 0);
			$this->setState('list.start', $limitstart);

			$value = $app->getUserStateFromRequest($this->context . '.ordercol', 'filter_order', $params->get('sort_namelist_field'));

			if (!in_array($value, $this->filter_fields))
			{
				$value = $ordering;
				$app->setUserState($this->context . '.ordercol', $value);
			}

			$this->setState('list.ordering', $value);

			$value = $app->getUserStateFromRequest($this->context . '.orderdirn', 'filter_order_Dir', strtoupper($params->get('sort_namelist_ord')));

			if (!in_array(strtoupper($value), array('ASC', 'DESC', '')))
			{
				$value = $direction;
				$app->setUserState($this->context . '.orderdirn', $value);
			}

			$this->setState('list.direction', $value);
		}
		else
		{
			$this->setState('list.start', 0);
			$this->state->set('list.limit', 0);
		}
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
		$id .= ':' . $this->getState('filter.name');
		$id .= ':' . $this->getState('list.limit');
		$id .= ':' . $this->getState('list.ordering');
		$id .= ':' . $this->getState('list.direction');

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
		$db = $this->getDbo();
		$user = JFactory::getUser();
		$lang = JFactory::getLanguage();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$where_id = array();

		$query = $db->getQuery(true);

		$query->select(
			$this->getState(
				'list.select',
				"n.id, n.name, n.latin_name, n.alias, n.fs_alias, DATE_FORMAT(n.date_of_birth, '%Y') AS date_of_birth, " .
				"DATE_FORMAT(n.date_of_death, '%Y') AS date_of_death, n.birthplace, n.gender, n.attribs, " .
				"cn.name AS country, cn.code, GROUP_CONCAT(DISTINCT g.name SEPARATOR ', ') AS genres, " .
				"GROUP_CONCAT(DISTINCT cr.title SEPARATOR ', ') AS career"
			)
		)
		->from($db->quoteName('#__ka_names', 'n'))
		->join('LEFT', $db->quoteName('#__ka_countries', 'cn') . ' ON cn.id = n.birthcountry AND cn.language IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ') AND cn.state = 1');

		// Join over gallery item
		$query->select($db->quoteName(array('gal.filename', 'gal.dimension')))
			->join('LEFT', $db->quoteName('#__ka_names_gallery', 'gal') . ' ON gal.name_id = n.id AND gal.type = 3 AND gal.photo_frontpage = 1 AND gal.state = 1');

		$query->join('LEFT', $db->quoteName('#__ka_genres', 'g') . ' ON g.id IN (SELECT genre_id FROM ' . $db->quoteName('#__ka_rel_names_genres') . ' WHERE name_id = n.id)')
		->join('LEFT', $db->quoteName('#__ka_names_career', 'cr') . ' ON cr.id IN (SELECT career_id FROM ' . $db->quoteName('#__ka_rel_names_career') . ' WHERE name_id = n.id)');

		if (!$user->get('guest'))
		{
			$query->select($db->quoteName('u.favorite'));
			$query->leftJoin($db->quoteName('#__ka_user_marked_names', 'u') . ' ON u.uid = ' . $user->get('id') . ' AND u.name_id = n.id');
		}

		$query->where('n.state = 1 AND n.language IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ') AND n.access IN (' . $groups . ')');

		if ($app->input->get('task', '', 'cmd') == 'search' && KAComponentHelper::checkToken() === true)
		{
			$searches = $this->getFiltersData();

			// Filter by name
			$name = $searches->get('filters.names.name');

			if ($params->get('search_names_name') == 1 && !empty($name))
			{
				$query->where("(n.name LIKE '%" . $db->escape($name) . "%' OR n.latin_name LIKE '%" . $db->escape($name) . "%')");
			}

			// Filter by birthday
			$birthday = $searches->get('filters.names.birthday');

			if ($params->get('search_names_birthday') == 1 && !empty($birthday))
			{
				$query->where("n.date_of_birth LIKE '%" . $db->escape($birthday) . "%'");
			}

			// Filter by gender
			$gender = $searches->get('filters.names.gender');

			if ($params->get('search_names_gender') == 1 && ($gender === 0 || $gender === 1))
			{
				$query->where("n.gender = " . (int) $gender);
			}

			// Filter by movie title
			$mtitle = $searches->get('filters.names.mtitle');

			if ($params->get('search_names_mtitle') == 1 && !empty($mtitle))
			{
				$subquery_title = $db->getQuery(true)
					->select('name_id')
					->from($db->quoteName('#__ka_rel_names'))
					->where('movie_id = ' . (int) $mtitle)
					->group('name_id');

				$db->setQuery($subquery_title);
				$name_ids = $db->loadColumn();

				$where_id = (!empty($name_ids)) ? array_merge($where_id, $name_ids) : array(0);
			}

			// Filter by birthplace
			$birthplace = trim($searches->get('filters.names.birthplace'));

			if ($params->get('search_names_birthplace') == 1 && !empty($birthplace))
			{
				$query->where("n.birthplace LIKE '%" . $db->escape($birthplace) . "%'");
			}

			// Filter by country
			$country = $searches->get('filters.names.birthcountry');

			if ($params->get('search_names_birthcountry') == 1 && !empty($country))
			{
				$query->where("n.birthcountry = " . (int) $country);
			}

			// Filter by amplua
			$amplua = $searches->get('filters.names.amplua');

			if ($params->get('search_names_amplua') == 1 && !empty($amplua))
			{
				$subquery_amplua = $db->getQuery(true)
					->select('name_id')
					->from($db->quoteName('#__ka_rel_names_career'))
					->where('career_id = ' . (int) $amplua)
					->group('name_id');

				$db->setQuery($subquery_amplua);
				$name_ids = $db->loadColumn();

				$where_id = (!empty($name_ids)) ? array_merge($where_id, $name_ids) : array(0);
			}

			if ((!empty($mtitle) || !empty($amplua)) && !empty($where_id))
			{
				// Remove 0 in array
				$ids_keys = array_keys($where_id, 0);

				foreach ($ids_keys as $k)
				{
					unset($where_id[$k]);
				}

				$query->where("n.id IN (" . implode(',', ArrayHelper::arrayUnique($where_id)) . ")");
			}
		}

		$query->group($db->quoteName('n.id'));

		$orderCol = $this->state->get('list.ordering', $db->quoteName('n.ordering'));
		$orderDirn = $this->state->get('list.direction', 'DESC');
		$query->order($db->escape('n.' . $orderCol . ' ' . $orderDirn));

		return $query;
	}

	/**
	 * Get the values from search inputs
	 *
	 * @return   object
	 */
	public function getFiltersData()
	{
		jimport('models.search', JPATH_COMPONENT);

		$search_model = new KinoarhivModelSearch;

		return $search_model->getActiveFilters();
	}

	/**
	 * Method to add a person into favorites
	 *
	 * @return array
	 *
	 * @throws Exception
	 */
	public function favorite()
	{
		$db = $this->getDbo();
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		$action = $app->input->get('action', '', 'cmd');
		$name_id = $app->input->get('id', 0, 'int');
		$name_ids = $app->input->get('ids', array(), 'array');
		$total = '';

		if (!empty($name_ids))
		{
			JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		}

		$itemid = $app->input->get('Itemid', 0, 'int');
		$success = false;
		$url = '';
		$text = '';

		if (empty($name_ids))
		{
			$query_total = $db->getQuery(true)
				->select('favorite')
				->from($db->quoteName('#__ka_user_marked_names'))
				->where('uid = ' . (int) $user->get('id') . ' AND name_id = ' . (int) $name_id);

			$db->setQuery($query_total);
			$total = $db->loadResult();
		}

		if ($action == 'add')
		{
			if ($total == 1)
			{
				$message = JText::_('COM_KA_FAVORITE_ERROR');
			}
			else
			{
				if (is_null($total))
				{
					$query = $db->getQuery(true)
						->insert($db->quoteName('#__ka_user_marked_names'))
						->columns('uid, name_id, favorite')
						->values("'" . $user->get('id') . "', '" . (int) $name_id . "', '1'");

					$db->setQuery($query);
				}
				elseif ($total == 0)
				{
					$query = $db->getQuery(true)
						->update($db->quoteName('#__ka_user_marked_names'))
						->set("favorite = '1'")
						->where('uid = ' . $user->get('id') . ' AND name_id = ' . (int) $name_id);

					$db->setQuery($query);
				}

				if ($db->execute())
				{
					$success = true;
					$message = JText::_('COM_KA_FAVORITE_ADDED');
					$url = JRoute::_('index.php?option=com_kinoarhiv&task=favorite&view=names&action=delete&Itemid=' . $itemid . '&id=' . $name_id, false);
					$text = JText::_('COM_KA_REMOVEFROM_FAVORITE');
				}
				else
				{
					$message = JText::_('JERROR_ERROR');
				}
			}
		}
		elseif ($action == 'delete')
		{
			if ($total == 1)
			{
				$query = $db->getQuery(true)
					->update($db->quoteName('#__ka_user_marked_names'))
					->set("favorite = '0'")
					->where('uid = ' . $user->get('id') . ' AND name_id = ' . (int) $name_id);

				$db->setQuery($query);

				if ($db->execute())
				{
					$success = true;
					$message = JText::_('COM_KA_FAVORITE_REMOVED');
					$url = JRoute::_('index.php?option=com_kinoarhiv&task=favorite&view=names&action=add&Itemid=' . $itemid . '&id=' . $name_id, false);
					$text = JText::_('COM_KA_ADDTO_FAVORITE');
				}
				else
				{
					$message = JText::_('JERROR_ERROR');
				}
			}
			else
			{
				if (!empty($name_ids))
				{
					$query_result = true;
					$db->setDebug(true);
					$db->lockTable('#__ka_user_marked_names');
					$db->transactionStart();

					foreach ($name_ids as $id)
					{
						$query = $db->getQuery(true);

						$query->update($db->quoteName('#__ka_user_marked_names'))
							->set("favorite = '0'")
							->where('uid = ' . $user->get('id') . ' AND name_id = ' . (int) $id);

						$db->setQuery($query . ';');

						if ($db->execute() === false)
						{
							$query_result = false;
							break;
						}
					}

					if ($query_result === true)
					{
						$db->transactionCommit();

						$success = true;
						$message = JText::_('COM_KA_FAVORITE_REMOVED');
						$url = JRoute::_('index.php?option=com_kinoarhiv&task=favorite&action=add&Itemid=' . $itemid . '&id=' . $name_id, false);
						$text = JText::_('COM_KA_ADDTO_FAVORITE');
					}
					else
					{
						$db->transactionRollback();

						$message = JText::_('JERROR_ERROR');
					}

					$db->unlockTables();
					$db->setDebug(false);
				}
				else
				{
					$message = JText::_('JERROR_AN_ERROR_HAS_OCCURRED');
				}
			}
		}
		else
		{
			$message = JText::_('JERROR_AN_ERROR_HAS_OCCURRED');
		}

		return array('success' => $success, 'message' => $message, 'url' => $url, 'text' => $text);
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
