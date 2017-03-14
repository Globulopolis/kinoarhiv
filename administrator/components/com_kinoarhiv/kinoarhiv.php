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

jimport('administrator.components.com_kinoarhiv.helpers.component', JPATH_ROOT);

KAComponentHelperBackend::setHeadTags();
JHtml::_('behavior.tabstate');

$controller = JControllerLegacy::getInstance('Kinoarhiv');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
