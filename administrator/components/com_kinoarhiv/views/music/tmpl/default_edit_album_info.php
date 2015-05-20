<?php defined('_JEXEC') or die; ?>
<script type="text/javascript">
	jQuery(document).ready(function($){
		$('#form_album_genres').select2({
			placeholder: '<?php echo JText::_('COM_KA_SEARCH_AJAX'); ?>',
			quietMillis: 100,
			minimumInputLength: 1,
			maximumSelectionSize: 5,
			multiple: true,
			ajax: {
				cache: true,
				url: 'index.php?option=com_kinoarhiv&task=ajaxData&element=genres&type=music&format=json',
				data: function(term, page){
					return { term: term, showAll: 0 }
				},
				results: function(data, page){
					return { results: data };
				}
			},
			<?php if (!empty($this->form->getValue('genres', $this->form_edit_group))):
				$genres = $this->form->getValue('genres', $this->form_edit_group); ?>
			initSelection: function(element, callback){
				var data = <?php echo json_encode($genres['data']); ?>;
				callback(data);
			},
			<?php endif; ?>
			formatResult: function(data){
				return data.title;
			},
			formatSelection: function(data, container){
				return data.title;
			},
			escapeMarkup: function(m) { return m; }
		}).select2('container').find('ul.select2-choices').sortable({
			containment: 'parent',
			start: function() { $("#form_album_genres").select2('onSortStart'); },
			update: function() { $("#form_album_genres").select2('onSortEnd'); }
		});

		<?php if ($this->form->getValue('id', $this->form_edit_group) != 0): ?>
		$('.album-cover-preview').parent().click(function(e){
			e.preventDefault();

			$('<div id="dialog-message"><img src="'+ $(this).attr('href') +'" border="0" /></div>').dialog({
				modal: true,
				minHeight: $(window).height() - 100,
				minWidth: $(window).height() - 100,
				maxHeight: $(window).height() - 100,
				maxWidth: $(window).width() - 100
			});
		});

		$('#image_uploader').pluploadQueue({
			runtimes: 'html5,gears,flash,silverlight,browserplus,html4',
			url: 'index.php?option=com_kinoarhiv&controller=mediamanager&task=upload&format=raw&section=movie&type=gallery&tab=2&id=<?php echo ($this->form->getValue('id', $this->form_edit_group) != 0) ? $this->form->getValue('id', $this->form_edit_group) : 0; ?>&frontpage=1',
			multipart_params: {
				'<?php echo JSession::getFormToken(); ?>': 1
			},
			max_file_size: '<?php echo $this->params->get('upload_limit'); ?>',
			unique_names: false,
			filters: [{title: 'Image', extensions: '<?php echo $this->params->get('upload_mime_images'); ?>'}],
			flash_swf_url: '<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/mediamanager/plupload.flash.swf',
			silverlight_xap_url: '<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/mediamanager/plupload.silverlight.xap',
			preinit: {
				init: function(up, info){
					$('#image_uploader').find('.plupload_buttons a:last').after('<a class="plupload_button plupload_clear_all" href="#"><?php echo JText::_('JCLEAR'); ?></a>');
					$('#image_uploader .plupload_clear_all').click(function(e){
						e.preventDefault();
						up.splice();
						$.each(up.files, function(i, file){
							up.removeFile(file);
						});
					});
				},
				UploadComplete: function(up, files){
					$('#image_uploader').find('.plupload_buttons').show();
				}
			},
			init: {
				FileUploaded: function(up, file, info){
					var obj = $.parseJSON(info.response);
					var file = $.parseJSON(obj.id);
					var url = '<?php echo (JString::substr($this->params->get('media_posters_root_www'), 0, 1) == '/') ? JURI::root().JString::substr($this->params->get('media_posters_root_www'), 1).'/'.JString::substr($this->form->getValue('alias', $this->form_edit_group), 0, 1).'/'.$this->form->getValue('id', $this->form_edit_group).'/posters/' : $this->params->get('media_posters_root_www').'/'.JString::substr($this->form->getValue('alias', $this->form_edit_group), 0, 1).'/'.$this->form->getValue('id', $this->form_edit_group).'/posters/'; ?>';

					blockUI('show');
					$.post('index.php?option=com_kinoarhiv&controller=mediamanager&view=mediamanager&task=fpOff&section=movie&type=gallery&tab=2&id=<?php echo ($this->form->getValue('id', $this->form_edit_group) != 0) ? $this->form->getValue('id', $this->form_edit_group) : 0; ?>&format=raw',
						{ '_id[]': file.id, '<?php echo JSession::getFormToken(); ?>': 1, 'reload': 0 }
					).done(function(response){
						$('img.album-cover-preview').attr('src', url + 'thumb_'+ file.filename +'?_='+ new Date().getTime()).addClass('y-poster');
						$('img.album-cover-preview').parent('a').attr('href', url + file.filename +'?_='+ new Date().getTime());
						$('.cmd-scr-delete').attr('href', 'index.php?option=com_kinoarhiv&controller=mediamanager&view=mediamanager&task=remove&section=movie&type=gallery&tab=2&id=<?php echo ($this->form->getValue('id', $this->form_edit_group) != 0) ? $this->form->getValue('id', $this->form_edit_group) : 0; ?>&_id[]='+ file.id +'&format=raw');
						blockUI();
						$('.layout_img_upload').dialog('close');
					}).fail(function(xhr, status, error){
						showMsg('#system-message-container', error);
						blockUI();
					});
				}
			}
		});

		$('a.file-upload-scr').click(function(e){
			e.preventDefault();

			$('.layout_img_upload').dialog({
				modal: true,
				width: 700
			});
		});

		$('a.cmd-scr-delete').click(function(e){
			e.preventDefault();

			if (!confirm('<?php echo JText::_('JTOOLBAR_DELETE'); ?>?')) {
				return false;
			}

			blockUI('show');
			$.post($(this).attr('href'), { '<?php echo JSession::getFormToken(); ?>': 1, 'reload': 0 }, function(response){
				
				if (typeof response !== 'object' && response != "") {
					showMsg('#system-message-container', response);
				} else {
					$('img.album-cover-preview').attr('src', '<?php echo JURI::root(); ?>components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/images/no_movie_cover.png').removeClass('y-poster');
					$('img.album-cover-preview').parent('a').attr('href', '<?php echo JURI::root(); ?>components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/images/no_movie_cover.png');
				}
				blockUI();
			}).fail(function(xhr, status, error){
				showMsg('#system-message-container', error);
				blockUI();
			});
		});

		$('#form_album_alias').attr('readonly', true);
		<?php endif; ?>

		$('.cmd-alias').click(function(e){
			e.preventDefault();

			var dialog = $('<div id="dialog_alias" title="<?php echo JText::_('NOTICE'); ?>"><p><?php echo JText::_('COM_KA_FIELD_ALBUMS_ALIAS_CHANGE_NOTICE', true); ?><hr /><?php echo JText::_('JFIELD_ALIAS_DESC', true); ?></p></div>');

			if ($(this).hasClass('info')) {
				$(dialog).dialog({
					modal: true,
					width: 800,
					height: $(window).height()-100,
					draggable: false,
					close: function(event, ui){
						dialog.remove();
					}
				});
			} else {
				if (!$('#form_album_alias').is('[readonly]')) {
					return;
				}

				$(dialog).dialog({
					modal: true,
					width: 800,
					height: $(window).height()-100,
					draggable: false,
					close: function(event, ui){
						dialog.remove();
					},
					buttons: [
						{
							text: '<?php echo JText::_('JMODIFY'); ?>',
							id: 'alias-modify',
							click: function(){
								$('#form_album_alias').removeAttr('readonly').trigger('focus');
								$(this).dialog('close');
								$('#form_album_alias').focus();
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
			}
		});
	});
</script>
<div class="row-fluid">
	<div class="span6">
		<fieldset class="form-horizontal">
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('title', $this->form_edit_group); ?></div>
				<div class="controls"><?php echo $this->form->getInput('title', $this->form_edit_group); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('alias', $this->form_edit_group); ?></div>
				<div class="controls">
					<div class="input-append">
						<?php echo $this->form->getInput('alias', $this->form_edit_group); ?>
						<?php if ($this->form->getValue('id', $this->form_edit_group) != 0): ?><button class="btn btn-default cmd-alias unblock"><i class="icon-pencil-2"></i></button><?php endif; ?>
						<button class="btn btn-default cmd-alias info"><i class="icon-help"></i></button>
					</div>
				</div>
				<?php echo $this->form->getInput('alias_orig', $this->form_edit_group); ?>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('composer', $this->form_edit_group); ?></div>
				<div class="controls"><?php echo $this->form->getInput('composer', $this->form_edit_group); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('genres', $this->form_edit_group); ?></div>
				<div class="controls">
					<?php echo $this->form->getInput('genres', $this->form_edit_group); ?>
					<span class="rel-link"><a href="index.php?option=com_kinoarhiv&controller=genres&task=add&type=music" target="_blank"><img src="components/com_kinoarhiv/assets/images/icons/add.png" border="0" /></a></span>
					<span class="rel-link"><a href="index.php?option=com_kinoarhiv&view=relations&task=genres&element=movies&mid=<?php echo ($this->form->getValue('id', $this->form_edit_group) != 0) ? $this->form->getValue('id', $this->form_edit_group) : 0; ?>" class="hasTip" title="<?php echo JText::_('COM_KA_TABLES_RELATIONS'); ?>" target="_blank"><img src="components/com_kinoarhiv/assets/images/icons/arrow_switch.png" border="0" /></a></span>
				</div>
			</div>
		</fieldset>
	</div>
	<div class="span6">
		<div class="span9">
			<fieldset class="form-horizontal">
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('year', $this->form_edit_group); ?></div>
					<div class="controls"><?php echo $this->form->getInput('year', $this->form_edit_group); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('length', $this->form_edit_group); ?></div>
					<div class="controls"><?php echo $this->form->getInput('length', $this->form_edit_group); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('isrc', $this->form_edit_group); ?></div>
					<div class="controls"><?php echo $this->form->getInput('isrc', $this->form_edit_group); ?></div>
				</div>
			</fieldset>
		</div>
		<div class="span3">
			<?php if ($this->form->getValue('id', $this->form_edit_group) != 0): ?>
			<a href="<?php echo $this->items->get('poster'); ?>"><img src="<?php echo $this->items->get('th_poster'); ?>" class="album-cover-preview <?php echo $this->items->get('y_poster'); ?>" height="110" /></a>
			<a href="#" class="file-upload-scr hasTip" title="<?php echo JText::_('JTOOLBAR_UPLOAD'); ?>"><span class="icon-upload"></span></a>
			<a href="index.php?option=com_kinoarhiv&controller=mediamanager&view=mediamanager&task=remove&section=movie&type=gallery&tab=2&id=<?php echo $this->form->getValue('id', $this->form_edit_group); ?>&_id[]=<?php echo $this->form->getValue('gid', $this->form_edit_group); ?>&format=raw" class="cmd-scr-delete hasTip" title="<?php echo JText::_('JTOOLBAR_DELETE'); ?>"><span class="icon-delete"></span></a>
			<?php endif; ?>
		</div>
	</div>
</div>
<div class="row-fluid">
	<div class="span12">
		<fieldset class="form-horizontal">
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('desc', $this->form_edit_group); ?></div>
				<div class="controls"><?php echo $this->form->getInput('desc', $this->form_edit_group); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('buy_url', $this->form_edit_group); ?></div>
				<div class="controls"><?php echo $this->form->getInput('buy_url', $this->form_edit_group); ?></div>
			</div>
		</fieldset>
	</div>
</div>

<div class="layout_img_upload" title="<?php echo JText::_('JTOOLBAR_UPLOAD'); ?>">
	<!-- At this first hidden input we will remove autofocus -->
	<input type="hidden" autofocus="autofocus" />
	<div id="image_uploader"><p>You browser doesn't have Flash, Silverlight, Gears, BrowserPlus or HTML5 support.</p></div>
</div>
