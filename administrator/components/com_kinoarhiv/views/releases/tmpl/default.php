<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *  
 * @copyright   Copyright (C) 2017 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery-ui.min.js');

$user      = JFactory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'a.ordering';
$columns   = 9;
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task === 'releases.edit' && jQuery('#articleList :checkbox:checked').length > 1) {
			alert('<?php echo JText::_('COM_KA_ITEMS_EDIT_DENIED'); ?>');
			return;
		}
		Joomla.submitform(task);
	};

	jQuery(document).ready(function($){
		$('.js-stools-btn-clear').parent().after('<div class="btn-wrapper"><button class="btn search-help" type="button" onclick="showMsg(\'#system-message-container\', \'<?php echo JText::_('COM_KA_RELEASES_SEARCH_HELP'); ?>\');"><span class="icon-help"></span></button></div>');

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
			update: function(e, ui){
				$.post('index.php?option=com_kinoarhiv&task=saveOrder&items=releases&tmpl=component', $('#articleList tbody .order input.ord').serialize() + '&<?php echo JSession::getFormToken(); ?>=1&movie_id=' + $(ui.item).find('input[name="movie_id"]').val(), function(response){
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
					<th width="1%" class="center">
						<?php echo JHtml::_('grid.checkall'); ?>
					</th>
					<th width="11%" class="center">
						<?php echo JHtml::_('searchtools.sort', 'COM_KA_FIELD_RELEASE_DATE_LABEL', 'r.release_date', $listDirn, $listOrder); ?>
					</th>
					<th width="30%" style="min-width:55px;">
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
					<th width="10%" class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'r.language', $listDirn, $listOrder); ?>
					</th>
					<th width="5%" class="nowrap center">
						<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'r.id', $listDirn, $listOrder); ?>
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
					$canEdit   = $user->authorise('core.edit', 'com_kinoarhiv');
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
							<input type="hidden" name="movie_id" value="<?php echo $item->movie_id; ?>" />
						<?php endif; ?>
					</td>
					<td class="center">
						<?php echo JHtml::_('grid.id', $i, $item->id, false, 'id'); ?>
					</td>
					<td class="center">
						<a href="index.php?option=com_kinoarhiv&view=releases&task=releases.edit&id[]=<?php echo $item->id; ?>" title="<?php echo JText::_('COM_KA_EDIT'); ?>"><?php echo $item->release_date; ?></a>
					</td>
					<td>
						<?php echo $this->escape($item->title); ?><?php echo $item->year != '0000' ? ' (' . $item->year . ')' : ''; ?>
					</td>
					<td class="nowrap hidden-phone">
						<?php echo $item->company_name; ?>
					</td>
					<td class="nowrap hidden-phone">
						<?php if ($item->name != ''): ?>
							<img class="flag-dd" src="<?php echo JUri::root(); ?>media/com_kinoarhiv/images/icons/countries/<?php echo $item->code; ?>.png" />
						<?php echo $item->name;
						else:
							echo 'N/a';
						endif; ?>
					</td>
					<td class="hidden-phone">
						<?php echo $item->media_type; ?>
					</td>
					<td class="small hidden-phone">
						<?php if ($item->language == '*'):?>
							<?php echo JText::alt('JALL', 'language'); ?>
						<?php else:?>
							<?php echo $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
						<?php endif;?>
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
	</form>
</div>
