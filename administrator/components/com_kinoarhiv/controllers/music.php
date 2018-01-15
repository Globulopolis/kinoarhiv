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
 * Music controller class
 *
 * @since  3.0
 */
class KinoarhivControllerMusic extends JControllerLegacy
{
	/**
	 * Method to add a new record.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function add()
	{
		$this->edit(true);
	}

	/**
	 * Method to edit an existing record or add a new record.
	 *
	 * @param   boolean  $isNew  Variable to check if it's new item or not.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function edit($isNew = false)
	{
		$this->addModelPath(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'music' . DIRECTORY_SEPARATOR);

		$view = $this->getView('music', 'html');
		$model = $this->getModel('album');
		$view->setModel($model, true);

		if ($isNew === true)
		{
			$tpl = 'add';
		}
		elseif ($isNew === false)
		{
			$tpl = 'edit';
		}

		$view->display($tpl);
	}

	/**
	 * Proxy to KinoarhivControllerMusic::save()
	 *
	 * @return  mixed
	 *
	 * @since   3.0
	 */
	public function save2new()
	{
		$this->save();
	}

	/**
	 * Proxy to KinoarhivControllerMusic::save()
	 *
	 * @return  mixed
	 *
	 * @since   3.0
	 */
	public function apply()
	{
		$this->save();
	}

	/**
	 * Method to save a record.
	 *
	 * @return  mixed
	 *
	 * @since   3.0
	 */
	/*public function save() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$document = JFactory::getDocument();
		$user = JFactory::getUser();

		// Check if the user is authorized to do this.
		if (!$user->authorise('core.create.award', 'com_kinoarhiv') && !$user->authorise('core.edit.award', 'com_kinoarhiv')) {
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
				unset($data['a_'.$key]);
			}
		}

		// Store data for use in KinoarhivModelAward::loadFormData()
		$app->setUserState('com_kinoarhiv.awards.'.$user->id.'.edit_data', $data);
		$validData = $model->validate($form, $data);

		if ($validData === false) {
			$errors = KAComponentHelperBackend::renderErrors($model->getErrors(), $document->getType());

			if ($document->getType() == 'html') {
				$this->setRedirect('index.php?option=com_kinoarhiv&controller=awards&task=edit&id[]='.$data['id']);

				return false;
			} else {
				$document->setName('response');
				echo json_encode(array('success'=>false, 'message'=>$errors));
				return;
			}
		}

		$result = $model->save($validData);
		$session_data = $app->getUserState('com_kinoarhiv.awards.'.$user->id.'.data');

		if (!$result) {
			if ($document->getType() == 'html') {
				$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
				$this->setMessage($this->getError(), 'error');

				$this->setRedirect('index.php?option=com_kinoarhiv&controller=awards&task=edit&id[]='.$data['id']);

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
		$app->setUserState('com_kinoarhiv.awards.'.$user->id.'.data', null);
		$app->setUserState('com_kinoarhiv.awards.'.$user->id.'.edit_data', null);

		if ($document->getType() == 'html') {
			$id = $session_data['data']['id'];

			// Set the redirect based on the task.
			switch ($this->getTask()) {
				case 'save2new':
					$this->setRedirect('index.php?option=com_kinoarhiv&controller=awards&task=add', $message);
					break;
				case 'apply':
					$this->setRedirect('index.php?option=com_kinoarhiv&controller=awards&task=edit&id[]='.$id, $message);
					break;

				case 'save':
				default:
					$this->setRedirect('index.php?option=com_kinoarhiv&view=awards', $message);
					break;
			}
		} else {
			$document->setName('response');
			echo json_encode($session_data);
		}

		return true;
	}*/

	public function saveAccessRules()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.admin', 'com_kinoarhiv') && !JFactory::getUser()->authorise('core.edit.access', 'com_kinoarhiv'))
		{
			return array('success' => false, 'message' => JText::_('JERROR_ALERTNOAUTHOR'));
		}

		$this->addModelPath(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'music' . DIRECTORY_SEPARATOR);
		$model = $this->getModel('album');
		$result = $model->saveAccessRules();

		echo json_encode($result);
	}

	/**
	 * Method to unpublish a list of items
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function unpublish()
	{
		$this->publish(true);
	}

	/**
	 * Method to publish a list of items
	 *
	 * @param   boolean  $isUnpublish  Action state
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function publish($isUnpublish = false)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.edit.state', 'com_kinoarhiv.album'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$this->addModelPath(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'music' . DIRECTORY_SEPARATOR);
		$model = $this->getModel('album');
		$result = $model->publish($isUnpublish);

		if ($result === false)
		{
			$this->setRedirect('index.php?option=com_kinoarhiv&view=music&type=' . $this->input->get('type', 'albums', 'word'), JText::_('COM_KA_ITEMS_EDIT_ERROR'), 'error');

			return;
		}

		// Clean the session data.
		$app = JFactory::getApplication();
		$app->setUserState('com_kinoarhiv.music.global.data', null);

		$message = $isUnpublish ? JText::_('COM_KA_ITEMS_EDIT_UNPUBLISHED') : JText::_('COM_KA_ITEMS_EDIT_PUBLISHED');
		$this->setRedirect('index.php?option=com_kinoarhiv&view=music&type=' . $this->input->get('type', 'albums', 'word'), $message);
	}

	/**
	 * Method to remove an item(s).
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	/*public function remove() {
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

			return;
		}

		// Clean the session data.
		$app = JFactory::getApplication();
		$app->setUserState('com_kinoarhiv.awards.global.data', null);

		$this->setRedirect('index.php?option=com_kinoarhiv&view=awards', JText::_('COM_KA_ITEMS_DELETED_SUCCESS'));
	}*/

	/**
	 * Method to cancel an edit.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function cancel()
	{
		$user = JFactory::getUser();
		$app = JFactory::getApplication();

		// Check if the user is authorized to do this.
		if (!$user->authorise('core.admin', 'com_kinoarhiv'))
		{
			$app->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		// Clean the session data.
		$app->setUserState('com_kinoarhiv.music.' . $user->id . '.data', null);
		$app->setUserState('com_kinoarhiv.music.' . $user->id . '.edit_data', null);

		$this->setRedirect('index.php?option=com_kinoarhiv&view=music&type=albums');
	}

	public function getComposers()
	{
		$this->addModelPath(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'music' . DIRECTORY_SEPARATOR);
		$model = $this->getModel('album');
		$result = $model->getComposers();

		echo json_encode($result);
	}

	public function deleteComposers()
	{
		$this->addModelPath(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'music' . DIRECTORY_SEPARATOR);
		$model = $this->getModel('album');
		$result = $model->deleteComposers();

		echo json_encode($result);
	}

	public function saveRelNames()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.edit', 'com_kinoarhiv.music'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$model = $this->getModel('relations');
		$result = $model->saveRelNames();

		echo json_encode($result);
	}

	/**
	 * Method to save the submitted ordering values for records.
	 *
	 * @return  string
	 *
	 * @since   3.0
	 */
	public function saveOrder()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$this->addModelPath(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'music' . DIRECTORY_SEPARATOR);
		$model = $this->getModel('album');
		$result = $model->saveOrder();

		echo json_encode($result);
	}

	/**
	 * Method to run batch operations.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function batch()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$user = JFactory::getUser();

		if (!$user->authorise('core.create', 'com_kinoarhiv.album')
			&& !$user->authorise('core.edit', 'com_kinoarhiv.album')
			&& !$user->authorise('core.edit.state', 'com_kinoarhiv.album'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$app = JFactory::getApplication();
		$ids = $app->input->post->get('id', array(), 'array');

		if (count($ids) != 0)
		{
			$model = $this->getModel('music');
			$result = $model->batch();

			if ($result === false)
			{
				KAComponentHelperBackend::renderErrors($model->getErrors(), 'html');
				$this->setRedirect('index.php?option=com_kinoarhiv&view=music&type=' . $this->input->get('type', 'albums', 'word'));

				return;
			}
		}

		$this->setRedirect('index.php?option=com_kinoarhiv&view=music&type=' . $this->input->get('type', 'albums', 'word'));
	}

	/**
	 * Method to encode item alias for using in filesystem paths and url.
	 *
	 * @return  void
	 *
	 * @since  3.0
	 */
	public function getFilesystemAlias()
	{
		jimport('components.com_kinoarhiv.helpers.content', JPATH_ROOT);

		$input = JFactory::getApplication()->input;

		echo KAContentHelper::getFilesystemAlias($input->get('form_album_alias', '', 'string'), $input->get('form_album_title', '', 'string'));
	}
}
