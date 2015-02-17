<?php defined('_JEXEC') or die;
if ($this->form->getValue('id', $this->form_edit_group) == 0):
	echo JText::_('COM_KA_NO_ID');
	return;
endif; ?>
<script type="text/javascript">
	jQuery(document).ready(function($){
		var c_grid_cfg = {
			grid_form_left: Math.round((($(document).width()/2)-($(document).width()/3))),
			grid_form_top: Math.round((($('body').height()/2)-($('body').height()/4))),
			grid_form_width: 780,
			grid_nav_config: {
				edit:false, add:false, del:false,
				refreshtext: '<?php echo JText::_('JTOOLBAR_REFRESH'); ?>',
				searchtext: '<?php echo JText::_('JSEARCH_FILTER'); ?>'
			},
			grid_height: Math.round(($(window).height() - $('#adminForm').offset().top) - 180),
			grid_width: $('#movie_tabs').width()
		}

		c_grid_cfg.grid_height = (c_grid_cfg.grid_height < 100) ? 200 : c_grid_cfg.grid_height;

		$('#list_actors').jqGrid({
			url: 'index.php?option=com_kinoarhiv&controller=movies&task=getCast&format=json<?php echo ($this->form->getValue('id', $this->form_edit_group) != 0) ? '&id='.$this->form->getValue('id', $this->form_edit_group) : ''; ?>',
			datatype: 'json',
			height: c_grid_cfg.grid_height,
			width: c_grid_cfg.grid_width,
			shrinkToFit: true,
			colNames: ['<?php echo JText::_('COM_KA_FIELD_NAME'); ?>', '<?php echo JText::_('JGRID_HEADING_ID'); ?>', '<?php echo JText::_('COM_KA_FIELD_NAME_ROLE'); ?>', '<?php echo JText::_('COM_KA_FIELD_NAME_DUB'); ?>', '<?php echo JText::_('JGRID_HEADING_ID'); ?>', '<?php echo JText::_('JFIELD_ORDERING_LABEL'); ?>', ''],
			colModel:[
				{name:'name', index:'n.name', width:350, sorttype:"text", searchoptions: {sopt: ['cn','eq','bw','ew']}},
				{name:'name_id', index:'n.id', width:50, sorttype:"int", searchoptions: {sopt: ['cn','eq','le','ge']}},
				{name:'role', index:'t.role', width:350, sorttype:"text", searchoptions: {sopt: ['cn','eq','bw','ew']}},
				{name:'dub_name', index:'d.name', width:350, sorttype:"text", searchoptions: {sopt: ['cn','eq','bw','ew']}},
				{name:'dub_id', index:'d.id', width:50, sorttype:"int", searchoptions: {sopt: ['cn','eq','le','ge']}},
				{name:'ordering', index:'t.ordering', width:60, align:"right", sortable: false, search: false},
				{name:'type', width:1, sortable: false, search: false}
			],
			multiselect: true,
			caption: '',
			rowNum: 0,
			pager: '#pager_actors',
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
		$('#list_actors').jqGrid('navGrid', '#pager_actors', c_grid_cfg.grid_nav_config, {}, {}, {}, {
			// Search form config
			width: c_grid_cfg.grid_form_width, left: c_grid_cfg.grid_form_left, top: c_grid_cfg.grid_form_top,
			closeAfterSearch: true, searchOnEnter: true, closeOnEscape: true
		});
		$('#list_actors').jqGrid('sortableRows', {
			connectWith: '#list_actors',
			update: function(e, ui){
				$.post('index.php?option=com_kinoarhiv&controller=relations&task=saveOrder&param=names&format=json', {
					'<?php echo JSession::getFormToken(); ?>': 1,
					'ids': $('#list_actors').jqGrid('getDataIDs').join(','),
					'id': ui.item.attr('id'),
					'item_id': <?php echo ($this->form->getValue('id', $this->form_edit_group) != 0) ? $this->form->getValue('id', $this->form_edit_group) : 0; ?>
				}, function(response){
					if (response.success) {
						$('#list_actors').trigger('reloadGrid');
					} else {
						showMsg('#j-main-container', '<?php echo JText::_('COM_KA_SAVE_ORDER_ERROR'); ?>');
					}
				}).fail(function(xhr, status, error){
					showMsg('#j-main-container', error);
				});
			}
		});
		$('#list_actors').jqGrid('gridResize', {});

		$('.actors-container a.a, .actors-container a.e, .actors-container a.d').click(function(e){
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
						$('#form_dub_id, #form_name_id, #form_type').select2('destroy');
						dialog.remove();
					},
					buttons: [
						{
							text: '<?php echo JText::_('JTOOLBAR_APPLY'); ?>',
							id: 'rel-add-apply',
							click: function(){
								var valid = true;
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
									url: 'index.php?option=com_kinoarhiv&controller=movies&task=saveRelNames&format=json&id=' + $('#id').val(),
									data: {
										'<?php echo JSession::getFormToken(); ?>': 1,
										'form[type]':			$('#form_type').select2('val'),
										'form[name_id]':		$('#form_name_id').select2('val'),
										'form[dub_id]':			$('#form_dub_id').select2('val'),
										'form[role]':			$('#form_role').val(),
										'form[is_directors]':	$('#form_is_directors').val(),
										'form[is_actors]':		$('#form_is_actors').val(),
										'form[voice_artists]':	$('#form_voice_artists').val(),
										'form[ordering]':		$('#form_r_ordering').val(),
										'form[desc]':			$('#form_r_desc').val(),
										'new': 1
									}
								}).done(function(response){
									if (response.success) {
										$(this).dialog('close');
										$('#list_actors').trigger('reloadGrid');
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
				dialog.load('index.php?option=com_kinoarhiv&task=loadTemplate&template=names_edit&model=movie&view=movies&format=raw');
			} else if ($(this).hasClass('e')) {
				// Load 'Edit item' layout
				var items = $('#list_actors .cbox').filter(':checked');
				if (items.length > 1) {
					showMsg('.actors-container', '<?php echo JText::_('COM_KA_ITEMS_EDIT_DENIED'); ?>');
				} else if (items.length == 1) {
					var ids = items.attr('id').substr(16).split('_');
					var dialog = $('<div id="dialog-name-edit" title="<?php echo JText::_('COM_KA_MOVIES_NAMES_LAYOUT_EDIT_TITLE'); ?>"><p class="ajax-loading"><?php echo JText::_('COM_KA_LOADING'); ?></p></div>');

					$(dialog).dialog({
						dialogClass: 'rel-names-dlg',
						modal: true,
						width: 800,
						height: 520,
						close: function(event, ui){
							$('#form_dub_id, #form_name_id, #form_type').select2('destroy');
							dialog.remove();
						},
						buttons: [
							{
								text: '<?php echo JText::_('JTOOLBAR_APPLY'); ?>',
								id: 'rel-add-apply',
								click: function(){
									var valid = true;
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
										url: 'index.php?option=com_kinoarhiv&controller=movies&task=saveRelNames&format=json&id=' + $('#id').val(),
										data: {
											'<?php echo JSession::getFormToken(); ?>': 1,
											'form[type]':			$('#form_type').select2('val'),
											'form[name_id]':		$('#form_name_id').select2('val'),
											'form[dub_id]':			$('#form_dub_id').select2('val'),
											'form[role]':			$('#form_role').val(),
											'form[is_directors]':	$('#form_is_directors').val(),
											'form[is_actors]':		$('#form_is_actors').val(),
											'form[voice_artists]':	$('#form_voice_artists').val(),
											'form[ordering]':		$('#form_r_ordering').val(),
											'form[desc]':			$('#form_r_desc').val(),
											'new': 0
										}
									}).done(function(response){
										if (response.success) {
											$(this).dialog('close');
											$('#list_actors').trigger('reloadGrid');
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
					dialog.load('index.php?option=com_kinoarhiv&task=loadTemplate&template=names_edit&model=movie&view=movies&format=raw&movie_id='+ids[1]+'&name_id='+ids[0]+'&career_id='+ids[2]+'#edit');
				} else {
					showMsg('.actors-container', '<?php echo JText::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'); ?>');
				}
			} else if ($(this).hasClass('d')) {
				var items = $('#list_actors .cbox').filter(':checked');

				if (items.length <= 0) {
					showMsg('.actors-container', '<?php echo JText::_('JWARNING_TRASH_MUST_SELECT'); ?>');
					return;
				}

				if (!confirm("<?php echo JText::_('COM_KA_DELETE_SELECTED'); ?>")) {
					return;
				}

				$.post('index.php?option=com_kinoarhiv&controller=movies&task=deleteCast&format=json<?php echo ($this->form->getValue('id', $this->form_edit_group) != 0) ? '&id='.$this->form->getValue('id', $this->form_edit_group) : ''; ?>', {'data': items.serializeArray()}, function(response){
					showMsg('.actors-container', response.message);
					$('#list_actors').trigger('reloadGrid');
				}).fail(function(xhr, status, error){
					showMsg('#j-main-container', error);
				});
			}
		});
	});
</script>
<div class="row-fluid">
	<div class="span12 actors-container">
		<table id="list_actors"></table>
		<div id="pager_actors"></div>
		<div class="btn-toolbar list_actors">
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
