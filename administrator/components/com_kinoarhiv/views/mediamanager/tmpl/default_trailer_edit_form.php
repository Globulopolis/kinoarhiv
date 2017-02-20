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

use Joomla\String\StringHelper;

$heading_video_modal = JText::_('JTOOLBAR_ADD') . ' ' . StringHelper::strtolower(JText::_('COM_KA_TRAILERS_HEADING_UPLOAD_FILES_VIDEO'));
$heading_subtl_video = JText::_('JTOOLBAR_ADD') . ' ' . StringHelper::strtolower(JText::_('COM_KA_TRAILERS_SUBTITLES'));
$heading_chapt_video = JText::_('JTOOLBAR_ADD') . ' ' . StringHelper::strtolower(JText::_('COM_KA_TRAILERS_CHAPTERS'));
?>
<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv&id=' . $this->id); ?>" method="post" name="adminForm" autocomplete="off" id="item-form" class="form-validate">
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
					<a href="#urlsVideoModal" title="<?php echo $heading_video_modal; ?>" class="hasTooltip" data-toggle="modal">
						<img src="<?php echo JUri::base(); ?>components/com_kinoarhiv/assets/images/icons/film.png" border="0"/>
					</a>
					<a href="#urlsSubtitlesModal" title="<?php echo $heading_subtl_video; ?>" class="hasTooltip" data-toggle="modal">
						<img src="<?php echo JUri::base(); ?>components/com_kinoarhiv/assets/images/icons/subtitles.png" border="0"/>
					</a>
					<a href="#urlsChaptersModal" title="<?php echo $heading_chapt_video; ?>" class="hasTooltip" data-toggle="modal">
						<img src="<?php echo JUri::base(); ?>components/com_kinoarhiv/assets/images/icons/timeline_marker.png" border="0"/>
					</a>
					<a href="#urlsHelpModal" title="<?php echo JText::_('JHELP'); ?>" class="hasTooltip" data-toggle="modal">
						<img src="<?php echo JUri::base(); ?>components/com_kinoarhiv/assets/images/icons/help.png" border="0"/>
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
		'title' => $heading_video_modal,
		'footer' => $this->loadTemplate('trailer_edit_urls_video_footer'),
		'modalWidth' => 50
	),
	$this->loadTemplate('trailer_edit_urls_video_body')
);

echo JHtml::_(
	'bootstrap.renderModal',
	'urlsSubtitlesModal',
	array(
		'title' => $heading_subtl_video,
		'footer' => $this->loadTemplate('trailer_edit_urls_subtitles_footer'),
		'modalWidth' => 50
	),
	$this->loadTemplate('trailer_edit_urls_subtitles_body')
);

echo JHtml::_(
	'bootstrap.renderModal',
	'urlsChaptersModal',
	array(
		'title' => $heading_chapt_video,
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
	JText::_('COM_KA_TRAILERS_UPLOAD_URLS_HELP')
);
?>

