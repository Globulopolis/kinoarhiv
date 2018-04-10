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

/**
 * Class KAImage
 *
 * @since  3.0
 */
class KAImage extends JImage
{
	/**
	 * Method to create thumbnails from the current image and save them to disk. It allows creation by resizing
	 * or croppping the original image.
	 *
	 * @param   string   $directory       Path where to find a file.
	 * @param   string   $filename        Filename.
	 * @param   mixed    $thumbSizes      String or array of strings. Example: $thumbSizes = array('150x75','250x150')
	 * @param   integer  $creationMethod  1-3 resize $scaleMethod | 4 create croppping
	 * @param   string   $thumbsFolder    Destination thumbs folder. null generates a thumbs folder in the image folder
	 * @param   mixed    $thumbsName      True for default filename, false - for default filename in component
	 *                                    (thumb_$filename), string - custom prefix for filename
	 *
	 * @return  array
	 *
	 * @throws  RuntimeException
	 *
	 * @since  3.0
	 */
	public function makeThumbs($directory, $filename, $thumbSizes, $creationMethod = 2, $thumbsFolder = null, $thumbsName = true)
	{
		jimport('joomla.filesystem.file');
		$image = new JImage($directory . DIRECTORY_SEPARATOR . $filename);

		// Make sure the resource handle is valid.
		if (!$image->isLoaded())
		{
			throw new RuntimeException('No valid image was loaded.');
		}

		// No thumbFolder set -> we will create a thumbs folder in the current image folder
		if (is_null($thumbsFolder))
		{
			$thumbsFolder = dirname($image->getPath()) . '/thumbs';
		}

		// Check destination
		if (!is_dir($thumbsFolder) && (!is_dir(dirname($thumbsFolder)) || !@mkdir($thumbsFolder)))
		{
			throw new RuntimeException('Folder does not exist and cannot be created: ' . $thumbsFolder);
		}

		// Process thumbs
		$thumbsCreated = array();

		// Parent image properties
		$imgProperties = JImage::getImageFileProperties($image->getPath());

		if ($thumbs = $image->generateThumbs($thumbSizes, $creationMethod))
		{
			foreach ($thumbs as $thumb)
			{
				// Get thumb properties
				$thumbWidth = $thumb->getWidth();
				$thumbHeight = $thumb->getHeight();

				// Generate thumb name
				$filename = pathinfo($image->getPath(), PATHINFO_FILENAME);
				$fileExtension = JFile::getExt($image->getPath());

				if ($thumbsName === true)
				{
					$thumbFileName = $filename . '_' . $thumbWidth . 'x' . $thumbHeight . '.' . $fileExtension;
				}
				elseif ($thumbsName === false)
				{
					$thumbFileName = 'thumb_' . $filename . '.' . $fileExtension;
				}
				elseif ($thumbsName === null)
				{
					$thumbFileName = $filename . '.' . $fileExtension;
				}
				else
				{
					$thumbFileName = $thumbsName . '_' . $filename . '.' . $fileExtension;
				}

				// Save thumb file to disk
				$thumbFileName = $thumbsFolder . '/' . $thumbFileName;

				if ($thumb->toFile($thumbFileName, $imgProperties->type))
				{
					// Return JImage object with thumb path to ease further manipulation
					$thumb->file_path = $thumbFileName;
					$thumbsCreated[] = $thumb;
				}
			}
		}

		return $thumbsCreated;
	}

	/**
	 * Method to add a watermark to an image.
	 *
	 * @param   string  $directory   path where to find file
	 * @param   string  $filename    filename
	 * @param   string  $watermark   path to an image
	 * @param   string  $position    watermark position. Available values are: tl - top left, tc - top center, tr - top
	 *                               right, cl - center left, cc - center center, cr - center right, bl - bottom left,
	 *                               bc - bottom center, br - bottom right.
	 * @param   array   $properties  additional properties
	 *
	 * @return  void
	 *
	 * @since  3.0
	 */
	public function addWatermark($directory, $filename, $watermark, $position = 'br', $properties = array())
	{
		$file = JPath::clean($directory . '/' . $filename);
		$image = new JImage($file);
		$watermark = new JImage($watermark);

		// Make sure the resource handle is valid.
		if (!$image->isLoaded() && !$watermark->isLoaded())
		{
			throw new RuntimeException('No valid image was loaded.');
		}

		// Parent image properties
		$imgProperties = JImage::getImageFileProperties($image->getPath());

		// Watermark image properties
		$wtProperties = JImage::getImageFileProperties($watermark->getPath());
		$wtCreated = false;

		if ($wtProperties->mime == 'image/gif')
		{
			if (KAComponentHelper::functionExists('imagecreatefromgif'))
			{
				$filter = @imagecreatefromgif($watermark->getPath());

				if (!$filter)
				{
					throw new RuntimeException('An error has occured while creating image.');
				}
				else
				{
					$wtCreated = true;
				}
			}
		}
		elseif ($wtProperties->mime == 'image/jpeg')
		{
			if (KAComponentHelper::functionExists('imagecreatefromjpeg'))
			{
				$filter = imagecreatefromjpeg($watermark->getPath());

				if (!$filter)
				{
					throw new RuntimeException('An error has occured while creating image.');
				}
				else
				{
					$wtCreated = true;
				}
			}
		}
		elseif ($wtProperties->mime == 'image/png')
		{
			if (KAComponentHelper::functionExists('imagecreatefrompng'))
			{
				$filter = @imagecreatefrompng($watermark->getPath());

				if (!$filter)
				{
					throw new RuntimeException('An error has occured while creating image.');
				}
				else
				{
					$wtCreated = true;
				}
			}
		}
		else
		{
			throw new RuntimeException('Unknown or unsupported image type.');
		}

		if ($wtCreated)
		{
			$watermarkDstWidth  = $watermarkSrcWidth  = imagesx($filter);
			$watermarkDstHeight = $watermarkSrcHeight = imagesy($filter);

			if ($watermarkDstWidth > $imgProperties->width || $watermarkDstHeight > $imgProperties->height)
			{
				$canvasWidth = $imgProperties->width - abs($wtProperties->width);
				$canvasHeight = $imgProperties->height - abs($wtProperties->height);

				if (($watermarkSrcWidth / $canvasWidth) > ($watermarkSrcHeight / $canvasHeight))
				{
					$watermarkDstWidth = $canvasWidth;
					$watermarkDstHeight = (int) ($watermarkSrcHeight * ($canvasWidth / $watermarkSrcWidth));
				}
				else
				{
					$watermarkDstHeight = $canvasHeight / 2;
					$watermarkDstWidth = (int) ($watermarkSrcWidth * ($canvasHeight / $watermarkSrcHeight)) / 2;
				}
			}

			$watermarkOffsetX = (int) isset($properties['watermark_x_offset']) ? $properties['watermark_x_offset'] : 10;
			$watermarkOffsetY = (int) isset($properties['watermark_y_offset']) ? $properties['watermark_y_offset'] : 10;

			if (isset($properties['watermark_x']) && is_numeric($properties['watermark_x']))
			{
				$watermarkX = ($wtProperties->width < 0) ? $imgProperties->width - $watermarkDstWidth + $wtProperties->width : $wtProperties->width;
			}
			else
			{
				if (strpos($position, 'r') !== false)
				{
					$watermarkX = ($imgProperties->width - $watermarkDstWidth) - $watermarkOffsetX;
				}
				else
				{
					if (strpos($position, 'l') !== false)
					{
						$watermarkX = $watermarkOffsetX;
					}
					else
					{
						$watermarkX = ($imgProperties->width - $watermarkDstWidth) / 2;
					}
				}
			}

			if (isset($properties['watermark_y']) && is_numeric($properties['watermark_y']))
			{
				$watermarkY = ($wtProperties->height < 0) ? $imgProperties->height - $watermarkDstHeight + $wtProperties->height : $wtProperties->height;
			}
			else
			{
				if (strpos($position, 'b') !== false)
				{
					$watermarkY = ($imgProperties->height - $watermarkDstHeight) - $watermarkOffsetY;
				}
				else
				{
					if (strpos($position, 't') !== false)
					{
						$watermarkY = $watermarkOffsetY;
					}
					else
					{
						$watermarkY = ($imgProperties->height - $watermarkDstHeight) / 2;
					}
				}
			}

			imagealphablending($image->handle, true);
			imagecopyresampled(
				$image->handle, $filter, $watermarkX, $watermarkY, 0, 0,
				$watermarkDstWidth, $watermarkDstHeight, $watermarkSrcWidth, $watermarkSrcHeight
			);
		}
		else
		{
			throw new RuntimeException('An error has occured.');
		}

		if ($imgProperties->mime == 'image/gif')
		{
			$image->toFile($file, IMAGETYPE_GIF);
		}
		elseif ($imgProperties->mime == 'image/jpeg')
		{
			$quality = isset($properties['output_quality']) ? (int) $properties['output_quality'] : 75;
			$image->toFile($file, IMAGETYPE_JPEG, array('quality' => $quality));
		}
		elseif ($imgProperties->mime == 'image/png')
		{
			$quality = isset($properties['output_quality']) ? (int) $properties['output_quality'] : 3;
			$image->toFile($file, IMAGETYPE_PNG, array('quality' => $quality));
		}
	}

	/**
	 * Update rating images.
	 *
	 * @param   integer  $id      Movie ID from component database.
	 * @param   string   $source  Type of source(server).
	 * @param   array    $data    Array with the ratings and votes.
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	public function createRateImage($id, $source, $data)
	{
		jimport('joomla.filesystem.folder');

		$params = JComponentHelper::getParams('com_kinoarhiv');
		$file   = JPath::clean(JPATH_ROOT . '/media/com_kinoarhiv/images/rating/' . $source . '_blank.png');
		$dstDir = JPath::clean($params->get('media_rating_image_root') . '/' . $source . '/');
		$font   = JPath::clean(JPATH_ROOT . '/media/com_kinoarhiv/fonts/OpenSans-Regular.ttf');

		if (empty($id))
		{
			return array('success' => false, 'message' => 'Empty movie ID!');
		}

		if (file_exists($file))
		{
			list($width, $height) = @getimagesize($file);

			$dstImg = imagecreatetruecolor($width, $height);
			$srcImg = imagecreatefrompng($file);
			imagealphablending($srcImg, true);
			imagesavealpha($srcImg, true);

			if (!isset($data[1]['fontsize']))
			{
				$rgbArr = $this->rgb2array('#333333');
				$color = imagecolorallocate($srcImg, $rgbArr['r'], $rgbArr['g'], $rgbArr['b']);
				imagettftext($srcImg, 10, 0, 5, 32, $color, $font, $data[0]['text']);
			}
			else
			{
				$rgbArr1 = $this->rgb2array($data[0]['color']);
				$rgbArr2 = $this->rgb2array($data[1]['color']);
				$color1  = imagecolorallocate($srcImg, $rgbArr1['r'], $rgbArr1['g'], $rgbArr1['b']);
				$color2  = imagecolorallocate($srcImg, $rgbArr2['r'], $rgbArr2['g'], $rgbArr2['b']);

				if ($source == 'rottentomatoes')
				{
					imagettftext($srcImg, $data[0]['fontsize'], 0, 5, 32, $color1, $font, $data[0]['text']);
					$offsetLeft = count(count_chars($data[0]['text'], 1)) * 10 + 7;
					imagettftext($srcImg, $data[1]['fontsize'], 0, $offsetLeft, 31, $color2, $font, $data[1]['text']);
				}
				elseif ($source == 'metacritic')
				{
					imagettftext($srcImg, $data[0]['fontsize'], 0, 45, 18, $color1, $font, $data[0]['text']);
					imagettftext($srcImg, $data[1]['fontsize'], 0, 45, 30, $color2, $font, $data[1]['text']);
				}
				elseif ($source == 'kinopoisk')
				{
					imagettftext($srcImg, $data[0]['fontsize'], 0, 5, 32, $color1, $font, $data[0]['text']);
					imagettftext($srcImg, $data[1]['fontsize'], 0, 40, 31, $color2, $font, $data[1]['text']);
				}
				elseif ($source == 'imdb')
				{
					imagettftext($srcImg, $data[0]['fontsize'], 0, 55, 18, $color1, $font, $data[0]['text']);
					imagettftext($srcImg, $data[1]['fontsize'], 0, 55, 30, $color2, $font, $data[1]['text']);
				}
				elseif ($source == 'myshows')
				{
					imagettftext($srcImg, $data[0]['fontsize'], 0, 5, 32, $color1, $font, $data[0]['text']);
					imagettftext($srcImg, $data[1]['fontsize'], 0, 50, 32, $color2, $font, $data[1]['text']);
				}
			}

			imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $width, $height, $width, $height);

			JFactory::getDocument()->setMimeEncoding('image/png');
			JFactory::getApplication()->allowCache(false);

			if (!file_exists($dstDir))
			{
				JFolder::create($dstDir);
			}

			$result = imagepng($srcImg, $dstDir . $id . '_big.png', 1);
			imagedestroy($srcImg);

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

	/**
	 * Convert HEX color code into rgb.
	 *
	 * @param   string  $rgb  HEX color code.
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	protected function rgb2array($rgb)
	{
		$rgb = str_replace('#', '', $rgb);

		return array(
			'r' => base_convert(substr($rgb, 0, 2), 16, 10),
			'g' => base_convert(substr($rgb, 2, 2), 16, 10),
			'b' => base_convert(substr($rgb, 4, 2), 16, 10)
		);
	}
}
