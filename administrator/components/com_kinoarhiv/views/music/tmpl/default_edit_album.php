<?php defined('_JEXEC') or die;
JHtml::_('behavior.keepalive');
?>
<link type="text/css" rel="stylesheet" href="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/css/mediamanager.css"/>
<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/ui.multiselect.js"></script>
<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/jquery.jqGrid.min.js"></script>
<?php KAComponentHelper::getScriptLanguage('grid.locale-', false, 'grid', false); ?>
<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/jquery.searchFilter.min.js"></script>
<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/grid.setcolumns.js"></script>
<script type="text/javascript" src="<?php echo JURI::root(); ?>components/com_kinoarhiv/assets/js/cookie.min.js"></script>

<!-- Uncomment line below to load Browser+ from YDN -->
<!-- <script type="text/javascript" src="http://bp.yahooapis.com/2.4.21/browserplus-min.js"></script> -->
<!-- Comment line below if load Browser+ from YDN -->
<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/mediamanager/browserplus-min.js"></script>

<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/mediamanager/plupload.full.js"></script>
<?php KAComponentHelper::getScriptLanguage('', false, 'mediamanager', false); ?>
<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/mediamanager/jquery.plupload.queue.js"></script>
<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/mediamanager/jquery.ui.plupload.js"></script>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task == 'apply' || task == 'save' || task == 'save2new') {
			if (jQuery('#form_title').val() == '') {
				showMsg('#j-main-container', '<?php echo JText::_('COM_KA_REQUIRED'); ?>');
				return;
			}
		}
		Joomla.submitform(task);
	};

	jQuery(document).ready(function($){
		// Strongly needed for override fucking bootstrap
		var bootstrapTooltip = $.fn.tooltip.noConflict();
		$.fn.bootstrapTlp = bootstrapTooltip;
		var bootstrapButton = $.fn.button.noConflict();
		$.fn.bootstrapBtn = bootstrapButton;

		var active_tab = 0;
		if (typeof $.cookie('com_kinoarhiv.albums.tabs') == 'undefined') {
			$.cookie('com_kinoarhiv.albums.tabs', 0);
		} else {
			active_tab = $.cookie('com_kinoarhiv.albums.tabs');
		}

		$('#albums_tabs').tabs({
			create: function(event, ui){
				$(this).tabs('option', 'active', parseInt(active_tab, 10));
			},
			activate: function(event, ui){
				$.cookie('com_kinoarhiv.albums.tabs', ui.newTab.index());
			}
		});

		<?php if ($this->form->getValue('id', $this->form_edit_group) != 0): ?>
		$('.cmd-rules').click(function(e){
			e.preventDefault();
			var dialog = $('<div id="dialog-rules" title="<?php echo JText::_('COM_KA_PERMISSION_SETTINGS'); ?>"><p class="ajax-loading"><?php echo JText::_('COM_KA_LOADING'); ?></p></div>');

			$(dialog).dialog({
				dialogClass: 'rules-dlg',
				modal: true,
				width: 800,
				height: 520,
				close: function(event, ui){
					dialog.remove();
				},
				buttons: [
					{
						text: '<?php echo JText::_('JTOOLBAR_APPLY'); ?>',
						id: 'rules-apply',
						click: function(){
							var $this = $(this);
							blockUI('show');
							$.ajax({
								type: 'POST',
								url: $('#rulesForm', this).attr('action') + '&id=' + $('#id').val(),
								data: $('#rulesForm', this).serialize()
							}).done(function(response){
								blockUI();
								if (response.success) {
									$this.dialog('close');
								} else {
									showMsg('.rules-dlg #rulesForm', response.message);
								}
							}).fail(function(xhr, status, error){
								showMsg('.rules-dlg #rulesForm', error);
								blockUI();
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
			dialog.load('index.php?option=com_kinoarhiv&task=loadTemplate&template=rules_edit&model=album&view=music&format=raw');
		});
		<?php endif; ?>
	});
</script>
<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" autocomplete="off">
	<div id="j-main-container">
		<div class="row-fluid">
			<div class="span12">
				<div id="albums_tabs">
					<ul>
						<li><a href="#page-main"><?php echo JText::_('COM_KA_MOVIES_TAB_MAIN'); ?></a></li>
						<li><a href="#page-composer"><?php echo JText::_('COM_KA_MUSIC_GROUP_HEADING'); ?></a></li>
						<li><a href="#page-tracks"><?php echo JText::_('COM_KA_MUSIC_TRACKS_TITLE'); ?></a></li>
						<li><a href="#page-rates"><?php echo JText::_('COM_KA_MOVIES_TAB_RATE'); ?></a></li>
						<li><a href="#page-meta"><?php echo JText::_('COM_KA_MOVIES_TAB_META'); ?></a></li>
						<li><a href="#page-publ"><?php echo JText::_('COM_KA_MOVIES_TAB_PUB'); ?></a></li>
					</ul>
					<div id="page-main">
						<?php echo $this->loadTemplate('edit_album_info'); ?>
					</div>
					<div id="page-composer">
						<div class="row-fluid">
							<?php echo $this->loadTemplate('edit_composer'); ?>
						</div>
					</div>
					<div id="page-tracks">
						<div class="row-fluid">
							<?php //echo $this->loadTemplate('edit_tracks'); ?>
						</div>
					</div>
					<div id="page-rates">
						<?php //echo $this->loadTemplate('edit_rates'); ?>
					</div>
					<div id="page-meta">
						<?php echo $this->loadTemplate('edit_meta'); ?>
					</div>
					<div id="page-publ">
						<div class="row-fluid">
							<div class="span6">
								<fieldset class="form-horizontal">
									<div class="control-group">
										<div class="control-label"><?php echo $this->form->getLabel('created', $this->form_edit_group); ?></div>
										<div class="controls"><?php echo $this->form->getInput('created', $this->form_edit_group); ?></div>
									</div>
									<div class="control-group">
										<div class="control-label"><?php echo $this->form->getLabel('modified', $this->form_edit_group); ?></div>
										<div class="controls"><?php echo $this->form->getInput('modified', $this->form_edit_group); ?></div>
									</div>
									<div class="control-group">
										<div class="control-label"><?php echo $this->form->getLabel('ordering', $this->form_edit_group); ?></div>
										<div class="controls"><?php echo $this->form->getInput('ordering', $this->form_edit_group); ?></div>
									</div>
									<div class="control-group">
										<div class="control-label"><?php echo $this->form->getLabel('created_by', $this->form_edit_group); ?></div>
										<div class="controls"><?php echo $this->form->getInput('created_by', $this->form_edit_group); ?></div>
									</div>
									<div class="control-group">
										<div class="control-label"><?php echo $this->form->getLabel('link_titles', $this->form_attribs_group); ?></div>
										<div class="controls"><?php echo $this->form->getInput('link_titles', $this->form_attribs_group); ?></div>
									</div>
									<div class="control-group">
										<div class="control-label"><?php echo $this->form->getLabel('show_author', $this->form_attribs_group); ?></div>
										<div class="controls"><?php echo $this->form->getInput('show_author', $this->form_attribs_group); ?></div>
									</div>
									<div class="control-group">
										<div class="control-label"><?php echo $this->form->getLabel('show_create_date', $this->form_attribs_group); ?></div>
										<div class="controls"><?php echo $this->form->getInput('show_create_date', $this->form_attribs_group); ?></div>
									</div>
									<div class="control-group">
										<div class="control-label"><?php echo $this->form->getLabel('show_modify_date', $this->form_attribs_group); ?></div>
										<div class="controls"><?php echo $this->form->getInput('show_modify_date', $this->form_attribs_group); ?></div>
									</div>
								</fieldset>
							</div>
							<div class="span6">
								<fieldset class="form-horizontal">
									<div class="control-group">
										<div class="control-label"><?php echo $this->form->getLabel('language', $this->form_edit_group); ?></div>
										<div class="controls"><?php echo $this->form->getInput('language', $this->form_edit_group); ?></div>
									</div>
									<div class="control-group">
										<div class="control-label"><?php echo $this->form->getLabel('access', $this->form_edit_group); ?></div>
										<div class="controls"><?php echo $this->form->getInput('access', $this->form_edit_group); ?></div>
									</div>
									<div class="control-group">
										<div class="control-label"><?php echo $this->form->getLabel('state', $this->form_edit_group); ?></div>
										<div class="controls"><?php echo $this->form->getInput('state', $this->form_edit_group); ?></div>
									</div>
									<div class="control-group">
										<div class="control-label"><label><?php echo JText::_('JGLOBAL_ACTION_PERMISSIONS_LABEL'); ?></label></div>
										<div class="controls">
										<?php if ($this->form->getValue('id', $this->form_edit_group) != 0): ?>
											<button class="btn btn-small btn-default cmd-rules"><span class="icon-users"></span> <?php echo JText::_('COM_KA_PERMISSION_ACTION_DO'); ?></button>
										<?php else: ?>
											<button class="btn btn-small btn-default" title="<?php echo JText::_('COM_KA_NO_ID'); ?>" disabled><span class="icon-users"></span> <?php echo JText::_('COM_KA_PERMISSION_ACTION_DO'); ?></button>
										<?php endif; ?>
										</div>
									</div>
                                    <div class="control-group">
                                        <div class="control-label"><?php echo $this->form->getLabel('show_tags', $this->form_attribs_group); ?></div>
                                        <div class="controls"><?php echo $this->form->getInput('show_tags', $this->form_attribs_group); ?></div>
                                    </div>
                                    <div class="control-group">
                                        <div class="control-label"><?php echo $this->form->getLabel('allow_votes', $this->form_attribs_group); ?></div>
                                        <div class="controls"><?php echo $this->form->getInput('allow_votes', $this->form_attribs_group); ?></div>
                                    </div>
                                    <div class="control-group">
                                        <div class="control-label"><?php echo $this->form->getLabel('ratings_show_local', $this->form_attribs_group); ?></div>
                                        <div class="controls"><?php echo $this->form->getInput('ratings_show_local', $this->form_attribs_group); ?></div>
                                    </div>
                                    <div class="control-group">
                                        <div class="control-label"><?php echo $this->form->getLabel('allow_reviews', $this->form_attribs_group); ?></div>
                                        <div class="controls"><?php echo $this->form->getInput('allow_reviews', $this->form_attribs_group); ?></div>
                                    </div>
								</fieldset>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php echo $this->form->getInput('fs_alias', $this->form_edit_group)."\n"; ?>
	<input type="hidden" name="controller" value="music" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="id" id="id" value="<?php echo ($this->form->getValue('id', $this->form_edit_group) != 0) ? $this->form->getValue('id', $this->form_edit_group): 0; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
