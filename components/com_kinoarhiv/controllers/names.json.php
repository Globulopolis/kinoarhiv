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
	 * Mark person as favorite
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function favorite()
	{
		if (JFactory::getUser()->guest)
		{
			header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden', true, 403);
			jexit();
		}

		$model = $this->getModel('names');
		$result = $model->favorite();

		echo json_encode($result);
	}
}
