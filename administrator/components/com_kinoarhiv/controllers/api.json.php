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
 * @since  3.1
 */
class KinoarhivControllerApi extends JControllerLegacy
{
	/**
	 * Get data from DB
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 * @since   3.1
	 */
	public function data()
	{
		if ($this->checkAccess() === false)
		{
			throw new Exception('Access denied', 403);
		}

		$this->addModelPath(JPath::clean(JPATH_ROOT . '/components/com_kinoarhiv/models'));

		$model_config = array(
			'item_state' => array(1, 0)
		);

		$model   = $this->getModel('api', '', $model_config);
		$content = $this->input->get('content', '', 'word');
		$method  = 'get' . ucfirst($content);

		if (method_exists($model, $method))
		{
			$result = $model->$method();
		}
		else
		{
			throw new Exception('Error', 500);
		}

		echo json_encode($result);
	}

	/**
	 * Check if user has access to API.
	 *
	 * @return  boolean
	 *
	 * @since   3.1
	 */
	private function checkAccess()
	{
		if (!KAComponentHelper::checkToken() && !KAComponentHelper::checkToken('get'))
		{
			return false;
		}

		return true;
	}
}