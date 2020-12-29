<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2018 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
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
	 * @since   3.1
	 */
	public function data()
	{
		if ($this->checkAccess() === false)
		{
			header('HTTP/1.0 403 Forbidden');
			echo json_encode(array('success' => false, 'message' => '403 Forbidden'));

			return;
		}

		$this->addModelPath(JPATH_ROOT . '/components/com_kinoarhiv/models');

		$modelConfig = array(
			'item_state' => array(1, 0)
		);

		$model   = $this->getModel('API', '', $modelConfig);
		$content = $this->input->get('content', '', 'word');
		$method  = 'get' . ucfirst($content);

		// Method getName() is reserved for internal Joomla platform use.
		if (method_exists($model, $method) && $method !== 'getName')
		{
			$result = $model->$method();

			if (!$result)
			{
				echo json_encode(array('success' => false, 'message' => JText::_('JERROR_AN_ERROR_HAS_OCCURRED')));

				return;
			}
		}
		else
		{
			header('HTTP/1.0 500 Server error', true, 500);
			echo json_encode(array('success' => false, 'message' => 'Method ' . $method . '() not found in class KinoarhivModelAPI'));

			return;
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
		if (!KAComponentHelper::checkToken('post') && !KAComponentHelper::checkToken('get'))
		{
			return false;
		}

		return true;
	}
}
