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

/**
 * Movies controller class
 *
 * @since  3.1
 */
class KinoarhivControllerMovies extends JControllerLegacy
{
	/**
	 * Mark movie, person as favorite
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   3.0
	 */
	public function favorite()
	{
		if (JFactory::getUser()->guest)
		{
			header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden', true, 403);
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$model = $this->getModel('movies');
		$result = $model->favorite();

		echo json_encode($result);
	}

	/**
	 * Mark movie as watched
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   3.0
	 */
	public function watched()
	{
		if (JFactory::getUser()->guest)
		{
			header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden', true, 403);
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$model = $this->getModel('movies');
		$result = $model->watched();

		echo json_encode($result);
	}

	/**
	 * Method to process user votes
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   3.0
	 */
	public function vote()
	{
		if (JFactory::getUser()->guest)
		{
			header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden', true, 403);
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$model = $this->getModel('movie');
		$result = $model->voted();

		echo json_encode($result);
	}
}
