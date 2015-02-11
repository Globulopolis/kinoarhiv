<?php defined('_JEXEC') or die;
if ($this->form->getValue('id', $this->form_edit_group) == 0):
	echo JText::_('COM_KA_NO_ID');
	return;
endif; ?>
<script type="text/javascript">
	jQuery(document).ready(function($){
		var aw_grid_cfg = {
			grid_form_left: Math.round((($(document).width()/2)-($(document).width()/3))),
			grid_form_top: Math.round((($('body').height()/2)-($('body').height()/4))),
			grid_form_width: 780,
			grid_nav_config: {
				edit:false, add:false, del:false,
				refreshtext: '<?php echo JText::_('JTOOLBAR_REFRESH'); ?>',
				searchtext: '<?php echo JText::_('JSEARCH_FILTER'); ?>'
			},
			grid_height: Math.round(($(window).height() - $('#adminForm').offset().top) - 180),
			grid_width: $('#name_tabs').width()
		};

		aw_grid_cfg.grid_height = (aw_grid_cfg.grid_height < 100) ? 200 : aw_grid_cfg.grid_height;

		$('#list_awards').jqGrid({
			url: 'index.php?option=com_kinoarhiv&controller=names&task=getAwards&format=json<?php echo ($this->form->getValue('id', $this->form_edit_group) != 0) ? '&id='.$this->form->getValue('id', $this->form_edit_group) : ''; ?>',
			datatype: 'json',
			height: aw_grid_cfg.grid_height,
			width: aw_grid_cfg.grid_width,
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
			pager: '#pager_awards',
			sortname: 'rel.id',
			sortorder: 'desc',
			viewrecords: true,
			rowNum: 50
		});
		$('#list_awards').jqGrid('navGrid', '#pager_awards', aw_grid_cfg.grid_nav_config, {}, {}, {}, {
			// Search form config
			width: aw_grid_cfg.grid_form_width, left: aw_grid_cfg.grid_form_left, top: aw_grid_cfg.grid_form_top,
			closeAfterSearch: true, searchOnEnter: true, closeOnEscape: true
		});
		$('#list_awards').jqGrid('gridResize', {});

		$('.awards-container a.a, .awards-container a.e, .awards-container a.d').click(function(e){
			e.preventDefault();
			if ($(this).hasClass('a')) {
				// Load 'Add item' layout
				var dialog = $('<div id="dialog-award-add" title="<?php echo JText::_('COM_KA_MOVIES_AW_LAYOUT_ADD_TITLE'); ?>"><p class="ajax-loading"><?php echo JText::_('COM_KA_LOADING'); ?></p></div>');

				$(dialog).dialog({
					dialogClass: 'rel-awards-dlg',
					modal: true,
					width: 800,
					height: 520,
					close: function(event, ui){
						dialog.remove();
					},
					buttons: [
						{
							text: '<?php echo JText::_('JTOOLBAR_APPLY'); ?>',
							id: 'rel-add-apply',
							click: function(){
								var valid = true;

								if ($('#form_award_id').select2('val') == '') {
									$('#form_award_id-lbl').addClass('red-label');
									valid = false;
								}
								if ($('#form_aw_year').val() == '') {
									$('#form_aw_year-lbl').addClass('red-label');
									valid = false;
								}
								if (!valid) {
									showMsg('.rel-awards-dlg .placeholder', '<?php echo JText::_('COM_KA_REQUIRED'); ?>');
									return;
								}

								$.ajax({
									type: 'POST',
									url: 'index.php?option=com_kinoarhiv&controller=names&task=saveRelAwards&format=json&type=1&id=' + $('#id').val(),
									data: {
										'<?php echo JSession::getFormToken(); ?>': 1,
										'form[award_id]':	$('#form_award_id').select2('val'),
										'form[desc]':		$('#form_aw_desc').val(),
										'form[year]':		$('#form_aw_year').val(),
										'new': 1
									}
								}).done(function(response){
									if (response.success) {
										dialog.remove();
										$('#list_awards').trigger('reloadGrid');
									} else {
										showMsg('.rel-awards-dlg .placeholder', response.message);
									}
								}).fail(function(xhr, status, error){
									showMsg('.rel-awards-dlg .placeholder', error);
								});
							}
						},
						{
							text: '<?php echo JText::_('JTOOLBAR_CLOSE'); ?>',
							click: function(){
								dialog.remove();
							}
						}
					]
				});
				dialog.load('index.php?option=com_kinoarhiv&task=loadTemplate&template=awards_edit&model=name&view=names&format=raw');
			} else if ($(this).hasClass('e')) {
				// Load 'Edit item' layout
				var items = $('#list_awards .cbox').filter(':checked');
				if (items.length > 1) {
					showMsg('.awards-container', '<?php echo JText::_('COM_KA_ITEMS_EDIT_DENIED'); ?>');
				} else if (items.length == 1) {
					var ids = items.attr('id').substr(16).split('_');
					var dialog = $('<div id="dialog-award-edit" title="<?php echo JText::_('COM_KA_MOVIES_AWARDS_LAYOUT_EDIT_TITLE'); ?>"><p class="ajax-loading"><?php echo JText::_('COM_KA_LOADING'); ?></p></div>');

					$(dialog).dialog({
						dialogClass: 'rel-awards-dlg',
						modal: true,
						width: 800,
						height: 520,
						close: function(event, ui){
							dialog.remove();
						},
						buttons: [
							{
								text: '<?php echo JText::_('JTOOLBAR_APPLY'); ?>',
								id: 'rel-add-apply',
								click: function(){
									var valid = true;

									if ($('#form_award_id').select2('val') == '') {
										$('#form_award_id-lbl').addClass('red-label');
										valid = false;
									}
									if ($('#form_aw_year').val() == '') {
										$('#form_aw_year-lbl').addClass('red-label');
										valid = false;
									}
									if (!valid) {
										showMsg('.rel-awards-dlg .placeholder', '<?php echo JText::_('COM_KA_REQUIRED'); ?>');
										return;
									}

									$.ajax({
										type: 'POST',
										url: 'index.php?option=com_kinoarhiv&controller=names&task=saveRelAwards&format=json&type=1&id=' + $('#id').val(),
										data: {
											'<?php echo JSession::getFormToken(); ?>': 1,
											'form[id]':			$('#form_rel_aw_id').val(),
											'form[award_id]':	$('#form_award_id').select2('val'),
											'form[desc]':		$('#form_aw_desc').val(),
											'form[year]':		$('#form_aw_year').val(),
											'new': 0
										}
									}).done(function(response){
										if (response.success) {
											dialog.remove();
											$('#list_awards').trigger('reloadGrid');
										} else {
											showMsg('.rel-awards-dlg .placeholder', response.message);
										}
									}).fail(function(xhr, status, error){
										showMsg('.rel-awards-dlg .placeholder', error);
									});
								}
							},
							{
								text: '<?php echo JText::_('JTOOLBAR_CLOSE'); ?>',
								click: function(){
									dialog.remove();
								}
							}
						]
					});
					dialog.load('index.php?option=com_kinoarhiv&task=loadTemplate&template=awards_edit&model=name&view=names&format=raw&award_id='+ids[0]+'#edit');
				} else {
					showMsg('.awards-container', '<?php echo JText::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'); ?>');
				}
			} else if ($(this).hasClass('d')) {
				var items = $('#list_awards .cbox').filter(':checked');

				if (items.length <= 0) {
					showMsg('.awards-container', '<?php echo JText::_('JWARNING_TRASH_MUST_SELECT'); ?>');
					return;
				}

				if (!confirm("<?php echo JText::_('COM_KA_DELETE_SELECTED'); ?>")) {
					return;
				}

				$.post('index.php?option=com_kinoarhiv&controller=names&task=deleteRelAwards&format=json<?php echo ($this->form->getValue('id', $this->form_edit_group) != 0) ? '&id='.$this->form->getValue('id', $this->form_edit_group) : ''; ?>', {'data': items.serializeArray()}, function(response){
					showMsg('.awards-container', response.message);
					$('#list_awards').trigger('reloadGrid');
				}).fail(function(xhr, status, error){
					showMsg('#j-main-container', error);
				});
			}
		});
	});
</script>
<div class="row-fluid">
	<div class="span12 awards-container">
		<table id="list_awards"></table>
		<div id="pager_awards"></div>
		<div class="btn-toolbar list_awards">
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
