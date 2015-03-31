<?php defined('_JEXEC') or die;
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url			http://киноархив.com/
 */

class KinoarhivControllerSettings extends JControllerLegacy {
	public function apply() {
		$this->save();
	}

	public function save() {
		$document = JFactory::getDocument();
		$doctype = $document->getType();

		// Check for request forgeries.
		if ($doctype != 'html') {
			JSession::checkToken() or jexit(json_encode(
				array(
					'success'=>false,
					'message'=>JText::_('JINVALID_TOKEN')
				)
			));
		} else {
			JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		}

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.admin')) {
			if ($doctype != 'html') {
				echo json_encode(
					array('success'=>false, 'message'=>JText::_('COM_KA_NO_ACCESS_RIGHTS'))
				);
			} else {
				JFactory::getApplication()->redirect('index.php', JText::_('COM_KA_NO_ACCESS_RIGHTS'));
			}
			return;
		}

		$app = JFactory::getApplication();
		$model = $this->getModel('settings');
		$result = $model->save($this->input->post->get('jform', array(), 'array'));

		// Check the return value.
		if ($result === false) {
			// Save the data in the session.
			$app->setUserState('com_kinoarhiv.settings.global.data', $this->input->post->get('jform', array(), 'array'));

			// Save failed, go back to the screen and display a notice.
			$message = JText::sprintf('JERROR_SAVE_FAILED', $model->getError());
			if ($doctype != 'html') {
				echo json_encode(
					array('success'=>false, 'message'=>$message)
				);
			} else {
				$this->setRedirect('index.php?option=com_kinoarhiv&view=settings', $message, 'error');
			}
			return false;
		}

		// Set the success message.
		$message = JText::_('COM_CONFIG_SAVE_SUCCESS');

		if ($doctype != 'html') {
			echo json_encode(
				array('success'=>true, 'message'=>$message)
			);
		} else {
			// Set the redirect based on the task.
			switch ($this->getTask()) {
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

	public function cancel() {
		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.admin', 'com_kinoarhiv')) {
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
			return;
		}

		// Clean the session data.
		$app = JFactory::getApplication();
		$app->setUserState('com_kinoarhiv.settings.global.data', null);

		$this->setRedirect('index.php?option=com_kinoarhiv');
	}

	public function saveConfig() {
		$app = JFactory::getApplication();

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.admin', 'com_kinoarhiv')) {
			$app->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
			return;
		}

		$document = JFactory::getDocument();
		$document->setMimeEncoding('application/octet-stream');
		$app->setHeader('Pragma', '1');
		$app->setHeader('Expires', '-1');
		$app->setHeader('Cache-Control', 'public, must-revalidate, post-check=0, pre-check=0');
		$app->setHeader('Content-Transfer-Encoding', 'Binary');
		$app->setHeader('Content-disposition', 'attachment; filename="com_kinoarhiv-settings-'.JHtml::_('date', time(), 'Y-m-d_H-i-s').'.json"');
		echo json_encode(JComponentHelper::getParams('com_kinoarhiv'));
	}

	public function restoreConfig() {
		$app = JFactory::getApplication();

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.admin', 'com_kinoarhiv')) {
			$app->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
			return;
		}

		//$model = $this->getModel('settings');
		//$result = $model->save($this->input->post->get('jform', array(), 'array'));
	}
}
