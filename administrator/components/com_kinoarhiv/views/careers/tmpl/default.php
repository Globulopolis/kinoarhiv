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
$saveOrder = $listOrder == 'a.ordering';
$columns   = 7;
?>
<script type="text/javascript">
	Joomla.submitbutton = function(pressbutton) {
		if (pressbutton == 'careers.edit' && jQuery('#articleList :checkbox:checked').length > 1) {
			alert('<?php echo JText::_('COM_KA_ITEMS_EDIT_DENIED'); ?>');
			return;
		}
		if (pressbutton == 'relations') {
			document.location.href = 'index.php?option=com_kinoarhiv&view=relations&task=careers&element=movies';
			return;
		}
		Joomla.submitform(pressbutton);
	};

	jQuery(document).ready(function($){
		<?php if (count($this->items) > 1): ?>
		$('#articleList tbody').sortable({
			axis:'y',
			cancel: 'input,textarea,button,select,option,.inactive',
			placeholder: 'ui-state-highlight',
			handle: '.sortable-handler',
			cursor: 'move',
			helper: function(e, tr){
				var $originals = tr.children();
				var $helper = tr.clone();

				$helper.children().each(function(index){
					$(this).width($originals.eq(index).width());
				});
				return $helper;
			},
			update: function(){
				$.post('index.php?option=com_kinoarhiv&task=careers.saveOrder&format=json', $('#articleList tbody .order input').serialize() + '&<?php echo JSession::getFormToken(); ?>=1', function(response){
					if (!response.success) {
						showMsg('#system-message-container', response.message);
					}
				}).fail(function(xhr, status, error){
					showMsg('#system-message-container', error);
				});
			}
		});
		<?php endif; ?>
	});
</script>

<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=careers'); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off">
	<div id="j-main-container">
		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
		<div class="clearfix"> </div>

		<table class="table table-striped" id="articleList">
			<thead>
				<tr>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
					</th>
					<th width="1%" class="center">
						<?php echo JHtml::_('grid.checkall'); ?>
					</th>
					<th>
						<?php echo JHtml::_('searchtools.sort', 'COM_KA_CAREER_FIELD_TITLE', 'a.title', $listDirn, $listOrder); ?>
					</th>
					<th width="7%" class="nowrap hidden-phone center">
						<?php echo JHtml::_('searchtools.sort', 'COM_KA_FIELD_CAREER_MAINPAGE', 'a.is_mainpage', $listDirn, $listOrder); ?>
					</th>
					<th width="7%" class="nowrap hidden-phone center">
						<?php echo JHtml::_('searchtools.sort', 'COM_KA_FIELD_CAREER_AMPLUA', 'a.is_amplua', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
					</th>
					<th width="7%" class="nowrap center">
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
					$canChange = $user->authorise('core.edit.state', 'com_kinoarhiv');
					$iconClass = '';
				?>
				<tr class="row<?php echo $i % 2; ?>">
					<td class="order nowrap center hidden-phone">
						<?php
						if (!$canChange)
						{
							$iconClass = ' inactive';
						}
						elseif (!$saveOrder)
						{
							$iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
						}
						?>
						<span class="sortable-handler<?php echo $iconClass ?>"><span class="icon-menu"></span></span>
					<?php if ($canChange && $saveOrder) : ?>
						<input type="hidden" name="ord[]" value="<?php echo $item->id; ?>" />
					<?php endif; ?>
					</td>
					<td class="center">
						<?php echo JHtml::_('grid.id', $i, $item->id, false, 'id'); ?>
					</td>
					<td class="nowrap has-context">
						<div class="pull-left">
							<?php if ($this->canEdit) : ?>
								<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&task=careers.edit&id[]=' . $item->id); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?>">
									<?php echo $this->escape($item->title); ?></a>
							<?php else : ?>
								<span><?php echo $this->escape($item->title); ?></span>
							<?php endif; ?>
						</div>
					</td>
					<td class="small hidden-phone center">
						<?php echo JHtml::_('jgrid.state', array(
							1 => array('careers.offmainpage', 'COM_KA_FIELD_CAREER_MAINPAGE', 'COM_KA_FIELD_CAREER_MAINPAGE_UNPUBLISH', 'COM_KA_FIELD_CAREER_MAINPAGE_PUBLISHED', true, 'publish', 'publish'),
							0 => array('careers.onmainpage', 'COM_KA_FIELD_CAREER_MAINPAGE', 'COM_KA_FIELD_CAREER_MAINPAGE_PUBLISH', 'COM_KA_FIELD_CAREER_MAINPAGE_UNPUBLISHED', true, 'unpublish', 'unpublish')
							), $item->is_mainpage, $i, '', $this->canEdit, 'cbm'); ?>
					</td>
					<td class="small hidden-phone center">
						<?php if ($item->is_amplua == 0): ?>
						<span class="icon-unpublish"></span> <?php echo JText::_('JNO'); ?>
						<?php else: ?>
						<span class="icon-publish"></span> <?php echo JText::_('JYES'); ?>
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
						<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=relations&task=careers&element=movies&id=' . $item->id); ?>" class="hasTooltip hidden-phone" title="<?php echo JText::_('COM_KA_TABLES_RELATIONS') . ': ' . $this->escape($item->title); ?>"><span class="icon-out-2"></span></a>
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
