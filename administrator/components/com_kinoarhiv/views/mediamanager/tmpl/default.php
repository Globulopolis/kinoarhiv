<?php defined('_JEXEC') or die;
JHtml::_('behavior.tooltip');
?>
<script type="text/javascript" src="<?php echo JURI::root(); ?>components/com_kinoarhiv/assets/js/ui.aurora.min.js"></script>
<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/jquery-ui-1.10.3.custom.min.js"></script>
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
			jQuery('<div class="ui-widget-overlay" style="z-index: 10001;"></div>').appendTo('body').show();
		} else {
			jQuery('.ui-widget-overlay').remove();
		}
	}

	Joomla.submitbutton = function(task) {
		if (task == 'upload') {
			jQuery(document).ready(function($){
				var dialog = $('<div id="dialog-upload" title="<?php echo JText::_('JTOOLBAR_UPLOAD'); ?>"><p class="ajax-loading"><?php echo JText::_('COM_KA_LOADING'); ?></p></div>').appendTo('body');

				$(dialog).dialog({
					dialogClass: 'dialog-upload-dlg',
					modal: true,
					width: 800,
					height: 520,
					close: function(event, ui){
						dialog.remove();
					}
				});
				dialog.load('index.php?option=com_kinoarhiv&task=loadTemplate&template=upload&model=mediamanager&view=movies&format=raw');
			});

			return false;
		}
		Joomla.submitform(task);
	}
</script>
<div id="j-main-container">
	<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=mediamanager'); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off">
		<input type="hidden" name="controller" value="mediamanager" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
	</form>
</div>
