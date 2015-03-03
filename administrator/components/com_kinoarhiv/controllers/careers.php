<?php defined('_JEXEC') or die;
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url			http://киноархив.com/
 */

class KinoarhivControllerCareers extends JControllerLegacy {
	public function add() {
		$this->edit(true);
	}

	public function edit($isNew=false) {
		$view = $this->getView('careers', 'html');
		$model = $this->getModel('career');
		$view->setModel($model, true);

		if ($isNew === true) {
			$tpl = 'add';
		} elseif ($isNew === false) {
			$tpl = 'edit';
		}

		$view->display($tpl);

		return $this;
	}

	public function apply() {
		$this->save();
	}

	public function save2new() {
		$this->save();
	}

	public function save() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$document = JFactory::getDocument();
		$user = JFactory::getUser();

		// Check if the user is authorized to do this.
		if (!$user->authorise('core.create.career', 'com_kinoarhiv') && !$user->authorise('core.edit.career', 'com_kinoarhiv')) {
			if ($document->getType() == 'html') {
				JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
				return;
			} else {
				$document->setName('response');
				echo json_encode(array('success'=>false, 'message'=>JText::_('JERROR_ALERTNOAUTHOR')));
				return;
			}
		}

		$model = $this->getModel('career');
		$alias = $this->input->get('quick', 0, 'int');
		$result = $model->save($alias);
		$id = $this->input->post->get('id', 0, 'int');

		if ($document->getType() == 'html') {
			if ($result['success'] === false) {
				$message = JText::sprintf('JERROR_SAVE_FAILED', $model->getError() ? $model->getError() : $result['message']);
				$this->setRedirect('index.php?option=com_kinoarhiv&view=careers', $message, 'error');
				return false;
			}

			$message = JText::_('COM_KA_ITEMS_SAVE_SUCCESS');

			switch ($this->getTask()) {
				case 'save2new':
					$this->setRedirect('index.php?option=com_kinoarhiv&controller=careers&task=add', $message);
					break;
				case 'apply':
					$this->setRedirect('index.php?option=com_kinoarhiv&controller=careers&task=edit&id[]='.(int)$id, $message);
					break;

				case 'save':
				default:
					$this->setRedirect('index.php?option=com_kinoarhiv&view=careers', $message);
					break;
			}
		} else {
			$document->setName('response');
			echo json_encode($result);
		}
	}

	public function remove() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.delete.career', 'com_kinoarhiv')) {
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
			return;
		}

		$model = $this->getModel('career');
		$result = $model->remove();

		if ($result === false) {
			$this->setRedirect('index.php?option=com_kinoarhiv&view=careers', JText::_('COM_KA_ITEMS_EDIT_ERROR'), 'error');
			return false;
		}

		// Clean the session data.
		$app = JFactory::getApplication();
		$app->setUserState('com_kinoarhiv.careers.global.data', null);

		$this->setRedirect('index.php?option=com_kinoarhiv&view=careers', JText::_('COM_KA_ITEMS_DELETED_SUCCESS'));
	}

	public function cancel() {
		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.admin', 'com_kinoarhiv')) {
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
			return;
		}

		// Clean the session data.
		$app = JFactory::getApplication();
		$app->setUserState('com_kinoarhiv.careers.global.data', null);

		$this->setRedirect('index.php?option=com_kinoarhiv&view=careers');
	}

	public function saveOrder() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$document = JFactory::getDocument();

		$model = $this->getModel('careers');
		$result = $model->saveOrder();

		$document->setName('response');
		echo json_encode($result);
	}

	public function batch() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$user = JFactory::getUser();

		if (!$user->authorise('core.create.career', 'com_kinoarhiv') && !$user->authorise('core.edit.career', 'com_kinoarhiv') && !$user->authorise('core.edit.state.career', 'com_kinoarhiv')) {
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
			return false;
		}

		$app = JFactory::getApplication();
		$ids = $app->input->post->get('id', array(), 'array');

		if (count($ids) != 0) {
			$model = $this->getModel('careers');
			$result = $model->batch();

			if ($result === false) {
				$errors = $model->getErrors();

				for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
					if ($errors[$i] instanceof Exception) {
						$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
					} else {
						$app->enqueueMessage($errors[$i], 'warning');
					}
				}

				$this->setRedirect('index.php?option=com_kinoarhiv&view=careers');

				return false;
			}
		}

		$this->setRedirect('index.php?option=com_kinoarhiv&view=careers');
	}
}
