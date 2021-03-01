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

if ($this->getLayout() !== 'modal')
{
	JHtml::_('formbehavior.chosen', 'select:not(.hasAutocomplete)');
	KAComponentHelperBackend::loadMediamanagerAssets();
	JHtml::_('stylesheet', 'media/com_kinoarhiv/css/select2.min.css');
	JHtml::_('script', 'media/com_kinoarhiv/js/select2.min.js');
	KAComponentHelper::getScriptLanguage('select2_locale_', 'media/com_kinoarhiv/js/i18n/select/');
}
else
{
	JHtml::_('script', 'media/system/js/core.js');
}
?>
<script type="text/javascript">
	jQuery(document).ready(function($){
		var tooltip_img = $('a.tooltip-img');

		tooltip_img.hover(function(e){
			$(this).next('img').stop().hide().fadeIn();
		}, function(e){
			$(this).next('img').stop().fadeOut();
		});

		<?php if ($this->getLayout() !== 'modal'): ?>
		tooltip_img.colorbox({ maxHeight: '95%', maxWidth: '95%', fixed: true });

		// Reload page if image files uploaded. Require hidden <input>
		$('#imgModalUpload').on('hidden', function(){
			if (parseInt($('input[name="file_uploaded"]').val(), 10) === 1) {
				document.location.reload();
			}
		});

		Joomla.submitbutton = function(task) {
			if (task === 'mediamanager.upload') {
				$('#imgModalUpload').modal('show');

				return false;
			} else if (task === 'mediamanager.copyfrom') {
				$('#copyfromModal').modal('show');

				return false;
			}

			Joomla.submitform(task);
		};
		<?php endif; ?>
	});
</script>

<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off">
<?php if ($this->getLayout() !== 'modal'):
	$url = 'index.php?option=com_kinoarhiv&view=mediamanager&section=' . $this->section . '&type=' . $this->type . '&id=' . $this->id;
	?>

	<div class="btn-group pull-left" style="margin: 0 10px 0 0;">

	<?php if ($this->section == 'movie'): ?>

		<a href="<?php echo $url; ?>&tab=3&layoutview=<?php echo $this->layout; ?>" class="btn <?php echo ($this->tab == 3) ? 'btn-success' : ''; ?>">
			<span class="icon-picture icon-white"></span> <?php echo JText::_('COM_KA_MOVIES_SCRSHOTS'); ?>
		</a>
		<a href="<?php echo $url; ?>&tab=2&layoutview=<?php echo $this->layout; ?>" class="btn <?php echo ($this->tab == 2) ? 'btn-success' : ''; ?>">
			<span class="icon-picture icon-white"></span> <?php echo JText::_('COM_KA_MOVIES_POSTERS'); ?>
		</a>
		<a href="<?php echo $url; ?>&tab=1&layoutview=<?php echo $this->layout; ?>" class="btn <?php echo ($this->tab == 1) ? 'btn-success' : ''; ?>">
			<span class="icon-picture icon-white"></span> <?php echo JText::_('COM_KA_MOVIES_WALLPP'); ?>
		</a>

	<?php elseif ($this->section == 'name'): ?>

		<a href="<?php echo $url; ?>&tab=3&layoutview=<?php echo $this->layout; ?>" class="btn <?php echo ($this->tab == 3) ? 'btn-success' : ''; ?>">
			<span class="icon-picture icon-white"></span> <?php echo JText::_('COM_KA_NAMES_GALLERY_PHOTO'); ?>
		</a>
		<a href="<?php echo $url; ?>&tab=2&layoutview=<?php echo $this->layout; ?>" class="btn <?php echo ($this->tab == 2) ? 'btn-success' : ''; ?>">
			<span class="icon-picture icon-white"></span> <?php echo JText::_('COM_KA_NAMES_GALLERY_POSTERS'); ?>
		</a>
		<a href="<?php echo $url; ?>&tab=1&layoutview=<?php echo $this->layout; ?>" class="btn <?php echo ($this->tab == 1) ? 'btn-success' : ''; ?>">
			<span class="icon-picture icon-white"></span> <?php echo JText::_('COM_KA_NAMES_GALLERY_WALLPP'); ?>
		</a>

	<?php elseif ($this->section == 'album'): ?>

		<a href="<?php echo $url; ?>&tab=1&layoutview=<?php echo $this->layout; ?>"
		   class="btn <?php echo ($this->tab == 0 || $this->tab == 1) ? 'btn-success' : ''; ?>">
			<span class="icon-picture icon-white"></span> <?php echo JText::_('COM_KA_FIELD_MUSIC_COVERS_FRONT'); ?>
		</a>
		<a href="<?php echo $url; ?>&tab=2&layoutview=<?php echo $this->layout; ?>"
		   class="btn <?php echo ($this->tab == 0 || $this->tab == 2) ? 'btn-success' : ''; ?>">
			<span class="icon-picture icon-white"></span> <?php echo JText::_('COM_KA_FIELD_MUSIC_COVERS_BACK'); ?>
		</a>
		<a href="<?php echo $url; ?>&tab=3&layoutview=<?php echo $this->layout; ?>"
		   class="btn <?php echo ($this->tab == 0 || $this->tab == 3) ? 'btn-success' : ''; ?>">
			<span class="icon-picture icon-white"></span> <?php echo JText::_('COM_KA_FIELD_MUSIC_COVERS_ARTIST'); ?>
		</a>
		<a href="<?php echo $url; ?>&tab=4&layoutview=<?php echo $this->layout; ?>"
		   class="btn <?php echo ($this->tab == 0 || $this->tab == 4) ? 'btn-success' : ''; ?>">
			<span class="icon-picture icon-white"></span> <?php echo JText::_('COM_KA_FIELD_MUSIC_COVERS_DISC'); ?>
		</a>

	<?php endif; ?>

	</div>
	<div class="btn-group pull-left" style="margin: 0 10px 0 0;">
		<a href="<?php echo $url; ?>&tab=<?php echo $this->tab; ?>&layoutview=list"
		   class="btn <?php echo ($this->layout == 'list') ? 'btn-success' : ''; ?>">
			<span class="icon-list icon-white"></span>
		</a>
		<a href="<?php echo $url; ?>&tab=<?php echo $this->tab; ?>&layoutview=thumb"
		   class="btn <?php echo ($this->layout == 'thumb') ? 'btn-success' : ''; ?>">
			<span class="icon-grid icon-white"></span>
		</a>
	</div>

	<?php
	echo JHtml::_(
		'bootstrap.renderModal',
		'collapseModal',
		array(
			'title' => JText::_('COM_KA_BATCH_OPTIONS'),
			'footer' => $this->loadTemplate('batch_footer')
		),
		$this->loadTemplate('batch_body')
	);

	if ($this->section == 'album'):
		echo JHtml::_(
			'bootstrap.renderModal',
			'importAlbumImageModal',
			array(
				'title' => JText::_('JLIB_HTML_TOOLBAR_IMPORT_IMAGES_TITLE'),
				'footer' => $this->loadTemplate('import_album_images_footer')
			),
			$this->loadTemplate('import_album_images_body')
		);
	endif;
endif; ?>

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
	<input type="hidden" name="id" class="album_id" value="<?php echo $this->id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="file_uploaded" value="0" />
	<?php echo JHtml::_('form.token'); ?>

<?php
if ($this->getLayout() !== 'modal'):
	echo JHtml::_(
		'bootstrap.renderModal',
		'imgModalUpload',
		array(
			'title'  => JText::_('COM_KA_TRAILERS_UPLOAD_IMAGES'),
			'footer' => JLayoutHelper::render('layouts.edit.upload_file_footer', array(), JPATH_COMPONENT)
		),
		JLayoutHelper::render(
			'layouts.edit.upload_image',
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
endif;
?>
</form>

<?php if ($this->getLayout() !== 'modal'): ?>
<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" id="copyForm" autocomplete="off">
	<?php
	echo JHtml::_(
		'bootstrap.renderModal',
		'copyfromModal',
		array(
			'title'  => JText::_('JTOOLBAR_COPYFROM'),
			'footer' => $this->loadTemplate('copyfrom_footer')
		),
		$this->loadTemplate('copyfrom_body')
	);
	?>
</form>
<?php endif;
