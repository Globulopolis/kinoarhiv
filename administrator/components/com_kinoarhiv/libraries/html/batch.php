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
 * Batch filters class
 *
 * @since  3.0
 */
abstract class KAHtmlBatch
{
	/**
	 * Display a batch widget for the country selector.
	 *
	 * @return  string  The necessary HTML for the widget.
	 */
	public static function country()
	{
		JHtml::_('bootstrap.tooltip', '.modalTooltip', array('container' => '.modal-body'));
		JHtml::_('formbehavior.chosen', '.modal-batch select');

		// Create the batch selector to change the country on a selection list.
		return
			'<label id="batch-country-lbl" for="batch-country-id" class="modalTooltip"'
			. ' title="' . JHtml::tooltipText('JLIB_HTML_BATCH_COUNTRY_LABEL', 'JLIB_HTML_BATCH_LABEL_DESC') . '">'
			. JText::_('JLIB_HTML_BATCH_COUNTRY_LABEL')
			. '</label>'
			. '<select name="batch[country_id]" class="inputbox span11" id="batch-country-id">'
			. '<option value="">' . JText::_('JLIB_HTML_BATCH_NOCHANGE') . '</option>'
			. JHtml::_('select.options', JHtml::_('kahtml.content.country'), 'value', 'text')
			. '</select>';
	}

	/**
	 * Display a batch widget for the mediatype selector.
	 *
	 * @return  string  The necessary HTML for the widget.
	 */
	public static function mediatype()
	{
		JHtml::_('bootstrap.tooltip', '.modalTooltip', array('container' => '.modal-body'));
		JHtml::_('formbehavior.chosen', '.modal-batch select');

		$mediatypes = array();

		for ($i = 0, $n = 20; $i < $n; $i++)
		{
			$mediatypes[] = array('value' => $i, 'text' => JText::_('COM_KA_RELEASES_MEDIATYPE_' . $i));
		}

		// Create the batch selector to change the media type on a selection list.
		return
			'<label id="batch-mediatype-lbl" for="batch-mediatype-id" class="modalTooltip"'
			. ' title="' . JHtml::tooltipText('JLIB_HTML_BATCH_MEDIATYPE_LABEL', 'JLIB_HTML_BATCH_LABEL_DESC') . '">'
			. JText::_('JLIB_HTML_BATCH_MEDIATYPE_LABEL')
			. '</label>'
			. '<select name="batch[mediatype_id]" class="inputbox span10" id="batch-mediatype-id">'
			. '<option value="">' . JText::_('JLIB_HTML_BATCH_NOCHANGE') . '</option>'
			. JHtml::_('select.options', $mediatypes, 'value', 'text')
			. '</select>';
	}

	/**
	 * Display a batch widget for the vendor selector.
	 *
	 * @return  string  The necessary HTML for the widget.
	 */
	public static function vendor()
	{
		JHtml::_('bootstrap.tooltip', '.modalTooltip', array('container' => '.modal-body'));
		JHtml::_('formbehavior.chosen', '.modal-batch select');

		// Create the batch selector to change the vendor on a selection list.
		return
			'<label id="batch-vendor-lbl" for="batch-vendor-id" class="modalTooltip"'
			. ' title="' . JHtml::tooltipText('JLIB_HTML_BATCH_VENDOR_LABEL', 'JLIB_HTML_BATCH_LABEL_DESC') . '">'
			. JText::_('JLIB_HTML_BATCH_VENDOR_LABEL')
			. '</label>'
			. '<select name="batch[vendor_id]" class="inputbox span11" id="batch-vendor-id">'
			. '<option value="">' . JText::_('JLIB_HTML_BATCH_NOCHANGE') . '</option>'
			. JHtml::_('select.options', JHtml::_('kahtml.content.vendor'), 'value', 'text')
			. '</select>';
	}
}
