<?php defined('_JEXEC') or die;
$user		= JFactory::getUser();
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$sortFields = $this->getSortFields();
?>
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

	Joomla.submitbutton = function(task) {
		if (task == 'edit' && jQuery('#articleList :checkbox:checked').length > 1) {
			alert('<?php echo JText::_('COM_KA_ITEMS_EDIT_DENIED'); ?>');
			return;
		} else if (task == 'menu') {
			jQuery('.rel-menu').toggle();
			return;
		}
		Joomla.submitform(task);
	}

	jQuery(document).ready(function($){
		$('#search_help').click(function(e){
			showMsg('#articleList', '<?php echo JText::_('COM_KA_MOVIES_SEARCH_HELP'); ?>');
		});
		$('.rel-menu').css({
			left: $('#toolbar-tools').offset().left+'px',
			top: ($('#toolbar-tools').offset().top+$('#toolbar-tools').height()+5)+'px'
		});
		$('#relations_menu').menu();
		$('a.dd-relations').click(function(e){
			e.preventDefault();
			$(this).parent().find('ul.dd-relations-menu').toggle();
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
				$.post('index.php?option=com_kinoarhiv&controller=movies&task=saveOrder&format=json', $('#articleList tbody .order input').serialize()+'&<?php echo JSession::getFormToken(); ?>=1', function(response){
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
	<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movies'); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off">
		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo JText::_('COM_FILTER_SEARCH_DESC'); ?></label>
				<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" style="width: 350px;" />
			</div>
			<div class="btn-group pull-left hidden-phone">
				<button class="btn" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
				<button class="btn" type="button" onclick="document.id('filter_search').value='';this.form.submit();" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
				<button class="btn" type="button" id="search_help" title="<?php echo JText::_('JTOOLBAR_HELP'); ?>"><i class="icon-help"></i></button>
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
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
					</th>
					<th width="1%" class="center hidden-phone">
						<?php echo JHtml::_('grid.checkall'); ?>
					</th>
					<th width="1%" style="min-width:55px" class="nowrap center">
						<?php echo JHtml::_('grid.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
					</th>
					<th>
						<?php echo JHtml::_('grid.sort', 'COM_KA_FIELD_MOVIE_LABEL', 'a.title', $listDirn, $listOrder); ?>
					</th>
					<th width="1%" style="min-width:55px" class="nowrap center">
					</th>
					<th width="10%" class="nowrap hidden-phone">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
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
					<td colspan="7" class="center hidden-phone"><?php echo JText::_('COM_KA_NO_ITEMS'); ?></td>
				</tr>
			<?php else:
				foreach ($this->items as $i => $item) :
					$canEdit    = $user->authorise('core.edit',			'com_kinoarhiv.movie.'.$item->id);
					$canChange  = $user->authorise('core.edit.state',	'com_kinoarhiv.movie.'.$item->id);
				?>
				<tr class="row<?php echo $i % 2; ?>">
					<td class="order nowrap center hidden-phone">
						<span class="sortable-handler<?php echo (count($this->items) < 2 || !$user->authorise('core.edit', 'com_kinoarhiv.movie.'.$item->id)) ? ' inactive tip-top' : ''; ?>"><i class="icon-menu"></i></span>
						<input type="hidden" name="ord[]" value="<?php echo $item->id; ?>" />
					</td>
					<td class="center hidden-phone">
						<?php echo JHtml::_('grid.id', $i, $item->id, false, 'id'); ?>
					</td>
					<td class="center">
						<div class="btn-group">
							<?php echo JHtml::_('jgrid.published', $item->state, $i, '', $canChange, 'cb'); ?>
						</div>
					</td>
					<td class="nowrap has-context">
						<div class="pull-left">
							<?php if ($item->language == '*'): ?>
								<?php $language = JText::alt('JALL', 'language'); ?>
							<?php else: ?>
								<?php $language = $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
							<?php endif; ?>
							<?php if ($canEdit): ?>
								<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&controller=movies&task=edit&id[]='.$item->id); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?>">
									<?php echo $this->escape($item->title); ?><?php echo ($item->year != '0000') ? ' ('.$item->year.')' : ''; ?></a>
							<?php else: ?>
								<span><?php echo $this->escape($item->title); ?><?php echo ($item->year != '0000') ? ' ('.$item->year.')' : ''; ?></span> 
							<?php endif; ?>
							<div class="small"><?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $item->alias); ?></div>
						</div>
					</td>
					<td class="small hidden-phone">
						<a href="index.php?option=com_kinoarhiv&view=mediamanager&section=movie&type=gallery&tab=3&id=<?php echo (int)$item->id; ?>" class="hasTooltip" title="<?php echo JText::_('COM_KA_MOVIES_GALLERY'); ?>" target="_blank"><img src="components/com_kinoarhiv/assets/images/icons/picture.png" border="0" /></a>
						<a href="index.php?option=com_kinoarhiv&view=mediamanager&section=movie&type=trailers&id=<?php echo (int)$item->id; ?>" class="hasTooltip" title="<?php echo JText::_('COM_KA_MOVIES_TRAILERS'); ?>" target="_blank"><img src="components/com_kinoarhiv/assets/images/icons/film.png" border="0" /></a>
						<a href="index.php?option=com_kinoarhiv&view=mediamanager&section=movie&type=sounds&id=<?php echo (int)$item->id; ?>" class="hasTooltip" title="<?php echo JText::_('COM_KA_MOVIES_SOUNDS'); ?>" target="_blank"><img src="components/com_kinoarhiv/assets/images/icons/music.png" border="0" /></a>
						<a href="javascript:void(0);" class="hasTooltip dd-relations" title="<?php echo JText::_('COM_KA_COUNTRIES_RELATIONS_BUTTON_TITLE'); ?>"><img src="components/com_kinoarhiv/assets/images/icons/arrow_switch.png" border="0" /></a>
						<ul class="dd-relations-menu ui-widget ui-widget-content ui-corner-all">
							<li><a href="index.php?option=com_kinoarhiv&view=relations&task=countries&mid=<?php echo (int)$item->id; ?>" target="_blank">&rarr; <?php echo JText::_('COM_KA_CP_COUNTRIES'); ?></a></li>
							<li><a href="index.php?option=com_kinoarhiv&view=relations&task=genres&mid=<?php echo (int)$item->id; ?>" target="_blank">&rarr; <?php echo JText::_('COM_KA_CP_GENRES'); ?></a></li>
							<li><a href="index.php?option=com_kinoarhiv&view=relations&task=awards&award_type=0&mid=<?php echo (int)$item->id; ?>" target="_blank">&rarr; <?php echo JText::_('COM_KA_AW_FIELD_TITLE'); ?></a></li>
						</ul>
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
						<?php echo (int)$item->id; ?>
					</td>
				</tr>
				<?php endforeach;
			endif; ?>
			</tbody>
		</table>
		<?php echo $this->pagination->getListFooter(); ?>

		<input type="hidden" name="controller" value="movies" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</form>

	<div class="rel-menu">
		<ul id="relations_menu">
			<li><a href="index.php?option=com_kinoarhiv&view=relations&task=countries"><?php echo JText::_('COM_KA_CP_COUNTRIES').' &harr; '.JText::_('COM_KA_CP_MOVIES'); ?></a></li>
			<li><a href="index.php?option=com_kinoarhiv&view=relations&task=genres"><?php echo JText::_('COM_KA_CP_GENRES').' &harr; '.JText::_('COM_KA_CP_MOVIES'); ?></a></li>
			<li><a href="index.php?option=com_kinoarhiv&view=relations&task=awards&award_type=0"><?php echo JText::_('COM_KA_AW_FIELD_TITLE').' &harr; '.JText::_('COM_KA_CP_MOVIES'); ?></a></li>
		</ul>
	</div>
</div>
