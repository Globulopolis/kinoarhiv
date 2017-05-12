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

$user      = JFactory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$columns   = 9;
?>
<script type="text/javascript">
	Joomla.submitbutton = function(pressbutton) {
		if (pressbutton === 'reviews.edit' && jQuery('#articleList :checkbox:checked').length > 1) {
			alert('<?php echo JText::_('COM_KA_ITEMS_EDIT_DENIED'); ?>');
			return;
		}
		Joomla.submitform(pressbutton);
	};

	jQuery(document).ready(function($){
		$('.js-stools-btn-clear').parent().after('<div class="btn-wrapper"><button class="btn search-help" type="button" onclick="showMsg(\'#system-message-container\', \'<?php echo JText::_('COM_KA_REVIEWS_SEARCH_HELP'); ?>\');"><span class="icon-help"></span></button></div>');
	});
</script>

<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=reviews'); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off">
	<div id="j-main-container">
		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
		<div class="clearfix"> </div>

		<table class="table" id="articleList">
			<thead>
				<tr>
					<th width="1%" class="center">
						<?php echo JHtml::_('grid.checkall'); ?>
					</th>
					<th width="1%" style="min-width:55px;" class="nowrap center hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
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
					<td colspan="<?php echo $columns; ?>" class="center"><?php echo JText::_('COM_KA_NO_ITEMS'); ?></td>
				</tr>
			<?php else:
				foreach ($this->items as $i => $item):
					if ($item->type == 1)
					{
						$text_class = 'muted';
					}
					elseif ($item->type == 2)
					{
						$text_class = 'text-success';
					}
					elseif ($item->type == 3)
					{
						$text_class = 'text-error';
					}
					else
					{
						$text_class = '';
					}
				?>
				<tr class="row<?php echo $i % 2; ?>">
					<td class="center">
						<?php echo JHtml::_('grid.id', $i, $item->id, false, 'id'); ?>
					</td>
					<td class="center hidden-phone">
						<?php echo JHtml::_('jgrid.published', $item->state, $i, 'reviews.', $this->canEditState, 'cb'); ?>
					</td>
					<td>
						<span class="<?php echo $text_class; ?>"><?php echo JHtml::_('string.truncate', $this->escape($item->review), 400); ?></span><br />
						<?php if ($this->canEdit) : ?>
							<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&task=reviews.edit&id[]=' . $item->id); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?>"><?php echo JText::_('JACTION_EDIT'); ?></a>
						<?php endif; ?>
					</td>
					<td class="small center hidden-phone">
						<?php echo $item->created; ?>
					</td>
					<td class="small">
						<?php echo $item->movie; ?><br />
						<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=reviews&mid=' . $item->movie_id); ?>" title="<?php echo JText::_('COM_KA_REVIEWS_SEARCH_BY_MOVIE'); ?>" class="hasTooltip"><span class="icon-search"></span></a>
					</td>
					<td class="small hidden-phone">
						<?php echo $item->username; ?><br />
						<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=reviews&uid=' . $item->uid); ?>" title="<?php echo JText::_('COM_KA_REVIEWS_SEARCH_BY_USER'); ?>" class="hasTooltip"><span class="icon-search"></span></a>
						<a href="<?php echo JRoute::_('index.php?option=com_users&task=user.edit&id=' . $item->uid); ?>" title="<?php echo JText::sprintf('COM_KA_REVIEWS_USERS_EDIT_USER', $item->username); ?>" class="hasTooltip"><span class="icon-edit"></span></a>
					</td>
					<td class="small hidden-phone">
						<?php echo $item->ip; ?>
					</td>
					<td class="center">
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
