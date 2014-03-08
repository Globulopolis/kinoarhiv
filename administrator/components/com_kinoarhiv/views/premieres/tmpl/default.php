<?php defined('_JEXEC') or die;
JHtml::_('behavior.keepalive');
?>
<script type="text/javascript" src="<?php echo JURI::root(); ?>components/com_kinoarhiv/assets/js/ui.aurora.min.js"></script>
<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/jquery-ui.custom.min.js"></script>
<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/jquery.ui.tooltip.min.js"></script>
<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/ui.multiselect.js"></script>
<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/jqGrid.min.js"></script>
<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/i18n/grid/grid.locale-<?php echo substr($this->lang->getTag(), 0, 2); ?>.js"></script>
<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/jquery.searchFilter.js"></script>
<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/grid.setcolumns.js"></script>
<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/jquery-ui-timepicker.min.js"></script>
<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/i18n/timepicker/jquery-ui-timepicker-<?php echo substr($this->lang->getTag(), 0, 2); ?>.js"></script>
<script type="text/javascript">
	function showMsg(selector, text) {
		jQuery(selector).aurora({
			text: text,
			placement: 'before',
			button: 'close',
			button_title: '[<?php echo JText::_('COM_KA_CLOSE'); ?>]'
		});
	}

	jQuery(document).ready(function($){
		$('.hasTip, .hasTooltip, td[title]').tooltip({
			show: null,
			position: {
				my: 'left top',
				at: 'left bottom'
			},
			open: function(event, ui){
				ui.tooltip.animate({ top: ui.tooltip.position().top + 10 }, 'fast');
			},
			content: function(){
				var parts = $(this).attr('title').split('::', 2),
					title = '';

				if (parts.length == 2) {
					if (parts[0] != '') {
						title += '<div style="text-align: center; border-bottom: 1px solid #EEEEEE;">' + parts[0] + '</div>' + parts[1];
					} else {
						title += parts[1];
					}
				} else {
					title += $(this).attr('title');
				}

				return title;
			}
		});

		var p_grid_cfg = {
			grid_form_left: Math.round((($(document).width()/2)-($(document).width()/3))),
			grid_form_top: Math.round((($('body').height()/2)-($('body').height()/4))),
			grid_form_width: 780,
			grid_nav_config: {
				edit:false, add:false, del:false,
				refreshtext: '<?php echo JText::_('JTOOLBAR_REFRESH'); ?>',
				searchtext: '<?php echo JText::_('JSEARCH_FILTER'); ?>'
			},
			grid_height: Math.round(($(window).height() - $('#adminForm').offset().top) - 180),
			grid_width: $('#adminForm').width()
		};

		p_grid_cfg.grid_height = (p_grid_cfg.grid_height < 100) ? 200 : p_grid_cfg.grid_height;

		$('#list_premieres').jqGrid({
			url: 'index.php?option=com_kinoarhiv&controller=premieres&task=getPremieres&format=json<?php //echo ($this->items->id != 0) ? '&id='.$this->items->id : ''; ?>',
			datatype: 'json',
			height: p_grid_cfg.grid_height,
			width: p_grid_cfg.grid_width,
			shrinkToFit: true,
			colNames: ['<?php echo JText::_('JGRID_HEADING_ID'); ?>', '<?php echo JText::_('COM_KA_FIELD_AW_ID'); ?>', '<?php echo JText::_('COM_KA_FIELD_AW_LABEL'); ?>', '<?php echo JText::_('COM_KA_FIELD_AW_YEAR'); ?>', '<?php echo JText::_('COM_KA_FIELD_AW_DESC'); ?>'],
			colModel:[
				{name:'id', index:'rel.id', width:50, sorttype:"int", searchoptions: {sopt: ['cn','eq','le','ge']}},
				{name:'award_id', index:'rel.award_id', width:50, sorttype:"int", searchoptions: {sopt: ['cn','eq','le','ge']}},
				{name:'title', index:'rel.title', width:350, sorttype:"text", searchoptions: {sopt: ['cn','eq','bw','ew']}},
				{name:'year', index:'rel.year', width:150, sorttype:"int", searchoptions: {sopt: ['cn','eq','le','ge']}},
				{name:'desc', index:'rel.desc', width:350, sorttype:"text", searchoptions: {sopt: ['cn','eq','bw','ew']}}
			],
			multiselect: true,
			caption: '',
			pager: '#pager_premieres',
			sortname: 'rel.id',
			sortorder: 'desc',
			viewrecords: true,
			rowNum: 50
		});
		$('#list_premieres').jqGrid('navGrid', '#pager_premieres', p_grid_cfg.grid_nav_config, {}, {}, {}, {
			// Search form config
			width: p_grid_cfg.grid_form_width, left: p_grid_cfg.grid_form_left, top: p_grid_cfg.grid_form_top,
			closeAfterSearch: true, searchOnEnter: true, closeOnEscape: true
		});
		$('#list_premieres').jqGrid('gridResize', {});
	});
</script>
<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv'); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off">
	<div class="row-fluid">
		<div class="span12 premieres-container">
			<table id="list_premieres"></table>
			<div id="pager_premieres"></div>
		</div>
	</div>
	<?php echo JHtml::_('form.token'); ?>
</form>
