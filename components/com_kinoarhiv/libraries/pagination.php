<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
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
		if ($this->app->isAdmin())
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

	/**
	 * Create the html for a list footer
	 *
	 * @param   array  $list  Pagination list data structure.
	 *
	 * @return  string  HTML for a list start, previous, next,end
	 *
	 * @since   3.0
	 */
	protected function _list_render($list)
	{
		// Reverse output rendering for right-to-left display.
		$html = '<ul class="uk-pagination">';
		$html .= '<li class="pagination-start">' . $list['start']['data'] . '</li>';
		$html .= '<li class="pagination-prev">' . $list['previous']['data'] . '</li>';

		foreach ($list['pages'] as $page)
		{
			$html .= '<li>' . $page['data'] . '</li>';
		}

		$html .= '<li class="pagination-next">' . $list['next']['data'] . '</li>';
		$html .= '<li class="pagination-end">' . $list['end']['data'] . '</li>';
		$html .= '</ul>';

		return $html;
	}

	/**
	 * Method to create an active pagination link to the item
	 *
	 * @param   JPaginationObject  $item  The object with which to make an active link.
	 *
	 * @return  string  HTML link
	 *
	 * @since   3.0
	 */
	protected function _item_active(JPaginationObject $item)
	{
		$title = '';
		$class = '';

		if (!is_numeric($item->text))
		{
			JHtml::_('bootstrap.tooltip');
			$title = ' title="' . $item->text . '"';
			$class = 'hasTooltip ';
		}

		if ($this->app->isAdmin())
		{
			return '<a' . $title . ' href="#" onclick="document.adminForm.' . $this->prefix
			. 'limitstart.value=' . ($item->base > 0 ? $item->base : '0') . '; Joomla.submitform();return false;">' . $item->text . '</a>';
		}
		else
		{
			return '<a' . $title . ' href="' . $item->link . '" class="' . $class . 'pagenav">' . $item->text . '</a>';
		}
	}
}
