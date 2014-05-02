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
		}
		Joomla.submitform(task);
	}

	jQuery(document).ready(function($){
		$('.js-stools-btn-clear').parent().after('<div class="btn-wrapper"><button class="btn search-help" type="button" onclick="showMsg(\'#articleList\', \'<?php echo JText::_('COM_KA_RELEASES_SEARCH_HELP'); ?>\');"><span class="icon-help"></span></button></div>');

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
				$.post('index.php?option=com_kinoarhiv&controller=releases&task=saveOrder&format=json', $('#articleList tbody .order input.ord').serialize()+'&<?php echo JSession::getFormToken(); ?>=1&movie_id='+$(ui.item).find('input[name="movie_id"]').val(), function(response){
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
	<form action="index.php?option=com_kinoarhiv&view=releases" method="post" name="adminForm" id="adminForm" autocomplete="off">
		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
		<div class="clearfix"> </div>

		<table class="table table-striped" id="articleList">
			<thead>
				<tr>
					<th width="3%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('searchtools.sort', '', 'r.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
					</th>
					<th width="1%" class="center hidden-phone">
						<?php echo JHtml::_('grid.checkall'); ?>
					</th>
					<th width="11%" class="center hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'COM_KA_FIELD_RELEASE_DATE_LABEL', 'r.release_date', $listDirn, $listOrder); ?>
					</th>
					<th width="30%" style="min-width:55px">
						<?php echo JHtml::_('searchtools.sort', 'COM_KA_FIELD_MOVIE_LABEL', 'm.title', $listDirn, $listOrder); ?>
					</th>
					<th width="25%" class="nowrap hidden-phone">
						<?php echo JText::_('COM_KA_FIELD_PREMIERE_VENDOR'); ?>
					</th>
					<th width="15%" class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'COM_KA_FIELD_RELEASE_COUNTRY', 'c.name', $listDirn, $listOrder); ?>
					</th>
					<th width="15%" class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'COM_KA_RELEASES_MEDIATYPE_TITLE', 'r.media_type', $listDirn, $listOrder); ?>
					</th>
					<th width="5%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'r.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tbody>
			<?php if (count($this->items) == 0): ?>
				<tr>
					<td colspan="8" class="center hidden-phone"><?php echo JText::_('COM_KA_NO_ITEMS'); ?></td>
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
						<a href="index.php?option=com_kinoarhiv&view=releases&controller=releases&task=edit&id[]=<?php echo $item->id; ?>" title="<?php echo JText::_('COM_KA_EDIT'); ?>"><?php echo $item->release_date; ?></a>
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
						<?php if ($item->name != ''): ?>
							<img class="flag-dd" src="<?php echo JURI::root(); ?>components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/images/icons/countries/<?php echo $item->code; ?>.png" />
						<?php echo $item->name;
						else:
							echo 'N/a';
						endif; ?>
					</td>
					<td class="hidden-phone">
						<?php echo JText::_('COM_KA_RELEASES_MEDIATYPE_'.$item->media_type); ?>
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

		<input type="hidden" name="controller" value="releases" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
