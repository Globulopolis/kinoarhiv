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
		if (!KAComponentHelper::checkToken('post'))
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JINVALID_TOKEN')));

			return;
		}

		$user = JFactory::getUser();

		// Check if the user is authorized to do this.
		if (!$user->authorise('core.create', 'com_kinoarhiv') && !$user->authorise('core.edit', 'com_kinoarhiv'))
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JERROR_ALERTNOAUTHOR')));

			return;
		}

		$app = JFactory::getApplication();
		$model = $this->getModel('premiere');
		$data = $this->input->post->get('form', array(), 'array');
		$form = $model->getForm($data, false);

		if (!$form)
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JGLOBAL_VALIDATION_FORM_FAILED')));

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
			$errors = KAComponentHelperBackend::renderErrors($model->getErrors(), 'json');

			echo json_encode(array('success' => false, 'message' => $errors));

			return;
		}

		// Store data for use in KinoarhivModelPremiere::loadFormData()
		$app->setUserState('com_kinoarhiv.premieres.' . $user->id . '.edit_data', $validData);
		$result = $model->save($validData);
		$session_data = $app->getUserState('com_kinoarhiv.premieres.' . $user->id . '.data');

		if (!$result)
		{
			$errors_arr = $app->getMessageQueue();
			echo json_encode(array('success' => false, 'message' => implode('<br/>', $errors_arr)));

			return;
		}

		// Delete session data taken from model
		$app->setUserState('com_kinoarhiv.premieres.' . $user->id . '.edit_data', null);

		echo json_encode(array('success' => true, 'message' => JText::_('COM_KA_ITEMS_SAVE_SUCCESS'), $session_data));
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
		if (!KAComponentHelper::checkToken('post'))
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JINVALID_TOKEN')));

			return;
		}

		$data = $this->input->post->get('ord', array(), 'array');
		$movie_id = $this->input->post->get('movie_id', 0, 'int');

		if (empty($movie_id))
		{
			echo json_encode(array('success' => false, 'message' => JText::_('COM_KA_SAVE_ORDER_ERROR')));

			return;
		}

		// Sorting required at least two items in list
		if (count($data) < 2)
		{
			echo json_encode(array('success' => false, 'message' => JText::_('COM_KA_SAVE_ORDER_AT_LEAST_TWO')));

			return;
		}

		$model = $this->getModel('premieres');
		$result = $model->saveOrder($data, $movie_id);

		if (!$result)
		{
			echo json_encode(array('success' => false, 'message' => JText::_('COM_KA_SAVE_ORDER_ERROR')));

			return;
		}

		echo json_encode(array('success' => true, 'message' => JText::_('COM_KA_SAVED')));
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
		if (!KAComponentHelper::checkToken('post'))
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JINVALID_TOKEN')));

			return;
		}

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.delete', 'com_kinoarhiv'))
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JERROR_ALERTNOAUTHOR')));

			return;
		}

		$model = $this->getModel('premiere');
		$result = $model->remove();

		if ($result === false)
		{
			$this->setRedirect('index.php?option=com_kinoarhiv&view=premieres', JText::_('COM_KA_ITEMS_EDIT_ERROR'), 'error');

			return;
		}

		if (!$result)
		{
			echo json_encode(array('success' => false, 'message' => JText::_('COM_KA_ITEMS_EDIT_ERROR')));

			return;
		}

		$this->setRedirect('index.php?option=com_kinoarhiv&view=premieres', JText::_('COM_KA_ITEMS_DELETED_SUCCESS'));
	}
}
