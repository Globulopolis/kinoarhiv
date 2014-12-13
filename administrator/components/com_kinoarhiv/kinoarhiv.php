<?php defined('_JEXEC') or die;
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url			http://киноархив.com/
 */

if (!JFactory::getUser()->authorise('core.manage', 'com_kinoarhiv')) {
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

@ini_set('zend.ze1_compatibility_mode', 'Off');

JLoader::register('GlobalHelper', dirname(__FILE__).DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'global.php');
GlobalHelper::setHeadTags();

require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'controller.php');
$input = JFactory::getApplication()->input;

if($controller = $input->get('controller', null, 'word')) {
	$path = JPATH_COMPONENT.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.$controller.'.php';
	if (file_exists($path)) {
		require_once $path;
	} else {
		$controller = '';
	}
}

$classname = 'KinoarhivController'.$controller;
$controller = new $classname();
$controller->execute($input->get('task', 'display', 'CMD'));
$controller->redirect();
