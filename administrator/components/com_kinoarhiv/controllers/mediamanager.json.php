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

jimport('joomla.filesystem.file');

/**
 * Mediamanager controller class.
 *
 * @since  3.0
 */
class KinoarhivControllerMediamanager extends JControllerLegacy
{
	/**
	 * Method to get list of trailer files.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function getTrailerFiles()
	{
		$app    = JFactory::getApplication();

		/** @var KinoarhivModelMediamanagerItem $model */
		$model  = $this->getModel('mediamanagerItem');

		$id     = $app->input->get('id', 0, 'int');
		$data   = $app->input->get('data', '', 'string');
		$result = $model->getTrailerFiles($data, $id, '', '');

		if (!$result)
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JERROR_AN_ERROR_HAS_OCCURRED')));

			return;
		}

		echo json_encode($result);
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

		$app    = JFactory::getApplication();

		/** @var KinoarhivModelMediamanagerItem $model */
		$model  = $this->getModel('mediamanagerItem');

		$itemID = $app->input->get('item_id', 0, 'int');
		$items  = $app->input->get('ord', array(), 'array');
		$type   = $app->input->get('type', '', 'word');
		$result = $model->saveOrderTrailerFiles($itemID, $items, $type);

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

		/** @var KinoarhivModelMediamanagerItem $model */
		$model = $this->getModel('mediamanagerItem');
		$result = $model->subtitleSetDefault($isDefault);

		if (!$result)
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JERROR_AN_ERROR_HAS_OCCURRED')));

			return;
		}

		echo json_encode(array('success' => true, 'message' => ''));
	}

	/**
	 * Method to publish or unpublish posters(photo) on movie(person) info page(not on posters page).
	 *
	 * @return  void
	 *
	 * @since  3.1
	 */
	public function setFrontpage()
	{
		if (!KAComponentHelper::checkToken('post'))
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JINVALID_TOKEN')));

			return;
		}

		$user = JFactory::getUser();

		// Check if the user is authorized to do this.
		if (!$user->authorise('core.edit.state', 'com_kinoarhiv'))
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JERROR_ALERTNOAUTHOR')));

			return;
		}

		/** @var KinoarhivModelMediamanager $model */
		$model = $this->getModel('mediamanager');
		$result = $model->setFrontpage(1);

		if (!$result)
		{
			echo json_encode(array('success' => false, 'message' => JText::_('COM_KA_ITEMS_EDIT_ERROR')));

			return;
		}

		echo json_encode(array('success' => true, 'message' => JText::_('COM_KA_ITEMS_EDIT_SET_ONFRONTPAGE')));
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

		/** @var KinoarhivModelMediamanagerItem $model */
		$model = $this->getModel('mediamanagerItem');

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
			$errors = KAComponentHelper::renderErrors($model->getErrors(), 'json');

			echo json_encode(array('success' => false, 'message' => $errors));

			return;
		}

		$result = $model->saveFileinfoData($validData['trailer_finfo_' . $type]);
		$errors = KAComponentHelper::renderErrors($app->getMessageQueue(), 'json');

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

		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$id = $app->input->getInt('id', 0);

		// Check if the user is authorized to do this.
		if (!$user->authorise('core.delete', 'com_kinoarhiv.movie.' . $id))
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JERROR_ALERTNOAUTHOR')));

			return;
		}

		jimport('components.com_kinoarhiv.helpers.content', JPATH_ROOT);
		jimport('joomla.filesystem.file');

		/** @var KinoarhivModelMediamanagerItem $model */
		$model  = $this->getModel('mediamanagerItem');

		$item   = $app->input->getInt('item', 0);
		$itemID = $app->input->getInt('item_id', 0);
		$all    = $app->input->getInt('all', 0);
		$path   = KAContentHelper::getPath('movie', 'trailers', null, $id);
		$errors = array();

		if ($all === 1)
		{
			$message = 'COM_KA_FILES_N_DELETED_SUCCESS';
			$type    = $app->input->getString('type', '');
			$types   = preg_split('/[\s*,\s*]*,+[\s*,\s*]*/', trim($type));
			$files   = $model->getTrailerFiles($type, $itemID, '', '');

			foreach ($types as $_type)
			{
				if (!$model->removeTrailerFiles($_type, $itemID, array_keys($files[$_type])))
				{
					$errors[] = implode('<br />', $app->getMessageQueue());
				}

				if ($_type == 'screenshot' || $_type == 'chapters')
				{
					$filepath = $path . $files[$_type]['file'];

					if ($files[$_type]['is_file'] == 1)
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
				else
				{
					$arrayKey = ($_type === 'video') ? 'src' : 'file';

					foreach ($files[$_type] as $file)
					{
						$filepath = $path . $file[$arrayKey];

						if ($file['is_file'] == 1)
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
			}
		}
		else
		{
			$message = 'COM_KA_FILES_N_DELETED_SUCCESS_1';
			$type = $app->input->getWord('type', '');
			$files = $model->getTrailerFiles($type, $itemID, $item, '');
			$arrayKey = ($type === 'video') ? 'src' : 'file';

			if (!$model->removeTrailerFiles($type, $itemID, $item))
			{
				$errors[] = implode('<br />', $app->getMessageQueue());
			}

			if ($type == 'screenshot')
			{
				$filepath = $path . $files['screenshot']['file'];
			}
			else
			{
				$filepath = $path . $files[$type][$arrayKey];
			}

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

	/**
	 * Removes a poster.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function removePoster()
	{
		if (!KAComponentHelper::checkToken('post'))
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JINVALID_TOKEN')));

			return;
		}

		$user = JFactory::getUser();

		// Check if the user is authorized to do this.
		if (!$user->authorise('core.edit', 'com_kinoarhiv') && !$user->authorise('core.delete', 'com_kinoarhiv'))
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JERROR_ALERTNOAUTHOR')));

			return;
		}

		jimport('components.com_kinoarhiv.helpers.content', JPATH_ROOT);
		jimport('joomla.filesystem.file');

		$app     = JFactory::getApplication();

		/** @var KinoarhivModelMediamanagerItem $model */
		$model   = $this->getModel('mediamanagerItem');

		$section = $app->input->get('section', '', 'word');
		$type    = $app->input->get('type', '', 'word');
		$tab     = $app->input->get('tab', 0, 'int');
		$id      = $app->input->get('id', 0, 'int');
		$ids     = $app->input->get('item_id', array(), 'array');
		$path    = KAContentHelper::getPath($section, $type, $tab, $id);

		if (!is_array($ids) || count($ids) < 1)
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JGLOBAL_NO_ITEM_SELECTED')));

			return;
		}

		// Make sure the item ids are integers
		$ids = Joomla\Utilities\ArrayHelper::toInteger($ids);

		// Delete files
		if ($section == 'movie')
		{
			if ($type == 'gallery')
			{
				$galleryItems = $model->getGalleryFiles($section, $type, $ids);

				foreach ($galleryItems as $item)
				{
					JFile::delete($path . '/' . $item->filename);
					JFile::delete($path . '/thumb_' . $item->filename);
				}
			}
		}
		elseif ($section == 'name')
		{
			$galleryItems = $model->getGalleryFiles($section, $type, $ids);

			foreach ($galleryItems as $item)
			{
				JFile::delete($path . '/' . $item->filename);
				JFile::delete($path . '/thumb_' . $item->filename);
			}
		}
		elseif ($section == 'album')
		{
			$galleryItems = $model->getGalleryFiles($section, $type, $ids);

			foreach ($galleryItems as $item)
			{
				JFile::delete($path . '/' . $item->filename);
				JFile::delete($path . '/thumb_' . $item->filename);
			}
		}
		else
		{
			echo json_encode(array('success' => false, 'message' => JText::_('ERROR')));

			return;
		}

		$result = $model->remove($section, $type, $tab, $id, $ids);

		if ($result === false)
		{
			echo json_encode(array('success' => false, 'message' => JText::_('ERROR')));

			return;
		}

		echo json_encode(array('success' => true, 'message' => JText::plural('COM_KA_ITEMS_N_DELETED_SUCCESS', count($ids))));
	}

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

		$media         = KAMedia::getInstance();

		/** @var KinoarhivModelMediamanagerItem $model */
		$model         = $this->getModel('mediamanagerItem');

		$id            = $app->input->get('id', 0, 'int');
		$itemID        = $app->input->get('item_id', null, 'int');
		$files         = $model->getTrailerFiles('screenshot,video', $itemID, '', '');
		$path          = KAContentHelper::getPath('movie', 'trailers', null, $id);
		$oldScreenshot = $path . $files['screenshot']['file'];

		unset($files['screenshot']);

		if (!empty($oldScreenshot) && is_file($oldScreenshot))
		{
			@unlink($oldScreenshot);
		}

		if (count($files['video']) < 1)
		{
			echo json_encode(array('success' => false, 'message' => JText::_('COM_KA_TRAILERS_VIDEO_SCREENSHOT_CREATE_FILE_ERR')));

			return;
		}

		// Get the first videofile to process
		$videofile = '';

		foreach ($files['video'] as $file)
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

		// Create screenshot
		$result = $media->createVideoScreenshot($path, $videofile, $time);

		if (!$result)
		{
			$errors = KAComponentHelper::renderErrors($app->getMessageQueue(), 'json');
			echo json_encode(array('success' => false, 'message' => $errors));

			return;
		}

		// Save into database
		$image = $model->saveImageInDB('trailer', $itemID, $result['filename']);

		if (!$image)
		{
			$errors = KAComponentHelper::renderErrors($app->getMessageQueue(), 'json');
			echo json_encode(array('success' => false, 'message' => $errors));

			return;
		}

		echo json_encode(array('success' => true, 'message' => JText::_('COM_KA_TRAILERS_VIDEO_SCREENSHOT_CREATED'), 'stdout' => $result['stdout']));
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

		$app       = JFactory::getApplication();
		$params    = JComponentHelper::getParams('com_kinoarhiv');

		/** @var KinoarhivModelMediamanagerItem $model */
		$model     = $this->getModel('mediamanagerItem');

		$errors    = array();
		$urls      = $app->input->post->get('urls', '', 'string');
		$urlsArr   = explode("\n", $urls);
		$section   = $app->input->get('section', '', 'word');
		$type      = $app->input->get('type', '', 'word');
		$tab       = $app->input->get('tab', null, 'int');
		$id        = $app->input->get('id', null, 'int');
		$frontpage = $app->input->get('frontpage', 0, 'int');
		$maxFiles  = $app->input->get('max_files', 0, 'int');

		if (count($urlsArr) > 0 && !empty($id))
		{
			jimport('joomla.filesystem.file');
			jimport('administrator.components.com_kinoarhiv.libraries.image', JPATH_ROOT);
			jimport('components.com_kinoarhiv.helpers.content', JPATH_ROOT);

			$image = new KAImage;

			foreach ($urlsArr as $index => $url)
			{
				// Limit number of files
				if ($maxFiles != 0)
				{
					if ($maxFiles == $index)
					{
						break;
					}
				}

				$output = KAComponentHelper::getRemoteData($url);

				if ($output->code == 200 || $output->code == 301 || $output->code == 304)
				{
					$dstDir = KAContentHelper::getPath($section, $type, $tab, $id) . '/';

					// Put image in Joomla 'temp' dir
					$tempFile = JPath::clean(JFactory::getConfig()->get('tmp_path') . '/' . uniqid(rand(), true) . '.tmp');

					if (file_put_contents($tempFile, $output->body) !== false)
					{
						$image->loadFile($tempFile);
						$fileProp = $image->getImageFileProperties($tempFile);
						$fileExt = image_type_to_extension($fileProp->type);
					}
					else
					{
						echo array('success' => false, 'message' => JText::_('COM_KA_FILE_UPLOAD_ERROR'));

						return;
					}

					// If image type is image/jpeg change the file ext to more native .jpg instead of .jpeg
					if (stripos($fileExt, 'jpeg') !== false)
					{
						$fileExt = str_ireplace('jpeg', 'jpg', $fileExt);
					}

					$filename = str_replace('.', '', uniqid(rand(), true)) . $fileExt;
					$filePath = JPath::clean($dstDir . $filename);
					$origWidth = $fileProp->width;
					$origHeight = $fileProp->height;

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
							$image->toFile($filePath, $fileProp->type);
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
									echo array('success' => false, 'message' => JText::_('ERROR'));

									return;
								}

								// Add watermark
								if ($params->get('upload_gallery_watermark_image_on') == 1)
								{
									$watermarkImg = $params->get('upload_gallery_watermark_image');
									$options = array();

									if ($fileProp->type == 2)
									{
										$options['output_quality'] = (int) $params->get('upload_quality_images_jpg');
									}
									elseif ($fileProp->type == 3)
									{
										$options['output_quality'] = (int) $params->get('upload_quality_images_png');
									}

									if (!empty($watermarkImg) && file_exists($watermarkImg))
									{
										$image->addWatermark($dstDir, $filename, $watermarkImg, 'br', $options);
									}
								}

								$image->makeThumbs($dstDir, $filename, $width . 'x' . $height, 1, $dstDir, false);
								$insertid = $model->saveImageInDB('movie', $id, $filename, array($origWidth, $origHeight), $tab, $frontpage);
							}
							elseif ($type == 'trailers')
							{
								// Item ID == movie_id field from #__ka_trailers table
								$insertid = $model->saveImageInDB('trailer', $app->input->get('item_id', 0, 'int'), $filename);
							}
						}
						elseif ($section == 'name')
						{
							if ($type == 'gallery')
							{
								if ($tab == 1)
								{
									$width = (int) $params->get('size_x_wallpp');
									$height = ($width * $origHeight) / $origWidth;
								}
								elseif ($tab == 2)
								{
									$width = (int) $params->get('size_x_posters');
									$height = ($width * $origHeight) / $origWidth;
								}
								elseif ($tab == 3)
								{
									$width = (int) $params->get('size_x_photo');
									$height = ($width * $origHeight) / $origWidth;
								}
								else
								{
									echo array('success' => false, 'message' => JText::_('ERROR'));

									return;
								}

								// Add watermark
								if ($params->get('upload_gallery_watermark_image_on') == 1)
								{
									$watermarkImg = $params->get('upload_gallery_watermark_image');
									$options = array();

									if ($fileProp->type == 2)
									{
										$options['output_quality'] = (int) $params->get('upload_quality_images_jpg');
									}
									elseif ($fileProp->type == 3)
									{
										$options['output_quality'] = (int) $params->get('upload_quality_images_png');
									}

									if (!empty($watermarkImg) && file_exists($watermarkImg))
									{
										$image->addWatermark($dstDir, $filename, $watermarkImg, 'br', $options);
									}
								}

								$image->makeThumbs($dstDir, $filename, $width . 'x' . $height, 1, $dstDir, false);
								$insertid = $model->saveImageInDB('name', $id, $filename, array($origWidth, $origHeight), $tab, $frontpage);
							}
						}
						elseif ($section == 'album')
						{
							// TODO Implement code for remote upload
						}
					}
					else
					{
						$c = $index + 1;
						$errors[] = $c . '. ' . $url . '<br />';
					}

					JFile::delete($tempFile);
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
				$result = array('success' => true, 'message' => '', 'insertid' => $insertid, 'filename' => $filename);
			}
		}
		else
		{
			$result = array('success' => false, 'message' => JText::_('COM_KA_FILE_UPLOAD_ERROR'));
		}

		echo json_encode($result);
	}

	/**
	 * Method to import music album images from folder into database.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function importAlbumImages()
	{
		if (!KAComponentHelper::checkToken())
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JINVALID_TOKEN')));

			return;
		}

		$app  = JFactory::getApplication();
		$id   = $app->input->get('id', 0, 'int');
		$data = $app->input->get('import_images_path', '', 'string');

		if (empty($id))
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JERROR_AN_ERROR_HAS_OCCURRED')));

			return;
		}

		/** @var KinoarhivModelMediamanager $model */
		$model = $this->getModel('mediamanager', '', array('section' => 'album', 'type' => 'gallery', 'tab' => 0, 'id' => $id));

		/** @var KinoarhivModelMediamanagerItem $modelItem */
		$modelItem = $this->getModel('mediamanagerItem');

		$itemFiles = $model->getItems();
		$itemFiles = \Joomla\Utilities\ArrayHelper::getColumn($itemFiles, 'filename');
		$errors    = array();
		$success   = array();

		try
		{
			$path = JPath::clean($data);
		}
		catch (UnexpectedValueException $e)
		{
			echo json_encode(array('success' => false, 'message' => $e->getMessage()));

			return;
		}

		JLoader::register('KAMediaHelper', JPath::clean(JPATH_ADMINISTRATOR . '/components/com_kinoarhiv/helpers/media.php'));
		jimport('administrator.components.com_kinoarhiv.libraries.image', JPATH_ROOT);

		$files = KAMediaHelper::getAlbumCovers($path);
		$image = new KAImage;

		if (empty($files))
		{
			echo json_encode(array('success' => false, 'message' => JText::_('COM_KA_NO_FILES')));

			return;
		}

		foreach ($files['images'] as $key => $fileInfo)
		{
			$filePath = JPath::clean($fileInfo['folder'] . '/' . $fileInfo['filename']);

			// Check if filename allready in database and skip adding this file.
			if (in_array($fileInfo['filename'], $itemFiles))
			{
				$errors[] = $filePath . ' - <span class="text-error">fail. Allready in database!</span>';

				continue;
			}

			try
			{
				$image->loadFile($filePath);
				$imageProp = $image->getImageFileProperties($filePath);

				if (!$modelItem->saveImageInDB('album', $id, $fileInfo['filename'], array($imageProp->width, $imageProp->height), 1))
				{
					$errors[] = $filePath . ' - <span class="text-error">fail.</span>';
				}
				else
				{
					$success[] = $filePath . ' - <span class="text-success">done.</span>';
				}
			}
			catch (Exception $e)
			{
				$errors[] = $e->getMessage() . ' ' . $filePath;
			}
		}

		if (!empty($errors))
		{
			echo json_encode(
				array(
					'success' => false,
					'message' => JText::_('JERROR_AN_ERROR_HAS_OCCURRED') . ':<br/>' . implode('<br/>', $errors)
				)
			);

			return;
		}

		echo json_encode(array('success' => true, 'message' => implode('<br/>', $success), 'folder' => $path));
	}
}
