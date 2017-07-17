<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *  
 * @copyright   Copyright (C) 2017 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

/**
 * Genres list controller class.
 *
 * @since  3.0
 */
class KinoarhivControllerGenres extends JControllerLegacy
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
		$view = $this->getView('genres', 'html');
		$model = $this->getModel('genre');
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
	 * Proxy to KinoarhivControllerGenres::save()
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function save2new()
	{
		$this->save();
	}

	/**
	 * Proxy to KinoarhivControllerGenres::save()
	 *
	 * @return  void
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
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function save()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$user = JFactory::getUser();

		// Check if the user is authorized to do this.
		if (!$user->authorise('core.create.genre', 'com_kinoarhiv') && !$user->authorise('core.edit.genre', 'com_kinoarhiv'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$app = JFactory::getApplication();
		$type = $app->input->get('type', 'movie', 'word');
		$model = $this->getModel('genre');
		$data = $this->input->post->get('form', array(), 'array');
		$form = $model->getForm($data, false);

		if (!$form)
		{
			$app->enqueueMessage(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'), 'error');

			return;
		}

		$validData = $model->validate($form, $data);

		if ($validData === false)
		{
			KAComponentHelperBackend::renderErrors($model->getErrors());
			$this->setRedirect('index.php?option=com_kinoarhiv&type=' . $type . '&task=genres.edit&id[]=' . $data['id']);

			return;
		}

		// Store data for use in KinoarhivModelGenre::loadFormData()
		$app->setUserState('com_kinoarhiv.genres.' . $user->id . '.edit_data', $validData);
		$result = $model->save($validData);

		if (!$result)
		{
			// Errors enqueue in the model
			$this->setRedirect('index.php?option=com_kinoarhiv&type=' . $type . '&task=genres.edit&id[]=' . $validData['id']);

			return;
		}

		$session_data = $app->getUserState('com_kinoarhiv.genres.' . $user->id . '.edit_data');

		// Set the success message.
		$message = JText::_('COM_KA_ITEMS_SAVE_SUCCESS');

		// Delete session data taken from model
		$app->setUserState('com_kinoarhiv.genres.' . $user->id . '.edit_data', null);

		// Set the redirect based on the task.
		switch ($this->getTask())
		{
			case 'save2new':
				$this->setRedirect('index.php?option=com_kinoarhiv&type=' . $type . '&task=genres.add', $message);
				break;
			case 'apply':
				$this->setRedirect('index.php?option=com_kinoarhiv&type=' . $type . '&task=genres.edit&id[]=' . $session_data['id'], $message);
				break;

			case 'save':
			default:
				$this->setRedirect('index.php?option=com_kinoarhiv&view=genres&type=' . $type, $message);
				break;
		}
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
		if (!JFactory::getUser()->authorise('core.edit.state.genre', 'com_kinoarhiv'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$app = JFactory::getApplication();
		$model = $this->getModel('genre');
		$result = $model->publish($isUnpublish);

		if ($result === false)
		{
			$this->setRedirect('index.php?option=com_kinoarhiv&view=genres&type=' . $app->input->get('type', 'movie', 'word'), JText::_('COM_KA_ITEMS_EDIT_ERROR'), 'error');

			return;
		}

		$message = $isUnpublish ? JText::_('COM_KA_ITEMS_EDIT_UNPUBLISHED') : JText::_('COM_KA_ITEMS_EDIT_PUBLISHED');
		$this->setRedirect('index.php?option=com_kinoarhiv&view=genres&type=' . $app->input->get('type', 'movie', 'word'), $message);
	}

	/**
	 * Method to remove an item(s).
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function remove()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.delete.genre', 'com_kinoarhiv'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$app = JFactory::getApplication();
		$model = $this->getModel('genre');
		$result = $model->remove();

		if ($result === false)
		{
			$this->setRedirect('index.php?option=com_kinoarhiv&view=genres&type=' . $app->input->get('type', 'movie', 'word'), JText::_('COM_KA_ITEMS_EDIT_ERROR'), 'error');

			return;
		}

		$this->setRedirect('index.php?option=com_kinoarhiv&view=genres&type=' . $app->input->get('type', 'movie', 'word'), JText::_('COM_KA_ITEMS_DELETED_SUCCESS'));
	}

	/**
	 * Method to cancel an edit.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function cancel()
	{
		$app = JFactory::getApplication();

		// Clean the session data.
		$app->setUserState('com_kinoarhiv.genres.' . JFactory::getUser()->id . '.edit_data', null);

		$this->setRedirect('index.php?option=com_kinoarhiv&view=genres&type=' . $app->input->get('type', 'movie', 'word'));
	}

	/**
	 * Method to update stats for genres.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function updateStat()
	{
		JSession::checkToken('post') or jexit(JText::_('JINVALID_TOKEN'));

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.recount.genre', 'com_kinoarhiv'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$model = $this->getModel('genre');
		$result = $model->updateStats();
		$message = $result !== false ? JText::_('COM_KA_GENRES_STATS_UPDATED') : JText::_('COM_KA_GENRES_STATS_UPDATE_ERROR');

		$this->setRedirect('index.php?option=com_kinoarhiv&view=genres&type=' . JFactory::getApplication()->input->get('type', 'movie', 'word'), $message);
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

		if (!$user->authorise('core.create.genre', 'com_kinoarhiv')
			&& !$user->authorise('core.edit.genre', 'com_kinoarhiv')
			&& !$user->authorise('core.edit.state.genre', 'com_kinoarhiv'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$app = JFactory::getApplication();
		$ids = $app->input->post->get('id', array(), 'array');

		if (count($ids) != 0)
		{
			$model = $this->getModel('genres');
			$result = $model->batch();

			if ($result === false)
			{
				KAComponentHelperBackend::renderErrors($model->getErrors(), 'html');
				$this->setRedirect('index.php?option=com_kinoarhiv&view=genres&type=' . $app->input->get('type', 'movie', 'word'));

				return;
			}
		}

		$this->setRedirect('index.php?option=com_kinoarhiv&view=genres&type=' . $app->input->get('type', 'movie', 'word'));
	}
}
