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
	 * @param   string   $path        Path to a file
	 * @param   boolean  $scale       Scale image or not
	 * @param   integer  $baseWidth   Base image width from settings
	 * @param   string   $dimension   Default image dimension
	 *
	 * @return  object
	 *
	 * @since  3.0
	 */
	public static function getImageSize($path, $scale = true, $baseWidth = 0, $dimension = '128x128')
	{
		$image = (object) array('width' => 0, 'height' => 0);

		if ($scale)
		{
			$image->width = (int) $baseWidth;
			$origSize = explode('x', $dimension);

			if (!isset($origSize[1]) || empty($origSize[0]) || empty($origSize[1]))
			{
				$origSize[0] = '128';
				$origSize[1] = '128';
			}

			$image->height = floor(($image->width * $origSize[1]) / $origSize[0]);
		}
		else
		{
			list($width, $height) = @getimagesize($path);
			$image->width = $width;
			$image->height = $height;
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
	 * Method to get the filesystem path to a file.
	 *
	 * @param   string  $section  Type of the item. Can be 'movie' or 'name'.
	 * @param   string  $type     Type of the section. Can be 'gallery', 'trailers', 'soundtracks'
	 * @param   mixed   $tab      Tab number from gallery(or null value for 'trailers', 'soundtracks').
	 *                            If $tab is array when return array of paths for each type of $tab.
	 * @param   mixed   $id       The item IDs(movie or name).
	 *
	 * @return  mixed    Absolute filesystem path to a file, array of paths, false otherwise.
	 *
	 * @since   3.0
	 * @throws  \Exception
	 */
	public static function getPath($section, $type, $tab = 0, $id = 0)
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
						),
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
						),
					);
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
	 * @throws  \Exception
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
					if ($section == 'movie')
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
	 * Method to get a front cover for music album
	 *
	 * @param   object  $data  Item data. Should contain three fields from albums table - id, fs_alias, filename,
	 *                         covers_path, covers_path_www, cover_filename.
	 *
	 * @return  mixed  Array on success, false otherwise.
	 *
	 * @since   3.1
	 * @throws  \Exception
	 */
	public static function getAlbumCover($data)
	{
		clearstatcache();

		$itemid         = JFactory::getApplication()->input->getInt('Itemid');
		$params         = JComponentHelper::getParams('com_kinoarhiv');
		$folderPart     = '/' . urlencode($data->fs_alias) . '/' . $data->id . '/';
		$folder         = $params->get('media_music_images_root') . $folderPart;
		$throttleEnable = $params->get('throttle_image_enable', 0);
		$covers         = array('poster' => '', 'th_poster' => '', 'size' => (object) array('width' => 250, 'height' => 250));

		foreach (explode("\n", $params->get('music_covers_front')) as $filename)
		{
			$filename = trim($filename);

			if ($throttleEnable == 0)
			{
				// Search for cover in default location from component settings
				if (is_file(JPath::clean($folder . $filename)))
				{
					if (substr($params->get('media_music_images_root_www'), 0, 1) == '/')
					{
						$covers['poster']    = JUri::root() . substr($params->get('media_music_images_root_www'), 1) . $folderPart . $filename;
						$covers['th_poster'] = JUri::root() . substr($params->get('media_music_images_root_www'), 1) . $folderPart . 'thumb_' . $filename;
					}
					else
					{
						$covers['poster']    = $params->get('media_music_images_root_www') . $folderPart . $filename;
						$covers['th_poster'] = $params->get('media_music_images_root_www') . $folderPart . 'thumb_' . $filename;
					}

					$covers['size'] = self::getImageSize(JPath::clean($params->get('media_music_images_root') . $folderPart . 'thumb_' . $filename), false);

					break;
				}
				// Search for cover in album location
				elseif (is_file(JPath::clean($params->get('media_music_root') . $folderPart . $filename)))
				{
					if (substr($params->get('media_music_images_root_www'), 0, 1) == '/')
					{
						$covers['poster']    = JUri::root() . substr($params->get('media_music_root'), 1) . $folderPart . $filename;
						$covers['th_poster'] = JUri::root() . substr($params->get('media_music_root_www'), 1) . $folderPart . 'thumb_' . $filename;
					}
					else
					{
						$covers['poster']    = $params->get('media_music_root_www') . $folderPart . $filename;
						$covers['th_poster'] = $params->get('media_music_root_www') . $folderPart . 'thumb_' . $filename;
					}

					break;
				}
				else
				{
					$covers['poster']    = JUri::root() . 'media/com_kinoarhiv/images/themes/default/no_album_cover.png';
					$covers['th_poster'] = JUri::root() . 'media/com_kinoarhiv/images/themes/default/no_album_cover.png';
				}
			}
			else
			{
				// Search for cover in default location from component settings
				if (is_file(JPath::clean($folder . $filename)))
				{
					$covers['poster'] = JRoute::_(
						'index.php?option=com_kinoarhiv&task=media.view&element=music&content=image&type=3&id=' . $data->id .
						'&fa=' . urlencode($data->fs_alias) . '&fn=' . $filename . '&format=raw&Itemid=' . $itemid
					);
					$covers['th_poster'] = JRoute::_(
						'index.php?option=com_kinoarhiv&task=media.view&element=music&content=image&type=3&id=' . $data->id .
						'&fa=' . urlencode($data->fs_alias) . '&fn=' . $filename . '&format=raw&Itemid=' . $itemid . '&thumbnail=1'
					);
					$covers['size'] = self::getImageSize(JPath::clean($params->get('media_music_images_root') . $folderPart . 'thumb_' . $filename), false);

					break;
				}
				else
				{
					$covers['poster']    = JUri::root() . 'media/com_kinoarhiv/images/themes/default/no_album_cover.png';
					$covers['th_poster'] = JUri::root() . 'media/com_kinoarhiv/images/themes/default/no_album_cover.png';
				}
			}
		}

		return $covers;
	}
}
