<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('_JEXEC') or die;
@ini_set('zend.ze1_compatibility_mode', 'Off');

$params = JComponentHelper::getParams('com_kinoarhiv');
JLoader::register('KAComponentHelper', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'component.php');

if ($params->get('offline') == 1)
{
	$document = JFactory::getDocument();
	$user = JFactory::getUser();

	$doc_params = array(
		'template'  => 'system',
		'file'      => 'offline.php',
		'directory' => JPATH_THEMES,
		'params'    => array()
	);

	if ($user->get('isRoot'))
	{
		echo KAComponentHelper::showMsg(JText::_('COM_KA_OFFLINE_MESSAGE'), array('icon' => 'alert', 'type' => 'error')) . "<br />";
	}
	else
	{
		$document->parse($doc_params);
		header('HTTP/1.1 503 Service Unavailable');

		echo $document->render(false, $doc_params);
		jexit();
	}
}

KAComponentHelper::setHeadTags();

$controller = JControllerLegacy::getInstance('Kinoarhiv');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
