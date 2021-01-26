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
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$user = JFactory::getUser();

		// Check if the user is authorized to do this.
		if (!$user->authorise('core.admin'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('COM_KA_NO_ACCESS_RIGHTS'));

			return;
		}

		/** @var KinoarhivModelSettings $model */
		$model = $this->getModel('settings');
		$data = $this->input->post->get('jform', array(), 'array');
		$result = $model->save($data);

		// Check the return value.
		if ($result === false)
		{
			// Save failed, go back to the screen and display a notice.
			$message = JText::sprintf('JERROR_SAVE_FAILED', $model->getError());

			$this->setRedirect('index.php?option=com_kinoarhiv&view=settings', $message, 'error');

			return;
		}

		// Set the success message.
		$message = JText::_('COM_CONFIG_SAVE_SUCCESS');

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

		if (!JSession::checkToken())
		{
			$app->enqueueMessage(JText::_('JINVALID_TOKEN_NOTICE'));
			$app->redirect('index.php');
		}

		$redirect = base64_decode($app->input->getBase64('return'));

		if (!JUri::isInternal($redirect))
		{
			$app->redirect(JUri::base());
		}

		$this->setRedirect($redirect);
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
		jimport('components.com_kinoarhiv.libraries.filesystem', JPATH_ROOT);

		/** @var KinoarhivModelSettings $model */
		$model = $this->getModel('settings');
		$file = $this->input->files->get('form_upload_config', '', 'array');
		$file['name'] = JFile::makeSafe($file['name']);
		$url = 'index.php?option=com_kinoarhiv&view=settings';

		if (KAFilesystem::getInstance()->detectMime($file['tmp_name']) != 'text/plain' || JFile::getExt($file['name']) != 'json')
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

				return;
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
}
