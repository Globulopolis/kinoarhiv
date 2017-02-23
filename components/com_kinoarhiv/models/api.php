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
