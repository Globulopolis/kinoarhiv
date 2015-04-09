<?php defined('_JEXEC') or die;
if ($this->form->getValue('id', $this->form_edit_group) == 0):
	echo JText::_('COM_KA_NO_ID');
	return;
endif; ?>
<script type="text/javascript">
	jQuery(document).ready(function($){
		var r_grid_cfg = {
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
		};

		r_grid_cfg.grid_height = (r_grid_cfg.grid_height < 100) ? 200 : r_grid_cfg.grid_height;

		$('#list_releases').jqGrid({
			url: 'index.php?option=com_kinoarhiv&controller=movies&task=getReleases&format=json<?php echo ($this->form->getValue('id', $this->form_edit_group) != 0) ? '&id='.$this->form->getValue('id', $this->form_edit_group) : ''; ?>',
			datatype: 'json',
			height: r_grid_cfg.grid_height,
			width: r_grid_cfg.grid_width,
			shrinkToFit: true,
			colNames: ['<?php echo JText::_('JGRID_HEADING_ID'); ?>', '<?php echo JText::_('COM_KA_FIELD_RELEASE_VENDOR'); ?>', '<?php echo JText::_('COM_KA_FIELD_RELEASE_DATE_LABEL'); ?>', '<?php echo JText::_('COM_KA_FIELD_RELEASES_MEDIATYPE_DESC'); ?>', '<?php echo JText::_('COM_KA_FIELD_RELEASE_COUNTRY'); ?>', '<?php echo JText::_('JFIELD_ORDERING_LABEL'); ?>'],
			colModel:[
				{name:'id', index:'id', width:50, sorttype:"int", searchoptions: {sopt: ['cn','eq','le','ge']}},
				{name:'vendor', index:'vendor', width:350, sorttype:"text", searchoptions: {sopt: ['cn','eq','bw','ew']}},
				{name:'release_date', index:'release_date', width:150, sorttype:"text", searchoptions: {sopt: ['cn','eq','le','ge']}},
				{name:'media_type', index:'media_type', width:250, sorttype:"text", searchoptions: {sopt: ['cn','eq','le','ge']}},
				{name:'country', index:'country', width:250, sorttype:"text", searchoptions: {sopt: ['cn','eq','bw','ew']}},
				{name:'ordering', index:'ordering', width:60, align:"right", sortable: false, search: false}
			],
			multiselect: true,
			caption: '',
			pager: '#pager_releases',
			sortname: 'id',
			sortorder: 'asc',
			viewrecords: true,
			rowNum: 50
		});
		$('#list_releases').jqGrid('navGrid', '#pager_releases', r_grid_cfg.grid_nav_config, {}, {}, {}, {
			// Search form config
			width: r_grid_cfg.grid_form_width, left: r_grid_cfg.grid_form_left, top: r_grid_cfg.grid_form_top,
			closeAfterSearch: true, searchOnEnter: true, closeOnEscape: true
		});
		$('#list_releases').jqGrid('gridResize', {});

		$('.releases-container a.a, .releases-container a.e, .releases-container a.d').click(function(e){
			e.preventDefault();
			if ($(this).hasClass('a')) {
				// Load 'Add item' layout
				var dialog = $('<div id="dialog-release-add" title="<?php echo JText::_('COM_KA_MOVIES_RELEASE_LAYOUT_ADD_TITLE'); ?>"><p class="ajax-loading"><?php echo JText::_('COM_KA_LOADING'); ?></p></div>');

				$(dialog).dialog({
					dialogClass: 'releases-dlg',
					modal: true,
					width: 800,
					height: 520,
					close: function(event, ui){
						$('#form_r_vendor_id, #form_r_country_id').select2('destroy');
						dialog.remove();
					},
					buttons: [
						{
							text: '<?php echo JText::_('JTOOLBAR_APPLY'); ?>',
							id: 'rel-add-apply',
							click: function(){
								var valid = true, $this = $(this);

								if ($('#form_r_vendor_id').select2('val') == '') {
									$('#form_r_vendor_id-lbl-lbl').addClass('red-label');
									valid = false;
								}
								if ($('#form_r_country_id').select2('val') == '') {
									$('#form_r_country_id-lbl-lbl').addClass('red-label');
									valid = false;
								}
								if (!valid) {
									showMsg('.releases-dlg .placeholder', '<?php echo JText::_('COM_KA_REQUIRED'); ?>');
									return;
								}

								$.ajax({
									type: 'POST',
									url: 'index.php?option=com_kinoarhiv&controller=movies&task=saveRelease&format=json&movie_id=' + $('#id').val(),
									data: {
										'<?php echo JSession::getFormToken(); ?>': 1,
										'form[r_vendor_id]':    $('#form_r_vendor_id').select2('val'),
										'form[r_country_id]':   $('#form_r_country_id').select2('val'),
										'form[r_release_date]': $('#form_r_release_date').val(),
										'form[r_media_type]':   $('#form_r_media_type').select2('val'),
										'form[r_desc]':         $('#form_r_desc').val(),
										'form[r_language]':     $('#form_r_language').val(),
										'form[r_ordering]':     $('#form_r_ordering').val(),
										'new': 1
									}
								}).done(function(response){
									if (response.success) {
										$this.dialog('close');
										$('#list_releases').trigger('reloadGrid');
									} else {
										showMsg('.releases-dlg .placeholder', response.message);
									}
								}).fail(function(xhr, status, error){
									showMsg('.releases-dlg .placeholder', error);
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
				dialog.load('index.php?option=com_kinoarhiv&task=loadTemplate&template=releases_edit&model=movie&view=movies&format=raw');
			} else if ($(this).hasClass('e')) {
				// Load 'Edit item' layout
				var items = $('#list_releases .cbox').filter(':checked');
				if (items.length > 1) {
					showMsg('.releases-container', '<?php echo JText::_('COM_KA_ITEMS_EDIT_DENIED'); ?>');
				} else if (items.length == 1) {
					var ids = items.attr('id').split('_');
					var dialog = $('<div id="dialog-release-edit" title="<?php echo JText::_('COM_KA_MOVIES_RELEASE_LAYOUT_EDIT_TITLE'); ?>"><p class="ajax-loading"><?php echo JText::_('COM_KA_LOADING'); ?></p></div>');

					$(dialog).dialog({
						dialogClass: 'releases-dlg',
						modal: true,
						width: 800,
						height: 520,
						close: function(event, ui){
							$('#form_r_vendor_id, #form_r_country_id').select2('destroy');
							dialog.remove();
						},
						buttons: [
							{
								text: '<?php echo JText::_('JTOOLBAR_APPLY'); ?>',
								id: 'rel-add-apply',
								click: function(){
									var valid = true, $this = $(this);

									if ($('#form_r_vendor_id').select2('val') == '') {
										$('#form_r_vendor_id-lbl-lbl').addClass('red-label');
										valid = false;
									}
									if ($('#form_r_country_id').select2('val') == '') {
										$('#form_r_country_id-lbl-lbl').addClass('red-label');
										valid = false;
									}
									if (!valid) {
										showMsg('.releases-dlg .placeholder', '<?php echo JText::_('COM_KA_REQUIRED'); ?>');
										return;
									}

									$.ajax({
										type: 'POST',
										url: 'index.php?option=com_kinoarhiv&controller=movies&task=saveRelease&format=json&movie_id=' + $('#id').val() + '&id=' + ids[3],
										data: {
											'<?php echo JSession::getFormToken(); ?>': 1,
											'form[r_vendor_id]':    $('#form_r_vendor_id').select2('val'),
											'form[r_country_id]':   $('#form_r_country_id').select2('val'),
											'form[r_release_date]': $('#form_r_release_date').val(),
											'form[r_media_type]':   $('#form_r_media_type').select2('val'),
											'form[r_desc]':         $('#form_r_desc').val(),
											'form[r_language]':     $('#form_r_language').val(),
											'form[r_ordering]':     $('#form_r_ordering').val(),
											'new': 0
										}
									}).done(function(response){
										if (response.success) {
											$this.dialog('close');
											$('#list_releases').trigger('reloadGrid');
										} else {
											showMsg('.releases-dlg .placeholder', response.message);
										}
									}).fail(function(xhr, status, error){
										showMsg('.releases-dlg .placeholder', error);
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
					dialog.load('index.php?option=com_kinoarhiv&task=loadTemplate&template=releases_edit&model=movie&view=movies&format=raw&release_id='+ids[3]+'#edit');
				} else {
					showMsg('.releases-container', '<?php echo JText::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'); ?>');
				}
			} else if ($(this).hasClass('d')) {
				var items = $('#list_releases .cbox').filter(':checked');

				if (items.length <= 0) {
					showMsg('.releases-container', '<?php echo JText::_('JWARNING_TRASH_MUST_SELECT'); ?>');
					return;
				}

				if (!confirm("<?php echo JText::_('COM_KA_DELETE_SELECTED'); ?>")) {
					return;
				}

				$.post('index.php?option=com_kinoarhiv&controller=movies&task=deleteReleases&format=json<?php echo ($this->form->getValue('id', $this->form_edit_group) != 0) ? '&id='.$this->form->getValue('id', $this->form_edit_group) : ''; ?>', {'data': items.serializeArray(), '<?php echo JSession::getFormToken(); ?>': 1}, function(response){
					$('#list_releases').trigger('reloadGrid');
				}).fail(function(xhr, status, error){
					showMsg('#j-main-container', error);
				});
			}
		});
	});
</script>
<div class="row-fluid">
	<div class="span12 releases-container">
		<table id="list_releases"></table>
		<div id="pager_releases"></div>
		<div class="btn-toolbar list_releases">
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
