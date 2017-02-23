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

jimport('components.com_kinoarhiv.models.api', JPATH_ROOT);

/**
 * Global model class to provide an API for backend.
 *
 * @since  3.1
 */
class KinoarhivModelAPIBackend extends KinoarhivModelAPI
{
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
		parent::__construct();
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
}
