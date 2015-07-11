<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('_JEXEC') or die;


/**
 * Genres list controller class.
 *
 * @since  3.0
 */
class KinoarhivControllerGenres extends JControllerLegacy
{
	public function add()
	{
		$this->edit(true);
	}

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

		return $this;
	}

	public function save()
	{
		$this->apply();
	}

	public function save2new()
	{
		$this->apply();
	}

	public function apply()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$document = JFactory::getDocument();
		$user = JFactory::getUser();

		// Check if the user is authorized to do this.
		if (!$user->authorise('core.create.genre', 'com_kinoarhiv') && !$user->authorise('core.edit.genre', 'com_kinoarhiv'))
		{
			if ($document->getType() == 'html')
			{
				JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

				return;
			}
			else
			{
				$document->setName('response');
				echo json_encode(array('success' => false, 'message' => JText::_('JERROR_ALERTNOAUTHOR')));

				return;
			}
		}

		$app = JFactory::getApplication();
		$model = $this->getModel('genre');
		$data = $this->input->post->get('form', array(), 'array');
		$form = $model->getForm($data, false);

		if (!$form)
		{
			if ($document->getType() == 'html')
			{
				$app->enqueueMessage($model->getError(), 'error');

				return false;
			}
			else
			{
				$document->setName('response');
				echo json_encode(array('success' => false, 'message' => $model->getError()));

				return;
			}
		}

		// Store data for use in KinoarhivModelGenre::loadFormData()
		$app->setUserState('com_kinoarhiv.genres.' . $user->id . '.edit_data', $data);
		$validData = $model->validate($form, $data);

		if ($validData === false)
		{
			$errors = KAComponentHelper::renderErrors($model->getErrors(), $document->getType());

			if ($document->getType() == 'html')
			{
				$this->setRedirect('index.php?option=com_kinoarhiv&controller=genres&type=' . $app->input->get('type', 'movie', 'word') . '&task=edit&id[]=' . $data['id']);

				return false;
			}
			else
			{
				$document->setName('response');
				echo json_encode(array('success' => false, 'message' => $errors));

				return;
			}
		}

		$result = $model->save($validData);
		$session_data = $app->getUserState('com_kinoarhiv.genres.' . $user->id . '.data');

		if (!$result)
		{
			if ($document->getType() == 'html')
			{
				$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
				$this->setMessage($this->getError(), 'error');

				$this->setRedirect('index.php?option=com_kinoarhiv&controller=genres&type=' . $app->input->get('type', 'movie', 'word') . '&task=edit&id[]=' . $data['id']);

				return false;
			}
			else
			{
				$document->setName('response');
				echo json_encode($session_data);

				return;
			}
		}

		// Set the success message.
		$message = JText::_('COM_KA_ITEMS_SAVE_SUCCESS');

		// Delete session data taken from model
		$app->setUserState('com_kinoarhiv.genres.' . $user->id . '.data', null);
		$app->setUserState('com_kinoarhiv.genres.' . $user->id . '.edit_data', null);

		if ($document->getType() == 'html')
		{
			$id = $session_data['data']['id'];

			// Set the redirect based on the task.
			switch ($this->getTask())
			{
				case 'save2new':
					$this->setRedirect('index.php?option=com_kinoarhiv&controller=genres&type=' . $app->input->get('type', 'movie', 'word') . '&task=add', $message);
					break;
				case 'apply':
					$this->setRedirect('index.php?option=com_kinoarhiv&controller=genres&type=' . $app->input->get('type', 'movie', 'word') . '&task=edit&id[]=' . $id, $message);
					break;

				case 'save':
				default:
					$this->setRedirect('index.php?option=com_kinoarhiv&view=genres&type=' . $app->input->get('type', 'movie', 'word'), $message);
					break;
			}
		}
		else
		{
			$document->setName('response');
			echo json_encode($session_data);
		}

		return true;
	}

	public function unpublish()
	{
		$this->publish(true);
	}

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

			return false;
		}

		// Clean the session data.
		$app->setUserState('com_kinoarhiv.genres.global.data', null);

		$message = $isUnpublish ? JText::_('COM_KA_ITEMS_EDIT_UNPUBLISHED') : JText::_('COM_KA_ITEMS_EDIT_PUBLISHED');
		$this->setRedirect('index.php?option=com_kinoarhiv&view=genres&type=' . $app->input->get('type', 'movie', 'word'), $message);
	}

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

			return false;
		}

		// Clean the session data.
		$app->setUserState('com_kinoarhiv.genres.global.data', null);

		$this->setRedirect('index.php?option=com_kinoarhiv&view=genres&type=' . $app->input->get('type', 'movie', 'word'), JText::_('COM_KA_ITEMS_DELETED_SUCCESS'));
	}

	public function cancel()
	{
		$user = JFactory::getUser();
		$app = JFactory::getApplication();

		// Check if the user is authorized to do this.
		if (!$user->authorise('core.delete.genre', 'com_kinoarhiv'))
		{
			$app->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		// Clean the session data.
		$app->setUserState('com_kinoarhiv.genres.' . $user->id . '.data', null);
		$app->setUserState('com_kinoarhiv.genres.' . $user->id . '.edit_data', null);

		$this->setRedirect('index.php?option=com_kinoarhiv&view=genres&type=' . $app->input->get('type', 'movie', 'word'));
	}

	public function updateStat()
	{
		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.recount.genre', 'com_kinoarhiv'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$document = JFactory::getDocument();
		$app = JFactory::getApplication();

		if ($document->getType() == 'html')
		{
			JSession::checkToken('post') or jexit(JText::_('JINVALID_TOKEN'));

			$model = $this->getModel('genre');
			$result = $model->updateStat();

			if ($result['success'])
			{
				$message = JText::_('COM_KA_GENRES_STATS_UPDATED');
			}
			else
			{
				$message = JText::_('COM_KA_GENRES_STATS_UPDATE_ERROR');
			}

			$this->setRedirect('index.php?option=com_kinoarhiv&view=genres&type=' . $app->input->get('type', 'movie', 'word'), $message);
		}
		else
		{
			JSession::checkToken('get') or jexit(json_encode(array('success' => false, 'message' => JText::_('JINVALID_TOKEN'))));

			$model = $this->getModel('genre');
			$result = $model->updateStat();
			$document->setName('response');

			echo json_encode($result);
		}
	}

	public function batch()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$user = JFactory::getUser();

		if (!$user->authorise('core.create.genre', 'com_kinoarhiv')
			&& !$user->authorise('core.edit.genre', 'com_kinoarhiv')
			&& !$user->authorise('core.edit.state.genre', 'com_kinoarhiv'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return false;
		}

		$app = JFactory::getApplication();
		$ids = $app->input->post->get('id', array(), 'array');

		if (count($ids) != 0)
		{
			$model = $this->getModel('genres');
			$result = $model->batch();

			if ($result === false)
			{
				KAComponentHelper::renderErrors($model->getErrors(), 'html');
				$this->setRedirect('index.php?option=com_kinoarhiv&view=genres&type=' . $app->input->get('type', 'movie', 'word'));

				return false;
			}
		}

		$this->setRedirect('index.php?option=com_kinoarhiv&view=genres&type=' . $app->input->get('type', 'movie', 'word'));
	}
}
