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

class KinoarhivViewMovies extends JViewLegacy
{
	protected $form;

	public function display($tpl = null)
	{
		$this->form = $this->get('Form');
		$this->params = JComponentHelper::getParams('com_kinoarhiv');

		parent::display($tpl);
	}
}
