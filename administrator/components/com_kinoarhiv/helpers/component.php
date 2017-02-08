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

jimport('components.com_kinoarhiv.helpers.component', JPATH_ROOT);

/**
 * Component helper class
 *
 * @since  3.0
 */
class KAComponentHelperBackend extends JComponentHelper
{
	/**
	 * Include some necessary JS into the HEAD of the document. Don't include if document format is not a html.
	 *
	 * @return  void
	 *
	 * @since  3.0
	 */
	public static function setHeadTags()
	{
		$document = JFactory::getDocument();
		$params = self::getParams('com_kinoarhiv');

		// Return nothing because JHtml::script doesn't work for JDocumentRaw
		if ($document->getType() != 'html')
		{
			return;
		}

		JHtml::_('stylesheet', JUri::base() . 'components/com_kinoarhiv/assets/css/style.css');
		JHtml::_('stylesheet', JUri::base() . 'components/com_kinoarhiv/assets/css/plugins.css');
		JHtml::_('stylesheet', 'components/com_kinoarhiv/assets/themes/ui/' . $params->get('ui_theme') . '/jquery-ui.css');

		JHtml::_('jquery.framework');
		JHtml::_('script', 'media/com_kinoarhiv/js/jquery-ui.min.js');
		JHtml::_('script', 'media/com_kinoarhiv/js/ui.aurora.min.js');
		JHtml::_('script', 'media/com_kinoarhiv/js/js.cookie.min.js');
		JHtml::_('script', 'media/com_kinoarhiv/js/core.min.js');
		JHtml::_('script', 'media/com_kinoarhiv/js/backend.js');
		//JHtml::_('script', 'media/com_kinoarhiv/js/backend.min.js');

		// Add some variables into the global scope
		$js_vars = array(
			'api_root' => JUri::base(), // We need to request API controller from backend not from frontend.
			'img_root' => JUri::root(),
			'uri_root' => JUri::root(),
			'uri_base' => JUri::base(),
			'params' => array(
				'ka_theme' => $params->get('ka_theme')
			),
			'language' => array(
				'tag'                          => JFactory::getLanguage()->getTag(),
				'JGLOBAL_SELECT_AN_OPTION'     => JText::_('JGLOBAL_SELECT_AN_OPTION'), // Default placeholder, if not set for Select2,
				'COM_KA_CLOSE'                 => JText::_('COM_KA_CLOSE'),
				'JERROR_AN_ERROR_HAS_OCCURRED' => JText::_('JERROR_AN_ERROR_HAS_OCCURRED'),
				'COM_KA_FILE_UPLOAD_ERROR'     => JText::_('COM_KA_FILE_UPLOAD_ERROR'),
				'COM_KA_FILES_UPLOAD_SUCCESS'  => JText::_('COM_KA_FILES_UPLOAD_SUCCESS'),
				'COM_KA_REQUIRED'              => JText::_('COM_KA_REQUIRED'),
				'JCLEAR'                       => JText::_('JCLEAR'),
				'COM_KA_READ_MORE'             => JText::_('COM_KA_READ_MORE'),
				'COM_KA_READ_LESS'             => JText::_('COM_KA_READ_LESS'),
			)
		);
		$document->addScriptDeclaration('var KA_vars = ' . json_encode($js_vars) . ';');
	}

	/**
	 * Load mediamanager assets
	 *
	 * @return void
	 *
	 * @since  3.0
	 */
	public static function loadMediamanagerAssets()
	{
		JHtml::_('stylesheet', 'media/com_kinoarhiv/plupload/jquery.plupload.queue/css/jquery.plupload.queue.css');
		JHtml::_('script', 'media/com_kinoarhiv/plupload/plupload.full.min.js');
		KAComponentHelper::getScriptLanguage('', 'media/com_kinoarhiv/plupload/i18n');
		JHtml::_('script', 'media/com_kinoarhiv/plupload/jquery.plupload.queue/jquery.plupload.queue.min.js');
	}

	/**
	 * Method to get an errors from $errors and enqueue or directly display them.
	 *
	 * @param   mixed    $errors  An Exception object or array.
	 * @param   string   $format  Document type format.
	 * @param   integer  $count   Number of errors to process.
	 *
	 * @return  string
	 *
	 * @since  3.0
	 */
	public static function renderErrors($errors, $format = 'html', $count = 3)
	{
		$app = JFactory::getApplication();
		$_errors = array();

		for ($i = 0, $n = count($errors); $i < $n && $i < $count; $i++)
		{
			if ($errors[$i] instanceof Exception)
			{
				if ($format == 'html')
				{
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
				{
					$_errors[] = $errors[$i]->getMessage();
				}
			}
			else
			{
				if ($format == 'html')
				{
					$app->enqueueMessage($errors[$i], 'warning');
				}
				else
				{
					$_errors[] = $errors[$i]['message'];
				}
			}
		}

		if ($format != 'html')
		{
			return implode('<br />', $_errors);
		}

		return true;
	}
}
