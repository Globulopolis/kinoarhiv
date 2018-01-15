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

JHtml::_('behavior.keepalive');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task == 'cancel') {
			document.location.href = 'index.php?option=com_kinoarhiv&view=relations&task=<?php echo $this->param; ?>&element=<?php echo $this->element; ?>';
		} else if (task == 'save' || task == 'apply' || task == 'save2new') {
			var state_required = true;

			jQuery('input.required').each(function(){
				var _this = jQuery(this);
				jQuery('#system-message-container').aurora.destroy({indexes:'all'});

				if (_this.val() == '') {
					state_required = false;
					_this.parent().prev('div').find('label').addClass('red-label');
					showMsg('#system-message-container', '<?php echo JText::_('COM_KA_REQUIRED'); ?>');
				} else {
					_this.parent().prev('div').find('label').removeClass('red-label');
				}
			});
			if (state_required) {
				jQuery.post('index.php?option=com_kinoarhiv&controller=relations&task='+task+'&element=<?php echo $this->element; ?>&format=json', jQuery('form').serialize(), function(response){
					if (response.success) {
						if (task == 'apply') {
							showMsg('#system-message-container', response.message);
							jQuery('input[name="control_id[0]"]').val(response.ids[0]);
							jQuery('input[name="control_id[1]"]').val(response.ids[1]);
						} else if (task == 'save') {
							document.location.href = 'index.php?option=com_kinoarhiv&view=relations&task=<?php echo $this->param; ?>&element=<?php echo $this->element; ?>';
						} else if (task == 'save2new') {
							document.location.href = 'index.php?option=com_kinoarhiv&controller=relations&task=add&element=<?php echo $this->element; ?>&param=<?php echo $this->param; ?>';
						}
					} else {
						showMsg('#system-message-container', response.message);
					}
				});
			}
		}
	};

	jQuery(document).ready(function($){
		$('#formordering').select2({ minimumResultsForSearch: -1 });
	});
</script>
<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv'); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off">
	<div id="j-main-container">
		<fieldset class="form-horizontal">
		<?php foreach ($this->form->getFieldset('relations_'.$this->param.'_'.$this->element) as $field): ?>
			<div class="control-group">
				<div class="control-label"><?php echo $field->label; ?></div>
				<div class="controls"><?php echo $field->input; ?></div>
			</div>
		<?php endforeach; ?>
		</fieldset>
	</div>
	<input type="hidden" name="param" value="<?php echo $this->param; ?>" />
	<input type="hidden" name="new" value="<?php echo ($this->task == 'add') ? 1 : 0; ?>" />
	<?php // Control IDs. 'Cause we need to know old id for update query. The decision on which id is responsible for what we receive in the model. These IDs don't make sense when we simply creating a new item.
	if ($this->param == 'countries') {
		$value1 = $this->form->getValue('country_id');
		$value2 = $this->form->getValue('movie_id');
	} elseif ($this->param == 'genres') {
		$value1 = $this->form->getValue('genre_id');
		if ($this->element == 'movies') {
			$value2 = $this->form->getValue('movie_id');
		} elseif ($this->element == 'names') {
			$value2 = $this->form->getValue('name_id');
		}
	} elseif ($this->param == 'careers') {
		$value1 = $this->form->getValue('career_id');
		$value2 = $this->form->getValue('name_id');
	} ?>
	<input type="hidden" name="control_id[0]" value="<?php echo $value1; ?>" />
	<input type="hidden" name="control_id[1]" value="<?php echo $value2; ?>" />
	<?php // end control IDs ?>
	<?php echo JHtml::_('form.token'); ?>
</form>
