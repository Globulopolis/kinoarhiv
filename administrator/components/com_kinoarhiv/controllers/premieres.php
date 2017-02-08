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
 * Premieres list controller class
 *
 * @since  3.0
 */
class KinoarhivControllerPremieres extends JControllerLegacy
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
		$view = $this->getView('premieres', 'html');
		$model = $this->getModel('premiere');
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
	 * Proxy to KinoarhivControllerPremieres::save()
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
	 * Proxy to KinoarhivControllerPremieres::save()
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
		if (!$user->authorise('core.create', 'com_kinoarhiv') && !$user->authorise('core.edit', 'com_kinoarhiv'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$app = JFactory::getApplication();
		$model = $this->getModel('premiere');
		$data = $this->input->post->get('form', array(), 'array');
		$form = $model->getForm($data, false);

		if (!$form)
		{
			$app->enqueueMessage(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'), 'error');

			return;
		}

		// Process aliases for columns name
		if ($app->input->get('alias', 0, 'int') == 1)
		{
			foreach ($data as $key => $value)
			{
				$key = substr($key, 2);
				$data[$key] = $value;
				unset($data['p_' . $key]);
			}
		}

		$validData = $model->validate($form, $data);

		if ($validData === false)
		{
			KAComponentHelperBackend::renderErrors($model->getErrors());

			$this->setRedirect('index.php?option=com_kinoarhiv&task=premieres.edit&id[]=' . $data['id']);

			return;
		}

		// Store data for use in KinoarhivModelPremiere::loadFormData()
		$app->setUserState('com_kinoarhiv.premieres.' . $user->id . '.edit_data', $validData);
		$result = $model->save($validData);

		if (!$result)
		{
			// Errors enqueue in the model
			$this->setRedirect('index.php?option=com_kinoarhiv&task=premieres.edit&id[]=' . $data['id']);

			return;
		}

		$session_data = $app->getUserState('com_kinoarhiv.premieres.' . $user->id . '.edit_data');

		// Set the success message.
		$message = JText::_('COM_KA_ITEMS_SAVE_SUCCESS');

		// Delete session data taken from model
		$app->setUserState('com_kinoarhiv.premieres.' . $user->id . '.edit_data', null);

		// Set the redirect based on the task.
		switch ($this->getTask())
		{
			case 'save2new':
				$this->setRedirect('index.php?option=com_kinoarhiv&task=premieres.add', $message);
				break;
			case 'apply':
				$this->setRedirect('index.php?option=com_kinoarhiv&task=premieres.edit&id[]=' . $session_data['id'], $message);
				break;

			case 'save':
			default:
				$this->setRedirect('index.php?option=com_kinoarhiv&view=premieres', $message);
				break;
		}
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
		if (!JFactory::getUser()->authorise('core.delete', 'com_kinoarhiv'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$model = $this->getModel('premiere');
		$result = $model->remove();

		if ($result === false)
		{
			$this->setRedirect('index.php?option=com_kinoarhiv&view=premieres', JText::_('COM_KA_ITEMS_EDIT_ERROR'), 'error');

			return;
		}

		$this->setRedirect('index.php?option=com_kinoarhiv&view=premieres', JText::_('COM_KA_ITEMS_DELETED_SUCCESS'));
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
		// Clean the session data.
		JFactory::getApplication()->setUserState('com_kinoarhiv.premieres.' . JFactory::getUser()->id . '.edit_data', null);

		$this->setRedirect('index.php?option=com_kinoarhiv&view=premieres');
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
			&& !$user->authorise('core.edit', 'com_kinoarhiv')
			&& !$user->authorise('core.edit.state', 'com_kinoarhiv'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$app = JFactory::getApplication();
		$ids = $app->input->post->get('id', array(), 'array');

		if (count($ids) != 0)
		{
			$model = $this->getModel('premieres');
			$result = $model->batch();

			if ($result === false)
			{
				KAComponentHelperBackend::renderErrors($model->getErrors(), 'html');
				$this->setRedirect('index.php?option=com_kinoarhiv&view=premieres');

				return;
			}
		}

		$this->setRedirect('index.php?option=com_kinoarhiv&view=premieres');
	}
}
