<?php defined('_JEXEC') or die;
JHtml::_('behavior.tooltip');
$input = JFactory::getApplication()->input;
$section = $input->get('section', '', 'word');
$type = $input->get('type', '', 'word');
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
				dialog.load('index.php?option=com_kinoarhiv&task=loadTemplate&template=upload&model=mediamanager&view=mediamanager&format=raw');
			});

			return false;
		}
		Joomla.submitform(task);
	}

	jQuery(document).ready(function($){
		$('#tabs').tabs({
			beforeLoad: function(event, ui){
				blockUI('show');
				ui.jqXHR.error(function(){
					ui.panel.html("Couldn't load this tab.");
					blockUI('hide');
				});
			},
			load: function(event, ui){
				blockUI('hide');
			}
		});
	});
</script>
<div id="j-main-container">
	<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=mediamanager'); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off">
		<?php if ($section == 'movie' && $type == 'gallery'): ?>
			<?php echo $this->loadTemplate('movie_gallery_tabs'); ?>
		<?php elseif ($section == 'movie' && $type == 'trailers'): ?>
		<?php elseif ($section == 'movie' && $type == 'sounds'): ?>
		<?php endif; ?>
		<input type="hidden" name="controller" value="mediamanager" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
	</form>
</div>
