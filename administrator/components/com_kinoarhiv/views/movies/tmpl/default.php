<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2018 Libra.ms. All rights reserved.
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
$columns   = 10;
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task === 'movies.edit' && jQuery('#articleList :checkbox:checked').length > 1) {
			alert('<?php echo JText::_('COM_KA_ITEMS_EDIT_DENIED'); ?>');

			return;
		} else if (task === 'movies.remove') {
			if (!confirm('<?php echo JText::_('COM_KA_MOVIES_REMOVE_TRAILERS_BEFORE', false, false); ?>')) {
				return;
			}
		}

		Joomla.submitform(task);
	};

	jQuery(document).ready(function($){
		$('.js-stools-btn-clear').parent().after(
			'<div class="btn-wrapper">' +
				'<button class="btn search-help" type="button"' +
						'onclick="Aurora.message([{text: \'<?php echo JText::_('COM_KA_MOVIES_SEARCH_HELP'); ?>\'}], \'#system-message-container\', {replace: true});">' +
					'<span class="icon-help"></span></button>' +
			'</div>'
		);
	});
</script>
<div id="j-main-container">
	<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movies'); ?>" method="post"
		  name="adminForm" id="adminForm" autocomplete="off">
		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
		<div class="clearfix"> </div>

		<table class="table table-striped sortable" id="articleList"
			   data-sort-url="index.php?option=com_kinoarhiv&task=saveOrder&items=movies&tmpl=component&format=json">
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
					<th width="7%" style="min-width:35px;" class="nowrap center hidden-phone hidden-tablet"></th>
					<th width="10%" class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort',  'JAUTHOR', 'a.created_by', $listDirn, $listOrder); ?>
					</th>
					<th width="6%" class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'JDATE', 'a.created', $listDirn, $listOrder); ?>
					</th>
					<th width="6%" class="nowrap center">
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
				foreach ($this->items as $i => $item) :
					$canEdit   = $user->authorise('core.edit', 'com_kinoarhiv.movie.' . $item->id);
					$canChange = $user->authorise('core.edit.state', 'com_kinoarhiv.movie.' . $item->id);
					$title     = KAContentHelper::formatItemTitle($item->title, '', $item->year);
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
					<td class="center hidden-phone">
						<div class="btn-group">
							<?php echo JHtml::_('jgrid.published', $item->state, $i, 'movies.', $canChange, 'cb'); ?>
						</div>
					</td>
					<td class="has-context">
						<div class="pull-left">
							<?php
							if ($item->language == '*')
							{
								$language = JText::alt('JALL', 'language');
							}
							else
							{
								$language = $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED');
							}
							?>
							<?php if ($canEdit): ?>
								<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&task=movies.edit&id=' . $item->id); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?>">
									<?php echo $this->escape($title); ?></a>
							<?php else: ?>
								<span><?php echo $this->escape($title); ?></span>
							<?php endif; ?>
							<div class="small"><?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $item->alias); ?></div>
						</div>
						<div class="hidden-desktop" style="float: left; clear: both;">
							<a href="index.php?option=com_kinoarhiv&view=mediamanager&section=movie&type=gallery&tab=3&id=<?php echo (int) $item->id; ?>"
							   class="hasTooltip" title="<?php echo JText::_('COM_KA_MOVIES_GALLERY'); ?>" target="_blank">
								<img src="<?php echo JUri::root(); ?>media/com_kinoarhiv/images/icons/picture.png" border="0" />
							</a>
							<a href="index.php?option=com_kinoarhiv&view=mediamanager&section=movie&type=trailers&id=<?php echo (int) $item->id; ?>"
							   class="hasTooltip" title="<?php echo JText::_('COM_KA_MOVIES_TRAILERS'); ?>" target="_blank">
								<img src="<?php echo JUri::root(); ?>media/com_kinoarhiv/images/icons/film.png" border="0" />
							</a>
							<a href="index.php?option=com_kinoarhiv&view=music&type=albums&movie_id=<?php echo (int) $item->id; ?>"
							   class="hasTooltip" title="<?php echo JText::_('COM_KA_MOVIES_SOUNDS'); ?>" target="_blank">
								<img src="<?php echo JUri::root(); ?>media/com_kinoarhiv/images/icons/music.png" border="0" />
							</a>
							<a href="index.php?option=com_kinoarhiv&view=reviews&mid=<?php echo (int) $item->id; ?>"
							   class="hasTooltip" title="<?php echo JText::_('COM_KA_REVIEWS_TAB'); ?>" target="_blank">
								<img border="0" src="<?php echo JUri::root(); ?>media/com_kinoarhiv/images/icons/comments_16.png" />
							</a>
							<div class="dropdown" style="display: inline-block;">
								<a class="dropdown-toggle hasTooltip" data-toggle="dropdown" href="#" title="<?php echo JText::_('COM_KA_TABLES_RELATIONS'); ?>"><span	class="icon-out-2"></span></a>
								<ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
									<li>
										<a href="index.php?option=com_kinoarhiv&view=relations&task=countries&element=movies&mid=<?php echo (int) $item->id; ?>" target="_blank"><?php echo JText::_('COM_KA_COUNTRIES_TITLE'); ?></a>
									</li>
									<li>
										<a href="index.php?option=com_kinoarhiv&view=relations&task=genres&element=movies&mid=<?php echo (int) $item->id; ?>" target="_blank"><?php echo JText::_('COM_KA_GENRES_TITLE'); ?></a>
									</li>
									<li>
										<a href="index.php?option=com_kinoarhiv&view=relations&task=awards&element=movies&award_type=0&mid=<?php echo (int) $item->id; ?>" target="_blank"><?php echo JText::_('COM_KA_AWARDS_TITLE'); ?></a>
									</li>
								</ul>
							</div>
						</div>
					</td>
					<td class="icons-list hidden-phone hidden-tablet">
						<p>
							<a href="index.php?option=com_kinoarhiv&view=mediamanager&section=movie&type=gallery&tab=3&id=<?php echo (int) $item->id; ?>"
							   class="hasTooltip" title="<?php echo JText::_('COM_KA_MOVIES_GALLERY'); ?>" target="_blank">
								<img src="<?php echo JUri::root(); ?>media/com_kinoarhiv/images/icons/picture.png" border="0" />
							</a>
							<a href="index.php?option=com_kinoarhiv&view=mediamanager&section=movie&type=trailers&id=<?php echo (int) $item->id; ?>"
							   class="hasTooltip" title="<?php echo JText::_('COM_KA_MOVIES_TRAILERS'); ?>" target="_blank">
								<img src="<?php echo JUri::root(); ?>media/com_kinoarhiv/images/icons/film.png" border="0" />
							</a>
							<a href="index.php?option=com_kinoarhiv&view=music&type=albums&movie_id=<?php echo (int) $item->id; ?>"
							   class="hasTooltip" title="<?php echo JText::_('COM_KA_MOVIES_SOUNDS'); ?>" target="_blank">
								<img src="<?php echo JUri::root(); ?>media/com_kinoarhiv/images/icons/music.png" border="0" />
							</a>
						</p>
						<a href="index.php?option=com_kinoarhiv&view=reviews&mid=<?php echo (int) $item->id; ?>"
						   class="hasTooltip" title="<?php echo JText::_('COM_KA_REVIEWS_TAB'); ?>" target="_blank">
							<img border="0" src="<?php echo JUri::root(); ?>media/com_kinoarhiv/images/icons/comments_16.png" />
						</a>
						<div class="dropdown" style="display: inline-block;">
							<a class="dropdown-toggle hasTooltip" data-toggle="dropdown" href="#" title="<?php echo JText::_('COM_KA_TABLES_RELATIONS'); ?>"><span	class="icon-out-2"></span></a>
							<ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
								<li>
									<a href="index.php?option=com_kinoarhiv&view=relations&task=countries&element=movies&mid=<?php echo (int) $item->id; ?>" target="_blank"><?php echo JText::_('COM_KA_COUNTRIES_TITLE'); ?></a>
								</li>
								<li>
									<a href="index.php?option=com_kinoarhiv&view=relations&task=genres&element=movies&mid=<?php echo (int) $item->id; ?>" target="_blank"><?php echo JText::_('COM_KA_GENRES_TITLE'); ?></a>
								</li>
								<li>
									<a href="index.php?option=com_kinoarhiv&view=relations&task=awards&element=movies&award_type=0&mid=<?php echo (int) $item->id; ?>" target="_blank"><?php echo JText::_('COM_KA_AWARDS_TITLE'); ?></a>
								</li>
							</ul>
						</div>
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
						<a href="<?php echo JRoute::_('index.php?option=com_users&task=user.edit&id=' . (int) $item->created_by); ?>"
						   title="<?php echo JText::_('JAUTHOR'); ?>"><?php echo $this->escape($item->author_name); ?></a>
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
