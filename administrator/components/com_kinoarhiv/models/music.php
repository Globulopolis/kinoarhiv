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
 * Class KinoarhivModelMusic
 *
 * @since  3.0
 */
class KinoarhivModelMusic extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since   3.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			if (JFactory::getApplication()->input->get('type', 'albums', 'word') == 'tracks')
			{
			}
			else
			{
				$config['filter_fields'] = array(
					'id', 'a.id',
					'title', 'a.title',
					'access', 'a.access', 'access_level',
					'ordering', 'a.ordering',
					'language', 'a.language');
			}
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

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

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
		parent::populateState('a.title', 'asc');
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
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
	 * Get the filter form
	 *
	 * @param   array    $data      data
	 * @param   boolean  $loadData  load current data
	 *
	 * @return  JForm/false  the JForm object or false
	 *
	 * @since   3.0
	 */
	public function getFilterForm($data = array(), $loadData = true)
	{
		$app = JFactory::getApplication();
		$form = null;

		// Try to locate the filter form automatically. Example: ContentModelArticles => "filter_articles"
		if (empty($this->filterFormName))
		{
			$classNameParts = explode('Model', get_called_class());

			if (count($classNameParts) == 2)
			{
				$this->filterFormName = 'filter_' . strtolower($classNameParts[1] . '_' . $app->input->get('type', 'albums', 'word'));
			}
		}

		if (!empty($this->filterFormName))
		{
			// Get the form.
			$form = $this->loadForm($this->context . '.filter', $this->filterFormName, array('control' => '', 'load_data' => $loadData));
		}

		return $form;
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   3.0
	 */
	protected function getListQuery()
	{
		$app = JFactory::getApplication();

		if ($app->input->get('type', 'albums', 'word') == 'tracks')
		{
		}
		else // Default to albums
		{
			return $this->getAlbumsQuery();
		}
	}

	protected function getAlbumsQuery()
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$user = JFactory::getUser();
		$query = $db->getQuery(true);

		$query->select(
			$this->getState(
				'list.select',
				$db->quoteName(array('a.id', 'a.title', 'a.alias', 'a.year', 'a.ordering', 'a.access', 'a.language', 'a.state'))
			)
		);
		$query->from($db->quoteName('#__ka_music_albums', 'a'));

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
			else
			{
				$search = $db->quote('%' . $db->escape(trim($search), true) . '%');
				$query->where('(a.title LIKE ' . $search . ')');
			}
		}

		// Filter by albums IDs.
		$movie_id = $app->input->get('movie_id', 0, 'int');

		if (!empty($movie_id))
		{
			$subquery = $db->getQuery(true)
				->select($db->quoteName('album_id'))
				->from($db->quoteName('#__ka_music_rel_movies'))
				->where($db->quoteName('movie_id') . ' = ' . (int) $movie_id);

			$query->where('a.id IN (' . $subquery . ')');
		}

		// Filter on the language.
		if ($language = $this->getState('filter.language'))
		{
			$query->where('a.language = ' . $db->quote($language));
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering', 'a.title');
		$orderDirn = $this->state->get('list.direction', 'asc');

		// SQL server change
		if ($orderCol == 'language')
		{
			$orderCol = 'l.title';
		}

		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		return $query;
	}

	/**
	 * Method to get a list of items.
	 *
	 * Overriden to inject convert the attribs field into a JParameter object.
	 *
	 * @return  mixed  An array of objects on success, false on failure.
	 *
	 * @since   1.6
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

	public function saveOrder()
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$data = $app->input->post->get('ord', array(), 'array');

		if (count($data) < 2)
		{
			return array('success' => false, 'message' => JText::_('COM_KA_SAVE_ORDER_AT_LEAST_TWO'));
		}

		$query_result = true;
		$db->setDebug(true);
		$db->lockTable('#__ka_music_albums');
		$db->transactionStart();

		foreach ($data as $key => $value)
		{
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__ka_music_albums'))
				->set($db->quoteName('ordering') . " = '" . (int) $key . "'")
				->where($db->quoteName('id') . ' = ' . (int) $value);
			$db->setQuery($query . ';');

			if ($db->execute() === false)
			{
				$query_result = false;
				break;
			}
		}

		if ($query_result === false)
		{
			$db->transactionRollback();
		}
		else
		{
			$db->transactionCommit();
		}

		$db->unlockTables();
		$db->setDebug(false);

		if ($query_result)
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
		$batch_data = $app->input->post->get('batch', array(), 'array');

		if (empty($batch_data))
		{
			return false;
		}

		$fields = array();

		if (!empty($batch_data['language_id']))
		{
			$fields[] = $db->quoteName('language') . " = '" . $db->escape((string) $batch_data['language_id']) . "'";
		}

		if (!empty($batch_data['assetgroup_id']))
		{
			$fields[] = $db->quoteName('access') . " = '" . (int) $batch_data['assetgroup_id'] . "'";
		}

		if (empty($fields))
		{
			return false;
		}

		$query = $db->getQuery(true);

		$query->update($db->quoteName('#__ka_music_albums'))
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

		if (!empty($batch_data['tag']))
		{
			foreach ($ids as $id)
			{
				$query = $db->getQuery(true);

				$query->select($db->quoteName('metadata'))
					->from($db->quoteName('#__ka_music_albums'))
					->where($db->quoteName('id') . ' = ' . (int) $id);

				$db->setQuery($query);
				$result = $db->loadObject();
				$obj = json_decode($result->metadata);

				if (is_array($batch_data['tag']))
				{
					$obj->tags = array_unique(array_merge($obj->tags, $batch_data['tag']));
				}
				else
				{
					if (!in_array($batch_data['tag'], $obj->tags))
					{
						$obj->tags[] = (int) $batch_data['tag'];
					}
				}

				$query = $db->getQuery(true);

				$query->update($db->quoteName('#__ka_music_albums'))
					->set($db->quoteName('metadata') . " = '" . json_encode($obj) . "'")
					->where($db->quoteName('id') . ' = ' . (int) $id);

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
			}
		}

		return true;
	}
}
