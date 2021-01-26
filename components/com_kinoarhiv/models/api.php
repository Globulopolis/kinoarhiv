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

/**
 * Global model class to provide an API.
 *
 * @since  3.1
 */
class KinoarhivModelAPI extends JModelLegacy
{
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
	protected $queryLang;

	/**
	 * User access groups to filter by
	 *
	 * @var    string
	 * @since  3.1
	 */
	protected $queryAccess;

	/**
	 * Item state
	 *
	 * @var    string
	 * @since  3.1
	 */
	protected $queryState;

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
		$this->lang  = JFactory::getLanguage();
		$db          = JFactory::getDbo();
		$user        = JFactory::getUser();
		$groups      = implode(',', $user->getAuthorisedViewLevels());
		$dataLang    = $this->input->get('data_lang', '', 'string');

		// If language is not defined in any way then set to default.
		if (empty($dataLang))
		{
			$this->queryLang = $db->quoteName('language')
				. ' IN (' . $db->quote($this->lang->getTag()) . ',' . $db->quote('*') . ')';
		}
		else
		{
			// Check for keyword 'request'. This mean that the language will get from query.
			if ($dataLang === 'request')
			{
				$this->queryLang = 'language IN (' . $db->quote($this->lang->getTag()) . ')';
			}
			// Show all in any language
			elseif ($dataLang === '*')
			{
				$this->queryLang = '';
			}
			// Else from input
			else
			{
				$langs = explode(',', trim($dataLang));
				$this->queryLang = "language IN ('" . implode("','", $langs) . "')";
			}
		}

		if (array_key_exists('item_access', $config) && is_array($config['item_access']))
		{
			$this->queryAccess = 'access IN (' . implode(',', $config['item_access']) . ')';
		}
		else
		{
			if (array_key_exists('item_access', $config) && $config['item_access'] == '*')
			{
				$this->queryAccess = '';
			}
			else
			{
				//$this->query_access = 'access IN (' . $groups . ')';
			}
		}

		if (array_key_exists('item_state', $config) && is_array($config['item_state']))
		{
			$this->queryState = 'state IN (' . implode(',', $config['item_state']) . ')';
		}
		else
		{
			$this->queryState = 'state = 1';
		}
	}

	/**
	 * Method to get list of countries or country based on filters.
	 *
	 * @return  object|boolean
	 *
	 * @since   3.1
	 */
	public function getCountries()
	{
		$db       = JFactory::getDbo();
		$id       = $this->input->get('id', 0, 'int');
		$multiple = $this->input->get('multiple', 0, 'int');
		$term     = $this->input->get('term', '', 'string');
		$ignore   = $this->input->get('ignore_ids', array(), 'array');

		// Do not remove `code` field from the query. It's necessary for flagging row in select
		$query = $db->getQuery(true)
			->select($db->quoteName(array('id', 'code')))
			->select($db->quoteName('name', 'text'))
			->from($db->quoteName('#__ka_countries'));

		// Filter by language
		if ($this->queryLang !== '')
		{
			$query->where($this->queryLang);
		}

		// Filter by item state
		if ($this->queryState !== '')
		{
			$query->where($this->queryState);
		}

		// Delete empty values from array of IDs
		$ignore = array_filter($ignore);

		// Filter results set by IDs
		if (!empty($ignore) && count($ignore) > 0)
		{
			$query->where($db->quoteName('id') . ' NOT IN (' . implode(',', $ignore) . ')');
		}

		try
		{
			if (!empty($id) || $multiple === 1)
			{
				// Filter rows by IDs
				if ($multiple === 1)
				{
					$ids = $this->input->get('id', '', 'string');
					$ids = $this->sanitizeIDList($ids);

					$query->where($db->quoteName('id') . ' IN (' . $ids . ')')
						// Preserve row ordering
						->order('FIELD (' . $db->quoteName('id') . ', ' . $ids . ')');
					$db->setQuery($query);

					$result = $db->loadObjectList();
				}
				// Get single row by ID
				else
				{
					$query->where($db->quoteName('id') . ' = ' . (int) $id);
					$db->setQuery($query);

					$result = $db->loadObject();
				}
			}
			else
			{
				if (!empty($term))
				{
					$query->where($db->quoteName('name') . ' LIKE ' . $db->quote($term . '%'));
				}

				$query->order($db->quoteName('name') . ' ASC');
				$db->setQuery($query);

				$result = $db->loadObjectList();
			}
		}
		catch (RuntimeException $e)
		{
			KAComponentHelper::eventLog($e->getMessage());

			return false;
		}

		return $result;
	}

	/**
	 * Method to get list of movies or movie based on filters.
	 *
	 * @return  object|boolean
	 *
	 * @since   3.1
	 */
	public function getMovies()
	{
		$db       = JFactory::getDbo();
		$id       = $this->input->get('id', 0, 'int');
		$multiple = $this->input->get('multiple', 0, 'int');
		$term     = $this->input->get('term', '', 'string');
		$ignore   = $this->input->get('ignore_ids', array(), 'array');

		$query = $db->getQuery(true)
			->select($db->quoteName(array('id', 'title', 'year')))
			->from($db->quoteName('#__ka_movies'));

		// Filter by language
		if ($this->queryLang !== '')
		{
			$query->where($this->queryLang);
		}

		// Filter by item state
		if ($this->queryState !== '')
		{
			$query->where($this->queryState);
		}

		// Delete empty values from array of IDs
		$ignore = array_filter($ignore);

		// Filter results set by IDs
		if (!empty($ignore) && count($ignore) > 0)
		{
			$query->where($db->quoteName('id') . ' NOT IN (' . implode(',', $ignore) . ')');
		}

		// Filter by access
		if ($this->queryAccess != '')
		{
			$query->where($this->queryAccess);
		}

		try
		{
			if (!empty($id) || $multiple === 1)
			{
				// Filter rows by IDs
				if ($multiple === 1)
				{
					$ids = $this->input->get('id', '', 'string');
					$ids = $this->sanitizeIDList($ids);

					$query->where($db->quoteName('id') . ' IN (' . $ids . ')')
						// Preserve row ordering
						->order('FIELD (' . $db->quoteName('id') . ', ' . $ids . ')');
					$db->setQuery($query);

					$result = $db->loadObjectList();
				}
				// Get single row by ID
				else
				{
					$query->where($db->quoteName('id') . ' = ' . (int) $id);
					$db->setQuery($query);

					$result = $db->loadObject();
				}
			}
			else
			{
				if (!empty($term))
				{
					$query->where('(' . $db->quoteName('title') . ' LIKE ' . $db->quote($term . '%') . ')');
				}

				$query->order($db->quoteName('title') . ' ASC');
				$db->setQuery($query);

				$result = $db->loadObjectList();
			}
		}
		catch (RuntimeException $e)
		{
			KAComponentHelper::eventLog($e->getMessage());

			return false;
		}

		return $result;
	}

	/**
	 * Method to get list of albums or album based on filters.
	 *
	 * @return  object|boolean
	 *
	 * @since   3.1
	 */
	public function getAlbums()
	{
		$db       = JFactory::getDbo();
		$id       = $this->input->get('id', 0, 'int');
		$multiple = $this->input->get('multiple', 0, 'int');
		$term     = $this->input->get('term', '', 'string');
		$ignore   = $this->input->get('ignore_ids', array(), 'array');

		$query = $db->getQuery(true)
			->select($db->quoteName(array('id', 'title')))
			->select('DATE_FORMAT(' . $db->quoteName('year') . ', "%Y") AS ' . $db->quoteName('year'))
			->from($db->quoteName('#__ka_music_albums'));

		// Filter by language
		if ($this->queryLang !== '')
		{
			$query->where($this->queryLang);
		}

		// Filter by item state
		if ($this->queryState !== '')
		{
			$query->where($this->queryState);
		}

		// Delete empty values from array of IDs
		$ignore = array_filter($ignore);

		// Filter results set by IDs
		if (!empty($ignore) && count($ignore) > 0)
		{
			$query->where($db->quoteName('id') . ' NOT IN (' . implode(',', $ignore) . ')');
		}

		// Filter by access
		if ($this->queryAccess != '')
		{
			$query->where($this->queryAccess);
		}

		try
		{
			if (!empty($id) || $multiple === 1)
			{
				// Filter rows by IDs
				if ($multiple === 1)
				{
					$ids = $this->input->get('id', '', 'string');
					$ids = $this->sanitizeIDList($ids);

					$query->where($db->quoteName('id') . ' IN (' . $ids . ')')
						// Preserve row ordering
						->order('FIELD (' . $db->quoteName('id') . ', ' . $ids . ')');
					$db->setQuery($query);

					$result = $db->loadObjectList();
				}
				// Get single row by ID
				else
				{
					$query->where($db->quoteName('id') . ' = ' . (int) $id);
					$db->setQuery($query);

					$result = $db->loadObject();
				}
			}
			else
			{
				if (!empty($term))
				{
					$query->where($db->quoteName('title') . ' LIKE ' . $db->quote($term . '%'));
				}

				$query->order($db->quoteName('title') . ' ASC');
				$db->setQuery($query);

				$result = $db->loadObjectList();
			}
		}
		catch (RuntimeException $e)
		{
			KAComponentHelper::eventLog($e->getMessage());

			return false;
		}

		return $result;
	}

	/**
	 * Method to get list of names or name based on filters.
	 *
	 * @return  object|boolean
	 *
	 * @since   3.1
	 */
	public function getNames()
	{
		$db       = JFactory::getDbo();
		$id       = $this->input->get('id', 0, 'int');
		$multiple = $this->input->get('multiple', 0, 'int');
		$term     = $this->input->get('term', '', 'string');
		$ignore   = $this->input->get('ignore_ids', array(), 'array');

		$query = $db->getQuery(true)
			->select($db->quoteName(array('id', 'name', 'latin_name', 'date_of_birth')))
			->from($db->quoteName('#__ka_names'));

		// Filter by language
		if ($this->queryLang !== '')
		{
			$query->where($this->queryLang);
		}

		// Filter by item state
		if ($this->queryState !== '')
		{
			$query->where($this->queryState);
		}

		// Delete empty values from array of IDs
		$ignore = array_filter($ignore);

		// Filter results set by IDs
		if (!empty($ignore) && count($ignore) > 0)
		{
			$query->where($db->quoteName('id') . ' NOT IN (' . implode(',', $ignore) . ')');
		}

		// Filter by access
		if ($this->queryAccess != '')
		{
			$query->where($this->queryAccess);
		}

		try
		{
			if (!empty($id) || $multiple === 1)
			{
				// Filter rows by IDs
				if ($multiple === 1)
				{
					$ids = $this->input->get('id', '', 'string');
					$ids = $this->sanitizeIDList($ids);

					$query->where($db->quoteName('id') . ' IN (' . $ids . ')')
						// Preserve row ordering
						->order('FIELD (' . $db->quoteName('id') . ', ' . $ids . ')');
					$db->setQuery($query);

					$result = $db->loadObjectList();
				}
				// Get single row by ID
				else
				{
					$query->where($db->quoteName('id') . ' = ' . (int) $id);
					$db->setQuery($query);

					$result = $db->loadObject();
				}
			}
			else
			{
				if (!empty($term))
				{
					$query->where(
						'(' . $db->quoteName('name') . " LIKE '" . $db->escape($term) . "%' OR " .
						$db->quoteName('latin_name') . " LIKE '" . $db->escape($term) . "%')"
					);
				}

				$db->setQuery($query);

				$result = $db->loadObjectList();
			}
		}
		catch (RuntimeException $e)
		{
			KAComponentHelper::eventLog($e->getMessage());

			return false;
		}

		return $result;
	}

	/**
	 * Method to get list of awards or award based on filters.
	 *
	 * @return  object|boolean
	 *
	 * @since   3.1
	 */
	public function getAwards()
	{
		$db       = JFactory::getDbo();
		$id       = $this->input->get('id', 0, 'int');
		$multiple = $this->input->get('multiple', 0, 'int');
		$term     = $this->input->get('term', '', 'string');
		$ignore   = $this->input->get('ignore_ids', array(), 'array');

		$query = $db->getQuery(true)
			->select($db->quoteName('id') . ', ' . $db->quoteName('title', 'text'))
			->from($db->quoteName('#__ka_awards'));

		// Filter by language
		if ($this->queryLang != '')
		{
			$query->where($this->queryLang);
		}

		// Filter by item state
		if ($this->queryState != '')
		{
			$query->where($this->queryState);
		}

		// Delete empty values from array of IDs
		$ignore = array_filter($ignore);

		// Filter results set by IDs
		if (!empty($ignore) && count($ignore) > 0)
		{
			$query->where($db->quoteName('id') . ' NOT IN (' . implode(',', $ignore) . ')');
		}

		try
		{
			if (!empty($id) || $multiple === 1)
			{
				// Filter rows by IDs
				if ($multiple === 1)
				{
					$ids = $this->input->get('id', '', 'string');
					$ids = $this->sanitizeIDList($ids);

					$query->where($db->quoteName('id') . ' IN (' . $ids . ')')
						// Preserve row ordering
						->order('FIELD (' . $db->quoteName('id') . ', ' . $ids . ')');
					$db->setQuery($query);

					$result = $db->loadObjectList();
				}
				// Get single row by ID
				else
				{
					$query->where($db->quoteName('id') . ' = ' . (int) $id);
					$db->setQuery($query);

					$result = $db->loadObject();
				}
			}
			else
			{
				if (!empty($term))
				{
					$query->where($db->quoteName('title') . ' LIKE ' . $db->quote($term . '%'));
				}

				$query->order($db->quoteName('title') . ' ASC');
				$db->setQuery($query);

				$result = $db->loadObjectList();
			}
		}
		catch (RuntimeException $e)
		{
			KAComponentHelper::eventLog($e->getMessage());

			return false;
		}

		return $result;
	}

	/**
	 * Method to get list of distributors or distributor based on filters.
	 *
	 * @return  object|boolean
	 *
	 * @since   3.1
	 */
	public function getVendors()
	{
		$db       = JFactory::getDbo();
		$id       = $this->input->get('id', 0, 'int');
		$multiple = $this->input->get('multiple', 0, 'int');
		$term     = $this->input->get('term', '', 'string');
		$ignore   = $this->input->get('ignore_ids', array(), 'array');

		$query = $db->getQuery(true)
			->select($db->quoteName('id') . ', ' . $db->quoteName('company_name', 'text'))
			->from($db->quoteName('#__ka_vendors'));

		// Filter by language
		if ($this->queryLang != '')
		{
			$query->where($this->queryLang);
		}

		// Filter by item state
		if ($this->queryState != '')
		{
			$query->where($this->queryState);
		}

		// Delete empty values from array of IDs
		$ignore = array_filter($ignore);

		// Filter results set by IDs
		if (!empty($ignore) && count($ignore) > 0)
		{
			$query->where($db->quoteName('id') . ' NOT IN (' . implode(',', $ignore) . ')');
		}

		try
		{
			if (!empty($id) || $multiple === 1)
			{
				// Filter rows by IDs
				if ($multiple === 1)
				{
					$ids = $this->input->get('id', '', 'string');
					$ids = $this->sanitizeIDList($ids);

					$query->where($db->quoteName('id') . ' IN (' . $ids . ')')
						// Preserve row ordering
						->order('FIELD (' . $db->quoteName('id') . ', ' . $ids . ')');
					$db->setQuery($query);

					$result = $db->loadObjectList();
				}
				// Get single row by ID
				else
				{
					$query->where($db->quoteName('id') . ' = ' . (int) $id);
					$db->setQuery($query);

					$result = $db->loadObject();
				}
			}
			else
			{
				if (!empty($term))
				{
					$query->where($db->quoteName('company_name') . ' LIKE ' . $db->quote($term . '%'));
				}

				$query->order($db->quoteName('company_name') . ' ASC');
				$db->setQuery($query);

				$result = $db->loadObjectList();
			}
		}
		catch (RuntimeException $e)
		{
			KAComponentHelper::eventLog($e->getMessage());

			return false;
		}

		return $result;
	}

	/**
	 * Method to get list of careers based on filters.
	 *
	 * @return  object|boolean
	 *
	 * @since   3.1
	 */
	public function getCareers()
	{
		$db       = JFactory::getDbo();
		$id       = $this->input->get('id', 0, 'int');
		$multiple = $this->input->get('multiple', 0, 'int');
		$term     = $this->input->get('term', '', 'string');
		$ignore   = $this->input->get('ignore_ids', array(), 'array');

		$query = $db->getQuery(true)
			->select($db->quoteName('id') . ', ' . $db->quoteName('title', 'text'))
			->from($db->quoteName('#__ka_names_career'));

		// Filter by language
		if ($this->queryLang !== '')
		{
			$query->where($this->queryLang);
		}

		// Delete empty values from array of IDs
		$ignore = array_filter($ignore);

		// Filter results set by IDs
		if (!empty($ignore) && count($ignore) > 0)
		{
			$query->where($db->quoteName('id') . ' NOT IN (' . implode(',', $ignore) . ')');
		}

		try
		{
			if (!empty($id) || $multiple === 1)
			{
				// Filter rows by IDs
				if ($multiple === 1)
				{
					$ids = $this->input->get('id', '', 'string');
					$ids = $this->sanitizeIDList($ids);

					$query->where($db->quoteName('id') . ' IN (' . $ids . ')')
						// Preserve row ordering
						->order('FIELD (' . $db->quoteName('id') . ', ' . $ids . ')');
					$db->setQuery($query);

					$result = $db->loadObjectList();
				}
				// Get single row by ID
				else
				{
					$query->where($db->quoteName('id') . ' = ' . (int) $id);
					$db->setQuery($query);

					$result = $db->loadObject();
				}
			}
			else
			{
				if (!empty($term))
				{
					$query->where($db->quoteName('title') . ' LIKE ' . $db->quote($term . '%'));
				}

				$query->order($db->quoteName('title') . ' ASC');
				$db->setQuery($query);

				$result = $db->loadObjectList();
			}
		}
		catch (RuntimeException $e)
		{
			KAComponentHelper::eventLog($e->getMessage());

			return false;
		}

		return $result;
	}

	/**
	 * Method to get list of genres based on filters.
	 *
	 * @return  object|boolean
	 *
	 * @since   3.1
	 */
	public function getGenres()
	{
		$db       = JFactory::getDbo();
		$id       = $this->input->get('id', 0, 'int');
		$multiple = $this->input->get('multiple', 0, 'int');
		$term     = $this->input->get('term', '', 'string');
		$ignore   = $this->input->get('ignore_ids', array(), 'array');
		$type     = $this->input->get('data_type', '', 'string');

		$query = $db->getQuery(true)
			->select($db->quoteName('id') . ', ' . $db->quoteName('name', 'text'))
			->from($db->quoteName('#__ka_genres'));

		// Filter by language
		if ($this->queryLang !== '')
		{
			$query->where($this->queryLang);
		}

		// Delete empty values from array of IDs
		$ignore = array_filter($ignore);

		// Filter results set by IDs
		if (!empty($ignore) && count($ignore) > 0)
		{
			$query->where($db->quoteName('id') . ' NOT IN (' . implode(',', $ignore) . ')');
		}

		// Filter by genre type
		if ($type !== '')
		{
			$query->where($db->quoteName('type') . ' IN (' . (string) $type . ')');
		}

		try
		{
			if (!empty($id) || $multiple === 1)
			{
				// Filter rows by IDs
				if ($multiple === 1)
				{
					$ids = $this->input->get('id', '', 'string');
					$ids = $this->sanitizeIDList($ids);

					$query->where($db->quoteName('id') . ' IN (' . $ids . ')')
						// Preserve row ordering
						->order('FIELD (' . $db->quoteName('id') . ', ' . $ids . ')');
					$db->setQuery($query);

					$result = $db->loadObjectList();
				}
				// Get single row by ID
				else
				{
					$query->where($db->quoteName('id') . ' = ' . (int) $id);
					$db->setQuery($query);

					$result = $db->loadObject();
				}
			}
			else
			{
				if (!empty($term))
				{
					$query->where($db->quoteName('name') . ' LIKE ' . $db->quote($term . '%'));
				}

				$query->order($db->quoteName('name') . ' ASC');
				$db->setQuery($query);

				$result = $db->loadObjectList();
			}
		}
		catch (RuntimeException $e)
		{
			KAComponentHelper::eventLog($e->getMessage());

			return false;
		}

		return $result;
	}

	/**
	 * Get cast and crew based on filters.
	 *
	 * @return  object|array
	 *
	 * @since   3.1
	 */
	public function getMovieCastAndCrew()
	{
		jimport('administrator.components.com_kinoarhiv.helpers.database', JPATH_ROOT);
		jimport('components.com_kinoarhiv.helpers.content', JPATH_ROOT);

		$db      = JFactory::getDbo();
		$id      = $this->input->get('id', 0, 'int');
		$page    = $this->input->get('page', 0, 'int');
		$orderby = $this->input->get('sidx', '1', 'string');
		$order   = $this->input->get('sord', 'asc', 'word');
		$field   = $this->input->get('searchField', '', 'cmd');
		$term    = $this->input->get('searchString', '', 'string');
		$operand = $this->input->get('searchOper', '', 'word');
		$careers = array();

		$query = $db->getQuery(true)
			->select($db->quoteName(array('id', 'title')))
			->from($db->quoteName('#__ka_names_career'))
			->order($db->quoteName('ordering') . ' ASC');

		$db->setQuery($query);

		try
		{
			$_careers = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			KAComponentHelper::eventLog($e->getMessage());

			return array();
		}

		foreach ($_careers as $career)
		{
			$careers[$career->id] = $career->title;
		}

		$query = $db->getQuery(true);

		$query->select($db->quoteName('n.id', 'name_id'))
			->select($db->quoteName(array('n.name', 'n.latin_name', 'n.date_of_birth', 't.id', 't.type', 't.role', 't.ordering')))
			->select(
				$db->quoteName('d.id', 'dub_id') . ',' . $db->quoteName('d.name', 'dub_name') . ','
				. $db->quoteName('d.latin_name', 'dub_latin_name') . ',' . $db->quoteName('d.date_of_birth', 'dub_date_of_birth')
			)
			->select("GROUP_CONCAT(" . $db->quoteName('r.role') . " SEPARATOR ', ') AS " . $db->quoteName('dub_role'))
			->from($db->quoteName('#__ka_names', 'n'))
			->leftJoin($db->quoteName('#__ka_rel_names', 't') . ' ON t.name_id = n.id AND t.movie_id = ' . (int) $id)
			->leftJoin($db->quoteName('#__ka_names', 'd') . ' ON ' . $db->quoteName('d.id') . ' = ' . $db->quoteName('t.dub_id'))
			->leftJoin($db->quoteName('#__ka_rel_names', 'r') . ' ON ' . $db->quoteName('r.dub_id') . ' = ' . $db->quoteName('n.id'));

			$subqueryWhere = $db->getQuery(true)
				->select($db->quoteName('name_id'))
				->from($db->quoteName('#__ka_rel_names'))
				->where($db->quoteName('movie_id') . ' = ' . (int) $id);

		$query->where($db->quoteName('n.id') . ' IN (' . $subqueryWhere . ')');

		if (!empty($term))
		{
			if ($field == 'n.name' || $field == 'd.name')
			{
				$query->where("("
					. KADatabaseHelper::transformOperands($db->quoteName($field), $operand, $db->escape($term))
					. " OR "
					. KADatabaseHelper::transformOperands($db->quoteName('n.latin_name'), $operand, $db->escape($term)) . ")"
				);
			}
			else
			{
				$query->where(KADatabaseHelper::transformOperands($db->quoteName($field), $operand, $db->escape($term)));
			}
		}

		$query->group($db->quoteName('t.id'));

		// Prevent 'ordering asc/desc, ordering asc/desc' duplication
		if (strpos($orderby, 'ordering') !== false)
		{
			$query->order($db->quoteName('t.ordering') . ' ASC');
		}
		else
		{
			// We need this if grid grouping is used. At the first(0) index - grouping field
			$ordRequest = explode(',', $orderby);

			if (count($ordRequest) > 1)
			{
				$query->order($db->quoteName(trim($ordRequest[1])) . ' ' . strtoupper($order) . ', ' . $db->quoteName('t.ordering') . ' ASC');
			}
			else
			{
				$query->order($db->quoteName(trim($orderby)) . ' ' . strtoupper($order) . ', ' . $db->quoteName('t.ordering') . ' ASC');
			}
		}

		$db->setQuery($query);

		try
		{
			$names = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			KAComponentHelper::eventLog($e->getMessage());

			return array();
		}

		// Presorting, based on the type of career person.
		$i = 0;
		$_result = array();

		foreach ($names as $value)
		{
			$name = KAContentHelper::formatItemTitle($value->name, $value->latin_name, $value->date_of_birth);
			$dub  = KAContentHelper::formatItemTitle($value->dub_name, $value->dub_latin_name, $value->dub_date_of_birth);

			foreach (explode(',', $value->type) as $k => $type)
			{
				$_result[$type][$i] = array(
					'id'       => $value->id,
					'name'     => $name,
					'name_id'  => $value->name_id,
					'role'     => $value->role,
					'dub_name' => $dub,
					'dub_id'   => $value->dub_id,
					'ordering' => $value->ordering,
					'type'     => $careers[$type],
					'type_id'  => $type
				);

				$i++;
			}
		}

		// The final sorting of array for the grid
		$k = 0;
		$result = (object) array('rows' => array());

		foreach ($_result as $row)
		{
			foreach ($row as $elem)
			{
				$result->rows[$k]['id']   = $elem['id'] . '_' . $elem['name_id'] . '_' . $elem['type_id'];
				$result->rows[$k]['cell'] = array(
					'row_id'   => $elem['id'],
					'name'     => $elem['name'],
					'name_id'  => $elem['name_id'],
					'role'     => $elem['role'],
					'dub_name' => $elem['dub_name'],
					'dub_id'   => $elem['dub_id'],
					'ordering' => $elem['ordering'],
					'type'     => $elem['type']
				);

				$k++;
			}
		}

		$result->page    = $page;
		$result->total   = 1;
		$result->records = count($result->rows);

		return $result;
	}

	/**
	 * Method to get list of name awards based on filters.
	 *
	 * @return  object
	 *
	 * @since   3.1
	 */
	public function getAlbumAwards()
	{
		return $this->getRelationAwards(2);
	}

	/**
	 * Method to get list of movie awards based on filters.
	 *
	 * @return  object
	 *
	 * @since   3.1
	 */
	public function getMovieAwards()
	{
		return $this->getRelationAwards(0);
	}

	/**
	 * Method to get list of name awards based on filters.
	 *
	 * @return  object
	 *
	 * @since   3.1
	 */
	public function getNameAwards()
	{
		return $this->getRelationAwards(1);
	}

	/**
	 * Method to get list of awards based on filters.
	 *
	 * @param   integer  $type  Content type. 0 - movie, 1 - name, 2 - album.
	 *
	 * @return  object|array|boolean  False on error.
	 *
	 * @since   3.1
	 */
	public function getRelationAwards($type)
	{
		jimport('administrator.components.com_kinoarhiv.helpers.database', JPATH_ROOT);

		$db         = JFactory::getDbo();
		$id         = $this->input->get('id', 0, 'int');
		$limit      = $this->input->get('rows', 25, 'int');
		$page       = $this->input->get('page', 0, 'int');
		$limitstart = $limit * $page - $limit;
		$limitstart = $limitstart <= 0 ? 0 : $limitstart;
		$orderby    = $this->input->get('sidx', '1', 'string');
		$order      = $this->input->get('sord', 'asc', 'word');
		$field      = $this->input->get('searchField', '', 'cmd');
		$term       = $this->input->get('searchString', '', 'string');
		$operand    = $this->input->get('searchOper', '', 'word');
		$result     = (object) array();
		$where      = "";

		if (!empty($term))
		{
			$where = " AND " . KADatabaseHelper::transformOperands($db->quoteName($field), $operand, $db->escape($term));
		}

		$query = $db->getQuery(true)
			->select('COUNT(rel.id)')
			->from($db->quoteName('#__ka_rel_awards', 'rel'))
			->where($db->quoteName('rel.type') . ' = ' . (int) $type)
			->where($db->quoteName('rel.item_id') . ' = ' . (int) $id . $where);

		$db->setQuery($query);

		try
		{
			$total = $db->loadResult();
		}
		catch (RuntimeException $e)
		{
			KAComponentHelper::eventLog($e->getMessage());

			return array();
		}

		$totalPages = ($total > 0) ? ceil($total / $limit) : 0;
		$page = ($page > $totalPages) ? $totalPages : $page;

		$query = $db->getQuery(true)
			->select(
				$db->quoteName(array('rel.id', 'rel.item_id', 'rel.award_id', 'rel.desc', 'rel.year', 'aw.title'))
			)
			->from($db->quoteName('#__ka_rel_awards', 'rel'))
			->leftJoin($db->quoteName('#__ka_awards', 'aw') . ' ON ' . $db->quoteName('aw.id') . ' = ' . $db->quoteName('rel.award_id'))
			->where($db->quoteName('rel.type') . ' = ' . (int) $type)
			->where($db->quoteName('rel.item_id') . ' = ' . (int) $id . $where)
			->order($db->quoteName($orderby) . ' ' . strtoupper($db->escape($order)))
			->setLimit($limit, $limitstart);

		$db->setQuery($query);

		try
		{
			$rows = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			KAComponentHelper::eventLog($e->getMessage());

			return false;
		}

		$result->rows    = $rows;
		$result->page    = $page;
		$result->total   = $totalPages;
		$result->records = (int) $total;

		return $result;
	}

	/**
	 * Method to get list of premieres based on filters.
	 *
	 * @return  object|array
	 *
	 * @since   3.1
	 */
	public function getMoviePremieres()
	{
		jimport('administrator.components.com_kinoarhiv.helpers.database', JPATH_ROOT);

		$db         = JFactory::getDbo();
		$id         = $this->input->get('id', 0, 'int');
		$limit      = $this->input->get('rows', 25, 'int');
		$page       = $this->input->get('page', 0, 'int');
		$limitstart = $limit * $page - $limit;
		$limitstart = $limitstart <= 0 ? 0 : $limitstart;
		$orderby    = $this->input->get('sidx', '1', 'string');
		$order      = $this->input->get('sord', 'asc', 'word');
		$field      = $this->input->get('searchField', '', 'cmd');
		$term       = $this->input->get('searchString', '', 'string');
		$operand    = $this->input->get('searchOper', '', 'word');
		$result     = (object) array();
		$where      = "";

		if (!empty($term))
		{
			$where = " AND " . KADatabaseHelper::transformOperands($db->quoteName($field), $operand, $db->escape($term));
		}

		$query = $db->getQuery(true)
			->select('COUNT(p.id)')
			->from($db->quoteName('#__ka_premieres', 'p'))
			->where($db->quoteName('p.movie_id') . ' = ' . (int) $id . $where);

		$db->setQuery($query);

		try
		{
			$total = $db->loadResult();
		}
		catch (RuntimeException $e)
		{
			KAComponentHelper::eventLog($e->getMessage());

			return array();
		}

		$totalPages = ($total > 0) ? ceil($total / $limit) : 0;
		$page = ($page > $totalPages) ? $totalPages : $page;

		$query = $db->getQuery(true)
			->select(
				$db->quoteName(
					array(
						'p.id', 'p.vendor_id', 'p.premiere_date', 'p.country_id', 'p.info', 'p.language', 'p.ordering',
						'cn.name', 'v.company_name'
					)
				)
			)
			->select($db->quoteName('l.title', 'lang'))
			->from($db->quoteName('#__ka_premieres', 'p'))
			->leftJoin($db->quoteName('#__ka_countries', 'cn') . ' ON ' . $db->quoteName('cn.id') . ' = ' . $db->quoteName('p.country_id'))
			->leftJoin($db->quoteName('#__ka_vendors', 'v') . ' ON ' . $db->quoteName('v.id') . ' = ' . $db->quoteName('p.vendor_id'))
			->leftJoin($db->quoteName('#__languages', 'l') . ' ON ' . $db->quoteName('l.lang_code') . ' = ' . $db->quoteName('p.language'))
			->where($db->quoteName('p.movie_id') . ' = ' . (int) $id . $where)
			->order($db->quoteName($orderby) . ' ' . strtoupper($db->escape($order)))
			->setLimit($limit, $limitstart);

		$db->setQuery($query);

		try
		{
			$rows = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			KAComponentHelper::eventLog($e->getMessage());

			return array();
		}

		foreach ($rows as $key => $item)
		{
			if ((int) $item->country_id == 0)
			{
				$rows[$key]->name = JText::_('COM_KA_PREMIERE_WORLD');
			}
		}

		$result->rows    = $rows;
		$result->page    = $page;
		$result->total   = $totalPages;
		$result->records = (int) $total;

		return $result;
	}

	/**
	 * Method to get list of releases based on filters.
	 *
	 * @return  object|array
	 *
	 * @since   3.1
	 */
	public function getReleases()
	{
		jimport('administrator.components.com_kinoarhiv.helpers.database', JPATH_ROOT);

		$db         = JFactory::getDbo();
		$id         = $this->input->get('id', 0, 'int');
		$limit      = $this->input->get('rows', 25, 'int');
		$page       = $this->input->get('page', 0, 'int');
		$limitstart = $limit * $page - $limit;
		$limitstart = $limitstart <= 0 ? 0 : $limitstart;
		$orderby    = $this->input->get('sidx', '1', 'string');
		$order      = $this->input->get('sord', 'asc', 'word');
		$field      = $this->input->get('searchField', '', 'cmd');
		$term       = $this->input->get('searchString', '', 'string');
		$operand    = $this->input->get('searchOper', '', 'word');
		$itemType   = $this->input->get('item_type', null, 'int');
		$result     = (object) array();
		$where      = "";

		if (!empty($term))
		{
			$where = " AND " . KADatabaseHelper::transformOperands($db->quoteName($field), $operand, $db->escape($term));
		}

		$query = $db->getQuery(true)
			->select('COUNT(r.id)')
			->from($db->quoteName('#__ka_releases', 'r'));

		if (!is_null($itemType))
		{
			$query->where($db->quoteName('r.item_type') . ' = ' . (int) $itemType);
		}

		$query->where($db->quoteName('r.item_id') . ' = ' . (int) $id . $where);

		$db->setQuery($query);

		try
		{
			$total = $db->loadResult();
		}
		catch (RuntimeException $e)
		{
			KAComponentHelper::eventLog($e->getMessage());

			return array();
		}

		$totalPages = ($total > 0) ? ceil($total / $limit) : 0;
		$page = ($page > $totalPages) ? $totalPages : $page;

		$query = $db->getQuery(true)
			->select(
				$db->quoteName(
					array(
						'r.id', 'r.country_id', 'r.vendor_id', 'r.media_type', 'r.item_type', 'r.release_date',
						'r.desc', 'r.language', 'r.ordering', 'cn.name', 'v.company_name', 'mt.title'
					)
				)
			)
			->select($db->quoteName('l.title', 'lang'))
			->from($db->quoteName('#__ka_releases', 'r'))
			->leftJoin($db->quoteName('#__ka_countries', 'cn') . ' ON ' . $db->quoteName('cn.id') . ' = ' . $db->quoteName('r.country_id'))
			->leftJoin($db->quoteName('#__ka_vendors', 'v') . ' ON ' . $db->quoteName('v.id') . ' = ' . $db->quoteName('r.vendor_id'))
			->leftJoin($db->quoteName('#__ka_media_types', 'mt') . ' ON ' . $db->quoteName('mt.id') . ' = ' . $db->quoteName('r.media_type'))
			->leftJoin($db->quoteName('#__languages', 'l') . ' ON ' . $db->quoteName('l.lang_code') . ' = ' . $db->quoteName('r.language'));

		if (!is_null($itemType))
		{
			$query->where($db->quoteName('r.item_type') . ' = ' . (int) $itemType);
		}

		$query->where($db->quoteName('r.item_id') . ' = ' . (int) $id . $where)
			->order($db->quoteName($orderby) . ' ' . strtoupper($db->escape($order)))
			->setLimit($limit, $limitstart);

		$db->setQuery($query);

		try
		{
			$rows = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			KAComponentHelper::eventLog($e->getMessage());

			return array();
		}

		$result->rows    = $rows;
		$result->page    = $page;
		$result->total   = $totalPages;
		$result->records = (int) $total;

		return $result;
	}

	/**
	 * Method to get list of album tracks based on filters.
	 *
	 * @return  object|array
	 *
	 * @since   3.1
	 */
	public function getAlbumTraks()
	{
		jimport('administrator.components.com_kinoarhiv.helpers.database', JPATH_ROOT);

		$db         = JFactory::getDbo();
		$id         = $this->input->get('id', 0, 'int');
		$limit      = $this->input->get('rows', 25, 'int');
		$page       = $this->input->get('page', 0, 'int');
		$limitstart = $limit * $page - $limit;
		$limitstart = $limitstart <= 0 ? 0 : $limitstart;
		$orderby    = $this->input->get('sidx', '1', 'string');
		$order      = $this->input->get('sord', 'asc', 'word');
		$field      = $this->input->get('searchField', '', 'cmd');
		$term       = $this->input->get('searchString', '', 'string');
		$operand    = $this->input->get('searchOper', '', 'word');
		$result     = (object) array();
		$where      = "";

		if (!empty($term))
		{
			$where = " AND " . KADatabaseHelper::transformOperands($db->quoteName($field), $operand, $db->escape($term));
		}

		$query = $db->getQuery(true)
			->select('COUNT(t.id)')
			->from($db->quoteName('#__ka_music', 't'))
			->where($db->quoteName('t.album_id') . ' = ' . (int) $id . $where);

		$db->setQuery($query);

		try
		{
			$total = $db->loadResult();
		}
		catch (RuntimeException $e)
		{
			KAComponentHelper::eventLog($e->getMessage());

			return array();
		}

		$totalPages = ($total > 0) ? ceil($total / $limit) : 0;
		$page = ($page > $totalPages) ? $totalPages : $page;

		$query = $db->getQuery(true)
			->select(
				$db->quoteName(
					array(
						't.id', 't.album_id', 't.title', 't.year', 't.length', 't.cd_number', 't.track_number', 't.filename'
					)
				)
			)
			->from($db->quoteName('#__ka_music', 't'))
			->where($db->quoteName('t.album_id') . ' = ' . (int) $id . $where)
			->order($db->quoteName($orderby) . ' ' . strtoupper($db->escape($order)))
			->setLimit($limit, $limitstart);

		$db->setQuery($query);

		try
		{
			$rows = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			KAComponentHelper::eventLog($e->getMessage());

			return array();
		}

		$result->rows    = $rows;
		$result->page    = $page;
		$result->total   = $totalPages;
		$result->records = (int) $total;

		return $result;
	}

	/**
	 * Get cast and crew based on filters.
	 *
	 * @return  object|array
	 *
	 * @since   3.1
	 */
	public function getAlbumCrew()
	{
		jimport('administrator.components.com_kinoarhiv.helpers.database', JPATH_ROOT);
		jimport('components.com_kinoarhiv.helpers.content', JPATH_ROOT);

		$db      = JFactory::getDbo();
		$id      = $this->input->get('id', 0, 'int');
		$page    = $this->input->get('page', 0, 'int');
		$orderby = $this->input->get('sidx', '1', 'string');
		$order   = $this->input->get('sord', 'asc', 'word');
		$field   = $this->input->get('searchField', '', 'cmd');
		$term    = $this->input->get('searchString', '', 'string');
		$operand = $this->input->get('searchOper', '', 'word');
		$query   = $db->getQuery(true);

		$query->select($db->quoteName('n.id', 'name_id'))
			->select($db->quoteName(array('n.name', 'n.latin_name', 'n.date_of_birth', 't.career_id', 't.id', 't.role', 't.ordering', 'c.title')))
			->from($db->quoteName('#__ka_names', 'n'))
			->leftJoin($db->quoteName('#__ka_music_rel_names', 't') . ' ON t.name_id = n.id AND t.item_id = ' . (int) $id)
			->leftJoin($db->quoteName('#__ka_names_career', 'c') . ' ON c.id = t.career_id');

		$subqueryWhere = $db->getQuery(true)
			->select($db->quoteName('name_id'))
			->from($db->quoteName('#__ka_music_rel_names'))
			->where($db->quoteName('item_id') . ' = ' . (int) $id)
			->where($db->quoteName('item_type') . ' = 0');

		$query->where($db->quoteName('n.id') . ' IN (' . $subqueryWhere . ')');

		if (!empty($term))
		{
			if ($field == 'n.name')
			{
				$query->where("("
					. KADatabaseHelper::transformOperands($db->quoteName($field), $operand, $db->escape($term))
					. " OR "
					. KADatabaseHelper::transformOperands($db->quoteName('n.latin_name'), $operand, $db->escape($term)) . ")"
				);
			}
			else
			{
				$query->where(KADatabaseHelper::transformOperands($db->quoteName($field), $operand, $db->escape($term)));
			}
		}

		$query->group($db->quoteName('t.id'));

		// Prevent 'ordering asc/desc, ordering asc/desc' duplication
		if (strpos($orderby, 'ordering') !== false)
		{
			$query->order($db->quoteName('t.career_id') . ' ASC');
		}
		else
		{
			// We need this if grid grouping is used. At the first(0) index - grouping field
			$ordRequest = explode(',', $orderby);

			if (count($ordRequest) > 1)
			{
				$query->order($db->quoteName(trim($ordRequest[1])) . ' ' . strtoupper($order) . ', ' . $db->quoteName('t.ordering') . ' ASC');
			}
			else
			{
				$query->order($db->quoteName(trim($orderby)) . ' ' . strtoupper($order) . ', ' . $db->quoteName('t.ordering') . ' ASC');
			}
		}

		$db->setQuery($query);

		try
		{
			$names = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			KAComponentHelper::eventLog($e->getMessage());

			return array();
		}

		$i = 0;
		$result = (object) array('rows' => array());

		foreach ($names as $value)
		{
			$result->rows[$i] = array(
				'id'       => $value->name_id . '_' . $value->career_id . '_' . $value->id,
				'row_id'   => $value->id,
				'name'     => KAContentHelper::formatItemTitle($value->name, $value->latin_name, $value->date_of_birth),
				'role'     => $value->role,
				'ordering' => $value->ordering,
				'type'     => $value->title,
				'type_id'  => $value->career_id
			);

			$i++;
		}

		$result->page    = $page;
		$result->total   = 1;
		$result->records = count($result->rows);

		return $result;
	}

	/**
	 * Sanitize list of IDs.
	 *
	 * @param   string  $ids  List of IDs separated by commas.
	 *
	 * @return  string
	 *
	 * @since  3.1
	 */
	public function sanitizeIDList($ids)
	{
		// Split by commas, ignore white space.
		$ids = preg_split('/[\s*,\s*]*,+[\s*,\s*]*/', trim($ids));

		// Make sure the item ids are integers
		$ids = Joomla\Utilities\ArrayHelper::toInteger($ids);

		return implode(',', $ids);
	}
}
