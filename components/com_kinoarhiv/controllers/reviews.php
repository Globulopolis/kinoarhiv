<?php defined('_JEXEC') or die;

class KinoarhivControllerReviews extends JControllerLegacy {
	public function save() {
		$id = $this->input->get('id', null, 'int');
		$redir_url = JRoute::_('index.php?option=com_kinoarhiv&view=movie&id='.$id, false);

		if (JSession::checkToken() === false) {
			GlobalHelper::eventLog(JText::_('JINVALID_TOKEN'));
			$this->setRedirect($redir_url);
		}

		$user = JFactory::getUser();

		if ($user->guest) {
			GlobalHelper::eventLog(JText::_('COM_KA_REVIEWS_AUTHREQUIRED_ERROR'));
			$this->setRedirect($redir_url);
		}

		$app = JFactory::getApplication();
		$model = $this->getModel('reviews');
		$data = $this->input->post->get('form', array(), 'array');
		$form = $model->getForm($data, false);

		if (!$form) {
			$app->enqueueMessage($model->getError(), 'error');
			$this->setRedirect($redir_url);

			return false;
		}

		$validData = $model->validate($form, $data);

		if ($validData === false) {
			$app->setUserState('com_kinoarhiv.movie.'.$id.'.user.'.$user->get('id'), $data);
			$errors = $model->getErrors();

			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
				if ($errors[$i] instanceof Exception) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				} else {
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			$this->setRedirect($redir_url);

			return false;
		}

		$result = $model->save($validData);

		if (!$result) {
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect($redir_url);

			return false;
		}

		// Clear stored data in session and redirect
		$app->setUserState('com_kinoarhiv.movie.'.$id.'.user.'.$user->get('id'), null);
		$this->setRedirect($redir_url);
	}

	public function delete() {
		$user = JFactory::getUser();
		$id = $this->input->get('id', null, 'int');
		$return = $this->input->get('return', null, 'word');
		$redir_url = ($return == 'movie') ? JRoute::_('index.php?option=com_kinoarhiv&view=movie&id='.$id, false) : JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=reviews', false);

		if (!$user->authorise('core.delete.reviews', 'com_kinoarhiv')) {
			$this->setRedirect($redir_url);
		}

		$model = $this->getModel('reviews');
		$result = $model->delete();

		if (!$result) {
			$this->setMessage($model->getError(), 'error');
			$this->setRedirect($redir_url);

			return false;
		} else {
			$this->setRedirect($redir_url);
		}
	}
}
