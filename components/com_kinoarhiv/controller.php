<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2018 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

/**
 * Kinoarhiv Component Controller
 *
 * @since  3.0
 */
class KinoarhivController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached.
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  object  This object to support chaining.
	 *
	 * @since   3.0
	 */
	public function display($cachable = false, $urlparams = array())
	{
		$cachable = true;

		// Set the default view name and format from the Request.
		$vName = $this->input->getCmd('view', 'movies');
		$this->input->set('view', $vName);

		$user = JFactory::getUser();

		if ($user->get('id') || ($this->input->getMethod() == 'POST'))
		{
			$cachable = false;
		}

		$safeurlparams = array('id'       => 'INT', 'cid' => 'ARRAY', 'gid' => 'ARRAY', 'year' => 'INT', 'limit' => 'UINT', 'limitstart' => 'UINT',
								'showall' => 'INT', 'return' => 'BASE64', 'filter' => 'STRING', 'filter_order' => 'CMD', 'filter_order_Dir' => 'CMD',
								'filter-search' => 'STRING', 'print' => 'BOOLEAN', 'lang' => 'CMD', 'Itemid' => 'INT');

		parent::display($cachable, $safeurlparams);

		return $this;
	}
}
