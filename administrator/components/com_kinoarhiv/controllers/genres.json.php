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
 * Genres list controller class.
 *
 * @since  3.1
 */
class KinoarhivControllerGenres extends JControllerLegacy
{
	/**
	 * Method to save a record.
	 *
	 * @return  mixed
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
		if (!$user->authorise('core.create.genre', 'com_kinoarhiv') && !$user->authorise('core.edit.genre', 'com_kinoarhiv'))
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JERROR_ALERTNOAUTHOR')));

			return;
		}

		$app = JFactory::getApplication();
		$model = $this->getModel('genre');
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

		// Store data for use in KinoarhivModelGenre::loadFormData()
		$app->setUserState('com_kinoarhiv.genres.' . $user->id . '.edit_data', $validData);
		$result = $model->save($validData);
		$session_data = $app->getUserState('com_kinoarhiv.genres.' . $user->id . '.edit_data');

		if (!$result)
		{
			$errors = KAComponentHelperBackend::renderErrors($app->getMessageQueue(), 'json');
			echo json_encode(array('success' => false, 'message' => $errors));

			return;
		}

		// Delete session data taken from model
		$app->setUserState('com_kinoarhiv.genres.' . $user->id . '.edit_data', null);

		echo json_encode(array('success' => true, 'message' => JText::_('COM_KA_ITEMS_SAVE_SUCCESS'), $session_data));
	}

	/**
	 * Method to update stats for genres.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function updateStat()
	{
		if (!KAComponentHelper::checkToken('post'))
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JINVALID_TOKEN')));

			return;
		}

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.recount.genre', 'com_kinoarhiv'))
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JERROR_ALERTNOAUTHOR')));

			return;
		}

		$app  = JFactory::getApplication();
		$ids  = $app->input->get('id', array(), 'array');
		$type = $app->input->get('type', 'movie', 'word');

		if (!is_array($ids) || count($ids) < 1)
		{
			echo json_encode(array('success' => false, 'message' => JText::_('COM_KA_GENRES_STATS_UPDATE_ERROR')));

			return;
		}

		$model  = $this->getModel('genre');
		$result = $model->updateStats($ids, $type);

		if ($result === false)
		{
			echo json_encode(array('success' => false, 'message' => JText::_('COM_KA_GENRES_STATS_UPDATE_ERROR')));

			return;
		}

		if ($type == 'movie')
		{
			$msg = JText::_('COM_KA_GENRES_STATS_UPDATED') . ' ' . JText::sprintf('COM_KA_GENRES_STATS_UPDATED_COUNT', $result);
		}
		else
		{
			$msg = JText::_('COM_KA_GENRES_STATS_UPDATED') . ' ' . JText::sprintf('COM_KA_GENRES_MUSIC_STATS_UPDATED_COUNT', $result);
		}

		echo json_encode(array('success' => true, 'message' => $msg, 'total' => $result));
	}
}
