<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('_JEXEC') or die;

/**
 * Kinoarhiv API class.
 *
 * @since  3.0
 */
class KinoarhivControllerApi extends JControllerLegacy
{
	/**
	 * Get data from DB
	 *
	 * @return  void
	 *
	 * @since  3.0
	 */
	public function read()
	{
		JFactory::getDocument()->setMimeEncoding('application/json', false);

		$model = $this->getModel('api');
		$result = $model->getData();

		echo json_encode($result);
	}
}
