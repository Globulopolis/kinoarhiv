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
 * Reviews controller class
 *
 * @since  3.0
 */
class KinoarhivControllerReviews extends JControllerLegacy
{
	/**
	 * Method to edit an existing record or add a new record.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function edit()
	{
		$view = $this->getView('reviews', 'html');
		$model = $this->getModel('review');
		$view->setModel($model, true);

		$view->display('edit');
	}

	/**
	 * Proxy to KinoarhivControllerReviews::save()
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
		if (!$user->authorise('core.edit', 'com_kinoarhiv'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$app = JFactory::getApplication();
		$model = $this->getModel('review');
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
			KAComponentHelper::renderErrors($model->getErrors());
			$this->setRedirect('index.php?option=com_kinoarhiv&task=reviews.edit&id[]=' . $data['id']);

			return;
		}

		// Store data for use in KinoarhivModelReview::loadFormData()
		$app->setUserState('com_kinoarhiv.reviews.' . $user->id . '.edit_data', $validData);
		$result = $model->save($validData);

		if (!$result)
		{
			// Errors enqueue in the model
			$this->setRedirect('index.php?option=com_kinoarhiv&task=reviews.edit&id[]=' . $data['id']);

			return;
		}

		// Set the success message.
		$message = JText::_('COM_KA_ITEMS_SAVE_SUCCESS');

		// Delete session data taken from model
		$app->setUserState('com_kinoarhiv.reviews.' . $user->id . '.edit_data', null);

		// Set the redirect based on the task.
		switch ($this->getTask())
		{
			case 'apply':
				$this->setRedirect('index.php?option=com_kinoarhiv&task=reviews.edit&id[]=' . $data['id'], $message);
				break;

			case 'save':
			default:
				$this->setRedirect('index.php?option=com_kinoarhiv&view=reviews', $message);
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
		$this->publish(0);
	}

	/**
	 * Method to publish a list of items
	 *
	 * @param   integer  $state  Item state
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function publish($state = 1)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.edit.state', 'com_kinoarhiv'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$ids = $this->input->get('id', array(), 'array');
		$url = 'index.php?option=com_kinoarhiv&view=reviews';

		if (!is_array($ids) || count($ids) < 1)
		{
			$this->setRedirect($url, JText::_('JGLOBAL_NO_ITEM_SELECTED'), 'error');

			return;
		}

		$model = $this->getModel('review');

		// Make sure the item ids are integers
		$ids = Joomla\Utilities\ArrayHelper::toInteger($ids);

		if ($state == 0)
		{
			$message = 'COM_KA_ITEMS_N_UNPUBLISHED';
		}
		elseif ($state == 1)
		{
			$message = 'COM_KA_ITEMS_N_PUBLISHED';
		}
		elseif ($state == 2)
		{
			$message = 'COM_KA_ITEMS_N_ARCHIVED';
		}
		else
		{
			$this->setRedirect($url, JText::_('COM_KA_ITEMS_EDIT_ERROR'), 'error');

			return;
		}

		$result = $model->setItemState($ids, $state);

		if (!$result)
		{
			$this->setRedirect($url, JText::_('COM_KA_ITEMS_EDIT_ERROR'), 'error');

			return;
		}

		$this->setRedirect($url, JText::plural($message, count($ids)));
	}

	/**
	 * Trash (set enabled = -2) an item.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function trash()
	{
		$this->publish(-2);
	}

	/**
	 * Archive an item.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function archive()
	{
		$this->publish(2);
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
		if (!JFactory::getUser()->authorise('core.admin', 'com_kinoarhiv'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$model = $this->getModel('review');
		$result = $model->remove();

		if ($result === false)
		{
			$this->setRedirect('index.php?option=com_kinoarhiv&view=reviews', JText::_('COM_KA_ITEMS_EDIT_ERROR'), 'error');

			return;
		}

		$this->setRedirect('index.php?option=com_kinoarhiv&view=reviews', JText::_('COM_KA_ITEMS_DELETED_SUCCESS'));
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
		JFactory::getApplication()->setUserState('com_kinoarhiv.reviews.' . JFactory::getUser()->id . '.edit_data', null);

		$this->setRedirect('index.php?option=com_kinoarhiv&view=reviews');
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

		if (!$user->authorise('core.edit', 'com_kinoarhiv') && !$user->authorise('core.edit.state', 'com_kinoarhiv'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$app = JFactory::getApplication();
		$ids = $app->input->post->get('id', array(), 'array');

		if (count($ids) != 0)
		{
			$model = $this->getModel('reviews');
			$result = $model->batch();

			if ($result === false)
			{
				KAComponentHelper::renderErrors($model->getErrors(), 'html');
				$this->setRedirect('index.php?option=com_kinoarhiv&view=reviews');

				return;
			}
		}

		$this->setRedirect('index.php?option=com_kinoarhiv&view=reviews');
	}
}
