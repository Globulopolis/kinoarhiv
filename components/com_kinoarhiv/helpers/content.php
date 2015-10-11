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
}
