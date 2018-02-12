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
 * Persons controller class
 *
 * @since  3.1
 */
class KinoarhivControllerNames extends JControllerLegacy
{
	/**
	 * Mark person as favorite
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
		$view = $this->input->get('view', 'names', 'cmd');
		$itemid = $this->input->get('Itemid', 0, 'int');
		$action = $this->input->get('action', '', 'word');

		if ($action == 'delete')
		{
			$this->favoriteRemove();

			return;
		}

		$model = $this->getModel('names');
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
					'url' => JRoute::_('index.php?option=com_kinoarhiv&view=' . $view . '&task=names.favorite&action=delete&Itemid=' . $itemid . '&id=' . $id, false),
					'text' => JText::_('COM_KA_REMOVEFROM_FAVORITE')
				)
			);
		}
	}

	/**
	 * Removes person(s) from favorites list.
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
		$view = $this->input->get('view', 'names', 'cmd');
		$itemid = $this->input->get('Itemid', 0, 'int');
		$model = $this->getModel('names');
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
					'url' => JRoute::_('index.php?option=com_kinoarhiv&view=' . $view . '&task=names.favorite&action=add&Itemid=' . $itemid . '&id=' . $id, false),
					'text' => JText::_('COM_KA_ADDTO_FAVORITE')
				)
			);
		}
	}
}
