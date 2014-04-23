<?php defined('_JEXEC') or die;

class KinoarhivControllerReviews extends JControllerLegacy {
	public function edit() {
		$view = $this->getView('reviews', 'html');
		$model = $this->getModel('review');
		$view->setModel($model, true);

		$view->display('edit');

		return $this;
	}

	public function apply() {
		$this->save();
	}

	public function save() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.create', 'com_kinoarhiv') && !JFactory::getUser()->authorise('core.edit', 'com_kinoarhiv')) {
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
			return;
		}

		$app = JFactory::getApplication();
		$model = $this->getModel('review');
		$data = $this->input->post->get('form', array(), 'array');
		$form = $model->getForm($data, false);
		$id = $this->input->post->get('id', 0, 'int');

		if (!$form) {
			$app->enqueueMessage($model->getError(), 'error');
			return false;
		}

		$validData = $model->validate($form, $data);

		if ($validData === false) {
			$app->setUserState('com_kinoarhiv.reviews.global.data', $data);
			$errors = $model->getErrors();

			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
				if ($errors[$i] instanceof Exception) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				} else {
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			$this->setRedirect('index.php?option=com_kinoarhiv&controller=reviews&task=edit&id[]='.(int)$id);

			return false;
		}
		$result = $model->save($validData);

		if (!$result) {
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect('index.php?option=com_kinoarhiv&view=reviews');

			return false;
		}

		// Set the success message.
		$app->enqueueMessage(JText::_('COM_KA_ITEMS_SAVE_SUCCESS'));

		// Set the redirect based on the task.
		switch ($this->getTask()) {
			case 'apply':
				$this->setRedirect('index.php?option=com_kinoarhiv&controller=reviews&task=edit&id[]='.(int)$id);
				break;

			case 'save':
			default:
				$this->setRedirect('index.php?option=com_kinoarhiv&view=reviews');
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
		if (!JFactory::getUser()->authorise('core.admin', 'com_kinoarhiv')) {
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
			return;
		}

		$model = $this->getModel('reviews');
		$result = $model->publish($isUnpublish);

		if ($result === false) {
			$this->setRedirect('index.php?option=com_kinoarhiv&view=reviews', JText::_('COM_KA_ITEMS_EDIT_ERROR'), 'error');
			return false;
		}

		// Clean the session data.
		$app = JFactory::getApplication();
		$app->setUserState('com_kinoarhiv.reviews.global.data', null);

		$this->setRedirect('index.php?option=com_kinoarhiv&view=reviews', $isUnpublish ? JText::_('COM_KA_ITEMS_EDIT_UNPUBLISHED') : JText::_('COM_KA_ITEMS_EDIT_PUBLISHED'));
	}

	public function remove() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.admin', 'com_kinoarhiv')) {
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
			return;
		}

		$model = $this->getModel('reviews');
		$result = $model->remove();

		if ($result === false) {
			$this->setRedirect('index.php?option=com_kinoarhiv&view=reviews', JText::_('COM_KA_ITEMS_EDIT_ERROR'), 'error');
			return false;
		}

		// Clean the session data.
		$app = JFactory::getApplication();
		$app->setUserState('com_kinoarhiv.reviews.global.data', null);

		$this->setRedirect('index.php?option=com_kinoarhiv&view=reviews', JText::_('COM_KA_ITEMS_DELETED_SUCCESS'));
	}

	public function cancel() {
		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.admin', 'com_kinoarhiv')) {
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
			return;
		}

		// Clean the session data.
		$app = JFactory::getApplication();
		$app->setUserState('com_kinoarhiv.reviews.global.data', null);

		$this->setRedirect('index.php?option=com_kinoarhiv&view=reviews');
	}

	public function batch() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$user = JFactory::getUser();

		if (!$user->authorise('core.edit', 'com_kinoarhiv') && !$user->authorise('core.edit.state', 'com_kinoarhiv')) {
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
			return false;
		}

		$app = JFactory::getApplication();
		$ids = $app->input->post->get('id', array(), 'array');

		if (count($ids) != 0) {
			$model = $this->getModel('reviews');
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

				$this->setRedirect('index.php?option=com_kinoarhiv&view=reviews');

				return false;
			}
		}

		$this->setRedirect('index.php?option=com_kinoarhiv&view=reviews');
	}
}
