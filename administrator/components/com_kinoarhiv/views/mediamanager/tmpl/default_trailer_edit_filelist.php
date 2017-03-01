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

jimport('components.com_kinoarhiv.helpers.content', JPATH_ROOT);
KAComponentHelperBackend::loadMediamanagerAssets();

$video_files = json_decode($this->form->getValue('trailer.video'));
$total_video_files = !empty($video_files) ? count($video_files) : 0;
$subtitle_files = json_decode($this->form->getValue('trailer.subtitles'));
$total_subtitle_files = !empty($subtitle_files) ? count($subtitle_files) : 0;
$chapter_files = json_decode($this->form->getValue('trailer.chapters'));
$total_chapter_files = !empty($chapter_files) ? count($chapter_files) : 0;
?>
<table class="table table-striped table-condensed filelist"
	   data-sort-url="index.php?option=com_kinoarhiv&task=mediamanager.saveOrderTrailerFiles&format=json&item_id=<?php echo $this->trailer_id; ?>&type=video"
	   data-list="video">
	<thead>
		<tr>
			<th colspan="3">
				<?php echo JText::_('COM_KA_TRAILERS_HEADING_UPLOAD_FILES_VIDEO'); ?>
				<span class="btn-small hasTooltip icon-help" title="<?php echo JText::_('COM_KA_TRAILERS_HEADING_VIDEOFILES_DESC'); ?>"></span>
			</th>
			<th width="12%">
				<div class="pull-right">
					<a href="index.php?option=com_kinoarhiv&task=mediamanager.getTrailerFiles&id=<?php echo $this->trailer_id; ?>&data=screenshot,video&format=json" class="cmd-refresh-filelist hasTooltip" title="<?php echo JText::_('JTOOLBAR_REFRESH'); ?>"><span class="icon-refresh"></span></a>&nbsp;<a href="index.php?option=com_kinoarhiv&task=mediamanager.removeTrailerFiles&type=screenshot,video&id=<?php echo $this->id; ?>&item_id=<?php echo $this->trailer_id; ?>&all=1&format=json" class="cmd-remove-file all hasTooltip" title="<?php echo JText::_('COM_KA_DELETE_ALL'); ?>"><span class="icon-delete"></span></a>
				</div>
			</th>
		</tr>
	</thead>
	<tbody>
	<?php if ($total_video_files == 0): ?>
		<tr>
			<td colspan="4"><?php echo JText::_('COM_KA_NO_FILES'); ?></td>
		</tr>
	<?php else:
		foreach ($video_files as $key => $item):
			$file_info = KAContentHelper::formatItemTitle($item->type, $item->resolution, '', ', ');
			$file_info_text = $file_info != "" ? ' <span class="gray">(' . $file_info . ')</span>': '';
			$filename_class = !$item->is_file ? ' red' : '';
		?>
		<tr>
			<td width="1%" class="ord_numbering">
				<span class="sortable-handler<?php echo $total_video_files < 2 ? ' inactive tip-top' : ''; ?>"><i class="icon-menu"></i></span>
				<input type="hidden" name="ord[]" value="<?php echo (int) $key; ?>" />
			</td>
			<td width="4%"><?php echo (int) $key; ?></td>
			<td class="item-row">
				<span class="more<?php echo $filename_class; ?>"><?php echo $item->src; ?></span><?php echo $file_info_text; ?>
			</td>
			<td width="12%">
				<div class="pull-right">
					<a href="index.php?option=com_kinoarhiv&task=mediamanager.editTrailerFile&type=video&id=<?php echo $this->id; ?>&item_id=<?php echo $this->trailer_id; ?>&item=<?php echo (int) $key; ?>&format=raw" class="cmd-file-edit"><span class="icon-pencil"></span></a>&nbsp;<a href="index.php?option=com_kinoarhiv&task=mediamanager.removeTrailerFiles&type=video&id=<?php echo $this->id; ?>&item_id=<?php echo $this->trailer_id; ?>&item=<?php echo (int) $key; ?>&format=json" class="cmd-remove-file"><span class="icon-delete"></span></a>
				</div>
			</td>
		</tr>
		<?php endforeach;
	endif; ?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="3" class="screenshot">
			<?php $screenshot = $this->form->getValue('trailer.screenshot');
			if (!empty($screenshot)): ?>

				<div class="item-row">
					<?php if (is_file($this->folder_path . $this->form->getValue('trailer.screenshot'))): ?>

					<a href="<?php echo $this->folder_path_www . $screenshot; ?>?_=<?php echo time(); ?>" id="screenshot_file" class="more"><?php echo $screenshot; ?></a>

					<?php else: ?>

					<a href="<?php echo $this->folder_path_www . $screenshot; ?>?_=<?php echo time(); ?>" id="screenshot_file" class="more error_image"><?php echo $screenshot; ?></a>

					<?php endif; ?>
				</div>

			<?php endif; ?>
			</td>
			<td width="12%">
				<div class="pull-right">
					<a href="#createScreenshotModal" data-toggle="modal" class="hasTooltip" title="<?php echo JText::_('COM_KA_TRAILERS_VIDEO_SCREENSHOT_CREATE_TITLE'); ?>"><span class="icon-refresh"></span></a>
					<a href="index.php?option=com_kinoarhiv&task=mediamanager.editTrailerFile&type=screenshot&id=<?php echo $this->id; ?>&item_id=<?php echo $this->trailer_id; ?>&item=0&format=raw" data-type="image" class="cmd-file-edit"><span class="icon-pencil"></span></a>
					<a href="index.php?option=com_kinoarhiv&task=mediamanager.removeTrailerFiles&type=screenshot&id=<?php echo $this->id; ?>&item_id=<?php echo $this->trailer_id; ?>&format=json" data-type="image" class="cmd-remove-file"><span class="icon-delete"></span></a>
				</div>
			</td>
		</tr>
		<tr>
			<td colspan="4">
				<a href="index.php?option=com_kinoarhiv&task=mediamanager.editTrailerFile&type=video&id=<?php echo $this->id; ?>&item_id=<?php echo $this->trailer_id; ?>&item=0&new=1&format=raw" class="cmd-file-edit"><span class="icon-plus"></span><?php echo JText::_('JTOOLBAR_ADD'); ?></a>&nbsp;&nbsp;<a href="#" class="cmd-upload" data-upload-tab="video"><span class="icon-upload"></span><?php echo JText::_('COM_KA_TRAILERS_VIDEO_UPLOAD_TITLE'); ?></a>&nbsp;&nbsp;<a href="#" class="cmd-upload" data-upload-tab="screenshot"><span class="icon-upload"></span><?php echo JText::_('COM_KA_TRAILERS_VIDEO_SCREENSHOT_UPLOAD_TITLE'); ?></a>
			</td>
		</tr>
	</tfoot>
</table>

<table class="table table-striped table-condensed filelist"
       data-sort-url="index.php?option=com_kinoarhiv&task=mediamanager.saveOrderTrailerFiles&format=json&item_id=<?php echo $this->trailer_id; ?>&type=subtitles"
       data-list="subtitles">
	<thead>
		<tr>
			<th colspan="4">
				<?php echo JText::_('COM_KA_TRAILERS_SUBTITLES'); ?>
				<span class="btn-small hasTooltip icon-help" title="<?php echo JText::_('COM_KA_TRAILERS_HEADING_VIDEOFILES_DESC'); ?>"></span>
			</th>
			<th width="9%">
				<div class="pull-right">
					<a href="index.php?option=com_kinoarhiv&task=mediamanager.getTrailerFiles&id=<?php echo $this->trailer_id; ?>&data=subtitles&format=json" class="cmd-refresh-filelist hasTooltip" title="<?php echo JText::_('JTOOLBAR_REFRESH'); ?>"><span class="icon-refresh"></span></a>&nbsp;<a href="index.php?option=com_kinoarhiv&task=mediamanager.removeTrailerFiles&type=subtitles&id=<?php echo $this->id; ?>&item_id=<?php echo $this->trailer_id; ?>&all=1&format=json" class="cmd-remove-file all hasTooltip" title="<?php echo JText::_('COM_KA_DELETE_ALL'); ?>"><span class="icon-delete"></span></a>
				</div>
			</th>
		</tr>
	</thead>
	<tbody>
	<?php if ($total_subtitle_files == 0): ?>
		<tr>
			<td colspan="5"><?php echo JText::_('COM_KA_NO_FILES'); ?></td>
		</tr>
	<?php else:
		foreach ($subtitle_files as $key => $item):
			$filename_class = !$item->is_file ? ' red' : '';
		?>
			<tr>
				<td width="1%" class="ord_numbering">
					<span class="sortable-handler<?php echo $total_subtitle_files < 2 ? ' inactive tip-top' : ''; ?>"><i class="icon-menu"></i></span>
					<input type="hidden" name="ord[]" value="<?php echo (int) $key; ?>" />
				</td>
				<td width="4%"><?php echo (int) $key; ?></td>
				<td class="item-row">
					<span class="more<?php echo $filename_class; ?>"><?php echo $item->file; ?></span><?php if (!empty($item->lang)): ?> <span class="gray">(<?php echo $item->lang; ?>)</span><?php endif; ?>
				</td>
				<td width="4%">
				<?php if ($item->default): ?>
					<a href="index.php?option=com_kinoarhiv&task=mediamanager.subtitleUnsetDefault&item_id=<?php echo $this->trailer_id; ?>&id=<?php echo $this->id; ?>&item=<?php echo (int) $key; ?>&format=json"
					   class="btn btn-micro cmd-subtitle-default"><span class="icon-featured"></span></a>
				<?php else: ?>
					<a href="index.php?option=com_kinoarhiv&task=mediamanager.subtitleSetDefault&item_id=<?php echo $this->trailer_id; ?>&id=<?php echo $this->id; ?>&item=<?php echo (int) $key; ?>&format=json"
					   class="btn btn-micro cmd-subtitle-default"><span class="icon-unfeatured"></span></a>
				<?php endif; ?>
				</td>
				<td>
					<div class="pull-right">
						<a href="index.php?option=com_kinoarhiv&task=mediamanager.editTrailerFile&type=subtitles&id=<?php echo $this->id; ?>&item_id=<?php echo $this->trailer_id; ?>&item=<?php echo (int) $key; ?>&format=raw" class="cmd-file-edit"><span class="icon-pencil"></span></a>&nbsp;<a href="index.php?option=com_kinoarhiv&task=mediamanager.removeTrailerFiles&type=subtitles&id=<?php echo $this->id; ?>&item_id=<?php echo $this->trailer_id; ?>&item=<?php echo (int) $key; ?>&format=json" class="cmd-remove-file"><span class="icon-delete"></span></a>
					</div>
				</td>
			</tr>
		<?php endforeach;
	endif; ?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="5">
				<a href="index.php?option=com_kinoarhiv&task=mediamanager.editTrailerFile&type=subtitles&id=<?php echo $this->id; ?>&item_id=<?php echo $this->trailer_id; ?>&item=0&new=1&format=raw" class="cmd-file-edit"><span class="icon-plus"></span><?php echo JText::_('JTOOLBAR_ADD'); ?></a>&nbsp;&nbsp;<a href="#" class="cmd-upload" data-upload-tab="subtitles"><span class="icon-upload"></span><?php echo JText::_('COM_KA_TRAILERS_UPLOAD_FILES_SUBTITLES'); ?></a>
			</td>
		</tr>
	</tfoot>
</table>

<table class="table table-striped table-condensed filelist"
       data-sort-url="index.php?option=com_kinoarhiv&task=mediamanager.saveOrderTrailerFiles&format=json&item_id=<?php echo $this->trailer_id; ?>&type=chapters"
       data-list="chapters">
	<thead>
		<tr>
			<th><?php echo JText::_('COM_KA_TRAILERS_CHAPTERS'); ?></th>
			<th width="9%">
				<div class="pull-right">
					<a href="index.php?option=com_kinoarhiv&task=mediamanager.getTrailerFiles&id=<?php echo $this->trailer_id; ?>&data=chapters&format=json" class="cmd-refresh-filelist hasTooltip" title="<?php echo JText::_('JTOOLBAR_REFRESH'); ?>"><span class="icon-refresh"></span></a>
				</div>
			</th>
		</tr>
	</thead>
	<tbody>
	<?php if ($total_chapter_files == 0): ?>
		<tr>
			<td colspan="2"><?php echo JText::_('COM_KA_NO_FILES'); ?></td>
		</tr>
	<?php else:
		$filename_class = !$chapter_files->is_file ? ' red' : '';
		?>
		<tr>
			<td class="item-row"><span class="more<?php echo $filename_class; ?>"><?php echo $chapter_files->file; ?></span></td>
			<td width="9%">
				<div class="pull-right">
					<a href="index.php?option=com_kinoarhiv&task=mediamanager.editTrailerFile&type=chapters&id=<?php echo $this->id; ?>&item_id=<?php echo $this->trailer_id; ?>&item=0&format=raw" class="cmd-file-edit"><span class="icon-pencil"></span></a>&nbsp;<a href="index.php?option=com_kinoarhiv&task=mediamanager.removeTrailerFiles&type=chapters&id=<?php echo $this->id; ?>&item_id=<?php echo $this->trailer_id; ?>&item=0&format=json" class="cmd-remove-file"><span class="icon-delete"></span></a>
				</div>
			</td>
		</tr>
	<?php endif; ?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="2">
				<a href="index.php?option=com_kinoarhiv&task=mediamanager.editTrailerFile&type=chapters&id=<?php echo $this->id; ?>&item_id=<?php echo $this->trailer_id; ?>&item=0&new=1&format=raw" class="cmd-file-edit"><span class="icon-plus"></span><?php echo JText::_('JTOOLBAR_ADD'); ?></a>&nbsp;&nbsp;<a href="#" class="cmd-upload" data-upload-tab="chapters"><span class="icon-upload"></span><?php echo JText::_('COM_KA_TRAILERS_UPLOAD_FILES_CHAPTERS'); ?></a>
			</td>
		</tr>
	</tfoot>
</table>

<?php
echo JHtml::_(
	'bootstrap.renderModal',
	'uploadVideoModal',
	array(
		'title'  => JText::_('COM_KA_LOADING'),
		'footer' => JLayoutHelper::render('layouts.edit.upload_file_footer', array(), JPATH_COMPONENT)
	),
	$this->loadTemplate('trailer_upload_body')
);
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
			'url'           => 'index.php?option=com_kinoarhiv&task=mediamanager.upload&format=json&section=' . $this->section
				. '&type=' . $this->type . '&tab=' . $this->tab . '&id=' . $this->id . '&item_id=' . $this->trailer_id . '&upload=images',
			'params'        => $this->params,
			'content-type'  => 'screenshot',
			'refresh'       => array('el_parent' => 'table[data-list="video"]', 'el_trigger' => '.cmd-refresh-filelist'),
			'max_files'     => 1,
			'remote_upload' => true,
			'remote_url'    => 'index.php?option=com_kinoarhiv&task=mediamanager.uploadRemote&format=json&section='
				. $this->section . '&type=' . $this->type . '&tab=' . $this->tab . '&id=' . $this->id . '&item_id='
				. $this->trailer_id . '&max_files=1'
		),
		JPATH_COMPONENT
	)
);
echo JHtml::_(
	'bootstrap.renderModal',
	'editFileModal',
	array(
		'title'  => JText::_('COM_KA_LOADING'),
		'footer' => $this->loadTemplate('trailer_edit_fileinfo_footer'),
		'modalWidth' => '50%'
	)
);
echo JHtml::_(
	'bootstrap.renderModal',
	'createScreenshotModal',
	array(
		'title'  => JText::_('COM_KA_TRAILERS_VIDEO_SCREENSHOT_CREATE_TITLE'),
		'footer' => $this->loadTemplate('trailer_edit_create_scr_footer'),
		'modalWidth' => '50%'
	),
	$this->loadTemplate('trailer_edit_create_scr_body')
);
