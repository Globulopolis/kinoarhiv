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

if ($this->award_type == 0)
{
	$colNames = array(
		'"' . JText::_('COM_KA_FIELD_AW_LABEL') . '"',
		'"' . JText::_('COM_KA_FIELD_AW_ID') . '"',
		'"' . JText::_('COM_KA_FIELD_MOVIE_LABEL') . '"',
		'"' . JText::_('COM_KA_FIELD_MOVIE_ID') . '"'
	);
	$colModel = array('item_title' => 'movie', 'item_id' => 'movie_id');
}
elseif ($this->award_type == 1)
{
	$colNames = array(
		'"' . JText::_('COM_KA_FIELD_AW_LABEL') . '"',
		'"' . JText::_('COM_KA_FIELD_AW_ID') . '"',
		'"' . JText::_('COM_KA_FIELD_NAME') . '"',
		'"' . JText::_('COM_KA_FIELD_NAME_ID') . '"'
	);
	$colModel = array('item_title' => 'title', 'item_id' => 'name_id');
}

$uid_hash = md5(crc32($this->user->get('id')) . md5($this->task)) . crc32($this->award_type);
?>
<script src="<?php echo JUri::base(); ?>components/com_kinoarhiv/assets/js/ui.multiselect.js" type="text/javascript"></script>
<script src="<?php echo JUri::base(); ?>components/com_kinoarhiv/assets/js/jquery.jqGrid.min.js" type="text/javascript"></script>
<?php KAComponentHelper::getScriptLanguage('grid.locale-', false, 'grid', false); ?>
<script src="<?php echo JUri::base(); ?>components/com_kinoarhiv/assets/js/jquery.searchFilter.min.js" type="text/javascript"></script>
<script src="<?php echo JUri::base(); ?>components/com_kinoarhiv/assets/js/grid.setcolumns.js" type="text/javascript"></script>
<script src="<?php echo JUri::root(); ?>components/com_kinoarhiv/assets/js/cookie.min.js" type="text/javascript"></script>
<script type="text/javascript">
	jQuery(document).ready(function ($) {
		var col_sort = '',
			list = $('#list');

		if (typeof $.cookie('<?php echo $uid_hash; ?>') == 'undefined') {
			$.cookie('<?php echo $uid_hash; ?>', '<?php echo $colModel['item_title']; ?>.3.asc', {expires: 365});
			col_sort = '<?php echo $colModel['item_title']; ?>.3.asc'.split('.');
		} else {
			col_sort = $.cookie('<?php echo $uid_hash; ?>').split('.');
		}

		list.jqGrid({
			url: 'index.php?option=com_kinoarhiv&controller=relations&task=awards&action=getList&format=json<?php echo $this->id != 0 ? '&id=' . $this->id : ''; ?>&award_type=<?php echo $this->award_type; ?><?php echo $this->movie_id != 0 ? '&mid=' . $this->movie_id : ''; ?>',
			datatype: 'json',
			loadui: 'block',
			height: Math.round($(window).height() - ($('.container-main').offset().top * 1.8)),
			shrinkToFit: true,
			width: $('#j-main-container').innerWidth(),
			colNames: [<?php echo implode(', ', $colNames); ?>, 'ID'],
			colModel: [
				{
					name: 'award',
					index: 'award',
					width: 300,
					searchrules: {required: true},
					sorttype: "text",
					searchoptions: {sopt: ['eq', 'bw', 'ew', 'cn']}
				},
				{
					name: 'award_id',
					index: 'award_id',
					width: 70,
					searchrules: {required: true},
					sorttype: "int",
					searchoptions: {sopt: ['eq', 'le', 'ge', 'in']}
				},
				{
					name: '<?php echo $colModel['item_title']; ?>',
					index: '<?php echo $colModel['item_title']; ?>',
					width: 350,
					sorttype: "text",
					searchrules: {required: true},
					searchoptions: {sopt: ['eq', 'bw', 'ew', 'cn']}
				},
				{
					name: '<?php echo $colModel['item_id']; ?>',
					index: '<?php echo $colModel['item_id']; ?>',
					width: 70,
					sorttype: "int",
					searchrules: {required: true},
					searchoptions: {sopt: ['eq', 'le', 'ge', 'in']}
				},
				{name: 'row_id', index: 'id', width: 70, search: false}
			],
			multiselect: true,
			caption: '',
			rowNum: 25,
			rowList: [10, 25, 30, 50, 100],
			pager: '#pager',
			sortname: col_sort[0],
			sortorder: col_sort[2],
			viewrecords: true,
			onSortCol: function (i, col, ord) {
				$.cookie('<?php echo $uid_hash; ?>', i + '.' + col + '.' + ord, {expires: 365});
			}
		});
		list.jqGrid('navGrid', '#pager', {
			edit: false,
			add: false,
			del: false,
			searchtext: '<?php echo JText::_('JSEARCH_FILTER'); ?>'
		}, {}, {}, {}, {
			width: 780, overlay: 60,
			left: Math.round((($(document).width() / 2) - ($(document).width() / 3))),
			top: Math.round((($(document).height() / 2) - ($(document).height() / 4))),
			closeAfterSearch: true,
			searchOnEnter: true,
			closeOnEscape: true
		});
		list.jqGrid('gridResize', {});
	});
</script>
<table id="list"></table>
<div id="pager"></div>
