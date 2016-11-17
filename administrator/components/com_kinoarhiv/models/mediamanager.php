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

use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

JLoader::register('KAMedia', JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'media.php');

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
				'language', 'g.language');
		}

		$input = JFactory::getApplication()->input;

		// Adjust context to support different active filters for gallery and trailers.
		if ($input->get('type', '', 'word') == 'trailers')
		{
			$this->context = 'com_kinoarhiv_mediamanager_trailers';
		}
		else
		{
			$this->context = 'com_kinoarhiv_mediamanager_' . $input->get('section', '', 'word') . '_gallery';
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
		parent::populateState('g.filename', 'asc');
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
	 * @throws  Exception
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
			throw new Exception('Empty JDatabaseQuery in ' . __METHOD__, 500);
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
					array('g.id', 'g.title', 'g.embed_code', 'g.filename', 'g.duration', 'g._subtitles', 'g._chapters',
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
				$query->where('(g.filename LIKE ' . $search . ')');
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
	 * Method to get a list of articles.
	 * Overridden to add a check for access levels.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   3.0
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
	 * Method to get a single record for trailer.
	 *
	 * @return  mixed  Object on success, false on failure.
	 *
	 * @since  3.0
	 */
	public function getItem()
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$id = $app->input->get('item_id', 0, 'int');

		$query->select(
			$db->quoteName(
				array('g.id', 'g.movie_id', 'g.title', 'g.embed_code', 'g.screenshot', 'g.urls', 'g.filename',
					'g.resolution', 'g.dar', 'g.duration', 'g._subtitles', 'g._chapters', 'g.frontpage', 'g.access',
					'g.state', 'g.language', 'g.is_movie'
				)
			)
		)
			->from($db->quoteName('#__ka_trailers', 'g'));

		$query->select($db->quoteName(array('m.alias', 'm.fs_alias')))
			->leftJoin($db->quoteName('#__ka_movies', 'm') . ' ON `m`.`id` = `g`.`movie_id`');

		$query->select($db->quoteName('l.title', 'language_title'))
			->leftJoin($db->quoteName('#__languages', 'l') . ' ON `l`.`lang_code` = `g`.`language`');

		$query->select($db->quoteName('ag.title', 'access_level'))
			->leftJoin($db->quoteName('#__viewlevels', 'ag') . ' ON ag.id = g.access');

		$query->where($db->quoteName('g.id') . ' = ' . $id);

		$db->setQuery($query);
		$result = $db->loadObject();

		return $result;
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
			$data = '';

			if (!empty($result->name))
			{
				$data .= $result->name;
			}

			if (!empty($result->name) && !empty($result->latin_name))
			{
				$data .= ' / ';
			}

			if (!empty($result->latin_name))
			{
				$data .= $result->latin_name;
			}
		}
		else
		{
			$app->enqueueMessage('Unknown section type!', 'error');

			return false;
		}

		return $data;
	}

	/**
	 * Method to get the filesystem path to a file.
	 *
	 * @param   string   $section  Type of the item. Can be 'movie' or 'name'.
	 * @param   string   $type     Type of the section. Can be 'gallery', 'trailers', 'soundtracks'
	 * @param   integer  $tab      Tab number from gallery.
	 * @param   integer  $id       The item ID (movie or name).
	 *
	 * @return  string   Absolute filesystem path to a file.
	 *
	 * @since   3.0
	 */
	public function getPath($section = '', $type = '', $tab = 0, $id = 0)
	{
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$path = JPATH_ROOT . DIRECTORY_SEPARATOR . 'tmp';
		$folder = '';
		$section = empty($section) ? $app->input->get('section', '', 'word') : $section;
		$type = empty($type) ? $app->input->get('type', '', 'word') : $type;
		$tab = empty($tab) ? $app->input->get('tab', 0, 'int') : $tab;
		$id = empty($id) ? $app->input->get('id', 0, 'int') : $id;

		if ($section == 'movie')
		{
			if ($type == 'gallery')
			{
				if ($tab == 1)
				{
					$path = $params->get('media_wallpapers_root');
					$folder = 'wallpapers';
				}
				elseif ($tab == 2)
				{
					$path = $params->get('media_posters_root');
					$folder = 'posters';
				}
				elseif ($tab == 3)
				{
					$path = $params->get('media_scr_root');
					$folder = 'screenshots';
				}
			}
			elseif ($type == 'trailers')
			{
				$path = $params->get('media_trailers_root');
				$folder = '';
			}
		}
		elseif ($section == 'name')
		{
			if ($type == 'gallery')
			{
				if ($tab == 1)
				{
					$path = $params->get('media_actor_wallpapers_root');
					$folder = 'wallpapers';
				}
				elseif ($tab == 2)
				{
					$path = $params->get('media_actor_posters_root');
					$folder = 'posters';
				}
				elseif ($tab == 3)
				{
					$path = $params->get('media_actor_photo_root');
					$folder = 'photo';
				}
			}
		}
		else
		{
			return false;
		}

		$fs_alias = $this->getFilesystemAlias($section, $id);

		$result = JPath::clean($path . DIRECTORY_SEPARATOR . $fs_alias . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . $folder);

		return $result;
	}

	/**
	 * Method to get an item alias for filesystem.
	 *
	 * @param   string   $section        Type of the item. Can be 'movie' or 'name'.
	 * @param   string   $id             The item ID (movie or name).
	 * @param   boolean  $content_alias  Return first letter of content alias(`alias` field).
	 *
	 * @return  string  URL safe alias
	 *
	 * @since   3.0
	 */
	public function getFilesystemAlias($section, $id, $content_alias=false)
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$result = (object) array();
		$id = empty($id) ? $app->input->get('id', 0, 'int') : $id;
		$section = empty($section) ? $app->input->get('section', '', 'word') : $section;

		if ($section == 'movie')
		{
			$table = '#__ka_movies';
		}
		elseif ($section == 'name')
		{
			$table = '#__ka_names';
		}
		else
		{
			KAComponentHelper::eventLog('Wrong section type!');

			return false;
		}

		$col = $content_alias ? 'alias' : 'fs_alias';
		$query = $db->getQuery(true)
			->select($db->quoteName($col))
			->from($db->quoteName($table))
			->where($db->quoteName('id') . ' = ' . (int) $id);

		$db->setQuery($query);
		$fs_alias = $db->loadResult();

		if (empty($fs_alias))
		{
			if ($section == 'movie')
			{
				$query = $db->getQuery(true)
					->select($db->quoteName('title'))
					->from($db->quoteName($table))
					->where($db->quoteName('id') . ' = ' . (int) $id);

				$db->setQuery($query);
				$result = $db->loadResult();
			}
			elseif ($section == 'name')
			{
				$query = $db->getQuery(true)
					->select($db->quoteName(array('name', 'latin_name')))
					->from($db->quoteName($table))
					->where($db->quoteName('id') . ' = ' . (int) $id);

				$db->setQuery($query);
				$result = $db->loadObject();

				if (!empty($result->latin_name))
				{
					$result = $result->latin_name;
				}
				else
				{
					$result = $result->name;
				}
			}

			$result = JPath::clean($result);
			$fs_alias = rawurlencode(StringHelper::substr($result, 0, 1));
		}

		return $fs_alias;
	}

	/**
	 * Method to save image information into DB. Accepted gallery items for movie and poster for trailer.
	 *
	 * @param   resource  $image        Image source.
	 * @param   string    $filename     System filename.
	 * @param   array     $image_sizes  Array with the sizes. array(width, height)
	 * @param   integer   $section      Section. (Movie, name, trailer, soundtrack)
	 * @param   integer   $item_type    Item type. (Poster or wallpaper or photo or screenshot)
	 * @param   integer   $item_id      Item ID.
	 * @param   integer   $frontpage    Item published on frontpage.
	 *
	 * @return  mixed
	 *
	 * @since   3.0
	 */
	public function saveImageInDB($image = null, $filename, $image_sizes = array(), $section, $item_type = null, $item_id, $frontpage = 0)
	{
		if (empty($section))
		{
			return array('success' => false, 'filename' => $filename, 'id' => 0);
		}

		$db = $this->getDbo();
		$result = array();
		$image_sizes = (count($image_sizes) == 0) ? array(0 => 0, 1 => 0) : $image_sizes;
		$dimension = floor($image_sizes[0]) . 'x' . floor($image_sizes[1]);

		if ($section == 'movie')
		{
			$query = $db->getQuery(true);

			$query->insert($db->quoteName('#__ka_movies_gallery'), 'id')
				->columns($db->quoteName(array('id', 'filename', 'dimension', 'movie_id', 'type', 'frontpage', 'state')))
				->values("'', '" . $filename . "', '" . $dimension . "', '" . (int) $item_id . "', '" . (int) $item_type . "', '" . (int) $frontpage . "', '1'");
			$db->setQuery($query);

			try
			{
				$result['success'] = $db->execute();
				$result['filename'] = $filename;
				$result['id'] = $db->insertid();

				// Unpublish all items from frontpage for type of poster and movie and not for a last inserted row.
				if ($frontpage == 1)
				{
					$query = $db->getQuery(true);

					$query->update($db->quoteName('#__ka_movies_gallery'))
						->set($db->quoteName('frontpage') . " = '0'")
						->where($db->quoteName('movie_id') . ' = ' . (int) $item_id . ' AND ' . $db->quoteName('type') . ' = 2')
						->where($db->quoteName('id') . ' != ' . $result['id']);
					$db->setQuery($query);
					$db->execute();
				}
			}
			catch (Exception $e)
			{
				$result['success'] = false;
				$result['filename'] = $filename;
				$result['id'] = 0;

				return false;
			}
		}
		elseif ($section == 'name')
		{
			$query = $db->getQuery(true);

			$query->insert($db->quoteName('#__ka_names_gallery'), 'id')
				->columns($db->quoteName(array('id', 'filename', 'dimension', 'name_id', 'type', 'frontpage', 'state')))
				->values("'', '" . $filename . "', '" . $dimension . "', '" . (int) $item_id . "', '" . (int) $item_type . "', '" . (int) $frontpage . "', '1'");
			$db->setQuery($query);

			try
			{
				$result['success'] = $db->execute();
				$result['filename'] = $filename;
				$result['id'] = $db->insertid();

				// Unpublish all items from frontpage for type of photo and name and not for a last inserted row.
				if ($frontpage == 1)
				{
					$query = $db->getQuery(true);

					$query->update($db->quoteName('#__ka_names_gallery'))
						->set($db->quoteName('frontpage') . " = '0'")
						->where($db->quoteName('name_id') . ' = ' . (int) $item_id . ' AND ' . $db->quoteName('type') . ' = 3')
						->where($db->quoteName('id') . ' != ' . $result['id']);
					$db->setQuery($query);
					$db->execute();
				}
			}
			catch (Exception $e)
			{
				$result['success'] = false;
				$result['filename'] = $filename;
				$result['id'] = 0;

				return false;
			}
		}
		elseif ($section == 'trailer')
		{
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__ka_trailers'))
				->set($db->quoteName('screenshot') . " = '" . $filename . "'")
				->where($db->quoteName('id') . ' = ' . (int) $item_id);
			$db->setQuery($query);
			$result['success'] = (bool) $db->execute();
			$result['filename'] = $filename;
		}

		return $result;
	}

	/**
	 * Method to publish or unpublish posters or trailer on movie info page(not on posters or trailers page)
	 *
	 * @param   integer  $action   0 - unpublish from frontpage, 1 - publish poster on frontpage.
	 * @param   integer  $type     Item type.
	 * @param   integer  $item_id  Item ID.
	 * @param   array    $id       Array of IDs which must be published or unpublished.
	 * @param   string   $section  Movie or name.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.0
	 */
	public function publishOnFrontpage($action, $type = null, $item_id = 0, $id = array(), $section = null)
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$type = $app->input->get('type', $type, 'word');
		$item_id = $app->input->get('id', $item_id, 'int');
		$id = $app->input->get('_id', $id, 'array');
		$section = $app->input->get('section', $section, 'word');

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
				$app->enqueueMessage('Unknown gallery type');

				return false;
			}

			// Reset all values to 0
			$query = $db->getQuery(true)
				->update($db->quoteName($table))
				->set($db->quoteName($pub_col) . " = '0'")
				->where($db->quoteName($item_col) . ' = ' . (int) $item_id . ' AND ' . $db->quoteName('type') . ' = ' . $type_num);
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				$app->enqueueMessage($e->getMessage());

				return false;
			}

			if (!isset($id[0]) || empty($id[0]))
			{
				$app->enqueueMessage('Unknown ID');

				return false;
			}

			$query = $db->getQuery(true)
				->update($db->quoteName($table))
				->set($db->quoteName($pub_col) . " = '" . (int) $action . "'")
				->where($db->quoteName('id') . ' = ' . (int) $id[0]);
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				$app->enqueueMessage($e->getMessage());

				return false;
			}
		}
		elseif ($type == 'trailers')
		{
			// We need to check if this is the movie to avoid errors when publishing a movie and trailer
			$query = $db->getQuery(true)
				->select('is_movie')
				->from($db->quoteName('#__ka_trailers'))
				->where($db->quoteName('id') . ' = ' . (int) $id[0]);
			$db->setQuery($query);
			$is_movie = $db->loadResult();

			if ($is_movie == 0)
			{
				// Reset all values to 0
				$query = $db->getQuery(true)
					->update($db->quoteName('#__ka_trailers'))
					->set($db->quoteName('frontpage') . " = '0'")
					->where($db->quoteName('movie_id') . ' = ' . (int) $item_id . ' AND ' . $db->quoteName('is_movie') . ' = 0');
				$db->setQuery($query);
			}
			else
			{
				// Reset all values to 0
				$query = $db->getQuery(true)
					->update($db->quoteName('#__ka_trailers'))
					->set($db->quoteName('frontpage') . " = '0'")
					->where($db->quoteName('movie_id') . ' = ' . (int) $item_id . ' AND ' . $db->quoteName('is_movie') . ' = 1');
				$db->setQuery($query);
			}

			try
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				$app->enqueueMessage($e->getMessage());

				return false;
			}

			if (!isset($id[0]) || empty($id[0]))
			{
				$app->enqueueMessage('Unknown ID');

				return false;
			}

			$query = $db->getQuery(true)
				->update($db->quoteName('#__ka_trailers'))
				->set($db->quoteName('frontpage') . " = '" . (int) $action . "'")
				->where($db->quoteName('id') . ' = ' . (int) $id[0]);
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				$app->enqueueMessage($e->getMessage());

				return false;
			}
		}

		return true;
	}

	/**
	 * Method to change the published state of one or more records.
	 *
	 * @param   integer  $action  Action state.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.0
	 */
	public function publish($action)
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$type = $app->input->get('type', '', 'word');
		$id = $app->input->get('_id', array(), 'array');
		$section = $app->input->get('section', null, 'word');

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
				$app->enqueueMessage('Unknown gallery type!');

				return false;
			}
		}
		elseif ($type == 'trailers')
		{
			$table = '#__ka_trailers';
		}
		else
		{
			$app->enqueueMessage('Unknown gallery!');

			return false;
		}

		$query = $db->getQuery(true);

		$query->update($db->quoteName($table))
			->set($db->quoteName('state') . " = '" . (int) $action . "'")
			->where($db->quoteName('id') . ' IN (' . implode(',', $id) . ')');
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage());

			return false;
		}

		return true;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  mixed  True on success, False on error, lastInsertID on trailer save.
	 *
	 * @since   3.0
	 */
	public function apply($data)
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$movie_id = $app->input->get('id', 0, 'int');
		$trailer_id = $app->input->get('item_id', 0, 'int');
		$type = $app->input->get('type', '', 'word');
		$section = $app->input->get('section', '', 'word');

		if ($section == 'movie')
		{
			if ($type == 'trailers')
			{
				if ($trailer_id == 0)
				{
					$query = $db->getQuery(true);

					$query->insert($db->quoteName('#__ka_trailers'))
						->columns(
							$db->quoteName(
								array('id', 'movie_id', 'title', 'embed_code', 'screenshot', 'urls', 'filename', 'resolution',
									'dar', 'duration', '_subtitles', '_chapters', 'frontpage', 'access', 'state', 'language', 'is_movie'
								)
							)
						)
						->values("'', '" . $movie_id . "', '" . $db->escape($data['title']) . "', '" . $db->escape($data['embed_code']) . "'")
						->values("'', '" . $db->escape($data['urls']) . "', '{}', '', '', '', '{}', '{}', '" . (int) $data['frontpage'] . "'")
						->values("'" . (int) $data['access'] . "', '" . (int) $data['state'] . "', '" . $data['language'] . "', '" . (int) $data['is_movie'] . "'");

					$db->setQuery($query);

					try
					{
						$db->execute();

						return $db->insertid();
					}
					catch (Exception $e)
					{
						$app->enqueueMessage($e->getMessage());

						return false;
					}
				}
				else
				{
					$query = $db->getQuery(true);

					$query->update($db->quoteName('#__ka_trailers'))
						->set($db->quoteName('title') . " = '" . $db->escape($data['title']) . "'," . $db->quoteName('embed_code') . " = '" . $data['embed_code'] . "'")
						->set($db->quoteName('urls') . " = '" . $data['urls'] . "'," . $db->quoteName('resolution') . " = '" . $data['resolution'] . "'")
						->set($db->quoteName('dar') . " = '" . $data['dar'] . "'," . $db->quoteName('duration') . " = '" . $data['duration'] . "'")
						->set($db->quoteName('frontpage') . " = '" . (int) $data['frontpage'] . "'," . $db->quoteName('access') . " = '" . (int) $data['access'] . "'")
						->set($db->quoteName('state') . " = '" . (int) $data['state'] . "'," . $db->quoteName('language') . " = '" . $data['language'] . "'")
						->set($db->quoteName('is_movie') . " = '" . $data['is_movie'] . "'")
						->where($db->quoteName('id') . ' = ' . (int) $trailer_id);
					$db->setQuery($query);

					try
					{
						$db->execute();
					}
					catch (Exception $e)
					{
						$app->enqueueMessage($e->getMessage());

						return false;
					}
				}
			}
		}

		return true;
	}

	/**
	 * Method to remove the images from database and filesystem except trailer files.
	 *
	 * @return  mixed  True on success, False on error, lastInsertID on trailer save.
	 *
	 * @since   3.0
	 */
	public function remove()
	{
		jimport('joomla.filesystem.file');

		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$item_id = $app->input->get('id', 0, 'int');
		$ids = $app->input->get('_id', array(), 'array');
		$section = $app->input->get('section', '', 'cmd');
		$type = $app->input->get('type', '', 'cmd');
		$tab = $app->input->get('tab', 0, 'int');
		$query_result = true;

		if ($section == 'movie')
		{
			if ($type == 'gallery')
			{
				if (empty($ids[0]))
				{
					echo JText::_('JERROR_AN_ERROR_HAS_OCCURRED');

					return false;
				}

				$query = $db->getQuery(true)
					->select($db->quoteName(array('id', 'filename')))
					->from($db->quoteName('#__ka_movies_gallery'))
					->where($db->quoteName('id') . ' IN (' . implode(',', $ids) . ')');
				$db->setQuery($query);

				try
				{
					$files_obj = $db->loadObjectList();

					if (count($files_obj) == 0)
					{
						echo JText::_('JERROR_AN_ERROR_HAS_OCCURRED');

						return false;
					}
				}
				catch (Exception $e)
				{
					$app->enqueueMessage($e->getMessage());

					return false;
				}

				$db->setDebug(true);
				$db->lockTable('#__ka_movies_gallery');
				$db->transactionStart();

				$path = $this->getPath('movie', 'gallery', $tab, $item_id) . '/';

				foreach ($files_obj as $file)
				{
					JFile::delete($path . $file->filename);
					JFile::delete($path . 'thumb_' . $file->filename);

					$query = $db->getQuery(true)
						->delete($db->quoteName('#__ka_movies_gallery'))
						->where($db->quoteName('id') . ' = ' . (int) $file->id);
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
					$app->enqueueMessage('Commit failed!');
				}
				else
				{
					$db->transactionCommit();
				}

				$db->unlockTables();
				$db->setDebug(false);
			}
			elseif ($type == 'trailers')
			{
				if (empty($ids[0]))
				{
					echo JText::_('JERROR_AN_ERROR_HAS_OCCURRED');

					return false;
				}

				$query = $db->getQuery(true)
					->select($db->quoteName(array('id', 'screenshot', 'filename', '_subtitles', '_chapters')))
					->from($db->quoteName('#__ka_trailers'))
					->where($db->quoteName('id') . ' IN (' . implode(',', $ids) . ')');
				$db->setQuery($query);

				try
				{
					$rows = $db->loadObjectList();

					if (count($rows) == 0)
					{
						echo JText::_('JERROR_AN_ERROR_HAS_OCCURRED');

						return false;
					}
				}
				catch (Exception $e)
				{
					$app->enqueueMessage($e->getMessage());

					return false;
				}

				$path = $this->getPath('movie', 'trailers', 0, $item_id);
				$db->setDebug(true);
				$db->lockTable('#__ka_trailers');
				$db->transactionStart();

				foreach ($rows as $row)
				{
					if (!empty($row->screenshot))
					{
						JFile::delete($path . $row->screenshot);
					}

					$video = json_decode($row->filename, true);

					if (count($video) > 0)
					{
						foreach ($video as $file)
						{
							JFile::delete($path . $file['src']);
						}
					}

					$subtitles = json_decode($row->_subtitles, true);

					if (count($subtitles) > 0)
					{
						foreach ($subtitles as $file)
						{
							JFile::delete($path . $file['file']);
						}
					}

					$chapters = json_decode($row->_chapters, true);

					if (count($chapters) > 0)
					{
						JFile::delete($path . $chapters['file']);
					}

					$query = $db->getQuery(true)
						->delete($db->quoteName('#__ka_trailers'))
						->where($db->quoteName('id') . ' = ' . (int) $row->id);
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
					$app->enqueueMessage('Commit failed!');
				}
				else
				{
					$db->transactionCommit();
				}

				$db->unlockTables();
				$db->setDebug(false);
			}
		}
		elseif ($section == 'name')
		{
			if ($type == 'gallery')
			{
				if (empty($ids[0]))
				{
					echo JText::_('JERROR_AN_ERROR_HAS_OCCURRED');

					return false;
				}

				$query = $db->getQuery(true)
					->select($db->quoteName(array('id', 'filename')))
					->from($db->quoteName('#__ka_names_gallery'))
					->where($db->quoteName('id') . ' IN (' . implode(',', $ids) . ')');
				$db->setQuery($query);

				try
				{
					$files_obj = $db->loadObjectList();

					if (count($files_obj) == 0)
					{
						echo JText::_('JERROR_AN_ERROR_HAS_OCCURRED');

						return false;
					}
				}
				catch (Exception $e)
				{
					$app->enqueueMessage($e->getMessage());

					return false;
				}

				$db->setDebug(true);
				$db->lockTable('#__ka_names_gallery');
				$db->transactionStart();

				$path = $this->getPath('name', 'gallery', $tab, $item_id) . '/';

				foreach ($files_obj as $file)
				{
					JFile::delete($path . $file->filename);
					JFile::delete($path . 'thumb_' . $file->filename);

					$query = $db->getQuery(true)
						->delete($db->quoteName('#__ka_names_gallery'))
						->where($db->quoteName('id') . ' = ' . (int) $file->id);
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
					$app->enqueueMessage('Commit failed!');
				}
				else
				{
					$db->transactionCommit();
				}

				$db->unlockTables();
				$db->setDebug(false);
			}
		}
		elseif ($section == 'music')
		{
			if ($type == 'gallery')
			{
				if (empty($ids[0]))
				{
					echo JText::_('JERROR_AN_ERROR_HAS_OCCURRED');

					return false;
				}

				// TODO Implement music gallery item remove code.
			}
		}
		else
		{
			$app->enqueueMessage(JText::_('COM_KA_ITEMS_DELETED_ERROR'));

			return false;
		}

		return true;
	}

	/**
	 * Method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure.
	 *                 Return true if not an 'edit' trailer call(avoid conflicts with filters and limits for gallery lists).
	 *
	 * @since   3.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$input = JFactory::getApplication()->input;
		$type  = $input->get('type', '', 'word');
		$task  = $input->get('task', '', 'cmd');

		if ($type == 'trailers' && $task == 'edit')
		{
			$form = $this->loadForm('com_kinoarhiv.mediamanager', 'mediamanager', array('control' => 'form', 'load_data' => $loadData));

			if (empty($form))
			{
				return false;
			}

			return $form;
		}

		return true;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The default data is an array. Otherwise method must return an array from parent overloaded method
	 *                 to avoid conflicts with filters and limits for gallery lists.
	 *
	 * @since   3.0
	 */
	protected function loadFormData()
	{
		$input = JFactory::getApplication()->input;
		$type  = $input->get('type', '', 'word');
		$task  = $input->get('task', '', 'cmd');

		if ($type == 'trailers' && $task == 'edit')
		{
			$data = JFactory::getApplication()->getUserState('com_kinoarhiv.trailers.' . JFactory::getUser()->id . '.edit_data', array());

			if (empty($data))
			{
				$data = $this->getItem();
			}

			return $data;
		}

		return parent::loadFormData();
	}

	/**
	 * Saves the manually set order of videofiles.
	 *
	 * @return  string
	 *
	 * @since   3.0
	 */
	public function saveOrderTrailerVideofile()
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$item_id = $app->input->get('item_id', 0, 'int');
		$items = $app->input->get('ord', array(), 'array');

		$query = $db->getQuery(true)
			->select($db->quoteName('filename'))
			->from($db->quoteName('#__ka_trailers'))
			->where($db->quoteName('id') . ' = ' . $item_id);

		$db->setQuery($query);
		$result = $db->loadResult();

		if (empty($result))
		{
			return json_encode(array('success' => false, 'message' => JText::_('JERROR_AN_ERROR_HAS_OCCURRED')));
		}

		$result_arr = json_decode($result, true);
		$new_arr = (object) array();

		foreach ($items as $new_index => $old_index)
		{
			foreach ($result_arr as $value)
			{
				$new_arr->$new_index = $result_arr[$old_index];
			}
		}

		$query = $db->getQuery(true)
			->update($db->quoteName('#__ka_trailers'))
			->set($db->quoteName('filename') . " = '" . json_encode($new_arr) . "'")
			->where($db->quoteName('id') . ' = ' . (int) $item_id);

		$db->setQuery($query);

		if ($db->execute() === false)
		{
			return json_encode(array('success' => false, 'message' => JText::_('JERROR_AN_ERROR_HAS_OCCURRED')));
		}

		return json_encode(array('success' => true, 'message' => ''));
	}

	/**
	 * Save default subtitle for trailer.
	 *
	 * @return  string
	 *
	 * @since   3.0
	 */
	public function saveDefaultTrailerSubtitlefile()
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$item_id = $app->input->get('item_id', 0, 'int');

		// Item ID in array of subtitles which should be default
		$id = $app->input->get('default', 0, 'int');

		$query = $db->getQuery(true)
			->select($db->quoteName('_subtitles'))
			->from($db->quoteName('#__ka_trailers'))
			->where($db->quoteName('id') . ' = ' . $item_id);

		$db->setQuery($query);
		$result = $db->loadResult();

		if (empty($result))
		{
			return json_encode(array('success' => false, 'message' => JText::_('JERROR_AN_ERROR_HAS_OCCURRED')));
		}

		$result_arr = json_decode($result);

		foreach ($result_arr as $key => $value)
		{
			$result_arr->$key->default = ($key != $id) ? (bool) false : (bool) true;
		}

		$query = $db->getQuery(true)
			->update($db->quoteName('#__ka_trailers'))
			->set($db->quoteName('_subtitles') . " = '" . json_encode($result_arr) . "'")
			->where($db->quoteName('id') . ' = ' . (int) $item_id);

		$db->setQuery($query);

		if ($db->execute() === false)
		{
			return json_encode(array('success' => false, 'message' => JText::_('JERROR_AN_ERROR_HAS_OCCURRED')));
		}

		return json_encode(array('success' => true, 'message' => ''));
	}

	/**
	 * Saves the manually set order of subtitles.
	 *
	 * @return  string
	 *
	 * @since   3.0
	 */
	public function saveOrderTrailerSubtitlefile()
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$item_id = $app->input->get('item_id', 0, 'int');
		$items = $app->input->get('cord', array(), 'array');

		$query = $db->getQuery(true)
			->select($db->quoteName('_subtitles'))
			->from($db->quoteName('#__ka_trailers'))
			->where($db->quoteName('id') . ' = ' . $item_id);

		$db->setQuery($query);
		$result = $db->loadResult();

		if (empty($result))
		{
			return json_encode(array('success' => false, 'message' => JText::_('JERROR_AN_ERROR_HAS_OCCURRED')));
		}

		$result_arr = json_decode($result, true);
		$new_arr = (object) array();

		foreach ($items as $new_index => $old_index)
		{
			foreach ($result_arr as $value)
			{
				$new_arr->$new_index = $result_arr[$old_index];
			}
		}

		$query = $db->getQuery(true)
			->update($db->quoteName('#__ka_trailers'))
			->set($db->quoteName('_subtitles') . " = '" . json_encode($new_arr) . "'")
			->where($db->quoteName('id') . ' = ' . (int) $item_id);

		$db->setQuery($query);

		if ($db->execute() === false)
		{
			return json_encode(array('success' => false, 'message' => JText::_('JERROR_AN_ERROR_HAS_OCCURRED')));
		}

		return json_encode(array('success' => true, 'message' => ''));
	}

	/**
	 * Save info about chapter file into DB
	 *
	 * @param   string   $file        Filename
	 * @param   integer  $trailer_id  ID of the trailer
	 * @param   integer  $movie_id    ID of the movie
	 *
	 * @return  mixed    Last insert ID on INSERT or true on UPDATE
	 *
	 * @since  3.0
	 */
	public function saveChapters($file, $trailer_id, $movie_id)
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select('COUNT(id)')
			->from($db->quoteName('#__ka_trailers'))
			->where($db->quoteName('id') . ' = ' . (int) $trailer_id);

		$db->setQuery($query);
		$total = $db->loadResult();

		$chapters = array('file' => $file);

		if ($total == 0)
		{
			$query = $db->getQuery(true);

			$query->insert($db->quoteName('#__ka_trailers'))
				->columns(
					$db->quoteName(
						array('id', 'movie_id', 'title', 'embed_code', 'screenshot', 'filename', 'duration',
							'_subtitles', '_chapters', 'frontpage', 'access', 'state', 'language', 'is_movie'
						)
					)
				)
				->values("'', '" . (int) $movie_id . "', '', '', '', '{}', '00:00:00', '{}', '" . $chapters . "', '0', '1', '0', 'language', '0'");

			$db->setQuery($query);
			$query = $db->execute();

			return $query ? (int) $db->insertid() : false;
		}
		else
		{
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__ka_trailers'))
				->set($db->quoteName('_chapters') . " = '" . json_encode($chapters) . "'")
				->where($db->quoteName('id') . ' = ' . (int) $trailer_id);

			$db->setQuery($query);
			$query = $db->execute();

			return $query ? true : false;
		}
	}

	/**
	 * Method to get videofile parameters from database for quick edit.
	 *
	 * @return  array
	 *
	 * @since  3.0
	 */
	public function getVideoDataEdit()
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$trailer_id = $app->input->get('trailer_id', 0, 'int');
		$video_id = $app->input->get('video_id', 0, 'int');
		$query = $db->getQuery(true);

		$query->select($db->quoteName('filename'))
			->from($db->quoteName('#__ka_trailers'))
			->where($db->quoteName('id') . ' = ' . (int) $trailer_id);

		$db->setQuery($query);
		$result = $db->loadResult();

		$file_obj = json_decode($result, true);

		return array(
			'src'        => $file_obj[$video_id]['src'],
			'type'       => $file_obj[$video_id]['type'],
			'resolution' => array_key_exists('resolution', $file_obj[$video_id]) ? $file_obj[$video_id]['resolution'] : '',
			'trailer_id' => $trailer_id,
			'video_id'   => $video_id
		);
	}

	/**
	 * Method to get videofile parameters from database for quick edit.
	 *
	 * @param   string   $trailer_id  ID of the trailer
	 * @param   integer  $video_id    ID of videofile from json array
	 * @param   integer  $movie_id    ID of the movie
	 *
	 * @return  string
	 *
	 * @since  3.0
	 */
	public function saveVideofileData($trailer_id, $video_id = null, $movie_id = null)
	{
		jimport('joomla.filesystem.file');

		$app = JFactory::getApplication();
		$db = $this->getDbo();

		if (is_null($video_id))
		{
			return 'ID cannot be null!';
		}

		$query = $db->getQuery(true);

		$query->select($db->quoteName('filename'))
			->from($db->quoteName('#__ka_trailers'))
			->where($db->quoteName('id') . ' = ' . (int) $trailer_id);

		$db->setQuery($query);
		$result = $db->loadResult();

		$file_arr = json_decode($result, true);
		$old_filename = JPath::clean($file_arr[$video_id]['src']);
		$new_filename = JPath::clean($app->input->get('src', '', 'string'));

		$file_arr[$video_id] = array(
			'src'        => $app->input->get('src', '', 'string'),
			'type'       => $app->input->get('type', '', 'string'),
			'resolution' => $app->input->get('resolution', '', 'string')
		);

		$file_obj = json_encode((object) $file_arr);

		$query = $db->getQuery(true);

		$query->update($db->quoteName('#__ka_trailers'))
			->set($db->quoteName('filename') . " = '" . $file_obj . "'")
			->where($db->quoteName('id') . ' = ' . (int) $trailer_id);

		$db->setQuery($query);

		try
		{
			$db->execute();

			// Rename the file
			$path = $this->getPath('movie', 'trailers', 0, $movie_id);

			if ($app->input->get('src_rename', 0, 'int') == 1 && (file_exists($path . $old_filename) && is_file($path . $old_filename)))
			{
				if (rename($path . $old_filename, $path . $new_filename) === false)
				{
					return JText::_('ERROR');
				}
			}
		}
		catch (Exception $e)
		{
			return $e->getMessage();
		}

		return JText::_('COM_KA_SAVED');
	}

	/**
	 * Method to get subtitle parameters for trailer from database for quick edit.
	 *
	 * @return  string
	 *
	 * @since  3.0
	 */
	public function getSubtitleEdit()
	{
		// TODO Use jimport
		JLoader::register('KALanguage', JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'language.php');

		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$lang_list = KALanguage::listOfLanguages();
		$trailer_id = $app->input->get('trailer_id', 0, 'int');
		$subtitle_id = $app->input->get('subtitle_id', 0, 'int');

		$query = $db->getQuery(true)
			->select($db->quoteName('_subtitles'))
			->from($db->quoteName('#__ka_trailers'))
			->where($db->quoteName('id') . ' = ' . (int) $trailer_id);

		$db->setQuery($query);
		$result = $db->loadResult();

		$subtl_obj = json_decode($result);

		return array(
			'langs'       => $lang_list,
			'lang_code'   => $subtl_obj->$subtitle_id->lang_code,
			'lang'        => $subtl_obj->$subtitle_id->lang,
			'is_default'  => $subtl_obj->$subtitle_id->default,
			'trailer_id'  => $trailer_id,
			'subtitle_id' => $subtitle_id
		);
	}

	/**
	 * Save info about subtitles file into DB
	 *
	 * @param   string   $file         Filename
	 * @param   integer  $trailer_id   ID of the trailer
	 * @param   integer  $movie_id     ID of the movie
	 * @param   integer  $subtitle_id  ID of the movie
	 * @param   boolean  $edit         If set to true, we save info from subtitle information edit form.
	 *
	 * @return  mixed    Last insert ID on INSERT or true on UPDATE
	 *
	 * @since  3.0
	 */
	public function saveSubtitles($file, $trailer_id, $movie_id = 0, $subtitle_id = null, $edit = false)
	{
		jimport('joomla.filesystem.file');
		JLoader::register('KALanguage', JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'language.php');

		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$lang_list = KALanguage::listOfLanguages();

		$query = $db->getQuery(true)
			->select($db->quoteName('_subtitles'))
			->from($db->quoteName('#__ka_trailers'))
			->where($db->quoteName('id') . ' = ' . (int) $trailer_id);

		$db->setQuery($query);
		$result = $db->loadResult();

		if ($edit === true)
		{
			$subtl_obj = json_decode($result);
			$lang_data = json_decode($app->input->get('language', '', 'string'));
			$default = $app->input->get('default', 'false', 'string');
			$desc = $app->input->get('desc', '', 'string');
			$desc = $desc != '' ? ' ' . $desc : '';

			if (isset($subtl_obj->$subtitle_id))
			{
				if ($default == 'true')
				{
					// Set to false all 'default' flags
					foreach ($subtl_obj as $key => $subtl)
					{
						$subtl_obj->$key->default = false;
					}

					$subtl_obj->$subtitle_id->default = true;
				}

				$subtl_obj->$subtitle_id->lang_code = $lang_data->lang_code;
				$subtl_obj->$subtitle_id->lang = $lang_data->lang . $desc;

				$fs_alias = $this->getFilesystemAlias('movie', $movie_id, true);
				$rn_dest_dir = $this->getPath('movie', 'trailers', 0, $movie_id);
				$old_filename = $rn_dest_dir . $subtl_obj->$subtitle_id->file;
				$ext = JFile::getExt($old_filename);
				$rn_filename = $fs_alias . '-' . $trailer_id . '.subtitles.' . $lang_data->lang_code . '.' . $ext;
				$subtl_obj->$subtitle_id->file = $rn_filename;

				rename($old_filename, $rn_dest_dir . $rn_filename);
			}

			$query = $db->getQuery(true)
				->update($db->quoteName('#__ka_trailers'))
				->set($db->quoteName('_subtitles') . " = '" . $db->escape(json_encode($subtl_obj)) . "'")
				->where($db->quoteName('id') . ' = ' . (int) $trailer_id);

			$db->setQuery($query);

			try
			{
				$db->execute();
				$result = true;
			}
			catch (Exception $e)
			{
				return false;
			}
		}
		else
		{
			$subtl_arr = json_decode($result, true);

			/* On 'else' condition we do nothing because no information about trailer exists in DB.
			 * In this situation files will be successfully uploaded, but not saved in DB.
			*/
			if (!empty($trailer_id))
			{
				if (preg_match('#subtitles\.(.*?)\.#si', $file, $matches))
				{
					// Get the language code from filename
					$lang_code = strtolower($matches[1]);
				}
				else
				{
					// Default set to English as it required for proper display
					$lang_code = 'en';
				}

				/* Uncomment block below if you want to check for duplicate languages.
				 Checking if lang allready exists and return false.*/
				/*$lang_exists = false;

				foreach ($subtl_arr as $k=>$v) {
					if ($v['lang_code'] == $lang_code) {
						$lang_exists = true;
						break;
					}
				}

				if ($lang_exists) {
					return false;
				}*/

				$subtl_arr[] = array(
					'default'   => false,
					'lang_code' => $lang_code,
					'lang'      => $lang_list[$lang_code],
					'file'      => $file
				);

				$subtl_obj = ArrayHelper::toObject($subtl_arr);
				$query = $db->getQuery(true);

				$query->update($db->quoteName('#__ka_trailers'))
					->set($db->quoteName('_subtitles') . " = '" . $db->escape(json_encode($subtl_obj)) . "'")
					->where($db->quoteName('id') . ' = ' . (int) $trailer_id);

				$db->setQuery($query);

				try
				{
					$db->execute();
					$result = true;
				}
				catch (Exception $e)
				{
					return false;
				}
			}
		}

		return $result;
	}

	/**
	 * Method to create screenshot from videofile store it in filesystem and database.
	 *
	 * @return  mixed
	 *
	 * @since   3.0
	 */
	public function create_screenshot()
	{
		jimport('joomla.filesystem.file');

		$media = KAMedia::getInstance();
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$id = $app->input->get('id', 0, 'int');
		$trailer_id = $app->input->get('item_id', 0, 'int');
		$time = $app->input->get('time', '', 'string');

		$query = $db->getQuery(true)
			->select($db->quoteName(array('tr.screenshot', 'tr.filename')))
			->from($db->quoteName('#__ka_trailers', 'tr'))
			->join('LEFT', $db->quoteName('#__ka_movies', 'm') . ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('tr.movie_id'))
			->where($db->quoteName('tr.id') . ' = ' . (int) $trailer_id);

		$db->setQuery($query);
		$result = $db->loadObject();
		$files = json_decode($result->filename, true);

		if (empty($files))
		{
			return 'error:' . JText::_('COM_KA_TRAILERS_VIDEO_SCREENSHOT_CREATE_FILE_ERR');
		}

		$data = array(
			'folder'     => $this->getPath('movie', 'trailers', 0, $id),
			'screenshot' => $result->screenshot,
			'filename'   => $files[0]['src'],
			'time'       => $time
		);

		if ($time != '00:00:00.000')
		{
			if (preg_match('#^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])\.[0-1][0-9][0-9]?$#', $time))
			{
				$query = $db->getQuery(true)
					->update($db->quoteName('#__ka_trailers'))
					->set($db->quoteName('screenshot') . " = '" . $files[0]['src'] . ".png'")
					->where($db->quoteName('id') . ' = ' . (int) $trailer_id);

				$db->setQuery($query);
				$db->execute();

				$output = $media->createVideoScreenshot($data);

				if ($output)
				{
					if ($output[0])
					{
						return json_encode(array('file' => $output[0], 'output' => $output[1]));
					}
					else
					{
						return 'error:' . $output[1];
					}
				}
				else
				{
					return 'error:' . JText::sprintf(
						'COM_KA_MEDIAMANAGER_FFMPEG_NOTFOUND',
						JComponentHelper::getParams('com_kinoarhiv')->get('ffmpeg_path') . ', ' . JComponentHelper::getParams('com_kinoarhiv')->get('ffprobe_path')
					);
				}
			}
		}

		return 'error:' . JText::_('COM_KA_TRAILERS_VIDEO_SCREENSHOT_CREATE_TIME_ERR');
	}

	/**
	 * Method to save information about uploaded videofile into JSON object and store it in database.
	 *
	 * @param   string   $filename    Filename to process and store.
	 * @param   integer  $trailer_id  Trailer ID.
	 * @param   integer  $movie_id    Movie ID.
	 *
	 * @return  mixed   Array of filtered data if valid, false otherwise.
	 *
	 * @since   3.0
	 */
	public function saveVideo($filename, $trailer_id, $movie_id)
	{
		$media = KAMedia::getInstance();
		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select($db->quoteName('filename'))
			->from($db->quoteName('#__ka_trailers'))
			->where($db->quoteName('id') . ' = ' . (int) $trailer_id);

		$db->setQuery($query);
		$result = $db->loadResult();

		$result_arr = json_decode($result, true);

		// If not empty and items more than 0 when add to array and store
		if (!empty($result) && count($result_arr) > 0)
		{
			// Checking if file allready exists in DB
			$file_exists = false;

			foreach ($result_arr as $k => $v)
			{
				if ($v['src'] == $filename)
				{
					$file_exists = true;
					break;
				}
			}

			if ($file_exists)
			{
				return false;
			}
			// End

			$files_arr = $result_arr;
			$mime_type = $media->detectMime($this->getPath('movie', 'trailers', 0, $movie_id) . $filename);
			$video_info = json_decode($media->getVideoInfo($this->getPath('movie', 'trailers', 0, $movie_id) . $filename));
			$duration = $media->getVideoDuration($this->getPath('movie', 'trailers', 0, $movie_id) . $filename, true);

			if (is_array($duration))
			{
				$duration = '00:00:00:000';
			}

			if (is_object($video_info))
			{
				$stream_width  = !isset($video_info->streams[0]->width) ? 0 : $video_info->streams[0]->width;
				$stream_height = !isset($video_info->streams[0]->height) ? 0 : $video_info->streams[0]->height;
				$stream_dar    = !isset($video_info->streams[0]->display_aspect_ratio) ? '16x9' : $video_info->streams[0]->display_aspect_ratio;
			}
			else
			{
				$stream_width  = 0;
				$stream_height = 0;
				$stream_dar    = '16x9';
			}

			$files_arr[] = array(
				'src'        => $filename,
				'type'       => $mime_type,
				'resolution' => $stream_width . 'x' . $stream_height
			);

			$new_obj = ArrayHelper::toObject($files_arr);
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__ka_trailers'))
				->set($db->quoteName('filename') . " = '" . json_encode($new_obj) . "'")
				->set($db->quoteName('resolution') . " = '" . $stream_width . 'x' . $stream_height . "'")
				->set($db->quoteName('dar') . " = '" . $stream_dar . "'")
				->set($db->quoteName('duration') . " = '" . $duration . "'")
				->where($db->quoteName('id') . ' = ' . (int) $trailer_id);

			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				return false;
			}
		}
		else
		{
			$mime_type = $media->detectMime($this->getPath('movie', 'trailers', 0, $movie_id) . $filename);
			$video_info = $media->getVideoInfo($this->getPath('movie', 'trailers', 0, $movie_id) . $filename);

			if ($video_info === false)
			{
				KAComponentHelper::eventLog(
					JText::sprintf(
						'COM_KA_MEDIAMANAGER_FFMPEG_NOTFOUND',
						JComponentHelper::getParams('com_kinoarhiv')->get('ffmpeg_path') . ', ' . JComponentHelper::getParams('com_kinoarhiv')->get('ffprobe_path')
					)
				);
			}

			$video_info = json_decode($video_info);
			$duration = $media->getVideoDuration($this->getPath('movie', 'trailers', 0, $movie_id) . $filename, true);

			if (is_array($duration))
			{
				$duration = '00:00:00:000';
			}

			if (is_object($video_info))
			{
				$stream_width  = !isset($video_info->streams[0]->width) ? 0 : $video_info->streams[0]->width;
				$stream_height = !isset($video_info->streams[0]->height) ? 0 : $video_info->streams[0]->height;
				$stream_dar    = !isset($video_info->streams[0]->display_aspect_ratio) ? '16x9' : $video_info->streams[0]->display_aspect_ratio;
			}
			else
			{
				$stream_width  = 0;
				$stream_height = 0;
				$stream_dar    = '16x9';
			}

			$files_arr = array(
				0 => array(
					'src'        => $filename,
					'type'       => $mime_type,
					'resolution' => $stream_width . 'x' . $stream_height
				)
			);

			$new_obj = ArrayHelper::toObject($files_arr);
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__ka_trailers'))
				->set($db->quoteName('filename') . " = '" . json_encode($new_obj) . "'")
				->set($db->quoteName('resolution') . " = '" . $stream_width . 'x' . $stream_height . "'")
				->set($db->quoteName('dar') . " = '" . $stream_dar . "'")
				->set($db->quoteName('duration') . " = '" . $duration . "'")
				->where($db->quoteName('id') . ' = ' . (int) $trailer_id);

			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Remove a file associated with trailer.
	 *
	 * @return  string
	 *
	 * @since   3.0
	 */
	public function removeTrailerFiles()
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$id = $app->input->get('id', 0, 'int');
		$item_id = $app->input->get('item_id', 0, 'int');
		$filename = $app->input->get('file', '', 'string');
		$type = $app->input->get('type', '', 'word');
		$success = true;
		$message = '';

		if ($type == '')
		{
			return json_encode(array('success' => false, 'message' => JText::_('JERROR_AN_ERROR_HAS_OCCURRED')));
		}

		if ($type == 'video')
		{
			$query = $db->getQuery(true)
				->select($db->quoteName('filename'))
				->from($db->quoteName('#__ka_trailers'))
				->where($db->quoteName('id') . ' = ' . (int) $item_id);

			$db->setQuery($query);
			$result = $db->loadResult();

			if (empty($result))
			{
				return json_encode(array('success' => false, 'message' => JText::_('JERROR_AN_ERROR_HAS_OCCURRED')));
			}

			$result_arr = json_decode($result, true);
			$new_arr = array();

			foreach ($result_arr as $k => $v)
			{
				if ($v['src'] != $filename)
				{
					$new_arr[] = $v;
				}
			}

			$new_arr = ArrayHelper::toObject($new_arr);
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__ka_trailers'))
				->set($db->quoteName('filename') . " = '" . json_encode($new_arr) . "'")
				->where($db->quoteName('id') . ' = ' . (int) $item_id);

			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				return json_encode(array('success' => false, 'message' => JText::_('JERROR_AN_ERROR_HAS_OCCURRED')));
			}

			// Remove file
			$sys_path = $this->getPath('movie', 'trailers', 0, $id) . $filename;

			if (!is_file($sys_path))
			{
				$success = false;
				$message = JText::_('JERROR_AN_ERROR_HAS_OCCURRED');
			}

			if (@unlink($sys_path) !== true)
			{
				return json_encode(array('success' => false, 'message' => JText::_('JERROR_AN_ERROR_HAS_OCCURRED')));
			}
		}
		elseif ($type == 'subtitle' || $type == 'subtitles')
		{
			$query = $db->getQuery(true)
				->select($db->quoteName('_subtitles'))
				->from($db->quoteName('#__ka_trailers'))
				->where($db->quoteName('id') . ' = ' . (int) $item_id);

			$db->setQuery($query);
			$result = $db->loadResult();

			if (empty($result))
			{
				return json_encode(array('success' => false, 'message' => JText::_('JERROR_AN_ERROR_HAS_OCCURRED')));
			}

			$result_arr = json_decode($result, true);

			if ($app->input->get('all', 0, 'int') == 0)
			{
				$new_arr = array();

				foreach ($result_arr as $k => $v)
				{
					if ($v['file'] != $filename)
					{
						$new_arr[] = $v;
					}
				}

				$new_arr = ArrayHelper::toObject($new_arr);
				$query = $db->getQuery(true);

				$query->update($db->quoteName('#__ka_trailers'))
					->set($db->quoteName('_subtitles') . " = '" . json_encode($new_arr) . "'")
					->where($db->quoteName('id') . ' = ' . (int) $item_id);

				$db->setQuery($query);

				try
				{
					$db->execute();
				}
				catch (Exception $e)
				{
					return json_encode(array('success' => false, 'message' => JText::_('JERROR_AN_ERROR_HAS_OCCURRED')));
				}

				// Remove file
				$sys_path = $this->getPath('movie', 'trailers', 0, $id) . $filename;

				if (file_exists($sys_path) && @unlink($sys_path) !== true)
				{
					return json_encode(array('success' => false, 'message' => JText::_('JERROR_AN_ERROR_HAS_OCCURRED')));
				}
			}
			else
			{
				foreach ($result_arr as $val)
				{
					$sys_path = $this->getPath('movie', 'trailers', 0, $id) . $val['file'];

					if (file_exists($sys_path) && @unlink($sys_path) !== true)
					{
						$success = false;
						$message .= JText::_('JERROR_AN_ERROR_HAS_OCCURRED') . ': ' . $sys_path . "\n";
					}
				}

				$query = $db->getQuery(true);

				$query->update($db->quoteName('#__ka_trailers'))
					->set($db->quoteName('_subtitles') . " = '{}'")
					->where($db->quoteName('id') . ' = ' . (int) $item_id);

				$db->setQuery($query);

				try
				{
					$db->execute();
				}
				catch (Exception $e)
				{
					return json_encode(array('success' => false, 'message' => JText::_('JERROR_AN_ERROR_HAS_OCCURRED')));
				}
			}
		}
		elseif ($type == 'chapter' || $type == 'chapters')
		{
			$query = $db->getQuery(true)
				->update($db->quoteName('#__ka_trailers'))
				->set($db->quoteName('_chapters') . " = '{}'")
				->where($db->quoteName('id') . ' = ' . (int) $item_id);

			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				return json_encode(array('success' => false, 'message' => JText::_('JERROR_AN_ERROR_HAS_OCCURRED')));
			}

			// Remove file
			$sys_path = $this->getPath('movie', 'trailers', 0, $id) . $filename;

			if (!is_file($sys_path))
			{
				$success = false;
				$message = JText::_('JERROR_AN_ERROR_HAS_OCCURRED');
			}

			if (@unlink($sys_path) !== true)
			{
				$success = false;
				$message = JText::_('JERROR_AN_ERROR_HAS_OCCURRED');
			}
		}
		elseif ($type == 'image' || $type == 'images')
		{
			$query = $db->getQuery(true)
				->update($db->quoteName('#__ka_trailers'))
				->set($db->quoteName('screenshot') . " = ''")
				->where($db->quoteName('id') . ' = ' . (int) $item_id);

			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				return json_encode(array('success' => false, 'message' => JText::_('JERROR_AN_ERROR_HAS_OCCURRED')));
			}

			// Remove file
			$sys_path = $this->getPath('movie', 'trailers', 0, $id) . $filename;

			if (!is_file($sys_path))
			{
				$success = false;
				$message = JText::_('JERROR_AN_ERROR_HAS_OCCURRED');
			}

			if (@unlink($sys_path) !== true)
			{
				$success = false;
				$message = JText::_('JERROR_AN_ERROR_HAS_OCCURRED');
			}
		}

		return json_encode(array('success' => $success, 'message' => $message));
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param   JForm   $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  mixed   Array of filtered data if valid, false otherwise.
	 *
	 * @see     JFormRule
	 * @see     JFilterInput
	 * @since   12.2
	 */
	public function validate($form, $data, $group = null)
	{
		// Filter and validate the form data.
		$data = $form->filter($data);
		$return = $form->validate($data, $group);

		// Check for an error.
		if ($return instanceof Exception)
		{
			$this->setError($return->getMessage());

			return false;
		}

		// Check the validation results.
		if ($return === false)
		{
			// Get the validation messages from the form.
			foreach ($form->getErrors() as $message)
			{
				$this->setError($message);
			}

			return false;
		}

		return $data;
	}

	/**
	 * Method for copy items from gallery from one movie to another.
	 *
	 * @return  mixed    Object with the data. False on error.
	 *
	 * @since  3.0
	 */
	public function copyfrom()
	{
		JLoader::register('KAFilesystemHelper', JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'filesystem.php');

		$db = $this->getDbo();
		$app = JFactory::getApplication();

		// Current item ID.
		$id = $app->input->get('id', 0, 'int');

		// Item ID. Can be 'movie' or 'name' ID.
		$item_id = $app->input->get('item_id', 0, 'int');

		// Item type. Can be 'gallery', 'trailers', 'soundtracks'
		$item_type = $app->input->get('item_type', '', 'word');

		// Item subtype. 1 - wallpapers, 2 - posters, 3 - screenshots(photo for names). Only available if we copy from gallery.
		$item_subtype = $app->input->get('item_subtype', 0, 'int');

		$section = $app->input->get('section', '', 'word');
		$src_path = $this->getPath($section, $item_type, $item_subtype, $item_id);
		$dst_path = $this->getPath($section, $item_type, $item_subtype, $id);
		$query_result = true;

		// Copy selected folders
		if (KAFilesystemHelper::move($src_path, $dst_path, true) === false)
		{
			$app->enqueueMessage('Something went wrong! See Joomla logs for details.');

			return false;
		}

		// Update DB
		if ($item_type == 'gallery')
		{
			if ($section == 'movie')
			{
				$table = '#__ka_movies_gallery';
				$col = 'movie_id';
			}
			elseif ($section == 'name')
			{
				$table = '#__ka_names_gallery';
				$col = 'name_id';
			}
			else
			{
				return false;
			}

			$cols_obj = $db->getTableColumns($table);
			$_keys = $db->quoteName(array_keys($cols_obj));
			$cols = implode(', ', $_keys);
			$cols_count = count($_keys);

			$query = $db->getQuery(true)
				->select($cols)
				->from($db->quoteName($table))
				->where($db->quoteName($col) . ' = ' . (int) $item_id . ' AND ' . $db->quoteName('type') . ' = ' . (int) $item_subtype);

			$db->setQuery($query);
			$data = $db->loadObjectList();

			$db->setDebug(true);
			$db->lockTable($table);
			$db->transactionStart();

			foreach ($data as $values)
			{
				$value = "";
				$i = 0;

				foreach ($values as $key => $val)
				{
					if ($key == 'id')
					{
						$value .= "''";
					}
					else
					{
						if ($key == $col)
						{
							$value .= "'" . (int) $id . "'";
						}
						else
						{
							$value .= "'" . $db->escape($val) . "'";
						}
					}

					if ($i + 1 != $cols_count)
					{
						$value .= ', ';
					}

					$i++;
				}

				$query = $db->getQuery(true)
					->insert($db->quoteName($table))
					->columns($cols)
					->values($value);

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
		}
		else
		{
			$app->enqueueMessage('Wrong item type');

			return false;
		}

		return true;
	}
}
