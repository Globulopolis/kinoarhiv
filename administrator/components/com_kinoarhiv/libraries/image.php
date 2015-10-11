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
	 * @return array
	 *
	 * @throws  LogicException
	 * @throws  InvalidArgumentException
	 */
	public function _createThumbs($directory, $filename, $thumbSizes, $creationMethod = 2, $thumbsFolder = null, $thumbsName = true)
	{
		jimport('joomla.filesystem.file');
		$image = new JImage($directory . DIRECTORY_SEPARATOR . $filename);

		// Make sure the resource handle is valid.
		if (!$image->isLoaded())
		{
			throw new LogicException('No valid image was loaded.');
		}

		// No thumbFolder set -> we will create a thumbs folder in the current image folder
		if (is_null($thumbsFolder))
		{
			$thumbsFolder = dirname($image->getPath()) . '/thumbs';
		}

		// Check destination
		if (!is_dir($thumbsFolder) && (!is_dir(dirname($thumbsFolder)) || !@mkdir($thumbsFolder)))
		{
			throw new InvalidArgumentException('Folder does not exist and cannot be created: ' . $thumbsFolder);
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
	 * @return array
	 *
	 * @throws  LogicException
	 * @throws  InvalidArgumentException
	 */
	public function addWatermark($directory, $filename, $watermark, $position = 'br', $properties = array())
	{
		$image = new JImage($directory . DIRECTORY_SEPARATOR . $filename);
		$watermark = new JImage($watermark);

		// Make sure the resource handle is valid.
		if (!$image->isLoaded() && !$watermark->isLoaded())
		{
			throw new LogicException('No valid image was loaded.');
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
					throw new LogicException('An error has occured while creating image.');
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
					throw new LogicException('An error has occured while creating image.');
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
					throw new LogicException('An error has occured while creating image.');
				}
				else
				{
					$wtCreated = true;
				}
			}
		}
		else
		{
			throw new LogicException('Unknown or unsupported image type.');
		}

		if ($wtCreated)
		{
			$watermark_dst_width = $watermark_src_width = imagesx($filter);
			$watermark_dst_height = $watermark_src_height = imagesy($filter);

			if ($watermark_dst_width > $imgProperties->width || $watermark_dst_height > $imgProperties->height)
			{
				$canvas_width = $imgProperties->width - abs($wtProperties->width);
				$canvas_height = $imgProperties->height - abs($wtProperties->height);

				if (($watermark_src_width / $canvas_width) > ($watermark_src_height / $canvas_height))
				{
					$watermark_dst_width = $canvas_width;
					$watermark_dst_height = (int) ($watermark_src_height * ($canvas_width / $watermark_src_width));
				}
				else
				{
					$watermark_dst_height = $canvas_height / 2;
					$watermark_dst_width = (int) ($watermark_src_width * ($canvas_height / $watermark_src_height)) / 2;
				}
			}

			$watermark_x = 0;
			$watermark_x_offset = (int) isset($properties['watermark_x_offset']) ? $properties['watermark_x_offset'] : 10;
			$watermark_y = 0;
			$watermark_y_offset = (int) isset($properties['watermark_y_offset']) ? $properties['watermark_y_offset'] : 10;

			if (isset($properties['watermark_x']) && is_numeric($properties['watermark_x']))
			{
				$watermark_x = ($wtProperties->width < 0) ? $imgProperties->width - $watermark_dst_width + $wtProperties->width : $wtProperties->width;
			}
			else
			{
				if (strpos($position, 'r') !== false)
				{
					$watermark_x = ($imgProperties->width - $watermark_dst_width) - $watermark_x_offset;
				}
				else
				{
					if (strpos($position, 'l') !== false)
					{
						$watermark_x = $watermark_x_offset;
					}
					else
					{
						$watermark_x = ($imgProperties->width - $watermark_dst_width) / 2;
					}
				}
			}

			if (isset($properties['watermark_y']) && is_numeric($properties['watermark_y']))
			{
				$watermark_y = ($wtProperties->height < 0) ? $imgProperties->height - $watermark_dst_height + $wtProperties->height : $wtProperties->height;
			}
			else
			{
				if (strpos($position, 'b') !== false)
				{
					$watermark_y = ($imgProperties->height - $watermark_dst_height) - $watermark_y_offset;
				}
				else
				{
					if (strpos($position, 't') !== false)
					{
						$watermark_y = $watermark_y_offset;
					}
					else
					{
						$watermark_y = ($imgProperties->height - $watermark_dst_height) / 2;
					}
				}
			}

			imagealphablending($image->handle, true);
			imagecopyresampled($image->handle, $filter, $watermark_x, $watermark_y, 0, 0, $watermark_dst_width, $watermark_dst_height, $watermark_src_width, $watermark_src_height);
		}
		else
		{
			throw new LogicException('An error has occured.');
		}

		if ($imgProperties->mime == 'image/gif')
		{
			$image->toFile($directory . DIRECTORY_SEPARATOR . $filename, IMAGETYPE_GIF);
		}
		elseif ($imgProperties->mime == 'image/jpeg')
		{
			$quality = isset($properties['output_quality']) ? (int) $properties['output_quality'] : 75;
			$image->toFile($directory . DIRECTORY_SEPARATOR . $filename, IMAGETYPE_JPEG, array('quality' => $quality));
		}
		elseif ($imgProperties->mime == 'image/png')
		{
			$quality = isset($properties['output_quality']) ? (int) $properties['output_quality'] : 3;
			$image->toFile($directory . DIRECTORY_SEPARATOR . $filename, IMAGETYPE_PNG, array('quality' => $quality));
		}
	}
}
