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
	 * @param   integer  $base_width  Base image width from settings
	 * @param   string   $dimension   Default image dimension
	 *
	 * @return  object
	 *
	 * @since  3.0
	 */
	public static function getImageSize($path, $scale = true, $base_width = 0, $dimension = '128x128')
	{
		$image = (object) array();

		if ($scale)
		{
			$image->width = (int) $base_width;
			$orig_size = explode('x', $dimension);

			if (!isset($orig_size[1]) || empty($orig_size[0]) || empty($orig_size[1]))
			{
				$orig_size[0] = '128';
				$orig_size[1] = '128';
			}

			$image->height = floor(($image->width * $orig_size[1]) / $orig_size[0]);
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

		if (!empty($date) && $date != '0000')
		{
			$title .= ' (' . $date . ')';
		}

		return $title;
	}

	/**
	 * Method to get the filesystem path to a file.
	 *
	 * @param   string   $section  Type of the item. Can be 'movie' or 'name'.
	 * @param   string   $type     Type of the section. Can be 'gallery', 'trailers', 'soundtracks'
	 * @param   integer  $tab      Tab number from gallery(or empty value for 'trailers', 'soundtracks').
	 * @param   integer  $id       The item ID (movie or name).
	 *
	 * @return  string   Absolute filesystem path to a file.
	 *
	 * @since   3.0
	 */
	public static function getPath($section = '', $type = '', $tab = 0, $id = 0)
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

		$fs_alias = self::getFilesystemAlias($section, $id);
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
	public static function getFilesystemAlias($section, $id, $content_alias = false)
	{
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
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
}
