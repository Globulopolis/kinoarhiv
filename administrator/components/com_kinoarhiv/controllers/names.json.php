<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *  
 * @copyright   Copyright (C) 2017 Libra.ms. All rights reserved.
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
	 * Method to remove award(s) in awards list on 'awards tab'.
	 *
	 * @return  string
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

		$app     = JFactory::getApplication();
		$user    = JFactory::getUser();
		$id      = $app->input->getInt('id', 0);
		$ids     = $app->input->get('items', array(), 'array');
		$new_ids = array();

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
			$new_ids[] = end($_id);
		}

		// Make sure the item ids are integers
		$new_ids = Joomla\Utilities\ArrayHelper::toInteger($new_ids);

		$model = $this->getModel('name');
		$result = $model->removeNameAwards($new_ids);

		if (!$result)
		{
			$errors = KAComponentHelperBackend::renderErrors($app->getMessageQueue(), 'json');
			echo json_encode(array('success' => false, 'message' => implode('<br/>', $errors)));

			return;
		}

		echo json_encode(array('success' => true, 'message' => ''));
	}

	/**
	 * Method to get an item alias for filesystem.
	 *
	 * @return  string
	 *
	 * @since   3.1
	 */
	public function getFilesystemAlias()
	{
		$app        = JFactory::getApplication();
		$name       = $app->input->getString('name', '');
		$latin_name = $app->input->getString('latin_name', '');
		$alias      = $app->input->getString('alias', '');

		if (empty($alias))
		{
			$name = empty($latin_name) ? $name : $latin_name;

			if (JFactory::getConfig()->get('unicodeslugs') == 1)
			{
				$alias = JFilterOutput::stringUrlUnicodeSlug($name);
			}
			else
			{
				$alias = JFilterOutput::stringURLSafe($name);
			}
		}

		$fs_alias = rawurlencode(StringHelper::substr($alias, 0, 1));

		echo json_encode(
			array('success' => true, 'fs_alias' => $fs_alias)
		);
	}
}
