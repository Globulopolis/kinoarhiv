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
	 * @return  void
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

		$app  = JFactory::getApplication();
		$user = JFactory::getUser();
		$id   = $app->input->get('id', 0, 'int');

		// Check if the user is authorized to do this.
		if (!$user->authorise('core.create', 'com_kinoarhiv') && !$user->authorise('core.edit', 'com_kinoarhiv.movie.' . $id))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$app = JFactory::getApplication();

		/** @var KinoarhivModelMovie $model */
		$model = $this->getModel('movie');
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
			$this->setRedirect('index.php?option=com_kinoarhiv&view=movie&task=movies.edit&id=' . $id);

			return;
		}

		// Store data for use in KinoarhivModelMovie::loadFormData()
		$app->setUserState('com_kinoarhiv.movies.' . $user->id . '.edit_data', $validData);
		$result = $model->save($validData);

		if (!$result)
		{
			// Errors enqueue in the model
			$this->setRedirect('index.php?option=com_kinoarhiv&view=movie&task=movies.edit&id=' . $id);

			return;
		}

		$sessionData = $app->getUserState('com_kinoarhiv.movies.' . $user->id . '.edit_data');

		// Set the success message.
		$message = JText::_('COM_KA_ITEMS_SAVE_SUCCESS');

		// Delete session data taken from model
		$app->setUserState('com_kinoarhiv.movies.' . $user->id . '.edit_data', null);

		switch ($this->getTask())
		{
			case 'save2new':
				$this->setRedirect('index.php?option=com_kinoarhiv&view=movie&task=movies.add', $message);
				break;

			case 'apply':
				$this->setRedirect('index.php?option=com_kinoarhiv&view=movie&task=movies.edit&id=' . $sessionData['id'], $message);
				break;

			case 'save':
			default:
				$this->setRedirect('index.php?option=com_kinoarhiv&view=movies', $message);
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
		if (!JFactory::getUser()->authorise('core.edit.state', 'com_kinoarhiv.movie'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		/** @var KinoarhivModelMovie $model */
		$model = $this->getModel('movie');
		$result = $model->publish($isUnpublish);

		if ($result === false)
		{
			$this->setRedirect('index.php?option=com_kinoarhiv&view=movies', JText::_('COM_KA_ITEMS_EDIT_ERROR'), 'error');

			return;
		}

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

		$user = JFactory::getUser();
		$app = JFactory::getApplication();

		// Check if the user is authorized to do this.
		if (!$user->authorise('core.delete', 'com_kinoarhiv'))
		{
			$app->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		jimport('components.com_kinoarhiv.helpers.content', JPATH_ROOT);
		jimport('components.com_kinoarhiv.libraries.filesystem', JPATH_ROOT);
		jimport('joomla.filesystem.folder');

		$ids = $app->input->get('id', array(), 'array');

		if (!is_array($ids) || count($ids) < 1)
		{
			$this->setRedirect('index.php?option=com_kinoarhiv&view=movies', JText::_('JGLOBAL_NO_ITEM_SELECTED'), 'error');

			return;
		}

		// Make sure the item ids are integers
		$ids = Joomla\Utilities\ArrayHelper::toInteger($ids);

		$params = JComponentHelper::getParams('com_kinoarhiv');

		/** @var KinoarhivModelMovie $model */
		$model  = $this->getModel('movie');
		$fs     = KAFilesystem::getInstance();
		$paths  = KAContentHelper::getPath('movie', 'gallery', array(1, 2, 3), $ids);

		// Remove gallery images
		foreach ($paths as $folder)
		{
			if (file_exists($folder[1]))
			{
				JFolder::delete($folder[1]);
			}

			if (file_exists($folder[2]))
			{
				JFolder::delete($folder[2]);
			}

			if (file_exists($folder[3]))
			{
				JFolder::delete($folder[3]);
			}

			// Delete parent folder
			if ($fs->getFolderSize($folder['parent']) == 0)
			{
				if (file_exists($folder[3]))
				{
					JFolder::delete($folder['parent']);
				}
			}
			else
			{
				$app->enqueueMessage(JText::sprintf('COM_KA_UNABLE_TO_DELETE_FOLDER_NOT_EMPTY', $folder['parent']), 'error');
			}
		}

		// Remove rating images
		foreach ($ids as $id)
		{
			$imdb           = JPath::clean($params->get('media_rating_image_root') . '/imdb/' . $id . '_big.png');
			$kinopoisk      = JPath::clean($params->get('media_rating_image_root') . '/kinopoisk/' . $id . '_big.png');
			$rottentomatoes = JPath::clean($params->get('media_rating_image_root') . '/rottentomatoes/' . $id . '_big.png');
			$metacritic     = JPath::clean($params->get('media_rating_image_root') . '/metacritic/' . $id . '_big.png');

			if (file_exists($imdb))
			{
				JFile::delete($imdb);
			}

			if (file_exists($kinopoisk))
			{
				JFile::delete($kinopoisk);
			}

			if (file_exists($rottentomatoes))
			{
				JFile::delete($rottentomatoes);
			}

			if (file_exists($metacritic))
			{
				JFile::delete($metacritic);
			}
		}

		// Call this after removes files, not before.
		$result = $model->remove($ids);

		if (!$result)
		{
			$this->setRedirect('index.php?option=com_kinoarhiv&view=movies', JText::plural('COM_KA_ITEMS_N_DELETED_ERROR', count($ids)), 'error');

			return;
		}

		// Clean the session data.
		$app->setUserState('com_kinoarhiv.movies.' . $user->id . '.edit_data', null);

		$this->setRedirect('index.php?option=com_kinoarhiv&view=movies', JText::plural('COM_KA_ITEMS_N_DELETED_SUCCESS', count($ids)));
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
		$app->setUserState('com_kinoarhiv.movies.' . $user->id . '.edit_data', null);

		$this->setRedirect('index.php?option=com_kinoarhiv&view=movies');
	}

	/**
	 * Method to save the submitted ordering values for records.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function saveOrder()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		/** @var KinoarhivModelMovies $model */
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
			/** @var KinoarhivModelMovies $model */
			$model = $this->getModel('movies');
			$result = $model->batch();

			if ($result === false)
			{
				KAComponentHelper::renderErrors($model->getErrors(), 'html');
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

		/** @var KinoarhivModelMovie $model */
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
			KAComponentHelper::renderErrors($model->getErrors());
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

		$sessionData = $app->getUserState('com_kinoarhiv.movie.' . $user->id . '.edit_data.c_id');

		// Set the success message.
		$message = JText::_('COM_KA_ITEMS_SAVE_SUCCESS');

		// Delete session data taken from model
		$app->setUserState('com_kinoarhiv.movie.' . $user->id . '.edit_data.c_id', null);

		$row_id = '&row_id=' . $sessionData['type'];
		$input_name = '&input_name=cc_' . $sessionData['name_id'] . '_' . $sessionData['type'];

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
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function saveMovieAwards()
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

		/** @var KinoarhivModelMovie $model */
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
			KAComponentHelper::renderErrors($model->getErrors());
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

		$sessionData = $app->getUserState('com_kinoarhiv.movie.' . $user->id . '.edit_data.aw_id');

		// Set the success message.
		$message = JText::_('COM_KA_ITEMS_SAVE_SUCCESS');

		// Delete session data taken from model
		$app->setUserState('com_kinoarhiv.movie.' . $user->id . '.edit_data.aw_id', null);

		$awardId = $sessionData['id'] ? '&row_id=' . $sessionData['id'] : '&row_id=' . $validData['id'];

		$this->setRedirect($url . $awardId, $message);
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
	 * @return  void
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

		/** @var KinoarhivModelMovie $model */
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
			KAComponentHelper::renderErrors($model->getErrors());
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

		$sessionData = $app->getUserState('com_kinoarhiv.movie.' . $user->id . '.edit_data.p_id');

		// Set the success message.
		$message = JText::_('COM_KA_ITEMS_SAVE_SUCCESS');

		// Delete session data taken from model
		$app->setUserState('com_kinoarhiv.movie.' . $user->id . '.edit_data.p_id', null);

		$id = $sessionData['id'] ? '&row_id=' . $sessionData['id'] : '&row_id=' . $validData['id'];

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
	 * @return  void
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

		/** @var KinoarhivModelMovie $model */
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
			KAComponentHelper::renderErrors($model->getErrors());
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

		$sessionData = $app->getUserState('com_kinoarhiv.movie.' . $user->id . '.edit_data.r_id');

		// Set the success message.
		$message = JText::_('COM_KA_ITEMS_SAVE_SUCCESS');

		// Delete session data taken from model
		$app->setUserState('com_kinoarhiv.movie.' . $user->id . '.edit_data.r_id', null);

		$id = $sessionData['id'] ? '&row_id=' . $sessionData['id'] : '&row_id=' . $validData['id'];

		$this->setRedirect($url . $id, $message);
	}
}
