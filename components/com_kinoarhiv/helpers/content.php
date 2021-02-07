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

use Joomla\String\StringHelper;

/**
 * Content helper class
 *
 * @since  3.0
 */
class KAContentHelper
{
	/**
	 * Get image size
	 *
	 * @param   string   $path        Path to image file
	 * @param   boolean  $scale       Scale image or not. Preserve proportions.
	 * @param   integer  $baseWidth   Base image width from settings
	 * @param   string   $dimension   Default image dimension
	 *
	 * @return  array
	 *
	 * @since  3.0
	 */
	public static function getImageSize($path, $scale = true, $baseWidth = 0, $dimension = '128x128')
	{
		$image = array('width' => 0, 'height' => 0);

		if ($scale)
		{
			$image['width'] = (int) $baseWidth;
			$origSize = explode('x', $dimension);

			if (!isset($origSize[1]) || empty($origSize[0]) || empty($origSize[1]))
			{
				$origSize[0] = '128';
				$origSize[1] = '128';
			}

			$image['height'] = floor(($image['width'] * $origSize[1]) / $origSize[0]);
		}
		else
		{
			list($width, $height) = @getimagesize($path);
			$image['width'] = $width;
			$image['height'] = $height;
		}

		return $image;
	}

	/**
	 * Format item title. If item have two fields for title, sometimes we need to properly process title if item
	 * does not have one of these fields.
	 *
	 * @param   string  $firstTitle   First item title.
	 * @param   string  $secondTitle  Second item title.
	 * @param   string  $date         Show date.
	 * @param   string  $separator    Separator to split titles.
	 *
	 * @return  string
	 *
	 * @since  3.0
	 */
	public static function formatItemTitle($firstTitle, $secondTitle, $date = '', $separator = ' / ')
	{
		if (empty($firstTitle) && empty($secondTitle))
		{
			return '';
		}

		$title = '';

		if (!empty($firstTitle))
		{
			$title .= $firstTitle;
		}

		if (!empty($firstTitle) && !empty($secondTitle))
		{
			$title .= $separator;
		}

		if (!empty($secondTitle))
		{
			$title .= $secondTitle;
		}

		if (!empty($date) && ($date != '0000' && $date != '0000-00' && $date != '0000-00-00'))
		{
			$title .= ' (' . $date . ')';
		}

		return $title;
	}

	/**
	 * Method to get the filesystem path to a folder.
	 * TODO Need refactor
	 *
	 * @param   string  $section  Type of the item. Can be 'movie', 'name', 'album'.
	 * @param   string  $type     Type of the section. Can be 'gallery', 'trailers', 'soundtracks'
	 * @param   mixed   $tab      Tab number from gallery(or null value for 'trailers', 'soundtracks').
	 *                            If $tab is array when return array of paths for each type of $tab.
	 * @param   mixed   $id       The item IDs(movie, name, album).
	 *
	 * @return  mixed   Absolute filesystem path to a folder, array of paths, false otherwise.
	 *
	 * @since   3.0
	 * @throws  Exception
	 */
	public static function getPath($section, $type, $tab, $id = 0)
	{
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$path   = JPATH_ROOT . '/tmp';
		$folder = '';
		$paths  = '';

		if ($section == 'movie')
		{
			if ($type == 'gallery')
			{
				if ($tab == 1)
				{
					$path   = $params->get('media_wallpapers_root');
					$folder = 'wallpapers';
				}
				elseif ($tab == 2)
				{
					$path   = $params->get('media_posters_root');
					$folder = 'posters';
				}
				elseif ($tab == 3)
				{
					$path   = $params->get('media_scr_root');
					$folder = 'screenshots';
				}
				elseif (is_array($tab))
				{
					$paths = array(
						1 => array(
							'path'   => $params->get('media_wallpapers_root'),
							'folder' => 'wallpapers'
						),
						2 => array(
							'path'   => $params->get('media_posters_root'),
							'folder' => 'posters'
						),
						3 => array(
							'path'   => $params->get('media_scr_root'),
							'folder' => 'screenshots'
						)
					);
				}
			}
			elseif ($type == 'trailers')
			{
				$path   = $params->get('media_trailers_root');
				$folder = '';
			}
		}
		elseif ($section == 'name')
		{
			if ($type == 'gallery')
			{
				if ($tab == 1)
				{
					$path   = $params->get('media_actor_wallpapers_root');
					$folder = 'wallpapers';
				}
				elseif ($tab == 2)
				{
					$path   = $params->get('media_actor_posters_root');
					$folder = 'posters';
				}
				elseif ($tab == 3)
				{
					$path   = $params->get('media_actor_photo_root');
					$folder = 'photo';
				}
				elseif (is_array($tab))
				{
					$paths = array(
						1 => array(
							'path'   => $params->get('media_actor_wallpapers_root'),
							'folder' => 'wallpapers'
						),
						2 => array(
							'path'   => $params->get('media_actor_posters_root'),
							'folder' => 'posters'
						),
						3 => array(
							'path'   => $params->get('media_actor_photo_root'),
							'folder' => 'photo'
						)
					);
				}
			}
		}
		elseif ($section == 'album')
		{
			// Get album covers and tracks path
			$db = JFactory::getDbo();

			$query = $db->getQuery(true)
				->select(
					$db->quoteName(
						array(
							'covers_path', 'covers_path_www', 'tracks_path', 'tracks_path_www', 'tracks_preview_path'
						)
					)
				)
				->from($db->quoteName('#__ka_music_albums'));

			if (is_array($id))
			{
				$_id = ' IN (' . implode(',', $id) . ')';
			}
			else
			{
				$_id = ' = ' . (int) $id;
			}

			$query->where($db->quoteName('id') . $_id);
			$db->setQuery($query);

			try
			{
				if (is_array($id))
				{
					$result = $db->loadObjectList();
				}
				else
				{
					$result = $db->loadObject();
				}
			}
			catch (RuntimeException $e)
			{
				JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');

				return false;
			}

			if ($type == 'gallery')
			{
				$path = !empty($result->covers_path) ? $result->covers_path : $params->get('media_music_images_root');
				$folder = '';

				if (is_array($tab))
				{
					$paths = array(
						1 => array(
							'path'   => $path,
							'folder' => $folder
						),
						2 => array(
							'path'   => $path,
							'folder' => $folder
						),
						3 => array(
							'path'   => $path,
							'folder' => $folder
						),
						4 => array(
							'path'   => $path,
							'folder' => $folder
						)
					);
				}

				if (!empty($result->covers_path))
				{
					return JPath::clean($path);
				}
			}
		}
		else
		{
			return false;
		}

		if (is_array($id))
		{
			$filesystemAlias = self::getFilesystemAlias($section, $id);
			$result = array();

			foreach ($id as $value)
			{
				if (is_array($tab))
				{
					$result[$value]['parent'] = JPath::clean($paths[1]['path'] . '/' . $filesystemAlias[$value] . '/' . $value);

					foreach ($tab as $number)
					{
						$result[$value][$number] = JPath::clean(
							$paths[$number]['path'] . '/' . $filesystemAlias[$value] . '/' . $value . '/' . $paths[$number]['folder']
						);
					}
				}
				else
				{
					$result[$value] = JPath::clean($path . '/' . $filesystemAlias[$value] . '/' . $value . '/' . $folder);
				}
			}
		}
		else
		{
			$filesystemAlias = self::getFilesystemAlias($section, array($id));
			$result = JPath::clean($path . '/' . $filesystemAlias[$id] . '/' . $id . '/' . $folder);
		}

		return $result;
	}

	/**
	 * Method to get an item alias for filesystem.
	 *
	 * @param   string  $section  Type of the item. Can be 'movie' or 'name'.
	 * @param   array   $ids      The item IDs (movie or name).
	 *
	 * @return  mixed   Array with URL safe aliases, false on errors.
	 *
	 * @since   3.0
	 * @throws  Exception
	 */
	public static function getFilesystemAlias($section, $ids)
	{
		$db           = JFactory::getDbo();
		$unicodeslugs = JFactory::getConfig()->get('unicodeslugs');
		$results      = array();

		if (!is_array($ids) || count($ids) < 1)
		{
			KAComponentHelper::eventLog('Empty IDs!');

			return false;
		}

		if ($section == 'movie')
		{
			$table = '#__ka_movies';
			$cols  = array('id', 'title', 'alias', 'fs_alias');
		}
		elseif ($section == 'name')
		{
			$table = '#__ka_names';
			$cols  = array('id', 'name', 'latin_name', 'alias', 'fs_alias');
		}
		elseif ($section == 'album')
		{
			$table = '#__ka_music_albums';
			$cols  = array('id', 'title', 'alias', 'fs_alias');
		}
		else
		{
			KAComponentHelper::eventLog('Wrong section type!');

			return false;
		}

		// Make sure the item ids are integers
		$ids = Joomla\Utilities\ArrayHelper::toInteger($ids);

		$query = $db->getQuery(true)
			->select($db->quoteName($cols))
			->from($db->quoteName($table))
			->where($db->quoteName('id') . ' IN (' . implode(',', $ids) . ')');

		$db->setQuery($query);

		try
		{
			$rows = $db->loadAssocList();
		}
		catch (RuntimeException $e)
		{
			KAComponentHelper::eventLog($e->getMessage());

			return false;
		}

		foreach ($rows as $row)
		{
			$results[$row['id']] = $row['fs_alias'];

			if (empty($row['fs_alias']))
			{
				if (empty($row['alias']))
				{
					if ($section == 'movie' || $section == 'album')
					{
						$string = $row['title'];
					}
					elseif ($section == 'name')
					{
						$string = empty($row['latin_name']) ? $row['name'] : $row['latin_name'];
					}
					else
					{
						return false;
					}

					if ($unicodeslugs == 1)
					{
						$row['alias'] = JFilterOutput::stringUrlUnicodeSlug($string);
					}
					else
					{
						$row['alias'] = JFilterOutput::stringURLSafe($string);
					}
				}

				$results[$row['id']] = rawurlencode(StringHelper::substr($row['alias'], 0, 1));
			}
		}

		return $results;
	}

	/**
	 * Time to ISO8601 duration.
	 *
	 * @param   string  $time  Time in format 00:00:00.
	 *
	 * @return  string
	 *
	 * @since   3.1
	 */
	public static function timeToISO8601($time)
	{
		$datetime = new DateTime('1970-01-01 ' . $time, new DateTimeZone('UTC'));
		$seconds  = (int) $datetime->getTimestamp();
		$days     = floor($seconds / 86400);
		$seconds  = $seconds % 86400;
		$hours    = floor($seconds / 3600);
		$seconds  = $seconds % 3600;
		$minutes  = floor($seconds / 60);
		$seconds  = $seconds % 60;

		return sprintf('P%dDT%dH%dM%dS', $days, $hours, $minutes, $seconds);
	}

	/**
	 * Format time without microseconds and leading zero hours.
	 *
	 * @param   string  $time  Time in format 00:00:00.000000.
	 *
	 * @return  string
	 *
	 * @since   3.1
	 */
	public static function formatTrackLength($time)
	{
		$datetime = DateTime::createFromFormat('H:i:s.u', $time);

		if ($datetime)
		{
			$time = $datetime->format('H:i:s');
		}

		// Remove 00 from hours
		$parts = explode(':', $time);

		// Check if dealing with hours
		if (count($parts) === 3)
		{
			if ($parts[0] === '00')
			{
				$time = substr($time, 3);
			}
		}

		return $time;
	}

	/**
	 * Get checkingPath to search for covers based on item content.
	 *
	 * @param   string  $path    Path to a folder with covers from item.
	 * @param   string  $folder  Path to a folder with covers from component settings.
	 * @param   object  $item    Item object.
	 *
	 * @return  string
	 *
	 * @since   3.1
	 */
	public static function getAlbumCheckingPath($path, $folder, $item)
	{
		$filename = property_exists($item, 'filename') ? $item->filename : '';

		// Check for path from item first.
		if (!empty($path))
		{
			$checkingPath = JPath::clean($path . '/' . $filename);
		}
		else
		{
			$checkingPath = JPath::clean(
				$folder . '/' . rawurlencode($item->fs_alias) . '/' . $item->id . '/' . $filename
			);
		}

		return $checkingPath;
	}

	/**
	 * Get album covers.
	 * TODO Refactor this
	 * @param   string  $path  Path where to search covers.
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	public static function getAlbumCovers($path)
	{
		jimport('joomla.filesystem.folder');

		$params        = JComponentHelper::getParams('com_kinoarhiv');
		$files         = array();
		$frontCovers   = self::cleanCoverFilename($params->get('music_covers_front'));
		$backCovers    = self::cleanCoverFilename($params->get('music_covers_back'));
		$artistCovers  = self::cleanCoverFilename($params->get('music_covers_artist'));
		$discCovers    = self::cleanCoverFilename($params->get('music_covers_disc'));
		$coversListArr = array_merge($frontCovers, $backCovers, $artistCovers, $discCovers);
		$coversList    = implode('|', array_filter($coversListArr));
		$_files        = JFolder::files($path, $coversList . '$', 1, true);

		// Filter files because JFolder::files() return all files which contains listed names.
		foreach ($_files as $i => $file)
		{
			if (in_array(basename($file), $coversListArr))
			{
				$files[$i]['folder'] = dirname(JPath::clean($file));
				$files[$i]['file']   = basename($file);
			}

			// Search for preview image
			if (StringHelper::strpos($file, 'thumb_', 0) !== false)
			{
				$files['thumb']['folder'] = dirname(JPath::clean($file));
				$files['thumb']['file']   = basename($file);
			}
		}

		return $files;
	}

	/**
	 * Clean covers filenames.
	 *
	 * @param   string  $covers  Cover filenames as string separated by new line.
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	private static function cleanCoverFilename($covers)
	{
		jimport('joomla.filesystem.file');

		$coversList = explode("\n", $covers);
		array_walk(
			$coversList,
			function (&$file)
			{
				$file = JFile::makeSafe($file);
			}
		);

		return $coversList;
	}

	/**
	 * Get proper itemid for menu &view=?&Itemid=? links based on view type.
	 *
	 * @param   string  $view     View name.
	 * @param   array   $options  Extra options.
	 *
	 * @return  integer
	 *
	 * @since   3.1
	 */
	public static function getItemid($view, $options = array())
	{
		$app          = JFactory::getApplication();
		$lang         = JFactory::getLanguage();
		$itemid       = $app->input->get('Itemid', 0, 'int');
		$properItemid = $itemid;
		$menus        = $app->getMenu();
		$_options     = array(
			'link'     => 'index.php?option=com_kinoarhiv&view=' . $view,
			'language' => $lang->getTag()
		);

		if (!empty($options))
		{
			$_options = array_merge($_options, $options);
		}

		$menu = self::searchMenu($menus, $_options);

		// Get menu ID for current link and language.
		if (!empty($menu))
		{
			$properItemid = $menu->id;
		}
		// Try to get menu for all languages.
		else
		{
			$_options['language'] = '*';
			$menu = self::searchMenu($menus, $_options);

			if (!empty($menu))
			{
				$properItemid = $menu->id;
			}
		}

		return (int) $properItemid;
	}

	/**
	 * Search for menu by parameters.
	 *
	 * @param   JMenu  $menus    MenuItems.
	 * @param   array  $options  Options.
	 *
	 * @return  object
	 *
	 * @since   3.1
	 */
	private static function searchMenu($menus, $options)
	{
		$menu = array();

		// Check for keyword 'params' to search by menu params.
		if (array_key_exists('params', $options))
		{
			// Save params to temporary variable and remove from search.
			$_menuParams = $options['params'];
			unset($options['params']);

			$_menus = $menus->getItems(array_keys($options), array_values($options));

			foreach ($_menus as $_menu)
			{
				$_params = $_menu->getParams()->toArray();
				$diff = array_diff_assoc($_menuParams, $_params);

				// We found menu by parameter(s)
				if (empty($diff))
				{
					$menu = $_menu;
					break;
				}
			}
		}
		else
		{
			$menu = $menus->getItems(array_keys($options), array_values($options), true);
		}

		return $menu;
	}

	/**
	 * Method to get some album data from database, if not in cache.
	 * This cache can be deleted via administrator/index.php?option=com_cache&filter[search]=com_kinoarhiv_media
	 *
	 * @param   integer  $id  Album ID.
	 *
	 * @return  array|boolean
	 *
	 * @since   3.1
	 * @throws  RuntimeException
	 */
	public static function getAlbumMetadata($id)
	{
		/** @var JCache $cache */
		$cache   = JFactory::getCache('com_kinoarhiv_media', '');
		$cacheId = 'album_meta_' . $id;

		// If caching is disabled, we force it only for caching metadata.
		if (!$cache->getCaching())
		{
			$cache->setCaching(true);

			// Set lifetime to one year
			$cache->setLifeTime(31536000);
		}

		// Check the cached results.
		if ($cache->contains($cacheId))
		{
			$meta = $cache->get($cacheId);
		}
		else
		{
			$db = JFactory::getDbo();

			$query = $db->getQuery(true)
				->select($db->quoteName(array('covers_path', 'covers_path_www', 'tracks_path', 'tracks_path_www', 'tracks_preview_path')))
				->from($db->quoteName('#__ka_music_albums'))
				->where($db->quoteName('id') . ' = ' . (int) $id);

			$db->setQuery($query);

			try
			{
				$meta = $db->loadAssoc();
			}
			catch (RuntimeException $e)
			{
				throw new RuntimeException($e->getMessage(), 500);
			}

			// Store the data in cache.
			$cache->store($meta, $cacheId);
		}

		return $meta;
	}
}
