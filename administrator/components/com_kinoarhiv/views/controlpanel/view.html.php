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

class KinoarhivViewControlPanel extends JViewLegacy
{
	protected $component;

	public function display($tpl = null)
	{
		$this->component = JInstaller::parseXMLInstallFile(JPath::clean(JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_kinoarhiv' . DIRECTORY_SEPARATOR . 'kinoarhiv.xml'));

		JToolbarHelper::title(JText::_('COM_KINOARHIV_CP'), 'play');

		parent::display($tpl);
	}
}
