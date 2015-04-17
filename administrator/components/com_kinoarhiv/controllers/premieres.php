<?php defined('_JEXEC') or die;
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url			http://киноархив.com/
 */

class KinoarhivControllerPremieres extends JControllerLegacy {
	public function add() {
		$this->edit(true);
	}

	public function edit($isNew=false) {
		$view = $this->getView('premieres', 'html');
		$model = $this->getModel('premiere');
		$view->setModel($model, true);

		if ($isNew === true) {
			$tpl = 'add';
		} elseif ($isNew === false) {
			$tpl = 'edit';
		}

		$view->display($tpl);

		return $this;
	}

	public function save2new() {
		$this->save();
	}

	public function apply() {
		$this->save();
	}

	public function save() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$document = JFactory::getDocument();
		$user = JFactory::getUser();

		// Check if the user is authorized to do this.
		if (!$user->authorise('core.create', 'com_kinoarhiv') && !$user->authorise('core.edit', 'com_kinoarhiv')) {
			if ($document->getType() == 'html') {
				JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
				return;
			} else {
				$document->setName('response');
				echo json_encode(array('success'=>false, 'message'=>JText::_('JERROR_ALERTNOAUTHOR')));
				return;
			}
		}

		$app = JFactory::getApplication();
		$model = $this->getModel('premiere');
		$data = $this->input->post->get('form', array(), 'array');
		$form = $model->getForm($data, false);

		if (!$form) {
			if ($document->getType() == 'html') {
				$app->enqueueMessage($model->getError(), 'error');

				return false;
			} else {
				$document->setName('response');
				echo json_encode(array('success'=>false, 'message'=>$model->getError()));
				return;
			}
		}

		// Process aliases for columns name
		if ($app->input->get('alias', 0, 'int') == 1) {
			foreach ($data as $key=>$value) {
				$key = substr($key, 2);
				$data[$key] = $value;
				unset($data['p_'.$key]);
			}
		}

		// Store data for use in KinoarhivModelPremiere::loadFormData()
		$app->setUserState('com_kinoarhiv.premieres.'.$user->id.'.edit_data', $data);
		$validData = $model->validate($form, $data);

		if ($validData === false) {
			$errors = GlobalHelper::renderErrors($model->getErrors(), $document->getType());

			if ($document->getType() == 'html') {
				$this->setRedirect('index.php?option=com_kinoarhiv&controller=premieres&task=edit&id[]='.$data['id']);

				return false;
			} else {
				$document->setName('response');
				echo json_encode(array('success'=>false, 'message'=>$errors));
				return;
			}
		}

		$result = $model->save($validData);
		$session_data = $app->getUserState('com_kinoarhiv.premieres.'.$user->id.'.data');

		if (!$result) {
			if ($document->getType() == 'html') {
				$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
				$this->setMessage($this->getError(), 'error');

				$this->setRedirect('index.php?option=com_kinoarhiv&controller=premieres&task=edit&id[]='.$data['id']);

				return false;
			} else {
				$document->setName('response');
				echo json_encode($session_data);
				return;
			}
		}

		// Set the success message.
		$message = JText::_('COM_KA_ITEMS_SAVE_SUCCESS');
		// Delete session data taken from model
		$app->setUserState('com_kinoarhiv.premieres.'.$user->id.'.data', null);
		$app->setUserState('com_kinoarhiv.premieres.'.$user->id.'.edit_data', null);

		if ($document->getType() == 'html') {
			$id = $session_data['data']['id'];

			// Set the redirect based on the task.
			switch ($this->getTask()) {
				case 'save2new':
					$this->setRedirect('index.php?option=com_kinoarhiv&controller=premieres&task=add', $message);
					break;
				case 'apply':
					$this->setRedirect('index.php?option=com_kinoarhiv&controller=premieres&task=edit&id[]='.$id, $message);
					break;

				case 'save':
				default:
					$this->setRedirect('index.php?option=com_kinoarhiv&view=premieres', $message);
					break;
			}
		} else {
			$document->setName('response');
			echo json_encode($session_data);
		}

		return true;
	}

	public function saveOrder() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$document = JFactory::getDocument();

		$model = $this->getModel('premieres');
		$result = $model->saveOrder();

		$document->setName('response');
		echo json_encode($result);
	}

	public function remove() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.delete', 'com_kinoarhiv')) {
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
			return;
		}

		$model = $this->getModel('premiere');
		$result = $model->remove();

		if ($result === false) {
			$this->setRedirect('index.php?option=com_kinoarhiv&view=premieres', JText::_('COM_KA_ITEMS_EDIT_ERROR'), 'error');
			return false;
		}

		// Clean the session data.
		$app = JFactory::getApplication();
		$app->setUserState('com_kinoarhiv.premieres.global.data', null);

		$this->setRedirect('index.php?option=com_kinoarhiv&view=premieres', JText::_('COM_KA_ITEMS_DELETED_SUCCESS'));
	}

	public function cancel() {
		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.admin', 'com_kinoarhiv')) {
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
			return;
		}

		// Clean the session data.
		$app = JFactory::getApplication();
		$app->setUserState('com_kinoarhiv.premieres.'.$user->id.'.data', null);
		$app->setUserState('com_kinoarhiv.premieres.'.$user->id.'.edit_data', null);

		$this->setRedirect('index.php?option=com_kinoarhiv&view=premieres');
	}

	public function batch() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$user = JFactory::getUser();

		if (!$user->authorise('core.create', 'com_kinoarhiv') && !$user->authorise('core.edit', 'com_kinoarhiv') && !$user->authorise('core.edit.state', 'com_kinoarhiv')) {
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
			return false;
		}

		$app = JFactory::getApplication();
		$ids = $app->input->post->get('id', array(), 'array');

		if (count($ids) != 0) {
			$model = $this->getModel('premieres');
			$result = $model->batch();

			if ($result === false) {
				GlobalHelper::renderErrors($model->getErrors(), 'html');
				$this->setRedirect('index.php?option=com_kinoarhiv&view=premieres');

				return false;
			}
		}

		$this->setRedirect('index.php?option=com_kinoarhiv&view=premieres');
	}
}
