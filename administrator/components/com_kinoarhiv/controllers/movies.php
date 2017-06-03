<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('_JEXEC') or die;

/**
 * Movies list controller class
 *
 * @since  3.0
 */
class KinoarhivControllerMovies extends JControllerLegacy
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
		$view = $this->getView('movie', 'html');
		$model = $this->getModel('movie');
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
	 * Proxy to KinoarhivControllerMovies::save()
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
	 * Proxy to KinoarhivControllerMovies::save()
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
	public function save()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$document = JFactory::getDocument();
		$user = JFactory::getUser();

		// Check if the user is authorized to do this.
		if (!$user->authorise('core.create', 'com_kinoarhiv') && !$user->authorise('core.edit', 'com_kinoarhiv.movie'))
		{
			if ($document->getType() == 'html')
			{
				JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

				return;
			}
			else
			{
				echo json_encode(array('success' => false, 'message' => JText::_('JERROR_ALERTNOAUTHOR')));

				return;
			}
		}

		$app = JFactory::getApplication();
		$model = $this->getModel('movie');
		$data = $this->input->post->get('form', array(), 'array');
		$form = $model->getForm($data, false);

		if (!$form)
		{
			if ($document->getType() == 'html')
			{
				$app->enqueueMessage($model->getError(), 'error');

				return;
			}
			else
			{
				echo json_encode(array('success' => false, 'message' => $model->getError()));

				return;
			}
		}

		// Store data for use in KinoarhivModelMovie::loadFormData()
		$app->setUserState('com_kinoarhiv.movies.' . $user->id . '.edit_data', $data);
		$validData = $model->validate($form, $data, 'movie');

		if ($validData === false)
		{
			$errors = KAComponentHelperBackend::renderErrors($model->getErrors(), $document->getType());

			if ($document->getType() == 'html')
			{
				$this->setRedirect('index.php?option=com_kinoarhiv&controller=movies&task=edit&id[]=' . $data['id']);

				return;
			}
			else
			{
				echo json_encode(array('success' => false, 'message' => $errors));

				return;
			}
		}

		$result = $model->save($validData);
		$session_data = $app->getUserState('com_kinoarhiv.movies.' . $user->id . '.data');

		if (!$result)
		{
			if ($document->getType() == 'html')
			{
				KAComponentHelperBackend::renderErrors($model->getErrors(), 'html');

				// TODO id key should be changed to avoid a notice about undefined index
				$this->setRedirect('index.php?option=com_kinoarhiv&controller=movies&task=edit&id[]=' . $data['id']);

				return;
			}
			else
			{
				echo json_encode($session_data);

				return;
			}
		}

		// Set the success message.
		$message = JText::_('COM_KA_ITEMS_SAVE_SUCCESS');

		// Delete session data taken from model
		$app->setUserState('com_kinoarhiv.movies.' . $user->id . '.data', null);
		$app->setUserState('com_kinoarhiv.movies.' . $user->id . '.edit_data', null);

		if ($document->getType() == 'html')
		{
			$id = $session_data['data']['id'];

			// Set the redirect based on the task.
			switch ($this->getTask())
			{
				case 'save2new':
					$this->setRedirect('index.php?option=com_kinoarhiv&controller=movies&task=add', $message);
					break;
				case 'apply':
					$this->setRedirect('index.php?option=com_kinoarhiv&controller=movies&task=edit&id[]=' . $id, $message);
					break;

				case 'save':
				default:
					$this->setRedirect('index.php?option=com_kinoarhiv&view=movies', $message);
					break;
			}
		}
		else
		{
			echo json_encode($session_data);
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
		if (!JFactory::getUser()->authorise('core.edit.state', 'com_kinoarhiv.movie'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$model = $this->getModel('movie');
		$result = $model->publish($isUnpublish);

		if ($result === false)
		{
			$this->setRedirect('index.php?option=com_kinoarhiv&view=movies', JText::_('COM_KA_ITEMS_EDIT_ERROR'), 'error');

			return;
		}

		// Clean the session data.
		$app = JFactory::getApplication();
		$app->setUserState('com_kinoarhiv.movies.global.data', null);

		$message = $isUnpublish ? JText::_('COM_KA_ITEMS_EDIT_UNPUBLISHED') : JText::_('COM_KA_ITEMS_EDIT_PUBLISHED');
		$this->setRedirect('index.php?option=com_kinoarhiv&view=movies', $message);
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
		if (!JFactory::getUser()->authorise('core.delete', 'com_kinoarhiv.movie'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$model = $this->getModel('movie');
		$result = $model->remove();

		if ($result === false)
		{
			$this->setRedirect('index.php?option=com_kinoarhiv&view=movies', JText::_('COM_KA_ITEMS_EDIT_ERROR'), 'error');

			return;
		}

		// Clean the session data.
		$app = JFactory::getApplication();
		$app->setUserState('com_kinoarhiv.movies.global.data', null);

		$this->setRedirect('index.php?option=com_kinoarhiv&view=movies', JText::_('COM_KA_ITEMS_DELETED_SUCCESS'));
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
		$user = JFactory::getUser();
		$app = JFactory::getApplication();

		// Check if the user is authorized to do this.
		if (!$user->authorise('core.edit', 'com_kinoarhiv.movie'))
		{
			$app->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		// Clean the session data.
		$app->setUserState('com_kinoarhiv.movies.' . $user->id . '.data', null);
		$app->setUserState('com_kinoarhiv.movies.' . $user->id . '.edit_data', null);

		$this->setRedirect('index.php?option=com_kinoarhiv&view=movies');
	}

	/**
	 * Method to delete an item from cast and crew list.
	 *
	 * @return  string
	 *
	 * @since  3.0
	 */
	public function deleteCast()
	{
		$model = $this->getModel('movie');
		$result = $model->deleteCast();

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

		$model = $this->getModel('movies');
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

		if (!$user->authorise('core.create', 'com_kinoarhiv')
			&& !$user->authorise('core.edit', 'com_kinoarhiv.movie')
			&& !$user->authorise('core.edit.state', 'com_kinoarhiv.movie'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$app = JFactory::getApplication();
		$ids = $app->input->post->get('id', array(), 'array');

		if (count($ids) != 0)
		{
			$model = $this->getModel('movies');
			$result = $model->batch();

			if ($result === false)
			{
				KAComponentHelperBackend::renderErrors($model->getErrors(), 'html');
				$this->setRedirect('index.php?option=com_kinoarhiv&view=movies');

				return;
			}
		}

		$this->setRedirect('index.php?option=com_kinoarhiv&view=movies');
	}

	/**
	 * Display add/edit cast&crew form.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function editMovieCast()
	{
		$view = $this->getView('movie', 'html');
		$model = $this->getModel('movie');
		$view->setModel($model, true);
		$view->display('cast');
	}

	/**
	 * Method to save a person for cast and crew list.
	 *
	 * @return  mixed
	 *
	 * @since  3.1
	 */
	public function saveMovieCast()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app  = JFactory::getApplication();
		$user = JFactory::getUser();
		$id   = $app->input->get('item_id', 0, 'int');

		// Check if the user is authorized to do this.
		if (!$user->authorise('core.create', 'com_kinoarhiv.movie.' . $id) && !$user->authorise('core.edit', 'com_kinoarhiv.movie.' . $id))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$model = $this->getModel('movie');
		$data  = $this->input->post->get('jform', array(), 'array');
		$form  = $model->getForm($data, false);
		$url   = 'index.php?option=com_kinoarhiv&task=movies.editMovieCast&item_id=' . $id;

		if (!$form)
		{
			$app->enqueueMessage(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'), 'error');

			return;
		}

		$validData = $model->validate($form, $data);

		if ($validData === false)
		{
			KAComponentHelperBackend::renderErrors($model->getErrors());
			$this->setRedirect($url);

			return;
		}

		$result = $model->saveMovieCast($validData);

		if (!$result)
		{
			// Errors enqueue in the model
			//$this->setRedirect($url);

			return;
		}

		$session_data = $app->getUserState('com_kinoarhiv.movie.' . $user->id . '.edit_data.c_id');

		// Set the success message.
		$message = JText::_('COM_KA_ITEMS_SAVE_SUCCESS');

		// Delete session data taken from model
		$app->setUserState('com_kinoarhiv.movie.' . $user->id . '.edit_data.c_id', null);

		$row_id = '&row_id=' . $session_data['type'];
		$input_name = '&input_name=cc_' . $session_data['name_id'] . '_' . $session_data['type'];

		//$this->setRedirect($url . $row_id . $input_name, $message);
	}

	/**
	 * Display add/edit award form.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function editMovieAwards()
	{
		$view = $this->getView('movie', 'html');
		$model = $this->getModel('movie');
		$view->setModel($model, true);
		$view->display('awards');
	}

	/**
	 * Method to save a record for editNameAwards.
	 *
	 * @return  mixed
	 *
	 * @since   3.1
	 */
	public function saveMovieAwards()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$id = $app->input->get('item_id', 0, 'int');

		// Check if the user is authorized to do this.
		if (!$user->authorise('core.create', 'com_kinoarhiv.movie.' . $id) && !$user->authorise('core.edit', 'com_kinoarhiv.movie.' . $id))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$model = $this->getModel('movie');
		$data  = $this->input->post->get('jform', array(), 'array');
		$form  = $model->getForm($data, false);
		$url   = 'index.php?option=com_kinoarhiv&task=movies.editMovieAwards&item_id=' . $id;

		if (!$form)
		{
			$app->enqueueMessage(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'), 'error');

			return;
		}

		$validData = $model->validate($form, $data);

		if ($validData === false)
		{
			KAComponentHelperBackend::renderErrors($model->getErrors());
			$this->setRedirect($url);

			return;
		}

		$result = $model->saveMovieAwards($validData);

		if (!$result)
		{
			// Errors enqueue in the model
			$this->setRedirect($url);

			return;
		}

		$session_data = $app->getUserState('com_kinoarhiv.movie.' . $user->id . '.edit_data.aw_id');

		// Set the success message.
		$message = JText::_('COM_KA_ITEMS_SAVE_SUCCESS');

		// Delete session data taken from model
		$app->setUserState('com_kinoarhiv.movie.' . $user->id . '.edit_data.aw_id', null);

		$award_id = $session_data['id'] ? '&row_id=' . $session_data['id'] : '&row_id=' . $validData['id'];

		$this->setRedirect($url . $award_id, $message);
	}

	/**
	 * Display add/edit premiere form.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function editMoviePremieres()
	{
		$view = $this->getView('movie', 'html');
		$model = $this->getModel('movie');
		$view->setModel($model, true);
		$view->display('premieres');
	}

	/**
	 * Method to save a record for editMoviePremieres.
	 *
	 * @return  mixed
	 *
	 * @since   3.1
	 */
	public function saveMoviePremieres()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$id = $app->input->get('item_id', 0, 'int');

		// Check if the user is authorized to do this.
		if (!$user->authorise('core.create', 'com_kinoarhiv.movie.' . $id) && !$user->authorise('core.edit', 'com_kinoarhiv.movie.' . $id))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$model = $this->getModel('movie');
		$data  = $this->input->post->get('jform', array(), 'array');
		$form  = $model->getForm($data, false);
		$url   = 'index.php?option=com_kinoarhiv&task=movies.editMoviePremieres&item_id=' . $id;

		if (!$form)
		{
			$app->enqueueMessage(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'), 'error');

			return;
		}

		$validData = $model->validate($form, $data);

		if ($validData === false)
		{
			KAComponentHelperBackend::renderErrors($model->getErrors());
			$this->setRedirect($url);

			return;
		}

		$result = $model->saveMoviePremieres($validData);

		if (!$result)
		{
			// Errors enqueue in the model
			$this->setRedirect($url);

			return;
		}

		$session_data = $app->getUserState('com_kinoarhiv.movie.' . $user->id . '.edit_data.p_id');

		// Set the success message.
		$message = JText::_('COM_KA_ITEMS_SAVE_SUCCESS');

		// Delete session data taken from model
		$app->setUserState('com_kinoarhiv.movie.' . $user->id . '.edit_data.p_id', null);

		$id = $session_data['id'] ? '&row_id=' . $session_data['id'] : '&row_id=' . $validData['id'];

		$this->setRedirect($url . $id, $message);
	}

	/**
	 * Display add/edit release form.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function editMovieReleases()
	{
		$view = $this->getView('movie', 'html');
		$model = $this->getModel('movie');
		$view->setModel($model, true);
		$view->display('releases');
	}

	/**
	 * Method to save a record for editMovieReleases.
	 *
	 * @return  mixed
	 *
	 * @since   3.1
	 */
	public function saveMovieReleases()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app  = JFactory::getApplication();
		$user = JFactory::getUser();
		$id   = $app->input->get('item_id', 0, 'int');

		// Check if the user is authorized to do this.
		if (!$user->authorise('core.create', 'com_kinoarhiv.movie.' . $id) && !$user->authorise('core.edit', 'com_kinoarhiv.movie.' . $id))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$model = $this->getModel('movie');
		$data  = $this->input->post->get('jform', array(), 'array');
		$form  = $model->getForm($data, false);
		$url   = 'index.php?option=com_kinoarhiv&task=movies.editMovieReleases&item_id=' . $id;

		if (!$form)
		{
			$app->enqueueMessage(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'), 'error');

			return;
		}

		$validData = $model->validate($form, $data);

		if ($validData === false)
		{
			KAComponentHelperBackend::renderErrors($model->getErrors());
			$this->setRedirect($url);

			return;
		}

		$result = $model->saveMovieReleases($validData);

		if (!$result)
		{
			// Errors enqueue in the model
			$this->setRedirect($url);

			return;
		}

		$session_data = $app->getUserState('com_kinoarhiv.movie.' . $user->id . '.edit_data.r_id');

		// Set the success message.
		$message = JText::_('COM_KA_ITEMS_SAVE_SUCCESS');

		// Delete session data taken from model
		$app->setUserState('com_kinoarhiv.movie.' . $user->id . '.edit_data.r_id', null);

		$id = $session_data['id'] ? '&row_id=' . $session_data['id'] : '&row_id=' . $validData['id'];

		$this->setRedirect($url . $id, $message);
	}
}
