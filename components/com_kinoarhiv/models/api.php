<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *  
 * @copyright   Copyright (C) 2017 Libra.ms. All rights reserved.
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
	 * @return  object
	 *
	 * @since   3.1
	 */
	public function getCountries()
	{
		$id = $this->input->get('id', 0, 'int');
		$all = $this->input->get('showAll', 0, 'int');
		$multiple = $this->input->get('multiple', 0, 'int');
		$term = $this->input->get('term', '', 'string');

		// Do not remove `code` field from the query. It's necessary for flagging row in select
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('id') . ', ' . $this->db->quoteName('name', 'text') . ', ' . $this->db->quoteName('code'))
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

				$query->where($this->db->quoteName('name') . ' LIKE "' . $this->db->escape($term) . '%"')
					->order($this->db->quoteName('name') . ' ASC');
				$this->db->setQuery($query);

				try
				{
					$result = $this->db->loadObjectList();
				}
				catch (RuntimeException $e)
				{
					KAComponentHelper::eventLog($e->getMessage());

					return false;
				}
			}
			else
			{
				if ($multiple == 1)
				{
					// TODO Convert ID's into string
					$ids = $this->input->get('id', '', 'string');
					$query->where($this->db->quoteName('id') . ' IN (' . $this->sanitizeIDList($ids) . ')')
						->order($this->db->quoteName('name') . ' ASC');
					$this->db->setQuery($query);

					try
					{
						$result = $this->db->loadObjectList();
					}
					catch (RuntimeException $e)
					{
						KAComponentHelper::eventLog($e->getMessage());

						return false;
					}
				}
				else
				{
					$query->where($this->db->quoteName('id') . ' = ' . (int) $id);
					$this->db->setQuery($query);

					try
					{
						$result = $this->db->loadObject();
					}
					catch (RuntimeException $e)
					{
						KAComponentHelper::eventLog($e->getMessage());

						return false;
					}
				}
			}
		}
		else
		{
			$query->order($this->db->quoteName('name') . ' ASC');
			$this->db->setQuery($query);

			try
			{
				$result = $this->db->loadObjectList();
			}
			catch (RuntimeException $e)
			{
				KAComponentHelper::eventLog($e->getMessage());

				return false;
			}
		}

		return $result;
	}

	/**
	 * Method to get list of movies or movie based on filters.
	 *
	 * @return  object
	 *
	 * @since   3.1
	 */
	public function getMovies()
	{
		$id = $this->input->get('id', 0, 'int');
		$all = $this->input->get('showAll', 0, 'int');
		$term = $this->input->get('term', '', 'string');
		$ignore = $this->input->get('ignore_ids', array(), 'array');

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName(array('id', 'title', 'year')))
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
				$query->where($this->db->quoteName('title') . " LIKE '" . $this->db->escape($term) . "%'");
				$this->db->setQuery($query);

				try
				{
					$result = $this->db->loadObjectList();
				}
				catch (RuntimeException $e)
				{
					KAComponentHelper::eventLog($e->getMessage());

					return false;
				}
			}
			else
			{
				$query->where($this->db->quoteName('id') . ' = ' . (int) $id);
				$this->db->setQuery($query);

				try
				{
					$result = $this->db->loadObject();
				}
				catch (RuntimeException $e)
				{
					KAComponentHelper::eventLog($e->getMessage());

					return false;
				}
			}
		}
		else
		{
			$query->order($this->db->quoteName('title') . ' ASC');
			$this->db->setQuery($query);

			try
			{
				$result = $this->db->loadObjectList();
			}
			catch (RuntimeException $e)
			{
				KAComponentHelper::eventLog($e->getMessage());

				return false;
			}
		}

		return $result;
	}

	/**
	 * Method to get list of names or name based on filters.
	 *
	 * @return  object
	 *
	 * @since   3.1
	 */
	public function getNames()
	{
		$id = $this->input->get('id', 0, 'int');
		$all = $this->input->get('showAll', 0, 'int');
		$term = $this->input->get('term', '', 'string');
		$ignore = $this->input->get('ignore_ids', array(), 'array');

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName(array('id', 'name', 'latin_name', 'date_of_birth')))
			->from($this->db->quoteName('#__ka_names'));

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
				$query->where(
					$this->db->quoteName('name') . " LIKE '" . $this->db->escape($term) . "%' OR " .
					$this->db->quoteName('latin_name') . " LIKE '" . $this->db->escape($term) . "%'"
				);
				$this->db->setQuery($query);

				try
				{
					$result = $this->db->loadObjectList();
				}
				catch (RuntimeException $e)
				{
					KAComponentHelper::eventLog($e->getMessage());

					return false;
				}
			}
			else
			{
				$query->where($this->db->quoteName('id') . ' = ' . (int) $id);
				$this->db->setQuery($query);

				try
				{
					$result = $this->db->loadObject();
				}
				catch (RuntimeException $e)
				{
					KAComponentHelper::eventLog($e->getMessage());

					return false;
				}
			}
		}
		else
		{
			$query->order($this->db->quoteName('id') . ' ASC');
			$this->db->setQuery($query);

			try
			{
				$result = $this->db->loadObjectList();
			}
			catch (RuntimeException $e)
			{
				KAComponentHelper::eventLog($e->getMessage());

				return false;
			}
		}

		return $result;
	}

	/**
	 * Method to get list of awards or award based on filters.
	 *
	 * @return  object
	 *
	 * @since   3.1
	 */
	public function getAwards()
	{
		$id = $this->input->get('id', 0, 'int');
		$all = $this->input->get('showAll', 0, 'int');
		$term = $this->input->get('term', '', 'string');
		$ignore = $this->input->get('ignore_ids', array(), 'array');

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('id') . ', ' . $this->db->quoteName('title', 'text'))
			->from($this->db->quoteName('#__ka_awards'));

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

		// Filter by item state
		if ($this->query_state != '')
		{
			$query->where($this->query_state);
		}

		if (empty($all))
		{
			if (empty($id))
			{
				$query->where($this->db->quoteName('title') . " LIKE '" . $this->db->escape($term) . "%'")
					->order($this->db->quoteName('title') . ' ASC');
				$this->db->setQuery($query);

				try
				{
					$result = $this->db->loadObjectList();
				}
				catch (RuntimeException $e)
				{
					KAComponentHelper::eventLog($e->getMessage());

					return false;
				}
			}
			else
			{
				$query->where($this->db->quoteName('id') . ' = ' . (int) $id)
					->order($this->db->quoteName('title') . ' ASC');
				$this->db->setQuery($query);

				try
				{
					$result = $this->db->loadObject();
				}
				catch (RuntimeException $e)
				{
					KAComponentHelper::eventLog($e->getMessage());

					return false;
				}
			}
		}
		else
		{
			$query->order($this->db->quoteName('title') . ' ASC');
			$this->db->setQuery($query);

			try
			{
				$result = $this->db->loadObjectList();
			}
			catch (RuntimeException $e)
			{
				KAComponentHelper::eventLog($e->getMessage());

				return false;
			}
		}

		return $result;
	}

	/**
	 * Method to get list of distributors or distributor based on filters.
	 *
	 * @return  object
	 *
	 * @since   3.1
	 */
	public function getVendors()
	{
		$id = $this->input->get('id', 0, 'int');
		$all = $this->input->get('showAll', 0, 'int');
		$multiple = $this->input->get('multiple', 0, 'int');
		$term = $this->input->get('term', '', 'string');

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('id') . ', ' . $this->db->quoteName('company_name', 'text'))
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

				$query->where($this->db->quoteName('company_name') . " LIKE '" . $this->db->escape($term) . "%'")
					->order($this->db->quoteName('company_name') . ' ASC');
				$this->db->setQuery($query);

				try
				{
					$result = $this->db->loadObjectList();
				}
				catch (RuntimeException $e)
				{
					KAComponentHelper::eventLog($e->getMessage());

					return false;
				}
			}
			else
			{
				if ($multiple == 1)
				{
					// TODO Convert ID's into string
					$ids = $this->input->get('id', '', 'string');
					$query->where($this->db->quoteName('id') . ' IN (' . $this->sanitizeIDList($ids) . ')')
						->order($this->db->quoteName('company_name') . ' ASC');
					$this->db->setQuery($query);

					try
					{
						$result = $this->db->loadObjectList();
					}
					catch (RuntimeException $e)
					{
						KAComponentHelper::eventLog($e->getMessage());

						return false;
					}
				}
				else
				{
					$query->where($this->db->quoteName('id') . ' = ' . (int) $id);
					$this->db->setQuery($query);

					try
					{
						$result = $this->db->loadObject();
					}
					catch (RuntimeException $e)
					{
						KAComponentHelper::eventLog($e->getMessage());

						return false;
					}
				}
			}
		}
		else
		{
			$query->order($this->db->quoteName('company_name') . ' ASC');
			$this->db->setQuery($query);

			try
			{
				$result = $this->db->loadObjectList();
			}
			catch (RuntimeException $e)
			{
				KAComponentHelper::eventLog($e->getMessage());

				return false;
			}
		}

		return $result;
	}

	/**
	 * Method to get list of careers based on filters.
	 *
	 * @return  object
	 *
	 * @since   3.1
	 */
	public function getCareers()
	{
		$id       = $this->input->get('id', 0, 'int');
		$all      = $this->input->get('showAll', 0, 'int');
		$multiple = $this->input->get('multiple', 0, 'int');
		$term     = $this->input->get('term', '', 'string');

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('id') . ', ' . $this->db->quoteName('title', 'text'))
			->from($this->db->quoteName('#__ka_names_career'));

		// Filter by language
		if ($this->query_lang != '')
		{
			$query->where($this->query_lang);
		}

		if ($all == 0)
		{
			if ($id == 0)
			{
				if (empty($term))
				{
					return array();
				}

				$query->where($this->db->quoteName('title') . " LIKE '" . $this->db->escape($term) . "%'")
					->order($this->db->quoteName('ordering') . ' ASC');
				$this->db->setQuery($query);

				try
				{
					$result = $this->db->loadObjectList();
				}
				catch (RuntimeException $e)
				{
					KAComponentHelper::eventLog($e->getMessage());

					return false;
				}
			}
			else
			{
				if ($multiple == 1)
				{
					// TODO Convert ID's into string
					$ids = $this->sanitizeIDList($this->input->get('id', '', 'string'));

					$query->where($this->db->quoteName('id') . ' IN (' . $ids . ')')
						->order('FIELD(id, ' . $ids . ')');
					$this->db->setQuery($query);

					try
					{
						$result = $this->db->loadObjectList();
					}
					catch (RuntimeException $e)
					{
						KAComponentHelper::eventLog($e->getMessage());

						return false;
					}
				}
				else
				{
					$query->where($this->db->quoteName('id') . ' = ' . (int) $id);
					$this->db->setQuery($query);

					try
					{
						$result = $this->db->loadObject();
					}
					catch (RuntimeException $e)
					{
						KAComponentHelper::eventLog($e->getMessage());

						return false;
					}
				}
			}
		}
		else
		{
			$query->where($this->db->quoteName('title') . " LIKE '" . $this->db->escape($term) . "%'");
			$query->order($this->db->quoteName('ordering') . ', ' . $this->db->quoteName('title') . ' ASC');
			$this->db->setQuery($query);

			try
			{
				$result = $this->db->loadObjectList();
			}
			catch (RuntimeException $e)
			{
				KAComponentHelper::eventLog($e->getMessage());

				return false;
			}
		}

		return $result;
	}

	/**
	 * Method to get list of careers based on filters.
	 *
	 * @return  object
	 *
	 * @since   3.1
	 */
	public function getGenresmovie()
	{
		$id       = $this->input->get('id', 0, 'int');
		$all      = $this->input->get('showAll', 0, 'int');
		$multiple = $this->input->get('multiple', 0, 'int');
		$term     = $this->input->get('term', '', 'string');

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('id') . ', ' . $this->db->quoteName('name', 'text'))
			->from($this->db->quoteName('#__ka_genres'));

		// Filter by language
		if ($this->query_lang != '')
		{
			$query->where($this->query_lang);
		}

		if ($all == 0)
		{
			if ($id == 0)
			{
				if (empty($term))
				{
					return array();
				}

				$query->where($this->db->quoteName('name') . " LIKE '" . $this->db->escape($term) . "%'")
					->order($this->db->quoteName('name') . ' ASC');
				$this->db->setQuery($query);

				try
				{
					$result = $this->db->loadObjectList();
				}
				catch (RuntimeException $e)
				{
					KAComponentHelper::eventLog($e->getMessage());

					return false;
				}
			}
			else
			{
				if ($multiple == 1)
				{
					// TODO Convert ID's into string
					$ids = $this->sanitizeIDList($this->input->get('id', '', 'string'));

					$query->where($this->db->quoteName('id') . ' IN (' . $ids . ')')
						->order('FIELD(id, ' . $ids . ')');
					$this->db->setQuery($query);

					try
					{
						$result = $this->db->loadObjectList();
					}
					catch (RuntimeException $e)
					{
						KAComponentHelper::eventLog($e->getMessage());

						return false;
					}
				}
				else
				{
					$query->where($this->db->quoteName('id') . ' = ' . (int) $id);
					$this->db->setQuery($query);

					try
					{
						$result = $this->db->loadObject();
					}
					catch (RuntimeException $e)
					{
						KAComponentHelper::eventLog($e->getMessage());

						return false;
					}
				}
			}
		}
		else
		{
			$query->order($this->db->quoteName('name') . ' ASC');
			$this->db->setQuery($query);

			try
			{
				$result = $this->db->loadObjectList();
			}
			catch (RuntimeException $e)
			{
				KAComponentHelper::eventLog($e->getMessage());

				return false;
			}
		}

		return $result;
	}

	/**
	 * Get cast and crew based on filters.
	 *
	 * @return  object
	 *
	 * @since   3.1
	 */
	public function getMovieCastAndCrew()
	{
		jimport('administrator.components.com_kinoarhiv.helpers.database', JPATH_ROOT);
		jimport('components.com_kinoarhiv.helpers.content', JPATH_ROOT);

		$id      = $this->input->get('id', 0, 'int');
		$page    = $this->input->get('page', 0, 'int');
		$orderby = $this->input->get('sidx', '1', 'string');
		$order   = $this->input->get('sord', 'asc', 'word');
		$field   = $this->input->get('searchField', '', 'cmd');
		$term    = $this->input->get('searchString', '', 'string');
		$operand = $this->input->get('searchOper', '', 'word');
		$careers = array();

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName(array('id', 'title')))
			->from($this->db->quoteName('#__ka_names_career'))
			->order($this->db->quoteName('ordering') . ' ASC');

		$this->db->setQuery($query);

		try
		{
			$_careers = $this->db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			KAComponentHelper::eventLog($e->getMessage());

			return false;
		}

		foreach ($_careers as $career)
		{
			$careers[$career->id] = $career->title;
		}

		$query = $this->db->getQuery(true);

		$query->select($this->db->quoteName('n.id', 'name_id'))
			->select($this->db->quoteName(array('n.name', 'n.latin_name', 'n.date_of_birth', 't.type', 't.role', 't.ordering')))
			->select(
				$this->db->quoteName('d.id', 'dub_id') . ',' . $this->db->quoteName('d.name', 'dub_name') . ','
				. $this->db->quoteName('d.latin_name', 'dub_latin_name') . ',' . $this->db->quoteName('d.date_of_birth', 'dub_date_of_birth')
			)
			->select("GROUP_CONCAT(" . $this->db->quoteName('r.role') . " SEPARATOR ', ') AS " . $this->db->quoteName('dub_role'))
			->from($this->db->quoteName('#__ka_names', 'n'))
			->leftJoin($this->db->quoteName('#__ka_rel_names', 't') . ' ON t.name_id = n.id AND t.movie_id = ' . (int) $id)
			->leftJoin($this->db->quoteName('#__ka_names', 'd') . ' ON ' . $this->db->quoteName('d.id') . ' = ' . $this->db->quoteName('t.dub_id'))
			->leftJoin($this->db->quoteName('#__ka_rel_names', 'r') . ' ON ' . $this->db->quoteName('r.dub_id') . ' = ' . $this->db->quoteName('n.id'));

			$where_subquery = $this->db->getQuery(true)
				->select($this->db->quoteName('name_id'))
				->from($this->db->quoteName('#__ka_rel_names'))
				->where($this->db->quoteName('movie_id') . ' = ' . (int) $id);

		$query->where($this->db->quoteName('n.id') . ' IN (' . $where_subquery . ')');

		if (!empty($search_string))
		{
			if ($field == 'n.name' || $field == 'd.name')
			{
				$query->where("("
					. KADatabaseHelper::transformOperands($this->db->quoteName($field), $operand, $this->db->escape($search_string))
					. " OR "
					. KADatabaseHelper::transformOperands($this->db->quoteName('n.latin_name'), $operand, $this->db->escape($search_string))
				. ")");
			}
			else
			{
				$query->where(KADatabaseHelper::transformOperands($this->db->quoteName($field), $operand, $this->db->escape($search_string)));
			}
		}

		$query->group($this->db->quoteName('n.id'));

		// Prevent 'ordering asc/desc, ordering asc/desc' duplication
		if (strpos($orderby, 'ordering') !== false)
		{
			$query->order($this->db->quoteName('t.ordering') . ' ASC');
		}
		else
		{
			// We need this if grid grouping is used. At the first(0) index - grouping field
			$ord_request = explode(',', $orderby);

			if (count($ord_request) > 1)
			{
				$query->order($this->db->quoteName(trim($ord_request[1])) . ' ' . strtoupper($order) . ', ' . $this->db->quoteName('t.ordering') . ' ASC');
			}
			else
			{
				$query->order($this->db->quoteName(trim($orderby)) . ' ' . strtoupper($order) . ', ' . $this->db->quoteName('t.ordering') . ' ASC');
			}
		}

		$this->db->setQuery($query);

		try
		{
			$names = $this->db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			KAComponentHelper::eventLog($e->getMessage());

			return false;
		}

		// Presorting, based on the type of career person.
		$i = 0;
		$_result = array();

		foreach ($names as $value)
		{
			$name     = KAContentHelper::formatItemTitle($value->name, $value->latin_name, $value->date_of_birth);
			$dub_name = KAContentHelper::formatItemTitle($value->dub_name, $value->dub_latin_name, $value->dub_date_of_birth);

			foreach (explode(',', $value->type) as $k => $type)
			{
				$_result[$type][$i] = array(
					'name'     => $name,
					'name_id'  => $value->name_id,
					'role'     => $value->role,
					'dub_name' => $dub_name,
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
				$result->rows[$k]['id']   = $elem['name_id'] . '_' . $elem['type_id'];
				$result->rows[$k]['cell'] = array(
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
	public function getNameAwards()
	{
		return $this->getRelationAwards(1);
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
	 * Method to get list of awards based on filters.
	 *
	 * @param   integer  $type  Content type. 0 - movie, 1 - name.
	 *
	 * @return  object
	 *
	 * @since   3.1
	 */
	public function getRelationAwards($type)
	{
		jimport('administrator.components.com_kinoarhiv.helpers.database', JPATH_ROOT);

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
			$where = " AND " . KADatabaseHelper::transformOperands($this->db->quoteName($field), $operand, $this->db->escape($term));
		}

		$query = $this->db->getQuery(true)
			->select('COUNT(rel.id)')
			->from($this->db->quoteName('#__ka_rel_awards', 'rel'))
			->where($this->db->quoteName('rel.item_id') . ' = ' . (int) $id . $where);

		$this->db->setQuery($query);

		try
		{
			$total = $this->db->loadResult();
		}
		catch (RuntimeException $e)
		{
			KAComponentHelper::eventLog($e->getMessage());

			return array();
		}

		$total_pages = ($total > 0) ? ceil($total / $limit) : 0;
		$page = ($page > $total_pages) ? $total_pages : $page;

		$query = $this->db->getQuery(true)
			->select(
				$this->db->quoteName(array('rel.id', 'rel.item_id', 'rel.award_id', 'rel.desc', 'rel.year', 'aw.title'))
			)
			->from($this->db->quoteName('#__ka_rel_awards', 'rel'))
			->leftJoin($this->db->quoteName('#__ka_awards', 'aw') . ' ON ' . $this->db->quoteName('aw.id') . ' = ' . $this->db->quoteName('rel.award_id'))
			->where($this->db->quoteName('rel.type') . ' = ' . (int) $type . ' AND ' . $this->db->quoteName('rel.item_id') . ' = ' . (int) $id . $where)
			->order($this->db->quoteName($orderby) . ' ' . strtoupper($this->db->escape($order)))
			->setLimit($limit, $limitstart);

		$this->db->setQuery($query);

		try
		{
			$rows = $this->db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			KAComponentHelper::eventLog($e->getMessage());

			return false;
		}

		$result->rows    = $rows;
		$result->page    = $page;
		$result->total   = $total_pages;
		$result->records = (int) $total;

		return $result;
	}

	/**
	 * Method to get list of premieres based on filters.
	 *
	 * @return  object
	 *
	 * @since   3.1
	 */
	public function getMoviePremieres()
	{
		jimport('administrator.components.com_kinoarhiv.helpers.database', JPATH_ROOT);

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
			$where = " AND " . KADatabaseHelper::transformOperands($this->db->quoteName($field), $operand, $this->db->escape($term));
		}

		$query = $this->db->getQuery(true)
			->select('COUNT(p.id)')
			->from($this->db->quoteName('#__ka_premieres', 'p'))
			->where($this->db->quoteName('p.movie_id') . ' = ' . (int) $id . $where);

		$this->db->setQuery($query);

		try
		{
			$total = $this->db->loadResult();
		}
		catch (RuntimeException $e)
		{
			KAComponentHelper::eventLog($e->getMessage());

			return array();
		}

		$total_pages = ($total > 0) ? ceil($total / $limit) : 0;
		$page = ($page > $total_pages) ? $total_pages : $page;

		$query = $this->db->getQuery(true)
			->select(
				$this->db->quoteName(
					array(
						'p.id', 'p.vendor_id', 'p.premiere_date', 'p.country_id', 'p.info', 'p.language', 'p.ordering',
						'cn.name', 'v.company_name'
					)
				)
			)
			->select($this->db->quoteName('l.title', 'lang'))
			->from($this->db->quoteName('#__ka_premieres', 'p'))
			->leftJoin($this->db->quoteName('#__ka_countries', 'cn') . ' ON ' . $this->db->quoteName('cn.id') . ' = ' . $this->db->quoteName('p.country_id'))
			->leftJoin($this->db->quoteName('#__ka_vendors', 'v') . ' ON ' . $this->db->quoteName('v.id') . ' = ' . $this->db->quoteName('p.vendor_id'))
			->leftJoin($this->db->quoteName('#__languages', 'l') . ' ON ' . $this->db->quoteName('l.lang_code') . ' = ' . $this->db->quoteName('p.language'))
			->where($this->db->quoteName('p.movie_id') . ' = ' . (int) $id . $where)
			->order($this->db->quoteName($orderby) . ' ' . strtoupper($this->db->escape($order)))
			->setLimit($limit, $limitstart);

		$this->db->setQuery($query);

		try
		{
			$rows = $this->db->loadObjectList();
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
		$result->total   = $total_pages;
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
	public function getMovieReleases()
	{
		jimport('administrator.components.com_kinoarhiv.helpers.database', JPATH_ROOT);

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
			$where = " AND " . KADatabaseHelper::transformOperands($this->db->quoteName($field), $operand, $this->db->escape($term));
		}

		$query = $this->db->getQuery(true)
			->select('COUNT(r.id)')
			->from($this->db->quoteName('#__ka_releases', 'r'))
			->where($this->db->quoteName('r.movie_id') . ' = ' . (int) $id . $where);

		$this->db->setQuery($query);

		try
		{
			$total = $this->db->loadResult();
		}
		catch (RuntimeException $e)
		{
			KAComponentHelper::eventLog($e->getMessage());

			return array();
		}

		$total_pages = ($total > 0) ? ceil($total / $limit) : 0;
		$page = ($page > $total_pages) ? $total_pages : $page;

		$query = $this->db->getQuery(true)
			->select(
				$this->db->quoteName(
					array(
						'r.id', 'r.country_id', 'r.vendor_id', 'r.media_type', 'r.release_date', 'r.desc', 'r.language',
						'r.ordering', 'cn.name', 'v.company_name', 'mt.title'
					)
				)
			)
			->select($this->db->quoteName('l.title', 'lang'))
			->from($this->db->quoteName('#__ka_releases', 'r'))
			->leftJoin($this->db->quoteName('#__ka_countries', 'cn') . ' ON ' . $this->db->quoteName('cn.id') . ' = ' . $this->db->quoteName('r.country_id'))
			->leftJoin($this->db->quoteName('#__ka_vendors', 'v') . ' ON ' . $this->db->quoteName('v.id') . ' = ' . $this->db->quoteName('r.vendor_id'))
			->leftJoin($this->db->quoteName('#__ka_media_types', 'mt') . ' ON ' . $this->db->quoteName('mt.id') . ' = ' . $this->db->quoteName('r.media_type'))
			->leftJoin($this->db->quoteName('#__languages', 'l') . ' ON ' . $this->db->quoteName('l.lang_code') . ' = ' . $this->db->quoteName('r.language'))
			->where($this->db->quoteName('r.movie_id') . ' = ' . (int) $id . $where)
			->order($this->db->quoteName($orderby) . ' ' . strtoupper($this->db->escape($order)))
			->setLimit($limit, $limitstart);

		$this->db->setQuery($query);

		try
		{
			$rows = $this->db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			KAComponentHelper::eventLog($e->getMessage());

			return array();
		}

		$result->rows    = $rows;
		$result->page    = $page;
		$result->total   = $total_pages;
		$result->records = (int) $total;

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
