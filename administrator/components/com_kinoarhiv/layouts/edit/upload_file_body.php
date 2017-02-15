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
$multipart = array_key_exists('multipart_params', $data) ? " data-multipart_params='" . $data['multipart_params'] . "'" : '';
$content_type = array_key_exists('content-type', $data) ? ' data-content-type="' . $data['content-type'] . '"' : '';
$max_file_size = array_key_exists('max_file_size', $data) ? ' data-max_file_size="' . $data['max_file_size'] . '"' : '';
$multiple_queues = array_key_exists('multiple_queues', $data) ? ' data-multiple_queues="' . $data['multiple_queues'] . '"' : '';
$filters = array_key_exists('filters', $data) ? " data-filters='" . $data['filters'] . "'" : '';
$chunk_size = array_key_exists('chunk_size', $data) ? ' data-chunk_size="' . $data['chunk_size'] . '"' : '';
?>
<div>
	<input type="hidden" autofocus="autofocus" />
	<div class="hasUploader" data-url="<?php echo $data['url']; ?>"
	<?php
		echo $multipart . $content_type . $max_file_size . $multiple_queues . $filters . $chunk_size;

	?>>
		<p>You browser doesn't have Flash, Silverlight or HTML5 support.</p>
	</div>

	<?php if (array_key_exists('info', $data) && !empty($data['info'])): ?>

	<div class="alert alert-info"><?php echo $data['info']; ?></div>

	<?php endif; ?>
</div>
