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
 * Music albums controller class
 *
 * @since  3.1
 */
class KinoarhivControllerAlbums extends JControllerLegacy
{
	/**
	 * Method to add a new record.
	 *
	 * @return  void
	 *
	 * @since   3.1
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
	 * @since   3.1
	 */
	public function edit($isNew = false)
	{
		$view = $this->getView('album', 'html');
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
	 * @since   3.1
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
	 * @since   3.1
	 */
	public function apply()
	{
		$this->save();
	}

	/**
	 * Method to save a record.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function save()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app  = JFactory::getApplication();
		$user = JFactory::getUser();
		$id   = $app->input->get('id', 0, 'int');

		// Check if the user is authorized to do this.
		if (!$user->authorise('core.create', 'com_kinoarhiv') && !$user->authorise('core.edit', 'com_kinoarhiv.album.' . $id))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$app = JFactory::getApplication();

		/** @var KinoarhivModelAlbum $model */
		$model = $this->getModel('album');
		$data  = $this->input->post->get('jform', array(), 'array');
		$form  = $model->getForm($data, false);

		if (!$form)
		{
			$app->enqueueMessage(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'), 'error');

			return;
		}

		$validData = $model->validate($form, $data);

		if ($validData === false)
		{
			KAComponentHelper::renderErrors($model->getErrors());
			$this->setRedirect('index.php?option=com_kinoarhiv&view=album&task=albums.edit&id=' . $id);

			return;
		}

		// Store data for use in KinoarhivModelAlbum::loadFormData()
		$app->setUserState('com_kinoarhiv.albums.' . $user->id . '.edit_data', $validData);
		$result = $model->save($validData);

		if (!$result)
		{
			// Errors enqueue in the model
			$this->setRedirect('index.php?option=com_kinoarhiv&view=album&task=albums.edit&id=' . $id);

			return;
		}

		$sessionData = $app->getUserState('com_kinoarhiv.albums.' . $user->id . '.edit_data');

		// Set the success message.
		$message = JText::_('COM_KA_ITEMS_SAVE_SUCCESS');

		// Delete session data taken from model
		$app->setUserState('com_kinoarhiv.albums.' . $user->id . '.edit_data', null);

		switch ($this->getTask())
		{
			case 'save2new':
				$this->setRedirect('index.php?option=com_kinoarhiv&view=album&task=albums.add', $message);
				break;

			case 'apply':
				$this->setRedirect('index.php?option=com_kinoarhiv&view=album&task=albums.edit&id=' . $sessionData['id'], $message);
				break;

			case 'save':
			default:
				$this->setRedirect('index.php?option=com_kinoarhiv&view=albums', $message);
				break;
		}
	}

	/**
	 * Method to unpublish a list of items
	 *
	 * @return  void
	 *
	 * @since   3.1
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
	 * @since   3.1
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

		/** @var KinoarhivModelAlbum $model */
		$model = $this->getModel('album');
		$result = $model->publish($isUnpublish);

		if ($result === false)
		{
			$this->setRedirect('index.php?option=com_kinoarhiv&view=albums&type=' . $this->input->get('type', 'albums', 'word'), JText::_('COM_KA_ITEMS_EDIT_ERROR'), 'error');

			return;
		}

		$message = $isUnpublish ? JText::_('COM_KA_ITEMS_EDIT_UNPUBLISHED') : JText::_('COM_KA_ITEMS_EDIT_PUBLISHED');
		$this->setRedirect('index.php?option=com_kinoarhiv&view=albums&type=' . $this->input->get('type', 'albums', 'word'), $message);
	}

	/**
	 * Method to remove an item(s).
	 *
	 * @return  void
	 *
	 * @since   3.1
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
	 * @since   3.1
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
		$app->setUserState('com_kinoarhiv.albums.' . $user->id . '.edit_data', null);

		$this->setRedirect('index.php?option=com_kinoarhiv&view=albums');
	}

	/**
	 * Method to save the submitted ordering values for records.
	 * // TODO Refactor
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function saveOrder()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		/** @var KinoarhivModelAlbums $model */
		$model = $this->getModel('albums');
		$result = $model->saveOrder();

		echo json_encode($result);
	}

	/**
	 * Method to run batch operations.
	 *
	 * @return  void
	 *
	 * @since   3.1
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
			/** @var KinoarhivModelAlbums $model */
			$model = $this->getModel('albums');
			$result = $model->batch();

			if ($result === false)
			{
				KAComponentHelper::renderErrors($model->getErrors(), 'html');
				$this->setRedirect('index.php?option=com_kinoarhiv&view=albums&type=' . $this->input->get('type', 'albums', 'word'));

				return;
			}
		}

		$this->setRedirect('index.php?option=com_kinoarhiv&view=albums&type=' . $this->input->get('type', 'albums', 'word'));
	}

	/**
	 * Display album crew edit form.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function editAlbumCrew()
	{
		$view = $this->getView('album', 'html');
		$model = $this->getModel('album');
		$view->setModel($model, true);
		$view->display('crew');
	}

	/**
	 * Method to save a record for editAlbumCrew.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function saveAlbumCrew()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app       = JFactory::getApplication();
		$user      = JFactory::getUser();
		$id        = $app->input->get('item_id', 0, 'int');
		$rowid     = $app->input->get('row_id', 0, 'int');
		$inputName = $app->input->get('input_name', '', 'string');

		// Check if the user is authorized to do this.
		if (!$user->authorise('core.create', 'com_kinoarhiv.album.' . $id) && !$user->authorise('core.edit', 'com_kinoarhiv.album.' . $id))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		/** @var KinoarhivModelAlbum $model */
		$model = $this->getModel('album');
		$data  = $this->input->post->get('jform', array(), 'array');
		$form  = $model->getForm($data, false);
		$url   = 'index.php?option=com_kinoarhiv&task=albums.editAlbumCrew&item_id=' . $id;

		if ($rowid != 0)
		{
			$url .= '&row_id=' . $rowid . '&input_name=' . $inputName;
		}

		if (!$form)
		{
			$app->enqueueMessage(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'), 'error');

			return;
		}

		$validData = $model->validate($form, $data);

		if ($validData === false)
		{
			KAComponentHelper::renderErrors($model->getErrors());
			$this->setRedirect($url);

			return;
		}

		$result = $model->saveAlbumCrew($validData);

		if (!$result)
		{
			// Errors enqueue in the model
			$this->setRedirect($url);

			return;
		}

		$sessionData = $app->getUserState('com_kinoarhiv.album.' . $user->id . '.edit_data.i_id');

		// Set the success message.
		$message = JText::_('COM_KA_ITEMS_SAVE_SUCCESS');

		// Delete session data taken from model
		$app->setUserState('com_kinoarhiv.album.' . $user->id . '.edit_data.i_id', null);

		if ($rowid == 0)
		{
			$url .= '&row_id=' . $sessionData['id'] . '&input_name=c_' . $validData['name_id'] . '_' . $validData['career_id'] . '_' . $sessionData['id'];
		}

		$this->setRedirect($url, $message);
	}

	/**
	 * Display add/edit track form.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function editTracks()
	{
		$view = $this->getView('album', 'html');
		$model = $this->getModel('album');
		$view->setModel($model, true);
		$view->display('tracks');
	}

	/**
	 * Method to encode item alias for using in filesystem paths and url.
	 *
	 * @return  void
	 * // TODO Refactor
	 * @since  3.0
	 */
	public function getFilesystemAlias()
	{
		/*jimport('components.com_kinoarhiv.helpers.content', JPATH_ROOT);

		$input = JFactory::getApplication()->input;

		echo KAContentHelper::getFilesystemAlias($input->get('form_album_alias', '', 'string'), $input->get('form_album_title', '', 'string'));*/
	}
}