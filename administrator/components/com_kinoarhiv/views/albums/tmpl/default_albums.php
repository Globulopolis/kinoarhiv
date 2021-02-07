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
JHtml::_('stylesheet', 'media/com_kinoarhiv/jqueryui/' . $this->params->get('ui_theme') . '/jquery-ui.min.css');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery-ui.min.js');

$user      = JFactory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'a.ordering';
$columns   = 8;
?>
<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off">
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
					<th width="1%" style="min-width:55px;" class="nowrap center hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
					</th>
					<th>
						<?php echo JHtml::_('searchtools.sort', 'COM_KA_MUSIC_ALBUMS_HEADING', 'a.title', $listDirn, $listOrder); ?>
					</th>
					<th width="7%" style="min-width:35px;" class="nowrap center hidden-phone hidden-tablet"></th>
					<th width="10%" class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
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
						$canEdit   = $user->authorise('core.edit',       'com_kinoarhiv.album.' . $item->id);
						$canChange = $user->authorise('core.edit.state', 'com_kinoarhiv.album.' . $item->id);
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
								<?php echo JHtml::_('jgrid.published', $item->state, $i, 'albums.', $canChange, 'cb'); ?>
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
									<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=album&task=albums.edit&id=' . $item->id); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?>">
										<?php echo $this->escape($title); ?></a>
								<?php else: ?>
									<span><?php echo $this->escape($title); ?></span>
								<?php endif; ?>
								<div class="small"><?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $item->alias); ?></div>
								<div class="small"><?php echo $item->tracks_path; ?></div>
							</div>
							<div class="hidden-desktop" style="float: left; clear: both;">
								<a href="index.php?option=com_kinoarhiv&view=mediamanager&section=album&type=gallery&tab=1&id=<?php echo (int) $item->id; ?>"
								   class="hasTooltip" title="<?php echo JText::_('COM_KA_MOVIES_GALLERY'); ?>" target="_blank">
									<img src="<?php echo JUri::root(); ?>media/com_kinoarhiv/images/icons/picture.png" border="0" />
								</a>
								<a href="index.php?option=com_kinoarhiv&view=reviews&item_type=1&item_id=<?php echo (int) $item->id; ?>"
								   class="hasTooltip" title="<?php echo JText::_('COM_KA_REVIEWS_TAB'); ?>" target="_blank">
									<img border="0" src="<?php echo JUri::root(); ?>media/com_kinoarhiv/images/icons/comments_16.png" />
								</a>
								<div class="dropdown" style="display: inline-block;">
									<a class="dropdown-toggle hasTooltip" data-toggle="dropdown" href="#"
									   title="<?php echo JText::_('COM_KA_TABLES_RELATIONS'); ?>"><span class="icon-out-2"></span>
									</a>
									<ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
										<li>
											<a href="index.php?option=com_kinoarhiv&view=relations&task=genres&element=albums&mid=<?php echo (int) $item->id; ?>" target="_blank"><?php echo JText::_('COM_KA_GENRES_TITLE'); ?></a>
										</li>
										<li>
											<a href="index.php?option=com_kinoarhiv&view=relations&task=awards&element=albums&award_type=0&mid=<?php echo (int) $item->id; ?>" target="_blank"><?php echo JText::_('COM_KA_AWARDS_TITLE'); ?></a>
										</li>
									</ul>
								</div>
							</div>
						</td>
						<td class="icons-list hidden-phone hidden-tablet">
							<a href="index.php?option=com_kinoarhiv&view=mediamanager&section=album&type=gallery&tab=0&id=<?php echo (int) $item->id; ?>"
							   class="hasTooltip" title="<?php echo JText::_('COM_KA_MOVIES_GALLERY'); ?>" target="_blank">
								<img src="<?php echo JUri::root(); ?>media/com_kinoarhiv/images/icons/picture.png" border="0" />
							</a>
							<a href="index.php?option=com_kinoarhiv&view=reviews&item_type=1&item_id=<?php echo (int) $item->id; ?>"
							   class="hasTooltip" title="<?php echo JText::_('COM_KA_REVIEWS_TAB'); ?>" target="_blank">
								<img border="0" src="<?php echo JUri::root(); ?>media/com_kinoarhiv/images/icons/comments_16.png" />
							</a>
							<div class="dropdown" style="display: inline-block;">
								<a class="dropdown-toggle hasTooltip" data-toggle="dropdown" href="#"
								   title="<?php echo JText::_('COM_KA_TABLES_RELATIONS'); ?>"><span class="icon-out-2"></span>
								</a>
								<ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
									<li>
										<a href="index.php?option=com_kinoarhiv&view=relations&task=countries&element=albums&mid=<?php echo (int) $item->id; ?>" target="_blank"><?php echo JText::_('COM_KA_COUNTRIES_TITLE'); ?></a>
									</li>
									<li>
										<a href="index.php?option=com_kinoarhiv&view=relations&task=genres&element=albums&mid=<?php echo (int) $item->id; ?>" target="_blank"><?php echo JText::_('COM_KA_GENRES_TITLE'); ?></a>
									</li>
									<li>
										<a href="index.php?option=com_kinoarhiv&view=relations&task=awards&element=albums&award_type=0&mid=<?php echo (int) $item->id; ?>" target="_blank"><?php echo JText::_('COM_KA_AWARDS_TITLE'); ?></a>
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

		<input type="hidden" name="type" value="albums" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
