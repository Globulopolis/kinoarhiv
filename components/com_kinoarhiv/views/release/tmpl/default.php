<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *  
 * @copyright   Copyright (C) 2017 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

use Joomla\String\StringHelper;

if (StringHelper::substr($this->params->get('media_rating_image_root_www'), 0, 1) == '/')
{
	$rating_image_www = JUri::base() . StringHelper::substr($this->params->get('media_rating_image_root_www'), 1);
}
else
{
	$rating_image_www = $this->params->get('media_rating_image_root_www');
}

JHtml::_('script', 'media/com_kinoarhiv/js/jquery.rateit.min.js');
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
		echo JLayoutHelper::render('layouts.navigation.alphabet', array('params' => $this->params, 'itemid' => $this->itemid), JPATH_COMPONENT);
	endif; ?>

	<article class="uk-article item">
		<?php
		echo JLayoutHelper::render(
			'layouts.navigation.movie_item_header',
			array('params' => $this->params, 'item' => $this->item, 'itemid' => $this->itemid),
			JPATH_COMPONENT
		);
		echo $this->item->event->afterDisplayTitle;
		echo $this->item->event->beforeDisplayContent; ?>

		<div class="clear"></div>
		<div class="content content-list clearfix">
			<div>
				<div class="poster">
					<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=posters&id=' . $this->item->id . '&Itemid=' . $this->itemid); ?>" title="<?php echo $this->escape(KAContentHelper::formatItemTitle($this->item->title, '', $this->item->year)); ?>"><img src="<?php echo $this->item->poster; ?>" border="0" alt="<?php echo JText::_('COM_KA_POSTER_ALT') . $this->escape($this->item->title); ?>" itemprop="image"/></a>
				</div>
				<div class="introtext">
					<div class="text"><?php echo $this->item->text; ?></div>
					<div class="separator"></div>
					<div class="plot"><?php echo $this->item->plot; ?></div>

					<?php if ($this->params->get('ratings_show_frontpage') == 1): ?>
						<div class="separator"></div>
						<div class="ratings-frontpage">
							<?php if (!empty($this->item->rate_custom)): ?>
								<div><?php echo $this->item->rate_custom; ?></div>
							<?php else: ?>
								<?php if ($this->params->get('ratings_show_img') == 1): ?>
									<div style="display: inline-block;">
										<?php if ($this->params->get('ratings_img_imdb') != 0 && !empty($this->item->imdb_id))
										{
											if (file_exists($this->params->get('media_rating_image_root') . '/imdb/' . $this->item->id . '_big.png'))
											{ ?>
												<a href="http://www.imdb.com/title/tt<?php echo $this->item->imdb_id; ?>/" rel="nofollow" target="_blank"><img src="<?php echo $rating_image_www; ?>/imdb/<?php echo $this->item->id; ?>_big.png" border="0"/></a>
											<?php }
										} ?>
										<?php if ($this->params->get('ratings_img_kp') != 0 && !empty($this->item->kp_id)): ?>
											<a href="https://www.kinopoisk.ru/film/<?php echo $this->item->kp_id; ?>/" rel="nofollow" target="_blank">
												<?php if ($this->params->get('ratings_img_kp_remote') == 0): ?>
													<img src="<?php echo $rating_image_www; ?>/kinopoisk/<?php echo $this->item->id; ?>_big.png" border="0"/>
												<?php else: ?>
													<img src="https://www.kinopoisk.ru/rating/<?php echo $this->item->kp_id; ?>.gif" border="0" style="padding-left: 1px;"/>
												<?php endif; ?>
											</a>
										<?php endif; ?>
										<?php if ($this->params->get('ratings_img_rotten') != 0 && !empty($this->item->rottentm_id)): ?>
											<?php if (file_exists($this->params->get('media_rating_image_root') . '/rottentomatoes/' . $this->item->id . '_big.png')): ?>
												<a href="https://www.rottentomatoes.com/m/<?php echo $this->item->rottentm_id; ?>/" rel="nofollow" target="_blank"><img src="<?php echo $rating_image_www; ?>/rottentomatoes/<?php echo $this->item->id; ?>_big.png" border="0"/></a>
											<?php endif; ?>
										<?php endif; ?>
										<?php if ($this->params->get('ratings_img_metacritic') != 0 && !empty($this->item->metacritics_id)): ?>
											<?php if (file_exists($this->params->get('media_rating_image_root') . '/metacritic/' . $this->item->id . '_big.png')): ?>
												<a href="http://www.metacritic.com/movie/<?php echo $this->item->metacritics_id; ?>/" rel="nofollow" target="_blank"><img src="<?php echo $rating_image_www; ?>/metacritic/<?php echo $this->item->id; ?>_big.png" border="0"/></a>
											<?php endif; ?>
										<?php endif; ?>
									</div>
								<?php else: ?>
									<?php if (!empty($this->item->imdb_votesum) && !empty($this->item->imdb_votes)): ?>
										<div id="rate-imdb">
											<span class="a"><?php echo JText::_('COM_KA_RATE_IMDB'); ?></span>
											<span class="b"><a href="http://www.imdb.com/title/tt<?php echo $this->item->imdb_id; ?>/?ref_=fn_al_tt_1" rel="nofollow" target="_blank"><?php echo $this->item->imdb_votesum; ?>
													(<?php echo $this->item->imdb_votes; ?>)</a></span></div>
									<?php else: ?>
										<div id="rate-imdb">
											<span class="a"><?php echo JText::_('COM_KA_RATE_IMDB'); ?></span> <?php echo JText::_('COM_KA_RATE_NO'); ?>
										</div>
									<?php endif; ?>
									<?php if (!empty($this->item->kp_votesum) && !empty($this->item->kp_votes)): ?>
										<div id="rate-kp">
											<span class="a"><?php echo JText::_('COM_KA_RATE_KP'); ?></span>
											<span class="b"><a href="https://www.kinopoisk.ru/film/<?php echo $this->item->kp_id; ?>/" rel="nofollow" target="_blank"><?php echo $this->item->kp_votesum; ?>
													(<?php echo $this->item->kp_votes; ?>)</a></span></div>
									<?php else: ?>
										<div id="rate-kp">
											<span class="a"><?php echo JText::_('COM_KA_RATE_KP'); ?></span> <?php echo JText::_('COM_KA_RATE_NO'); ?>
										</div>
									<?php endif; ?>
									<?php if (!empty($this->item->rate_fc)): ?>
										<div id="rate-rt">
											<span class="a"><?php echo JText::_('COM_KA_RATE_RT'); ?></span>
											<span class="b"><a href="https://www.rottentomatoes.com/m/<?php echo $this->item->rottentm_id; ?>/" rel="nofollow" target="_blank"><?php echo $this->item->rate_fc; ?>
													%</a></span></div>
									<?php else: ?>
										<div id="rate-rt">
											<span class="a"><?php echo JText::_('COM_KA_RATE_RT'); ?></span> <?php echo JText::_('COM_KA_RATE_NO'); ?>
										</div>
									<?php endif; ?>
									<?php if (!empty($this->item->metacritics)): ?>
										<div id="rate-rt">
											<span class="a"><?php echo JText::_('COM_KA_RATE_MC'); ?></span>
											<span class="b"><a href="http://www.metacritic.com/movie/<?php echo $this->item->metacritics_id; ?>/" rel="nofollow" target="_blank"><?php echo $this->item->metacritics; ?>
													%</a></span></div>
									<?php else: ?>
										<div id="rate-rt">
											<span class="a"><?php echo JText::_('COM_KA_RATE_MC'); ?></span> <?php echo JText::_('COM_KA_RATE_NO'); ?>
										</div>
									<?php endif; ?>
								<?php endif; ?>
							<?php endif; ?>
							<div class="local-rt<?php echo $this->item->rate_loc_label_class; ?>">
								<div class="rateit" data-rateit-value="<?php echo $this->item->rate_loc_c; ?>" data-rateit-min="0" data-rateit-max="<?php echo (int) $this->params->get('vote_summ_num'); ?>" data-rateit-ispreset="true" data-rateit-readonly="true"></div>
								&nbsp;<?php echo $this->item->rate_loc_label; ?>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<?php if (count($this->item->items) > 0): ?>
				<div class="clear"></div>
				<div>
					<table class="table table-striped table-hover uk-table uk-table-striped uk-table-hover release-table" data-sortable>
						<thead>
						<tr>
							<th title="<?php echo JText::_('JGLOBAL_CLICK_TO_SORT_THIS_COLUMN'); ?>" class="hasTooltip"
								data-sorted="true" data-sorted-direction="descending">
								<?php echo JText::_('COM_KA_RELEASES_MEDIATYPE_DATE_TITLE'); ?>
							</th>
							<th title="<?php echo JText::_('JGLOBAL_CLICK_TO_SORT_THIS_COLUMN'); ?>" class="hasTooltip">
								<?php echo JText::_('COM_KA_COUNTRY'); ?>
							</th>
							<th title="<?php echo JText::_('JGLOBAL_CLICK_TO_SORT_THIS_COLUMN'); ?>" class="hasTooltip">
								<?php echo JText::_('COM_KA_RELEASES_MEDIATYPE_TITLE'); ?>
							</th>
							<th width="2%" data-sortable="false">&nbsp;</th>
						</tr>
						</thead>
						<tbody>
						<?php foreach ($this->item->items as $row):
							$tr_class = ($row->desc != '') ? ' hasDesc info uk-alert' : '';
							?>
							<tr class="<?php echo $tr_class; ?>">
								<td>
									<span class="hasTooltip" title="<?php echo $row->release_date; ?>"><?php echo JHtml::_('date', $row->release_date, JText::_('DATE_FORMAT_LC3')); ?></span>
									<a name="row-<?php echo $row->id; ?>"></a>
								</td>
								<td>
									<img class="flag-dd" src="media/com_kinoarhiv/images/icons/countries/<?php echo $row->code; ?>.png"/><?php echo $row->name; ?>
								</td>
								<td><?php echo $row->media_type; ?></td>
								<td>
									<?php if ($row->desc != ''): ?>
										<span class="icon icon-chevron-down"></span>
									<?php endif; ?>
								</td>
							</tr>
							<?php if ($row->desc != ''): ?>
							<tr style="display: none;">
								<td colspan="4">
									<div><?php echo str_replace(array("\r\n", "\r", "\n"), '<br/>', $row->desc); ?></div>
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
