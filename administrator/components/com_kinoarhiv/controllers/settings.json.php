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
	 * @return  void
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
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function save()
	{
		if (!KAComponentHelper::checkToken('post'))
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JINVALID_TOKEN')));

			return;
		}

		$user = JFactory::getUser();

		// Check if the user is authorized to do this.
		if (!$user->authorise('core.admin'))
		{
			echo json_encode(array('success' => false, 'message' => JText::_('COM_KA_NO_ACCESS_RIGHTS')));

			return;
		}

		$model = $this->getModel('settings');
		$data = $this->input->post->get('jform', array(), 'array');
		$result = $model->save($data);

		// Check the return value.
		if (!$result)
		{
			$errors = KAComponentHelperBackend::renderErrors(JFactory::getApplication()->getMessageQueue(), 'json');
			echo json_encode(array('success' => false, 'message' => $errors));

			return;
		}

		echo json_encode(array('success' => true, 'message' => JText::_('COM_CONFIG_SAVE_SUCCESS')));
	}

	/**
	 * Method to save component configuration into json file.
	 *
	 * @return  void
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
