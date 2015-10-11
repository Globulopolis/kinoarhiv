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
 * @since  1.5
 */
class KinoarhivViewSearch extends JViewLegacy
{
	protected $items;

	protected $params;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function display($tpl = null)
	{
		$app = JFactory::getApplication();

		$items = $this->get('Items');
		$activeFilters = $this->get('ActiveFilters');
		$this->home_itemid = $this->get('HomeItemid');

		if (count($errors = $this->get('Errors')))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		$params = JComponentHelper::getParams('com_kinoarhiv');

		$this->itemid = $app->input->get('Itemid', 0, 'int');
		$this->items =&$items;
		$this->params = $params;
		$this->activeFilters = $activeFilters;

		parent::display($tpl);
	}
}
