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
			$image['width'] = !empty($width) ? $width : 0;
			$image['height'] = !empty($height) ? $height : 0;
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

	/**
	 * Method to get album cover image.
	 *
	 * @param   object  $item    Album data.
	 * @param   object  $params  Component params.
	 *
	 * @return  object
	 *
	 * @since   3.1
	 */
	public static function getAlbumCover($item, $params)
	{
		$itemid       = JFactory::getApplication()->input->getInt('Itemid');
		$data         = (object) array();
		$checkingPath = self::getAlbumCheckingPath($item->covers_path, $params->get('media_music_images_root'), $item);

		if ($params->get('throttle_image_enable', 0) == 0)
		{
			if (!is_file($checkingPath))
			{
				$data->cover            = JUri::base() . 'media/com_kinoarhiv/images/themes/' . $params->get('ka_theme') . '/no_album_cover.png';
				$data->coverThumb       = $data->cover;
				$dimension              = self::getImageSize(
					JPATH_ROOT . '/media/com_kinoarhiv/images/themes/' . $params->get('ka_theme') . '/no_album_cover.png',
					false
				);
				$data->dimension        = $dimension['width'] . 'x' . $dimension['height'];
				$data->coverWidth       = $dimension['width'];
				$data->coverHeight      = $dimension['height'];
				$data->coverThumbWidth  = $dimension['width'];
				$data->coverThumbHeight = $dimension['height'];
			}
			else
			{
				$item->fs_alias = rawurlencode($item->fs_alias);
				$filename = (!is_file(JPath::clean(dirname($checkingPath) . '/thumb_' . $item->filename)))
					? $item->filename : 'thumb_' . $item->filename;

				if (!empty($item->covers_path))
				{
					if (StringHelper::substr($item->covers_path_www, 0, 1) == '/')
					{
						$data->cover = JUri::base() . StringHelper::substr($item->covers_path_www, 1) . '/' . $item->filename;
					}
					else
					{
						$data->cover = $item->covers_path_www . '/' . $item->filename;
						$data->coverThumb = $item->covers_path_www . '/' . $filename;
					}
				}
				else
				{
					if (StringHelper::substr($params->get('media_music_images_root_www'), 0, 1) == '/')
					{
						$data->cover = JUri::base() . StringHelper::substr($params->get('media_music_images_root_www'), 1) . '/'
							. $item->fs_alias . '/' . $item->id . '/' . $item->filename;
					}
					else
					{
						$data->cover = $params->get('media_music_images_root_www') . '/' . $item->fs_alias
							. '/' . $item->id . '/' . $item->filename;
					}
				}
			}
		}
		else
		{
			// Check for thumbnail image. If not found when load full image.
			$thumbnail = (!is_file(JPath::clean(dirname($checkingPath) . '/thumb_' . $item->filename))) ? 0 : 1;

			$data->cover = JRoute::_(
				'index.php?option=com_kinoarhiv&task=media.view&element=album&content=image&type=1&id=' . $item->id .
				'&fa=' . urlencode($item->fs_alias) . '&fn=' . $item->filename . '&format=raw&Itemid=' . $itemid .
				'&thumbnail=' . $thumbnail
			);
		}

		$dimension         = explode('x', $item->dimension);
		$data->dimension   = $item->dimension;
		$data->coverWidth  = array_key_exists(0, $dimension) ? $dimension[0] : 0;
		$data->coverHeight = array_key_exists(1, $dimension) ? $dimension[1] : 0;

		$dimensionThumb = self::getImageSize(
			$checkingPath,
			true,
			(int) $params->get('music_covers_size'),
			$item->dimension
		);
		$data->dimensionThumb   = $dimensionThumb['width'] . 'x' . $dimensionThumb['height'];
		$data->coverThumbWidth  = $dimensionThumb['width'];
		$data->coverThumbHeight = $dimensionThumb['height'];

		return $data;
	}

	/**
	 * Method to get poster for movie.
	 *
	 * @param   object  $item    Movie data.
	 * @param   object  $params  Component params.
	 *
	 * @return  object
	 *
	 * @since   3.1
	 */
	public static function getMoviePoster($item, $params)
	{
		$itemid = JFactory::getApplication()->input->getInt('Itemid');
		$data = (object) array();
		$checkingPath = JPath::clean(
			$params->get('media_posters_root') . '/' . $item->fs_alias . '/' . $item->id . '/posters/' . $item->filename
		);

		if ($params->get('throttle_image_enable', 0) == 0)
		{
			if (!is_file($checkingPath))
			{
				$data->poster            = JUri::base() . 'media/com_kinoarhiv/images/themes/' . $params->get('ka_theme') . '/no_movie_cover.png';
				$data->posterThumb       = $data->poster;
				$dimension               = self::getImageSize(
					JPATH_ROOT . '/media/com_kinoarhiv/images/themes/' . $params->get('ka_theme') . '/no_movie_cover.png',
					false
				);
				$data->dimension         = $dimension['width'] . 'x' . $dimension['height'];
				$data->posterWidth       = $dimension['width'];
				$data->posterHeight      = $dimension['height'];
				$data->posterThumbWidth  = $dimension['width'];
				$data->posterThumbHeight = $dimension['height'];
			}
			else
			{
				$item->fs_alias = rawurlencode($item->fs_alias);

				if (StringHelper::substr($params->get('media_posters_root_www'), 0, 1) == '/')
				{
					$data->poster = JUri::base() . StringHelper::substr($params->get('media_posters_root_www'), 1)
						. '/' . $item->fs_alias . '/' . $item->id . '/posters/' . $item->filename;
					$data->posterThumb = JUri::base() . StringHelper::substr($params->get('media_posters_root_www'), 1)
						. '/' . $item->fs_alias . '/' . $item->id . '/posters/thumb_' . $item->filename;
				}
				else
				{
					$data->poster = $params->get('media_posters_root_www') . '/' . $item->fs_alias
						. '/' . $item->id . '/posters/' . $item->filename;
					$data->posterThumb = $params->get('media_posters_root_www') . '/' . $item->fs_alias
						. '/' . $item->id . '/posters/thumb_' . $item->filename;
				}

				$dimension          = explode('x', $item->dimension);
				$data->dimension    = $item->dimension;
				$data->posterWidth  = $dimension[0];
				$data->posterHeight = $dimension[1];

				$dimensionThumb = self::getImageSize(
					$params->get('media_posters_root') . '/' . $item->fs_alias . '/' . $item->id . '/posters/thumb_' . $item->filename,
					true,
					(int) $params->get('size_x_posters'),
					$item->dimension
				);
				$data->dimensionThumb    = $dimensionThumb['width'] . 'x' . $dimensionThumb['height'];
				$data->posterThumbWidth  = $dimensionThumb['width'];
				$data->posterThumbHeight = $dimensionThumb['height'];
			}
		}
		else
		{
			$data->poster = JRoute::_(
				'index.php?option=com_kinoarhiv&task=media.view&element=movie&content=image&type=2&id=' . $item->id .
				'&fa=' . urlencode($item->fs_alias) . '&fn=' . $item->filename . '&format=raw&Itemid=' . $itemid .
				'&thumbnail=0'
			);
			$data->posterThumb = JRoute::_(
				'index.php?option=com_kinoarhiv&task=media.view&element=movie&content=image&type=2&id=' . $item->id .
				'&fa=' . urlencode($item->fs_alias) . '&fn=' . $item->filename . '&format=raw&Itemid=' . $itemid .
				'&thumbnail=1'
			);
			$dimension = self::getImageSize(
				$checkingPath,
				false
			);
			$data->dimension    = $item->dimension;
			$data->posterWidth  = $dimension['width'];
			$data->posterHeight = $dimension['height'];

			$dimensionThumb = self::getImageSize(
				$params->get('media_posters_root') . '/' . $item->fs_alias . '/' . $item->id . '/posters/thumb_' . $item->filename,
				true,
				(int) $params->get('size_x_posters'),
				$item->dimension
			);
			$data->dimensionThumb    = $dimensionThumb['width'] . 'x' . $dimensionThumb['height'];
			$data->posterThumbWidth  = $dimensionThumb['width'];
			$data->posterThumbHeight = $dimensionThumb['height'];
		}

		return $data;
	}

	/**
	 * Method to get photo for person.
	 *
	 * @param   object  $item    Person data.
	 * @param   object  $params  Component params.
	 *
	 * @return  object
	 *
	 * @since   3.1
	 */
	public static function getPersonPhoto($item, $params)
	{
		$itemid = JFactory::getApplication()->input->getInt('Itemid');
		$data = (object) array();
		$checkingPath = JPath::clean(
			$params->get('media_actor_photo_root') . '/' . $item->fs_alias . '/' . $item->id . '/photo/' . $item->filename
		);

		if ($params->get('throttle_image_enable', 0) == 0)
		{
			$noCover = ($item->gender == 0) ? 'no_name_cover_f' : 'no_name_cover_m';

			if (!is_file($checkingPath))
			{
				$data->photo            = JUri::base() . 'media/com_kinoarhiv/images/themes/' . $params->get('ka_theme') . '/' . $noCover . '.png';
				$data->photoThumb       = $data->photo;
				$dimension              = self::getImageSize(
					JPATH_ROOT . '/media/com_kinoarhiv/images/themes/' . $params->get('ka_theme') . '/' . $noCover . '.png',
					false
				);
				$data->dimension        = $dimension['width'] . 'x' . $dimension['height'];
				$data->photoWidth       = $dimension['width'];
				$data->photoHeight      = $dimension['height'];
				$data->photoThumbWidth  = $dimension['width'];
				$data->photoThumbHeight = $dimension['height'];
			}
			else
			{
				$item->fs_alias = rawurlencode($item->fs_alias);

				if (StringHelper::substr($params->get('media_actor_photo_root_www'), 0, 1) == '/')
				{
					$data->photo = JUri::base() . StringHelper::substr($params->get('media_actor_photo_root_www'), 1)
						. '/' . $item->fs_alias . '/' . $item->id . '/photo/' . $item->filename;
					$data->photoThumb = JUri::base() . StringHelper::substr($params->get('media_actor_photo_root_www'), 1)
						. '/' . $item->fs_alias . '/' . $item->id . '/photo/thumb_' . $item->filename;
				}
				else
				{
					$data->photo = $params->get('media_actor_photo_root_www') . '/' . $item->fs_alias
						. '/' . $item->id . '/photo/' . $item->filename;
					$data->photoThumb = $params->get('media_actor_photo_root_www') . '/' . $item->fs_alias
						. '/' . $item->id . '/photo/thumb_' . $item->filename;
				}

				$dimension         = explode('x', $item->dimension);
				$data->dimension   = $item->dimension;
				$data->photoWidth  = $dimension[0];
				$data->photoHeight = $dimension[1];

				$dimensionThumb = self::getImageSize(
					$params->get('media_actor_photo_root') . '/' . $item->fs_alias . '/' . $item->id . '/photo/thumb_' . $item->filename,
					true,
					(int) $params->get('size_x_photo'),
					$item->dimension
				);
				$data->dimensionThumb   = $dimensionThumb['width'] . 'x' . $dimensionThumb['height'];
				$data->photoThumbWidth  = $dimensionThumb['width'];
				$data->photoThumbHeight = $dimensionThumb['height'];
			}
		}
		else
		{
			$data->photo = JRoute::_(
				'index.php?option=com_kinoarhiv&task=media.view&element=name&content=image&type=3&id=' . $item->id .
				'&fa=' . urlencode($item->fs_alias) . '&fn=' . $item->filename . '&format=raw&Itemid=' . $itemid .
				'&thumbnail=0&gender=' . $item->gender
			);
			$data->photoThumb = JRoute::_(
				'index.php?option=com_kinoarhiv&task=media.view&element=name&content=image&type=3&id=' . $item->id .
				'&fa=' . urlencode($item->fs_alias) . '&fn=' . $item->filename . '&format=raw&Itemid=' . $itemid .
				'&thumbnail=1&gender=' . $item->gender
			);
			$dimension = self::getImageSize(
				$checkingPath,
				false
			);
			$data->dimension   = $item->dimension;
			$data->photoWidth  = $dimension['width'];
			$data->photoHeight = $dimension['height'];

			$dimensionThumb = self::getImageSize(
				$params->get('media_actor_photo_root') . '/' . $item->fs_alias . '/' . $item->id . '/photo/thumb_' . $item->filename,
				true,
				(int) $params->get('size_x_photo'),
				$item->dimension
			);
			$data->dimensionThumb   = $dimensionThumb['width'] . 'x' . $dimensionThumb['height'];
			$data->photoThumbWidth  = $dimensionThumb['width'];
			$data->photoThumbHeight = $dimensionThumb['height'];
		}

		return $data;
	}
}
