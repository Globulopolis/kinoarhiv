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

jimport('components.com_kinoarhiv.helpers.component', JPATH_ROOT);

/**
 * Component helper class
 *
 * @since  3.0
 */
class KAComponentHelperBackend
{
	/**
	 * Include some necessary JS into the HEAD of the document. Don't include if document format is not a html.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function setHeadTags()
	{
		$document = JFactory::getDocument();
		$params   = JComponentHelper::getParams('com_kinoarhiv');

		// Return nothing because JHtml::script doesn't work for JDocumentRaw
		if ($document->getType() != 'html')
		{
			return;
		}

		JHtml::_('stylesheet', 'media/com_kinoarhiv/css/component/plugins_backend.min.css');
		JHtml::_('stylesheet', 'media/com_kinoarhiv/css/component/styles_backend.min.css');
		JHtml::_('stylesheet', 'media/com_kinoarhiv/css/aurora.min.css');

		JHtml::_('jquery.framework');
		JHtml::_('script', 'media/com_kinoarhiv/js/aurora.min.js');
		JHtml::_('script', 'media/com_kinoarhiv/js/core.min.js');
		JHtml::_('script', 'media/com_kinoarhiv/js/backend.min.js');

		// Add some variables into the global scope
		$jsVars = array(
			'api_root' => JUri::base(), // We need to request API controller from backend not from frontend.
			'img_root' => JUri::root(),
			'uri_root' => JUri::root(),
			'uri_base' => JUri::base(),
			'params' => array(
				'ka_theme' => $params->get('ka_theme')
			),
			'language' => array(
				'tag'                          => JFactory::getLanguage()->getTag(),
				'JGLOBAL_SELECT_AN_OPTION'     => JText::_('JGLOBAL_SELECT_AN_OPTION', true), // Default placeholder, if not set for Select2,
				'COM_KA_CLOSE'                 => JText::_('COM_KA_CLOSE', true),
				'JERROR_AN_ERROR_HAS_OCCURRED' => JText::_('JERROR_AN_ERROR_HAS_OCCURRED', true),
				'COM_KA_FILE_UPLOAD_ERROR'     => JText::_('COM_KA_FILE_UPLOAD_ERROR', true),
				'COM_KA_FILES_UPLOAD_SUCCESS'  => JText::_('COM_KA_FILES_UPLOAD_SUCCESS', true),
				'COM_KA_REQUIRED'              => JText::_('COM_KA_REQUIRED', true),
				'JCLEAR'                       => JText::_('JCLEAR', true),
				'COM_KA_READ_MORE'             => JText::_('COM_KA_READ_MORE', true),
				'COM_KA_READ_LESS'             => JText::_('COM_KA_READ_LESS', true),
				'COM_KA_DELETE_SELECTED'       => JText::_('COM_KA_DELETE_SELECTED', true),
				'COM_KA_NEWWINDOW_BLOCKED_A'   => JText::_('COM_KA_NEWWINDOW_BLOCKED_A'),
				'COM_KA_NEWWINDOW_BLOCKED_B'   => JText::_('COM_KA_NEWWINDOW_BLOCKED_B'),
			)
		);
		$document->addScriptDeclaration('var KA_vars = ' . json_encode($jsVars) . ';');
	}

	/**
	 * Load mediamanager assets
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public static function loadMediamanagerAssets()
	{
		JHtml::_('jquery.framework');
		JHtml::_('stylesheet', 'media/com_kinoarhiv/plupload/jquery.plupload.queue/css/jquery.plupload.queue.css');
		JHtml::_('script', 'media/com_kinoarhiv/plupload/plupload.full.min.js');
		KAComponentHelper::getScriptLanguage('', 'media/com_kinoarhiv/plupload/i18n');
		JHtml::_('script', 'media/com_kinoarhiv/plupload/jquery.plupload.queue/jquery.plupload.queue.min.js');
	}
}
