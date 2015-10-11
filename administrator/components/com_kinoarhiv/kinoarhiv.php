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

if (!JFactory::getUser()->authorise('core.manage', 'com_kinoarhiv'))
{
	throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
}

@ini_set('zend.ze1_compatibility_mode', 'Off');

JLoader::register('KAComponentHelper', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'component.php');
KAComponentHelper::setHeadTags();

require_once(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'controller.php');
$input = JFactory::getApplication()->input;

if ($controller = $input->get('controller', null, 'word'))
{
	$path = JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . $controller . '.php';

	if (file_exists($path))
	{
		require_once $path;
	}
	else
	{
		$controller = '';
	}
}

$classname = 'KinoarhivController' . $controller;
$controller = new $classname;
$controller->execute($input->get('task', 'display', 'CMD'));
$controller->redirect();
