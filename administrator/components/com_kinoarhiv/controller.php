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
 * Default controller class
 *
 * @since  3.0
 */
class KinoarhivController extends JControllerLegacy
{
	protected $default_view = 'controlpanel';

	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JControllerLegacy    This object to support chaining.
	 *
	 * @since   3.0
	 */
	public function display($cachable = false, $urlparams = array())
	{
		JHtml::addIncludePath(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR);

		return parent::display();
	}

	/**
	 * Method to save the submitted ordering values for records.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function saveOrder()
	{
		if (!KAComponentHelper::checkToken('post'))
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JINVALID_TOKEN')));

			return;
		}

		$data = $this->input->post->get('ord', array(), 'array');

		// Sorting required at least two items in list
		if (count($data) < 2)
		{
			echo json_encode(array('success' => false, 'message' => JText::_('COM_KA_SAVE_ORDER_AT_LEAST_TWO')));

			return;
		}

		$model = $this->getModel($this->input->getWord('items', ''));
		$result = $model->saveOrder($data);

		if (!$result)
		{
			echo json_encode(array('success' => false, 'message' => JText::_('COM_KA_SAVE_ORDER_ERROR')));

			return;
		}

		echo json_encode(array('success' => true, 'message' => JText::_('COM_KA_SAVED')));
		JFactory::getApplication()->close();
	}
}
