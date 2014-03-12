<?php defined('_JEXEC') or die;
JHtml::_('behavior.keepalive');

$input = JFactory::getApplication()->input;
$section = $input->get('section', '', 'word');
$type = $input->get('type', '', 'word');
?>
<script type="text/javascript" src="<?php echo JURI::root(); ?>components/com_kinoarhiv/assets/js/jquery.colorbox-min.js"></script>
<script src="<?php echo JURI::root(); ?>components/com_kinoarhiv/assets/js/i18n/colorbox/jquery.colorbox-<?php echo substr(JFactory::getLanguage()->getTag(), 0, 2); ?>.js" type="text/javascript"></script>
<script type="text/javascript" src="<?php echo JURI::root(); ?>components/com_kinoarhiv/assets/js/ui.aurora.min.js"></script>
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
</script>
<div id="j-main-container">
	<?php if ($section == 'movie'): ?>
		<?php if ($type == 'gallery'): ?>
			<?php echo $this->loadTemplate('movie_gallery_list'); ?>
		<?php elseif ($type == 'trailers'): ?>
			<?php echo $this->loadTemplate('movie_trailers_list'); ?>
		<?php endif; ?>
	<?php endif; ?>
</div>
