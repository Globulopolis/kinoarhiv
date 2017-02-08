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

KAComponentHelperBackend::loadMediamanagerAssets();
JHtml::_('stylesheet', 'media/com_kinoarhiv/css/select.css');
JHtml::_('script', 'media/com_kinoarhiv/js/select2.min.js');
KAComponentHelper::getScriptLanguage('select2_locale_', 'media/com_kinoarhiv/js/i18n/select/');
?>
<script type="text/javascript">
	jQuery(document).ready(function($){
		$('a.tooltip-img').hover(function(e){
			$(this).next('img').stop().hide().fadeIn();
		}, function(e){
			$(this).next('img').stop().fadeOut();
		}).colorbox({ maxHeight: '95%', maxWidth: '95%', fixed: true });
		$('.thumbnail a.th_img').colorbox({ maxHeight: '95%', maxWidth: '95%', fixed: true });

		// Reload page if image files uploaded. Require hidden <input>
		$('#imgModalUpload').on('hidden', function(){
			if (parseInt($('input[name="file_uploaded"]').val(), 10) == 1) {
				document.location.reload();
			}
		});

		Joomla.submitbutton = function(task) {
			if (task == 'mediamanager.upload') {
				$('#imgModalUpload').modal('show');

				return false;
			} else if (task == 'mediamanager.copyfrom') {
				$('#copyfromModal').modal('show');

				return false;
			}

			Joomla.submitform(task);
		}
	});
</script>
<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off">
	<div class="btn-group pull-left" style="margin: 0 10px 0 0;">
	<?php if ($this->section == 'movie'): ?>
		<a href="index.php?option=com_kinoarhiv&view=mediamanager&section=<?php echo $this->section; ?>&type=<?php echo $this->type; ?>&tab=3&id=<?php echo $this->id; ?>&layoutview=<?php echo $this->layout; ?>" class="btn <?php echo ($this->tab == 3) ? 'btn-success' : ''; ?>">
			<span class="icon-picture icon-white"></span> <?php echo JText::_('COM_KA_MOVIES_SCRSHOTS'); ?>
		</a>
		<a href="index.php?option=com_kinoarhiv&view=mediamanager&section=<?php echo $this->section; ?>&type=<?php echo $this->type; ?>&tab=2&id=<?php echo $this->id; ?>&layoutview=<?php echo $this->layout; ?>" class="btn <?php echo ($this->tab == 2) ? 'btn-success' : ''; ?>">
			<span class="icon-picture icon-white"></span> <?php echo JText::_('COM_KA_MOVIES_POSTERS'); ?>
		</a>
		<a href="index.php?option=com_kinoarhiv&view=mediamanager&section=<?php echo $this->section; ?>&type=<?php echo $this->type; ?>&tab=1&id=<?php echo $this->id; ?>&layoutview=<?php echo $this->layout; ?>" class="btn <?php echo ($this->tab == 1) ? 'btn-success' : ''; ?>">
			<span class="icon-picture icon-white"></span> <?php echo JText::_('COM_KA_MOVIES_WALLPP'); ?>
		</a>
	<?php elseif ($this->section == 'name'): ?>
		<a href="index.php?option=com_kinoarhiv&view=mediamanager&section=<?php echo $this->section; ?>&type=<?php echo $this->type; ?>&tab=3&id=<?php echo $this->id; ?>&layoutview=<?php echo $this->layout; ?>" class="btn <?php echo ($this->tab == 3) ? 'btn-success' : ''; ?>">
			<span class="icon-picture icon-white"></span> <?php echo JText::_('COM_KA_NAMES_GALLERY_PHOTO'); ?>
		</a>
		<a href="index.php?option=com_kinoarhiv&view=mediamanager&section=<?php echo $this->section; ?>&type=<?php echo $this->type; ?>&tab=2&id=<?php echo $this->id; ?>&layoutview=<?php echo $this->layout; ?>" class="btn <?php echo ($this->tab == 2) ? 'btn-success' : ''; ?>">
			<span class="icon-picture icon-white"></span> <?php echo JText::_('COM_KA_NAMES_GALLERY_POSTERS'); ?>
		</a>
		<a href="index.php?option=com_kinoarhiv&view=mediamanager&section=<?php echo $this->section; ?>&type=<?php echo $this->type; ?>&tab=1&id=<?php echo $this->id; ?>&layoutview=<?php echo $this->layout; ?>" class="btn <?php echo ($this->tab == 1) ? 'btn-success' : ''; ?>">
			<span class="icon-picture icon-white"></span> <?php echo JText::_('COM_KA_NAMES_GALLERY_WALLPP'); ?>
		</a>
	<?php endif; ?>
	</div>
	<div class="btn-group pull-left" style="margin: 0 10px 0 0;">
		<a href="index.php?option=com_kinoarhiv&view=mediamanager&section=<?php echo $this->section; ?>&type=<?php echo $this->type; ?>&tab=<?php echo $this->tab; ?>&id=<?php echo $this->id; ?>&layoutview=list" class="btn <?php echo ($this->layout == 'list') ? 'btn-success' : ''; ?>">
			<span class="icon-list icon-white"></span>
		</a>
		<a href="index.php?option=com_kinoarhiv&view=mediamanager&section=<?php echo $this->section; ?>&type=<?php echo $this->type; ?>&tab=<?php echo $this->tab; ?>&id=<?php echo $this->id; ?>&layoutview=thumb" class="btn <?php echo ($this->layout == 'thumb') ? 'btn-success' : ''; ?>">
			<span class="icon-grid icon-white"></span>
		</a>
	</div>
	<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
	<div class="clearfix"> </div>

	<?php
	if ($this->layout == 'thumb')
	{
		echo $this->loadTemplate('gallery_thumb');
	}
	else
	{
		echo $this->loadTemplate('gallery_list');
	}
	?>

	<?php echo $this->pagination->getListFooter(); ?>
	<?php echo $this->pagination->getResultsCounter(); ?>

	<input type="hidden" name="section" value="<?php echo $this->section; ?>" />
	<input type="hidden" name="type" value="<?php echo $this->type; ?>" />
	<input type="hidden" name="tab" value="<?php echo $this->tab; ?>" />
	<input type="hidden" name="id" value="<?php echo $this->id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="file_uploaded" value="0" />
	<?php echo JHtml::_('form.token'); ?>

<?php
echo JHtml::_(
	'bootstrap.renderModal',
	'imgModalUpload',
	array(
		'title'  => JText::_('COM_KA_TRAILERS_HEADING_UPLOAD_IMAGES'),
		'footer' => JLayoutHelper::render('layouts.edit.upload_image_footer', array(), JPATH_COMPONENT)
	),
	JLayoutHelper::render(
		'layouts.edit.upload_image_body',
		array(
			'view'          => $this,
			'params'        => $this->params,
			'remote_upload' => true,
			'remote_url'    => 'index.php?option=com_kinoarhiv&task=mediamanager.uploadRemote&format=json&section='
				. $this->section . '&type=' . $this->type . '&tab=' . $this->tab . '&id=' . $this->id
		),
		JPATH_COMPONENT
	)
);
echo JHtml::_(
	'bootstrap.renderModal',
	'copyfromModal',
	array(
		'title'  => JText::_('JTOOLBAR_COPYFROM'),
		'footer' => $this->loadTemplate('copyfrom_footer'),
		'modalWidth' => '50%'
	),
	$this->loadTemplate('copyfrom_body')
);
?>
</form>