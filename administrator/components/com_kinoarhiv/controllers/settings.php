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
 * Settings controller class
 *
 * @since  3.0
 */
class KinoarhivControllerSettings extends JControllerLegacy
{
	/**
	 * Proxy to KinoarhivControllerSettings::save()
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
	 * Method to save a config object.
	 *
	 * @return  mixed
	 *
	 * @since   3.0
	 */
	public function save()
	{
		$document = JFactory::getDocument();
		$doctype = $document->getType();

		// Check for request forgeries.
		if ($doctype != 'html')
		{
			if (!KAComponentHelper::checkToken())
			{
				echo json_encode(
					array(
						'success' => false,
						'message' => JText::_('JINVALID_TOKEN')
					)
				);

				return false;
			}
		}
		else
		{
			JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		}

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.admin'))
		{
			if ($doctype != 'html')
			{
				echo json_encode(
					array('success' => false, 'message' => JText::_('COM_KA_NO_ACCESS_RIGHTS'))
				);
			}
			else
			{
				JFactory::getApplication()->redirect('index.php', JText::_('COM_KA_NO_ACCESS_RIGHTS'));
			}

			return false;
		}

		$app = JFactory::getApplication();
		$model = $this->getModel('settings');
		$result = $model->save($this->input->post->get('jform', array(), 'array'));

		// Check the return value.
		if ($result === false)
		{
			// Save the data in the session.
			$app->setUserState('com_kinoarhiv.settings.global.data', $this->input->post->get('jform', array(), 'array'));

			// Save failed, go back to the screen and display a notice.
			$message = JText::sprintf('JERROR_SAVE_FAILED', $model->getError());

			if ($doctype != 'html')
			{
				echo json_encode(
					array('success' => false, 'message' => $message)
				);
			}
			else
			{
				$this->setRedirect('index.php?option=com_kinoarhiv&view=settings', $message, 'error');
			}

			return false;
		}

		// Set the success message.
		$message = JText::_('COM_CONFIG_SAVE_SUCCESS');

		if ($doctype != 'html')
		{
			echo json_encode(
				array('success' => true, 'message' => $message)
			);
		}
		else
		{
			// Set the redirect based on the task.
			switch ($this->getTask())
			{
				case 'apply':
					$this->setRedirect('index.php?option=com_kinoarhiv&view=settings', $message);
					break;

				case 'save':
				default:
					$this->setRedirect('index.php?option=com_kinoarhiv', $message);
					break;
			}
		}

		return true;
	}

	/**
	 * Method to cancel an edit.
	 *
	 * @return  boolean  True if access level checks pass, false otherwise.
	 *
	 * @since   3.0
	 */
	public function cancel()
	{
		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.admin', 'com_kinoarhiv'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		// Clean the session data.
		$app = JFactory::getApplication();
		$app->setUserState('com_kinoarhiv.settings.global.data', null);

		$this->setRedirect('index.php?option=com_kinoarhiv');
	}

	/**
	 * Method to save component configuration into json file.
	 *
	 * @return  string
	 *
	 * @since  3.0
	 */
	public function saveConfig()
	{
		$app = JFactory::getApplication();

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.admin', 'com_kinoarhiv'))
		{
			$app->redirect('index.php', JText::_('JLIB_RULES_NOT_ALLOWED'), 'error');

			return;
		}

		$document = JFactory::getDocument();
		$document->setMimeEncoding('application/octet-stream');
		$app->setHeader('Pragma', 'no-cache', true);
		$app->setHeader('Expires', '-1');
		$app->setHeader('Cache-Control', 'public, no-store, no-cache, must-revalidate, post-check=0, pre-check=0', true);
		$app->setHeader('Content-Transfer-Encoding', 'Binary');
		$app->setHeader('Content-disposition', 'attachment; filename="com_kinoarhiv-settings-' . JHtml::_('date', time(), 'Y-m-d_H-i-s') . '.json"');
		$app->sendHeaders();
		echo json_encode(JComponentHelper::getParams('com_kinoarhiv'));
	}

	/**
	 * Method to restore component configuration from json file.
	 *
	 * @return  void
	 *
	 * @since  3.0
	 */
	public function restoreConfig()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.admin', 'com_kinoarhiv'))
		{
			$app->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		jimport('joomla.filesystem.file');
		JLoader::register('KAMedia', JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'media.php');
		$media = KAMedia::getInstance();
		$model = $this->getModel('settings');

		$file = $this->input->files->get('form_upload_config', '', 'array');
		$file['name'] = JFile::makeSafe($file['name']);
		$url = 'index.php?option=com_kinoarhiv&view=settings';

		if ($media->detectMime($file['tmp_name']) != 'text/plain' || JFile::getExt($file['name']) != 'json')
		{
			$app->redirect($url, JText::_('COM_KA_SETTINGS_RESTORE_INVALID_REQUEST'), 'error');

			return;
		}

		if (isset($file['name']))
		{
			$fc = file_get_contents($file['tmp_name']);
			$data = json_decode($fc);
			$errors = json_last_error();

			if ($errors === JSON_ERROR_NONE)
			{
				if ($model->restoreConfig($data))
				{
					$app->redirect($url, JText::_('COM_KA_SETTINGS_BUTTON_RESTORECONFIG_SUCCESS'));
				}
				else
				{
					$app->redirect($url, JText::_('COM_KA_SETTINGS_BUTTON_RESTORECONFIG_ERROR'), 'error');
				}
			}
			else
			{
				$app->redirect($url, JText::_('COM_KA_SETTINGS_RESTORE_INVALID_FILE'), 'error');
			}
		}
		else
		{
			$app->redirect($url, JText::_('COM_KA_SETTINGS_RESTORE_INVALID_REQUEST'), 'error');
		}
	}

	public function validatePaths()
	{
		$paths = JFactory::getApplication()->input->get('jform', array(), 'array');
		$result = array();

		foreach ($paths as $key => $path)
		{
			$path = JPath::clean($path);
			$files_keys = array('ffmpeg_path', 'ffprobe_path', 'gnuplot_path', 'upload_gallery_watermark_image');

			// Check if checked value not a file
			if (!in_array($key, $files_keys))
			{
				if (!is_dir($path) || !is_writable($path))
				{
					$result[$key] = JText::_('COM_KA_FIELD_PATHS_DIR_NOT_FOUND');
				}
			}
			else
			{
				if (!is_file($path) || !is_executable($path))
				{
					$result[$key] = JText::_('COM_KA_FIELD_PATHS_FILE_NOT_FOUND');
				}
			}
		}

		echo json_encode($result);
	}
}
