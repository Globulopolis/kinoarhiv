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
 * Movies controller class
 *
 * @since  3.1
 */
class KinoarhivControllerNames extends JControllerLegacy
{
	/**
	 * Removes person(s) from favorites list.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function favoriteRemove()
	{
		if (JFactory::getUser()->guest)
		{
			$this->setRedirect('index.php?option=com_kinoarhiv', JText::_('JERROR_ALERTNOAUTHOR'), 'error');

			return;
		}

		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$ids = $this->input->get('ids', array(), 'array');

		if (!is_array($ids) || count($ids) < 1)
		{
			$this->setRedirect(JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=favorite'), JText::_('ERROR'), 'error');

			return;
		}

		// Encoded value. Default 'view=profile'
		$return = $this->input->getBase64('return', 'dmlldz1wcm9maWxl');
		$redirUrl = JRoute::_('index.php?option=com_kinoarhiv&' . base64_decode($return), false);
		$view = $this->input->get('view', 'names', 'cmd');
		$model = $this->getModel($view);
		$result = $model->favorite();

		$this->setMessage($result['message'], $result['success'] ? 'message' : 'error');

		$this->setRedirect($redirUrl);
	}
}
