<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2018 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

/**
 * Class KinoarhivModelNames
 *
 * @since  3.0
 */
class KinoarhivModelNames extends JModelList
{
	/**
	 * Context string for the model type.  This is used to handle uniqueness
	 * when dealing with the getStoreId() method and caching data structures.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $context = 'com_kinoarhiv.names';

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
			$config['filter_fields'] = array(
				'id', 'a.id',
				'name', 'a.name',
				'latin_name', 'a.latin_name',
				'alias', 'a.alias',
				'state', 'a.state',
				'access', 'a.access', 'access_level',
				'ordering', 'a.ordering',
				'language', 'a.language',
				'published');
		}

		parent::__construct($config);
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
		$app = JFactory::getApplication();

		// Adjust the context to support modal layouts.
		if ($layout = $app->input->get('layout'))
		{
			$this->context .= '.' . $layout;
		}

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access');
		$this->setState('filter.access', $access);

		$language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		// Force a language
		$forcedLanguage = $app->input->get('forcedLanguage');

		if (!empty($forcedLanguage))
		{
			$this->setState('filter.language', $forcedLanguage);
			$this->setState('filter.forcedLanguage', $forcedLanguage);
		}

		// List state information.
		parent::populateState('a.id', 'DESC');
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
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.access');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.language');

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
		$query = $db->getQuery(true);

		$query->select(
			$this->getState(
				'list.select',
				$db->quoteName(
					array(
						'a.id', 'a.name', 'a.latin_name', 'a.date_of_birth', 'a.date_of_death', 'a.birthplace',
						'a.birthcountry', 'a.alias', 'a.state', 'a.access', 'a.ordering', 'a.language'
					)
				)
			)
		);
		$query->from($db->quoteName('#__ka_names', 'a'));

		// Join over the language
		$query->select($db->quoteName('l.title', 'language_title'))
			->join('LEFT', $db->quoteName('#__languages', 'l') . ' ON ' . $db->quoteName('l.lang_code') . ' = ' . $db->quoteName('a.language'));

		// Join over the asset groups.
		$query->select($db->quoteName('ag.title', 'access_level'))
			->join('LEFT', $db->quoteName('#__viewlevels', 'ag') . ' ON ' . $db->quoteName('ag.id') . ' = ' . $db->quoteName('a.access'));

		// Filter by access level.
		if ($access = $this->getState('filter.access'))
		{
			$query->where('a.access = ' . (int) $access);
		}

		// Implement View Level Access
		if (!$user->authorise('core.admin'))
		{
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN (' . $groups . ')');
		}

		// Filter by published state
		$published = $this->getState('filter.published');

		if (is_numeric($published))
		{
			$query->where('a.state = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.state = 0 OR a.state = 1)');
		}

		// Filter by search in title.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			elseif (stripos($search, 'access:') === 0)
			{
				$search = trim(substr($search, 7));

				if (is_numeric($search))
				{
					$query->where('a.access = ' . (int) $search);
				}
				else
				{
					$search = $db->quote('%' . $db->escape(trim($search), true) . '%');
					$query->where('ag.title LIKE ' . $search);
				}
			}
			else
			{
				$search = $db->quote('%' . $db->escape(trim($search), true) . '%');
				$query->where('(a.name LIKE ' . $search . ' OR a.latin_name LIKE ' . $search . ' OR a.alias LIKE ' . $search . ')');
			}
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering', 'a.id');
		$orderDirn = $this->state->get('list.direction', 'desc');

		// SQL server change
		if ($orderCol == 'language')
		{
			$orderCol = 'l.title';
		}

		if ($orderCol == 'access_level')
		{
			$orderCol = 'ag.title';
		}

		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		return $query;
	}

	/**
	 * Method to get a list of articles.
	 * Overridden to add a check for access levels.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.6.1
	 */
	public function getItems()
	{
		$items = parent::getItems();

		if (JFactory::getApplication()->isClient('site'))
		{
			$user = JFactory::getUser();
			$groups = $user->getAuthorisedViewLevels();

			for ($x = 0, $count = count($items); $x < $count; $x++)
			{
				// Check the access level. Remove articles the user shouldn't see
				if (!in_array($items[$x]->access, $groups))
				{
					unset($items[$x]);
				}
			}
		}

		return $items;
	}

	/**
	 * Saves the manually set order of records.
	 *
	 * @return  array
	 *
	 * @since   3.0
	 */
	public function saveOrder()
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$data = $app->input->post->get('ord', array(), 'array');

		if (count($data) < 2)
		{
			return array('success' => false, 'message' => JText::_('COM_KA_SAVE_ORDER_AT_LEAST_TWO'));
		}

		$queryResult = true;
		$db->lockTable('#__ka_names');
		$db->transactionStart();

		foreach ($data as $key => $value)
		{
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__ka_names'))
				->set($db->quoteName('ordering') . " = '" . (int) $key . "'")
				->where($db->quoteName('id') . ' = ' . (int) $value);

			$db->setQuery($query . ';');

			if ($db->execute() === false)
			{
				$queryResult = false;
				break;
			}
		}

		if ($queryResult === false)
		{
			$db->transactionRollback();
		}
		else
		{
			$db->transactionCommit();
		}

		$db->unlockTables();

		if ($queryResult)
		{
			$success = true;
			$message = JText::_('COM_KA_SAVED');
		}
		else
		{
			$success = false;
			$message = JText::_('COM_KA_SAVE_ORDER_ERROR');
		}

		return array('success' => $success, 'message' => $message);
	}

	/**
	 * Method to perform batch operations on an item or a set of items.
	 *
	 * @return  boolean  Returns true on success, false on failure.
	 *
	 * @since   3.0
	 */
	public function batch()
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$ids = $app->input->post->get('id', array(), 'array');
		$batchData = $app->input->post->get('batch', array(), 'array');

		if (empty($batchData))
		{
			return false;
		}

		$fields = array();

		if (!empty($batchData['language_id']))
		{
			$fields[] = $db->quoteName('language') . " = '" . $db->escape((string) $batchData['language_id']) . "'";
		}

		if (!empty($batchData['assetgroup_id']))
		{
			$fields[] = $db->quoteName('access') . " = '" . (int) $batchData['assetgroup_id'] . "'";
		}

		if (empty($fields))
		{
			return false;
		}

		$query = $db->getQuery(true);

		$query->update($db->quoteName('#__ka_names'))
			->set(implode(', ', $fields))
			->where($db->quoteName('id') . ' IN (' . implode(',', $ids) . ')');

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return true;
	}
}
