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

$uid_hash = md5(crc32($this->user->get('id')) . md5($this->task . $this->element)) . crc32($this->task);

if ($this->element == 'movies')
{
	$cookie = 'movie.3.asc';
	$cols = array(
		'colnames' => array(
			JText::_('COM_KA_FIELD_GENRE_LABEL'),
			JText::_('COM_KA_FIELD_GENRE_ID'),
			JText::_('COM_KA_FIELD_MOVIE_LABEL'),
			JText::_('COM_KA_FIELD_MOVIE_ID'),
			JText::_('JFIELD_ORDERING_LABEL')
		),
		'colmodel' => array(
			(object) array('name' => 'genre', 'index' => 'genre', 'width' => 300, 'sorttype' => 'text', 'searchoptions' => (object) array(
				'sopt' => array('eq', 'bw', 'ew', 'cn'))
			),
			(object) array('name' => 'genre_id', 'index' => 'genre_id', 'width' => 70, 'sorttype' => 'int', 'searchoptions' => (object) array(
				'sopt' => array('eq', 'le', 'ge'))
			),
			(object) array('name' => 'movie', 'index' => 'movie', 'width' => 350, 'sorttype' => 'text', 'searchoptions' => (object) array(
				'sopt' => array('eq', 'bw', 'ew', 'cn'))
			),
			(object) array('name' => 'movie_id', 'index' => 'movie_id', 'width' => 70, 'sorttype' => 'int', 'searchoptions' => (object) array(
				'sopt' => array('eq', 'le', 'ge'))
			),
			(object) array('name' => 'ordering', 'index' => 'ordering', 'width' => 70, 'align' => 'right', 'sortable' => false, 'search' => false)
		)
	);
}
elseif ($this->element == 'names')
{
	$cookie = 'name.3.asc';
	$cols = array(
		'colnames' => array(
			JText::_('COM_KA_FIELD_GENRE_LABEL'),
			JText::_('COM_KA_FIELD_GENRE_ID'),
			JText::_('COM_KA_FIELD_NAME'),
			JText::_('COM_KA_FIELD_NAME_ID')
		),
		'colmodel' => array(
			(object) array('name'   => 'genre', 'index' => 'genre', 'width' => 300, 'sorttype' => 'text',
					'searchoptions' => (object) array('sopt' => array('eq', 'bw', 'ew', 'cn'))),
			(object) array('name'   => 'genre_id', 'index' => 'genre_id', 'width' => 70, 'sorttype' => 'int',
					'searchoptions' => (object) array('sopt' => array('eq', 'le', 'ge'))),
			(object) array('name'   => 'name', 'index' => 'name', 'width' => 350, 'sorttype' => 'text',
					'searchoptions' => (object) array('sopt' => array('eq', 'bw', 'ew', 'cn'))),
			(object) array('name'   => 'name_id', 'index' => 'name_id', 'width' => 70, 'sorttype' => 'int',
					'searchoptions' => (object) array('sopt' => array('eq', 'le', 'ge')))
		)
	);
}
?>
<script src="<?php echo JUri::base(); ?>components/com_kinoarhiv/assets/js/ui.multiselect.js" type="text/javascript"></script>
<script src="<?php echo JUri::base(); ?>components/com_kinoarhiv/assets/js/jquery.jqGrid.min.js" type="text/javascript"></script>
<?php KAComponentHelper::getScriptLanguage('grid.locale-', 'media/com_kinoarhiv/js/i18n/grid/', false); ?>
<script src="<?php echo JUri::base(); ?>components/com_kinoarhiv/assets/js/jquery.searchFilter.min.js" type="text/javascript"></script>
<script src="<?php echo JUri::base(); ?>components/com_kinoarhiv/assets/js/grid.setcolumns.js" type="text/javascript"></script>
<script type="text/javascript">
	jQuery(document).ready(function ($) {
		var col_sort = '',
			list = $('#list');

		if (typeof Cookies.get('<?php echo $uid_hash; ?>') == 'undefined') {
			Cookies.set('<?php echo $uid_hash; ?>', '<?php echo $cookie; ?>', {expires: 365});
			col_sort = '<?php echo $cookie; ?>'.split('.');
		} else {
			col_sort = Cookies.get('<?php echo $uid_hash; ?>').split('.');
		}

		list.jqGrid({
			<?php if ($this->element == 'movies'): ?>
			url: 'index.php?option=com_kinoarhiv&controller=relations&task=genres&action=getList&element=movies&format=json<?php echo ($this->id != 0) ? '&id=' . $this->id : ''; ?><?php echo ($this->movie_id != 0) ? '&mid=' . $this->movie_id : ''; ?>',
			<?php elseif ($this->element == 'names'): ?>
			url: 'index.php?option=com_kinoarhiv&controller=relations&task=genres&action=getList&element=names&format=json<?php echo ($this->id != 0) ? '&id=' . $this->id : ''; ?><?php echo ($this->name_id != 0) ? '&nid=' . $this->name_id : ''; ?>',
			<?php endif; ?>
			datatype: 'json',
			loadui: 'block',
			height: Math.round($(window).height() - ($('.container-main').offset().top * 1.8)),
			shrinkToFit: true,
			width: $('#system-message-container').innerWidth(),
			colNames: <?php echo json_encode($cols['colnames']); ?>,
			colModel: <?php echo json_encode($cols['colmodel']); ?>,
			multiselect: true,
			caption: '',
			rowNum: 25,
			rowList: [10, 25, 30, 50, 100],
			pager: '#pager',
			sortname: col_sort[0],
			sortorder: col_sort[2],
			viewrecords: true,
			onSortCol: function (i, col, ord) {
				Cookies.set('<?php echo $uid_hash; ?>', i + '.' + col + '.' + ord, {expires: 365});
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
		<?php if ($this->element == 'movies'): ?>
		list.jqGrid('sortableRows', {
			connectWith: '#list',
			update: function (e, ui) {
				$.post('index.php?option=com_kinoarhiv&controller=relations&task=saveOrder&param=genres&format=json', {
					'<?php echo JSession::getFormToken(); ?>': 1,
					'ids': $('#list').jqGrid('getDataIDs').join(','),
					'id': ui.item.attr('id')
				}, function (response) {
					if (response.success) {
						$('#list').trigger('reloadGrid');
					} else {
						showMsg('#system-message-container', '<?php echo JText::_('COM_KA_SAVE_ORDER_ERROR'); ?>');
					}
				}).fail(function (xhr, status, error) {
					showMsg('#system-message-container', error);
				});
			}
		});
		<?php endif; ?>
		list.jqGrid('gridResize', {});
	});
</script>
<table id="list"></table>
<div id="pager"></div>
