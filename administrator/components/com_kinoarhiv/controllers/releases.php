<?php defined('_JEXEC') or die;
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url			http://киноархив.com/
 */

class KinoarhivControllerReleases extends JControllerLegacy {
	public function saveOrder() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$document = JFactory::getDocument();

		$model = $this->getModel('release');
		$result = $model->saveOrder();

		$document->setName('response');
		echo json_encode($result);
	}

	public function add() {
		$this->edit(true);
	}

	public function edit($isNew=false) {
		$view = $this->getView('releases', 'html');
		$model = $this->getModel('release');
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

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.create', 'com_kinoarhiv') && !JFactory::getUser()->authorise('core.edit', 'com_kinoarhiv')) {
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
			return;
		}

		$app = JFactory::getApplication();
		$model = $this->getModel('release');
		$data = $this->input->post->get('form', array(), 'array');
		$form = $model->getForm($data, false);
		$id = $app->input->get('id', array(0), 'array');

		if (!$form) {
			$app->enqueueMessage($model->getError(), 'error');
			return false;
		}

		$validData = $model->validate($form, $data);

		if ($validData === false) {
			$app->setUserState('com_kinoarhiv.releases.global.data', $data);
			$errors = $model->getErrors();

			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
				if ($errors[$i] instanceof Exception) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				} else {
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			$this->setRedirect('index.php?option=com_kinoarhiv&controller=releases&task=edit&id[]='.$id[0]);

			return false;
		}

		$result = $model->savePremiere($validData);

		if (!$result) {
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect('index.php?option=com_kinoarhiv&view=releases');

			return false;
		}

		// Set the success message.
		$app->enqueueMessage(JText::_('COM_KA_ITEMS_SAVE_SUCCESS'));

		// Set the redirect based on the task.
		switch ($this->getTask()) {
			case 'apply':
				$this->setRedirect('index.php?option=com_kinoarhiv&controller=releases&task=edit&id[]='.$id[0]);
				break;
			case 'save2new':
				$this->setRedirect('index.php?option=com_kinoarhiv&controller=releases&task=edit&id[]=0');
				break;
			case 'save':
			default:
				$this->setRedirect('index.php?option=com_kinoarhiv&view=releases');
				break;
		}

		return true;
	}

	public function remove() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.delete', 'com_kinoarhiv')) {
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
			return;
		}

		$model = $this->getModel('release');
		$result = $model->remove();

		if ($result === false) {
			$this->setRedirect('index.php?option=com_kinoarhiv&view=releases', JText::_('COM_KA_ITEMS_EDIT_ERROR'), 'error');
			return false;
		}

		// Clean the session data.
		$app = JFactory::getApplication();
		$app->setUserState('com_kinoarhiv.releases.global.data', null);

		$this->setRedirect('index.php?option=com_kinoarhiv&view=releases', JText::_('COM_KA_ITEMS_DELETED_SUCCESS'));
	}

	public function cancel() {
		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.admin', 'com_kinoarhiv')) {
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
			return;
		}

		// Clean the session data.
		$app = JFactory::getApplication();
		$app->setUserState('com_kinoarhiv.releases.global.data', null);

		$this->setRedirect('index.php?option=com_kinoarhiv&view=releases');
	}
}
