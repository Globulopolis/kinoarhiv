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

use Joomla\String\StringHelper;

$headingModalVideo = JText::_('JTOOLBAR_ADD') . ' ' . StringHelper::strtolower(JText::_('COM_KA_TRAILERS_HEADING_UPLOAD_FILES_VIDEO'));
$headingModalSubs  = JText::_('JTOOLBAR_ADD') . ' ' . StringHelper::strtolower(JText::_('COM_KA_TRAILERS_SUBTITLES'));
$headingModalChap  = JText::_('JTOOLBAR_ADD') . ' ' . StringHelper::strtolower(JText::_('COM_KA_TRAILERS_CHAPTERS'));
?>
<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv&id=' . $this->id); ?>" method="post"
	  name="adminForm" autocomplete="off" id="item-form" class="form-validate">
	<!-- At this first hidden input we will remove autofocus -->
	<input type="hidden" autofocus="autofocus"/>

	<div class="form-horizontal">
	<?php foreach ($this->form->getFieldset('trailer_edit') as $field):
		if (strtolower($field->type) != 'hidden'): ?>

		<div class="control-group">
			<div class="control-label"><?php echo $field->label; ?></div>
			<div class="controls">
			<?php if ($field->name == 'form[trailer][urls]'): ?>
				<div class="urls_form_toolbar">
					<a href="#urlsVideoModal" title="<?php echo $headingModalVideo; ?>" class="hasTooltip" data-toggle="modal">
						<img src="<?php echo JUri::root(); ?>media/com_kinoarhiv/images/icons/film.png" border="0"/>
					</a>
					<a href="#urlsSubtitlesModal" title="<?php echo $headingModalSubs; ?>" class="hasTooltip" data-toggle="modal">
						<img src="<?php echo JUri::root(); ?>media/com_kinoarhiv/images/icons/subtitles.png" border="0"/>
					</a>
					<a href="#urlsChaptersModal" title="<?php echo $headingModalChap; ?>" class="hasTooltip" data-toggle="modal">
						<img src="<?php echo JUri::root(); ?>media/com_kinoarhiv/images/icons/timeline_marker.png" border="0"/>
					</a>
					<a href="#urlsHelpModal" title="<?php echo JText::_('JHELP'); ?>" class="hasTooltip" data-toggle="modal">
						<span class="icon-help"></span>
					</a>
				</div>
			<?php endif; ?>
				<?php echo $field->input; ?>
			</div>
		</div>

		<?php else:
			echo $field->input . "\n\t";
		endif;
	endforeach; ?>
	</div>

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="section" value="movie"/>
	<input type="hidden" name="type" value="trailers"/>
	<input type="hidden" name="id" value="<?php echo $this->id; ?>"/>
	<input type="hidden" name="file_uploaded" value="0"/>
	<?php echo JHtml::_('form.token'); ?>
</form>

<?php
echo JHtml::_(
	'bootstrap.renderModal',
	'urlsVideoModal',
	array(
		'title' => $headingModalVideo,
		'footer' => $this->loadTemplate('trailer_edit_urls_video_footer'),
		'modalWidth' => 50
	),
	$this->loadTemplate('trailer_edit_urls_video_body')
);

echo JHtml::_(
	'bootstrap.renderModal',
	'urlsSubtitlesModal',
	array(
		'title' => $headingModalSubs,
		'footer' => $this->loadTemplate('trailer_edit_urls_subtitles_footer'),
		'modalWidth' => 50
	),
	$this->loadTemplate('trailer_edit_urls_subtitles_body')
);

echo JHtml::_(
	'bootstrap.renderModal',
	'urlsChaptersModal',
	array(
		'title' => $headingModalChap,
		'footer' => $this->loadTemplate('trailer_edit_urls_chapters_footer'),
		'modalWidth' => 50
	),
	$this->loadTemplate('trailer_edit_urls_chapters_body')
);

echo JHtml::_(
	'bootstrap.renderModal',
	'urlsHelpModal',
	array(
		'title' => JText::_('JHELP'),
		'footer' => '<a class="btn" type="button" data-dismiss="modal">' . JText::_('COM_KA_CLOSE') . '</a>'
	),
	'<div class="container-fluid">' . JText::_('COM_KA_TRAILERS_UPLOAD_URLS_HELP') . '</div>'
);
