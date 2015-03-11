<?php defined('_JEXEC') or die;
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url			http://киноархив.com/
 */

@ini_set('zend.ze1_compatibility_mode', 'Off');

$params = JComponentHelper::getParams('com_kinoarhiv');
JLoader::register('GlobalHelper', dirname(__FILE__).DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'global.php');

if ($params->get('offline') == 1) {
	$document = JFactory::getDocument();
	$user = JFactory::getUser();

	$doc_params = array(
		'template' => 'system',
		'file' => 'offline.php',
		'directory' => JPATH_THEMES,
		'params' => array()
	);

	if ($user->get('isRoot')) {
		echo GlobalHelper::showMsg(JText::_('COM_KA_OFFLINE_MESSAGE'), array('icon'=>'alert', 'type'=>'error'))."<br />";
	} else {
		$document->parse($doc_params);
		header('HTTP/1.1 503 Service Unavailable');
		
		echo $document->render(false, $doc_params);
		jexit();
	}
}

GlobalHelper::setHeadTags();

$controller = JControllerLegacy::getInstance('Kinoarhiv');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
