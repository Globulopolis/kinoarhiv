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

JHtml::_('behavior.keepalive');
KAComponentHelperBackend::loadMediamanagerAssets();

$this->id = ($this->form->getValue('id', $this->form_edit_group) != 0) ? $this->form->getValue('id', $this->form_edit_group) : 0;
?>
<script type="text/javascript" src="<?php echo JUri::base(); ?>components/com_kinoarhiv/assets/js/ui.multiselect.js"></script>
<script type="text/javascript" src="<?php echo JUri::base(); ?>components/com_kinoarhiv/assets/js/jquery.jqGrid.min.js"></script>
<?php KAComponentHelper::getScriptLanguage('grid.locale-', 'media/com_kinoarhiv/js/i18n/grid/', false); ?>
<script type="text/javascript" src="<?php echo JUri::base(); ?>components/com_kinoarhiv/assets/js/jquery.searchFilter.min.js"></script>
<script type="text/javascript" src="<?php echo JUri::base(); ?>components/com_kinoarhiv/assets/js/grid.setcolumns.js"></script>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		var url, handler;
		if (task == 'apply' || task == 'save' || task == 'save2new') {
			if (document.getElementById('form_movie_title').value == '') {
				showMsg('#system-message-container', '<?php echo JText::_('COM_KA_REQUIRED'); ?>');
				return;
			}
		} else if (task == 'gallery' || task == 'trailers') {
			var tab = (task == 'gallery') ? '&tab=3' : '';
			url = 'index.php?option=com_kinoarhiv&view=mediamanager&section=movie&type='+ task + tab +'<?php echo $this->id != 0 ? '&id=' . $this->id : ''; ?>';
			handler = window.open(url);
			if (!handler) {
				showMsg(
					'#system-message-container',
					'<?php echo JText::_('COM_KA_NEWWINDOW_BLOCKED_A'); ?>' + url + '<?php echo JText::_('COM_KA_NEWWINDOW_BLOCKED_B'); ?>'
				);
			}

			return false;
		} else if (task == 'soundtracks') {
			url = 'index.php?option=com_kinoarhiv&view=music&type=albums<?php echo $this->id != 0 ? '&movie_id=' . $this->id : ''; ?>';
			handler = window.open(url);
			if (!handler) {
				showMsg(
					'#system-message-container',
					'<?php echo JText::_('COM_KA_NEWWINDOW_BLOCKED_A'); ?>' + url + '<?php echo JText::_('COM_KA_NEWWINDOW_BLOCKED_B'); ?>'
				);
			}

			return false;
		}
		Joomla.submitform(task);
	};

	jQuery(document).ready(function($){
		// Strongly needed for override fucking bootstrap
		var bootstrapTooltip = $.fn.tooltip.noConflict();
		$.fn.bootstrapTlp = bootstrapTooltip;
		var bootstrapButton = $.fn.button.noConflict();
		$.fn.bootstrapBtn = bootstrapButton;

		<?php if ($this->id != 0): ?>
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
			dialog.load('index.php?option=com_kinoarhiv&task=loadTemplate&template=rules_edit&model=movie&view=movies&format=raw');
		});
		<?php endif; ?>
	});
</script>
<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv'); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off">
	<div id="j-main-container">
		<div class="row-fluid">
			<div class="span12">
			<?php echo JHtml::_('bootstrap.startTabSet', 'movies', array('active' => 'page0')); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'movies', 'page0', JText::_('COM_KA_MOVIES_TAB_MAIN')); ?>

				<div id="page0">
					<?php echo $this->loadTemplate('edit_info'); ?>
				</div>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'movies', 'page1', JText::_('COM_KA_MOVIES_TAB_RATE')); ?>

				<div id="page1">
					<?php echo $this->loadTemplate('edit_rates'); ?>
				</div>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'movies', 'page2', JText::_('COM_KA_MOVIES_TAB_CAST_CREW')); ?>

				<div id="page2">
					<?php echo $this->loadTemplate('edit_crew'); ?>
				</div>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'movies', 'page3', JText::_('COM_KA_MOVIES_TAB_AWARDS')); ?>

				<div id="page3">
					<?php echo $this->loadTemplate('edit_awards'); ?>
				</div>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'movies', 'page4', JText::_('COM_KA_MOVIES_TAB_PREMIERES')); ?>

				<div id="page4">
					<?php echo $this->loadTemplate('edit_premieres'); ?>
				</div>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'movies', 'page5', JText::_('COM_KA_MOVIES_TAB_RELEASES')); ?>

				<div id="page5">
					<?php echo $this->loadTemplate('edit_releases'); ?>
				</div>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'movies', 'page6', JText::_('COM_KA_MOVIES_TAB_META')); ?>

				<div id="page6">
					<?php echo $this->loadTemplate('edit_meta'); ?>
				</div>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'movies', 'page7', JText::_('COM_KA_MOVIES_TAB_PUB')); ?>

				<div id="page7">
					<div class="row-fluid">
						<div class="span6">
							<fieldset class="form-horizontal">
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('publish_up', $this->form_edit_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('publish_up', $this->form_edit_group); ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('publish_down', $this->form_edit_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('publish_down', $this->form_edit_group); ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('created', $this->form_edit_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('created', $this->form_edit_group); ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('created_by', $this->form_edit_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('created_by', $this->form_edit_group); ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('modified', $this->form_edit_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('modified', $this->form_edit_group); ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('modified_by', $this->form_edit_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('modified_by', $this->form_edit_group); ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('link_titles', $this->form_attribs_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('link_titles', $this->form_attribs_group); ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('show_tags', $this->form_attribs_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('show_tags', $this->form_attribs_group); ?></div>
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
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('allow_votes', $this->form_attribs_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('allow_votes', $this->form_attribs_group); ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('ratings_show_local', $this->form_attribs_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('ratings_show_local', $this->form_attribs_group); ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('ratings_show_remote', $this->form_attribs_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('ratings_show_remote', $this->form_attribs_group); ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('allow_reviews', $this->form_attribs_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('allow_reviews', $this->form_attribs_group); ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('slider', $this->form_attribs_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('slider', $this->form_attribs_group); ?></div>
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
									<div class="control-label"><?php echo $this->form->getLabel('state', $this->form_edit_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('state', $this->form_edit_group); ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('access', $this->form_edit_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('access', $this->form_edit_group); ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><label><?php echo JText::_('JGLOBAL_ACTION_PERMISSIONS_LABEL'); ?></label></div>
									<div class="controls">
										<?php if ($this->id != 0): ?>
											<button class="btn btn-small btn-default cmd-rules"><span class="icon-users"></span> <?php echo JText::_('COM_KA_PERMISSION_ACTION_DO'); ?></button>
										<?php else: ?>
											<button class="btn btn-small btn-default" title="<?php echo JText::_('COM_KA_NO_ID'); ?>" disabled><span class="icon-users"></span> <?php echo JText::_('COM_KA_PERMISSION_ACTION_DO'); ?></button>
										<?php endif; ?>
									</div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('ordering', $this->form_edit_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('ordering', $this->form_edit_group); ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('tab_movie_wallpp', $this->form_attribs_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('tab_movie_wallpp', $this->form_attribs_group); ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('tab_movie_posters', $this->form_attribs_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('tab_movie_posters', $this->form_attribs_group); ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('tab_movie_scr', $this->form_attribs_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('tab_movie_scr', $this->form_attribs_group); ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('tab_movie_awards', $this->form_attribs_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('tab_movie_awards', $this->form_attribs_group); ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('tab_movie_tr', $this->form_attribs_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('tab_movie_tr', $this->form_attribs_group); ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('tab_movie_snd', $this->form_attribs_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('tab_movie_snd', $this->form_attribs_group); ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('trailer_collapsed', $this->form_attribs_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('trailer_collapsed', $this->form_attribs_group); ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('movie_collapsed', $this->form_attribs_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('movie_collapsed', $this->form_attribs_group); ?></div>
								</div>
							</fieldset>
						</div>
					</div>
				</div>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php echo JHtml::_('bootstrap.endTabSet'); ?>
			</div>
		</div>
	</div>

	<?php echo $this->form->getInput('genres_orig', $this->form_edit_group) . "\n"; ?>
	<input type="hidden" name="controller" value="movies" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="id" id="id" value="<?php echo $this->id; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
