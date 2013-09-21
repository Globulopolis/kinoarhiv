<?php defined('_JEXEC') or die;
$user		= JFactory::getUser();
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
?>
<script type="text/javascript">
//<![CDATA[
	jQuery(document).ready(function($){
		$('.hasTip, .hasTooltip').tooltip({
			show: null,
			position: {
				my: 'left top',
				at: 'left bottom'
			},
			open: function(event, ui){
				ui.tooltip.animate({ top: ui.tooltip.position().top + 10 }, 'fast');
			},
			content: function(){
				var parts = $(this).attr('title').split('::', 2),
					title = '';

				if (parts.length == 2) {
					if (parts[0] != '') {
						title += '<div style="text-align: center; border-bottom: 1px solid #EEEEEE;">' + parts[0] + '</div>' + parts[1];
					} else {
						title += parts[1];
					}
				} else {
					title += $(this).attr('title');
				}

				return title;
			}
		});
	});
//]]>
</script>
<table class="table table-striped gallery-list" id="articleList">
	<thead>
		<tr>
			<th width="1%" class="center hidden-phone">
				<?php echo JHtml::_('grid.checkall'); ?>
			</th>
			<th>
				<?php echo JHtml::_('grid.sort', 'COM_KA_MOVIES_GALLERY_HEADING_FILENAME', 'filename', $listDirn, $listOrder); ?>
			</th>
			<th width="15%" class="nowrap center">
				<?php echo JHtml::_('grid.sort', 'COM_KA_MOVIES_GALLERY_HEADING_DIMENSION', 'dimension', $listDirn, $listOrder); ?>
			</th>
			<th width="10%" style="min-width: 55px" class="nowrap center">
				<?php echo JHtml::_('grid.sort', 'COM_KA_MOVIES_GALLERY_HEADING_FRONTPAGE', 'poster_frontpage', $listDirn, $listOrder); ?>
			</th>
			<th width="1%" style="min-width: 55px" class="nowrap center">
				<?php echo JHtml::_('grid.sort', 'JSTATUS', 'state', $listDirn, $listOrder); ?>
			</th>
			<th width="5%" class="nowrap center hidden-phone">
				<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'id', $listDirn, $listOrder); ?>
			</th>
		</tr>
	</thead>
	<tbody>
		<?php if (count($this->items) == 0): ?>
			<tr>
				<td colspan="6" class="center hidden-phone"><?php echo JText::_('COM_KA_NO_ITEMS'); ?></td>
			</tr>
		<?php else:
			foreach ($this->items as $i => $item):
				$canEdit    = $user->authorise('core.edit',			'com_kinoarhiv.movie.'.$item->id);
				$canChange  = $user->authorise('core.edit.state',	'com_kinoarhiv.movie.'.$item->id);
				$stateTask	= $item->poster_frontpage == 1 ? 'fp_off' : 'fp_on';
				$stateClass	= $item->poster_frontpage == 1 ? 'publish' : 'unpublish';
				$stateChangeArray = array('task'=>$stateTask, 'active_class'=>$stateClass);
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="center hidden-phone">
					<?php echo JHtml::_('grid.id', $i, $item->id, false, 'id'); ?>
				</td>
				<td class="hidden-phone">
					<?php echo $item->filename; ?>
				</td>
				<td class="center hidden-phone">
					<?php echo $item->dimension; ?>
				</td>
				<td class="center">
					<div class="btn-group">
						<?php echo JHtml::_('jgrid.state', array(0=>$stateChangeArray), $item->poster_frontpage, $i, '', $canChange, true, 'ab'); ?>
					</div>
				</td>
				<td class="center">
					<div class="btn-group">
						<?php echo JHtml::_('jgrid.published', $item->state, $i, '', $canChange, 'cb'); ?>
					</div>
				</td>
				<td class="center hidden-phone">
					<?php echo (int)$item->id; ?>
				</td>
			</tr>
			<?php endforeach;
		endif; ?>
	</tbody>
</table>
