<?php defined('_JEXEC') or die;
JHtml::_('behavior.keepalive');
?>
<script type="text/javascript">
	jQuery(document).ready(function($){
		$('#form_rate_sum_loc, #form_rate_loc').blur(function(){
			var vote = parseFloat($('#form_rate_sum_loc').val() / $('#form_rate_loc').val()).toFixed(<?php echo (int)$this->params->get('vote_summ_precision'); ?>);
			$('#vote').text(vote);
		}).trigger('blur');

		$('#form_countries').select2({
			placeholder: '<?php echo JText::_('COM_KA_SEARCH_AJAX'); ?>',
			quietMillis: 100,
			minimumInputLength: 1,
			maximumSelectionSize: 10,
			multiple: true,
			ajax: {
				cache: true,
				url: 'index.php?option=com_kinoarhiv&task=ajaxData&element=countries&format=json',
				data: function(term, page){
					return { term: term, showAll: 0 }
				},
				results: function(data, page){
					return { results: data };
				}
			},
			initSelection: function(element, callback){
				var data = <?php echo json_encode($this->items['countries']['data']); ?>;
				callback(data);
			},
			formatResult: function(data){
				return "<img class='flag-dd' src='<?php echo JURI::root(); ?>components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/images/icons/countries/" + data.code + ".png' />" + data.title;
			},
			formatSelection: function(data, container){
				return data.title;
			},
			escapeMarkup: function(m) { return m; }
		}).select2('container').find('ul.select2-choices').sortable({
			containment: 'parent',
			start: function() { $("#form_countries").select2('onSortStart'); },
			update: function() { $("#form_countries").select2('onSortEnd'); }
		});

		$('#form_genres').select2({
			placeholder: '<?php echo JText::_('COM_KA_SEARCH_AJAX'); ?>',
			quietMillis: 100,
			minimumInputLength: 1,
			maximumSelectionSize: 10,
			multiple: true,
			ajax: {
				cache: true,
				url: 'index.php?option=com_kinoarhiv&task=ajaxData&element=genres&format=json',
				data: function(term, page){
					return { term: term, showAll: 0 }
				},
				results: function(data, page){
					return { results: data };
				}
			},
			initSelection: function(element, callback){
				var data = <?php echo json_encode($this->items['genres']['data']); ?>;
				callback(data);
			},
			formatResult: function(data){
				return data.title;
			},
			formatSelection: function(data, container){
				return data.title;
			},
			escapeMarkup: function(m) { return m; }
		}).select2('container').find('ul.select2-choices').sortable({
			containment: 'parent',
			start: function() { $("#form_genres").select2('onSortStart'); },
			update: function() { $("#form_genres").select2('onSortEnd'); }
		});

		$('a.update-vote').click(function(e){
			e.preventDefault();

			var cmd = $(this).attr('id');

			if (cmd == 'imdb_vote') {
				if ($('#form_kp_id').val() == '') { return; }

				blockUI('show');
				$.ajax({
					url: 'index.php?option=com_kinoarhiv&controller=movies&task=getRates&format=json&param=' + cmd + '&id=' + $('#form_kp_id').val()
				}).done(function(response){
					if (response.success) {
						$('#form_imdb_votesum').val(response.votesum);
						$('#form_imdb_votes').val(response.votes);
						requestUpdateStatImg(cmd, response);
					} else {
						showMsg('#j-main-container', response.message);
						$(document).scrollTop(0);
					}
					blockUI('hide');
				}).fail(function(xhr, status, error){
					showMsg('#j-main-container', error);
					$(document).scrollTop(0);
					blockUI('hide');
				});
			} else if (cmd == 'kp_vote') {
				if ($('#form_kp_id').val() == '') { return; }

				blockUI('show');
				$.ajax({
					url: 'index.php?option=com_kinoarhiv&controller=movies&task=getRates&format=json&param=' + cmd + '&id=' + $('#form_kp_id').val()
				}).done(function(response){
					if (response.success) {
						$('#form_kp_votesum').val(response.votesum);
						$('#form_kp_votes').val(response.votes);
						requestUpdateStatImg(cmd, response);
					} else {
						showMsg('#j-main-container', response.message);
						$(document).scrollTop(0);
					}
					blockUI('hide');
				}).fail(function(xhr, status, error){
					showMsg('#j-main-container', error);
					$(document).scrollTop(0);
					blockUI('hide');
				});
			} else if (cmd == 'rt_vote') {
				if ($('#form_rottentm_id').val() == '') { return; }

				blockUI('show');
				$.ajax({
					url: 'index.php?option=com_kinoarhiv&controller=movies&task=getRates&format=json&param=' + cmd + '&id=' + $('#form_rottentm_id').val()
				}).done(function(response){
					if (response.success) {
						$('#form_rate_fc').val(response.votesum);
						requestUpdateStatImg(cmd, response);
					} else {
						showMsg('#j-main-container', response.message);
						$(document).scrollTop(0);
					}
					blockUI('hide');
				}).fail(function(xhr, status, error){
					showMsg('#j-main-container', error);
					$(document).scrollTop(0);
					blockUI('hide');
				});
			}
		});

		function requestUpdateStatImg(elem, data) {
			if (confirm('<?php echo JText::_('COM_KA_MOVIE_RATES_UPDATE_IMG'); ?>')) {
				blockUI('show');

				$.ajax({
					type: 'POST',
					url: 'index.php?option=com_kinoarhiv&controller=movies&task=updateRateImg&format=json&id=<?php echo ($this->items['data']->id != 0) ? $this->items['data']->id : ''; ?>&elem=' + elem,
					data: data
				}).done(function(response){
					var mktime = new Date().getTime();
					if (response.success) {
						if (elem == 'imdb_vote') {
							folder = 'imdb';
						} else if (elem == 'kp_vote') {
							folder = 'kinopoisk';
						} else if (elem == 'rt_vote') {
							folder = 'rottentomatoes';
						}

						var dlg = '<div id="dialog-message" title="<?php echo JText::_('MESSAGE'); ?>"><p><img src="<?php echo JURI::root().$this->params->get('media_rating_image_root_www'); ?>/' + folder + '/' + $('#id').val() + '_big.png?' + mktime + '" border="0" /></p></div>';
						$(dlg).appendTo('body');
						$(dlg).dialog({
							modal: true
						});
					} else {
						showMsg('#j-main-container', response.message);
						$(document).scrollTop(0);
						blockUI('hide');
					}
				}).fail(function(xhr, status, error){
					showMsg('#j-main-container', error);
					$(document).scrollTop(0);
					blockUI('hide');
				});
			}
		}

		$('.movie-poster-preview').parent().colorbox({ maxHeight: '95%', maxWidth: '95%', fixed: true });

		$('#image_uploader').pluploadQueue({
			runtimes: 'html5,gears,flash,silverlight,browserplus,html4',
			url: 'index.php?option=com_kinoarhiv&controller=mediamanager&task=upload&format=raw&section=movie&type=gallery&tab=2&id=<?php echo $this->items['data']->id; ?>',
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
					var url = '<?php echo JURI::root().$this->params->get('media_posters_root_www').'/'.JString::substr($this->items['data']->alias, 0, 1).'/'.$this->items['data']->id.'/posters/'; ?>';

					blockUI('show');
					$.post('index.php?option=com_kinoarhiv&controller=mediamanager&view=mediamanager&task=fpOff&section=movie&type=gallery&tab=2&id=<?php echo $this->items['data']->id; ?>&format=raw',
						{ '_id[]': file.id, '<?php echo JSession::getFormToken(); ?>': 1, 'reload': 0 }
					).done(function(response){
						$('img.movie-poster-preview').attr('src', url + 'thumb_'+ file.filename +'?_='+ new Date().getTime());
						$('img.movie-poster-preview').parent('a').attr('href', url + file.filename +'?_='+ new Date().getTime());
						$('.cmd-scr-delete').attr('href', 'index.php?option=com_kinoarhiv&controller=mediamanager&view=mediamanager&task=remove&section=movie&type=gallery&tab=2&id=<?php echo $this->items['data']->id; ?>&_id[]='+ file.id +'&format=raw');
						blockUI();
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
				height: 330,
				width: 600
			});
		});

		$('a.cmd-scr-delete').click(function(e){
			e.preventDefault();

			if (!confirm('<?php echo JText::_('JTOOLBAR_DELETE'); ?>?')) {
				return false;
			}

			blockUI('show');
			$.post($(this).attr('href'), { '<?php echo JSession::getFormToken(); ?>': 1, 'reload': 0 }
			).done(function(response){
				blockUI();
			}).fail(function(xhr, status, error){
				showMsg('#system-message-container', error);
				blockUI();
			});
		});
	});
</script>
<div class="row-fluid">
	<div class="span6">
		<fieldset class="form-horizontal">
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('title'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('title'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('alias'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('alias'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('slogan'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('slogan'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<label id="form_genres-lbl" for="form_genres" class="hasTip" title="<?php echo JText::_('COM_KA_FIELD_MOVIE_GENRES_DESC'); ?>"><?php echo JText::_('COM_KA_FIELD_MOVIE_GENRES'); ?></label>
				</div>
				<div class="controls">
					<input type="hidden" name="form[genres]" id="form_genres" value="<?php echo implode(',', $this->items['genres']['ids']); ?>" class="span11 autocomplete" data-ac-type="genres" />
					<span class="rel-link"><a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=relations&task=genres&mid='.$this->items['data']->id); ?>" class="hasTip" title="::<?php echo JText::_('COM_KA_COUNTRIES_RELATIONS_BUTTON_TITLE'); ?>" target="_blank"><img src="components/com_kinoarhiv/assets/images/icons/arrow_switch.png" border="0" /></a></span>
				</div>
			</div>
		</fieldset>
	</div>
	<div class="span6">
		<div class="span9">
			<fieldset class="form-horizontal">
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('year'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('year'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('length'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('length'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('budget'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('budget'); ?></div>
				</div>
			</fieldset>
		</div>
		<div class="span3">
			<a href="<?php echo JURI::root().$this->params->get('media_posters_root_www').'/'.JString::substr($this->items['data']->alias, 0, 1).'/'.$this->items['data']->id.'/posters/'.$this->items['data']->filename; ?>"><img src="<?php echo JURI::root().$this->params->get('media_posters_root_www').'/'.JString::substr($this->items['data']->alias, 0, 1).'/'.$this->items['data']->id.'/posters/thumb_'.$this->items['data']->filename; ?>" class="movie-poster-preview" height="110" /></a>
			<a href="#" class="file-upload-scr hasTip" title="<?php echo JText::_('JTOOLBAR_UPLOAD'); ?>"><span class="icon-upload"></span></a>
			<a href="index.php?option=com_kinoarhiv&controller=mediamanager&view=mediamanager&task=remove&section=movie&type=gallery&tab=2&id=<?php echo $this->items['data']->id; ?>&_id[]=<?php echo $this->items['data']->gid; ?>&format=raw" class="cmd-scr-delete hasTip" title="<?php echo JText::_('JTOOLBAR_DELETE'); ?>"><span class="icon-delete"></span></a>
		</div>
		<fieldset class="form-horizontal">
			<div class="control-group">
				<div class="control-label">
					<label id="form_countries-lbl" for="form_countries" class="hasTip" title="<?php echo JText::_('COM_KA_FIELD_MOVIE_COUNTRIES_DESC'); ?>"><?php echo JText::_('COM_KA_FIELD_MOVIE_COUNTRIES'); ?></label>
				</div>
				<div class="controls">
					<input type="hidden" name="form[countries]" id="form_countries" value="<?php echo implode(',', $this->items['countries']['ids']); ?>" class="span11 autocomplete" data-ac-type="countries" />
					<span class="rel-link"><a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=relations&task=countries&mid='.$this->items['data']->id); ?>" class="hasTip" title="::<?php echo JText::_('COM_KA_COUNTRIES_RELATIONS_BUTTON_TITLE'); ?>" target="_blank"><img src="components/com_kinoarhiv/assets/images/icons/arrow_switch.png" border="0" /></a></span>
				</div>
			</div>
		</fieldset>
	</div>
</div>
<div class="row-fluid">
	<div class="span12">
		<fieldset class="form-horizontal">
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('plot'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('plot'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('known'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('known'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('desc'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('desc'); ?></div>
			</div>
		</fieldset>
	</div>
</div>
<div class="row-fluid">
	<legend><?php echo JText::_('COM_KA_FIELD_MOVIE_RATES'); ?></legend>
	<div class="span7">
		<fieldset class="form-horizontal">
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('mpaa'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('mpaa'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('age_restrict'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('age_restrict'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('ua_rate'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('ua_rate'); ?></div>
			</div>
		</fieldset>
	</div>
	<div class="span5">
		<fieldset class="form-horizontal">
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('imdb_votesum'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('imdb_votesum'); ?> <a href="#" id="imdb_vote" class="update-vote hasTip" title="::<?php echo JText::_('JTOOLBAR_REFRESH'); ?>"><img src="components/com_kinoarhiv/assets/images/icons/arrow_refresh_small.png" border="0" /></a></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('imdb_votes'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('imdb_votes'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('imdb_id'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('imdb_id'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('kp_votesum'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('kp_votesum'); ?> <a href="#" id="kp_vote" class="update-vote hasTip" title="::<?php echo JText::_('JTOOLBAR_REFRESH'); ?>"><img src="components/com_kinoarhiv/assets/images/icons/arrow_refresh_small.png" border="0" /></a></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('kp_votes'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('kp_votes'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('kp_id'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('kp_id'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('rate_fc'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('rate_fc'); ?> <a href="#" id="rt_vote" class="update-vote hasTip" title="::<?php echo JText::_('JTOOLBAR_REFRESH'); ?>"><img src="components/com_kinoarhiv/assets/images/icons/arrow_refresh_small.png" border="0" /></a></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('rottentm_id'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('rottentm_id'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('rate_sum_loc'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('rate_sum_loc'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('rate_loc'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('rate_loc'); ?></div>
			</div>
			<div class="control-group">
				<div class="span12"><?php echo JText::_('COM_KA_FIELD_MOVIE_VOTESUMM'); ?> / <?php echo JText::_('COM_KA_FIELD_MOVIE_VOTES'); ?> = <span id="vote">0</span></div>
			</div>
		</fieldset>
	</div>
	<div class="span12">
		<fieldset class="form-horizontal">
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('rate_custom'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('rate_custom'); ?></div>
			</div>
		</fieldset>
	</div>
</div>

<div style="display: none;" class="layout_img_upload" title="<?php echo JText::_('JTOOLBAR_UPLOAD'); ?>">
	<!-- At this first hidden input we will remove autofocus -->
	<input type="hidden" autofocus="autofocus" />
	<div id="image_uploader" class="tr-uploader"><p>You browser doesn't have Flash, Silverlight, Gears, BrowserPlus or HTML5 support.</p></div>
</div>
