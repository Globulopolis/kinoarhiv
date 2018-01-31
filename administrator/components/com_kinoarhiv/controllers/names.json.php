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

use Joomla\String\StringHelper;

/**
 * Names controller class
 *
 * @since  3.1
 */
class KinoarhivControllerNames extends JControllerLegacy
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
		$model = $this->getModel('name');
		$data = $this->input->post->get('jform', array(), 'array');
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

		$result = $model->save($validData);

		if (!$result)
		{
			$errors = KAComponentHelperBackend::renderErrors($app->getMessageQueue(), 'json');
			echo json_encode(array('success' => false, 'message' => $errors));

			return;
		}

		// Delete session data taken from model
		$app->setUserState('com_kinoarhiv.names.' . $user->id . '.edit_data', null);

		echo json_encode(array('success' => true, 'message' => JText::_('COM_KA_ITEMS_SAVE_SUCCESS'), $validData));
	}

	/**
	 * Method to remove award(s) in awards list on 'awards tab'.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function removeNameAwards()
	{
		if (!KAComponentHelper::checkToken('post'))
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JINVALID_TOKEN')));

			return;
		}

		$app    = JFactory::getApplication();
		$user   = JFactory::getUser();
		$id     = $app->input->getInt('id', 0);
		$ids    = $app->input->get('items', array(), 'array');
		$newIDs = array();

		// Check if the user is authorized to do this.
		if (!$user->authorise('core.edit', 'com_kinoarhiv.name.' . $id) && !$user->authorise('core.delete', 'com_kinoarhiv.name.' . $id))
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JERROR_ALERTNOAUTHOR')));

			return;
		}

		if (!is_array($ids) || count($ids) < 1)
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JGLOBAL_NO_ITEM_SELECTED')));

			return;
		}

		// Get ID from string
		foreach ($ids as $id)
		{
			$_id = explode('_', $id['name']);
			$newIDs[] = end($_id);
		}

		// Make sure the item ids are integers
		$newIDs = Joomla\Utilities\ArrayHelper::toInteger($newIDs);

		$model = $this->getModel('name');
		$result = $model->removeNameAwards($newIDs);

		if (!$result)
		{
			$errors = KAComponentHelperBackend::renderErrors($app->getMessageQueue(), 'json');
			echo json_encode(array('success' => false, 'message' => $errors));

			return;
		}

		echo json_encode(array('success' => true, 'message' => ''));
	}

	/**
	 * Method to get an item alias for filesystem.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function getFilesystemAlias()
	{
		$app       = JFactory::getApplication();
		$name      = $app->input->getString('name', '');
		$latinName = $app->input->getString('latin_name', '');
		$alias     = $app->input->getString('alias', '');

		if (empty($alias))
		{
			$name = empty($latinName) ? $name : $latinName;

			if (JFactory::getConfig()->get('unicodeslugs') == 1)
			{
				$alias = JFilterOutput::stringUrlUnicodeSlug($name);
			}
			else
			{
				$alias = JFilterOutput::stringURLSafe($name);
			}
		}

		$fsAlias = rawurlencode(StringHelper::substr($alias, 0, 1));

		echo json_encode(
			array('success' => true, 'fs_alias' => $fsAlias)
		);
	}
}
