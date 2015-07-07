<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Class GlobalHelper
 *
 * @since  3.0
 */
class GlobalHelper
{
	/**
	 * Include some necessary JS into the HEAD of the document. Don't include if document format is not a html.
	 *
	 * @return  void
	 */
	public static function setHeadTags()
	{
		$document = JFactory::getDocument();
		$params = JComponentHelper::getParams('com_kinoarhiv');

		// Return nothing because JHtml::script doesn't work for JDocumentRaw
		if ($document->getType() != 'html')
		{
			return;
		}

		$document->addHeadLink(JURI::base() . 'components/com_kinoarhiv/assets/css/style.css', 'stylesheet', 'rel', array('type' => 'text/css'));
		$document->addHeadLink(JURI::base() . 'components/com_kinoarhiv/assets/css/plugins.css', 'stylesheet', 'rel', array('type' => 'text/css'));
		$document->addHeadLink(JURI::root() . 'components/com_kinoarhiv/assets/themes/ui/' . $params->get('ui_theme') . '/jquery-ui.css', 'stylesheet', 'rel', array('type' => 'text/css'));
		JHtml::_('jquery.framework');
		JHtml::_('script', JURI::root() . 'components/com_kinoarhiv/assets/js/jquery-ui.min.js');
		JHtml::_('script', JURI::root() . 'components/com_kinoarhiv/assets/js/ui.aurora.min.js');
		JHtml::_('script', JURI::base() . 'components/com_kinoarhiv/assets/js/utils.js');

		JText::script('COM_KA_CLOSE', true);
	}

	public static function getRemoteData($url, $headers = null, $timeout = 30, $transport = 'curl')
	{
		$options = new Registry;

		$http = JHttpFactory::getHttp($options, $transport);
		$response = $http->get($url, $headers, $timeout);

		return $response;
	}

	/**
	 * Just proxy for KALanguage::getScriptLanguage()
	 *
	 * @param   string  $file         Part of the filename w/o language tag and extension
	 * @param   string  $jhtml        Use JHTML::script() to load
	 * @param   string  $script_type  Type of the script(folder name in assets/js/i8n/)
	 * @param   bool    $frontend     Load language file from the frontend if set to true
	 *
	 * @return void
	 */
	public static function getScriptLanguage($file, $jhtml, $script_type, $frontend)
	{
		JLoader::register('KALanguage', JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'language.php');

		KALanguage::getScriptLanguage($file, $jhtml, $script_type, $frontend);
	}

	/**
	 * Method to get an errors from $errors and enqueue or directly display them.
	 *
	 * @param   mixed   $errors  An Exception object or array.
	 * @param   string  $format  Document type format.
	 * @param   int     $count   Number of errors to process.
	 *
	 * @return  string
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
					$_errors[] = $errors[$i];
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
