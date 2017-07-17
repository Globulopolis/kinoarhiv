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
 * View class for component control panel.
 *
 * @since  3.0
 */
class KinoarhivViewControlPanel extends JViewLegacy
{
	protected $component;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   3.0
	 */
	public function display($tpl = null)
	{
		$this->component = JInstaller::parseXMLInstallFile(JPath::clean(JPATH_ROOT . '/administrator/components/com_kinoarhiv/kinoarhiv.xml'));

		JToolbarHelper::title(JText::_('COM_KINOARHIV_CP'), 'play');

		parent::display($tpl);
	}
}
