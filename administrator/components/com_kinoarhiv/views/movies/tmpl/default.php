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
		var dirn;
		if (order != '<?php echo $listOrder; ?>') {
			dirn = 'asc';
		} else {
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	};

	Joomla.submitbutton = function(task) {
		if (task == 'edit' && jQuery('#articleList :checkbox:checked').length > 1) {
			alert('<?php echo JText::_('COM_KA_ITEMS_EDIT_DENIED'); ?>');
			return;
		} else if (task == 'menu') {
			jQuery('.rel-menu').toggle();
			return;
		}
		Joomla.submitform(task);
	};

	jQuery(document).ready(function($){
		$('.js-stools-btn-clear').parent().after('<div class="btn-wrapper"><button class="btn search-help" type="button" onclick="showMsg(\'#articleList\', \'<?php echo JText::_('COM_KA_MOVIES_SEARCH_HELP'); ?>\');"><span class="icon-help"></span></button></div>');

		var toolbar_tools = $('#toolbar-tools');
		$('.rel-menu').css({
			left: toolbar_tools.offset().left+'px',
			top: (toolbar_tools.offset().top + toolbar_tools.height() + 5)+'px'
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
					<th width="1%" style="min-width:55px;" class="nowrap center hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
					</th>
					<th>
						<?php echo JHtml::_('searchtools.sort', 'COM_KA_FIELD_MOVIE_LABEL', 'a.title', $listDirn, $listOrder); ?>
					</th>
					<th width="7%" style="min-width:35px;" class="nowrap center"></th>
					<th width="10%" class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort',  'JAUTHOR', 'a.created_by', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'JDATE', 'a.created', $listDirn, $listOrder); ?>
					</th>
					<th width="5%" class="nowrap center">
						<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tbody>
			<?php if (count($this->items) == 0): ?>
				<tr>
					<td colspan="10" class="center"><?php echo JText::_('COM_KA_NO_ITEMS'); ?></td>
				</tr>
			<?php else:
				foreach ($this->items as $i => $item) :
					$canEdit    = $user->authorise('core.edit', 'com_kinoarhiv.movie.'.$item->id);
					$canChange  = $user->authorise('core.edit.state', 'com_kinoarhiv.movie.'.$item->id);
				?>
				<tr class="row<?php echo $i % 2; ?>">
					<td class="order nowrap center hidden-phone">
						<span class="sortable-handler<?php echo (count($this->items) < 2 || !$user->authorise('core.edit', 'com_kinoarhiv.movie.'.$item->id)) ? ' inactive tip-top' : ''; ?>"><i class="icon-menu"></i></span>
						<input type="hidden" name="ord[]" value="<?php echo $item->id; ?>" />
					</td>
					<td class="center">
						<?php echo JHtml::_('grid.id', $i, $item->id, false, 'id'); ?>
					</td>
					<td class="center hidden-phone">
						<div class="btn-group">
							<?php echo JHtml::_('jgrid.published', $item->state, $i, '', $canChange, 'cb'); ?>
						</div>
					</td>
					<td class="has-context">
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
					<td class="small">
						<a href="index.php?option=com_kinoarhiv&view=mediamanager&section=movie&type=gallery&tab=3&id=<?php echo (int)$item->id; ?>" class="hasTooltip" title="<?php echo JText::_('COM_KA_MOVIES_GALLERY'); ?>" target="_blank"><img src="components/com_kinoarhiv/assets/images/icons/picture.png" border="0" /></a>
						<a href="index.php?option=com_kinoarhiv&view=mediamanager&section=movie&type=trailers&id=<?php echo (int)$item->id; ?>" class="hasTooltip" title="<?php echo JText::_('COM_KA_MOVIES_TRAILERS'); ?>" target="_blank"><img src="components/com_kinoarhiv/assets/images/icons/film.png" border="0" /></a>
						<a href="index.php?option=com_kinoarhiv&view=music&type=albums&movie_id=<?php echo (int)$item->id; ?>" class="hasTooltip" title="<?php echo JText::_('COM_KA_MOVIES_SOUNDS'); ?>" target="_blank"><img src="components/com_kinoarhiv/assets/images/icons/music.png" border="0" /></a>&nbsp;
						<a href="javascript:void(0);" class="hasTooltip dd-relations hidden-phone" title="<?php echo JText::_('COM_KA_TABLES_RELATIONS'); ?>"><img src="components/com_kinoarhiv/assets/images/icons/arrow_switch.png" border="0" /></a>
						<ul class="dd-relations-menu ui-widget ui-widget-content ui-corner-all hidden-phone">
							<li><a href="index.php?option=com_kinoarhiv&view=relations&task=countries&element=movies&mid=<?php echo (int)$item->id; ?>" target="_blank">&rarr; <?php echo JText::_('COM_KA_COUNTRIES_TITLE'); ?></a></li>
							<li><a href="index.php?option=com_kinoarhiv&view=relations&task=genres&element=movies&mid=<?php echo (int)$item->id; ?>" target="_blank">&rarr; <?php echo JText::_('COM_KA_GENRES_TITLE'); ?></a></li>
							<li><a href="index.php?option=com_kinoarhiv&view=relations&task=awards&element=movies&award_type=0&mid=<?php echo (int)$item->id; ?>" target="_blank">&rarr; <?php echo JText::_('COM_KA_AWARDS_TITLE'); ?></a></li>
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
					<td class="small hidden-phone">
						<a href="<?php echo JRoute::_('index.php?option=com_users&task=user.edit&id='.(int) $item->created_by); ?>" title="<?php echo JText::_('JAUTHOR'); ?>"><?php echo $this->escape($item->author_name); ?></a>
					</td>
					<td class="nowrap small hidden-phone">
						<?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC4')); ?>
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

		<input type="hidden" name="controller" value="movies" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</form>

	<div class="rel-menu">
		<ul id="relations_menu">
			<li><a href="index.php?option=com_kinoarhiv&view=relations&task=countries&element=movies"><?php echo JText::_('COM_KA_COUNTRIES_TITLE').' &harr; '.JText::_('COM_KA_MOVIES_TITLE'); ?></a></li>
			<li><a href="index.php?option=com_kinoarhiv&view=relations&task=genres&element=movies"><?php echo JText::_('COM_KA_GENRES_TITLE').' &harr; '.JText::_('COM_KA_MOVIES_TITLE'); ?></a></li>
			<li><a href="index.php?option=com_kinoarhiv&view=relations&task=awards&element=movies&award_type=0"><?php echo JText::_('COM_KA_AWARDS_TITLE').' &harr; '.JText::_('COM_KA_MOVIES_TITLE'); ?></a></li>
		</ul>
	</div>
</div>
