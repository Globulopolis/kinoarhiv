<?php defined('_JEXEC') or die;
JHtml::_('behavior.keepalive');
?>
<link type="text/css" rel="stylesheet" href="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/css/mediamanager.css"/>
<script type="text/javascript" src="<?php echo JURI::root(); ?>components/com_kinoarhiv/assets/js/ui.aurora.min.js"></script>
<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/jquery-ui.custom.min.js"></script>
<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/jquery.ui.tooltip.min.js"></script>
<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/ui.multiselect.js"></script>
<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/jqGrid.min.js"></script>
<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/i18n/grid/grid.locale-<?php echo substr($this->lang->getTag(), 0, 2); ?>.js"></script>
<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/jquery.searchFilter.js"></script>
<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/grid.setcolumns.js"></script>
<script type="text/javascript" src="<?php echo JURI::root(); ?>components/com_kinoarhiv/assets/js/cookie.min.js"></script>
<script type="text/javascript" src="<?php echo JURI::root(); ?>components/com_kinoarhiv/assets/js/select2.min.js"></script>
<script type="text/javascript" src="<?php echo JURI::root(); ?>components/com_kinoarhiv/assets/js/i18n/select/select2_locale_<?php echo substr($this->lang->getTag(), 0, 2); ?>.js"></script>

<!-- Uncomment line below to load Browser+ from YDN -->
<!-- <script type="text/javascript" src="http://bp.yahooapis.com/2.4.21/browserplus-min.js"></script> -->
<!-- Comment line below if load Browser+ from YDN -->
<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/mediamanager/browserplus-min.js"></script>

<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/mediamanager/plupload.full.js"></script>
<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/i18n/mediamanager/<?php echo substr($this->lang->getTag(), 0, 2); ?>.js"></script>
<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/mediamanager/jquery.plupload.queue.js"></script>
<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/mediamanager/jquery.ui.plupload.js"></script>
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

	function blockUI(action) {
		if (action == 'show') {
			jQuery('<div class="ui-widget-overlay" id="blockui" style="z-index: 10001;"></div>').appendTo('body').show();
		} else {
			jQuery('#blockui').remove();
		}
	}

	Joomla.submitbutton = function(task) {
		if (task == 'apply' || task == 'save' || task == 'save2new') {
			if (jQuery('#form_title').val() == '') {
				showMsg('#j-main-container', '<?php echo JText::_('COM_KA_REQUIRED'); ?>');
				return;
			}
		} else if (task == 'gallery' || task == 'trailers' || task == 'sounds') {
			var tab = (task == 'gallery') ? '&tab=3' : '';
			var url = 'index.php?option=com_kinoarhiv&view=mediamanager&section=movie&type='+ task + tab +'<?php echo ($this->items->id != 0) ? '&id='.$this->items->id : ''; ?>';
			var handler = window.open(url);
			if (!handler) {
				showMsg('#j-main-container', '<?php echo JText::_('COM_KA_NEWWINDOW_BLOCKED_A'); ?>'+url+'<?php echo JText::_('COM_KA_NEWWINDOW_BLOCKED_B'); ?>');
			}

			return false;
		}
		Joomla.submitform(task);
	}

	jQuery(document).ready(function($){
		var active_tab = 0;
		if (typeof $.cookie('com_kinoarhiv.movie.tabs') == 'undefined') {
			$.cookie('com_kinoarhiv.movie.tabs', 0);
		} else {
			active_tab = $.cookie('com_kinoarhiv.movie.tabs');
		}

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

		$('#movie_tabs').tabs({
			create: function(event, ui){
				$(this).tabs('option', 'active', parseInt(active_tab, 10));
			},
			activate: function(event, ui){
				$.cookie('com_kinoarhiv.movie.tabs', ui.newTab.index());
			}
		});

		$('.hasDatetime').each(function(i, el){
			if ($(el).hasClass('time')) {
				$(el).timepicker({
					timeFormat: $(el).data('time-format')
				});
			} else if ($(el).hasClass('date')) {
				
			} else if ($(el).hasClass('datetime')) {
				$(el).datetimepicker({
					dateFormat: $(el).data('date-format'),
					timeFormat: $(el).data('time-format')
				});
			}
		});

		$('.cmd-datetime').click(function(e){
			e.preventDefault();

			$(this).prev('input').trigger('focus');
		});
	});
</script>
<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv'); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off">
	<div id="j-main-container">
		<div class="row-fluid">
			<div class="span12">
				<div id="movie_tabs">
					<ul>
						<li><a href="#page-main"><?php echo JText::_('COM_KA_MOVIES_TAB_MAIN'); ?></a></li>
						<li><a href="#page-rates"><?php echo JText::_('COM_KA_MOVIES_TAB_RATE'); ?></a></li>
						<li><a href="#page-cast-crew"><?php echo JText::_('COM_KA_MOVIES_TAB_CAST_CREW'); ?></a></li>
						<li><a href="#page-awards"><?php echo JText::_('COM_KA_MOVIES_TAB_AWARDS'); ?></a></li>
						<li><a href="#page-meta"><?php echo JText::_('COM_KA_MOVIES_TAB_META'); ?></a></li>
						<li><a href="#page-publ"><?php echo JText::_('COM_KA_MOVIES_TAB_PUB'); ?></a></li>
					</ul>
					<div id="page-main">
						<?php echo $this->loadTemplate('edit_info'); ?>
					</div>
					<div id="page-rates">
						<?php echo $this->loadTemplate('edit_rates'); ?>
					</div>
					<div id="page-cast-crew">
						<?php echo $this->loadTemplate('edit_crew'); ?>
					</div>
					<div id="page-awards">
						<?php echo $this->loadTemplate('edit_awards'); ?>
					</div>
					<div id="page-meta">
						<?php echo $this->loadTemplate('edit_meta'); ?>
					</div>
					<div id="page-publ">
						<div class="row-fluid">
							<div class="span6">
								<fieldset class="form-horizontal">
									<div class="control-group">
										<div class="control-label"><?php echo $this->form->getLabel('created', $this->form_group); ?></div>
										<div class="controls"><?php echo $this->form->getInput('created', $this->form_group); ?></div>
									</div>
									<div class="control-group">
										<div class="control-label"><?php echo $this->form->getLabel('modified', $this->form_group); ?></div>
										<div class="controls"><?php echo $this->form->getInput('modified', $this->form_group); ?></div>
									</div>
									<div class="control-group">
										<div class="control-label"><?php echo $this->form->getLabel('ordering', $this->form_group); ?></div>
										<div class="controls"><?php echo $this->form->getInput('ordering', $this->form_group); ?></div>
									</div>
									<div class="control-group">
										<div class="control-label"><?php echo $this->form->getLabel('created_by', $this->form_group); ?></div>
										<div class="controls"><?php echo $this->form->getInput('created_by', $this->form_group); ?></div>
									</div>
								</fieldset>
							</div>
							<div class="span6">
								<fieldset class="form-horizontal">
									<div class="control-group">
										<div class="control-label"><?php echo $this->form->getLabel('language', $this->form_group); ?></div>
										<div class="controls"><?php echo $this->form->getInput('language', $this->form_group); ?></div>
									</div>
									<div class="control-group">
										<div class="control-label"><?php echo $this->form->getLabel('access', $this->form_group); ?></div>
										<div class="controls"><?php echo $this->form->getInput('access', $this->form_group); ?></div>
									</div>
									<div class="control-group">
										<div class="control-label"><?php echo $this->form->getLabel('state', $this->form_group); ?></div>
										<div class="controls"><?php echo $this->form->getInput('state', $this->form_group); ?></div>
									</div>
								</fieldset>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php echo $this->form->getInput('countries_orig', $this->form_group)."\n"; ?>
	<?php echo $this->form->getInput('genres_orig', $this->form_group)."\n"; ?>
	<?php echo $this->form->getInput('tags_orig', $this->form_group)."\n"; ?>
	<input type="hidden" name="controller" value="movies" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="id" id="id" value="<?php echo !empty($this->items->id) ? $this->items->id : 0; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
