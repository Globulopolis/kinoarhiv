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
 * Careers list controller class.
 *
 * @since  3.1
 */
class KinoarhivControllerCareers extends JControllerLegacy
{
	/**
	 * Proxy to KinoarhivControllerCareers::save()
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function apply()
	{
		$this->save();
	}

	/**
	 * Proxy to KinoarhivControllerCareers::save()
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function save2new()
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
		if (!KAComponentHelper::checkToken('post'))
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JINVALID_TOKEN')));

			return;
		}

		$user = JFactory::getUser();

		// Check if the user is authorized to do this.
		if (!$user->authorise('core.create.career', 'com_kinoarhiv') && !$user->authorise('core.edit.career', 'com_kinoarhiv'))
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JERROR_ALERTNOAUTHOR')));

			return;
		}

		$app = JFactory::getApplication();
		$model = $this->getModel('career');
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
				unset($data['c_' . $key]);
			}
		}

		$validData = $model->validate($form, $data);

		if ($validData === false)
		{
			$errors = KAComponentHelperBackend::renderErrors($model->getErrors(), 'json');

			echo json_encode(array('success' => false, 'message' => $errors));

			return;
		}

		// Store data for use in KinoarhivModelCareer::loadFormData()
		$app->setUserState('com_kinoarhiv.careers.' . $user->id . '.edit_data', $validData);
		$result = $model->save($validData);
		$session_data = $app->getUserState('com_kinoarhiv.careers.' . $user->id . '.edit_data');

		if (!$result)
		{
			$errors_arr = $app->getMessageQueue();
			echo json_encode(array('success' => false, 'message' => implode('<br/>', $errors_arr)));

			return;
		}

		// Delete session data taken from model
		$app->setUserState('com_kinoarhiv.careers.' . $user->id . '.edit_data', null);

		echo json_encode(array('success' => true, 'message' => JText::_('COM_KA_ITEMS_SAVE_SUCCESS'), $session_data));
	}

	/**
	 * Method to save the submitted ordering values for records.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function saveOrder()
	{
		if (!KAComponentHelper::checkToken('post'))
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JINVALID_TOKEN')));

			return;
		}

		$data = $this->input->post->get('ord', array(), 'array');

		// Sorting required at least two items in list
		if (count($data) < 2)
		{
			echo json_encode(array('success' => false, 'message' => JText::_('COM_KA_SAVE_ORDER_AT_LEAST_TWO')));

			return;
		}

		$model = $this->getModel('careers');
		$result = $model->saveOrder($data);

		if (!$result)
		{
			echo json_encode(array('success' => false, 'message' => JText::_('COM_KA_SAVE_ORDER_ERROR')));

			return;
		}

		echo json_encode(array('success' => true, 'message' => JText::_('COM_KA_SAVED')));
	}
}
