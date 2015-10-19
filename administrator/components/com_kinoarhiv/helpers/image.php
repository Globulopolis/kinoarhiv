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
 * Class KAImageHelper
 *
 * @since  3.0
 */
class KAImageHelper
{
	/**
	 * Convert HEX color code into rgb.
	 *
	 * @param   string  $rgb  HEX color code.
	 *
	 * @return  array
	 */
	private static function rgb2array($rgb)
	{
		$rgb = str_replace('#', '', $rgb);

		return array(
			'r' => base_convert(substr($rgb, 0, 2), 16, 10),
			'g' => base_convert(substr($rgb, 2, 2), 16, 10),
			'b' => base_convert(substr($rgb, 4, 2), 16, 10),
		);
	}

	/**
	 * Method to update rating images.
	 *
	 * @param   array  $data  Array with the ratings and votes.
	 *
	 * @return  array
	 */
	public static function createRateImage($data)
	{
		jimport('joomla.filesystem.folder');

		$document = JFactory::getDocument();
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$cmd = $app->input->get('elem', '', 'string');
		$id = $app->input->get('id', 0, 'int');
		$path = JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'rating';

		if ($cmd == 'rt_vote')
		{
			$file = $path . DIRECTORY_SEPARATOR . 'rottentomatoes_blank.png';
			$dst_dir = $params->get('media_rating_image_root') . DIRECTORY_SEPARATOR . 'rottentomatoes' . DIRECTORY_SEPARATOR;
		}
		elseif ($cmd == 'mc_vote')
		{
			$file = $path . DIRECTORY_SEPARATOR . 'metacritic_blank.png';
			$dst_dir = $params->get('media_rating_image_root') . DIRECTORY_SEPARATOR . 'metacritic' . DIRECTORY_SEPARATOR;
		}
		elseif ($cmd == 'kp_vote')
		{
			$file = $path . DIRECTORY_SEPARATOR . 'kinopoisk_blank.png';
			$dst_dir = $params->get('media_rating_image_root') . DIRECTORY_SEPARATOR . 'kinopoisk' . DIRECTORY_SEPARATOR;
		}
		elseif ($cmd == 'imdb_vote')
		{
			$file = $path . DIRECTORY_SEPARATOR . 'imdb_blank.png';
			$dst_dir = $params->get('media_rating_image_root') . DIRECTORY_SEPARATOR . 'imdb' . DIRECTORY_SEPARATOR;
		}
		else
		{
			return array('success' => false, 'message' => 'Unknown type!');
		}

		$font = JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . 'OpenSans-Regular.ttf';

		if (file_exists($file))
		{
			list($width, $height) = @getimagesize($file);

			$dst_im = imagecreatetruecolor($width, $height);
			$src_im = imagecreatefrompng($file);
			imagealphablending($src_im, true);
			imagesavealpha($src_im, true);

			if (!isset($data[1]['fontsize']))
			{
				$rgb_array = self::rgb2array('#333333');
				$color = imagecolorallocate($src_im, $rgb_array['r'], $rgb_array['g'], $rgb_array['b']);
				imagettftext($src_im, 10, 0, 5, 32, $color, $font, $data[0]['text']);
			}
			else
			{
				$rgb_array1 = self::rgb2array($data[0]['color']);
				$rgb_array2 = self::rgb2array($data[1]['color']);
				$color1 = imagecolorallocate($src_im, $rgb_array1['r'], $rgb_array1['g'], $rgb_array1['b']);
				$color2 = imagecolorallocate($src_im, $rgb_array2['r'], $rgb_array2['g'], $rgb_array2['b']);

				if ($cmd == 'rt_vote')
				{
					imagettftext($src_im, $data[0]['fontsize'], 0, 5, 32, $color1, $font, $data[0]['text']);
					$offset_left = count(count_chars($data[0]['text'], 1)) * 10 + 7;
					imagettftext($src_im, $data[1]['fontsize'], 0, $offset_left, 31, $color2, $font, $data[1]['text']);
				}
				elseif ($cmd == 'mc_vote')
				{
					imagettftext($src_im, $data[0]['fontsize'], 0, 45, 18, $color1, $font, $data[0]['text']);
					imagettftext($src_im, $data[1]['fontsize'], 0, 45, 30, $color2, $font, $data[1]['text']);
				}
				elseif ($cmd == 'kp_vote')
				{
					imagettftext($src_im, $data[0]['fontsize'], 0, 5, 32, $color1, $font, $data[0]['text']);
					imagettftext($src_im, $data[1]['fontsize'], 0, 40, 31, $color2, $font, $data[1]['text']);
				}
				elseif ($cmd == 'imdb_vote')
				{
					imagettftext($src_im, $data[0]['fontsize'], 0, 55, 18, $color1, $font, $data[0]['text']);
					imagettftext($src_im, $data[1]['fontsize'], 0, 55, 30, $color2, $font, $data[1]['text']);
				}
			}

			imagecopyresampled($dst_im, $src_im, 0, 0, 0, 0, $width, $height, $width, $height);

			$document->setMimeEncoding('image/png');
			JResponse::allowCache(false);

			if (!file_exists($dst_dir))
			{
				JFolder::create($dst_dir);
			}

			$result = imagepng($src_im, $dst_dir . $id . '_big.png', 1);
			imagedestroy($src_im);

			if ($result === true)
			{
				return array('success' => true, 'message' => 'Success');
			}
			else
			{
				return array('success' => false, 'message' => 'Failed to create an image!');
			}
		}

		return array('success' => false, 'message' => 'File with the blank rating image not found at path ' . $file);
	}
}
