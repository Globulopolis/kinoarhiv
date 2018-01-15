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
 * View class to list some items.
 *
 * @since  3.0
 */
class KinoarhivViewMediamanager extends JViewLegacy
{
	protected $form;

	protected $params;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function display($tpl = null)
	{
		jimport('components.com_kinoarhiv.helpers.content', JPATH_ROOT);

		$this->params = JComponentHelper::getParams('com_kinoarhiv');
		$this->form = $this->get('Form');
		$this->path = KAContentHelper::getPath('movie', 'trailers', null, JFactory::getApplication()->input->getInt('id', 0));

		parent::display($tpl);
	}
}
