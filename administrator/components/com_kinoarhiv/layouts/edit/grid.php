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

JHtml::_('jquery.framework');
JHtml::_('stylesheet', 'media/com_kinoarhiv/jqueryui/' . JComponentHelper::getParams('com_kinoarhiv')->get('ui_theme') . '/jquery-ui.min.css');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery-ui.min.js');
JHtml::_('stylesheet', 'media/com_kinoarhiv/css/ui.jqgrid.min.css');
JHtml::_('stylesheet', 'media/com_kinoarhiv/css/ui.multiselect.css');
JHtml::_('script', 'media/com_kinoarhiv/js/jqgrid/plugins/ui.multiselect.min.js');
JHtml::_('script', 'media/com_kinoarhiv/js/jqgrid/jquery.jqgrid.min.js');
KAComponentHelper::getScriptLanguage('grid.locale-', 'media/com_kinoarhiv/js/i18n/jqgrid');

/** @var array $displayData */
$data = $displayData;
$attr = array();

if (isset($data['url']) && !empty($data['url']))
{
	$url = $data['url'];
}
else
{
	return false;
}

$id      = str_replace('.', '', uniqid(rand(), true));
$gridID  = 'grid_' . $id;
$pagerID = 'grid_pager_' . $id;
$class   = (array_key_exists('class', $data) && !empty($data['class'])) ? $data['class'] : '';

if (array_key_exists('id', $data) && !empty($data['id']))
{
	$gridID  = $data['id'];
	$pagerID = 'pager_' . $data['id'];
}

if (array_key_exists('colModel', $data) && !empty($data['colModel']))
{
	$cn = array();
	$cm = array();

	foreach ($data['colModel'] as $colName => $colModel)
	{
		$cn[] = JText::_($colName, true);
		$cm[] = $colModel;
	}

	$attr[] = " data-colnames='" . json_encode($cn) . "'";
	$attr[] = " data-colmodel='" . json_encode($cm) . "'";
}

if (array_key_exists('grouping', $data) && !empty($data['grouping']))
{
	$attr[] = ' data-grouping="true"';
	$attr[] = " data-grouping-view='" . json_encode($data['groupingview']) . "'";
}

$attr[] = array_key_exists('width', $data) ? ' data-width="' . $data['width'] . '"' : ' data-width="800"';
$attr[] = array_key_exists('height', $data) ? ' data-height="' . $data['height'] . '"' : ' data-height="200"';
$attr[] = array_key_exists('rownum', $data) ? ' data-rownum="' . $data['rownum'] . '"' : ' data-rownum="25"';
$attr[] = array_key_exists('order', $data) ? ' data-order="' . $data['order'] . '"' : '';
$attr[] = array_key_exists('orderby', $data) ? ' data-orderby="' . $data['orderby'] . '"' : '';
$attr[] = array_key_exists('toppager', $data) ? ' data-toppager="' . $data['toppager'] . '"' : ' data-toppager="false"';
$attr[] = array_key_exists('pager', $data) ? ' data-pager="' . $data['pager'] . '"' : ' data-pager="true"';
$attr[] = array_key_exists('idprefix', $data) ? ' data-idprefix="' . $data['idprefix'] . '"' : '';
$attr[] = array_key_exists('rowlist', $data) ? ' data-rowlist="' . json_encode($data['rowlist']) . '"' : ' data-rowlist="[]"';
$attr[] = array_key_exists('navgrid', $data) ? " data-navgrid_setup='" . json_encode($data['navgrid']) . "'" : '';
$attr[] = array_key_exists('add_url', $data) ? " data-add_url='" . $data['add_url'] . "'" : '';
$attr[] = array_key_exists('edit_url', $data) ? " data-edit_url='" . $data['edit_url'] . "'" : '';
$attr[] = array_key_exists('del_url', $data) ? " data-del_url='" . $data['del_url'] . "'" : '';
$attr[] = array_key_exists('pgbuttons', $data) ? ' data-pgbuttons="' . (bool) $data['pgbuttons'] . '"' : ' data-pgbuttons="true"';
$attr[] = array_key_exists('pginput', $data) ? ' data-pginput="' . (bool) $data['pginput'] . '"' : ' data-pginput="true"';
$attr[] = array_key_exists('actionsNavOptions', $data) ? " data-actnavgrid_setup='" . json_encode($data['actionsNavOptions']) . "'" : '';
$attr[] = array_key_exists('navButtonAdd', $data) ? " data-navbuttonadd_setup='" . json_encode($data['navButtonAdd']) . "'" : '';
?>
<table id="<?php echo $gridID; ?>" class="jqgrid <?php echo $class; ?>" data-url="<?php echo $url; ?>" <?php echo implode('', $attr); ?>></table>
<div id="<?php echo $pagerID; ?>"></div>
