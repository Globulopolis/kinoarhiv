<?php defined('_JEXEC') or die;

/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */
class KinoarhivViewSearch extends JViewLegacy
{
	protected $items;
	protected $params;

	public function display($tpl = null)
	{
		$app = JFactory::getApplication();

		$items = $this->get('Items');
		$activeFilters = $this->get('ActiveFilters');
		$this->home_itemid = $this->get('HomeItemid');

		if (count($errors = $this->get('Errors'))) {
			GlobalHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		$params = JComponentHelper::getParams('com_kinoarhiv');

		$this->itemid = $app->input->get('Itemid', 0, 'int');
		$this->items = &$items;
		$this->params = &$params;
		$this->activeFilters = &$activeFilters;

		parent::display($tpl);
	}
}
