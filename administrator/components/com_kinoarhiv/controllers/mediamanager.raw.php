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
 * Mediamanager controller class.
 *
 * @since  3.1
 */
class KinoarhivControllerMediamanager extends JControllerLegacy
{
	/**
	 * Method to upload media content and proccess some media items, like images.
	 *
	 * @return string  JSON string with result
	 *
	 * @since  3.1
	 */
	public function upload()
	{
		if (!KAComponentHelper::checkToken('post'))
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
		$dest_dir = KAContentHelper::getPath($section, $type, $tab, $id);

		// Remove old files
		$cleanup_dir = true;

		// Temp file age in seconds
		$max_file_age = 5 * 3600;

		// Create target dir
		if (!file_exists($dest_dir))
		{
			if (!JFolder::create($dest_dir))
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

		$file_ext = JFile::getExt($filename);
		$file_key = 'com_kinoarhiv.uploads.' . $user->get('id');

		// Validate filename
		if (preg_match("/[^a-z0-9_.,\[\]@'%()\s-]/i", $filename))
		{
			if ($app->getUserState($file_key) == '')
			{
				$filename = str_replace('.', '', uniqid(rand(), true)) . '.' . $file_ext;
				$app->setUserState($file_key, $filename);
			}
			else
			{
				$filename = $app->getUserState($file_key);
			}
		}

		$file_path = JPath::clean($dest_dir . '/' . $filename);

		// Chunking might be enabled
		$chunk  = $this->input->request->getInt('chunk', 0);
		$chunks = $this->input->request->getInt('chunks', 0);

		// Check if file with the same name allready exists
		if (is_file($file_path))
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
		if (!$this->checkFileExt($file_ext))
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
		if ($cleanup_dir)
		{
			if (!is_dir($dest_dir) || !$dir = opendir($dest_dir))
			{
				header('HTTP/1.0 500 Server error', true, 500);

				jexit(
					json_encode(
						array(
							'success' => false,
							'message' => JText::_('COM_KA_TRAILERS_UPLOAD_FOLDER_OPEN_ERR')
						)
					)
				);
			}

			while (($file = readdir($dir)) !== false)
			{
				$tmpfilePath = JPath::clean($dest_dir . '/' . $file);

				// If temp file is current file proceed to the next
				if ($tmpfilePath == "{$file_path}.part")
				{
					continue;
				}

				// Remove temp file if it is older than the max age and is not the current file
				if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $max_file_age))
				{
					JFile::delete($tmpfilePath);
				}
			}

			closedir($dir);
		}

		// Open temp file
		if (!$out = @fopen("{$file_path}.part", $chunks ? "ab" : "wb"))
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
			if (!rename("{$file_path}.part", $file_path))
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
			if (!$this->checkMime($file_path, $file_ext))
			{
				JFile::delete($file_path);

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
			$app->setUserState($file_key, '');

			$post_proc = $this->postProcessUploads($dest_dir, $filename);

			if (is_array($post_proc))
			{
				header('HTTP/1.0 500 Server error', true, 500);

				jexit(
					json_encode(
						array(
							'success' => false,
							'message' => $post_proc['message']
						)
					)
				);
			}
		}

		// Return Success JSON response
		jexit(
			json_encode(
				array(
					'success' => true,
					'message' => ''
				)
			)
		);
	}

	/**
	 * Post process uploaded file.
	 *
	 * @param   string  $dest_dir  Path to a folder.
	 * @param   string  $filename  Filename.
	 *
	 * @return  mixed  Boolean true on success, array with errors otherwise.
	 *
	 * @since   3.1
	 */
	private function postProcessUploads($dest_dir, $filename)
	{
		$app     = JFactory::getApplication();
		$section = $app->input->get('section', '', 'word');
		$type    = $app->input->get('type', '', 'word');
		$tab     = $app->input->get('tab', 0, 'int');
		$upload  = $app->input->get('upload', '', 'word');
		$result  = true;

		if (($section == 'movie' && $type == 'gallery')
			|| ($section == 'name' && $type == 'gallery'))
		{
			$result = $this->postProcessImageUploads($dest_dir, $filename, $section, $type, $tab);
		}
		elseif ($section == 'movie' && $type == 'trailers')
		{
			if ($upload == 'images')
			{
				$result = $this->postProcessImageUploads($dest_dir, $filename, $section, $type, $tab);
			}
			elseif ($upload == 'video')
			{
				$result = $this->postProcessVideoUploads($dest_dir, $filename);
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

		return $result;
	}

	/**
	 * Post process uploaded image.
	 *
	 * @param   string   $dest_dir  Path to a folder.
	 * @param   string   $filename  Filename.
	 * @param   string   $section   Type of the item. Can be 'movie' or 'name'.
	 * @param   string   $type      Type of the section. Can be 'gallery', 'trailers', 'soundtracks'
	 * @param   integer  $tab       Tab number from gallery(or empty value for 'trailers', 'soundtracks').
	 *
	 * @return  mixed  Boolean true on success, array with errors otherwise.
	 *
	 * @since   3.1
	 */
	private function postProcessImageUploads($dest_dir, $filename, $section, $type, $tab)
	{
		jimport('joomla.filesystem.file');
		jimport('administrator.components.com_kinoarhiv.libraries.image', JPATH_ROOT);

		$params    = JComponentHelper::getParams('com_kinoarhiv');
		$app       = JFactory::getApplication();
		$model     = $this->getModel('mediamanagerItem');
		$file_path = JPath::clean($dest_dir . '/' . $filename);
		$id        = $app->input->get('id', 0, 'int');
		$frontpage = $app->input->get('frontpage', 0, 'int');

		$image = new KAImage;
		$image->loadFile($file_path);
		$image_prop  = $image->getImageFileProperties($file_path);
		$orig_width  = $image_prop->width;
		$orig_height = $image_prop->height;

		if ($section == 'movie')
		{
			if ($type == 'gallery')
			{
				if ($tab == 1)
				{
					$width  = (int) $params->get('size_x_wallpp');
					$height = ($width * $orig_height) / $orig_width;
				}
				elseif ($tab == 2)
				{
					$width  = (int) $params->get('size_x_posters');
					$height = ($width * $orig_height) / $orig_width;
				}
				elseif ($tab == 3)
				{
					$width  = (int) $params->get('size_x_scr');
					$height = ($width * $orig_height) / $orig_width;
				}
				else
				{
					return array('success' => false, 'message' => 'Wrong tab');
				}

				// Add watermark
				if ($params->get('upload_gallery_watermark_image_on') == 1)
				{
					$watermark_img = $params->get('upload_gallery_watermark_image');
					$options       = array();

					if ($image_prop->type == 2)
					{
						$options['output_quality'] = (int) $params->get('upload_quality_images_jpg');
					}
					elseif ($image_prop->type == 3)
					{
						$options['output_quality'] = (int) $params->get('upload_quality_images_png');
					}

					if (!empty($watermark_img) && file_exists($watermark_img))
					{
						$image->addWatermark($dest_dir, $filename, $watermark_img, 'br', $options);
					}
				}

				$image->makeThumbs($dest_dir, $filename, $width . 'x' . $height, 1, $dest_dir, false);
				$model->saveImageInDB('movie', $id, $filename, array($orig_width, $orig_height), $tab, $frontpage);
			}
			elseif ($type == 'trailers')
			{
				$width  = (int) $params->get('player_width');
				$height = ($width * $orig_height) / $orig_width;
				$image->resize($width, $height, false);
				$image->toFile($file_path, $image_prop->type);

				// Add watermark
				if ($params->get('upload_gallery_watermark_image_on') == 1)
				{
					$watermark_img = $params->get('upload_gallery_watermark_image');
					$options       = array();

					if ($image_prop->type == 2)
					{
						$options['output_quality'] = (int) $params->get('upload_quality_images_jpg');
					}
					elseif ($image_prop->type == 3)
					{
						$options['output_quality'] = (int) $params->get('upload_quality_images_png');
					}

					if (!empty($watermark_img) && file_exists($watermark_img))
					{
						$image->addWatermark($dest_dir, $filename, $watermark_img, 'br', $options);
					}
				}

				// Item ID == id field from #__ka_trailers table
				$model->saveImageInDB('trailer', $app->input->getInt('item_id', 0), $filename);
			}
		}
		elseif ($section == 'name')
		{
			if ($type == 'gallery')
			{
				if ($tab == 1)
				{
					$width  = (int) $params->get('size_x_wallpp');
					$height = ($width * $orig_height) / $orig_width;
				}
				elseif ($tab == 2)
				{
					$width  = (int) $params->get('size_x_posters');
					$height = ($width * $orig_height) / $orig_width;
				}
				elseif ($tab == 3)
				{
					$width  = (int) $params->get('size_x_photo');
					$height = ($width * $orig_height) / $orig_width;
				}
				else
				{
					return array('success' => false, 'message' => 'Wrong tab');
				}

				// Add watermark
				if ($params->get('upload_gallery_watermark_image_on') == 1)
				{
					$watermark_img = $params->get('upload_gallery_watermark_image');
					$options       = array();

					if ($image_prop->type == 2)
					{
						$options['output_quality'] = (int) $params->get('upload_quality_images_jpg');
					}
					elseif ($image_prop->type == 3)
					{
						$options['output_quality'] = (int) $params->get('upload_quality_images_png');
					}

					if (!empty($watermark_img) && file_exists($watermark_img))
					{
						$image->addWatermark($dest_dir, $filename, $watermark_img, 'br', $options);
					}
				}

				$image->makeThumbs($dest_dir, $filename, $width . 'x' . $height, 1, $dest_dir, false);
				$model->saveImageInDB('name', $id, $filename, array($orig_width, $orig_height), $tab, $frontpage);
			}
		}

		return true;
	}

	/**
	 * Post process uploaded video.
	 *
	 * @param   string  $dest_dir  Path to a folder.
	 * @param   string  $filename  Filename.
	 *
	 * @return  mixed  Boolean true on success, array with errors otherwise.
	 *
	 * @since   3.1
	 */
	private function postProcessVideoUploads($dest_dir, $filename)
	{
		jimport('components.com_kinoarhiv.libraries.filesystem', JPATH_ROOT);
		jimport('administrator.components.com_kinoarhiv.libraries.media', JPATH_ROOT);

		$model     = $this->getModel('mediamanagerItem');
		$media     = KAMedia::getInstance();
		$file_path = JPath::clean($dest_dir . '/' . $filename);
		$finfo     = json_decode($media->getVideoInfo($file_path), true);
		$file_mime = KAFilesystem::getInstance()->detectMime($file_path);
		$width     = 0;
		$height    = 0;

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
			'type'       => $file_mime,
			'resolution' => $width . 'x' . $height
		);

		$result = $model->saveFileinfoData($data, array('list' => 'video', 'new' => 1));

		return $result;
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

		$model = $this->getModel('mediamanagerItem');
		$lang_list = KALanguage::listOfLanguages();

		if (preg_match('#subtitles\.(.*?)\.#si', $filename, $matches))
		{
			$lang_code = strtolower($matches[1]);
		}
		else
		{
			$lang_code = 'en';
		}

		$data   = array(
			'file'      => $filename,
			'lang'      => $lang_list[$lang_code],
			'lang_code' => $lang_code,
			'default'   => false
		);
		$result = $model->saveFileinfoData($data, array('list' => 'subtitles', 'new' => 1));

		return $result;
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
		$model  = $this->getModel('mediamanagerItem');
		$data   = array('file' => $filename);
		$result = $model->saveFileinfoData($data, array('list' => 'chapters', 'new' => 1));

		return $result;
	}

	/**
	 * Check file extention.
	 *
	 * @param   string  $file_ext  File extention to check.
	 *
	 * @return  boolean
	 *
	 * @since   3.1
	 */
	private function checkFileExt($file_ext)
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

		return (bool) in_array($file_ext, $allowed);
	}

	/**
	 * Check file mime-type. Do not check mime by upload type.
	 *
	 * @param   string  $path      File to check.
	 * @param   string  $file_ext  File extension.
	 *
	 * @return  boolean
	 *
	 * @since   3.1
	 */
	private function checkMime($path, $file_ext)
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
		$file_mime = $fs->detectMime($path);

		// Get all allowed extensions from settings and reduce array with mimes to these extensions.
		$mimes = array_intersect_key($fs->mimes(), array_flip($exts));

		if (array_key_exists($file_ext, $mimes))
		{
			$mime = $mimes[$file_ext];

			if (is_array($mime))
			{
				foreach ($mime as $value)
				{
					if ($value == $file_mime)
					{
						return true;
					}
				}
			}
			else
			{
				if ($mime == $file_mime)
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
