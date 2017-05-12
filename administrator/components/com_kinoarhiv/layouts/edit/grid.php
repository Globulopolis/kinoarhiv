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

JHtml::_('jquery.framework');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery-ui.min.js');
JHtml::_('stylesheet', 'media/com_kinoarhiv/css/ui.jqgrid.min.css');
JHtml::_('stylesheet', 'media/com_kinoarhiv/css/ui.multiselect.css');
JHtml::_('script', 'media/com_kinoarhiv/js/jqgrid/plugins/ui.multiselect.min.js');
JHtml::_('script', 'media/com_kinoarhiv/js/jqgrid/jquery.jqgrid.min.js');
KAComponentHelper::getScriptLanguage('grid.locale-', 'media/com_kinoarhiv/js/i18n/jqgrid');

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

$id       = str_replace('.', '', uniqid(rand(), true));
$grid_id  = 'grid_' . $id;
$pager_id = 'grid_pager_' . $id;

if (array_key_exists('id', $data) && !empty($data['id']))
{
	$grid_id = $data['id'];
	$pager_id = 'pager_' . $data['id'];
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

$attr[] = array_key_exists('width', $data) ? ' data-width="' . $data['width'] . '"' : ' data-width="800"';
$attr[] = array_key_exists('height', $data) ? ' data-height="' . $data['height'] . '"' : ' data-height="200"';
$attr[] = array_key_exists('rows', $data) ? ' data-rows="' . $data['rows'] . '"' : ' data-rows="25"';
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
?>
<table id="<?php echo $grid_id; ?>" class="jqgrid" data-url="<?php echo $url; ?>" <?php echo implode('', $attr); ?>></table>
<div id="<?php echo $pager_id; ?>"></div>
