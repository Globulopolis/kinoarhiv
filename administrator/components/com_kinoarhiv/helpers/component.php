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
	 * @since  3.0
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

		JHtml::_('stylesheet', 'media/com_kinoarhiv/jqueryui/' . $params->get('ui_theme') . '/jquery-ui.css');
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
	 * @return void
	 *
	 * @since  3.0
	 */
	public static function loadMediamanagerAssets()
	{
		JHtml::_('jquery.framework');
		JHtml::_('stylesheet', 'media/com_kinoarhiv/plupload/jquery.plupload.queue/css/jquery.plupload.queue.css');
		JHtml::_('script', 'media/com_kinoarhiv/plupload/plupload.full.min.js');
		KAComponentHelper::getScriptLanguage('', 'media/com_kinoarhiv/plupload/i18n');
		JHtml::_('script', 'media/com_kinoarhiv/plupload/jquery.plupload.queue/jquery.plupload.queue.min.js');
	}

	/**
	 * Method to get an errors from $errors and enqueue or directly display them.
	 *
	 * @param   mixed    $errors  Exceptions object or array.
	 * @param   string   $format  Document type format.
	 * @param   integer  $count   Number of errors to process.
	 *
	 * @return  string|boolean  Return string if document type not a html.
	 *
	 * @since  3.0
	 */
	public static function renderErrors($errors, $format = 'html', $count = 3)
	{
		$app = JFactory::getApplication();
		$totalErrors = count($errors);
		$_errors = array();

		for ($i = 0; $i < $totalErrors && $i < $count; $i++)
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

	/**
	 * Method to save the access rules.
	 *
	 * @param   integer  $id         Item ID.
	 * @param   string   $assetName  The unique name for the asset.
	 * @param   string   $title      The descriptive title for the asset.
	 * @param   array    $rules      The form data.
	 *
	 * @return  mixed  lastInsertID on insert, true on update, false otherwise.
	 *
	 * @since   3.1
	 */
	public static function saveAccessRules($id, $assetName, $title = '', $rules = array())
	{
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$rules = new JAccessRules($rules);

		if (empty($id))
		{
			// Get parent ID.
			$query = $db->getQuery(true)
				->select($db->quoteName('id'))
				->from($db->quoteName('#__assets'))
				->where($db->quoteName('name') . " = 'com_kinoarhiv' AND " . $db->quoteName('parent_id') . " = 1");
			$db->setQuery($query);

			try
			{
				$parentID = $db->loadResult();
			}
			catch (RuntimeException $e)
			{
				$app->enqueueMessage($e->getMessage(), 'error');

				return false;
			}

			// Get lft, rgt values
			$query = $db->getQuery(true)
				->select('MAX(lft)+2 AS lft')
				->from($db->quoteName('#__assets'));
			$db->setQuery($query);

			try
			{
				$lft = $db->loadResult();
				$rgt = (int) $lft + 1;
			}
			catch (RuntimeException $e)
			{
				$app->enqueueMessage($e->getMessage(), 'error');

				return false;
			}

			$query = $db->getQuery(true)
				->insert($db->quoteName('#__assets'))
				->columns($db->quoteName(array_keys($db->getTableColumns('#__assets'))))
				->values("'', '" . (int) $parentID . "', '" . $lft . "', '" . $rgt . "', '2', '" . $assetName . "', "
					. "'" . $db->escape($title) . "', '" . $rules . "'"
				);
		}
		else
		{
			$query = $db->getQuery(true)
				->update($db->quoteName('#__assets'))
				->set($db->quoteName('rules') . " = '" . $rules . "'")
				->where($db->quoteName('name') . " = '" . $assetName . "'");
		}

		$db->setQuery($query);

		try
		{
			$db->execute();

			if (empty($id))
			{
				return $db->insertid();
			}
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		return true;
	}
}
