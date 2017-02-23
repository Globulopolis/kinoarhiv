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

/**
 * Mediamanager controller class.
 *
 * @since  3.0
 */
class KinoarhivControllerMediamanager extends JControllerLegacy
{
	/**
	 * Method to edit a data for video/subtitle/chapter.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function editTrailerFile()
	{
		$view = $this->getView('mediamanager', 'raw');
		$model = $this->getModel('mediamanagerItem');
		$view->setModel($model, true);
		$view->display('trailer_edit_fileinfo');
	}
}
