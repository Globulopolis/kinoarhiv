<?php defined('_JEXEC') or die;
$user		= JFactory::getUser();
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$sortFields = $this->getSortFields();
?>
<script type="text/javascript" src="<?php echo JURI::root(); ?>components/com_kinoarhiv/assets/js/ui.aurora.min.js"></script>
<script type="text/javascript">
	function showMsg(selector, text) {
		jQuery(selector).aurora({
			text: text,
			placement: 'before',
			button: 'close',
			button_title: '[<?php echo JText::_('COM_KA_CLOSE'); ?>]'
		});
	}

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

	Joomla.submitbutton = function(task) {
		if (task == 'edit' && jQuery('#articleList :checkbox:checked').length > 1) {
			alert('<?php echo JText::_('COM_KA_ITEMS_EDIT_DENIED'); ?>');
			return;
		}
		Joomla.submitform(task);
	}

	jQuery(document).ready(function($){
		$('.hasTip, .hasTooltip, td[title]').tooltip({
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

		<?php if (count($this->items) > 1): ?>
		$('#articleList tbody').sortable({
			placeholder: 'ui-state-highlight',
			helper: function(e, tr){
				var $originals = tr.children();
				var $helper = tr.clone();

				$helper.children().each(function(index){
					$(this).width($originals.eq(index).width());
				});
				return $helper;
			},
			handle: '.sortable-handler',
			cursor: 'move',
			update: function(e, ui){
				$.post('index.php?option=com_kinoarhiv&controller=premieres&task=saveOrder&format=json', $('#articleList tbody .order input.ord').serialize()+'&<?php echo JSession::getFormToken(); ?>=1&movie_id='+$(ui.item).find('input[name="movie_id"]').val(), function(response){
					if (!response.success) {
						showMsg('#j-main-container', response.message);
					}
				}).fail(function(xhr, status, error){
					showMsg('#j-main-container', error);
				});
			}
		});
		<?php endif; ?>
	});
</script>
<div id="j-main-container">
	<form action="index.php?option=com_kinoarhiv&view=premieres" method="post" name="adminForm" id="adminForm" autocomplete="off">
		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo JText::_('COM_FILTER_SEARCH_DESC'); ?></label>
				<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape(trim($this->state->get('filter.search'))); ?>" style="width: 350px;" />
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
				<select name="sortTable" id="sortTable" class="input-xlarge" onchange="Joomla.orderTable()">
					<option value=""><?php echo JText::_('JGLOBAL_SORT_BY');?></option>
					<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder); ?>
				</select>
			</div>
		</div>
		<div class="clearfix"> </div>

		<table class="table table-striped" id="articleList">
			<thead>
				<tr>
					<th width="3%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'p.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
					</th>
					<th width="1%" class="center hidden-phone">
						<?php echo JHtml::_('grid.checkall'); ?>
					</th>
					<th width="11%" class="center hidden-phone">
						<?php echo JHtml::_('grid.sort', 'COM_KA_FIELD_PREMIERE_DATE_LABEL', 'p.premiere_date', $listDirn, $listOrder); ?>
					</th>
					<th width="30%" style="min-width:55px">
						<?php echo JHtml::_('grid.sort', 'COM_KA_FIELD_MOVIE_LABEL', 'm.title', $listDirn, $listOrder); ?>
					</th>
					<th width="25%" class="nowrap hidden-phone">
						<?php echo JText::_('COM_KA_FIELD_PREMIERE_VENDOR'); ?>
					</th>
					<th width="15%" class="nowrap hidden-phone">
						<?php echo JHtml::_('grid.sort', 'COM_KA_FIELD_PREMIERE_COUNTRY_LABEL', 'c.name', $listDirn, $listOrder); ?>
					</th>
					<th width="5%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'p.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tbody>
			<?php if (count($this->items) == 0): ?>
				<tr>
					<td colspan="7" class="center hidden-phone"><?php echo JText::_('COM_KA_NO_ITEMS'); ?></td>
				</tr>
			<?php else:
				foreach ($this->items as $i => $item) :
					$canEdit = $user->authorise('core.edit', 'com_kinoarhiv');
				?>
				<tr class="row<?php echo $i % 2; ?>">
					<td class="order nowrap center hidden-phone">
						<span class="sortable-handler<?php echo (count($this->items) < 2 || !$user->authorise('core.edit', 'com_kinoarhiv')) ? ' inactive tip-top' : ''; ?>"><i class="icon-menu"></i></span>
						<span class="i"><?php echo (int)$item->ordering; ?></span>
						<input type="hidden" name="ord[]" class="ord" value="<?php echo $item->id; ?>" />
						<input type="hidden" name="movie_id" value="<?php echo $item->movie_id; ?>" />
					</td>
					<td class="center hidden-phone">
						<?php echo JHtml::_('grid.id', $i, $item->id, false, 'id'); ?>
					</td>
					<td class="center hidden-phone">
						<?php echo $item->premiere_date; ?>
					</td>
					<td class="nowrap hidden-phone">
						<?php echo $this->escape($item->title); ?><?php echo ($item->year != '0000') ? ' ('.$item->year.')' : ''; ?>
					</td>
					<td class="nowrap hidden-phone">
						<?php $vendor_0 = !empty($item->company_name) ? $item->company_name : '';
						$vendor_1 = !empty($item->company_name) && !empty($item->company_name_intl) ? ' / ' : '';
						$vendor_2 = !empty($item->company_name_intl) ? $item->company_name_intl : '';
						echo $vendor_0.$vendor_1.$vendor_2;
						?>
					</td>
					<td class="nowrap hidden-phone">
						<?php echo ($item->name != '') ? $item->name : JText::_('COM_KA_PREMIERE_WORLD'); ?>
					</td>
					<td class="center hidden-phone">
						<?php echo (int)$item->id; ?>
					</td>
				</tr>
				<?php endforeach;
			endif; ?>
			</tbody>
		</table>
		<?php echo $this->pagination->getListFooter(); ?>
		<input type="hidden" name="controller" value="premieres" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
