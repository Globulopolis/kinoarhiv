<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2018 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

/**
 * Music controller class
 *
 * @since  3.1
 */
class KinoarhivControllerAlbums extends JControllerLegacy
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 * Recognized key values include 'name', 'default_task', 'model_path', and
	 * 'view_path' (this list is not meant to be comprehensive).
	 *
	 * @since   3.0
	 */
	public function __construct($config = array())
	{
		$this->addModelPath(JPath::clean(JPATH_COMPONENT . '/models/music/'));

		parent::__construct($config);
	}

	/**
	 * Mark album as favorite
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function favorite()
	{
		if (JFactory::getUser()->guest)
		{
			header($_SERVER['SERVER_PROTOCOL'] . ' 401 Unauthorized', true, 401);
			jexit();
		}

		$id = $this->input->get('id', 0, 'int');
		$view = $this->input->get('view', 'albums', 'cmd');
		$itemid = $this->input->get('Itemid', 0, 'int');
		$action = $this->input->get('action', '', 'word');

		if ($action == 'delete')
		{
			$this->favoriteRemove();

			return;
		}

		$model = $this->getModel('albums');
		$result = $model->favoriteAdd($id);

		if (!$result)
		{
			$errors = KAComponentHelper::renderErrors(JFactory::getApplication()->getMessageQueue(), 'json');
			echo json_encode(array('success' => false, 'message' => $errors));
		}
		else
		{
			echo json_encode(
				array(
					'success' => true,
					'message' => JText::_('COM_KA_FAVORITE_ADDED'),
					'url' => JRoute::_('index.php?option=com_kinoarhiv&view=' . $view . '&task=albums.favorite&action=delete&Itemid=' . $itemid . '&id=' . $id, false),
					'text' => JText::_('COM_KA_REMOVEFROM_FAVORITE')
				)
			);
		}
	}

	/**
	 * Removes album(s) from favorites list.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function favoriteRemove()
	{
		if (JFactory::getUser()->guest)
		{
			header($_SERVER['SERVER_PROTOCOL'] . ' 401 Unauthorized', true, 401);
			jexit();
		}

		$id = $this->input->get('id', 0, 'int');
		$view = $this->input->get('view', 'albums', 'cmd');
		$itemid = $this->input->get('Itemid', 0, 'int');
		$model = $this->getModel('albums');
		$result = $model->favoriteRemove($id);

		if (!$result)
		{
			$errors = KAComponentHelper::renderErrors(JFactory::getApplication()->getMessageQueue(), 'json');
			echo json_encode(array('success' => false, 'message' => $errors));
		}
		else
		{
			echo json_encode(
				array(
					'success' => true,
					'message' => JText::_('COM_KA_FAVORITE_REMOVED'),
					'url' => JRoute::_('index.php?option=com_kinoarhiv&view=' . $view . '&task=albums.favorite&action=add&Itemid=' . $itemid . '&id=' . $id, false),
					'text' => JText::_('COM_KA_ADDTO_FAVORITE')
				)
			);
		}
	}

	/**
	 * Process user votes
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function vote()
	{
		if (JFactory::getUser()->guest)
		{
			header($_SERVER['SERVER_PROTOCOL'] . ' 401 Unauthorized', true, 401);
			jexit();
		}

		if (!KAComponentHelper::checkToken('get'))
		{
			header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden', true, 403);
			jexit();
		}

		$id     = $this->input->get('id', 0, 'int');
		$value  = $this->input->get('value', 0, 'int');
		$model  = $this->getModel('albums');
		$result = $model->vote($id, $value);

		echo json_encode($result);
	}

	/**
	 * Removes user votes
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function votesRemove()
	{
		if (JFactory::getUser()->guest)
		{
			header($_SERVER['SERVER_PROTOCOL'] . ' 401 Unauthorized', true, 401);
			jexit();
		}

		if (!KAComponentHelper::checkToken('get'))
		{
			header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden', true, 403);
			jexit();
		}

		$ids = $this->input->get('id', array(), 'array');

		if (!is_array($ids) || count($ids) < 1)
		{
			header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
			jexit();
		}

		// Make sure the item ids are integers
		$ids = Joomla\Utilities\ArrayHelper::toInteger($ids);

		$model = $this->getModel('albums');
		$result = $model->votesRemove($ids);

		echo json_encode($result);
	}
}
