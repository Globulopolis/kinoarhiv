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
	};

	jQuery(document).ready(function($){
		$('.js-stools-btn-clear').parent().after('<div class="btn-wrapper"><button class="btn search-help" type="button" onclick="showMsg(\'#articleList\', \'<?php echo JText::_('COM_KA_REVIEWS_SEARCH_HELP'); ?>\');"><span class="icon-help"></span></button></div>');
	});
</script>

<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=reviews'); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off">
	<div id="j-main-container">
		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
		<div class="clearfix"> </div>

		<table class="table table-striped" id="articleList">
			<thead>
				<tr>
					<th width="1%" class="center">
						<?php echo JHtml::_('grid.checkall'); ?>
					</th>
					<th width="1%" style="min-width:55px" class="nowrap center hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
					</th>
					<th width="1%" style="min-width:55px" class="nowrap center hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'COM_KA_REVIEWS_FIELD_TYPE', 'a.type', $listDirn, $listOrder); ?>
					</th>
					<th>
						<?php echo JText::_('COM_KA_REVIEWS_FIELD_REVIEW'); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_SHOW_PUBLISH_DATE_LABEL', 'a.created', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'COM_KA_FIELD_MOVIE_LABEL', 'm.title', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'COM_KA_REVIEWS_FIELD_USER', 'u.username', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'COM_KA_REVIEWS_FIELD_USER_IP', 'a.ip', $listDirn, $listOrder); ?>
					</th>
					<th width="5%" class="nowrap center">
						<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tbody>
			<?php if (count($this->items) == 0): ?>
				<tr>
					<td colspan="9" class="center"><?php echo JText::_('COM_KA_NO_ITEMS'); ?></td>
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
					<td class="center hidden-phone">
						<?php if ($item->type == 2): ?>
							<img src="components/com_kinoarhiv/assets/images/icons/thumb_up.png" border="0">
						<?php elseif ($item->type == 3): ?>
							<img src="components/com_kinoarhiv/assets/images/icons/thumb_down.png" border="0">
						<?php else: ?>
						<?php endif; ?>
					</td>
					<td>
						<span><?php echo JHtml::_('string.truncate', $this->escape($item->review), 400); ?></span><br />
						<?php if ($this->canEdit) : ?>
							<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&controller=reviews&task=edit&id[]='.$item->id); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?>"><?php echo JText::_('JACTION_EDIT'); ?></a>
						<?php endif; ?>
					</td>
					<td class="small center hidden-phone">
						<?php echo $item->created; ?>
					</td>
					<td class="small">
						<?php echo $item->movie; ?><br />
						<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=reviews&mid='.$item->movie_id); ?>" title="<?php echo JText::_('COM_KA_REVIEWS_SEARCH_BY_MOVIE'); ?>" class="hasTooltip"><img src="components/com_kinoarhiv/assets/images/icons/search.png" border="0"></a>
					</td>
					<td class="small hidden-phone">
						<?php echo $item->username; ?><br />
						<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=reviews&uid='.$item->uid); ?>" title="<?php echo JText::_('COM_KA_REVIEWS_SEARCH_BY_USER'); ?>" class="hasTooltip"><img src="components/com_kinoarhiv/assets/images/icons/search.png" border="0"></a>
						<a href="<?php echo JRoute::_('index.php?option=com_users&task=user.edit&id='.$item->uid); ?>" title="<?php echo JText::sprintf('COM_KA_REVIEWS_USERS_EDIT_USER', $item->username); ?>" class="hasTooltip"><img src="components/com_kinoarhiv/assets/images/icons/user_edit.png" border="0"></a>
					</td>
					<td class="small hidden-phone">
						<?php echo $item->ip; ?>
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

		<input type="hidden" name="controller" value="reviews" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
