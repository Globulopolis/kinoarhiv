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
 * Mediamanager controller class.
 *
 * @since  3.1
 */
class KinoarhivControllerMediamanager extends JControllerLegacy
{
	/**
	 * Method to upload media content and proccess some media items, like images.
	 *
	 * @return  mixed  JSON string with result
	 *
	 * @since   3.1
	 */
	public function upload()
	{
		if (!KAComponentHelper::checkToken())
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JINVALID_TOKEN')));

			return;
		}

		// Send headers to prevent caching
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Cache-Control: post-check=0, pre-check=0', false);
		header('Pragma: no-cache');

		@set_time_limit(5 * 60);

		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		jimport('components.com_kinoarhiv.helpers.content', JPATH_ROOT);

		$app      = JFactory::getApplication();
		$user     = JFactory::getUser();
		$section  = $this->input->get('section', '', 'word');
		$type     = $this->input->get('type', '', 'word');
		$tab      = $this->input->get('tab', 0, 'int');
		$id       = $this->input->get('id', 0, 'int');
		$files    = $this->input->files->get('file', array(), 'array');
		$destDir  = KAContentHelper::getPath($section, $type, $tab, $id);
		$insertid = '';

		if (empty($destDir))
		{
			header('HTTP/1.0 500 Server error', true, 500);

			jexit(
				json_encode(
					array(
						'success' => false,
						'message' => JText::_('JERROR_AN_ERROR_HAS_OCCURRED')
					)
				)
			);
		}

		// Remove old files
		$cleanupDir = true;

		// Temp file age in seconds
		$maxFileAge = 5 * 3600;

		// Create target dir
		if (!file_exists($destDir))
		{
			if (!JFolder::create($destDir))
			{
				header('HTTP/1.0 500 Server error', true, 500);

				jexit(
					json_encode(
						array(
							'success' => false,
							'message' => JText::_('COM_KA_TRAILERS_UPLOAD_FOLDER_CREATE_ERR')
						)
					)
				);
			}
		}

		// Get a file name
		if (isset($_REQUEST['name']))
		{
			$filename = $_REQUEST['name'];
		}
		elseif (!empty($_FILES))
		{
			$filename = $files['name'];
		}
		else
		{
			$filename = '';
		}

		$fileExt = JFile::getExt($filename);
		$fileKey = 'com_kinoarhiv.uploads.' . $user->get('id');

		// Check platform and PHP version. PHP < 7 on Windows platform doesn't support unicode filenames.
		if (strtoupper(substr(php_uname('s'), 0, 3)) === 'WIN' && PHP_MAJOR_VERSION < 7)
		{
			if (preg_match("/[^a-z0-9_.,\[\]@'%()\s-]/i", $filename))
			{
				$filename = str_replace('.', '', uniqid(rand(), true)) . '.' . $fileExt;
			}
		}
		else
		{
			$filename = preg_replace("#\x{00a0}#siu", ' ', $filename);
		}

		if ($app->getUserState($fileKey) == '')
		{
			$app->setUserState($fileKey, $filename);
		}

		$filePath = JPath::clean($destDir . '/' . $filename);

		// Chunking might be enabled
		$chunk  = $this->input->request->getInt('chunk', 0);
		$chunks = $this->input->request->getInt('chunks', 0);

		// Check if file with the same name allready exists
		if (is_file($filePath))
		{
			header('HTTP/1.0 500 Server error', true, 500);

			jexit(
				json_encode(
					array(
						'success' => false,
						'message' => JText::_('COM_KA_FILE_EXISTS')
					)
				)
			);
		}

		// Validate file extension
		if (!$this->checkFileExt($fileExt))
		{
			header('HTTP/1.0 500 Server error', true, 500);

			jexit(
				json_encode(
					array(
						'success' => false,
						'message' => JText::_('COM_KA_TRAILERS_UPLOAD_FILE_EXT_ERR')
					)
				)
			);
		}

		// Remove old temp files
		if ($cleanupDir)
		{
			if (!is_dir($destDir) || !$dir = opendir($destDir))
			{
				header('HTTP/1.0 500 Server error', true, 500);

				jexit(
					json_encode(
						array(
							'success' => false,
							'message' => JText::sprintf('COM_KA_TRAILERS_UPLOAD_FOLDER_OPEN_ERR', $destDir)
						)
					)
				);
			}

			while (($file = readdir($dir)) !== false)
			{
				$tmpfilePath = JPath::clean($destDir . '/' . $file);

				// If temp file is current file proceed to the next
				if ($tmpfilePath == "{$filePath}.part")
				{
					continue;
				}

				// Remove temp file if it is older than the max age and is not the current file
				if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge))
				{
					JFile::delete($tmpfilePath);
				}
			}

			closedir($dir);
		}

		// Open temp file
		if (!$out = @fopen("{$filePath}.part", $chunks ? "ab" : "wb"))
		{
			header('HTTP/1.0 500 Server error', true, 500);

			jexit(
				json_encode(
					array(
						'success' => false,
						'message' => JText::_('COM_KA_TRAILERS_UPLOAD_STREAM_O_OPEN_ERR')
					)
				)
			);
		}

		if (!empty($_FILES))
		{
			if ($files['error'] || !is_uploaded_file($files['tmp_name']))
			{
				header('HTTP/1.0 500 Server error', true, 500);

				jexit(
					json_encode(
						array(
							'success' => false,
							'message' => JText::_('ERROR')
						)
					)
				);
			}

			// Read binary input stream and append it to temp file
			if (!$in = @fopen($files['tmp_name'], "rb"))
			{
				header('HTTP/1.0 500 Server error', true, 500);

				jexit(
					json_encode(
						array(
							'success' => false,
							'message' => JText::_('COM_KA_TRAILERS_UPLOAD_STREAM_I_OPEN_ERR')
						)
					)
				);
			}
		}
		else
		{
			if (!$in = @fopen("php://input", "rb"))
			{
				header('HTTP/1.0 500 Server error', true, 500);

				jexit(
					json_encode(
						array(
							'success' => false,
							'message' => JText::_('COM_KA_TRAILERS_UPLOAD_STREAM_I_OPEN_ERR')
						)
					)
				);
			}
		}

		while ($buff = fread($in, 4096))
		{
			fwrite($out, $buff);
		}

		@fclose($out);
		@fclose($in);

		// Check if file has been uploaded
		if (!$chunks || $chunk == $chunks - 1)
		{
			// Strip the temp .part suffix off
			if (!rename("{$filePath}.part", $filePath))
			{
				header('HTTP/1.0 500 Server error', true, 500);

				jexit(
					json_encode(
						array(
							'success' => false,
							'message' => JText::_('ERROR')
						)
					)
				);
			}

			// Check mime. We need to do this after upload to avoid errors with small chunks w/o mime info.
			if (!$this->checkMime($filePath, $fileExt))
			{
				JFile::delete($filePath);

				header('HTTP/1.0 500 Server error', true, 500);

				jexit(
					json_encode(
						array(
							'success' => false,
							'message' => JText::_('COM_KA_TRAILERS_UPLOAD_FILE_MIME_ERR')
						)
					)
				);
			}

			// Remove filename from session
			$app->setUserState($fileKey, '');

			$postProc = $this->postProcessUploads($destDir, $filename);

			if (is_array($postProc))
			{
				JFile::delete($filePath);

				header('HTTP/1.0 500 Server error', true, 500);

				jexit(
					json_encode(
						array(
							'success' => false,
							'message' => $postProc['message']
						)
					)
				);
			}
			else
			{
				if ($postProc !== false)
				{
					$insertid = $postProc;
				}
			}
		}

		// Return Success JSON response
		jexit(
			json_encode(
				array(
					'success'  => true,
					'message'  => '',
					'insertid' => $insertid,
					'filename' => $filename
				)
			)
		);
	}

	/**
	 * Proxy method to post process uploaded file.
	 *
	 * @param   string  $destDir   Path to a folder.
	 * @param   string  $filename  Filename.
	 *
	 * @return  mixed
	 *
	 * @since   3.1
	 */
	private function postProcessUploads($destDir, $filename)
	{
		$app     = JFactory::getApplication();
		$section = $app->input->get('section', '', 'word');
		$type    = $app->input->get('type', '', 'word');
		$tab     = $app->input->get('tab', 0, 'int');
		$upload  = $app->input->get('upload', '', 'word');

		if (($section == 'movie' && $type == 'gallery')
			|| ($section == 'name' && $type == 'gallery')
			|| ($section == 'album' && $type == 'gallery'))
		{
			$result = $this->postProcessImageUploads($destDir, $filename, $section, $type, $tab);
		}
		elseif ($section == 'movie' && $type == 'trailers')
		{
			if ($upload == 'images')
			{
				$result = $this->postProcessImageUploads($destDir, $filename, $section, $type, $tab);
			}
			elseif ($upload == 'video')
			{
				$result = $this->postProcessVideoUploads($destDir, $filename);
			}
			elseif ($upload == 'subtitles')
			{
				$result = $this->postProcessSubtitleUploads($filename);
			}
			elseif ($upload == 'chapters')
			{
				$result = $this->postProcessChapterUploads($filename);
			}
		}
		else
		{
			return false;
		}

		return $result;
	}

	/**
	 * Post process uploaded image.
	 *
	 * @param   string   $destDir   Path to a folder.
	 * @param   string   $filename  Filename.
	 * @param   string   $section   Type of the item. Can be 'movie' or 'name'.
	 * @param   string   $type      Type of the section. Can be 'gallery', 'trailers', 'soundtracks'
	 * @param   integer  $tab       Tab number from gallery(or empty value for 'trailers', 'soundtracks').
	 *
	 * @return  mixed  Last insert ID on success, array with errors otherwise.
	 *
	 * @since   3.1
	 */
	private function postProcessImageUploads($destDir, $filename, $section, $type, $tab)
	{
		jimport('joomla.filesystem.file');
		jimport('administrator.components.com_kinoarhiv.libraries.image', JPATH_ROOT);

		$params    = JComponentHelper::getParams('com_kinoarhiv');
		$app       = JFactory::getApplication();

		/** @var KinoarhivModelMediamanagerItem $model */
		$model     = $this->getModel('mediamanagerItem');
		$filePath  = JPath::clean($destDir . '/' . $filename);
		$id        = $app->input->get('id', 0, 'int');
		$frontpage = $app->input->get('frontpage', 0, 'int');
		$imgSave   = '';

		$image = new KAImage;

		try
		{
			$image->loadFile($filePath);
		}
		catch (Exception $e)
		{
			return array('success' => false, 'message' => $e->getMessage());
		}

		$imageProp  = $image->getImageFileProperties($filePath);
		$origWidth  = $imageProp->width;
		$origHeight = $imageProp->height;

		if ($section == 'movie')
		{
			if ($type == 'gallery')
			{
				if ($tab == 1)
				{
					$width  = (int) $params->get('size_x_wallpp');
					$height = ($width * $origHeight) / $origWidth;
				}
				elseif ($tab == 2)
				{
					$width  = (int) $params->get('size_x_posters');
					$height = ($width * $origHeight) / $origWidth;
				}
				elseif ($tab == 3)
				{
					$width  = (int) $params->get('size_x_scr');
					$height = ($width * $origHeight) / $origWidth;
				}
				else
				{
					return array('success' => false, 'message' => 'Wrong tab');
				}

				// Add watermark
				if ($params->get('upload_gallery_watermark_image_on') == 1)
				{
					$watermarkImg = $params->get('upload_gallery_watermark_image');
					$options      = array();

					if ($imageProp->type == 2)
					{
						$options['output_quality'] = (int) $params->get('upload_quality_images_jpg');
					}
					elseif ($imageProp->type == 3)
					{
						$options['output_quality'] = (int) $params->get('upload_quality_images_png');
					}

					if (!empty($watermarkImg) && file_exists($watermarkImg))
					{
						$image->addWatermark($destDir, $filename, $watermarkImg, 'br', $options);
					}
				}

				$image->makeThumbs($destDir, $filename, $width . 'x' . $height, 1, $destDir, false);
				$imgSave = $model->saveImageInDB('movie', $id, $filename, array($origWidth, $origHeight), $tab, $frontpage);
			}
			elseif ($type == 'trailers')
			{
				$width  = (int) $params->get('player_width');
				$height = ($width * $origHeight) / $origWidth;
				$image->resize($width, $height, false);
				$image->toFile($filePath, $imageProp->type);

				// Add watermark
				if ($params->get('upload_gallery_watermark_image_on') == 1)
				{
					$watermarkImg = $params->get('upload_gallery_watermark_image');
					$options      = array();

					if ($imageProp->type == 2)
					{
						$options['output_quality'] = (int) $params->get('upload_quality_images_jpg');
					}
					elseif ($imageProp->type == 3)
					{
						$options['output_quality'] = (int) $params->get('upload_quality_images_png');
					}

					if (!empty($watermarkImg) && file_exists($watermarkImg))
					{
						$image->addWatermark($destDir, $filename, $watermarkImg, 'br', $options);
					}
				}

				// Item ID == id field from #__ka_trailers table
				$imgSave = $model->saveImageInDB('trailer', $app->input->getInt('item_id', 0), $filename);
			}
		}
		elseif ($section == 'name')
		{
			if ($type == 'gallery')
			{
				if ($tab == 1)
				{
					$width  = (int) $params->get('size_x_wallpp');
					$height = ($width * $origHeight) / $origWidth;
				}
				elseif ($tab == 2)
				{
					$width  = (int) $params->get('size_x_posters');
					$height = ($width * $origHeight) / $origWidth;
				}
				elseif ($tab == 3)
				{
					$width  = (int) $params->get('size_x_photo');
					$height = ($width * $origHeight) / $origWidth;
				}
				else
				{
					return array('success' => false, 'message' => 'Wrong tab');
				}

				// Add watermark
				if ($params->get('upload_gallery_watermark_image_on') == 1)
				{
					$watermarkImg = $params->get('upload_gallery_watermark_image');
					$options      = array();

					if ($imageProp->type == 2)
					{
						$options['output_quality'] = (int) $params->get('upload_quality_images_jpg');
					}
					elseif ($imageProp->type == 3)
					{
						$options['output_quality'] = (int) $params->get('upload_quality_images_png');
					}

					if (!empty($watermarkImg) && file_exists($watermarkImg))
					{
						$image->addWatermark($destDir, $filename, $watermarkImg, 'br', $options);
					}
				}

				$image->makeThumbs($destDir, $filename, $width . 'x' . $height, 1, $destDir, false);
				$imgSave = $model->saveImageInDB('name', $id, $filename, array($origWidth, $origHeight), $tab, $frontpage);
			}
		}
		elseif ($section == 'album')
		{
			if ($type == 'gallery')
			{
				if ($tab == 1 || $tab == 2 || $tab == 3 || $tab == 4)
				{
					$width  = (int) $params->get('music_covers_size');
					$height = ($width * $origHeight) / $origWidth;
				}
				else
				{
					return array('success' => false, 'message' => 'Wrong tab');
				}

				// Add watermark
				if ($params->get('upload_gallery_watermark_image_on') == 1)
				{
					$watermarkImg = $params->get('upload_gallery_watermark_image');
					$options      = array();

					if ($imageProp->type == 2)
					{
						$options['output_quality'] = (int) $params->get('upload_quality_images_jpg');
					}
					elseif ($imageProp->type == 3)
					{
						$options['output_quality'] = (int) $params->get('upload_quality_images_png');
					}

					if (!empty($watermarkImg) && file_exists($watermarkImg))
					{
						$image->addWatermark($destDir, $filename, $watermarkImg, 'br', $options);
					}
				}

				$image->makeThumbs($destDir, $filename, $width . 'x' . $height, 1, $destDir, false);
				$imgSave = $model->saveImageInDB('album', $id, $filename, array($origWidth, $origHeight), $tab, $frontpage);
			}
		}

		return $imgSave;
	}

	/**
	 * Post process uploaded video.
	 *
	 * @param   string  $destDir   Path to a folder.
	 * @param   string  $filename  Filename.
	 *
	 * @return  mixed  Boolean true on success, array with errors otherwise.
	 *
	 * @since   3.1
	 */
	private function postProcessVideoUploads($destDir, $filename)
	{
		jimport('components.com_kinoarhiv.libraries.filesystem', JPATH_ROOT);
		jimport('administrator.components.com_kinoarhiv.libraries.media', JPATH_ROOT);

		/** @var KinoarhivModelMediamanagerItem $model */
		$model    = $this->getModel('mediamanagerItem');
		$media    = KAMedia::getInstance();
		$filePath = JPath::clean($destDir . '/' . $filename);
		$finfo    = json_decode($media->getVideoInfo($filePath), true);
		$fileMime = KAFilesystem::getInstance()->detectMime($filePath);
		$width    = 0;
		$height   = 0;

		if (is_null($finfo) || count($finfo) < 1)
		{
			return false;
		}

		if (array_key_exists('streams', $finfo))
		{
			if (array_key_exists(0, $finfo['streams']))
			{
				$width  = $finfo['streams'][0]['width'];
				$height = $finfo['streams'][0]['height'];
			}
		}

		$data = array(
			'src'        => $filename,
			'type'       => $fileMime,
			'resolution' => $width . 'x' . $height
		);

		return $model->saveFileinfoData($data, array('list' => 'video', 'new' => 1));
	}

	/**
	 * Post process uploaded subtitles.
	 *
	 * @param   string  $filename  Filename.
	 *
	 * @return  mixed  Boolean true on success, array with errors otherwise.
	 *
	 * @since   3.1
	 */
	private function postProcessSubtitleUploads($filename)
	{
		jimport('administrator.components.com_kinoarhiv.libraries.language', JPATH_ROOT);

		/** @var KinoarhivModelMediamanagerItem $model */
		$model = $this->getModel('mediamanagerItem');
		$langList = KALanguage::listOfLanguages();

		if (preg_match('#subtitles\.(.*?)\.#si', $filename, $matches))
		{
			$langCode = strtolower($matches[1]);
		}
		else
		{
			$langCode = 'en';
		}

		$data   = array(
			'file'      => $filename,
			'lang'      => $langList[$langCode],
			'lang_code' => $langCode,
			'default'   => false
		);

		return $model->saveFileinfoData($data, array('list' => 'subtitles', 'new' => 1));
	}

	/**
	 * Post process uploaded chapters.
	 *
	 * @param   string  $filename  Filename.
	 *
	 * @return  mixed  Boolean true on success, array with errors otherwise.
	 *
	 * @since   3.1
	 */
	private function postProcessChapterUploads($filename)
	{
		/** @var KinoarhivModelMediamanagerItem $model */
		$model  = $this->getModel('mediamanagerItem');
		$data   = array('file' => $filename);

		return $model->saveFileinfoData($data, array('list' => 'chapters', 'new' => 1));
	}

	/**
	 * Check file extention.
	 *
	 * @param   string  $fileExt  File extention to check.
	 *
	 * @return  boolean
	 *
	 * @since   3.1
	 */
	private function checkFileExt($fileExt)
	{
		$params = JComponentHelper::getParams('com_kinoarhiv');

		if ($this->input->get('upload') == 'images')
		{
			$allowed = $params->get('upload_mime_images');
		}
		elseif ($this->input->get('upload') == 'video')
		{
			$allowed = $params->get('upload_mime_video');
		}
		elseif ($this->input->get('upload') == 'subtitles')
		{
			$allowed = $params->get('upload_mime_subtitles');
		}
		elseif ($this->input->get('upload') == 'chapters')
		{
			$allowed = $params->get('upload_mime_chapters');
		}
		elseif ($this->input->get('upload') == 'audio')
		{
			$allowed = $params->get('upload_mime_audio');
		}
		else
		{
			return false;
		}

		$allowed = preg_split('/[\s*,\s*]*,+[\s*,\s*]*/', trim($allowed));

		return (bool) in_array($fileExt, $allowed);
	}

	/**
	 * Check file mime-type. Do not check mime by upload type.
	 *
	 * @param   string  $path     File to check.
	 * @param   string  $fileExt  File extension.
	 *
	 * @return  boolean
	 *
	 * @since   3.1
	 */
	private function checkMime($path, $fileExt)
	{
		jimport('components.com_kinoarhiv.libraries.filesystem', JPATH_ROOT);

		$params = JComponentHelper::getParams('com_kinoarhiv');

		if ($this->input->get('upload') == 'images')
		{
			$exts = preg_split('/[\s*,\s*]*,+[\s*,\s*]*/', trim($params->get('upload_mime_images')));
		}
		elseif ($this->input->get('upload') == 'video')
		{
			$exts = preg_split('/[\s*,\s*]*,+[\s*,\s*]*/', trim($params->get('upload_mime_video')));
		}
		elseif ($this->input->get('upload') == 'subtitles')
		{
			$exts = preg_split('/[\s*,\s*]*,+[\s*,\s*]*/', trim($params->get('upload_mime_subtitles')));
		}
		elseif ($this->input->get('upload') == 'chapters')
		{
			$exts = preg_split('/[\s*,\s*]*,+[\s*,\s*]*/', trim($params->get('upload_mime_chapters')));
		}
		elseif ($this->input->get('upload') == 'audio')
		{
			$exts = preg_split('/[\s*,\s*]*,+[\s*,\s*]*/', trim($params->get('upload_mime_audio')));
		}
		else
		{
			return false;
		}

		$fs = KAFilesystem::getInstance();
		$fileMime = $fs->detectMime($path);

		// Get all allowed extensions from settings and reduce array with mimes to these extensions.
		$mimes = array_intersect_key($fs->mimes(), array_flip($exts));

		if (array_key_exists($fileExt, $mimes))
		{
			$mime = $mimes[$fileExt];

			if (is_array($mime))
			{
				foreach ($mime as $value)
				{
					if ($value == $fileMime)
					{
						return true;
					}
				}
			}
			else
			{
				if ($mime == $fileMime)
				{
					return true;
				}
				else
				{
					return false;
				}
			}
		}

		return false;
	}

	/**
	 * Method to edit a data for video/subtitle/chapter.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function editTrailerFile()
	{
		$view = $this->getView('mediamanager', 'raw');
		$model = $this->getModel('mediamanagerItem');
		$view->setModel($model, true);
		$view->display('trailer_edit_fileinfo');
	}
}
