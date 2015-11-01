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

if ($this->form->getValue('id', $this->form_edit_group) == 0)
{
	echo JText::_('COM_KA_NO_ID');

	return;
}
?>
<script type="text/javascript">
	jQuery(document).ready(function($){
		var body = $('body');
		var p_grid_cfg = {
			grid_form_left: Math.round((($(document).width() / 2) - ($(document).width() / 3))),
			grid_form_top: Math.round(((body.height() / 2) - (body.height() / 4))),
			grid_form_width: 780,
			grid_nav_config: {
				edit:false, add:false, del:false,
				refreshtext: '<?php echo JText::_('JTOOLBAR_REFRESH'); ?>',
				searchtext: '<?php echo JText::_('JSEARCH_FILTER'); ?>'
			},
			grid_height: Math.round(($(window).height() - $('#adminForm').offset().top) - 180),
			grid_width: $('#movie_tabs').width()
		};

		p_grid_cfg.grid_height = (p_grid_cfg.grid_height < 100) ? 200 : p_grid_cfg.grid_height;

		var premieres_grid = $('#list_premieres');

		premieres_grid.jqGrid({
			url: 'index.php?option=com_kinoarhiv&controller=movies&task=getPremieres&format=json<?php echo ($this->form->getValue('id', $this->form_edit_group) != 0) ? '&id=' . $this->form->getValue('id', $this->form_edit_group) : ''; ?>',
			datatype: 'json',
			height: p_grid_cfg.grid_height,
			width: p_grid_cfg.grid_width,
			shrinkToFit: true,
			colNames: [
				'<?php echo JText::_('JGRID_HEADING_ID'); ?>',
				'<?php echo JText::_('COM_KA_FIELD_RELEASE_VENDOR') . ' - ' . JText::_('COM_KA_VENDORS_FIELD_TITLE'); ?>',
				'<?php echo JText::_('COM_KA_VENDORS_FIELD_TITLE_INTL'); ?>',
				'<?php echo JText::_('COM_KA_FIELD_PREMIERE_DATE'); ?>',
				'<?php echo JText::_('COM_KA_FIELD_COUNTRY_LABEL'); ?>',
				'<?php echo JText::_('JFIELD_ORDERING_LABEL'); ?>'
			],
			colModel:[
				{name:'id', index:'id', width:50, title:false, sorttype:"int", searchoptions: {sopt: ['cn','eq','le','ge']}},
				{name:'company_name', index:'company_name', width:350, title:false, sorttype:"text", searchoptions: {sopt: ['cn','eq','bw','ew']}},
				{name:'company_name_intl', index:'company_name_intl', width:350, title:false, sorttype:"text", searchoptions: {sopt: ['cn','eq','bw','ew']}},
				{name:'premiere_date', index:'premiere_date', width:150, title:false, sorttype:"date", datefmt:'Y-m-d', searchoptions: {
					sopt: ['cn','eq','le','ge']}
				},
				{name:'country', index:'c.name', width:350, title:false, sorttype:"text", searchoptions: {sopt: ['cn','eq','bw','ew']}},
				{name:'ordering', index:'ordering', width:60, title:false, align:"right", sortable: false, search: false}
			],
			multiselect: true,
			caption: '',
			pager: '#pager_premieres',
			sortname: 'id',
			sortorder: 'desc',
			viewrecords: true,
			rowNum: 50
		});
		premieres_grid.jqGrid('navGrid', '#pager_premieres', p_grid_cfg.grid_nav_config, {}, {}, {}, {
			// Search form config
			width: p_grid_cfg.grid_form_width, left: p_grid_cfg.grid_form_left, top: p_grid_cfg.grid_form_top,
			closeAfterSearch: true, searchOnEnter: true, closeOnEscape: true
		});
		premieres_grid.jqGrid('gridResize', {});

		$('.premieres-container a.a, .premieres-container a.e, .premieres-container a.d').click(function(e){
			e.preventDefault();

			if ($(this).hasClass('a')) {
				// Load 'Add item' layout
				var dialog = $('<div id="dialog-premiere-add" title="<?php echo JText::_('COM_KA_MOVIES_PREMIERE_LAYOUT_ADD_TITLE'); ?>"><p class="ajax-loading"><?php echo JText::_('COM_KA_LOADING'); ?></p></div>');

				$(dialog).dialog({
					dialogClass: 'premieres-dlg',
					modal: true,
					width: 800,
					height: 520,
					close: function(event, ui){
						$('#form_p_vendor_id, #form_p_country_id').select2('destroy');
						dialog.remove();
					},
					buttons: [
						{
							text: '<?php echo JText::_('JTOOLBAR_APPLY'); ?>',
							id: 'rel-add-apply',
							click: function(){
								var valid = true,
									$this = $(this),
									form_p_vendor_id = $('#form_p_vendor_id');

								if (form_p_vendor_id.select2('val') == '') {
									$('#form_p_vendor_id-lbl').addClass('red-label');
									valid = false;
								}
								if (!valid) {
									showMsg('.premieres-dlg .placeholder', '<?php echo JText::_('COM_KA_REQUIRED'); ?>');
									return;
								}

								$.ajax({
									type: 'POST',
									url: 'index.php?option=com_kinoarhiv&controller=premieres&task=save&alias=1&format=json',
									data: {
										'<?php echo JSession::getFormToken(); ?>': 1,
										'form[p_movie_id]':	    $('#id').val(),
										'form[p_vendor_id]':	form_p_vendor_id.select2('val'),
										'form[p_country_id]':	$('#form_p_country_id').select2('val'),
										'form[p_premiere_date]':$('#form_p_premiere_date').val(),
										'form[p_info]':			$('#form_p_info').val(),
										'form[p_language]':     $('#form_p_language').val(),
										'form[p_ordering]':		$('#form_p_ordering').val(),
										'new': 1
									}
								}).done(function(response){
									if (response.success) {

										premieres_grid.trigger('reloadGrid');
										$this.dialog('close');
									} else {
										showMsg('.premieres-dlg .placeholder', response.message);
									}
								}).fail(function(xhr, status, error){
									showMsg('.premieres-dlg .placeholder', error);
								});
							}
						},
						{
							text: '<?php echo JText::_('JTOOLBAR_CLOSE'); ?>',
							click: function(){
								$(this).dialog('close');
							}
						}
					]
				});
				dialog.load('index.php?option=com_kinoarhiv&task=loadTemplate&template=premieres_edit&model=movie&view=movies&format=raw');
			} else if ($(this).hasClass('e')) {
				// Load 'Edit item' layout
				var items = $('#list_premieres .cbox').filter(':checked');
				if (items.length > 1) {
					showMsg('.premieres-container', '<?php echo JText::_('COM_KA_ITEMS_EDIT_DENIED'); ?>');
				} else if (items.length == 1) {
					var ids = items.attr('id').split('_');
					var dialog = $('<div id="dialog-premiere-edit" title="<?php echo JText::_('COM_KA_MOVIES_PREMIERE_LAYOUT_EDIT_TITLE'); ?>"><p class="ajax-loading"><?php echo JText::_('COM_KA_LOADING'); ?></p></div>');

					$(dialog).dialog({
						dialogClass: 'premieres-dlg',
						modal: true,
						width: 800,
						height: 520,
						close: function(event, ui){
							$('#form_p_vendor_id, #form_p_country_id').select2('destroy');
							dialog.remove();
						},
						buttons: [
							{
								text: '<?php echo JText::_('JTOOLBAR_APPLY'); ?>',
								id: 'rel-add-apply',
								click: function(){
									var valid = true,
										$this = $(this),
										form_p_vendor_id = $('#form_p_vendor_id');

									if (form_p_vendor_id.select2('val') == '') {
										$('#form_p_vendor_id-lbl').addClass('red-label');
										valid = false;
									}
									if (!valid) {
										showMsg('.premieres-dlg .placeholder', '<?php echo JText::_('COM_KA_REQUIRED'); ?>');
										return;
									}

									$.ajax({
										type: 'POST',
										url: 'index.php?option=com_kinoarhiv&controller=premieres&task=save&alias=1&format=json',
										data: {
											'<?php echo JSession::getFormToken(); ?>': 1,
											'form[p_movie_id]':	    $('#id').val(),
											'form[p_vendor_id]':	form_p_vendor_id.select2('val'),
											'form[p_country_id]':	$('#form_p_country_id').select2('val'),
											'form[p_premiere_date]':$('#form_p_premiere_date').val(),
											'form[p_info]':			$('#form_p_info').val(),
											'form[p_language]':     $('#form_p_language').val(),
											'form[p_ordering]':		$('#form_p_ordering').val(),
											'id':  ids[3],
											'new': 0
										}
									}).done(function(response){
										if (response.success) {
											$this.dialog('close');
											premieres_grid.trigger('reloadGrid');
										} else {
											showMsg('.premieres-dlg .placeholder', response.message);
										}
									}).fail(function(xhr, status, error){
										showMsg('.premieres-dlg .placeholder', error);
									});
								}
							},
							{
								text: '<?php echo JText::_('JTOOLBAR_CLOSE'); ?>',
								click: function(){
									$(this).dialog('close');
								}
							}
						]
					});
					dialog.load('index.php?option=com_kinoarhiv&task=loadTemplate&template=premieres_edit&model=movie&view=movies&format=raw&premiere_id='+ids[3]+'#edit');
				} else {
					showMsg('.premieres-container', '<?php echo JText::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'); ?>');
				}
			} else if ($(this).hasClass('d')) {
				var items = $('#list_premieres .cbox').filter(':checked');

				if (items.length <= 0) {
					showMsg('.premieres-container', '<?php echo JText::_('JWARNING_TRASH_MUST_SELECT'); ?>');
					return;
				}

				if (!confirm("<?php echo JText::_('COM_KA_DELETE_SELECTED'); ?>")) {
					return;
				}

				$.post('index.php?option=com_kinoarhiv&controller=movies&task=deletePremieres&format=json<?php echo ($this->form->getValue('id', $this->form_edit_group) != 0) ? '&id='.$this->form->getValue('id', $this->form_edit_group) : ''; ?>', {'data': items.serializeArray(), '<?php echo JSession::getFormToken(); ?>': 1}, function(response){
					premieres_grid.trigger('reloadGrid');
				}).fail(function(xhr, status, error){
					showMsg('#j-main-container', error);
				});
			}
		});
	});
</script>
<div class="row-fluid">
	<div class="span12 premieres-container">
		<table id="list_premieres"></table>
		<div id="pager_premieres"></div>
		<div class="btn-toolbar list_premieres">
			<div class="btn-group">
				<a href="#" class="btn btn-small a"><i class="icon-new"> </i> <?php echo JText::_('JTOOLBAR_ADD'); ?></a>
			</div>
			<div class="btn-group">
				<a href="#" class="btn btn-small e"><i class="icon-edit"> </i> <?php echo JText::_('JTOOLBAR_EDIT'); ?></a>
			</div>
			<div class="btn-group">
				<a href="#" class="btn btn-small d"><i class="icon-delete"> </i> <?php echo JText::_('JTOOLBAR_REMOVE'); ?></a>
			</div>
		</div>
	</div>
</div>
