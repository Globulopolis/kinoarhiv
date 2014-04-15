<?php defined('_JEXEC') or die;

class KinoarhivControllerGenres extends JControllerLegacy {
	public function add() {
		$this->edit(true);
	}

	public function edit($isNew=false) {
		$view = $this->getView('genres', 'html');
		$model = $this->getModel('genre');
		$view->setModel($model, true);

		if ($isNew === true) {
			$tpl = 'add';
		} elseif ($isNew === false) {
			$tpl = 'edit';
		}

		$view->display($tpl);

		return $this;
	}

	public function save() {
		$this->apply();
	}

	public function save2new() {
		$this->apply();
	}

	public function apply() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.create.genre', 'com_kinoarhiv') && !JFactory::getUser()->authorise('core.edit.genre', 'com_kinoarhiv')) {
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
			return;
		}

		$app = JFactory::getApplication();
		$model = $this->getModel('genre');
		$form = $model->getForm();
		$data = $this->input->post->get('form', array(), 'array');
		$id = $this->input->post->get('id', 0, 'int');
		$return = $model->apply($data);

		// Check the return value.
		if ($return === false) {
			// Save the data in the session.
			$app->setUserState('com_kinoarhiv.genres.global.data', $data);

			// Save failed, go back to the screen and display a notice.
			$message = JText::sprintf('JERROR_SAVE_FAILED', $model->getError());
			$this->setRedirect('index.php?option=com_kinoarhiv&view=genres', $message, 'error');
			return false;
		}

		// Set the success message.
		$message = JText::_('COM_KA_ITEMS_SAVE_SUCCESS');

		// Set the redirect based on the task.
		switch ($this->getTask()) {
			case 'apply':
				$this->setRedirect('index.php?option=com_kinoarhiv&controller=genres&task=edit&id[]='.(int)$id, $message);
				break;

			case 'save2new':
				$this->setRedirect('index.php?option=com_kinoarhiv&controller=genres&task=edit', $message);
				break;
			case 'save':
			default:
				$this->setRedirect('index.php?option=com_kinoarhiv&view=genres', $message);
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
		if (!JFactory::getUser()->authorise('core.edit.state.genre', 'com_kinoarhiv')) {
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
			return;
		}

		$model = $this->getModel('genre');
		$result = $model->publish($isUnpublish);

		if ($result === false) {
			$this->setRedirect('index.php?option=com_kinoarhiv&view=genres', JText::_('COM_KA_ITEMS_EDIT_ERROR'), 'error');
			return false;
		}

		// Clean the session data.
		$app = JFactory::getApplication();
		$app->setUserState('com_kinoarhiv.genres.global.data', null);

		$this->setRedirect('index.php?option=com_kinoarhiv&view=genres', $isUnpublish ? JText::_('COM_KA_ITEMS_EDIT_UNPUBLISHED') : JText::_('COM_KA_ITEMS_EDIT_PUBLISHED'));
	}

	public function remove() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.delete.genre', 'com_kinoarhiv')) {
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
			return;
		}

		$model = $this->getModel('genre');
		$result = $model->remove();

		if ($result === false) {
			$this->setRedirect('index.php?option=com_kinoarhiv&view=genres', JText::_('COM_KA_ITEMS_EDIT_ERROR'), 'error');
			return false;
		}

		// Clean the session data.
		$app = JFactory::getApplication();
		$app->setUserState('com_kinoarhiv.genres.global.data', null);

		$this->setRedirect('index.php?option=com_kinoarhiv&view=genres', JText::_('COM_KA_ITEMS_DELETED_SUCCESS'));
	}

	public function cancel() {
		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.delete.genre', 'com_kinoarhiv')) {
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
			return;
		}

		// Clean the session data.
		$app = JFactory::getApplication();
		$app->setUserState('com_kinoarhiv.genres.global.data', null);

		$this->setRedirect('index.php?option=com_kinoarhiv&view=genres');
	}

	public function updateStat() {
		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.recount.genre', 'com_kinoarhiv')) {
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
			return;
		}

		$document = JFactory::getDocument();
		$model = $this->getModel('genre');
		$result = $model->updateStat();

		if ($document->getType() == 'json') {
			JSession::checkToken('get') or jexit(json_encode(array('success'=>false, 'message'=>JText::_('JINVALID_TOKEN'))));
			$document->setName('response');

			echo json_encode($result);
		} else {
			JSession::checkToken('post') or jexit(JText::_('JINVALID_TOKEN'));

			if ($result['success']) {
				$message = JText::_('COM_KA_GENRES_STATS_UPDATED');
			} else {
				$message = JText::_('COM_KA_GENRES_STATS_UPDATE_ERROR');
			}

			$this->setRedirect('index.php?option=com_kinoarhiv&view=genres', $message);
		}
	}

	public function batch() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$user = JFactory::getUser();

		if (!$user->authorise('core.create.genre', 'com_kinoarhiv') && !$user->authorise('core.edit.genre', 'com_kinoarhiv') && !$user->authorise('core.edit.state.genre', 'com_kinoarhiv')) {
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
			return false;
		}

		$app = JFactory::getApplication();
		$ids = $app->input->post->get('id', array(), 'array');

		if (count($ids) != 0) {
			$model = $this->getModel('genres');
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

				$this->setRedirect('index.php?option=com_kinoarhiv&view=genres');

				return false;
			}

			$this->setRedirect('index.php?option=com_kinoarhiv&view=genres');
		}
	}
}
