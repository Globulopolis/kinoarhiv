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
 * Countries list controller class.
 *
 * @since  3.0
 */
class KinoarhivControllerCountries extends JControllerLegacy
{
	/**
	 * Proxy to KinoarhivControllerCountries::save()
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
	 * Proxy to KinoarhivControllerCountries::save()
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
	 * @return  mixed
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
		if (!$user->authorise('core.create.country', 'com_kinoarhiv') && !$user->authorise('core.edit.country', 'com_kinoarhiv'))
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JERROR_ALERTNOAUTHOR')));

			return;
		}

		$app = JFactory::getApplication();
		$model = $this->getModel('country');
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

		// Store data for use in KinoarhivModelCountry::loadFormData()
		$app->setUserState('com_kinoarhiv.countries.' . $user->id . '.edit_data', $validData);
		$result = $model->save($validData);
		$session_data = $app->getUserState('com_kinoarhiv.countries.' . $user->id . '.edit_data');

		if (!$result)
		{
			$errors_arr = $app->getMessageQueue();
			echo json_encode(array('success' => false, 'message' => implode('<br/>', $errors_arr)));

			return;
		}

		// Delete session data taken from model
		$app->setUserState('com_kinoarhiv.countries.' . $user->id . '.edit_data', null);

		echo json_encode(array('success' => true, 'message' => JText::_('COM_KA_ITEMS_SAVE_SUCCESS'), $session_data));
	}
}