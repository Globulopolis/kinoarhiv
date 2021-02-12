<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2018 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

use Joomla\String\StringHelper;

/**
 * Class KAMediaHelper
 *
 * @since  3.1
 */
class KAMediaHelper
{
	/**
	 * Get album covers.
	 *
	 * @param   string  $path  Path where to search covers.
	 *
	 * @return  array|boolean  Array with paths, false if nothing found.
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
		$_files        = JFolder::files(
			$path,
			$coversList . '$',
			false,
			true,
			array('.svn', 'CVS', '.DS_Store', '__MACOSX'),
			$excludeFilter = array('^\..*', '.*~'),
			$naturalSort = true
		);

		if (empty($_files))
		{
			return false;
		}

		// Filter files because JFolder::files() return all files which contains searched names.
		foreach ($_files as $i => $file)
		{
			list($width, $height) = @getimagesize($file);

			if (in_array(basename($file), $coversListArr))
			{
				$files['images'][] = array(
					'folder'    => dirname(JPath::clean($file)),
					'filename'  => basename($file),
					'dimension' => $width . 'x' . $height,
					'type'      => '' // TODO Get item type?
				);
			}
			// Search for preview image
			elseif (StringHelper::strpos($file, 'thumb_', 0) !== false)
			{
				$files['thumbs'][] = array(
					'folder'    => dirname(JPath::clean($file)),
					'filename'  => basename($file),
					'dimension' => $width . 'x' . $height
				);
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
}
