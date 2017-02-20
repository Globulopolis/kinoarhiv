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
$attr = array();
$attr[] = array_key_exists('chunk_size', $data) ? ' data-chunk_size="' . $data['chunk_size'] . '"' : '';
$attr[] = array_key_exists('file_data_name', $data) ? ' data-file_data_name="' . (string) $data['file_data_name'] . '"' : '';
$attr[] = array_key_exists('filters', $data) ? " data-filters='" . $data['filters'] . "'" : '';
$attr[] = array_key_exists('flash_swf_url', $data) ? ' data-flash_swf_url="' . (string) $data['flash_swf_url'] . '"' : '';
$attr[] = array_key_exists('headers', $data) ? " data-headers='" . $data['headers'] . "'" : '';
$attr[] = array_key_exists('max_file_size', $data) ? ' data-max_file_size="' . $data['max_file_size'] . '"' : '';
$attr[] = array_key_exists('max_retries', $data) ? ' data-max_retries="' . (int) $data['max_retries'] . '"' : '';
$attr[] = array_key_exists('multipart', $data) ? ' data-multipart="' . (bool) $data['multipart'] . '"' : '';
$attr[] = array_key_exists('multipart_params', $data) ? " data-multipart_params='" . $data['multipart_params'] . "'" : '';
$attr[] = array_key_exists('multi_selection', $data) ? ' data-multi_selection="' . (bool) $data['multi_selection'] . '"' : '';
$attr[] = array_key_exists('prevent_duplicates', $data) ? ' data-prevent_duplicates="' . (bool) $data['prevent_duplicates'] . '"' : '';
$attr[] = array_key_exists('required_features', $data) ? ' data-required_features="' . $data['required_features'] . '"' : '';
$attr[] = array_key_exists('resize', $data) ? ' data-resize="' . $data['resize'] . '"' : '';
$attr[] = array_key_exists('width', $data) ? ' data-width="' . $data['width'] . '"' : '';
$attr[] = array_key_exists('height', $data) ? ' data-height="' . $data['height'] . '"' : '';
$attr[] = array_key_exists('quality', $data) ? ' data-quality="' . $data['quality'] . '"' : '';
$attr[] = array_key_exists('crop', $data) ? ' data-crop="' . (bool) $data['crop'] . '"' : '';
$attr[] = array_key_exists('runtimes', $data) ? ' data-runtimes="' . (string) $data['runtimes'] . '"' : '';
$attr[] = array_key_exists('silverlight_xap_url', $data) ? ' data-silverlight_xap_url="' . (string) $data['silverlight_xap_url'] . '"' : '';
$attr[] = array_key_exists('unique_names', $data) ? ' data-unique_names="' . (bool) $data['unique_names'] . '"' : '';
$attr[] = array_key_exists('dragdrop', $data) ? ' data-dragdrop="' . (bool) $data['dragdrop'] . '"' : '';
$attr[] = array_key_exists('rename', $data) ? ' data-rename="' . (bool) $data['rename'] . '"' : '';
$attr[] = array_key_exists('multiple_queues', $data) ? ' data-multiple_queues="true"' : 'data-multiple_queues="false"';

$attr[] = array_key_exists('content-type', $data) ? ' data-content_type="' . (string) $data['content-type'] . '"' : '';
$attr[] = array_key_exists('max_files', $data) && !empty($data['max_files']) ? ' data-max_files="' . (int) $data['max_files'] . '"' : '';
?>
<div>
	<input type="hidden" autofocus="autofocus" />
	<div class="hasUploader" data-url="<?php echo $data['url']; ?>" <?php echo implode('', $attr); ?>>
		<p>You browser doesn't have Flash, Silverlight or HTML5 support.</p>
	</div>
	<div class="uploader-info" style="display: none;">
		<button type="button" class="close" data-dismiss="alert">&times;</button>
	</div>

<?php if (array_key_exists('info', $data) && !empty($data['info'])): ?>

	<div class="alert alert-info">
	<?php if (array_key_exists('close', $data['info']) && $data['info']['close']): ?>
		<button type="button" class="close" data-dismiss="alert">&times;</button>
	<?php endif; ?>
		<?php echo $data['info']['text']; ?>
	</div>

<?php endif; ?>
</div>
