<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2018 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

JHtml::_('script', 'media/com_kinoarhiv/js/sortable.min.js');
?>
<script type="text/javascript">
	jQuery(document).ready(function ($) {
		$('.hasDesc').click(function () {
			$(this).next('tr').toggle();
			if ($(this).next('tr').is(':hidden')) {
				$('td:last span', this).attr('class', 'icon icon-chevron-down uk-icon-caret-down');
			} else {
				$('td:last span', this).attr('class', 'icon icon-chevron-up uk-icon-caret-up');
			}
		});
	});
</script>
<div class="uk-article ka-content">
	<?php if ($this->params->get('use_alphabet') == 1):
		echo JLayoutHelper::render(
			'layouts.navigation.movie_alphabet',
			array('url' => 'index.php?option=com_kinoarhiv&view=movies&content=movies&Itemid=' . $this->moviesItemid, 'params' => $this->params),
			JPATH_COMPONENT
		);
	endif; ?>

	<article class="uk-article item">
		<?php
		echo JLayoutHelper::render(
			'layouts.navigation.movie_item_header',
			array(
				'params' => $this->params,
				'item'   => $this->item,
				'itemid' => $this->moviesItemid,
				'guest'  => $this->user->get('guest'),
				'url'    => 'index.php?option=com_kinoarhiv&view=movie&id=' . $this->item->id . '&Itemid=' . $this->moviesItemid
			),
			JPATH_COMPONENT
		);
		?>
		<?php echo $this->item->event->afterDisplayTitle; ?>
		<?php echo $this->item->event->beforeDisplayContent; ?>

		<div class="clear"></div>
		<div class="content content-list clearfix">
			<div>
				<div class="poster">
					<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=posters&id=' . $this->item->id . '&Itemid=' . $this->moviesItemid); ?>" title="<?php echo $this->escape(KAContentHelper::formatItemTitle($this->item->title, '', $this->item->year)); ?>"><img src="<?php echo $this->item->poster; ?>" alt="<?php echo JText::_('COM_KA_POSTER_ALT') . $this->escape($this->item->title); ?>" itemprop="image"/></a>
				</div>
				<div class="introtext">
					<div class="text"><?php echo $this->item->text; ?></div>
					<div class="separator"></div>
					<div class="plot"><?php echo $this->item->plot; ?></div>

					<?php if ($this->params->get('ratings_show_frontpage') == 1):
						echo JLayoutHelper::render(
							'layouts.content.ratings_movie',
							array('params' => $this->params, 'item' => $this->item),
							JPATH_COMPONENT
						);
					endif;

					echo JLayoutHelper::render('layouts.content.votes_movie',
						array(
							'params'  => $this->params,
							'item'    => $this->item,
							'guest'   => $this->user->get('guest'),
							'itemid'  => $this->itemid,
							'view'    => $this->view
						),
						JPATH_COMPONENT
					);
					?>
				</div>
			</div>
			<?php if (count($this->item->items) > 0): ?>
				<div class="clear"></div>
				<div>
					<table class="table table-striped table-hover uk-table uk-table-striped uk-table-hover premiere-table" data-sortable>
						<thead>
						<tr>
							<th title="<?php echo JText::_('JGLOBAL_CLICK_TO_SORT_THIS_COLUMN'); ?>" data-sorted="true"
								data-sorted-direction="descending">
								<?php echo JText::_('COM_KA_SEARCH_ADV_MOVIES_PREMIERE_DATE'); ?>
							</th>
							<th title="<?php echo JText::_('JGLOBAL_CLICK_TO_SORT_THIS_COLUMN'); ?>">
								<?php echo JText::_('COM_KA_COUNTRY'); ?>
							</th>
							<th width="2%" data-sortable="false">&nbsp;</th>
						</tr>
						</thead>
						<tbody>
						<?php foreach ($this->item->items as $row):
							$trClass = ($row->info != '') ? ' hasDesc info uk-alert' : '';
							?>
							<tr class="<?php echo $trClass; ?>">
								<td>
									<span class="hasTooltip"
										  title="<?php echo JHtml::_('date', $row->premiere_date, JText::_('DATE_FORMAT_LC5')); ?>">
										<?php echo JHtml::_('date', $row->premiere_date, JText::_('DATE_FORMAT_LC3')); ?>
									</span>
									<span id="row-<?php echo $row->id; ?>"></span>
								</td>
								<td>
									<?php if (!empty($row->country_id)): ?>
										<img class="flag-dd" src="media/com_kinoarhiv/images/icons/countries/<?php echo $row->code; ?>.png"
											 alt="<?php echo $row->name; ?>"/>
										<?php echo $row->name; ?>
									<?php else: ?>
										<?php echo JText::_('COM_KA_PREMIERE_DATE_WORLDWIDE'); ?>
									<?php endif; ?>
								</td>
								<td>
									<?php if ($row->info != ''): ?>
										<span class="icon icon-chevron-down"></span>
									<?php endif; ?>
								</td>
							</tr>
							<?php if ($row->info != ''): ?>
							<tr style="display: none;">
								<td colspan="4">
									<div><?php echo str_replace(array("\r\n", "\r", "\n"), '<br/>', $row->info); ?></div>
									<div class="pull-right">
										<a href="#row-<?php echo $row->id; ?>"><?php echo JText::_('COM_KA_TO_TOP'); ?></a>
									</div>
								</td>
							</tr>
							<?php endif; ?>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
		</div>

		<?php echo $this->item->event->afterDisplayContent; ?>
	</article>
</div>
