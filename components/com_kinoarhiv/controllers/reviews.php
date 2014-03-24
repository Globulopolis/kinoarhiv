<?php defined('_JEXEC') or die;

class KinoarhivControllerReviews extends JControllerLegacy {
	public function save() {
		$id = $this->input->get('id', null, 'int');

		if (JSession::checkToken() === false) {
			GlobalHelper::eventLog(JText::_('JINVALID_TOKEN'));
			$this->setRedirect(JRoute::_('index.php?option=com_kinoarhiv&view=movie&id='.$id, false));
		}

		$user = JFactory::getUser();

		if ($user->guest) {
			GlobalHelper::eventLog(JText::_('COM_KA_REVIEWS_AUTHREQUIRED_ERROR'));
			$this->setRedirect(JRoute::_('index.php?option=com_kinoarhiv&view=movie&id='.$id, false));
		}

		$app = JFactory::getApplication();
		$model = $this->getModel('reviews');
		$data = $this->input->post->get('form', array(), 'array');
		$form = $model->getForm($data, false);

		if (!$form) {
			$app->enqueueMessage($model->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_kinoarhiv&view=movie&id='.$id, false));

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

			$this->setRedirect(JRoute::_('index.php?option=com_kinoarhiv&view=movie&id='.$id, false));

			return false;
		}

		$result = $model->save($validData);

		if (!$result) {
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(JRoute::_('index.php?option=com_kinoarhiv&view=movie&id='.$id, false));

			return false;
		}

		// Clear stored data in session and redirect
		//$app->setUserState('com_kinoarhiv.movie.'.$id.'.user.'.$user->get('id'), null);
		$this->setRedirect(JRoute::_('index.php?option=com_kinoarhiv&view=movie&id='.$id, false));
	}

	/*public function delete() {
		$user = JFactory::getUser();
		$document = JFactory::getDocument();

		if ($user->guest) {
			if ($document->getType() == 'raw' || $document->getType() == 'json') {
				$document->setMimeEncoding('application/json');

				echo json_encode(array('success'=>0, 'message'=>JText::_('JERROR_ALERTNOAUTHOR')));
			} else {
				throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
			}

			return false;
		}

		$canDelete = $user->authorise('core.delete.reviews', 'com_kinoarhiv');

		if ($canDelete !== true) {
			if ($document->getType() == 'raw' || $document->getType() == 'json') {
				$document->setMimeEncoding('application/json');

				echo json_encode(array('success'=>0, 'message'=>JText::_('JERROR_ALERTNOAUTHOR')));
			} else {
				throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
			}

			return false;
		} else {
			$view = $this->input->get('view', 'movies', 'cmd');
			$model = $this->getModel('reviews');
			$result = $model->delete();
		}

		if ($document->getType() == 'raw' || $document->getType() == 'json') {
			$document->setMimeEncoding('application/json');

			echo json_encode($result);
		} else {
			$tab = $this->input->get('tab', '', 'cmd');

			$page = $this->input->get('page', '', 'cmd');
			$id = $this->input->get('id', 0, 'int'); // Item ID. Movie, person etc. Not a review ID
			$_id = ($id != 0) ? '&id='.$id : '';
			$tab = !empty($tab) ? '&tab='.$tab : '';
			$page = !empty($page) ? '&page='.$page : '';
			$return = $this->input->get('return', 'movies', 'cmd');
			echo '<pre>';
			print_r($_REQUEST);

			$url = JRoute::_('index.php?option=com_kinoarhiv&view='.$return.$tab.$page.$_id.'&Itemid='.$this->input->get('Itemid', 0, 'int'), false);

			$this->setMessage($result['message'], $result['success'] ? '' : 'error');

			$this->setRedirect($url);
		}
	}*/
}
