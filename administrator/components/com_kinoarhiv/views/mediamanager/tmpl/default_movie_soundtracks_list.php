<?php defined('_JEXEC') or die;
$user		= JFactory::getUser();
$input 		= JFactory::getApplication()->input;
//$sortFields = $this->getSortFields();
?>
<script type="text/javascript">
//<![CDATA[
	jQuery(document).ready(function($){
	});
//]]>
</script>
<form action="<?php echo htmlspecialchars(JURI::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off">
	<table class="table table-striped album-list" id="articleList">
		<thead>
			<tr>
				<th width="1%" class="center">
					<?php echo JHtml::_('grid.checkall', 'album-checkall-toggle', 'COM_KA_SND_ALBUM_CHECK_ALL', 'checkall(this, \'.album-list tbody td\')'); ?>
				</th>
				<th><?php echo JText::_('COM_KA_SND_ALBUM_HEADING'); ?></th>
				<th width="15%" class="nowrap center hidden-phone"><?php echo JText::_('JGRID_HEADING_ACCESS'); ?></th>
				<th width="15%" class="nowrap center hidden-phone"><?php echo JText::_('JGRID_HEADING_LANGUAGE'); ?></th>
				<th width="1%" style="min-width: 55px" class="nowrap center"><?php echo JText::_('JSTATUS'); ?></th>
				<th width="5%" class="nowrap center"><?php echo JText::_('JGRID_HEADING_ID'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if (count($this->items) == 0): ?>
				<tr>
					<td colspan="6" class="center"><?php echo JText::_('COM_KA_NO_ITEMS'); ?></td>
				</tr>
			<?php else:
				foreach ($this->items as $i => $item):
					$canEdit    = $user->authorise('core.edit',			'com_kinoarhiv.movie.'.$item->id);
					$canChange  = $user->authorise('core.edit.state',	'com_kinoarhiv.movie.'.$item->id);
				?>
				<tr class="row<?php echo $i % 2; ?>">
					<td class="center">
						<?php echo JHtml::_('grid.id', $i, $item->id, false, '_id'); ?>
					</td>
					<td>
						<?php if ($canEdit): ?>
							<a href="index.php?option=com_kinoarhiv&view=mediamanager&task=edit&section=movie&type=sounds&id=<?php echo $input->get('id', 0, 'int'); ?>&item_id=<?php echo $item->id; ?>"><?php echo ($this->escape($item->title) == '') ? JText::_('COM_KA_NOTITLE') : $this->escape($item->title); ?> (<?php echo $item->year; ?>)</a>
						<?php else: ?>
							<?php echo ($this->escape($item->title) == '') ? JText::_('COM_KA_NOTITLE') : $this->escape($item->title); ?> (<?php echo $item->year; ?>)
						<?php endif; ?>
						<div class="small"><?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $item->alias); ?></div>
					</td>
					<td class="center hidden-phone">
						<?php echo $this->escape($item->access_level); ?>
					</td>
					<td class="center hidden-phone">
						<?php if ($item->language == '*'):?>
							<?php echo JText::alt('JALL', 'language'); ?>
						<?php else:?>
							<?php echo $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
						<?php endif;?>
					</td>
					<td class="center">
						<?php echo JHtml::_('jgrid.published', $item->state, $i, '', $canChange, 'cb'); ?>
					</td>
					<td class="center">
						<?php echo (int)$item->id; ?>
					</td>
				</tr>
				<?php if (count($item->tracks) > 0): ?>
				<tr class="row<?php echo $i % 2; ?>">
					<td></td>
					<td colspan="5" style="padding: 0;">
						<table class="table table-striped tracklist">
							<thead>
								<tr>
									<th width="1%" class="center"><?php echo JHtml::_('grid.checkall', 'album-checkall-toggle', 'COM_KA_SND_TRACK_CHECK_ALL', 'checkall(this, \'table.tracklist tbody td\')'); ?></th>
									<th><?php echo JText::_('COM_KA_SND_ALBUM_TRACK_HEADING'); ?></th>
									<th width="15%" class="nowrap center hidden-phone" style="width: 15.45%"></th>
									<th width="15%" class="nowrap center hidden-phone" style="width: 15.4%"><?php echo JText::_('JGRID_HEADING_ACCESS'); ?></th>
									<th width="1%" style="min-width: 55px" class="nowrap center"><?php echo JText::_('JSTATUS'); ?></th>
									<th width="5%" class="nowrap center"><?php echo JText::_('JGRID_HEADING_ID'); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($item->tracks as $k => $track): ?>
								<tr>
									<td class="center">
										<?php echo JHtml::_('grid.id', $k, $track->id, false, 't_id'); ?>
									</td>
									<td>
										<div style="float:left;">
											<?php echo (int)$track->track_number; ?>. <?php echo $this->escape($track->title); ?>
										</div>
										<div style="float:right;">
											<?php echo ($track->length == '0') ? '' : $track->length; ?>
										</div>
									</td>
									<td class="center hidden-phone">
										1
									</td>
									<td class="center hidden-phone">
										<?php echo $this->escape($track->access_level); ?>
									</td>
									<td class="center">
										<?php echo JHtml::_('jgrid.published', $track->state, $k, '', $canChange, 'cb'); ?>
									</td>
									<td class="center">
										<?php echo (int)$track->id; ?>
									</td>
								</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</td>
				</tr>
				<?php endif; ?>
				<?php endforeach;
			endif; ?>
		</tbody>
	</table>
	<input type="hidden" name="controller" value="mediamanager" />
	<input type="hidden" name="section" value="<?php echo $input->get('section', '', 'word'); ?>" />
	<input type="hidden" name="type" value="<?php echo $input->get('type', '', 'word'); ?>" />
	<input type="hidden" name="id" value="<?php echo $input->get('id', 0, 'int'); ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHtml::_('form.token'); ?>
</form>
