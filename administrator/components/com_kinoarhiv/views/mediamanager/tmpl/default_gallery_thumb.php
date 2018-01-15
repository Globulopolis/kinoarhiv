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

$user      = JFactory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$columns   = 2;
$canChange = $user->authorise('core.edit.state', 'com_kinoarhiv.' . $this->section . '.' . $this->id);
?>
<div>
	<table class="table table-striped gallery-list" id="articleList">
		<thead>
			<tr>
				<th width="1%"><?php echo JHtml::_('grid.checkall'); ?></th>
				<th>&nbsp;</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td colspan="<?php echo $columns; ?>" style="padding-top: 15px;">

				<?php
				if (count($this->items) == 0):
					echo JText::_('COM_KA_NO_FILES');
				else:
				?>

					<ul class="thumbnails gallery-list">

					<?php foreach ($this->items as $i => $item):
						$dimension = explode('x', $item->dimension);
						$height    = 120;
						$width     = floor(($height * $dimension[0]) / $dimension[1]);
						?>
						<li class="span2">
							<div class="thumbnail">
								<div class="center">
									<a href="<?php echo $item->filepath; ?>" class="img-preview" rel="group_<?php echo $this->tab; ?>"><img src="<?php echo $item->th_filepath; ?>" alt="<?php echo $item->dimension; ?>" style="width: <?php echo $width; ?>px; height: <?php echo $height; ?>px;" /></a>
								</div>
								<div class="caption">
									<p>
										<span><?php echo $item->dimension; ?></span>

										<?php if (!empty($item->error)): ?>
											<a href="#" class="hasTooltip error_image" title="<?php echo $item->error; ?>"></a>
										<?php endif; ?>
										<?php if ($item->th_filepath != ''): ?>
											<img src="<?php echo $item->th_filepath; ?>" class="tooltip-img-content" />
										<?php endif; ?>
										<?php if ($item->folderpath != ''): ?>
											<br /><span class="small gray more"><?php echo $item->folderpath . $item->filename; ?></span>
										<?php endif; ?>
									</p>
									<div>
										<div style="display: inline;"><?php echo JHtml::_('grid.id', $i, $item->id, false, 'item_id'); ?></div>
										<div class="btn-group" style="margin: 3px 0 0 3px;">
											<?php
											echo JHtml::_('jgrid.published', $item->state, $i, 'mediamanager.', $canChange, 'cb');

											if (($this->section == 'movie' && $this->tab == 2) || ($this->section == 'name' && $this->tab == 3) && $canChange):
												echo JHtml::_(
													'jgrid.state',
													array(
														0 => array('mediamanager.setFrontpage', '', 'COM_KA_MOVIES_GALLERY_SETFRONTPAGE', '', 1, 'unfeatured', 'unfeatured'),
														1 => array('mediamanager.unsetFrontpage', '', 'COM_KA_MOVIES_GALLERY_UNSETFRONTPAGE', '', 1, 'featured', 'featured')
													),
													$item->frontpage, $i
												);
											endif;
											?>
										</div>
									</div>
								</div>
							</div>
						</li>
					<?php endforeach; ?>

					</ul>

				<?php endif; ?>

				</td>
			</tr>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="<?php echo $columns; ?>"></td>
			</tr>
		</tfoot>
	</table>
</div>
