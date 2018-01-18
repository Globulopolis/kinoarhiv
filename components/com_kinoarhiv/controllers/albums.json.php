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
		$this->addModelPath(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'music' . DIRECTORY_SEPARATOR);

		parent::__construct($config);
	}

	/**
	 * Mark music album as favorite
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

		$model = $this->getModel('albums');
		$result = $model->favorite();

		echo json_encode($result);
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
