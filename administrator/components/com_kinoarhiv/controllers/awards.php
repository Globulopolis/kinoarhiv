<?php defined('_JEXEC') or die;

class KinoarhivControllerAwards extends JControllerLegacy {
	public function add() {
		$this->edit(true);
	}

	public function edit($isNew=false) {
		$view = $this->getView('awards', 'html');
		$model = $this->getModel('award');
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

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.create.award', 'com_kinoarhiv') && !JFactory::getUser()->authorise('core.edit.award', 'com_kinoarhiv')) {
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
			return;
		}

		$app = JFactory::getApplication();
		$model = $this->getModel('award');
		$form = $model->getForm();
		$data = $this->input->post->get('form', array(), 'array');
		$id = $this->input->post->get('id', 0, 'int');
		$return = $model->save($data);

		// Check the return value.
		if ($return === false) {
			// Save the data in the session.
			$app->setUserState('com_kinoarhiv.awards.global.data', $data);

			// Save failed, go back to the screen and display a notice.
			$message = JText::sprintf('JERROR_SAVE_FAILED', $model->getError());
			$this->setRedirect('index.php?option=com_kinoarhiv&view=awards', $message, 'error');
			return false;
		}

		// Set the success message.
		$message = JText::_('COM_KA_ITEMS_SAVE_SUCCESS');

		// Set the redirect based on the task.
		switch ($this->getTask()) {
			case 'save2new':
				$this->setRedirect('index.php?option=com_kinoarhiv&controller=awards&task=add', $message);
				break;
			case 'apply':
				$this->setRedirect('index.php?option=com_kinoarhiv&controller=awards&task=edit&id[]='.(int)$id, $message);
				break;

			case 'save':
			default:
				$this->setRedirect('index.php?option=com_kinoarhiv&view=awards', $message);
				break;
		}

		return true;
	}

	public function unpublish() {
		$this->publish(true);
	}

	public function publish($isUnpublish=false) {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.edit.state.award', 'com_kinoarhiv')) {
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
			return;
		}

		$model = $this->getModel('award');
		$result = $model->publish($isUnpublish);

		if ($result === false) {
			$this->setRedirect('index.php?option=com_kinoarhiv&view=awards', JText::_('COM_KA_ITEMS_EDIT_ERROR'), 'error');
			return false;
		}

		// Clean the session data.
		$app = JFactory::getApplication();
		$app->setUserState('com_kinoarhiv.awards.global.data', null);

		$this->setRedirect('index.php?option=com_kinoarhiv&view=awards', $isUnpublish ? JText::_('COM_KA_ITEMS_EDIT_UNPUBLISHED') : JText::_('COM_KA_ITEMS_EDIT_PUBLISHED'));
	}

	public function remove() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.delete.award', 'com_kinoarhiv')) {
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
			return;
		}

		$model = $this->getModel('award');
		$result = $model->remove();

		if ($result === false) {
			$this->setRedirect('index.php?option=com_kinoarhiv&view=awards', JText::_('COM_KA_ITEMS_EDIT_ERROR'), 'error');
			return false;
		}

		// Clean the session data.
		$app = JFactory::getApplication();
		$app->setUserState('com_kinoarhiv.awards.global.data', null);

		$this->setRedirect('index.php?option=com_kinoarhiv&view=awards', JText::_('COM_KA_ITEMS_DELETED_SUCCESS'));
	}

	public function cancel() {
		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.admin', 'com_kinoarhiv')) {
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
			return;
		}

		// Clean the session data.
		$app = JFactory::getApplication();
		$app->setUserState('com_kinoarhiv.awards.global.data', null);

		$this->setRedirect('index.php?option=com_kinoarhiv&view=awards');
	}

	public function quickSave() {
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
		$model = $this->getModel('award');
		$result = $model->quickSave();

		$document->setName('response');
		echo json_encode($result);
	}

	public function batch() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$user = JFactory::getUser();

		if (!$user->authorise('core.create.award', 'com_kinoarhiv') && !$user->authorise('core.edit.award', 'com_kinoarhiv') && !$user->authorise('core.edit.state.award', 'com_kinoarhiv')) {
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
			return false;
		}

		$app = JFactory::getApplication();
		$ids = $app->input->post->get('id', array(), 'array');

		if (count($ids) != 0) {
			$model = $this->getModel('awards');
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

				$this->setRedirect('index.php?option=com_kinoarhiv&view=awards');

				return false;
			}

			$this->setRedirect('index.php?option=com_kinoarhiv&view=awards');
		}
	}
}
