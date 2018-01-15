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
class KinoarhivControllerMovies extends JControllerLegacy
{
	/**
	 * Mark movie favorite
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

		$model = $this->getModel('movies');
		$result = $model->favorite();

		echo json_encode($result);
	}

	/**
	 * Mark movie as watched
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function watched()
	{
		if (JFactory::getUser()->guest)
		{
			header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden', true, 403);
			jexit();
		}

		$model = $this->getModel('movies');
		$result = $model->watched();

		echo json_encode($result);
	}

	/**
	 * Process user votes
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function vote()
	{
		if (JFactory::getUser()->guest)
		{
			header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden', true, 403);
			jexit();
		}

		if (!KAComponentHelper::checkToken('get'))
		{
			header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden', true, 403);
			jexit();
		}

		$id     = $this->input->get('id', 0, 'int');
		$value  = $this->input->get('value', 0, 'int');
		$model  = $this->getModel('movies');
		$result = $model->vote($id, $value);

		echo json_encode($result);
	}

	/**
	 * Removes user votes
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function votesRemove()
	{
		if (JFactory::getUser()->guest)
		{
			header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden', true, 403);
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

		$model = $this->getModel('movies');
		$result = $model->votesRemove($ids);

		echo json_encode($result);
	}
}
