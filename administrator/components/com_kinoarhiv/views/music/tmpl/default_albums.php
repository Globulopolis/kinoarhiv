<?php defined('_JEXEC') or die;
$user		= JFactory::getUser();
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
?>
<script type="text/javascript">
	Joomla.orderTable = function() {
		var table = document.getElementById("sortTable");
		var direction = document.getElementById("directionTable");
		var order = table.options[table.selectedIndex].value;
		if (order != '<?php echo $listOrder; ?>') {
			var dirn = 'asc';
		} else {
			var dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	}
</script>
<form action="<?php echo htmlspecialchars(JURI::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off">
	<div id="j-main-container">
		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
		<div class="clearfix"> </div>

		<table class="table table-striped" id="articleList">
			<thead>
				<tr>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
					</th>
					<th width="1%" class="center">
						<?php echo JHtml::_('grid.checkall'); ?>
					</th>
					<th width="1%" style="min-width:55px" class="nowrap center hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
					</th>
					<th>
						<?php echo JHtml::_('searchtools.sort', 'COM_KA_MUSIC_ALBUMS_HEADING', 'a.title', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
					</th>
					<th width="5%" class="nowrap center">
						<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php if (count($this->items) == 0): ?>
					<tr>
						<td colspan="7" class="center"><?php echo JText::_('COM_KA_NO_ITEMS'); ?></td>
					</tr>
				<?php else:
					foreach ($this->items as $i => $item):
						$canEdit    = $user->authorise('core.edit',			'com_kinoarhiv.album.'.$item->id);
						$canChange  = $user->authorise('core.edit.state',	'com_kinoarhiv.album.'.$item->id);
					?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="order nowrap center hidden-phone">
							<span class="sortable-handler<?php echo (count($this->items) < 2 || !$user->authorise('core.edit', 'com_kinoarhiv.album.'.$item->id)) ? ' inactive tip-top' : ''; ?>"><i class="icon-menu"></i></span>
							<input type="hidden" name="ord[]" value="<?php echo $item->id; ?>" />
						</td>
						<td class="center">
							<?php echo JHtml::_('grid.id', $i, $item->id, false, 'id'); ?>
						</td>
						<td class="center hidden-phone">
							<div class="btn-group">
								<?php echo JHtml::_('jgrid.published', $item->state, $i, '', $canChange, 'cb'); ?>
							</div>
						</td>
						<td>
							<?php if ($canEdit): ?>
								<a href="index.php?option=com_kinoarhiv&controller=music&task=edit&type=albums&id[]=<?php echo $item->id; ?>"><?php echo ($this->escape($item->title) == '') ? JText::_('COM_KA_NOTITLE') : $this->escape($item->title); ?> (<?php echo $item->year; ?>)</a>
							<?php else: ?>
								<?php echo ($this->escape($item->title) == '') ? JText::_('COM_KA_NOTITLE') : $this->escape($item->title); ?> (<?php echo $item->year; ?>)
							<?php endif; ?>
							<div class="small"><?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $item->alias); ?></div>
						</td>
						<td class="small hidden-phone">
							<?php echo $this->escape($item->access_level); ?>
						</td>
						<td class="small hidden-phone">
							<?php if ($item->language == '*'):?>
								<?php echo JText::alt('JALL', 'language'); ?>
							<?php else:?>
								<?php echo $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
							<?php endif;?>
						</td>
						<td class="center">
							<?php echo (int)$item->id; ?>
						</td>
					</tr>
					<?php endforeach;
				endif; ?>
			</tbody>
		</table>
		<?php echo $this->pagination->getListFooter(); ?>
		<?php echo $this->loadTemplate('batch'); ?>

		<input type="hidden" name="controller" value="music" />
		<input type="hidden" name="type" value="albums" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
