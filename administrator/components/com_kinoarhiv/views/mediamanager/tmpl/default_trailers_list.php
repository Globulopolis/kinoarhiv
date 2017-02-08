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

JHtml::_('bootstrap.tooltip');

$user      = JFactory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off">
	<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
	<div class="clearfix"> </div>

	<table class="table table-striped gallery-list" id="articleList">
		<thead>
			<tr>
				<th width="1%" class="center">
					<?php echo JHtml::_('grid.checkall'); ?>
				</th>
				<th width="5%" style="min-width:35px;" class="nowrap center hidden-phone">
				<?php
					echo JHtml::_('searchtools.sort', 'JSTATUS', 'g.state', $listDirn, $listOrder) . '|'
						. JHtml::_('searchtools.sort', 'COM_KA_MOVIES_GALLERY_HEADING_FRONTPAGE', 'g.frontpage', $listDirn, $listOrder);
				?>
				</th>
				<th>
					<?php echo JHtml::_('searchtools.sort', 'COM_KA_TRAILERS_TITLE', 'g.title', $listDirn, $listOrder); ?>
				</th>
				<th width="15%" class="nowrap center hidden-phone">
					<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'g.access', $listDirn, $listOrder); ?>
				</th>
				<th width="10%" class="nowrap center hidden-phone">
					<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
				</th>
				<th width="5%" class="nowrap center">
					<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'g.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
		</thead>
		<tbody>
		<?php if (count($this->items) == 0): ?>
			<tr>
				<td colspan="6" class="center"><?php echo JText::_('COM_KA_NO_ITEMS'); ?></td>
			</tr>
		<?php else:
			foreach ($this->items as $i => $item):
				$canChange = $user->authorise('core.edit.state', 'com_kinoarhiv.movie.' . $item->id);
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="center">
					<?php echo JHtml::_('grid.id', $i, $item->id, false, 'item_id'); ?>
				</td>
				<td class="center">
					<div class="btn-group">
						<?php
						echo JHtml::_('jgrid.published', $item->state, $i, 'mediamanager.', $canChange, 'cb');
						echo JHtml::_(
							'jgrid.state',
							array(
								0 => array('mediamanager.setFrontpage', '', '', '', 1, 'unfeatured', 'unfeatured'),
								1 => array('mediamanager.unsetFrontpage', '', '', '', 1, 'featured', 'featured')
							),
							$item->frontpage, $i
						);
						?>
					</div>
				</td>
				<td>
					<?php if ($item->embed_code != ''): ?>
						<span class="icon icon-play-2 hasTooltip" title="<?php echo JText::_('COM_KA_TRAILERS_ISCODE'); ?>"></span>
					<?php elseif ($item->video != ''): ?>
						<span class="icon icon-camera-2 hasTooltip" title="<?php echo JText::_('COM_KA_TRAILERS_ISFILE'); ?>"></span>
					<?php else: ?>
						<a class="error_image"></a>
					<?php endif; ?>
					&nbsp;<a href="index.php?option=com_kinoarhiv&task=mediamanager.edit&section=movie&type=trailers&id=<?php echo $this->id; ?>&item_id[]=<?php echo $item->id; ?>"><?php echo ($this->escape($item->title) == '') ? JText::_('COM_KA_NOTITLE') : $this->escape($item->title); ?></a>
					<?php if ($item->video != ''): ?> <span class="small">(<?php echo $item->duration; ?>)</span><?php endif; ?>
				</td>
				<td class="center hidden-phone">
					<?php echo $this->escape($item->access_level); ?>
				</td>
				<td class="center hidden-phone">
					<?php if ($item->language == '*'): ?>
						<?php echo JText::alt('JALL', 'language'); ?>
					<?php else: ?>
						<?php echo $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
					<?php endif; ?>
				</td>
				<td class="center">
					<?php echo (int) $item->id; ?>
				</td>
			</tr>
			<?php endforeach;
		endif; ?>
		</tbody>
	</table>
	<?php echo $this->pagination->getListFooter(); ?>
	<?php echo $this->pagination->getResultsCounter(); ?>
	<?php if ($user->authorise('core.create', 'com_kinoarhiv')
		&& $user->authorise('core.edit', 'com_kinoarhiv')
		&& $user->authorise('core.edit.state', 'com_kinoarhiv')) : ?>
		<?php echo JHtml::_(
			'bootstrap.renderModal',
			'collapseModal',
			array(
				'title' => JText::_('COM_KA_BATCH_OPTIONS'),
				'footer' => $this->loadTemplate('batch_footer')
			),
			$this->loadTemplate('batch_body')
		); ?>
	<?php endif; ?>

	<input type="hidden" name="section" value="<?php echo $this->section; ?>" />
	<input type="hidden" name="type" value="<?php echo $this->type; ?>" />
	<input type="hidden" name="id" value="<?php echo $this->id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHtml::_('form.token'); ?>
</form>
