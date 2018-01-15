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

$user      = JFactory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$columns   = 7;

if (JFactory::getApplication()->input->get('type', 'movie', 'word') == 'music')
{
	$item_type = 'music';
	$upd_stat_text = 'COM_KA_GENRES_MUSIC_STATS_UPDATED_COUNT';
}
else
{
	$item_type = 'movie';
	$upd_stat_text = 'COM_KA_GENRES_STATS_UPDATED_COUNT';
}
?>
<script type="text/javascript">
	Joomla.submitbutton = function(pressbutton) {
		if (pressbutton === 'genres.edit' && jQuery('#articleList :checkbox:checked').length > 1) {
			alert('<?php echo JText::_('COM_KA_ITEMS_EDIT_DENIED'); ?>');

			return;
		}
		if (pressbutton === 'relations') {
			document.location.href = 'index.php?option=com_kinoarhiv&view=relations&task=genres&element=movies&type=<?php echo $item_type; ?>';

			return;
		}
		Joomla.submitform(pressbutton);
	};

	jQuery(document).ready(function($){
		$('a.updateStat').click(function(e){
			e.preventDefault();
			var $this = $(this);

			$.getJSON($this.attr('href') + '&boxchecked=1&<?php echo JSession::getFormToken(); ?>=1', function(response){
				if (response.success) {
					$this.closest('td').find('span.total').text(response.total);
					showMsg('#system-message-container', response.message + '&nbsp;' + response.total + '<?php echo JText::_($upd_stat_text); ?>');
				} else {
					showMsg('#system-message-container', response.message);
				}
			});
		});
	});
</script>

<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=genres&type=' . $item_type); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off">
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
						<?php echo JHtml::_('searchtools.sort', ($item_type == 'movie') ? 'COM_KA_GENRES_STATS' : 'COM_KA_GENRES_MUSIC_STATS', 'a.stats', $listDirn, $listOrder); ?>
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
					<td colspan="<?php echo $columns; ?>" class="center"><?php echo JText::_('COM_KA_NO_ITEMS'); ?></td>
				</tr>
			<?php else:
				foreach ($this->items as $i => $item): ?>
				<tr class="row<?php echo $i % 2; ?>">
					<td class="center">
						<?php echo JHtml::_('grid.id', $i, $item->id, false, 'id'); ?>
					</td>
					<td class="center hidden-phone">
						<?php echo JHtml::_('jgrid.published', $item->state, $i, 'genres.', $this->canEditState, 'cb'); ?>
					</td>
					<td class="nowrap has-context">
						<div class="pull-left">
							<?php if ($this->canEdit) : ?>
								<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&type=' . $item_type . '&task=genres.edit&id[]=' . $item->id); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?>">
									<?php echo $this->escape($item->name); ?></a>
								<span class="small">(<?php echo JText::_('JFIELD_ALIAS_LABEL'); ?>: <?php echo $this->escape($item->alias); ?>)</span>
							<?php else : ?>
								<span title="<?php echo JText::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->alias)); ?>"><?php echo $this->escape($item->name); ?></span>
							<?php endif; ?>
						</div>
					</td>
					<td class="small hidden-phone">
						<span class="total"><?php echo $item->stats; ?></span>&nbsp;
						<?php if ($this->canUpdateStat) : ?>

						<span style="float: right;">
							<a href="index.php?option=com_kinoarhiv&task=genres.updateStat&type=<?php echo $item_type; ?>&id[]=<?php echo $item->id; ?>&format=json" class="hasTooltip updateStat" title="<?php echo JText::_('COM_KA_GENRES_STATS_UPDATE'); ?>">
								<span class="icon-refresh"></span>
							</a>
						</span>

						<?php endif; ?>
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
						<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=relations&task=genres&element=movies&type=' . $item_type . '&id=' . $item->id); ?>" class="hasTooltip hidden-phone" title="<?php echo JText::_('COM_KA_TABLES_RELATIONS') . ': ' . $this->escape($item->name); ?>"><span class="icon-out-2"></span></a>
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
		<?php echo $this->pagination->getListFooter(); ?>
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

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
