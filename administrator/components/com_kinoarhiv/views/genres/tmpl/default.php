<?php defined('_JEXEC') or die;
JHtml::_('bootstrap.tooltip');

$user		= JFactory::getUser();
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$sortFields = $this->getSortFields();
?>
<script src="<?php echo JURI::root(); ?>components/com_kinoarhiv/assets/js/ui.aurora.min.js" type="text/javascript"></script>
<script type="text/javascript">
	Joomla.orderTable = function() {
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != '<?php echo $listOrder; ?>') {
			dirn = 'asc';
		} else {
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	}

	Joomla.submitbutton = function(pressbutton) {
		if (pressbutton == 'edit' && jQuery('#articleList :checkbox:checked').length > 1) {
			alert('<?php echo JText::_('COM_KA_ITEMS_EDIT_DENIED'); ?>');
			return;
		}
		if (pressbutton == 'relations') {
			document.location.href = 'index.php?option=com_kinoarhiv&view=relations&task=genres';
			return;
		}
		Joomla.submitform(pressbutton);
	}
	jQuery(document).ready(function($){
		function showMsg(selector, text) {
			$(selector).aurora({
				text: text,
				placement: 'before',
				button: 'close',
				button_title: '[<?php echo JText::_('COM_KA_CLOSE'); ?>]'
			});
		}

		$('#articleList a.updateStat').click(function(e){
			e.preventDefault();
			var _this = $(this);

			$.getJSON('index.php?option=com_kinoarhiv&controller=genres&task=updateStat&id[]='+_this.closest('tr').attr('sortable-group-id')+'&format=json&<?php echo JSession::getFormToken(); ?>=1', function(response){
				if (response.success) {
					_this.closest('td').find('span.total').text(response.total);
					showMsg('#articleList', '<?php echo JText::_('COM_KA_GENRES_STATS_UPDATED'); ?>');
				} else {
					showMsg('#articleList', response.message);
				}
			});
		});
	});
</script>

<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=genres'); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off">
	<div id="j-main-container">
		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo JText::_('COM_FILTER_SEARCH_DESC'); ?></label>
				<input type="text" name="filter_search" placeholder="<?php echo JText::_('COM_FILTER_SEARCH_DESC'); ?>" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_FILTER_SEARCH_DESC'); ?>" />
			</div>
			<div class="btn-group pull-left hidden-phone">
				<button class="btn tip hasTooltip" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
				<button class="btn tip hasTooltip" type="button" onclick="document.id('filter_search').value='';this.form.submit();" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
			</div>
			<div class="btn-group pull-right hidden-phone">
				<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
			<div class="btn-group pull-right hidden-phone">
				<label for="directionTable" class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></label>
				<select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
					<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></option>
					<option value="asc" <?php if ($listDirn == 'asc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_ASCENDING'); ?></option>
					<option value="desc" <?php if ($listDirn == 'desc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_DESCENDING');  ?></option>
				</select>
			</div>
			<div class="btn-group pull-right">
				<label for="sortTable" class="element-invisible"><?php echo JText::_('JGLOBAL_SORT_BY'); ?></label>
				<select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
					<option value=""><?php echo JText::_('JGLOBAL_SORT_BY');?></option>
					<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder); ?>
				</select>
			</div>
		</div>
		<div class="clearfix"> </div>

		<table class="table table-striped" id="articleList">
			<thead>
				<tr>
					<th width="1%" class="center hidden-phone">
						<?php echo JHtml::_('grid.checkall'); ?>
					</th>
					<th width="1%" style="min-width:55px" class="nowrap center">
						<?php echo JHtml::_('grid.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
					</th>
					<th>
						<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.name', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone">
						<?php echo JHtml::_('grid.sort', 'COM_KA_GENRES_STATS', 'a.stats', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone">
						<?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
					</th>
					<th width="5%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tbody>
			<?php if (count($this->items) == 0): ?>
				<tr>
					<td colspan="6" class="center hidden-phone"><?php echo JText::_('COM_KA_NO_ITEMS'); ?></td>
				</tr>
			<?php else:
				foreach ($this->items as $i => $item): ?>
				<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->id; ?>">
					<td class="center hidden-phone">
						<?php echo JHtml::_('grid.id', $i, $item->id, false, 'id'); ?>
					</td>
					<td class="center">
						<div class="btn-group">
							<?php echo JHtml::_('jgrid.published', $item->state, $i, '', $this->canEditState, 'cb'); ?>
						</div>
					</td>
					<td class="nowrap has-context">
						<div class="pull-left">
							<?php if ($this->canEdit) : ?>
								<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&controller=genres&task=edit&id[]='.$item->id); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?>">
									<?php echo $this->escape($item->name); ?></a>
								<span class="small">(<?php echo JText::_('JFIELD_ALIAS_LABEL'); ?>: <?php echo $this->escape($item->alias); ?>)</span>
							<?php else : ?>
								<span title="<?php echo JText::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->alias)); ?>"><?php echo $this->escape($item->name); ?></span>
							<?php endif; ?>
						</div>
					</td>
					<td class="small hidden-phone">
						<span class="total"><?php echo $item->stats; ?></span> <?php if ($this->canUpdateStat) : ?><span style="float: right;"><a href="#" class="hasTooltip updateStat" title="<?php echo JText::_('COM_KA_GENRES_STATS_UPDATE'); ?>"><img src="components/com_kinoarhiv/assets/images/icons/arrow_refresh_small.png" border="0" /></a></span><?php endif; ?>
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
					<td class="center hidden-phone">
						<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=relations&task=genres&id='.$item->id); ?>" class="hasTooltip" title="<?php echo JText::sprintf('COM_KA_COUNTRIES_RELATIONS_BUTTON_TEXT', $this->escape($item->name)); ?>"><img src="components/com_kinoarhiv/assets/images/icons/arrow_switch.png" border="0" /></a>
						<?php echo (int) $item->id; ?>
					</td>
				</tr>
				<?php endforeach;
			endif; ?>
			</tbody>
		</table>
		<?php echo $this->pagination->getListFooter(); ?>

		<input type="hidden" name="controller" value="genres" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
