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

$user      = JFactory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$columns   = ($this->getLayout() !== 'modal') ? 5 : 4;
$canChange = $user->authorise('core.edit.state', 'com_kinoarhiv.' . $this->section . '.' . $this->id);
?>
<table class="table table-striped gallery-list" id="articleList">
	<?php if ($this->getLayout() !== 'modal'): ?>

	<thead>
		<tr class="">
			<th width="1%" class="center">
				<?php echo JHtml::_('grid.checkall'); ?>
			</th>
			<th width="5%" style="min-width:35px;" class="nowrap center">
			<?php
				echo JHtml::_('searchtools.sort', 'JSTATUS', 'g.state', $listDirn, $listOrder);

				if (($this->section == 'movie' && $this->tab == 2) || ($this->section == 'name' && $this->tab == 3)):
					echo '|' . JHtml::_('searchtools.sort', 'COM_KA_MOVIES_GALLERY_HEADING_FRONTPAGE', 'g.frontpage', $listDirn, $listOrder);
				endif;
			?>
			</th>
			<th>
				<?php echo JHtml::_('searchtools.sort', 'COM_KA_MOVIES_GALLERY_HEADING_FILENAME', 'g.filename', $listDirn, $listOrder); ?>
			</th>
			<th width="10%" class="nowrap center hidden-phone">
				<?php echo JHtml::_('searchtools.sort', 'COM_KA_MOVIES_GALLERY_HEADING_DIMENSION', 'g.dimension', $listDirn, $listOrder); ?>
			</th>
			<th width="5%" class="nowrap center hidden-phone">
				<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'g.id', $listDirn, $listOrder); ?>
			</th>
		</tr>
	</thead>

	<?php endif; ?>

	<tbody>
	<?php if (count($this->items) == 0): ?>
		<tr>
			<td colspan="<?php echo $columns; ?>" class="center"><?php echo JText::_('COM_KA_NO_FILES'); ?></td>
		</tr>
	<?php else:
		foreach ($this->items as $i => $item): ?>
			<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->id; ?>">

				<?php if ($this->getLayout() !== 'modal'): ?>
				<td class="center">
					<?php echo JHtml::_('grid.id', $i, $item->id, false, 'item_id'); ?>
				</td>
				<?php endif; ?>

				<td class="center">
					<div class="btn-group">
					<?php
						if ($this->getLayout() == 'modal'):
							$canChange = 0;
						endif;

						echo JHtml::_('jgrid.published', $item->state, $i, 'mediamanager.', $canChange, 'cb');

						if (($this->section == 'movie' && $this->tab == 2) || ($this->section == 'name' && $this->tab == 3)):
							echo JHtml::_(
								'jgrid.state',
								array(
									0 => array('mediamanager.setFrontpage', '', 'COM_KA_MOVIES_GALLERY_SETFRONTPAGE', '', 1, 'unfeatured', 'unfeatured'),
									1 => array('mediamanager.unsetFrontpage', '', 'COM_KA_MOVIES_GALLERY_UNSETFRONTPAGE', '', 1, 'featured', 'featured')
								),
								$item->frontpage, $i, '', $canChange
							);
						endif;
					?>
					</div>
				</td>
				<td class="has-context">
				<?php
					if (!empty($item->error)):
				?>
					<a href="#" class="hasTooltip error_image" title="<?php echo $item->error; ?>"></a>
				<?php
					endif;

					if ($this->getLayout() !== 'modal'):
				?>
					<a href="<?php echo $item->filepath; ?>" class="tooltip-img" rel="group_<?php echo $this->tab; ?>"><?php echo $item->filename; ?></a>
				<?php
					else:
						$func = "Kinoarhiv.selectFrontpageImage(this, '" . $this->section . "', '" . $this->type . "', " . (int) $this->id . ", " . (int) $item->id . ", '" . $this->escape($item->filename) . "')";
				?>
					<a href="#" class="tooltip-img" onclick="<?php echo $func; ?>"><?php echo $item->filename; ?></a>
				<?php
					endif;
				?>
					<?php if ($item->th_filepath != ''): ?><img src="<?php echo $item->th_filepath; ?>" class="tooltip-img-content" /><?php endif; ?>
					<?php if ($item->folderpath != ''): ?> <span class="small gray hidden-phone">(<?php echo $item->folderpath . $item->filename; ?>)</span><?php endif; ?>
				</td>
				<td class="center hidden-phone">
					<?php echo $item->dimension; ?>
				</td>
				<td class="center hidden-phone">
					<?php echo (int) $item->id; ?>
				</td>
			</tr>
		<?php endforeach;
	endif; ?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="<?php echo $columns; ?>"></td>
		</tr>
	</tfoot>
</table>
