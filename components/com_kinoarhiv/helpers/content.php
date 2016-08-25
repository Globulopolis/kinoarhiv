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
	 * @return object
	 *
	 * @since  3.0
	 */
	public static function getImageSize($path, $scale=true, $base_width=0, $dimension='128x128')
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
	public static function formatItemTitle($firstTitle, $secondTitle, $date='', $separator=' / ')
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
}
