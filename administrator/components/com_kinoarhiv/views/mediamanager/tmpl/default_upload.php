<?php defined('_JEXEC') or die; ?>
<link type="text/css" rel="stylesheet" href="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/css/mediamanager.css"/>

<!-- Uncomment line below to load B+ from YDN -->
<!-- <script src="http://bp.yahooapis.com/2.4.21/browserplus-min.js" type="text/javascript"></script> -->
<!-- Comment line below if load B+ from YDN -->
<script src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/mediamanager/browserplus-min.js" type="text/javascript"></script>

<script src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/mediamanager/plupload.full.js" type="text/javascript"></script>
<script src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/i18n/mediamanager/<?php echo substr(JFactory::getLanguage()->getTag(), 0, 2); ?>.js" type="text/javascript"></script>
<script src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/mediamanager/jquery.plupload.queue.js" type="text/javascript"></script>
<script src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/mediamanager/jquery.ui.plupload.js" type="text/javascript"></script>
<script type="text/javascript">
//<![CDATA[
	jQuery(document).ready(function($){
		$('#uploader').pluploadQueue({
			runtimes: 'gears,html5,flash,silverlight,browserplus',
			url: '<?php echo JRoute::_('index.php'); ?>',
			max_file_size: '100mb',
			chunk_size: '1mb',
			unique_names: true,
			filters: [
				{title: 'Image files', extensions: 'jpg,gif,png'}
			],
			flash_swf_url: '<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/mediamanager/plupload.flash.swf',
			silverlight_xap_url: '<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/mediamanager/plupload.silverlight.xap'
		});
	});
//]]>
</script>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" style="margin: 0;">
	<!-- At this first hidden input we will remove autofocus -->
	<input type="hidden" autofocus="autofocus" />
	<div id="uploader">
		<p>You browser doesn't have Flash, Silverlight, Gears, BrowserPlus or HTML5 support.</p>
	</div>
</form>
