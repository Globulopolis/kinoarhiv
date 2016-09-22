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
 * Search View class
 *
 * @since  3.0
 */
class KinoarhivViewSearch extends JViewLegacy
{
	protected $form;

	protected $params;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 *
	 * @since  3.0
	 */
	public function display($tpl = null)
	{
		$app = JFactory::getApplication();

		if ($app->input->get('task', '', 'cmd') == 'search' && KAComponentHelper::checkToken() === true)
		{
			return false;
		}

		$this->form = $this->get('Form');
		$this->itemid = $app->input->get('Itemid', 0, 'int');
		$this->home_itemid = $this->get('HomeItemid');
		$this->params = JComponentHelper::getParams('com_kinoarhiv');

		if (count($errors = $this->get('Errors')))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		parent::display($tpl);
	}
}
