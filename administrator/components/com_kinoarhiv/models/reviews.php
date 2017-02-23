<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('_JEXEC') or die;

/**
 * Class KinoarhivModelReviews
 *
 * @since  3.0
 */
class KinoarhivModelReviews extends JModelList
{
	protected $context = 'com_kinoarhiv.reviews';

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
				'username', 'u.username',
				'title', 'm.title',
				'state', 'a.state',
				'published', 'author_id',
				'type', 'a.type',
				'ip', 'a.ip',
				'created', 'a.created');
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

		$author_id = $this->getUserStateFromRequest($this->context . '.filter.author_id', 'filter_author_id', '');
		$this->setState('filter.author_id', $author_id);

		$type = $this->getUserStateFromRequest($this->context . '.filter.type', 'filter_type', '');
		$this->setState('filter.type', $type);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		// List state information.
		parent::populateState('a.created', 'desc');
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
		$id .= ':' . $this->getState('filter.author_id');
		$id .= ':' . $this->getState('filter.type');
		$id .= ':' . $this->getState('filter.published');

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
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$uid = $app->input->get('uid', 0, 'int');
		$mid = $app->input->get('mid', 0, 'int');

		$query->select(
			$this->getState(
				'list.select',
				$db->quoteName(array('a.id', 'a.uid', 'a.movie_id', 'a.review', 'a.created', 'a.type', 'a.ip', 'a.state'))
			)
		);
		$query->from($db->quoteName('#__ka_reviews', 'a'));

		$query->select($db->quoteName('u.name', 'username'))
			->join('LEFT', $db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('a.uid'));

		$query->select($db->quoteName('m.title', 'movie'))
			->join('LEFT', $db->quoteName('#__ka_movies', 'm') . ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('a.movie_id'));

		// Filter by author ID
		$author_id = $this->getState('filter.author_id');

		if (is_numeric($author_id))
		{
			$query->where('a.uid = ' . (int) $author_id);
		}

		// Filter by type
		$type = $this->getState('filter.type');

		if (is_numeric($type))
		{
			$query->where('a.type = ' . (int) $type);
		}
		elseif ($type === '')
		{
			$query->where('(a.type = 0 OR a.type = 1 OR a.type = 2 OR a.type = 3)');
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

		// Filter by search string.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			elseif (stripos($search, 'movie:') === 0)
			{
				$search = $db->quote('%' . $db->escape(trim(substr($search, 6)), true) . '%');
				$query->where('m.title LIKE ' . $search);
			}
			elseif (stripos($search, 'user:') === 0)
			{
				$search = $db->quote('%' . $db->escape(trim(substr($search, 5)), true) . '%');
				$query->where('u.username LIKE ' . $search);
			}
			elseif (stripos($search, 'ip:') === 0)
			{
				$search = $db->quote('%' . $db->escape(trim(substr($search, 3)), true) . '%');
				$query->where('a.ip LIKE ' . $search);
			}
			elseif (stripos($search, 'type:') === 0)
			{
				$query->where('a.type = ' . (int) substr($search, 5));
			}
			elseif (stripos($search, 'date:') === 0)
			{
				$search = $db->quote('%' . $db->escape(trim(substr($search, 5)), true) . '%');
				$query->where('a.created LIKE ' . $search);
			}
			else
			{
				$search = $db->quote('%' . $db->escape($search, true) . '%');
				$query->where('(a.review LIKE ' . $search . ')');
			}
		}

		if (!empty($uid) && is_numeric($uid))
		{
			$query->where('u.id = ' . (int) $uid);
		}

		if (!empty($mid) && is_numeric($mid))
		{
			$query->where('m.id = ' . (int) $mid);
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering', 'a.created');
		$orderDirn = $this->state->get('list.direction', 'desc');

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

		if (JFactory::getApplication()->isSite())
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
		$batch_data = $app->input->post->get('batch', array(), 'array');

		if (empty($batch_data))
		{
			return false;
		}

		$fields = array();

		if (!empty($batch_data['type']))
		{
			$fields[] = $db->quoteName('type') . " = '" . (int) $batch_data['type'] . "'";
		}

		if (!empty($batch_data['user_id']))
		{
			$fields[] = $db->quoteName('uid') . " = '" . (int) $batch_data['user_id'] . "'";
		}

		if (empty($fields))
		{
			return false;
		}

		$query = $db->getQuery(true);

		$query->update($db->quoteName('#__ka_reviews'))
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
