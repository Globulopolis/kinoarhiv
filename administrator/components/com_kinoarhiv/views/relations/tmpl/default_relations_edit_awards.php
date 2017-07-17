<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *  
 * @copyright   Copyright (C) 2017 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		var _$ = jQuery;
		if (task == 'cancel') {
			document.location.href = 'index.php?option=com_kinoarhiv&view=relations&task=<?php echo $this->param; ?>&award_type=<?php echo (int) $this->award_type; ?>';
		} else if (task == 'save' || task == 'apply' || task == 'save2new') {
			var state_required = true;

			_$('input.required').each(function(){
				var _this = _$(this);
				_$('#system-message-container').aurora.destroy({indexes:'all'});

				if (_this.val() == '') {
					state_required = false;
					_this.parent().prev('div').find('label').addClass('red-label');
					showMsg('#system-message-container', '<?php echo JText::_('COM_KA_REQUIRED'); ?>');
				} else {
					_this.parent().prev('div').find('label').removeClass('red-label');
				}
			});
			if (state_required) {
				_$.post('index.php?option=com_kinoarhiv&controller=relations&task='+task+'&format=json', _$('form').serialize(), function(response){
					if (response.success) {
						if (task == 'apply') {
							if (_$('#form_r_type').val() != <?php echo (int) $this->award_type; ?>) {
								_$('.ui-widget-overlay').show();
								document.location.href = 'index.php?option=com_kinoarhiv&controller=relations&task=edit&param=awards&award_id='+response.ids[0]+'&item_id='+response.ids[1]+'&award_type='+_$('#form_r_type').val();
							}

							showMsg('#system-message-container', response.message);
							_$('input[name="control_id[0]"]').val(response.ids[0]);
							_$('input[name="control_id[1]"]').val(response.ids[1]);
						} else if (task == 'save') {
							document.location.href = 'index.php?option=com_kinoarhiv&view=relations&task=<?php echo $this->param; ?>';
						} else if (task == 'save2new') {
							document.location.href = 'index.php?option=com_kinoarhiv&controller=relations&task=add&param=<?php echo $this->param; ?>';
						}
					} else {
						showMsg('#system-message-container', response.message);
					}
				}).fail(function(xhr, status, error){
					showMsg('#system-message-container', error);
				});
			}
		}
	};

	jQuery(document).ready(function($){
		$('#form_r_type').change(function(){
			if ($(this).val() == 0) {
				$('#form_r_item_id-lbl').html('<?php echo JText::_('COM_KA_FIELD_MOVIE_LABEL'); ?><span class="star"> *</span>');
			} else if ($(this).val() == 1) {
				$('#form_r_item_id-lbl').html('<?php echo JText::_('COM_KA_FIELD_NAME'); ?><span class="star"> *</span>');
			}

			$('#form_r_item_id').select2('val', '');
			$('input[name="control_id[2]"]').val($(this).val());
		}).trigger('change');
	});
</script>
<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv'); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off">
	<div id="j-main-container">
		<fieldset class="form-horizontal">
			<div class="row-fluid">
				<div class="span6">
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('award_id'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('award_id'); ?></div>
					</div>
				</div>
				<div class="span6">
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('year'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('year'); ?></div>
					</div>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span6">
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('item_id'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('item_id'); ?></div>
					</div>
				</div>
				<div class="span6">
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('type'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('type'); ?></div>
					</div>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('desc'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('desc'); ?></div>
			</div>
		</fieldset>
	</div>
	<input type="hidden" name="param" value="<?php echo $this->param; ?>" />
	<input type="hidden" name="new" value="<?php echo ($this->task == 'add') ? 1 : 0; ?>" />
	<?php echo $this->form->getInput('id'); ?>
	<!-- Control ids. 'Cause we need to know old id for update query. The decision on which id is responsible for what we receive in the model. These ids don't make sense when we simply creating a new item. -->
	<input type="hidden" name="control_id[0]" value="<?php echo $this->form->getValue('award_id'); ?>" />
	<input type="hidden" name="control_id[1]" value="<?php echo $this->form->getValue('item_id'); ?>" />
	<input type="hidden" name="control_id[2]" value="<?php echo $this->form->getValue('type'); ?>" />
	<!-- end -->
	<?php echo JHtml::_('form.token'); ?>
</form>
<div class="ui-widget-overlay" style="display: none;"></div>
