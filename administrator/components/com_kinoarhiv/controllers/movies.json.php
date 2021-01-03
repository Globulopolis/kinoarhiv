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
 * Movies controller class
 *
 * @since  3.1
 */
class KinoarhivControllerMovies extends JControllerLegacy
{
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
		if (!$user->authorise('core.create', 'com_kinoarhiv') && !$user->authorise('core.edit', 'com_kinoarhiv'))
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JERROR_ALERTNOAUTHOR')));

			return;
		}

		$app = JFactory::getApplication();

		/** @var KinoarhivModelMovie $model */
		$model = $this->getModel('movie');
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
			$errors = KAComponentHelper::renderErrors($model->getErrors(), 'json');

			echo json_encode(array('success' => false, 'message' => $errors));

			return;
		}

		$result = $model->save($validData);

		if (!$result)
		{
			$errors = KAComponentHelper::renderErrors($app->getMessageQueue(), 'json');
			echo json_encode(array('success' => false, 'message' => $errors));

			return;
		}

		// Delete session data taken from model
		$app->setUserState('com_kinoarhiv.movies.' . $user->id . '.edit_data', null);

		echo json_encode(array('success' => true, 'message' => JText::_('COM_KA_ITEMS_SAVE_SUCCESS'), $validData));
	}

	/**
	 * Removes cast in cast&crew list on 'cast&crew tab'.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function removeMovieCast()
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

		// Get IDs from string
		foreach ($ids as $key => $_id)
		{
			$array = explode('_', $_id['name']);
			$total = count($array);
			$rowID = (int) $array[$total - 3];

			$newIDs['rows'][$rowID][] = (int) $array[$total - 1];
			$newIDs['row_ids'][] = $rowID;
		}

		/** @var KinoarhivModelMovie $model */
		$model = $this->getModel('movie');
		$result = $model->removeMovieCast($newIDs);

		if (!$result)
		{
			$errors = KAComponentHelper::renderErrors($app->getMessageQueue(), 'json');
			echo json_encode(array('success' => false, 'message' => $errors));

			return;
		}

		echo json_encode(array('success' => true, 'message' => ''));
	}

	/**
	 * Removes award(s) in awards list on 'awards tab'.
	 *
	 * @return  void
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

		$app    = JFactory::getApplication();
		$user   = JFactory::getUser();
		$id     = $app->input->getInt('id', 0);
		$ids    = $app->input->get('items', array(), 'array');
		$newIDs = array();

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
			$newIDs[] = end($_id);
		}

		// Make sure the item ids are integers
		$newIDs = Joomla\Utilities\ArrayHelper::toInteger($newIDs);

		/** @var KinoarhivModelMovie $model */
		$model = $this->getModel('movie');
		$result = $model->removeMovieAwards($newIDs);

		if (!$result)
		{
			$errors = KAComponentHelper::renderErrors($app->getMessageQueue(), 'json');
			echo json_encode(array('success' => false, 'message' => $errors));

			return;
		}

		echo json_encode(array('success' => true, 'message' => ''));
	}

	/**
	 * Removes premiere(s) in premieres list on 'premieres tab'.
	 *
	 * @return  void
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

		$app    = JFactory::getApplication();
		$user   = JFactory::getUser();
		$id     = $app->input->getInt('id', 0);
		$ids    = $app->input->get('items', array(), 'array');
		$newIDs = array();

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
			$newIDs[] = end($_id);
		}

		// Make sure the item ids are integers
		$newIDs = Joomla\Utilities\ArrayHelper::toInteger($newIDs);

		/** @var KinoarhivModelPremiere $model */
		$model = $this->getModel('premiere');
		$result = $model->remove($newIDs);

		if (!$result)
		{
			$errors = KAComponentHelper::renderErrors($app->getMessageQueue(), 'json');
			echo json_encode(array('success' => false, 'message' => $errors));

			return;
		}

		echo json_encode(array('success' => true, 'message' => ''));
	}

	/**
	 * Removes release(s) in releases list on 'releases tab'.
	 *
	 * @return  void
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

		$app    = JFactory::getApplication();
		$user   = JFactory::getUser();
		$id     = $app->input->getInt('id', 0);
		$ids    = $app->input->get('items', array(), 'array');
		$newIDs = array();

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
			$newIDs[] = end($_id);
		}

		// Make sure the item ids are integers
		$newIDs = Joomla\Utilities\ArrayHelper::toInteger($newIDs);

		/** @var KinoarhivModelRelease $model */
		$model = $this->getModel('release');
		$result = $model->remove($newIDs);

		if (!$result)
		{
			$errors = KAComponentHelper::renderErrors($app->getMessageQueue(), 'json');
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

		$fsAlias = rawurlencode(StringHelper::substr($alias, 0, 1));

		echo json_encode(array('success' => true, 'fs_alias' => $fsAlias));
	}
}
