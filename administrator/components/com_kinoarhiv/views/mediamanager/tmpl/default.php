<?php defined('_JEXEC') or die;
JHtml::_('behavior.keepalive');

$input = JFactory::getApplication()->input;
$section = $input->get('section', '', 'word');
$type = $input->get('type', '', 'word');
?>
<script type="text/javascript" src="<?php echo JURI::root(); ?>components/com_kinoarhiv/assets/js/jquery.colorbox-min.js"></script>
<script src="<?php echo JURI::root(); ?>components/com_kinoarhiv/assets/js/i18n/colorbox/jquery.colorbox-<?php echo substr(JFactory::getLanguage()->getTag(), 0, 2); ?>.js" type="text/javascript"></script>
<div id="j-main-container">
	<?php if ($section == 'movie'): ?>
		<?php if ($type == 'gallery'): ?>
			<?php echo $this->loadTemplate('movie_gallery_list'); ?>
		<?php elseif ($type == 'trailers'): ?>
			<?php echo $this->loadTemplate('movie_trailers_list'); ?>
		<?php elseif ($type == 'sounds'): ?>
			<?php echo $this->loadTemplate('movie_soundtracks_list'); ?>
		<?php endif; ?>
	<?php elseif ($section == 'name'): ?>
		<?php if ($type == 'gallery'): ?>
			<?php echo $this->loadTemplate('name_gallery_list'); ?>
		<?php endif; ?>
	<?php endif; ?>
</div>
