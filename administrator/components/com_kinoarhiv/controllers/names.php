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

use Joomla\String\String;

/**
 * Names list controller class
 *
 * @since  3.0
 */
class KinoarhivControllerNames extends JControllerLegacy
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
	}

	/**
	 * Proxy to KinoarhivControllerNames::save()
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
	 * Proxy to KinoarhivControllerNames::save()
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
		if (!$user->authorise('core.create', 'com_kinoarhiv') && !$user->authorise('core.edit', 'com_kinoarhiv.name'))
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
		$model = $this->getModel('name');
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

		if (!array_key_exists('id', $data) || empty($data['id']))
		{
			$id = $app->input->get('id', array(), 'array');
			$id = $id[0];
		}
		else
		{
			$id = $data['id'];
		}

		if ($validData === false)
		{
			$errors = KAComponentHelper::renderErrors($model->getErrors(), $document->getType());

			if ($document->getType() == 'html')
			{
				$this->setRedirect('index.php?option=com_kinoarhiv&controller=names&task=edit&id[]=' . $id);

				return;
			}
			else
			{
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
				KAComponentHelper::renderErrors($model->getErrors(), 'html');
				$this->setRedirect('index.php?option=com_kinoarhiv&controller=names&task=edit&id[]=' . $id);

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

			return;
		}

		// Clean the session data.
		$app = JFactory::getApplication();
		$app->setUserState('com_kinoarhiv.names.global.data', null);

		$message = $isUnpublish ? JText::_('COM_KA_ITEMS_EDIT_UNPUBLISHED') : JText::_('COM_KA_ITEMS_EDIT_PUBLISHED');
		$this->setRedirect('index.php?option=com_kinoarhiv&view=names', $message);
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

			return;
		}

		// Clean the session data.
		$app = JFactory::getApplication();
		$app->setUserState('com_kinoarhiv.names.global.data', null);

		$this->setRedirect('index.php?option=com_kinoarhiv&view=names', JText::_('COM_KA_ITEMS_DELETED_SUCCESS'));
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

		$model = $this->getModel('names');
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
			&& !$user->authorise('core.edit', 'com_kinoarhiv.name')
			&& !$user->authorise('core.edit.state', 'com_kinoarhiv.name'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$app = JFactory::getApplication();
		$ids = $app->input->post->get('id', array(), 'array');

		if (count($ids) != 0)
		{
			$model = $this->getModel('names');
			$result = $model->batch();

			if ($result === false)
			{
				KAComponentHelper::renderErrors($model->getErrors(), 'html');
				$this->setRedirect('index.php?option=com_kinoarhiv&view=names');

				return;
			}
		}

		$this->setRedirect('index.php?option=com_kinoarhiv&view=names');
	}

	/**
	 * Method to save access rules for an item.
	 *
	 * @return  mixed
	 *
	 * @since   3.0
	 */
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

		return true;
	}

	/**
	 * Method to get a list of awards for person.
	 *
	 * @return  string
	 *
	 * @since   3.0
	 */
	public function getAwards()
	{
		$model = $this->getModel('name');
		$result = $model->getAwards();

		echo json_encode($result);
	}

	/**
	 * Method to save a new award in award_edit layout.
	 *
	 * @return  string
	 *
	 * @since   3.0
	 */
	public function saveRelAwards()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.edit', 'com_kinoarhiv'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$model = $this->getModel('relations');
		$result = $model->saveRelAwards();

		echo json_encode($result);
	}

	/**
	 * Method to delete award(s) in awards list on 'awards tab'.
	 *
	 * @return  string
	 *
	 * @since   3.0
	 */
	public function deleteRelAwards()
	{
		$model = $this->getModel('name');
		$result = $model->deleteRelAwards();

		echo json_encode($result);
	}

	/**
	 * Method to encode item alias for using in filesystem paths and url.
	 *
	 * @return  string
	 *
	 * @since  3.0
	 */
	public function getFilesystemAlias()
	{
		$input = JFactory::getApplication()->input;
		$alias = $input->get('form_name_alias', '', 'string');

		if (empty($alias))
		{
			echo json_encode(
				array(
					'success' => false,
					'message' => JText::_('COM_KA_FIELD_NAME_FS_ALIAS_GET_ERROR')
				)
			);

			return;
		}

		$item_alias = String::substr(String::strtolower($alias), 0, 1);

		echo json_encode(
			array(
				'success' => true,
				'data'    => rawurlencode($item_alias)
			)
		);
	}
}
