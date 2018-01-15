<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2018 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('JPATH_PLATFORM') or die;

/**
 * Pagination Class. Provides a common interface for content pagination for the Kinoarhiv component.
 *
 * @since  3.0
 */
class KAPagination extends JPagination
{
	/**
	 * @var    boolean  View all flag
	 * @since  3.0
	 */
	public $viewall = false;

	/**
	 * Creates a dropdown box for selecting how many records to show per page.
	 *
	 * @return  string  The HTML for the limit # input box.
	 *
	 * @since   3.0
	 */
	public function getLimitBox()
	{
		$limits = array();

		// Make the option list.
		for ($i = 5; $i <= 30; $i += 5)
		{
			$limits[] = JHtml::_('select.option', "$i");
		}

		$limits[] = JHtml::_('select.option', '50', JText::_('J50'));
		$limits[] = JHtml::_('select.option', '100', JText::_('J100'));

		if ($this->viewall === true)
		{
			$limits[] = JHtml::_('select.option', '0', JText::_('JALL'));
			$selected = 0;
		}
		else
		{
			$selected = $this->limit;
		}

		// Build the select list.
		if ($this->app->isClient('administrator'))
		{
			$html = JHtml::_(
				'select.genericlist',
				$limits,
				$this->prefix . 'limit',
				'class="inputbox input-mini" size="1" onchange="Joomla.submitform();"',
				'value',
				'text',
				$selected
			);
		}
		else
		{
			$html = JHtml::_(
				'select.genericlist',
				$limits,
				$this->prefix . 'limit',
				'class="inputbox input-mini" size="1" onchange="this.form.submit()"',
				'value',
				'text',
				$selected
			);
		}

		return $html;
	}
}
