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

$data = $displayData;

if (isset($data['url']) && !empty($data['url']))
{
	$url = $data['url'];
}
else
{
	$url = 'index.php?option=com_kinoarhiv&task=mediamanager.upload&format=json&section=' . $data['view']->section
		. '&type=' . $data['view']->type . '&tab=' . $data['view']->tab . '&id=' . $data['view']->id . '&upload=images';
}

$remote_upload = false;
$remote_url = '';

if (isset($data['refresh']) && !empty($data['refresh']))
{
	$refresh = json_encode($data['refresh']);
}
else
{
	$refresh = '[]';
}

if (isset($data['remote_upload']) && $data['remote_upload'] === true)
{
	$remote_upload = true;
	$remote_url = $data['remote_url'];
}

$max_files = isset($data['max_files']) && !empty($data['max_files']) ? (int) $data['max_files'] : '';
?>
<div>
	<?php echo JHtml::_('bootstrap.startTabSet', 'upload_tab', array('active' => 'local')); ?>
	<?php echo JHtml::_('bootstrap.addTab', 'upload_tab', 'local', JText::_('COM_KA_TRAILERS_UPLOAD_IMAGE_LOCAL')); ?>

	<input type="hidden" autofocus="autofocus" />
	<?php
	echo JLayoutHelper::render(
		'layouts.edit.upload_file_body',
		array(
			'params' => $data['params'],
			'url' => $url,
			'max_files' => $max_files,
			'multipart_params' => '{"' . JSession::getFormToken() . '": 1}',
			'content-type' => isset($data['content-type']) && !empty($data['content-type']) ? $data['content-type'] : 'images',
			'max_file_size' => $data['params']->get('upload_limit'),
			'multiple_queues' => 'true',
			'filters' => '{"title": "Images", "extensions": "' . $data['params']->get('upload_mime_images') . '"}',
			'chunk_size' => $data['params']->get('upload_chunk_size')
		),
		JPATH_COMPONENT
	);
	?>

	<?php echo JHtml::_('bootstrap.endTab'); ?>
	<?php if ($remote_upload):
		echo JHtml::_('bootstrap.addTab', 'upload_tab', 'remote', JText::_('COM_KA_TRAILERS_UPLOAD_IMAGE_REMOTE')); ?>

		<fieldset class="form-horizontal">
			<div class="control-group">
				<div class="control-label">
					<label for="remote_urls" class="hasPopover" title="<?php echo JText::_('COM_KA_TRAILERS_UPLOAD_IMAGE_URL'); ?>" data-content="<?php echo JText::_('COM_KA_TRAILERS_UPLOAD_IMAGE_URL_HELP'); ?>"><?php echo JText::_('COM_KA_TRAILERS_UPLOAD_IMAGE_URL'); ?></label>
				</div>
				<div class="controls">
					<textarea name="remote_urls" id="remote_urls" rows="7" cols="32" class="span12" spellcheck="false" data-url="<?php echo $remote_url; ?>"></textarea>
				</div>
			</div>
			<input type="button" class="btn btn-success cmd-remote-urls" value="<?php echo JText::_('JTOOLBAR_UPLOAD'); ?>" />
		</fieldset>

		<?php
		echo JHtml::_('bootstrap.endTab');
	endif; ?>
	<?php echo JHtml::_('bootstrap.endTabSet'); ?>

	<input type="hidden" name="refresh" value='<?php echo $refresh; ?>'/>
</div>
