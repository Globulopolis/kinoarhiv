<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *  
 * @copyright   Copyright (C) 2017 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

if (!JFactory::getUser()->authorise('core.manage', 'com_kinoarhiv'))
{
	throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
}

@ini_set('zend.ze1_compatibility_mode', 'Off');

JLoader::register('KAComponentHelperBackend', JPath::clean(dirname(__FILE__) . '/helpers/component.php'));

KAComponentHelperBackend::setHeadTags();

$controller = JControllerLegacy::getInstance('Kinoarhiv');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
