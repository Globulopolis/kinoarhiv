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

use Joomla\String\StringHelper;

/**
 * Movies controller class
 *
 * @since  3.1
 */
class KinoarhivControllerMovies extends JControllerLegacy
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

		$model = $this->getModel('movies');
		$result = $model->saveOrder();

		echo json_encode($result);
	}

	/**
	 * Removes award(s) in awards list on 'awards tab'.
	 *
	 * @return  string
	 *
	 * @since   3.1
	 */
	public function removeMovieAwards()
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
		if (!$user->authorise('core.edit', 'com_kinoarhiv.movie.' . $id) && !$user->authorise('core.delete', 'com_kinoarhiv.movie.' . $id))
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

		$model = $this->getModel('movie');
		$result = $model->removeMovieAwards($new_ids);

		if (!$result)
		{
			$errors = KAComponentHelperBackend::renderErrors($app->getMessageQueue(), 'json');
			echo json_encode(array('success' => false, 'message' => implode('<br/>', $errors)));

			return;
		}

		echo json_encode(array('success' => true, 'message' => ''));
	}

	/**
	 * Removes premiere(s) in premieres list on 'premieres tab'.
	 *
	 * @return  string
	 *
	 * @since   3.1
	 */
	public function removeMoviePremieres()
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
		if (!$user->authorise('core.edit', 'com_kinoarhiv.movie.' . $id) && !$user->authorise('core.delete', 'com_kinoarhiv.movie.' . $id))
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

		$model = $this->getModel('premiere');
		$result = $model->remove($new_ids);

		if (!$result)
		{
			$errors = KAComponentHelperBackend::renderErrors($app->getMessageQueue(), 'json');
			echo json_encode(array('success' => false, 'message' => implode('<br/>', $errors)));

			return;
		}

		echo json_encode(array('success' => true, 'message' => ''));
	}

	/**
	 * Removes release(s) in releases list on 'releases tab'.
	 *
	 * @return  string
	 *
	 * @since   3.1
	 */
	public function removeMovieReleases()
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
		if (!$user->authorise('core.edit', 'com_kinoarhiv.movie.' . $id) && !$user->authorise('core.delete', 'com_kinoarhiv.movie.' . $id))
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

		$model = $this->getModel('release');
		$result = $model->remove($new_ids);

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
		$app   = JFactory::getApplication();
		$title = $app->input->getString('title', '');
		$alias = $app->input->getString('alias', '');

		if (empty($alias))
		{
			if (JFactory::getConfig()->get('unicodeslugs') == 1)
			{
				$alias = JFilterOutput::stringUrlUnicodeSlug($title);
			}
			else
			{
				$alias = JFilterOutput::stringURLSafe($title);
			}
		}

		$fs_alias = rawurlencode(StringHelper::substr($alias, 0, 1));

		echo json_encode(
			array('success' => true, 'fs_alias' => $fs_alias)
		);
	}
}
