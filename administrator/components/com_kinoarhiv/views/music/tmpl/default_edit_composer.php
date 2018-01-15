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

if ($this->form->getValue('id', $this->form_edit_group) == 0):
	echo JText::_('COM_KA_NO_ID');
	return;
endif; ?>
<script type="text/javascript">
	jQuery(document).ready(function($){
		var c_grid_cfg = {
			grid_form_left: Math.round((($(document).width()/2)-($(document).width()/3))),
			grid_form_top: Math.round((($('body').height()/2)-($('body').height4))),
			grid_form_width: 780,
			grid_nav_config: {
				edit:false, add:false, del:false,
				refreshtext: '<?php echo JText::_('JTOOLBAR_REFRESH'); ?>',
				searchtext: '<?php echo JText::_('JSEARCH_FILTER'); ?>'
			},
			grid_height: Math.round(($(window).height() - $('#adminForm').offset().top) - 180),
			grid_width: $('#albums_tabs').width()
		};

		c_grid_cfg.grid_height = (c_grid_cfg.grid_height < 100) ? 200 : c_grid_cfg.grid_height;

		$('#list_composers').jqGrid({
			url: 'index.php?option=com_kinoarhiv&controller=music&task=getComposers&format=json<?php echo ($this->form->getValue('id', $this->form_edit_group) != 0) ? '&id='.$this->form->getValue('id', $this->form_edit_group) : ''; ?>',
			datatype: 'json',
			height: c_grid_cfg.grid_height,
			width: c_grid_cfg.grid_width,
			shrinkToFit: true,
			colNames: ['<?php echo JText::_('COM_KA_FIELD_NAME'); ?>', '<?php echo JText::_('JGRID_HEADING_ID'); ?>', '<?php echo JText::_('COM_KA_FIELD_NAME_ROLE'); ?>', '<?php echo JText::_('JFIELD_ORDERING_LABEL'); ?>', ''],
			colModel:[
				{name:'name', index:'n.name', width:350, sorttype:"text", searchoptions: {sopt: ['cn','eq','bw','ew']}},
				{name:'name_id', index:'n.id', width:50, sorttype:"int", searchoptions: {sopt: ['cn','eq','le','ge']}},
				{name:'role', index:'t.role', width:350, sorttype:"text", searchoptions: {sopt: ['cn','eq','bw','ew']}},
				{name:'ordering', index:'t.ordering', width:60, align:"right", sortable: false, search: false},
				{name:'type', width:1, sortable: false, search: false}
			],
			multiselect: true,
			caption: '',
			rowNum: 0,
			pager: '#pager_composers',
			sortname: 'ordering',
			sortorder: 'asc',
			viewrecords: true,
			pgbuttons: false,
			pginput: false,
			grouping: true,
			groupingView: {
				groupField: ['type'],
				groupColumnShow: [false],
				groupText: ['<b>{0} - {1}<?php echo JText::_('COM_KA_ITEMS_NUM'); ?></b>'],
				groupCollapse: false,
				groupSummary: [false],
				groupDataSorted: false
			},
			gridComplete: function(){
				$(this).find('.jqgroup').addClass('ui-widget-header');
			}
		});
		$('#list_composers').jqGrid('navGrid', '#pager_composers', c_grid_cfg.grid_nav_config, {}, {}, {}, {
			// Search form config
			width: c_grid_cfg.grid_form_width, left: c_grid_cfg.grid_form_left, top: c_grid_cfg.grid_form_top,
			closeAfterSearch: true, searchOnEnter: true, closeOnEscape: true
		});
		$('#list_composers').jqGrid('sortableRows', {
			connectWith: '#list_composers',
			update: function(e, ui){
				$.post('index.php?option=com_kinoarhiv&controller=relations&task=saveOrder&param=composers&format=json', {
					'<?php echo JSession::getFormToken(); ?>': 1,
					'ids': $('#list_composers').jqGrid('getDataIDs').join(','),
					'id': ui.item.attr('id'),
					'item_id': <?php echo ($this->form->getValue('id', $this->form_edit_group) != 0) ? $this->form->getValue('id', $this->form_edit_group) : 0; ?>
				}, function(response){
					if (response.success) {
						$('#list_composers').trigger('reloadGrid');
					} else {
						showMsg('#system-message-container', '<?php echo JText::_('COM_KA_SAVE_ORDER_ERROR'); ?>');
					}
				}).fail(function(xhr, status, error){
					showMsg('#system-message-container', error);
				});
			}
		});
		$('#list_composers').jqGrid('gridResize', {});

		$('.composer-container a.a, .composer-container a.e, .composer-container a.d').click(function(e){
			e.preventDefault();
			if ($(this).hasClass('a')) {
				// Load 'Add item' layout
				var dialog = $('<div id="dialog-name-add" title="<?php echo JText::_('COM_KA_MOVIES_NAMES_LAYOUT_ADD_TITLE'); ?>"><p class="ajax-loading"><?php echo JText::_('COM_KA_LOADING'); ?></p></div>');

				$(dialog).dialog({
					dialogClass: 'rel-names-dlg',
					modal: true,
					width: 800,
					height: 520,
					close: function(event, ui){
						$('#form_name_id, #form_type').select2('destroy');
						dialog.remove();
					},
					buttons: [
						{
							text: '<?php echo JText::_('JTOOLBAR_APPLY'); ?>',
							id: 'rel-add-apply',
							click: function(){
								var valid = true, $this = $(this);
								if ($('#form_type').select2('val') == '' || $('#form_type').select2('val') == 0) {
									$('#form_type-lbl').addClass('red-label');
									valid = false;
								}
								if ($('#form_name_id').select2('val') == '' || $('#form_name_id').select2('val') == 0) {
									$('#form_name_id-lbl').addClass('red-label');
									valid = false;
								}
								if (!valid) {
									showMsg('.rel-names-dlg .placeholder', '<?php echo JText::_('COM_KA_REQUIRED'); ?>');
									return;
								}

								$.ajax({
									type: 'POST',
									url: 'index.php?option=com_kinoarhiv&controller=music&task=saveRelNames&type=composers&format=json&id=' + $('#id').val(),
									data: {
										'<?php echo JSession::getFormToken(); ?>': 1,
										'form[type]':     $('#form_type').select2('val'),
										'form[name_id]':  $('#form_name_id').select2('val'),
										'form[role]':     $('#form_role').val(),
										'form[ordering]': $('#form_r_ordering').val(),
										'form[desc]':     $('#form_r_desc').val(),
										'new': 1
									}
								}).done(function(response){
									if (response.success) {
										$this.dialog('close');
										$('#list_composers').trigger('reloadGrid');
									} else {
										showMsg('.rel-names-dlg .placeholder', response.message);
									}
								}).fail(function(xhr, status, error){
									showMsg('.rel-names-dlg .placeholder', error);
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
				dialog.load('index.php?option=com_kinoarhiv&task=loadTemplate&template=names_edit&model=album&view=music&format=raw');
			} else if ($(this).hasClass('e')) {
				// Load 'Edit item' layout
				var items = $('#list_composers .cbox').filter(':checked');
				if (items.length > 1) {
					showMsg('.composer-container', '<?php echo JText::_('COM_KA_ITEMS_EDIT_DENIED'); ?>');
				} else if (items.length == 1) {
					var ids = items.attr('id').split('_');
					var dialog = $('<div id="dialog-name-edit" title="<?php echo JText::_('COM_KA_MOVIES_NAMES_LAYOUT_EDIT_TITLE'); ?>"><p class="ajax-loading"><?php echo JText::_('COM_KA_LOADING'); ?></p></div>');

					$(dialog).dialog({
						dialogClass: 'rel-names-dlg',
						modal: true,
						width: 800,
						height: 520,
						close: function(event, ui){
							$('#form_name_id, #form_type').select2('destroy');
							dialog.remove();
						},
						buttons: [
							{
								text: '<?php echo JText::_('JTOOLBAR_APPLY'); ?>',
								id: 'rel-add-apply',
								click: function(){
									var valid = true, $this = $(this);
									if ($('#form_type').select2('val') == '' || $('#form_type').select2('val') == 0) {
										$('#form_type-lbl').addClass('red-label');
										valid = false;
									}
									if ($('#form_name_id').select2('val') == '' || $('#form_name_id').select2('val') == 0) {
										$('#form_name_id-lbl').addClass('red-label');
										valid = false;
									}
									if (!valid) {
										showMsg('.rel-names-dlg .placeholder', '<?php echo JText::_('COM_KA_REQUIRED'); ?>');
										return;
									}

									$.ajax({
										type: 'POST',
										url: 'index.php?option=com_kinoarhiv&controller=music&task=saveRelNames&type=composers&format=json&id=' + $('#id').val(),
										data: {
											'<?php echo JSession::getFormToken(); ?>': 1,
											'form[type]':     $('#form_type').select2('val'),
											'form[name_id]':  $('#form_name_id').select2('val'),
											'form[role]':     $('#form_role').val(),
											'form[ordering]': $('#form_r_ordering').val(),
											'form[desc]':     $('#form_r_desc').val(),
											'new': 0
										}
									}).done(function(response){
										if (response.success) {
											$this.dialog('close');
											$('#list_composers').trigger('reloadGrid');
										} else {
											showMsg('.rel-names-dlg .placeholder', response.message);
										}
									}).fail(function(xhr, status, error){
										showMsg('.rel-names-dlg .placeholder', error);
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
					dialog.load('index.php?option=com_kinoarhiv&task=loadTemplate&template=names_edit&model=album&view=music&format=raw&album_id='+ids[4]+'&name_id='+ids[3]+'&career_id='+ids[5]+'#edit');
				} else {
					showMsg('.composer-container', '<?php echo JText::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'); ?>');
				}
			} else if ($(this).hasClass('d')) {
				var items = $('#list_composers .cbox').filter(':checked');

				if (items.length <= 0) {
					showMsg('.composer-container', '<?php echo JText::_('JWARNING_TRASH_MUST_SELECT'); ?>');
					return;
				}

				if (!confirm("<?php echo JText::_('COM_KA_DELETE_SELECTED'); ?>")) {
					return;
				}

				$.post('index.php?option=com_kinoarhiv&controller=music&task=deleteComposers&format=json<?php echo ($this->form->getValue('id', $this->form_edit_group) != 0) ? '&id='.$this->form->getValue('id', $this->form_edit_group) : ''; ?>', {'data': items.serializeArray()}, function(response){
					showMsg('.composer-container', response.message);
					$('#list_composers').trigger('reloadGrid');
				}).fail(function(xhr, status, error){
					showMsg('#system-message-container', error);
				});
			}
		});
	});
</script>
<div class="row-fluid">
	<div class="span12 composer-container">
		<table id="list_composers"></table>
		<div id="pager_composers"></div>
		<div class="btn-toolbar list_composers">
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
