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

$user		= JFactory::getUser();
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$sortFields = $this->getSortFields();
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
	};

	Joomla.submitbutton = function(pressbutton) {
		if (pressbutton == 'edit' && jQuery('#articleList :checkbox:checked').length > 1) {
			alert('<?php echo JText::_('COM_KA_ITEMS_EDIT_DENIED'); ?>');
			return;
		}

		Joomla.submitform(pressbutton);
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=vendors'); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off">
	<div id="j-main-container">
		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
		<div class="clearfix"> </div>

		<table class="table table-striped" id="articleList">
			<thead>
				<tr>
					<th width="1%">
						<?php echo JHtml::_('grid.checkall'); ?>
					</th>
					<th width="1%" style="min-width:55px" class="nowrap center hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'v.state', $listDirn, $listOrder); ?>
					</th>
					<th>
						<?php echo JHtml::_('searchtools.sort', 'COM_KA_VENDORS_FIELD_TITLE', 'v.company_name', $listDirn, $listOrder); ?>
					</th>
					<th>
						<?php echo JHtml::_('searchtools.sort', 'COM_KA_VENDORS_FIELD_TITLE_INTL', 'v.company_name_intl', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
					</th>
					<th width="5%" class="nowrap center">
						<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'v.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tbody>
			<?php if (count($this->items) == 0): ?>
				<tr>
					<td colspan="6" class="center"><?php echo JText::_('COM_KA_NO_ITEMS'); ?></td>
				</tr>
			<?php else:
				foreach ($this->items as $i => $item): ?>
				<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->id; ?>">
					<td class="center">
						<?php echo JHtml::_('grid.id', $i, $item->id, false, 'id'); ?>
					</td>
					<td class="center hidden-phone">
						<?php echo JHtml::_('jgrid.published', $item->state, $i, '', $this->canEditState, 'cb'); ?>
					</td>
					<td class="nowrap has-context">
						<div class="pull-left">
							<?php if ($this->canEdit): ?>
								<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&controller=vendors&task=edit&id[]='.$item->id); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?>">
									<?php echo $this->escape($item->company_name); ?></a>
								<?php if (!empty($item->company_name)): ?><br /><span class="small" title="<?php echo JText::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->company_name_alias)); ?>">(<?php echo JText::_('JFIELD_ALIAS_LABEL').': '.$this->escape($item->company_name_alias); ?>)</span><?php endif; ?>
							<?php else : ?>
								<span><?php echo $this->escape($item->company_name); ?></span>
							<?php endif; ?>
						</div>
					</td>
					<td class="nowrap has-context">
						<?php if ($this->canEdit): ?>
								<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&controller=vendors&task=edit&id[]='.$item->id); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?>">
									<?php echo $this->escape($item->company_name_intl); ?></a>
								<?php if (!empty($item->company_name_intl)): ?><br /><span class="small" title="<?php echo JText::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->company_name_alias)); ?>">(<?php echo JText::_('JFIELD_ALIAS_LABEL').': '.$this->escape($item->company_name_alias); ?>)</span><?php endif; ?>
							<?php else : ?>
								<span><?php echo $this->escape($item->company_name_intl); ?></span>
							<?php endif; ?>
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

		<input type="hidden" name="controller" value="vendors" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
