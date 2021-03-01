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
 * Music albums controller class
 *
 * @since  3.1
 */
class KinoarhivControllerAlbums extends JControllerLegacy
{
	/**
	 * Removes album awards.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function removeAlbumAwards()
	{
		if (!KAComponentHelper::checkToken())
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
		if (!$user->authorise('core.edit', 'com_kinoarhiv.album.' . $id) && !$user->authorise('core.delete', 'com_kinoarhiv.album.' . $id))
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

		/** @var KinoarhivModelAlbum $model */
		$model = $this->getModel('album');
		$result = $model->removeAlbumAwards($newIDs);

		if (!$result)
		{
			$errors = KAComponentHelper::renderErrors($app->getMessageQueue(), 'json');
			echo json_encode(array('success' => false, 'message' => $errors));

			return;
		}

		echo json_encode(array('success' => true, 'message' => ''));
	}

	/**
	 * Removes album crew.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function removeAlbumCrew()
	{
		if (!KAComponentHelper::checkToken())
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JINVALID_TOKEN')));

			return;
		}

		$app    = JFactory::getApplication();
		$user   = JFactory::getUser();
		$id     = $app->input->getInt('id', 0);
		$ids    = $app->input->get('items', array(), 'array');
		$type   = $app->input->getInt('item_type', 0);
		$newIDs = array();

		// Check if the user is authorized to do this.
		if (!$user->authorise('core.edit', 'com_kinoarhiv.album.' . $id) && !$user->authorise('core.delete', 'com_kinoarhiv.album.' . $id))
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

		/** @var KinoarhivModelAlbum $model */
		$model = $this->getModel('album');
		$result = $model->removeAlbumCrew($newIDs, $type);

		if (!$result)
		{
			$errors = KAComponentHelper::renderErrors($app->getMessageQueue(), 'json');
			echo json_encode(array('success' => false, 'message' => $errors));

			return;
		}

		echo json_encode(array('success' => true, 'message' => ''));
	}

	/**
	 * Removes album releases.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function removeAlbumReleases()
	{
		if (!KAComponentHelper::checkToken())
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
		if (!$user->authorise('core.edit', 'com_kinoarhiv.album.' . $id) && !$user->authorise('core.delete', 'com_kinoarhiv.album.' . $id))
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

		/** @var KinoarhivModelAlbum $model */
		$model = $this->getModel('album');
		$result = $model->removeAlbumReleases($newIDs);

		if (!$result)
		{
			$errors = KAComponentHelper::renderErrors($app->getMessageQueue(), 'json');
			echo json_encode(array('success' => false, 'message' => $errors));

			return;
		}

		echo json_encode(array('success' => true, 'message' => ''));
	}

	/**
	 * Removes album tracks. Doesn't delete files from filesystem.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function removeTracks()
	{
		if (!KAComponentHelper::checkToken())
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
		if (!$user->authorise('core.edit', 'com_kinoarhiv.album.' . $id) && !$user->authorise('core.delete', 'com_kinoarhiv.album.' . $id))
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

		/** @var KinoarhivModelAlbum $model */
		$model = $this->getModel('album');
		$result = $model->removeTracks($newIDs);

		if (!$result)
		{
			$errors = KAComponentHelper::renderErrors($app->getMessageQueue(), 'json');
			echo json_encode(array('success' => false, 'message' => $errors));

			return;
		}

		echo json_encode(array('success' => true, 'message' => ''));
	}
}
