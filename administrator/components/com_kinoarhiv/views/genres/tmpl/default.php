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

JHtml::_('bootstrap.tooltip');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<script type="text/javascript">
	Joomla.submitbutton = function(pressbutton) {
		if (pressbutton === 'genres.edit' && jQuery('#articleList :checkbox:checked').length > 1) {
			alert('<?php echo JText::_('COM_KA_ITEMS_EDIT_DENIED'); ?>');

			return;
		}
		if (pressbutton === 'relations') {
			document.location.href = 'index.php?option=com_kinoarhiv&view=relations&task=genres&element=movies';

			return;
		}
		Joomla.submitform(pressbutton);
	};
</script>

<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=genres'); ?>"
	  method="post" name="adminForm" id="adminForm" autocomplete="off">
	<div id="j-main-container">
		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
		<div class="clearfix"> </div>

		<table class="table table-striped" id="articleList">
			<thead>
				<tr>
					<th width="1%" class="center">
						<?php echo JHtml::_('grid.checkall'); ?>
					</th>
					<th width="1%" style="min-width:35px;" class="nowrap center hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
					</th>
					<th>
						<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.name', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort',  'COM_KA_FIELD_GENRE_TYPE', 'a.type', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'COM_KA_GENRES_STATS', 'a.stats', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort',  'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
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
					<td colspan="8" class="center"><?php echo JText::_('COM_KA_NO_ITEMS'); ?></td>
				</tr>
			<?php else:
				foreach ($this->items as $i => $item): ?>
				<tr class="row<?php echo $i % 2; ?>">
					<td class="center">
						<?php echo JHtml::_('grid.id', $i, $item->id, false, 'id'); ?>
						<input type="hidden" name="type[<?php echo $item->id; ?>]" value="<?php echo $item->type; ?>" />
					</td>
					<td class="center hidden-phone">
						<?php echo JHtml::_('jgrid.published', $item->state, $i, 'genres.', $this->canEditState, 'cb'); ?>
					</td>
					<td class="nowrap has-context">
						<div class="pull-left">
							<?php if ($this->canEdit) : ?>
								<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&task=genres.edit&id[]=' . $item->id); ?>"
								   title="<?php echo JText::_('JACTION_EDIT'); ?>">
									<?php echo $this->escape($item->name); ?></a>
								<div class="small">(<?php echo JText::_('JFIELD_ALIAS_LABEL'); ?>: <?php echo $this->escape($item->alias); ?>)</div>
							<?php else : ?>
								<span title="<?php echo JText::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->alias)); ?>">
									<?php echo $this->escape($item->name); ?>
								</span>
							<?php endif; ?>
						</div>
					</td>
					<td class="small hidden-phone">
						<?php echo $item->type == 1 ? JText::_('COM_KA_GENRES_MUSIC_TITLE') : JText::_('COM_KA_GENRES_MOVIE_TITLE'); ?>
					</td>
					<td class="small hidden-phone">
						<div class="row-fluid">
							<span class="span6" id="total_<?php echo $item->id; ?>"><?php echo $item->stats; ?></span>

							<?php if ($this->canUpdateStat): ?>
								<span class="span6">
									<a href="<?php echo 'index.php?option=com_kinoarhiv&task=genres.updateStat&type[' . $item->id . ']=' . $item->type .'&id[]=' . $item->id . '&boxchecked=1&format=json'; ?>"
									   class="hasTooltip cmd-update-genre-stat" data-gs-update="#total_<?php echo $item->id; ?>"
									   title="<?php echo JText::_('COM_KA_GENRES_STATS_UPDATE'); ?>">
										<span class="icon-refresh"></span>
									</a>
								</span>
							<?php endif; ?>
						</div>
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
						<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=relations&task=genres&element=movies&id=' . $item->id); ?>"
						   class="hasTooltip hidden-phone"
						   title="<?php echo JText::_('COM_KA_TABLES_RELATIONS') . ': ' . $this->escape($item->name); ?>">
							<span class="icon-out-2"></span>
						</a>
						<?php echo (int) $item->id; ?>
					</td>
				</tr>
				<?php endforeach;
			endif; ?>
			</tbody>
		</table>
		<?php echo $this->pagination->getListFooter(); ?>

		<?php if ($this->user->authorise('core.create', 'com_kinoarhiv')
			&& $this->user->authorise('core.edit', 'com_kinoarhiv')
			&& $this->user->authorise('core.edit.state', 'com_kinoarhiv')) : ?>
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

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
