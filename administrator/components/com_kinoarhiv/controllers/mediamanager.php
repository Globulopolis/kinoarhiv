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
		$app = JFactory::getApplication();

		$app->setHeader('Content-type', 'application/json');

		if (JSession::checkToken() === false)
		{
			$app->setHeader('HTTP/1.0', '500 Server error');
			$app->sendHeaders();

			KAComponentHelper::eventLog(JText::_('JINVALID_TOKEN'));
			jexit();
		}

		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		$params = JComponentHelper::getParams('com_kinoarhiv');
		$model = $this->getModel('mediamanager');
		$dest_dir = $model->getPath();
		$filename = rawurlencode($app->input->get('name', '', 'string'));
		$id = 0;
		$trailer_id = $app->input->get('item_id', 0, 'int');
		$item_id = $app->input->get('id', 0, 'int');
		$frontpage = $app->input->get('frontpage', 0, 'int');

		// Get extensions from settings
		$original_extension = JFile::getExt($filename);

		if ($app->input->get('type') == 'gallery')
		{
			$allowed_ext = explode(',', str_replace(' ', '', $params->get('upload_mime_images')));

			if (!in_array($original_extension, $allowed_ext))
			{
				$app->setHeader('HTTP/1.0', '500 Server error');
				$app->sendHeaders();

				KAComponentHelper::eventLog('Incorrected file extension');
				jexit();
			}
		}
		elseif ($app->input->get('type') == 'trailers')
		{
			if ($app->input->get('upload') == 'video')
			{
				$allowed_ext = explode(',', str_replace(' ', '', $params->get('upload_mime_video')));

				if (!in_array($original_extension, $allowed_ext))
				{
					$app->setHeader('HTTP/1.0', '500 Server error');
					$app->sendHeaders();

					KAComponentHelper::eventLog('Incorrected file extension');
					jexit();
				}
			}
			elseif ($app->input->get('upload') == 'subtitles')
			{
				$allowed_ext = explode(',', str_replace(' ', '', $params->get('upload_mime_subtitles')));

				if (!in_array($original_extension, $allowed_ext))
				{
					$app->setHeader('HTTP/1.0', '500 Server error');
					$app->sendHeaders();

					KAComponentHelper::eventLog('Incorrected file extension');
					jexit();
				}
			}
			elseif ($app->input->get('upload') == 'chapters')
			{
				$allowed_ext = explode(',', str_replace(' ', '', $params->get('upload_mime_chapters')));

				if (!in_array($original_extension, $allowed_ext))
				{
					$app->setHeader('HTTP/1.0', '500 Server error');
					$app->sendHeaders();

					KAComponentHelper::eventLog('Incorrected file extension');
					jexit();
				}
			}
			elseif ($app->input->get('upload') == 'images')
			{
				$allowed_ext = explode(',', str_replace(' ', '', $params->get('upload_mime_images')));

				if (!in_array($original_extension, $allowed_ext))
				{
					$app->setHeader('HTTP/1.0', '500 Server error');
					$app->sendHeaders();

					KAComponentHelper::eventLog('Incorrected file extension');
					jexit();
				}
			}
		}

		$app->setHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT', true);
		$app->setHeader('Last-Modified', gmdate('D, d M Y H:i:s'), true);
		$app->setHeader('Cache-Control', 'public, no-store, no-cache, must-revalidate, post-check=0, pre-check=0', true);
		$app->setHeader('Pragma', 'no-cache', true);

		// CORS
		$app->setHeader('Access-Control-Allow-Origin', '*', true);

		if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS')
		{
			$app->setHeader('Access-Control-Allow-Methods', 'POST, GET, OPTIONS');
			$app->setHeader('Access-Control-Max-Age', 10000);
			$app->setHeader('Access-Control-Allow-Headers', 'origin, x-csrftoken, content-type, accept');
		}

		$app->sendHeaders();

		$cleanup_dir = true;
		$max_file_age = 5 * 3600;
		@set_time_limit(0);

		$chunk = $app->input->get('chunk', 0, 'int');
		$chunks = $app->input->get('chunks', 0, 'int');

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

		if (!file_exists($dest_dir))
		{
			JFolder::create($dest_dir);
		}

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
					$result = $model->saveImageInDB($image, $filename, $orig_image, 'movie', $tab, $item_id, $frontpage);
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
						$result = $model->saveImageInDB($image, $rn_filename, array(), 'trailer', null, $trailer_id);
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
					$result = $model->saveImageInDB($image, $filename, $orig_image, 'name', $tab, $item_id, $frontpage);
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
		die($response);
	}

	public function gallery()
	{
		$view = $this->getView('mediamanager', 'raw');
		$model = $this->getModel('mediamanager');
		$view->setModel($model, true);

		$view->display('movie_gallery_list');

		return $this;
	}

	/**
	 * Proxy for $this->fp_on() method
	 *
	 * @return void
	 *
	 * @since  3.0
	 */
	public function fpOff()
	{
		$this->fpOn(1);
	}

	/**
	 * Method to publish or unpublish posters(photo) on movie(person) info page(not on posters page)
	 *
	 * @param   int  $action  0 - unpublish from frontpage, 1 - publish poster(photo) on frontpage
	 *
	 * @return  void
	 *
	 * @since  3.0
	 */
	public function fpOn($action = 0)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();
		$model = $this->getModel('mediamanager');

		// Unpublish item from frontpage
		$model->publishOnFrontpage((int) $action);
		$errors = $model->getErrors();

		if (count($errors) > 0)
		{
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					if ($app->input->get('format', 'html', 'word') == 'raw')
					{
						echo $errors[$i]->getMessage() . "\n";
					}
					else
					{
						$app->enqueueMessage($errors[$i]->getMessage(), 'error');
					}
				}
				else
				{
					if ($app->input->get('format', 'html', 'word') == 'raw')
					{
						echo $errors[$i] . "\n";
					}
					else
					{
						$app->enqueueMessage($errors[$i], 'error');
					}
				}
			}
		}

		if ($app->input->get('reload', 1, 'int') == 1)
		{
			$this->setRedirect(JUri::getInstance()->toString());
		}
	}

	/**
	 * Method to unpublish a list of items
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function unpublish()
	{
		$this->publish(0);
	}

	/**
	 * Method to publish a list of items
	 *
	 * @param   integer  $action  Action state
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function publish($action = 1)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();
		$model = $this->getModel('mediamanager');
		$model->publish((int) $action);
		$errors = $model->getErrors();

		if (count($errors) > 0)
		{
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					if ($app->input->get('format', 'html', 'word') == 'raw')
					{
						echo $errors[$i]->getMessage() . "\n";
					}
					else
					{
						$app->enqueueMessage($errors[$i]->getMessage(), 'error');
					}
				}
				else
				{
					if ($app->input->get('format', 'html', 'word') == 'raw')
					{
						echo $errors[$i] . "\n";
					}
					else
					{
						$app->enqueueMessage($errors[$i], 'error');
					}
				}
			}
		}

		$this->setRedirect(JUri::getInstance()->toString());
	}

	/**
	 * Method to remove an item(s).
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function remove()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();
		$model = $this->getModel('mediamanager');
		$model->remove();
		$errors = $model->getErrors();

		if (count($errors) > 0)
		{
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					if ($app->input->get('format', 'html', 'word') == 'raw')
					{
						echo $errors[$i]->getMessage() . "\n";
					}
					else
					{
						$app->enqueueMessage($errors[$i]->getMessage(), 'error');
					}
				}
				else
				{
					if ($app->input->get('format', 'html', 'word') == 'raw')
					{
						echo $errors[$i] . "\n";
					}
					else
					{
						$app->enqueueMessage($errors[$i], 'error');
					}
				}
			}
		}

		if ($app->input->get('reload', 1, 'int') == 1)
		{
			$this->setRedirect(JUri::getInstance()->toString());
		}
	}

	public function saveOrderTrailerVideofile()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$model = $this->getModel('mediamanager');
		$result = $model->saveOrderTrailerVideofile();

		echo $result;
	}

	public function saveDefaultTrailerSubtitlefile()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$model = $this->getModel('mediamanager');
		$result = $model->saveDefaultTrailerSubtitlefile();

		echo $result;
	}

	public function saveOrderTrailerSubtitlefile()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$model = $this->getModel('mediamanager');
		$result = $model->saveOrderTrailerSubtitlefile();

		echo $result;
	}

	public function removeTrailerFiles()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$model = $this->getModel('mediamanager');
		$result = $model->removeTrailerFiles();

		echo $result;
	}

	/**
	 * Proxy to KinoarhivControllerMediamanager::save()
	 *
	 * @return  mixed
	 *
	 * @since   3.0
	 */
	public function apply()
	{
		$this->save();
	}

	/**
	 * Proxy to KinoarhivControllerMediamanager::save()
	 *
	 * @return  mixed
	 *
	 * @since   3.0
	 */
	public function save2new()
	{
		$this->save();
	}

	/**
	 * Method to save a record.
	 *
	 * @return  boolean  True if successful, false otherwise.
	 *
	 * @since   3.0
	 */
	public function save()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.create.movie', 'com_kinoarhiv') && !JFactory::getUser()->authorise('core.edit.movie', 'com_kinoarhiv'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return false;
		}

		$app = JFactory::getApplication();
		$model = $this->getModel('mediamanager');
		$data = $this->input->post->get('form', array(), 'array');
		$form = $model->getForm($data, false);

		if (!$form)
		{
			$app->enqueueMessage($model->getError(), 'error');

			return false;
		}

		$validData = $model->validate($form, $data);

		if ($validData === false)
		{
			$errors = $model->getErrors();

			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
				{
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			$this->setRedirect('index.php?option=com_kinoarhiv&view=mediamanager&task=edit&section=' . $app->input->get('section', '', 'word') . '&type=' . $app->input->get('type', '', 'word') . '&id=' . $app->input->get('id', 0, 'int') . '&item_id=' . $app->input->get('item_id', 0, 'int'));

			return false;
		}

		$result = $model->apply($validData);

		if (!$result)
		{
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect('index.php?option=com_kinoarhiv&view=mediamanager&task=edit&section=' . $app->input->get('section', '', 'word') . '&type=' . $app->input->get('type', '', 'word') . '&id=' . $app->input->get('id', 0, 'int') . '&item_id=' . $app->input->get('item_id', 0, 'int'));

			return false;
		}

		$this->setMessage(JText::_('COM_KA_ITEMS_SAVE_SUCCESS'));

		switch ($this->getTask())
		{
			case 'save2new':
				$this->setRedirect('index.php?option=com_kinoarhiv&view=mediamanager&task=edit&section=' . $app->input->get('section', '', 'word') . '&type=' . $app->input->get('type', '', 'word') . '&id=' . $app->input->get('id', 0, 'int'));
				break;
			case 'apply':
				$item_id = is_int($result) ? $result : $app->input->get('item_id', 0, 'int');
				$this->setRedirect('index.php?option=com_kinoarhiv&view=mediamanager&task=edit&section=' . $app->input->get('section', '', 'word') . '&type=' . $app->input->get('type', '', 'word') . '&id=' . $app->input->get('id', 0, 'int') . '&item_id=' . $item_id);
				break;
			case 'save':
			default:
				$this->setRedirect('index.php?option=com_kinoarhiv&view=mediamanager&section=' . $app->input->get('section', '', 'word') . '&type=' . $app->input->get('type', '', 'word') . '&id=' . $app->input->get('id', 0, 'int'));
				break;
		}

		return true;
	}

	/**
	 * Method to cancel an edit.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function cancel()
	{
		$app = JFactory::getApplication();

		$this->setRedirect('index.php?option=com_kinoarhiv&view=mediamanager&section=' . $app->input->get('section', '', 'word') . '&type=' . $app->input->get('type', '', 'word') . '&id=' . $app->input->get('id', 0, 'int') . '&item_id=' . $app->input->get('item_id', 0, 'int'));
	}

	public function saveVideofileData()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();
		$trailer_id = $app->input->get('trailer_id', 0, 'int');
		$video_id = $app->input->get('video_id', 0, 'int');
		$movie_id = $app->input->get('movie_id', 0, 'int');

		$model = $this->getModel('mediamanager');
		$result = $model->saveVideofileData($trailer_id, $video_id, $movie_id);

		echo $result;
	}

	public function saveSubtitles()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();
		$movie_id = $app->input->get('movie_id', 0, 'int');
		$trailer_id = $app->input->get('trailer_id', 0, 'int');
		$subtitle_id = $app->input->get('subtitle_id', 0, 'int');

		$model = $this->getModel('mediamanager');
		$result = $model->saveSubtitles('', $trailer_id, $movie_id, $subtitle_id, true);

		echo $result;
	}

	public function create_screenshot()
	{
		$model = $this->getModel('mediamanager');
		$result = $model->create_screenshot();

		echo $result;
	}

	public function copyfrom()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();
		$model = $this->getModel('mediamanager');
		$message = '';

		$updated = $model->copyfrom();

		if ($updated)
		{
			$result = array('success' => true);
		}
		else
		{
			$errors = $app->getMessageQueue();

			foreach ($errors as $i => $e)
			{
				$message .= $e['message'] . '<br />';
			}

			$result = array('success' => false, 'message' => $message);
		}

		echo json_encode($result);
	}

	public function upload_remote()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$model = $this->getModel('mediamanager');
		$message = '';
		$urls = $app->input->post->get('urls', '', 'string');
		$urls_arr = explode("\n", $urls);
		$section = $app->input->get('section', '', 'word');
		$type = $app->input->get('type', '', 'word');
		$tab = $app->input->get('tab', null, 'int');
		$id = $app->input->get('id', null, 'int');
		$frontpage = $app->input->get('frontpage', 0, 'int');

		if (count($urls_arr) > 0 && !empty($id))
		{
			JLoader::register('KAImage', JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'image.php');
			$image = new KAImage;

			foreach ($urls_arr as $url)
			{
				$output = KAComponentHelper::getRemoteData($url);

				if ($output->code == 200 || $output->code == 301 || $output->code == 304)
				{
					$dest_dir = $model->getPath($section, $type, $tab, $id) . '/';
					$filename = basename($url);
					$file_path = JPath::clean($dest_dir . $filename);

					if (file_put_contents($file_path, $output->body) !== false)
					{
						if ($section == 'movie')
						{
							if ($type == 'gallery')
							{
								$orig_image = @getimagesize($file_path);

								if ($tab == 1)
								{
									$width  = (int) $params->get('size_x_wallpp');
									$height = ($width * $orig_image[1]) / $orig_image[0];
								}
								elseif ($tab == 2)
								{
									$width  = (int) $params->get('size_x_posters');
									$height = ($width * $orig_image[1]) / $orig_image[0];
								}
								elseif ($tab == 3)
								{
									$width  = (int) $params->get('size_x_scr');
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
								//$result = $model->saveImageInDB($image, $filename, $orig_image, 'movie', $tab, $id, $frontpage);
							}
						}
						elseif ($section == 'name')
						{
							if ($app->input->get('type') == 'gallery')
							{
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
								//$result = $model->saveImageInDB($image, $filename, $orig_image, 'name', $tab, $id, $frontpage);
							}
						}
					}
					else
					{
						$message .= JText::_('COM_KA_FILE_UPLOAD_ERROR') . ': ' . $url . "\n";
					}
				}
				else
				{
					$message .= 'HTTP error: ' . $output->code . "\n";
				}
			}

			$result = array('success' => true, 'message' => $message);
		}
		else
		{
			$result = array('success' => false, 'message' => JText::_('COM_KA_FILE_UPLOAD_ERROR'));
		}

		echo json_encode($result);
	}
}
