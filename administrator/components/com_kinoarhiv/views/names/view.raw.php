<?php defined('_JEXEC') or die;

/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */
class KinoarhivViewNames extends JViewLegacy
{
	protected $form;

	public function display($tpl = null)
	{
		$this->form = $this->get('Form');

		parent::display($tpl);
	}
}
