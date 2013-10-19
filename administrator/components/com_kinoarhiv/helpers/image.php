<?php defined('_JEXEC') or die;
define('DS', DIRECTORY_SEPARATOR);

class ImageHelper {
	private static function rgb2array($rgb) {
		$rgb = str_replace('#', '', $rgb);

		return array(
			'r'=>base_convert(substr($rgb, 0, 2), 16, 10),
			'g'=>base_convert(substr($rgb, 2, 2), 16, 10),
			'b'=>base_convert(substr($rgb, 4, 2), 16, 10),
		);
	}

	public static function createRateImage($data) {
		$document = JFactory::getDocument();
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$cmd = $app->input->get('elem', '', 'string');
		$id = $app->input->get('id', 0, 'int');

		if ($cmd == 'rt_vote') {
			$file = JPATH_COMPONENT.DS.'assets'.DS.'images'.DS.'rating'.DS.'rottentomatoes_blank.png';
		} elseif ($cmd == 'kp_vote') {
			$file = JPATH_COMPONENT.DS.'assets'.DS.'images'.DS.'rating'.DS.'kinopoisk_blank.png';
		} elseif ($cmd == 'imdb_vote') {
			$file = JPATH_COMPONENT.DS.'assets'.DS.'images'.DS.'rating'.DS.'imdb_blank.png';
		}

		$font = JPATH_COMPONENT.DS.'assets'.DS.'fonts'.DS.'OpenSans-Regular.ttf';

		if (file_exists($file)) {
			list($w, $h) = getimagesize($file);

			$dst_im = imagecreatetruecolor($w, $h);
			$src_im = imagecreatefrompng($file);
			imagealphablending($src_im, true);
			imagesavealpha($src_im, true);

			if (!isset($data[1]['fontsize'])) {
				$rgb_array = self::rgb2array('#333333');
				$color = imagecolorallocate($src_im, $rgb_array['r'], $rgb_array['g'], $rgb_array['b']);
				imagettftext($src_im, 10, 0, 5, 32, $color, $font, $data[0]['text']);
			} else {
				$rgb_array1 = self::rgb2array($data[0]['color']);
				$rgb_array2 = self::rgb2array($data[1]['color']);
				$color1 = imagecolorallocate($src_im, $rgb_array1['r'], $rgb_array1['g'], $rgb_array1['b']);
				$color2 = imagecolorallocate($src_im, $rgb_array2['r'], $rgb_array2['g'], $rgb_array2['b']);

				if ($cmd == 'rt_vote') {
					imagettftext($src_im, $data[0]['fontsize'], 0, 5, 32, $color1, $font, $data[0]['text']);
					$offset_left = count(count_chars($data[0]['text'], 1))*10+7;
					imagettftext($src_im, $data[1]['fontsize'], 0, $offset_left, 31, $color2, $font, $data[1]['text']);
				} elseif ($cmd == 'kp_vote') {
					imagettftext($src_im, $data[0]['fontsize'], 0, 5, 32, $color1, $font, $data[0]['text']);
					imagettftext($src_im, $data[1]['fontsize'], 0, 40, 31, $color2, $font, $data[1]['text']);
				} elseif ($cmd == 'imdb_vote') {
					imagettftext($src_im, $data[0]['fontsize'], 0, 55, 18, $color1, $font, $data[0]['text']);
					imagettftext($src_im, $data[1]['fontsize'], 0, 55, 30, $color2, $font, $data[1]['text']);
				}
			}

			imagecopyresampled($dst_im, $src_im, 0, 0, 0, 0, $w, $h, $w, $h);

			$document->setMimeEncoding('image/png');
			JResponse::allowCache(false);

			if ($cmd == 'rt_vote') {
				$result = imagepng($src_im, $params->get('media_rating_image_root').DS.'rottentomatoes'.DS.$id.'_big.png', 1);
			} elseif ($cmd == 'kp_vote') {
				$result = imagepng($src_im, $params->get('media_rating_image_root').DS.'kinopoisk'.DS.$id.'_big.png', 1);
			} elseif ($cmd == 'imdb_vote') {
				$result = imagepng($src_im, $params->get('media_rating_image_root').DS.'imdb'.DS.$id.'_big.png', 1);
			}

			imagedestroy($src_im);

			return $result;
		}

		return false;
	}
}
