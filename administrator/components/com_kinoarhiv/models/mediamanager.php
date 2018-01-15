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
 * Class KinoarhivModelMediamanager
 *
 * @since  3.0
 */
class KinoarhivModelMediamanager extends JModelList
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
			$config['filter_fields'] = array(
				'id', 'g.id',
				'filename', 'g.filename',
				'title', 'g.title',
				'dimension', 'g.dimension',
				'frontpage', 'g.frontpage',
				'state', 'g.state',
				'access', 'published',
				'language', 'g.language');
		}

		$input = JFactory::getApplication()->input;

		// Adjust context to support different active filters for gallery and trailers.
		if ($input->get('type', '', 'word') == 'trailers')
		{
			$this->context = 'com_kinoarhiv.mediamanager.trailers';
		}
		else
		{
			$this->context = 'com_kinoarhiv.mediamanager.' . $input->get('section', '', 'word') . '.gallery.' . $input->get('tab', 0, 'int');
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
		if ($app->input->get('type', 'gallery', 'word') == 'gallery')
		{
			parent::populateState('g.filename', 'asc');
		}
		else
		{
			parent::populateState('g.title', 'asc');
		}
	}

	/**
	 * Get the filter form
	 *
	 * @param   array    $data      data
	 * @param   boolean  $loadData  load current data
	 *
	 * @return  JForm|boolean  The JForm object or false on error
	 *
	 * @since   3.1
	 */
	public function getFilterForm($data = array(), $loadData = true)
	{
		$input = JFactory::getApplication()->input;
		$form = null;

		// Try to locate the filter form automatically. Example: ContentModelArticles => "filter_articles"
		if (empty($this->filterFormName))
		{
			$classNameParts = explode('Model', get_called_class());

			if (count($classNameParts) == 2)
			{
				$this->filterFormName = 'filter_' . strtolower($classNameParts[1]);
			}
		}

		if (!empty($this->filterFormName))
		{
			if ($input->get('type', '', 'word') == 'gallery')
			{
				$filterFormName = $this->filterFormName . '_gallery';
			}
			elseif ($input->get('type', '', 'word') == 'trailers')
			{
				$filterFormName = $this->filterFormName . '_trailers';
			}
			else
			{
				$filterFormName = $this->filterFormName;
			}

			// Get the form based on content type.
			$form = $this->loadForm($this->context . '.filter', $filterFormName, array('control' => '', 'load_data' => $loadData));
		}

		return $form;
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
	 * @throws  RuntimeException
	 * @since   3.0
	 */
	protected function getListQuery()
	{
		$input   = JFactory::getApplication()->input;
		$section = $input->get('section', '', 'word');
		$type    = $input->get('type', '', 'word');

		if ($section == 'movie' && $type == 'gallery')
		{
			$query = $this->listQueryMovieImages();
		}
		elseif ($section == 'movie' && $type == 'trailers')
		{
			$query = $this->listQueryMovieTrailers();
		}
		elseif ($section == 'name' && $type == 'gallery')
		{
			$query = $this->listQueryNameImages();
		}

		if (empty($query))
		{
			throw new RuntimeException('Empty JDatabaseQuery in ' . __METHOD__, 500);
		}

		return $query;
	}

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set for movie images.
	 *
	 * @return  JDatabaseQuery   A JDatabaseQuery object to retrieve the data set.
	 *
	 * @since   3.0
	 */
	private function listQueryMovieImages()
	{
		$input = JFactory::getApplication()->input;
		$tab   = $input->get('tab', 0, 'int');
		$id    = $input->get('id', 0, 'int');
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select(
			$this->getState(
				'list.select',
				$db->quoteName(array('g.id', 'g.filename', 'g.dimension', 'g.movie_id', 'g.frontpage', 'g.state', 'm.alias', 'm.fs_alias'))
			)
		);
		$query->from($db->quoteName('#__ka_movies_gallery', 'g'))
			->leftJoin($db->quoteName('#__ka_movies', 'm') . ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('g.movie_id'));

		$query->where($db->quoteName('g.type') . ' = ' . (int) $tab)
			->where($db->quoteName('g.movie_id') . ' = ' . (int) $id);

		// Filter by published state
		$published = $this->getState('filter.published');

		if (is_numeric($published))
		{
			$query->where('g.state = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(g.state = 0 OR g.state = 1)');
		}

		// Filter by search in title.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('g.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->quote('%' . $db->escape(trim($search), true) . '%');
				$query->where('(g.filename LIKE ' . $search . ')');
			}
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering', 'g.filename');
		$orderDirn = $this->state->get('list.direction', 'asc');

		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		return $query;
	}

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set for name images.
	 *
	 * @return  JDatabaseQuery   A JDatabaseQuery object to retrieve the data set.
	 *
	 * @since   3.0
	 */
	private function listQueryNameImages()
	{
		$input = JFactory::getApplication()->input;
		$tab   = $input->get('tab', 0, 'int');
		$id    = $input->get('id', 0, 'int');
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select(
			$this->getState(
				'list.select',
				$db->quoteName(array('g.id', 'g.filename', 'g.dimension', 'g.name_id', 'g.frontpage', 'g.state', 'n.alias', 'n.fs_alias'))
			)
		);
		$query->from($db->quoteName('#__ka_names_gallery', 'g'))
			->leftJoin($db->quoteName('#__ka_names', 'n') . ' ON ' . $db->quoteName('n.id') . ' = ' . $db->quoteName('g.name_id'));

		$query->where($db->quoteName('g.type') . ' = ' . (int) $tab)
			->where($db->quoteName('g.name_id') . ' = ' . (int) $id);

		// Filter by published state
		$published = $this->getState('filter.published');

		if (is_numeric($published))
		{
			$query->where('g.state = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(g.state = 0 OR g.state = 1)');
		}

		// Filter by search in title.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('g.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->quote('%' . $db->escape(trim($search), true) . '%');
				$query->where('(g.filename LIKE ' . $search . ')');
			}
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering', 'g.filename');
		$orderDirn = $this->state->get('list.direction', 'asc');

		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		return $query;
	}

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set for trailers list.
	 *
	 * @return  JDatabaseQuery   A JDatabaseQuery object to retrieve the data set.
	 *
	 * @since   3.0
	 */
	private function listQueryMovieTrailers()
	{
		$input = JFactory::getApplication()->input;
		$id    = $input->get('id', 0, 'int');
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select(
			$this->getState(
				'list.select',
				$db->quoteName(
					array('g.id', 'g.title', 'g.embed_code', 'g.duration', 'g.video', 'g.subtitles', 'g.chapters',
						'g.frontpage', 'g.state', 'g.language', 'g.is_movie', 'm.fs_alias'
					)
				)
			)
		);
		$query->from($db->quoteName('#__ka_trailers', 'g'))
			->leftJoin($db->quoteName('#__ka_movies', 'm') . ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('g.movie_id'));

		// Join over the language
		$query->select($db->quoteName('l.title', 'language_title'))
			->leftJoin($db->quoteName('#__languages', 'l') . ' ON ' . $db->quoteName('l.lang_code') . ' = ' . $db->quoteName('g.language'));

		// Join over the asset groups.
		$query->select($db->quoteName('ag.title', 'access_level'))
			->join('LEFT', $db->quoteName('#__viewlevels', 'ag') . ' ON ' . $db->quoteName('ag.id') . ' = ' . $db->quoteName('g.access'));

		$query->where('(' . $db->quoteName('g.state') . ' = 0 OR ' . $db->quoteName('g.state') . ' = 1)')
			->where($db->quoteName('g.movie_id') . ' = ' . (int) $id);

		// Filter by access level.
		if ($access = $this->getState('filter.access'))
		{
			$query->where('g.access = ' . (int) $access);
		}

		// Filter by published state
		$published = $this->getState('filter.published');

		if (is_numeric($published))
		{
			$query->where('g.state = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(g.state = 0 OR g.state = 1)');
		}

		// Filter by search in title.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('g.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->quote('%' . $db->escape(trim($search), true) . '%');
				$query->where('(g.title LIKE ' . $search . ')');
			}
		}

		// Filter on the language.
		if ($language = $this->getState('filter.language'))
		{
			$query->where('g.language = ' . $db->quote($language));
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering', 'g.title');
		$orderDirn = $this->state->get('list.direction', 'asc');

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
	 * Method to get a list of items.
	 * Overridden to add a check for access levels.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   3.0
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
	 * Method to get an item title.
	 *
	 * @param   string   $section  The section for searching. Can be 'movie', 'name', 'trailer', 'soundtrack'
	 * @param   integer  $id       Item ID.
	 *
	 * @return  mixed  Object with the data. False on error.
	 *
	 * @since  3.0
	 */
	public function getItemTitle($section = null, $id = null)
	{
		$db = $this->getDbo();
		$app = JFactory::getApplication();
		$section = empty($section) ? $app->input->get('section', '', 'word') : $section;
		$id = empty($id) ? $app->input->get('id', 0, 'int') : $id;

		if ($section == 'movie')
		{
			$query = $db->getQuery(true)
				->select($db->quoteName('title'))
				->from($db->quoteName('#__ka_movies'))
				->where($db->quoteName('id') . ' = ' . (int) $id);

			$db->setQuery($query);
			$data = $db->loadResult();
		}
		elseif ($section == 'name')
		{
			$query = $db->getQuery(true)
				->select($db->quoteName(array('name', 'latin_name')))
				->from($db->quoteName('#__ka_names'))
				->where($db->quoteName('id') . ' = ' . (int) $id);

			$db->setQuery($query);
			$result = $db->loadObject();

			jimport('components.com_kinoarhiv.helpers.content', JPATH_ROOT);

			$data = KAContentHelper::formatItemTitle($result->name, $result->latin_name);
		}
		else
		{
			$app->enqueueMessage('Unknown section type!', 'error');

			return false;
		}

		return $data;
	}

	/**
	 * Method to publish or unpublish posters or trailer on movie info page(not on posters or trailers page)
	 *
	 * @param   integer  $state  0 - unpublish from frontpage, 1 - publish on frontpage.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.0
	 */
	public function setFrontpage($state)
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$section = $app->input->get('section', null, 'word');
		$type = $app->input->get('type', '', 'word');
		$id = $app->input->get('id', null, 'int');
		$ids = $app->input->get('item_id', array(), 'array');

		if ($type == 'gallery')
		{
			if ($section == 'movie')
			{
				$table = '#__ka_movies_gallery';
				$pub_col = 'frontpage';
				$item_col = 'movie_id';
				$type_num = 2;
			}
			elseif ($section == 'name')
			{
				$table = '#__ka_names_gallery';
				$pub_col = 'frontpage';
				$item_col = 'name_id';
				$type_num = 3;
			}
			else
			{
				$app->enqueueMessage('Unknown gallery type', 'error');

				return false;
			}

			// Reset all frontpage field values to 0
			$query = $db->getQuery(true)
				->update($db->quoteName($table))
				->set($db->quoteName($pub_col) . " = 0")
				->where($db->quoteName($item_col) . ' = ' . (int) $id . ' AND ' . $db->quoteName('type') . ' = ' . $type_num);
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				$app->enqueueMessage($e->getMessage(), 'error');

				return false;
			}

			if (!isset($ids[0]) || empty($ids[0]))
			{
				$app->enqueueMessage('Unknown ID', 'error');

				return false;
			}

			$query = $db->getQuery(true)
				->update($db->quoteName($table))
				->set($db->quoteName($pub_col) . " = '" . (int) $state . "'")
				->where($db->quoteName('id') . ' = ' . (int) $ids[0]);
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				$app->enqueueMessage($e->getMessage(), 'error');

				return false;
			}
		}
		elseif ($type == 'trailers')
		{
			// We need to check if this is the movie to avoid errors when publishing a movie and trailer
			$query = $db->getQuery(true)
				->select('is_movie')
				->from($db->quoteName('#__ka_trailers'))
				->where($db->quoteName('id') . ' = ' . (int) $ids[0]);
			$db->setQuery($query);
			$is_movie = $db->loadResult();

			if ($is_movie == 0)
			{
				// Reset all values to 0
				$query = $db->getQuery(true)
					->update($db->quoteName('#__ka_trailers'))
					->set($db->quoteName('frontpage') . " = '0'")
					->where($db->quoteName('movie_id') . ' = ' . (int) $id . ' AND ' . $db->quoteName('is_movie') . ' = 0');
				$db->setQuery($query);
			}
			else
			{
				// Reset all values to 0
				$query = $db->getQuery(true)
					->update($db->quoteName('#__ka_trailers'))
					->set($db->quoteName('frontpage') . " = '0'")
					->where($db->quoteName('movie_id') . ' = ' . (int) $id . ' AND ' . $db->quoteName('is_movie') . ' = 1');
				$db->setQuery($query);
			}

			try
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				$app->enqueueMessage($e->getMessage(), 'error');

				return false;
			}

			if (!isset($ids[0]) || empty($ids[0]))
			{
				$app->enqueueMessage('Unknown ID', 'error');

				return false;
			}

			$query = $db->getQuery(true)
				->update($db->quoteName('#__ka_trailers'))
				->set($db->quoteName('frontpage') . " = '" . (int) $state . "'")
				->where($db->quoteName('id') . ' = ' . (int) $ids[0]);
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				$app->enqueueMessage($e->getMessage(), 'error');

				return false;
			}
		}
		else
		{
			return false;
		}

		return true;
	}

	/**
	 * Method to change the published state of one or more records.
	 *
	 * @param   boolean  $isUnpublish  Action state
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.0
	 */
	public function publish($isUnpublish)
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$section = $app->input->get('section', null, 'word');
		$type = $app->input->get('type', '', 'word');
		$ids = $app->input->get('item_id', array(), 'array');
		$state = $isUnpublish ? 0 : 1;

		if ($type == 'gallery')
		{
			if ($section == 'movie')
			{
				$table = '#__ka_movies_gallery';
			}
			elseif ($section == 'name')
			{
				$table = '#__ka_names_gallery';
			}
			else
			{
				$app->enqueueMessage('Unknown gallery type!', 'error');

				return false;
			}
		}
		elseif ($type == 'trailers')
		{
			$table = '#__ka_trailers';
		}
		else
		{
			$app->enqueueMessage('Unknown gallery!', 'error');

			return false;
		}

		$query = $db->getQuery(true);

		$query->update($db->quoteName($table))
			->set($db->quoteName('state') . " = '" . (int) $state . "'")
			->where($db->quoteName('id') . ' IN (' . implode(',', $ids) . ')');
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		return true;
	}

	/**
	 * Method to perform batch operations on an item or a set of items.
	 *
	 * @return  boolean  Returns true on success, false on failure.
	 *
	 * @since   3.1
	 */
	public function batch()
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$ids = $app->input->post->get('item_id', array(), 'array');
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

		if (empty($fields))
		{
			return false;
		}

		$query = $db->getQuery(true);

		$query->update($db->quoteName('#__ka_trailers'))
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
