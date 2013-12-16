<?php defined('_JEXEC') or die;
$input = JFactory::getApplication()->input;
$section = $input->get('section', '', 'word');
$type = $input->get('type', '', 'word');
?>
<link type="text/css" rel="stylesheet" href="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/css/mediamanager.css"/>

<!-- Uncomment line below to load Browser+ from YDN -->
<!-- <script src="http://bp.yahooapis.com/2.4.21/browserplus-min.js" type="text/javascript"></script> -->
<!-- Comment line below if load Browser+ from YDN -->
<script src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/mediamanager/browserplus-min.js" type="text/javascript"></script>

<script src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/mediamanager/plupload.full.js" type="text/javascript"></script>
<script src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/i18n/mediamanager/<?php echo substr(JFactory::getLanguage()->getTag(), 0, 2); ?>.js" type="text/javascript"></script>
<script src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/mediamanager/jquery.plupload.queue.js" type="text/javascript"></script>
<script src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/mediamanager/jquery.ui.plupload.js" type="text/javascript"></script>
<script type="text/javascript">
//<![CDATA[
	jQuery(document).ready(function($){
		$('#dialog-upload').prev('.ui-dialog-titlebar').find('button.ui-button').addClass('stateChanged');
		$('#uploader').pluploadQueue({
			runtimes: 'html5,gears,flash,silverlight,browserplus,html4',
			url: 'index.php?option=com_kinoarhiv&controller=mediamanager&task=upload&format=raw&section=<?php echo $input->get('section', '', 'word'); ?>&type=<?php echo $input->get('type', '', 'word'); ?>&tab=<?php echo $input->get('tab', 0, 'int'); ?>&id=<?php echo $input->get('id', 0, 'int'); ?>',
			multipart_params: {
				'<?php echo JSession::getFormToken(); ?>': 1
			},
			max_file_size: '<?php echo $this->params->get('upload_limit'); ?>',
			<?php if ($this->params->get('upload_chunk') == 1): ?>chunk_size: '<?php echo $this->params->get('upload_chunk_size'); ?>',<?php endif; ?>
			unique_names: false,
			filters: [
				{title: 'Image files', extensions: '<?php echo $this->params->get('upload_mime_images'); ?>'}
			],
			flash_swf_url: '<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/mediamanager/plupload.flash.swf',
			silverlight_xap_url: '<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/mediamanager/plupload.silverlight.xap',
			init: {
				StateChanged: function(up){
					$('#dialog-upload').addClass('stateChanged');
				}
			}
		});
	});
//]]>
</script>
<form action="index.php" method="post" style="margin: 0;">
	<!-- At this first hidden input we will remove autofocus -->
	<input type="hidden" autofocus="autofocus" />
	<div id="uploader">
		<p>You browser doesn't have Flash, Silverlight, Gears, BrowserPlus or HTML5 support.</p>
	</div>
	<input type="hidden" name="option" value="com_kinoarhiv" />
	<input type="hidden" name="controller" value="mediamanager" />
	<input type="hidden" name="task" value="upload" />
	<input type="hidden" name="format" value="raw" />
	<?php echo JHtml::_('form.token'); ?>
</form>
