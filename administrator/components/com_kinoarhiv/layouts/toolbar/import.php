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

JHtml::_('behavior.core');

/** @var array $displayData */
$title = $displayData['title'];
?>
<button type="button" data-toggle="modal" onclick="jQuery('#importAlbumImageModal').modal('show'); return true;" class="btn btn-small">
	<span class="icon-checkbox-partial" aria-hidden="true"></span>
	<?php echo $title; ?>
</button>
