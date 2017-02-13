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
 * Global model class to provide an API.
 *
 * @since  3.1
 */
class KinoarhivModelAPI extends JModelLegacy
{
	/**
	 * Database Connector
	 *
	 * @var    JDatabaseDriver
	 * @since  3.1
	 */
	protected $db;

	/**
	 * An input object
	 *
	 * @var    object
	 * @since  3.1
	 */
	protected $input;

	/**
	 * A language object
	 *
	 * @var    object
	 * @since  3.1
	 */
	protected $lang;

	/**
	 * A content language to filter by
	 *
	 * @var    string
	 * @since  3.1
	 */
	protected $query_lang;

	/**
	 * User access groups to filter by
	 *
	 * @var    string
	 * @since  3.1
	 */
	protected $query_access;

	/**
	 * Item state
	 *
	 * @var    string
	 * @since  3.1
	 */
	protected $query_state;

	/**
	 * Constructor
	 *
	 * @param   array  $config  An array of configuration options (name, state, dbo, table_path, ignore_request).
	 *
	 * @since   12.2
	 * @throws  Exception
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->input = JFactory::getApplication()->input;
		$this->lang = JFactory::getLanguage();
		$this->db = $this->getDbo();
		$user = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$data_lang = $this->input->get('data_lang', '', 'string');

		if ($data_lang !== '')
		{
			if ($data_lang == '*')
			{
				$this->query_lang = '';
			}
			else
			{
				$this->query_lang = 'language IN (' . $this->db->quote($data_lang) . ')';
			}
		}
		else
		{
			if (array_key_exists('data_lang', $config) && $config['data_lang'] != '')
			{
				if ($config['data_lang'] == '*')
				{
					$this->query_lang = '';
				}
				else
				{
					$this->query_lang = 'language IN (' . $this->db->quote($config['data_lang']) . ')';
				}
			}
			else
			{
				//$this->query_lang = 'language IN (' . $this->db->quote($this->lang->getTag()) . ',' . $this->db->quote('*') . ')';
			}
		}

		if (array_key_exists('item_access', $config) && is_array($config['item_access']))
		{
			$this->query_access = 'access IN (' . implode(',', $config['item_access']) . ')';
		}
		else
		{
			if (array_key_exists('item_access', $config) && $config['item_access'] == '*')
			{
				$this->query_access = '';
			}
			else
			{
				//$this->query_access = 'access IN (' . $groups . ')';
			}
		}

		if (array_key_exists('item_state', $config) && is_array($config['item_state']))
		{
			$this->query_state = 'state IN (' . implode(',', $config['item_state']) . ')';
		}
		else
		{
			$this->query_state = 'state = 1';
		}
	}

	/**
	 * Method to get list of countries or country based on filters.
	 *
	 * @return  mixed
	 *
	 * @since   3.1
	 * @throws  RuntimeException
	 */
	public function getCountries()
	{
		$id = $this->input->get('id', 0, 'int');
		$all = $this->input->get('showAll', 0, 'int');
		$multiple = $this->input->get('multiple', 0, 'int');
		$term = $this->input->get('term', '', 'string');

		// Do not remove `code` field from the query. It's necessary for flagging row in select
		$query = $this->db->getQuery(true)
			->select('id, name AS text, code')
			->from($this->db->quoteName('#__ka_countries'));

		// Filter by language
		if ($this->query_lang != '')
		{
			$query->where($this->query_lang);
		}

		// Filter by item state
		if ($this->query_state != '')
		{
			$query->where($this->query_state);
		}

		if ($all == 0)
		{
			if ($id == 0)
			{
				if (empty($term))
				{
					return array();
				}

				$query->where('name LIKE "' . $this->db->escape($term) . '%"')
					->order('name ASC');
				$this->db->setQuery($query);

				try
				{
					$result = $this->db->loadObjectList();
				}
				catch (RuntimeException $e)
				{
					throw new RuntimeException(JText::_('ERROR'), 500);
				}
			}
			else
			{
				if ($multiple == 1)
				{
					// TODO Convert ID's into string
					$ids = $this->input->get('id', '', 'string');
					$query->where('id IN (' . $ids . ')')
						->order('name ASC');
					$this->db->setQuery($query);

					try
					{
						$result = $this->db->loadObjectList();
					}
					catch (RuntimeException $e)
					{
						throw new RuntimeException(JText::_('ERROR'), 500);
					}
				}
				else
				{
					$query->where('id = ' . (int) $id);
					$this->db->setQuery($query);

					try
					{
						$result = $this->db->loadObject();
					}
					catch (RuntimeException $e)
					{
						throw new RuntimeException(JText::_('ERROR'), 500);
					}
				}
			}
		}
		else
		{
			$query->order('name ASC');
			$this->db->setQuery($query);

			try
			{
				$result = $this->db->loadObjectList();
			}
			catch (RuntimeException $e)
			{
				throw new RuntimeException(JText::_('ERROR'), 500);
			}
		}

		return $result;
	}

	/**
	 * Method to get list of movies or movie based on filters.
	 *
	 * @return  mixed
	 *
	 * @since   3.1
	 * @throws  RuntimeException
	 */
	public function getMovies()
	{
		$id = $this->input->get('id', 0, 'int');
		$all = $this->input->get('showAll', 0, 'int');
		$term = $this->input->get('term', '', 'string');
		$ignore = $this->input->get('ignore_ids', array(), 'array');

		$query = $this->db->getQuery(true)
			->select('id, title, year')
			->from($this->db->quoteName('#__ka_movies'));

		// Filter results set by IDs
		if (!empty($ignore))
		{
			$query->where($this->db->quoteName('id') . ' NOT IN (' . implode(',', $ignore) . ')');
		}

		// Filter by language
		if ($this->query_lang != '')
		{
			$query->where($this->query_lang);
		}

		// Filter by access
		if ($this->query_access != '')
		{
			$query->where($this->query_access);
		}

		// Filter by item state
		if ($this->query_state != '')
		{
			$query->where($this->query_state);
		}

		if (empty($all))
		{
			if (empty($id))
			{
				$query->where("title LIKE '" . $this->db->escape($term) . "%'");
				$this->db->setQuery($query);

				try
				{
					$result = $this->db->loadObjectList();
				}
				catch (RuntimeException $e)
				{
					throw new RuntimeException(JText::_('ERROR'), 500);
				}
			}
			else
			{
				$query->where('id = ' . (int) $id);
				$this->db->setQuery($query);

				try
				{
					$result = $this->db->loadObject();
				}
				catch (RuntimeException $e)
				{
					throw new RuntimeException(JText::_('ERROR'), 500);
				}
			}
		}
		else
		{
			$query->order('title ASC');
			$this->db->setQuery($query);

			try
			{
				$result = $this->db->loadObjectList();
			}
			catch (RuntimeException $e)
			{
				throw new RuntimeException(JText::_('ERROR'), 500);
			}
		}

		return $result;
	}

	/**
	 * Method to get list of distributors or distributor based on filters.
	 *
	 * @return  mixed
	 *
	 * @since   3.1
	 * @throws  RuntimeException
	 */
	public function getVendors()
	{
		$id = $this->input->get('id', 0, 'int');
		$all = $this->input->get('showAll', 0, 'int');
		$multiple = $this->input->get('multiple', 0, 'int');
		$term = $this->input->get('term', '', 'string');

		$query = $this->db->getQuery(true)
			->select('id, company_name AS text')
			->from($this->db->quoteName('#__ka_vendors'));

		// Filter by language
		if ($this->query_lang != '')
		{
			$query->where($this->query_lang);
		}

		// Filter by item state
		if ($this->query_state != '')
		{
			$query->where($this->query_state);
		}

		if ($all == 0)
		{
			if ($id == 0)
			{
				if (empty($term))
				{
					return array();
				}

				$query->where('company_name LIKE "' . $this->db->escape($term) . '%"')
					->order('company_name ASC');
				$this->db->setQuery($query);

				try
				{
					$result = $this->db->loadObjectList();
				}
				catch (RuntimeException $e)
				{
					throw new RuntimeException(JText::_('ERROR'), 500);
				}
			}
			else
			{
				if ($multiple == 1)
				{
					// TODO Convert ID's into string
					$ids = $this->input->get('id', '', 'string');
					$query->where('id IN (' . $ids . ')')
						->order('company_name ASC');
					$this->db->setQuery($query);

					try
					{
						$result = $this->db->loadObjectList();
					}
					catch (RuntimeException $e)
					{
						throw new RuntimeException(JText::_('ERROR'), 500);
					}
				}
				else
				{
					$query->where('id = ' . (int) $id);
					$this->db->setQuery($query);

					try
					{
						$result = $this->db->loadObject();
					}
					catch (RuntimeException $e)
					{
						throw new RuntimeException(JText::_('ERROR'), 500);
					}
				}
			}
		}
		else
		{
			$query->order('company_name ASC');
			$this->db->setQuery($query);

			try
			{
				$result = $this->db->loadObjectList();
			}
			catch (RuntimeException $e)
			{
				throw new RuntimeException(JText::_('ERROR'), 500);
			}
		}

		return $result;
	}

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set from a database.
	 *
	 * @param   string   $section  Type of the item. Can be 'movie' or 'name'.
	 * @param   string   $type     Type of the section. Can be 'gallery', 'trailers', 'soundtracks'
	 * @param   integer  $tab      Tab number from gallery(or empty value for 'trailers', 'soundtracks').
	 * @param   integer  $id       The item ID (movie or name).
	 *
	 * @return  object
	 *
	 * @throws  RuntimeException
	 * @since   3.1
	 */
	public function getGalleryFiles($section = '', $type = '', $tab = 0, $id = 0)
	{
		$db      = $this->getDbo();
		$input   = JFactory::getApplication()->input;
		$section = !empty($section) ? $section : $input->get('section', '', 'word');
		$type    = !empty($type) ? $type : $input->get('type', '', 'word');
		$tab     = !empty($tab) ? $tab : $input->get('tab', 0, 'int');
		$id      = !empty($id) ? $id : $input->get('id', 0, 'int');

		if ($section == 'movie' && $type == 'gallery')
		{
			$query = $this->listQueryMovieImages($tab, $id);
		}
		elseif ($section == 'name' && $type == 'gallery')
		{
			$query = $this->listQueryNameImages($tab, $id);
		}

		if (empty($query))
		{
			throw new RuntimeException(JText::_('ERROR'), 500);
		}

		$db->setQuery($query);

		try
		{
			$result = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException(JText::_('ERROR'), 500);
		}

		return $result;
	}

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set for movie images.
	 *
	 * @param   integer  $tab  Tab number from gallery.
	 * @param   integer  $id   The movie ID.
	 *
	 * @return  JDatabaseQuery   A JDatabaseQuery object to retrieve the data set.
	 *
	 * @since   3.1
	 */
	private function listQueryMovieImages($tab, $id)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select(
			$db->quoteName(
				array('g.id', 'g.filename', 'g.dimension', 'g.movie_id', 'g.frontpage', 'g.state', 'm.alias', 'm.fs_alias')
			)
		);

		$query->from($db->quoteName('#__ka_movies_gallery', 'g'))
			->leftJoin($db->quoteName('#__ka_movies', 'm') . ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('g.movie_id'));

		$query->where($db->quoteName('g.type') . ' = ' . (int) $tab)
			->where($db->quoteName('g.movie_id') . ' = ' . (int) $id);

		// Filter by published state
		/*$published = $this->getState('filter.published');

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

		$query->order($db->escape($orderCol . ' ' . $orderDirn));*/

		return $query;
	}

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set for name images.
	 *
	 * @param   integer  $tab  Tab number from gallery.
	 * @param   integer  $id   The movie ID.
	 *
	 * @return  JDatabaseQuery   A JDatabaseQuery object to retrieve the data set.
	 *
	 * @since   3.0
	 */
	private function listQueryNameImages($tab, $id)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select(
			$db->quoteName(
				array('g.id', 'g.filename', 'g.dimension', 'g.name_id', 'g.frontpage', 'g.state', 'n.alias', 'n.fs_alias')
			)
		);

		$query->from($db->quoteName('#__ka_names_gallery', 'g'))
			->leftJoin($db->quoteName('#__ka_names', 'n') . ' ON ' . $db->quoteName('n.id') . ' = ' . $db->quoteName('g.name_id'));

		$query->where($db->quoteName('g.type') . ' = ' . (int) $tab)
			->where($db->quoteName('g.name_id') . ' = ' . (int) $id);

		// Filter by published state
		/*$published = $this->getState('filter.published');

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

		$query->order($db->escape($orderCol . ' ' . $orderDirn));*/

		return $query;
	}

	/**
	 * Method to get list of trailer files.
	 *
	 * @param   integer  $id    Trailer ID.
	 * @param   string   $data  Comma separated list of columns which should return.
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	public function getTrailerFiles($id = 0, $data = '')
	{
		jimport('components.com_kinoarhiv.helpers.content', JPATH_ROOT);

		$id = !empty($id) ? $id : $this->input->get('id', 0, 'int');
		$data = !empty($data) ? $data : $this->input->get('data', '', 'string');
		$filters = !empty($filters) ? $filters : $this->input->get('filters', array(), 'array');

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName(array('movie_id', 'screenshot', 'video', 'subtitles', 'chapters')))
			->from($this->db->quoteName('#__ka_trailers'))
			->where('id = ' . (int) $id);

		// Filter by language
		if ($this->query_lang != '')
		{
			$query->where($this->query_lang);
		}

		// Filter by access
		if ($this->query_access != '')
		{
			$query->where($this->query_access);
		}

		// Filter by item state
		if ($this->query_state != '')
		{
			$query->where($this->query_state);
		}

		$this->db->setQuery($query);

		try
		{
			$result = $this->db->loadAssoc();

			if (count($result) < 1)
			{
				return array();
			}

			$folder = KAContentHelper::getPath('movie', 'trailers', null, $result['movie_id']);
		}
		catch (Exception $e)
		{
			return array();
		}

		if ($data != '')
		{
			$columns = preg_split('/[\s*,\s*]*,+[\s*,\s*]*/', trim($data));

			// Always attach screenshot if data type = video
			if (in_array('video', $columns))
			{
				$columns[] = 'screenshot';
				$screenshot['file'] = $result['screenshot'];
				$screenshot['is_file'] = (!is_file($folder . $result['screenshot'])) ? 0 : 1;
				$result['screenshot'] = $screenshot;
			}

			$video = json_decode($result['video']);
			$result['video'] = array();

			foreach ($video as $key => $item)
			{
				$result['video'][$key] = $item;
				$result['video'][$key]->is_file = (!is_file($folder . $item->src)) ? 0 : 1;
			}

			$subtitles = json_decode($result['subtitles']);
			$result['subtitles'] = array();

			foreach ($subtitles as $key => $item)
			{
				$result['subtitles'][$key] = $item;
				$result['subtitles'][$key]->is_file = (!is_file($folder . $item->file)) ? 0 : 1;
			}

			$chapters = json_decode($result['chapters']);
			$result['chapters'] = array();

			foreach ($chapters as $key => $item)
			{
				$result['chapters'][$key] = $item;
				$result['chapters']['is_file'] = (!is_file($folder . $item)) ? 0 : 1;
			}

			$result = array_intersect_key($result, array_flip($columns));
		}

		return $result;
	}

	/**
	 * Method to get ajax data from some tables
	 *
	 * @param   string  $element  Data type. Can be 'countries', 'genres', 'movies', 'awards', 'names', 'tags', 'careers', 'vendors'
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @since 3.0
	 */
	public function getData($element = '')
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$element = !empty($element) ? $element : $app->input->get('element', '', 'string');
		$all = $app->input->get('showAll', 0, 'int');
		$term = $app->input->get('term', '', 'string');
		$id = $app->input->get('id', 0, 'int');
		$multiple = $app->input->get('multiple', 0, 'int');
		$lang = JFactory::getLanguage();
		$user = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$language_in = "language IN (" . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ")";

		if ($element == 'countries')
		{
			// Do not remove `code` field from the query. It's necessary for flagging row in select
			if (empty($all))
			{
				if (empty($id))
				{
					if (empty($term))
					{
						return array();
					}

					$query = $db->getQuery(true)
						->select('id, name AS text, code')
						->from($db->quoteName('#__ka_countries'))
						->where('name LIKE "' . $db->escape($term) . '%" AND ' . $language_in . ' AND state = 1')
						->order('name ASC');

					$db->setQuery($query);
					$result = $db->loadObjectList();
				}
				else
				{
					if ($multiple == 1)
					{
						// TODO Convert ID's into string
						$ids = $app->input->get('id', '', 'string');
						$query = $db->getQuery(true)
							->select('id, name AS text, code')
							->from($db->quoteName('#__ka_countries'))
							->where('id IN (' . $ids . ') AND ' . $language_in . ' AND state = 1')
							->order('name ASC');

						$db->setQuery($query);
						$result = $db->loadObjectList();
					}
					else
					{
						$query = $db->getQuery(true)
							->select('id, name AS text, code')
							->from($db->quoteName('#__ka_countries'))
							->where('id = ' . (int) $id . ' AND ' . $language_in . ' AND state = 1');

						$db->setQuery($query);
						$result = $db->loadObject();
					}
				}
			}
			else
			{
				$query = $db->getQuery(true)
					->select('id, name AS text, code')
					->from($db->quoteName('#__ka_countries'))
					->where($language_in . ' AND state = 1')
					->order('name ASC');

				$db->setQuery($query);
				$result = $db->loadObjectList();
			}
		}
		elseif ($element == 'genres-movie')
		{
			if (empty($all))
			{
				if (empty($id))
				{
					if (empty($term))
					{
						return array();
					}

					$query = $db->getQuery(true)
						->select('id, name AS text')
						->from($db->quoteName('#__ka_genres'))
						->where("name LIKE '" . $db->escape($term) . "%'" . " AND " . $language_in . " AND state = 1 AND access IN (" . $groups . ")");

					$db->setQuery($query);
					$result = $db->loadObjectList();
				}
				else
				{
					if ($multiple == 1)
					{
						// TODO Convert ID's into string
						$ids = $app->input->get('id', '', 'string');
						$query = $db->getQuery(true)
							->select('id, name AS text')
							->from($db->quoteName('#__ka_genres'))
							->where('id IN (' . $ids . ') AND ' . $language_in . ' AND state = 1 AND access IN (' . $groups . ')')
							->order('name ASC');

						$db->setQuery($query);
						$result = $db->loadObjectList();
					}
					else
					{
						$query = $db->getQuery(true)
							->select('id, name AS text')
							->from($db->quoteName('#__ka_genres'))
							->where("id = " . (int) $id . " AND " . $language_in . " AND state = 1 AND access IN (" . $groups . ")");

						$db->setQuery($query);
						$result = $db->loadObject();
					}
				}
			}
			else
			{
				$query = $db->getQuery(true)
					->select('id, name AS text')
					->from($db->quoteName('#__ka_genres'))
					->where("language IN (" . $db->quote($lang->getTag()) . "," . $db->quote('*') . ") AND state = 1 AND access IN (" . $groups . ")");

				$db->setQuery($query);
				$result = $db->loadObjectList();
			}
		}
		elseif ($element == 'movies')
		{
			if (empty($all))
			{
				if (empty($id))
				{
					$query = $db->getQuery(true)
						->select('id, title, year')
						->from($db->quoteName('#__ka_movies'))
						->where("title LIKE '" . $db->escape($term) . "%'" . " AND " . $language_in . " AND state = 1 AND access IN (" . $groups . ")");

					$db->setQuery($query);
					$result = $db->loadObjectList();
				}
				else
				{
					$query = $db->getQuery(true)
						->select('id, title, year')
						->from($db->quoteName('#__ka_movies'))
						->where("id = " . (int) $id . " AND " . $language_in . " AND state = 1 AND access IN (" . $groups . ")");

					$db->setQuery($query);
					$result = $db->loadObject();
				}
			}
			else
			{
				$query = $db->getQuery(true)
					->select('id, title, year')
					->from($db->quoteName('#__ka_movies'))
					->where($language_in . " AND state = 1 AND access IN (" . $groups . ")");

				$db->setQuery($query);
				$result = $db->loadObjectList();
			}
		}
		elseif ($element == 'awards')
		{
			$type = $app->input->get('type', -1, 'int');

			if ($type == 0)
			{
				$result = $this->getData('movies');
			}
			elseif ($type == 1)
			{
				$result = $this->getData('names');
			}
			else
			{
				if (empty($all))
				{
					if (empty($id))
					{
						$query = $db->getQuery(true)
							->select('id, title')
							->from($db->quoteName('#__ka_awards'))
							->where("title LIKE '" . $db->escape($term) . "%'" . " AND " . $language_in . " AND state = 1");

						$db->setQuery($query);
						$result = $db->loadObjectList();
					}
					else
					{
						$query = $db->getQuery(true)
							->select('id, title')
							->from($db->quoteName('#__ka_awards'))
							->where("id = " . (int) $id . " AND " . $language_in . " AND state = 1");

						$db->setQuery($query);
						$result = $db->loadObject();
					}
				}
				else
				{
					$query = $db->getQuery(true)
						->select('id, title')
						->from($db->quoteName('#__ka_awards'))
						->where($language_in . " AND state = 1");

					$db->setQuery($query);
					$result = $db->loadObjectList();
				}
			}
		}
		elseif ($element == 'names')
		{
			if (empty($all))
			{
				if (empty($id))
				{
					$query = $db->getQuery(true)
						->select('id, name, latin_name, date_of_birth')
						->from($db->quoteName('#__ka_names'))
						->where("(name LIKE '" . $db->escape($term) . "%' OR latin_name LIKE '" . $db->escape($term) . "%')" . " AND " . $language_in . " AND state = 1 AND access IN (" . $groups . ")");

					$db->setQuery($query);
					$result = $db->loadObjectList();
				}
				else
				{
					if ($multiple == 1)
					{
						// TODO Convert ID's into string
						$ids = $app->input->get('id', '', 'string');
						$query = $db->getQuery(true)
							->select('id, name, latin_name, date_of_birth')
							->from($db->quoteName('#__ka_names'))
							->where('id IN (' . $ids . ') AND ' . $language_in . ' AND state = 1 AND access IN (' . $groups . ')');

						$db->setQuery($query);
						$result = $db->loadObjectList();
					}
					else
					{
						$query = $db->getQuery(true)
							->select('id, name, latin_name, date_of_birth')
							->from($db->quoteName('#__ka_names'))
							->where("id = " . (int) $id . " AND " . $language_in . " AND state = 1 AND access IN (" . $groups . ")");

						$db->setQuery($query);
						$result = $db->loadObject();
					}
				}
			}
			else
			{
				$query = $db->getQuery(true)
					->select('id, name, latin_name, date_of_birth')
					->from($db->quoteName('#__ka_names'))
					->where($language_in . " AND state = 1 AND access IN (" . $groups . ")");

				$db->setQuery($query);
				$result = $db->loadObjectList();
			}
		}
		elseif ($element == 'tags')
		{
			if (empty($all))
			{
				if (empty($id))
				{
					$query = $db->getQuery(true)
						->select('id, title AS text')
						->from($db->quoteName('#__tags'))
						->where("title LIKE '" . $db->escape($term) . "%' AND " . $language_in . " AND published = 1 AND access IN (" . $groups . ") AND parent_id != 0");

					$db->setQuery($query);
					$result = $db->loadObjectList();
				}
				else
				{
					if ($multiple == 1)
					{
						// TODO Convert ID's into string
						$ids = $app->input->get('id', '', 'string');
						$query = $db->getQuery(true)
							->select('id, title AS text')
							->from($db->quoteName('#__tags'))
							->where('id IN (' . $ids . ') AND ' . $language_in . ' AND published = 1 AND parent_id != 0')
							->order('title ASC');

						$db->setQuery($query);
						$result = $db->loadObjectList();
					}
					else
					{
						$query = $db->getQuery(true)
							->select('id, title AS text')
							->from($db->quoteName('#__tags'))
							->where('id = ' . (int) $id . ' AND ' . $language_in . ' AND published = 1 AND access IN (' . $groups . ') AND parent_id != 0');

						$db->setQuery($query);
						$result = $db->loadObject();
					}
				}
			}
			else
			{
				$query = $db->getQuery(true)
					->select('id, title')
					->from($db->quoteName('#__tags'))
					->where($language_in . " AND published = 1 AND access IN (" . $groups . ")");

				$db->setQuery($query);
				$result = $db->loadObjectList();
			}
		}
		elseif ($element == 'career' || $element == 'careers')
		{
			if (empty($all))
			{
				if (empty($id))
				{
					$query = $db->getQuery(true)
						->select('id, title')
						->from($db->quoteName('#__ka_names_career'))
						->where("title LIKE '" . $db->escape($term) . "%'" . " AND " . $language_in);

					$db->setQuery($query);
					$result = $db->loadObjectList();
				}
				else
				{
					$query = $db->getQuery(true)
						->select('id, title')
						->from($db->quoteName('#__ka_names_career'))
						->where("id = " . (int) $id . " AND " . $language_in);

					$db->setQuery($query);
					$result = $db->loadObject();
				}
			}
			else
			{
				$query = $db->getQuery(true)
					->select('id, title')
					->from($db->quoteName('#__ka_names_career'))
					->where($language_in);

				$db->setQuery($query);
				$result = $db->loadObjectList();
			}
		}
		elseif ($element == 'vendors')
		{
			if ($all == 0)
			{
				if (empty($id))
				{
					$query = $db->getQuery(true)
						->select('id, company_name, company_name_intl')
						->from($db->quoteName('#__ka_vendors'))
						->where("company_name LIKE '" . $db->escape($term) . "%' OR company_name_intl LIKE '" . $db->escape($term) . "%'" . " AND " . $language_in . " AND state = 1");

					$db->setQuery($query);
					$result = $db->loadObjectList();
				}
				else
				{
					if ($multiple == 1)
					{
						// TODO Convert ID's into string
						$ids = $app->input->get('id', '', 'string');
						$query = $db->getQuery(true)
							->select('id, company_name, company_name_intl')
							->from($db->quoteName('#__ka_vendors'))
							->where('id IN (' . $ids . ') AND ' . $language_in . ' AND state = 1');

						$db->setQuery($query);
						$result = $db->loadObjectList();
					}
					else
					{
						$query = $db->getQuery(true)
							->select('id, company_name, company_name_intl')
							->from($db->quoteName('#__ka_vendors'))
							->where("id = " . (int) $id . " AND " . $language_in . " AND state = 1");

						$db->setQuery($query);
						$result = $db->loadObject();
					}
				}
			}
			else
			{
				$query = $db->getQuery(true)
					->select('id, company_name, company_name_intl')
					->from($db->quoteName('#__ka_vendors'))
					->where($language_in . " AND state = 1");

				$db->setQuery($query);
				$result = $db->loadObjectList();
			}
		}
		else
		{
			$result = array();
		}

		return $result;
	}

	public function getRatingById($id, $name)
	{
		$db = $this->getDbo();

		switch ($name)
		{
			case 'imdb':
				$cols = array('imdb_votesum', 'imdb_votes');
				break;
			default:
				return false;
		}

		$query = $db->getQuery(true)
			->select($db->quoteName($cols))
			->from($db->quoteName('#__ka_movies'))
			->where('');
	}
}
