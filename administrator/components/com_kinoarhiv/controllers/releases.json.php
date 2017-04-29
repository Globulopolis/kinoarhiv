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
 * Releases list controller class
 *
 * @since  3.0
 */
class KinoarhivControllerReleases extends JControllerLegacy
{
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
		$model = $this->getModel('release');
		$data = $this->input->post->get('form', array(), 'array');
		$form = $model->getForm($data, false);

		if (!$form)
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JGLOBAL_VALIDATION_FORM_FAILED')));

			return;
		}

		$validData = $model->validate($form, $data);

		if ($validData === false)
		{
			$errors = KAComponentHelperBackend::renderErrors($model->getErrors(), 'json');

			echo json_encode(array('success' => false, 'message' => $errors));

			return;
		}

		// Store data for use in KinoarhivModelRelease::loadFormData()
		$app->setUserState('com_kinoarhiv.releases.' . $user->id . '.edit_data', $validData);
		$result = $model->save($validData);
		$session_data = $app->getUserState('com_kinoarhiv.releases.' . $user->id . '.data');

		if (!$result)
		{
			$errors = KAComponentHelperBackend::renderErrors($app->getMessageQueue(), 'json');
			echo json_encode(array('success' => false, 'message' => $errors));

			return;
		}

		// Delete session data taken from model
		$app->setUserState('com_kinoarhiv.releases.' . $user->id . '.edit_data', null);

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

		$model = $this->getModel('releases');
		$result = $model->saveOrder($data, $movie_id);

		if (!$result)
		{
			echo json_encode(array('success' => false, 'message' => JText::_('COM_KA_SAVE_ORDER_ERROR')));

			return;
		}

		echo json_encode(array('success' => true, 'message' => JText::_('COM_KA_SAVED')));
	}
}
