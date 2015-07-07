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
 * Names list controller class
 *
 * @since  3.0
 */
class KinoarhivControllerNames extends JControllerLegacy
{
	public function add()
	{
		$this->edit(true);
	}

	public function edit($isNew = false)
	{
		$view = $this->getView('names', 'html');
		$model = $this->getModel('name');
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

	public function save2new()
	{
		$this->save();
	}

	public function apply()
	{
		$this->save();
	}

	public function save()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$document = JFactory::getDocument();
		$user = JFactory::getUser();

		// Check if the user is authorized to do this.
		if (!$user->authorise('core.create', 'com_kinoarhiv') && !$user->authorise('core.edit', 'com_kinoarhiv.name'))
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
		$model = $this->getModel('name');
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

		// Process aliases for columns name
		if ($app->input->get('quick_save', 0, 'int') == 1)
		{
			foreach ($data as $key => $value)
			{
				$key = substr($key, 2);
				$data['name'][$key] = $value;
				unset($data['n_' . $key]);
			}
		}

		// Store data for use in KinoarhivModelName::loadFormData()
		$app->setUserState('com_kinoarhiv.names.' . $user->id . '.edit_data', $data);
		$validData = $model->validate($form, $data, 'name');

		if ($validData === false)
		{
			$errors = GlobalHelper::renderErrors($model->getErrors(), $document->getType());

			if ($document->getType() == 'html')
			{
				$this->setRedirect('index.php?option=com_kinoarhiv&controller=names&task=edit&id[]=' . $data['id']);

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
		$session_data = $app->getUserState('com_kinoarhiv.names.' . $user->id . '.data');

		if (!$result)
		{
			if ($document->getType() == 'html')
			{
				GlobalHelper::renderErrors($model->getErrors(), 'html');
				$this->setRedirect('index.php?option=com_kinoarhiv&controller=names&task=edit&id[]=' . $data['id']);

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
		$app->setUserState('com_kinoarhiv.names.' . $user->id . '.data', null);
		$app->setUserState('com_kinoarhiv.names.' . $user->id . '.edit_data', null);

		if ($document->getType() == 'html')
		{
			$id = $session_data['data']['id'];

			// Set the redirect based on the task.
			switch ($this->getTask())
			{
				case 'save2new':
					$this->setRedirect('index.php?option=com_kinoarhiv&controller=names&task=add', $message);
					break;
				case 'apply':
					$this->setRedirect('index.php?option=com_kinoarhiv&controller=names&task=edit&id[]=' . $id, $message);
					break;

				case 'save':
				default:
					$this->setRedirect('index.php?option=com_kinoarhiv&view=names', $message);
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
		if (!JFactory::getUser()->authorise('core.edit.state', 'com_kinoarhiv.name'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$model = $this->getModel('name');
		$result = $model->publish($isUnpublish);

		if ($result === false)
		{
			$this->setRedirect('index.php?option=com_kinoarhiv&view=names', JText::_('COM_KA_ITEMS_EDIT_ERROR'), 'error');

			return false;
		}

		// Clean the session data.
		$app = JFactory::getApplication();
		$app->setUserState('com_kinoarhiv.names.global.data', null);

		$message = $isUnpublish ? JText::_('COM_KA_ITEMS_EDIT_UNPUBLISHED') : JText::_('COM_KA_ITEMS_EDIT_PUBLISHED');
		$this->setRedirect('index.php?option=com_kinoarhiv&view=names', $message);
	}

	public function remove()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.delete', 'com_kinoarhiv.name'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$model = $this->getModel('name');
		$result = $model->remove();

		if ($result === false)
		{
			$this->setRedirect('index.php?option=com_kinoarhiv&view=names', JText::_('COM_KA_ITEMS_EDIT_ERROR'), 'error');

			return false;
		}

		// Clean the session data.
		$app = JFactory::getApplication();
		$app->setUserState('com_kinoarhiv.names.global.data', null);

		$this->setRedirect('index.php?option=com_kinoarhiv&view=names', JText::_('COM_KA_ITEMS_DELETED_SUCCESS'));
	}

	public function cancel()
	{
		$user = JFactory::getUser();
		$app = JFactory::getApplication();

		// Check if the user is authorized to do this.
		if (!$user->authorise('core.edit', 'com_kinoarhiv.name'))
		{
			$app->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		// Clean the session data.
		$app->setUserState('com_kinoarhiv.names.' . $user->id . '.data', null);
		$app->setUserState('com_kinoarhiv.names.' . $user->id . '.edit_data', null);

		$this->setRedirect('index.php?option=com_kinoarhiv&view=names');
	}

	public function saveOrder()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$document = JFactory::getDocument();

		$model = $this->getModel('names');
		$result = $model->saveOrder();

		$document->setName('response');
		echo json_encode($result);
	}

	public function batch()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$user = JFactory::getUser();

		if (!$user->authorise('core.create', 'com_kinoarhiv')
			&& !$user->authorise('core.edit', 'com_kinoarhiv.name')
			&& !$user->authorise('core.edit.state', 'com_kinoarhiv.name'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return false;
		}

		$app = JFactory::getApplication();
		$ids = $app->input->post->get('id', array(), 'array');

		if (count($ids) != 0)
		{
			$model = $this->getModel('names');
			$result = $model->batch();

			if ($result === false)
			{
				GlobalHelper::renderErrors($model->getErrors(), 'html');
				$this->setRedirect('index.php?option=com_kinoarhiv&view=names');

				return false;
			}
		}

		$this->setRedirect('index.php?option=com_kinoarhiv&view=names');
	}

	public function saveNameAccessRules()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.admin', 'com_kinoarhiv') && !JFactory::getUser()->authorise('core.edit.access', 'com_kinoarhiv'))
		{
			return array('success' => false, 'message' => JText::_('JERROR_ALERTNOAUTHOR'));
		}

		$model = $this->getModel('name');
		$result = $model->saveNameAccessRules();

		echo json_encode($result);
	}

	public function getAwards()
	{
		$document = JFactory::getDocument();
		$document->setName('response');

		$model = $this->getModel('name');
		$result = $model->getAwards();

		echo json_encode($result);
	}

	public function saveRelAwards()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.edit', 'com_kinoarhiv'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$document = JFactory::getDocument();
		$document->setName('response');

		$model = $this->getModel('relations');
		$result = $model->saveRelAwards();

		echo json_encode($result);
	}

	public function deleteRelAwards()
	{
		$document = JFactory::getDocument();
		$document->setName('response');

		$model = $this->getModel('name');
		$result = $model->deleteRelAwards();

		echo json_encode($result);
	}

	public function check_name()
	{
		$document = JFactory::getDocument();
		$document->setName('response');

		$model = $this->getModel('name');
		$result = $model->check_name();

		echo json_encode($result);
	}
}
