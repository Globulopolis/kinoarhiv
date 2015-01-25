<?php defined('_JEXEC') or die;
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url			http://киноархив.com/
 */

class GlobalHelper {
	static function setHeadTags() {
		$document = JFactory::getDocument();
		$params = JComponentHelper::getParams('com_kinoarhiv');

		// Return nothing because JHtml::script doesn't work for JDocumentRaw
		if ($document->getType() != 'html') {
			return;
		}

		$document->addHeadLink(JURI::base().'components/com_kinoarhiv/assets/css/style.css', 'stylesheet', 'rel', array('type'=>'text/css'));
		$document->addHeadLink(JURI::base().'components/com_kinoarhiv/assets/css/plugins.css', 'stylesheet', 'rel', array('type'=>'text/css'));
		$document->addHeadLink(JURI::root().'components/com_kinoarhiv/assets/themes/ui/'.$params->get('ui_theme').'/jquery-ui.css', 'stylesheet', 'rel', array('type'=>'text/css'));
		JHtml::_('jquery.framework');
		JHtml::_('script', JURI::root().'components/com_kinoarhiv/assets/js/jquery-ui.min.js');
		JHtml::_('script', JURI::root().'components/com_kinoarhiv/assets/js/ui.aurora.min.js');
		JHtml::_('script', JURI::base().'components/com_kinoarhiv/assets/js/utils.js');

		JText::script('COM_KA_CLOSE', true);
	}

	public static function getRemoteData($url, $headers=null, $timeout=30, $transport='curl') {
		$options = new JRegistry;

		$http = JHttpFactory::getHttp($options, $transport);
		$response = $http->get($url, $headers, $timeout);

		return $response;
	}

	/**
	 * Just proxy for KALanguage::getScriptLanguage()
	 */
	public static function getScriptLanguage($file, $jhtml, $script_type, $frontend) {
		JLoader::register('KALanguage', JPATH_COMPONENT.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'language.php');
		$language = new KALanguage();

		return $language::getScriptLanguage($file, $jhtml, $script_type, $frontend);
	}
}
