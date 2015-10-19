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

/**
 * Class KAMediaHelper
 *
 * @since  3.0
 */
class KAMediaHelper
{
	/**
	 * Method to get a front cover for music album
	 *
	 * @param   object  $item_data  Item data. Should contain these fields from albums table - id, fs_alias, filename,
	 *                              covers_path, covers_path_www, cover_filename.
	 *
	 * @return  array
	 */
	public static function getAlbumCover($item_data)
	{
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$path = $params->get('media_music_images_root') . DIRECTORY_SEPARATOR . $item_data->fs_alias . DIRECTORY_SEPARATOR
			. $item_data->id . DIRECTORY_SEPARATOR;
		$covers = array('poster' => '', 'th_poster' => '');

		if (is_file(JPath::clean($path . $item_data->filename)))
		{
			if (substr($params->get('media_music_images_root_www'), 0, 1) == '/')
			{
				$covers['poster'] = JUri::root() . substr($params->get('media_music_images_root_www'), 1) . '/'
					. urlencode($item_data->fs_alias) . '/' . $item_data->id . '/' . $item_data->filename;
				$covers['th_poster'] = JUri::root() . substr($params->get('media_music_images_root_www'), 1) . '/'
					. urlencode($item_data->fs_alias) . '/' . $item_data->id . '/' . 'thumb_' . $item_data->filename;
			}
			else
			{
				$covers['poster'] = $params->get('media_music_images_root_www') . '/' . urlencode($item_data->fs_alias)
					. '/' . $item_data->id . '/' . $item_data->filename;
				$covers['th_poster'] = $params->get('media_music_images_root_www') . '/' . urlencode($item_data->fs_alias)
					. '/' . $item_data->id . '/' . 'thumb_' . $item_data->filename;
			}
		}
		else
		{
			if (is_file(JPath::clean($item_data->covers_path . $item_data->cover_filename)))
			{
				$covers['poster'] = $item_data->covers_path_www . $item_data->cover_filename;
				$covers['th_poster'] = $item_data->covers_path_www . 'thumb_' . $item_data->cover_filename;
			}
			else
			{
				// Nothing found in previous two locations. Search for files by pattern in previous locations.
				$file_patterns = preg_split('/(:\r\n|[\r\n])/', $params->get('music_covers_front'), null, PREG_SPLIT_NO_EMPTY);
				$paths = array();

				foreach ($file_patterns as $file_pattern)
				{
					$paths[] = array(
						'folder'      => $path,
						'url'         => $params->get('media_music_images_root_www') . '/' . urlencode($item_data->fs_alias) . '/' . $item_data->id . '/',
						'file'        => $file_pattern,
						'from_config' => 1 // For additional checks
					);

					if (!empty($item_data->covers_path) && JPath::clean($path . $file_pattern) != JPath::clean($item_data->covers_path . $file_pattern))
					{
						$paths[] = array(
							'folder'    => $item_data->covers_path,
							'url'       => $item_data->covers_path_www,
							'file'      => $file_pattern,
							'from_item' => 1
						);
					}
				}

				foreach ($paths as $filepath)
				{
					if (is_file(JPath::clean($filepath['folder'] . $filepath['file'])))
					{
						if (array_key_exists('from_config', $filepath))
						{
							if (substr($filepath['url'], 0, 1) == '/')
							{
								$covers['poster'] = JUri::root() . substr($filepath['url'], 1) . $filepath['file'];
								$covers['th_poster'] = JUri::root() . substr($filepath['url'], 1) . 'thumb_' . $filepath['file'];
							}
							else
							{
								$covers['poster'] = $filepath['url'] . $filepath['file'];
								$covers['th_poster'] = $filepath['url'] . 'thumb_' . $filepath['file'];
							}
						}
						else
						{
							$covers['poster'] = $filepath['url'] . $filepath['file'];
							$covers['th_poster'] = $filepath['url'] . 'thumb_' . $filepath['file'];
						}

						break;
					}
					else
					{
						$covers['poster'] = JUri::root() . 'components/com_kinoarhiv/assets/themes/component/default/images/no_album_cover.png';
						$covers['th_poster'] = JUri::root() . 'components/com_kinoarhiv/assets/themes/component/default/images/no_album_cover.png';
					}
				}
			}
		}

		return $covers;
	}
}
