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

jimport('joomla.filesystem.file');

/**
 * Mediamanager controller class.
 *
 * @since  3.0
 */
class KinoarhivControllerMediamanager extends JControllerLegacy
{
	/**
	 * Method to upload media content and proccess some media items, like images.
	 *
	 * @return string  JSON string with result
	 *
	 * @since  3.0
	 */
	public function upload()
	{
		// Send headers to prevent caching
		header('Content-type: application/json');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT', true);
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Cache-Control: post-check=0, pre-check=0', false);
		header('Pragma: no-cache');

		// CORS
		header('Access-Control-Allow-Origin: *');

		if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS')
		{
			header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
			header('Access-Control-Max-Age: 10000');
			header('Access-Control-Allow-Headers: origin, x-csrftoken, content-type, accept');
		}

		if (KAComponentHelper::checkToken() === false)
		{
			header('HTTP/1.0 403 Access denied', true, 403);
			KAComponentHelper::eventLog(JText::_('JINVALID_TOKEN'));

			jexit(
				json_encode(
					array(
						'success' => false,
						'message' => JText::_('JINVALID_TOKEN')
					)
				)
			);
		}

		@set_time_limit(0);

		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		jimport('components.com_kinoarhiv.helpers.content', JPATH_ROOT);

		$app = JFactory::getApplication();

		// Remove old files
		$cleanup_dir = true;

		// Temp file age in seconds
		$max_file_age = 5 * 3600;

		$params = JComponentHelper::getParams('com_kinoarhiv');
		$section = $app->input->get('section', '', 'word');
		$type = $app->input->get('type', '', 'word');
		$tab = $app->input->get('tab', null, 'int');
		$id = $app->input->get('id', null, 'int');
		$file = $app->input->files->get('file');
		$filename = basename($file['name']);
		$file_ext = JFile::getExt($file['name']);

		// Validate filename
		if (preg_match("/[^a-z0-9_.,\[\]@'%()\s-]/i", $filename))
		{
			$filename = str_replace('.', '', uniqid(rand(), true)) . '.' . $file_ext;
		}

		// Validate file extension
		if (!$this->checkFileExt($file_ext, $params))
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

		// Check mime
		if (!$this->checkMime($file['tmp_name'], $file_ext, $params))
		{
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

		$dest_dir = KAContentHelper::getPath($section, $type, $tab, $id);
		$file_path = $dest_dir . DIRECTORY_SEPARATOR . $filename;

		// Chunking might be enabled
		$chunk = $app->input->get('chunk', 0, 'int');
		$chunks = $app->input->get('chunks', 0, 'int');

		// Create target dir
		if (!is_dir($dest_dir))
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

			while (($_file = readdir($dir)) !== false)
			{
				$tmpfilePath = $dest_dir . DIRECTORY_SEPARATOR . $_file;

				// If temp file is current file proceed to the next
				if ($tmpfilePath == "{$file_path}.part")
				{
					continue;
				}

				// Remove temp file if it is older than the max age and is not the current file
				if (preg_match('/\.part$/', $_file) && (filemtime($tmpfilePath) < time() - $max_file_age))
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
			if ($file['error'] || !is_uploaded_file($file['tmp_name']))
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
			if (!$in = @fopen($file['tmp_name'], "rb"))
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
			rename("{$file_path}.part", $file_path);

			if (!$this->postProcessUpload())
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
		/*$params = JComponentHelper::getParams('com_kinoarhiv');
		$model = $this->getModel('mediamanager');
		$id = 0;
		$trailer_id = $app->input->get('item_id', 0, 'int');
		$item_id = $app->input->get('id', 0, 'int');

		if ($chunks < 2 && file_exists($dest_dir . DIRECTORY_SEPARATOR . $filename))
		{
			$ext = strrpos($filename, '.');
			$fileName_a = substr($filename, 0, $ext);
			$fileName_b = substr($filename, $ext);

			$count = 1;
			while (file_exists($dest_dir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b))
				$count++;

			$filename = $fileName_a . '_' . $count . $fileName_b;
		}

		$file_path = $dest_dir . DIRECTORY_SEPARATOR . $filename;

		if ($cleanup_dir)
		{
			if (is_dir($dest_dir) && ($dir = opendir($dest_dir)))
			{
				while (($file = readdir($dir)) !== false)
				{
					$tmpfilePath = $dest_dir . DIRECTORY_SEPARATOR . $file;

					// Remove temp file if it is older than the max age and is not the current file
					if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $max_file_age) && ($tmpfilePath != "{$file_path}.part"))
					{
						JFile::delete($tmpfilePath);
					}
				}

				closedir($dir);
			}
			else
			{
				$app->setHeader('HTTP/1.0', '500 Server error');
				$app->sendHeaders();

				KAComponentHelper::eventLog('Failed to open temp directory.');
				jexit();
			}
		}

		if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
		{
			$contentType = $_SERVER["HTTP_CONTENT_TYPE"];
		}

		if (isset($_SERVER["CONTENT_TYPE"]))
		{
			$contentType = $_SERVER["CONTENT_TYPE"];
		}

		if (strpos($contentType, "multipart") !== false)
		{
			if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name']))
			{
				// Open temp file
				$out = @fopen("{$file_path}.part", $chunk == 0 ? "wb" : "ab");

				if ($out)
				{
					// Read binary input stream and append it to temp file
					$in = @fopen($_FILES['file']['tmp_name'], "rb");

					if ($in)
					{
						while ($buff = fread($in, 4096))
							fwrite($out, $buff);
					}
					else
					{
						$app->setHeader('HTTP/1.0', '500 Server error');
						$app->sendHeaders();

						KAComponentHelper::eventLog('Failed to open input stream.');
						jexit();
					}

					@fclose($in);
					@fclose($out);
					JFile::delete($_FILES['file']['tmp_name']);
				}
				else
				{
					$app->setHeader('HTTP/1.0', '500 Server error');
					$app->sendHeaders();

					KAComponentHelper::eventLog('Failed to open output stream.');
					jexit();
				}
			}
			else
			{
				$app->setHeader('HTTP/1.0', '500 Server error');
				$app->sendHeaders();

				KAComponentHelper::eventLog('Failed to move uploaded file.');
				jexit();
			}
		}
		else
		{
			// Open temp file
			$out = @fopen("{$file_path}.part", $chunk == 0 ? "wb" : "ab");

			if ($out)
			{
				// Read binary input stream and append it to temp file
				$in = @fopen("php://input", "rb");

				if ($in)
				{
					while ($buff = fread($in, 4096))
						fwrite($out, $buff);
				}
				else
				{
					$app->setHeader('HTTP/1.0', '500 Server error');
					$app->sendHeaders();

					KAComponentHelper::eventLog('Failed to open input stream.');
					jexit();
				}

				@fclose($in);
				@fclose($out);
			}
			else
			{
				$app->setHeader('HTTP/1.0', '500 Server error');
				$app->sendHeaders();

				KAComponentHelper::eventLog('Failed to open output stream.');
				jexit();
			}
		}

		// Check if file has been uploaded
		if (!$chunks || $chunk == $chunks - 1)
		{
			// Strip the temp .part suffix off
			rename("{$file_path}.part", $file_path);

			// Proccess watermarks and thumbnails
			JLoader::register('KAImage', JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'image.php');
			$image = new KAImage;
			$section = $app->input->get('section', '', 'word');

			if ($section == 'movie')
			{
				if ($app->input->get('type') == 'gallery')
				{
					$tab = $app->input->get('tab', 0, 'int');
					$orig_image = @getimagesize($file_path);

					if ($tab == 1)
					{
						$width = (int) $params->get('size_x_wallpp');
						$height = ($width * $orig_image[1]) / $orig_image[0];
					}
					elseif ($tab == 2)
					{
						$width = (int) $params->get('size_x_posters');
						$height = ($width * $orig_image[1]) / $orig_image[0];
					}
					elseif ($tab == 3)
					{
						$width = (int) $params->get('size_x_scr');
						$height = ($width * $orig_image[1]) / $orig_image[0];
					}

					// Add watermark
					if ($params->get('upload_gallery_watermark_image_on') == 1)
					{
						$watermark_img = $params->get('upload_gallery_watermark_image');

						if (!empty($watermark_img) && file_exists($watermark_img))
						{
							$image->addWatermark($dest_dir, $filename, $watermark_img);
						}
					}

					$image->_createThumbs($dest_dir, $filename, $width . 'x' . $height, 1, $dest_dir, false);
					$result = $model->saveImageInDB($filename, $orig_image, 'movie', $tab, $item_id, $frontpage);
					$id = $result;
				}
				elseif ($app->input->get('type') == 'trailers')
				{
					// Get the movie transliterated alias
					$fs_alias = $model->getFilesystemAlias($section, $item_id, true);

					if ($app->input->get('upload') == 'video')
					{
						JLoader::register('KAMedia', JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'media.php');
						$media = KAMedia::getInstance();

						$rn_dest_dir = $dest_dir . DIRECTORY_SEPARATOR;
						$old_filename = $rn_dest_dir . $filename;
						$ext = JFile::getExt($old_filename);
						$video_info = json_decode($media->getVideoInfo($rn_dest_dir . $filename));
						$video_height = isset($video_info->streams[0]) ? $video_info->streams[0]->height : 0;
						$rn_filename = $fs_alias . '-' . $trailer_id . '-' . $item_id . '.' . $video_height . 'p.' . $ext;
						rename($old_filename, $rn_dest_dir . $rn_filename);

						$model->saveVideo($rn_filename, $trailer_id, $item_id);
					}
					elseif ($app->input->get('upload') == 'subtitles')
					{
						if (preg_match('#subtitles\.(.*?)\.#si', $filename, $matches))
						{
							$lang_code = strtolower($matches[1]);
						}
						else
						{
							$lang_code = 'en';
						}

						$rn_dest_dir = $dest_dir . DIRECTORY_SEPARATOR;
						$old_filename = $rn_dest_dir . $filename;
						$ext = JFile::getExt($old_filename);
						$rn_filename = $fs_alias . '-' . $trailer_id . '.subtitles.' . $lang_code . '.' . $ext;
						rename($old_filename, $rn_dest_dir . $rn_filename);

						$model->saveSubtitles($rn_filename, $trailer_id, $item_id, false);
					}
					elseif ($app->input->get('upload') == 'chapters')
					{
						$rn_dest_dir = $dest_dir . DIRECTORY_SEPARATOR;
						$old_filename = $rn_dest_dir . $filename;
						$ext = JFile::getExt($old_filename);
						$rn_filename = $fs_alias . '-' . $trailer_id . '.chapters.' . $ext;
						rename($old_filename, $rn_dest_dir . $rn_filename);

						$result = $model->saveChapters($rn_filename, $trailer_id, $item_id);

						if (is_int($result))
						{
							$id = $result;
						}
					}
					elseif ($app->input->get('upload') == 'images')
					{
						$rn_dest_dir = $dest_dir . DIRECTORY_SEPARATOR;
						$old_filename = $rn_dest_dir . $filename;
						$ext = JFile::getExt($old_filename);
						$rn_filename = $fs_alias . '-' . $trailer_id . '.' . $ext;
						rename($old_filename, $rn_dest_dir . $rn_filename);

						if ($params->get('upload_gallery_watermark_image_on') == 1)
						{
							$watermark_img = $params->get('upload_gallery_watermark_image');

							if (!empty($watermark_img) && file_exists($watermark_img))
							{
								$image->addWatermark($dest_dir, $rn_filename, $watermark_img);
							}
						}

						list($width, $height) = @getimagesize($rn_dest_dir . $rn_filename);
						$th_w = (int) $params->get('player_width');
						$th_h = ($height * $th_w) / $width;
						$image->_createThumbs($dest_dir, $rn_filename, $th_w . 'x' . $th_h, 1, $dest_dir, null);
						$result = $model->saveImageInDB($rn_filename, array(), 'trailer', null, $trailer_id);
						$id = $result['filename'];
					}
				}
			}
			elseif ($section == 'name')
			{
				if ($app->input->get('type') == 'gallery')
				{
					$tab = $app->input->get('tab', 0, 'int');
					$orig_image = @getimagesize($file_path);

					if ($tab == 1)
					{
						$width = (int) $params->get('size_x_wallpp');
						$height = ($width * $orig_image[1]) / $orig_image[0];
					}
					elseif ($tab == 2)
					{
						$width = (int) $params->get('size_x_posters');
						$height = ($width * $orig_image[1]) / $orig_image[0];
					}
					elseif ($tab == 3)
					{
						$width = (int) $params->get('size_x_photo');
						$height = ($width * $orig_image[1]) / $orig_image[0];
					}

					// Add watermark
					if ($params->get('upload_gallery_watermark_image_on') == 1)
					{
						$watermark_img = $params->get('upload_gallery_watermark_image');

						if (!empty($watermark_img) && file_exists($watermark_img))
						{
							$image->addWatermark($dest_dir, $filename, $watermark_img);
						}
					}

					$image->_createThumbs($dest_dir, $filename, $width . 'x' . $height, 1, $dest_dir, false);
					$result = $model->saveImageInDB($filename, $orig_image, 'name', $tab, $item_id, $frontpage);
					$id = $result;
				}
			}
		}

		// Success response. Don't change the ID format, because it's require for proper handling of some items in templates.
		$response = json_encode(
			array(
				'jsonrpc' => '2.0',
				'result'  => null,
				'id'      => is_array($id) ? json_encode($id) : $id
			)
		);
		die($response);*/
	}

	/**
	 * Post process uploaded file.
	 *
	 * @return  boolean
	 *
	 * @since   3.1
	 */
	private function postProcessUpload()
	{
		$app = JFactory::getApplication();
		$frontpage = $app->input->get('frontpage', 0, 'int');

		return true;
	}

	/**
	 * Check file extention.
	 *
	 * @param   string  $file_ext  File extention to check
	 * @param   object  &$params   Component parameters.
	 *
	 * @return  boolean
	 *
	 * @since   3.1
	 */
	private function checkFileExt($file_ext, &$params)
	{
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
	 * @param   string  $path      File mime to check.
	 * @param   string  $file_ext  File extension.
	 * @param   object  &$params   Component parameters.
	 *
	 * @return  boolean
	 *
	 * @since   3.1
	 */
	private function checkMime($path, $file_ext, &$params)
	{
		jimport('components.com_kinoarhiv.libraries.filesystem', JPATH_ROOT);
		jimport('administrator.components.com_kinoarhiv.helpers.filesystem', JPATH_ROOT);

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

		$file_mime = KAFilesystem::getInstance()->detectMime($path);

		// Get all allowed extensions from settings and reduce array with mimes to these extensions.
		$mimes = array_intersect_key(KAFilesystemHelper::mimes(), array_flip($exts));

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
					else
					{
						return false;
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
	 * Save the manually set order of files on trailers edit page.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function saveOrderTrailerFiles()
	{
		if (!KAComponentHelper::checkToken('post'))
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JINVALID_TOKEN')));

			return;
		}

		$app = JFactory::getApplication();
		$model = $this->getModel('mediamanageritem');
		$item_id = $app->input->get('item_id', 0, 'int');
		$items = $app->input->get('ord', array(), 'array');
		$type = $app->input->get('type', '', 'word');
		$result = $model->saveOrderTrailerFiles($item_id, $items, $type);

		if (!$result)
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JERROR_AN_ERROR_HAS_OCCURRED')));

			return;
		}

		echo json_encode(array('success' => true, 'message' => ''));
	}

	/**
	 * Unset default subtitle for trailer.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function subtitleUnsetDefault()
	{
		$this->subtitleSetDefault(false);
	}

	/**
	 * Set default subtitle for trailer.
	 *
	 * @param   boolean  $isDefault  Action state
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function subtitleSetDefault($isDefault = true)
	{
		if (!KAComponentHelper::checkToken('post'))
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JINVALID_TOKEN')));

			return;
		}

		$model = $this->getModel('mediamanageritem');
		$result = $model->subtitleSetDefault($isDefault);

		if (!$result)
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JERROR_AN_ERROR_HAS_OCCURRED')));

			return;
		}

		echo json_encode(array('success' => true, 'message' => ''));
	}

	/**
	 * Method to save edit data from edit form for video/subtitles/chapters.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function saveFileInfo()
	{
		if (!KAComponentHelper::checkToken('post'))
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JINVALID_TOKEN')));

			return;
		}

		$user = JFactory::getUser();

		// Check if the user is authorized to do this.
		if (!$user->authorise('core.create', 'com_kinoarhiv') && !$user->authorise('core.edit', 'com_kinoarhiv'))
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JERROR_ALERTNOAUTHOR')));

			return;
		}

		$app = JFactory::getApplication();
		$model = $this->getModel('mediamanageritem');
		$type = $app->input->get('list', '', 'word');
		$data = $this->input->post->get('form', array(), 'array');
		$form = $model->getForm($data, false);

		if (!$form)
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JGLOBAL_VALIDATION_FORM_FAILED')));

			return;
		}

		$validData = $model->validate($form, $data, 'trailer_finfo_' . $type);

		if ($validData === false)
		{
			$errors = KAComponentHelperBackend::renderErrors($model->getErrors(), 'json');

			echo json_encode(array('success' => false, 'message' => $errors));

			return;
		}

		$result = $model->saveFileinfoData($validData['trailer_finfo_' . $type]);
		$errors = KAComponentHelperBackend::renderErrors($app->getMessageQueue(), 'json');

		if (!$result)
		{
			echo json_encode(array('success' => false, 'message' => $errors));

			return;
		}

		// Model can return valid state but contain some errors.
		if ($errors > '')
		{
			$message = JText::_('COM_KA_ITEMS_SAVE_SUCCESS') . '<br/>' . $errors;
		}
		else
		{
			$message = JText::_('COM_KA_ITEMS_SAVE_SUCCESS');
		}

		echo json_encode(array('success' => true, 'message' => $message));
	}

	/**
	 * Method to remove trailer files, e.g. video/subtitles/chapters.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function removeTrailerFiles()
	{
		if (!KAComponentHelper::checkToken('post'))
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JINVALID_TOKEN')));

			return;
		}

		$user = JFactory::getUser();

		// Check if the user is authorized to do this.
		if (!$user->authorise('core.edit', 'com_kinoarhiv') && !$user->authorise('core.edit.delete', 'com_kinoarhiv'))
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JERROR_ALERTNOAUTHOR')));

			return;
		}

		jimport('components.com_kinoarhiv.helpers.content', JPATH_ROOT);
		jimport('joomla.filesystem.file');

		$app = JFactory::getApplication();
		$model = $this->getModel('mediamanageritem');
		$id = $app->input->getInt('id', 0);
		$item = $app->input->getInt('item', 0);
		$item_id = $app->input->getInt('item_id', 0);
		$all = $app->input->getInt('all', 0);
		$type = $app->input->getWord('type', '');
		$path = KAContentHelper::getPath('movie', 'trailers', 0, $id);
		$array_key = ($type === 'video') ? 'src' : 'file';
		$errors = array();

		if ($all === 1)
		{
			$message = 'COM_KA_FILES_N_DELETED_SUCCESS';
			$files = $model->getTrailerFiles($type, $item_id);

			// Remove screenshot from database and filesystem
			if ($type == 'video')
			{
				$model->removeTrailerFiles('image', $item_id);
				JFile::delete($path . $files['trailer_finfo_' . $type]['screenshot']);
				unset($files['trailer_finfo_' . $type]['screenshot']);
			}

			if (!$model->removeTrailerFiles($type, $item_id, array_keys($files['trailer_finfo_' . $type])))
			{
				$errors[] = implode('<br />', $app->getMessageQueue());
			}

			foreach ($files['trailer_finfo_' . $type] as $key => $file)
			{
				$filepath = $path . $file[$array_key];

				if (is_file($filepath))
				{
					if (!JFile::delete($filepath))
					{
						$errors[] = 'File not removed: ' . $filepath . '<br />';
					}
				}
				else
				{
					$errors[] = JText::sprintf('COM_KA_FILE_NOT_FOUND', $filepath) . '<br />';
				}
			}
		}
		else
		{
			$message = 'COM_KA_FILES_N_DELETED_SUCCESS_1';
			$files = $model->getTrailerFiles($type, $item_id, $item);

			if (!$model->removeTrailerFiles($type, $item_id, $item))
			{
				$errors[] = implode('<br />', $app->getMessageQueue());
			}

			$filepath = $path . $files['trailer_finfo_' . $type][$array_key];

			if (is_file($filepath))
			{
				if (!JFile::delete($filepath))
				{
					$errors[] = 'File not removed: ' . $filepath . '<br />';
				}
			}
			else
			{
				$errors[] = JText::sprintf('COM_KA_FILE_NOT_FOUND', $filepath) . '<br />';
			}
		}

		if (!empty($errors))
		{
			echo json_encode(array('success' => false, 'message' => implode('', $errors)));
		}
		else
		{
			echo json_encode(array('success' => true, 'message' => JText::_($message)));
		}
	}

	/*public function saveSubtitles()
	{
		if (!KAComponentHelper::checkToken('post'))
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JINVALID_TOKEN')));

			return false;
		}

		$app = JFactory::getApplication();
		$movie_id = $app->input->get('movie_id', 0, 'int');
		$trailer_id = $app->input->get('trailer_id', 0, 'int');
		$subtitle_id = $app->input->get('subtitle_id', 0, 'int');

		$model = $this->getModel('mediamanageritem');
		$result = $model->saveSubtitles('', $trailer_id, $movie_id, $subtitle_id, true);

		echo $result;
	}*/

	/**
	 * Method to make screenshot from videofile.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function createScreenshot()
	{
		if (!KAComponentHelper::checkToken('post'))
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JINVALID_TOKEN')));

			return;
		}

		$user = JFactory::getUser();

		// Check if the user is authorized to do this.
		if (!$user->authorise('core.create', 'com_kinoarhiv') && !$user->authorise('core.edit', 'com_kinoarhiv'))
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JERROR_ALERTNOAUTHOR')));

			return;
		}

		$app = JFactory::getApplication();
		$time = $app->input->get('screenshot_time', '', 'string');

		// Validate time
		if (!preg_match('/^\d{2,}:(?:[0-5]\d):(?:[0-5]\d)(?:.\d{3,})?$/', $time) || ($time == '00:00:00' || $time == '00:00:00.000'))
		{
			echo json_encode(array('success' => false, 'message' => JText::_('COM_KA_TRAILERS_VIDEO_SCREENSHOT_CREATE_TIME_ERR')));

			return;
		}

		jimport('administrator.components.com_kinoarhiv.libraries.media', JPATH_ROOT);
		jimport('components.com_kinoarhiv.helpers.content', JPATH_ROOT);

		$media = KAMedia::getInstance();
		$model = $this->getModel('mediamanageritem');
		$id = $app->input->get('id', 0, 'int');
		$item_id = $app->input->get('item_id', null, 'int');
		$files = $model->getTrailerFiles('video', $item_id);
		$path = KAContentHelper::getPath('movie', 'trailers', null, $id);
		$old_screenshot = $path . $files['trailer_finfo_video']['screenshot'];

		unset($files['trailer_finfo_video']['screenshot']);

		if (!empty($old_screenshot) && is_file($old_screenshot))
		{
			@unlink($old_screenshot);
		}

		if (count($files['trailer_finfo_video']) < 1)
		{
			echo json_encode(array('success' => false, 'message' => JText::_('COM_KA_TRAILERS_VIDEO_SCREENSHOT_CREATE_FILE_ERR')));

			return;
		}

		// Get the first videofile to process
		$videofile = '';

		foreach ($files['trailer_finfo_video'] as $file)
		{
			if (is_file($path . $file['src']))
			{
				$videofile = $file['src'];
				break;
			}
		}

		if (empty($videofile))
		{
			echo json_encode(array('success' => false, 'message' => JText::sprintf('COM_KA_FILE_NOT_FOUND', $videofile)));

			return;
		}

		// CReate screenshot
		$result = $media->createVideoScreenshot($path, $videofile, $time);

		if (!$result)
		{
			echo json_encode(array('success' => false, 'message' => implode('<br />', $app->getMessageQueue())));

			return;
		}

		// Save into database
		$image = $model->saveImageInDB('trailer', $item_id, $result['filename']);

		if (!$image)
		{
			echo json_encode(array('success' => false, 'message' => implode('<br />', $app->getMessageQueue())));

			return;
		}

		echo json_encode(array('success' => true, 'message' => JText::_('COM_KA_TRAILERS_VIDEO_SCREENSHOT_CREATED')));
	}

	/**
	 * Download image from remote server and store it in filesystem.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function uploadRemote()
	{
		if (!KAComponentHelper::checkToken('post'))
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JINVALID_TOKEN')));

			return;
		}

		$user = JFactory::getUser();

		// Check if the user is authorized to do this.
		if (!$user->authorise('core.create', 'com_kinoarhiv') && !$user->authorise('core.edit', 'com_kinoarhiv'))
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JERROR_ALERTNOAUTHOR')));

			return;
		}

		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$model = $this->getModel('mediamanageritem');
		$errors = array();
		$urls = $app->input->post->get('urls', '', 'string');
		$urls_arr = explode("\n", $urls);
		$section = $app->input->get('section', '', 'word');
		$type = $app->input->get('type', '', 'word');
		$tab = $app->input->get('tab', null, 'int');
		$id = $app->input->get('id', null, 'int');
		$frontpage = $app->input->get('frontpage', 0, 'int');
		$max_files = $app->input->get('max_files', 0, 'int');

		if (count($urls_arr) > 0 && !empty($id))
		{
			jimport('joomla.filesystem.file');
			jimport('administrator.components.com_kinoarhiv.libraries.image', JPATH_ROOT);
			jimport('components.com_kinoarhiv.helpers.content', JPATH_ROOT);

			$image = new KAImage;

			foreach ($urls_arr as $index => $url)
			{
				// Limit number of files
				if ($max_files != 0)
				{
					if ($max_files == $index)
					{
						break;
					}
				}

				$output = KAComponentHelper::getRemoteData($url);

				if ($output->code == 200 || $output->code == 301 || $output->code == 304)
				{
					$dest_dir = KAContentHelper::getPath($section, $type, $tab, $id) . '/';

					// Put image in Joomla 'temp' dir
					$temp_file = JPath::clean(JFactory::getConfig()->get('tmp_path') . '/' . uniqid(rand(), true) . '.tmp');

					if (file_put_contents($temp_file, $output->body) !== false)
					{
						$image->loadFile($temp_file);
						$file_prop = $image->getImageFileProperties($temp_file);
						$file_ext = image_type_to_extension($file_prop->type);
					}
					else
					{
						echo array('success' => false, 'message' => JText::_('COM_KA_FILE_UPLOAD_ERROR'));

						return;
					}

					// If image type is image/jpeg change the file ext to more native .jpg instead of .jpeg
					if (stripos($file_ext, 'jpeg') !== false)
					{
						$file_ext = str_ireplace('jpeg', 'jpg', $file_ext);
					}

					$filename = str_replace('.', '', uniqid(rand(), true)) . $file_ext;
					$file_path = JPath::clean($dest_dir . $filename);
					$orig_width = $file_prop->width;
					$orig_height = $file_prop->height;

					if ($image->isLoaded())
					{
						try
						{
							$image->resize('100%', '100%');
						}
						catch (LogicException $e)
						{
							echo array('success' => false, 'message' => $e->getMessage());

							return;
						}

						// Save image to file
						try
						{
							$image->toFile($file_path, $file_prop->type);
						}
						catch (LogicException $e)
						{
							echo array('success' => false, 'message' => $e->getMessage());

							return;
						}

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
									echo array('success' => false, 'message' => JText::_('ERROR'));

									return;
								}

								// Add watermark
								if ($params->get('upload_gallery_watermark_image_on') == 1)
								{
									$watermark_img = $params->get('upload_gallery_watermark_image');

									if (!empty($watermark_img) && file_exists($watermark_img))
									{
										$image->addWatermark($dest_dir, $filename, $watermark_img);
									}
								}

								$image->_createThumbs($dest_dir, $filename, $width . 'x' . $height, 1, $dest_dir, false);
								$model->saveImageInDB('movie', $id, $filename, array($orig_width, $orig_height), $tab, $frontpage);
							}
							elseif ($type == 'trailers')
							{
								// Item ID == movie_id field from #__ka_trailers table
								$model->saveImageInDB('trailer', $app->input->get('item_id', 0, 'int'), $filename);
							}
						}
						elseif ($section == 'name')
						{
							if ($app->input->get('type') == 'gallery')
							{
								if ($tab == 1)
								{
									$width = (int) $params->get('size_x_wallpp');
									$height = ($width * $orig_height) / $orig_width;
								}
								elseif ($tab == 2)
								{
									$width = (int) $params->get('size_x_posters');
									$height = ($width * $orig_height) / $orig_width;
								}
								elseif ($tab == 3)
								{
									$width = (int) $params->get('size_x_photo');
									$height = ($width * $orig_height) / $orig_width;
								}
								else
								{
									echo array('success' => false, 'message' => JText::_('ERROR'));

									return;
								}

								// Add watermark
								if ($params->get('upload_gallery_watermark_image_on') == 1)
								{
									$watermark_img = $params->get('upload_gallery_watermark_image');

									if (!empty($watermark_img) && file_exists($watermark_img))
									{
										$image->addWatermark($dest_dir, $filename, $watermark_img);
									}
								}

								$image->_createThumbs($dest_dir, $filename, $width . 'x' . $height, 1, $dest_dir, false);
								$model->saveImageInDB('name', $id, $filename, array($orig_width, $orig_height), $tab, $frontpage);
							}
						}
					}
					else
					{
						$c = $index + 1;
						$errors[] = $c . '. ' . $url . '<br />';
					}

					JFile::delete($temp_file);
				}
				else
				{
					$errors[] = 'HTTP error: ' . $output->code . '<br />';
				}

				$index++;
			}

			if (count($errors) > 0)
			{
				$result = array('success' => false, 'message' => JText::_('COM_KA_FILE_UPLOAD_ERROR') . '<br />' . implode('', $errors));
			}
			else
			{
				$result = array('success' => true, 'message' => '');
			}
		}
		else
		{
			$result = array('success' => false, 'message' => JText::_('COM_KA_FILE_UPLOAD_ERROR'));
		}

		echo json_encode($result);
	}
}
