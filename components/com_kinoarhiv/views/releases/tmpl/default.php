<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
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
JHtml::_('script', 'media/com_kinoarhiv/js/jquery.lazyload.min.js');
?>
<script type="text/javascript">
	//<![CDATA[
	jQuery(document).ready(function ($) {
		<?php if ($this->params->get('vegas_enable') == 1):
		$src = explode(',', $this->params->get('vegas_bg'));

			if (count($src) > 0): ?>
		$.vegas('slideshow', {
			delay: <?php echo (int) $this->params->get('vegas_slideshow_delay'); ?>,
			backgrounds: [
				<?php foreach ($src as $image): ?>
				{src: '<?php echo trim($image); ?>', fade: 500},
				<?php endforeach; ?>
			]
			<?php else: ?>
			$.vegas({
				src: '<?php echo trim($image); ?>'
				<?php endif; ?>
			})<?php if ($this->params->get('vegas_overlay') != '-1'): ?>('overlay', {
			src: '<?php echo JUri::base(); ?>components/com_kinoarhiv/assets/themes/component/default/images/overlays/<?php echo $this->params->get('vegas_overlay'); ?>',
			opacity: <?php echo $this->params->get('vegas_overlay_opacity'); ?>
		})<?php endif; ?>;
		<?php if ($this->params->get('vegas_bodybg_transparent') == 1): ?>$('<?php echo $this->params->get('vegas_bodybg_selector'); ?>').css('background-color', 'transparent');
		<?php endif; ?>
		<?php endif; ?>
	});
	//]]>
</script>
<div class="uk-article ka-content">
	<?php if ($this->params->get('use_alphabet') == 1):
		echo JLayoutHelper::render('layouts.navigation.alphabet', array('params' => $this->params, 'itemid' => $this->itemid), JPATH_COMPONENT);
	endif; ?>

	<?php if (count($this->items) > 0): ?>
		<?php if ($this->params->get('pagevan_top') == 1): ?>
			<div class="pagination top">
				<?php echo $this->pagination->getPagesLinks(); ?>
			</div>
		<?php endif;

		foreach ($this->items as $item):
			$title = $this->escape(KAContentHelper::formatItemTitle($item->title, '', $item->year)); ?>
			<article class="item" data-permalink="<?php echo $item->params->get('url'); ?>">
				<header>
					<h1 class="uk-article-title title title-small">
						<?php if ($item->attribs->link_titles === ''): ?>
							<?php if ($this->params->get('link_titles') == 1): ?>
								<a href="<?php echo $item->params->get('url'); ?>" class="brand" title="<?php echo $title; ?>"><?php echo $title; ?></a>
							<?php else: ?>
								<span class="brand"><?php echo $title; ?></span>
							<?php endif; ?>
						<?php elseif ($item->attribs->link_titles == 1): ?>
							<a href="<?php echo $item->params->get('url'); ?>" class="brand" title="<?php echo $title; ?>"><?php echo $title; ?></a>
						<?php elseif ($item->attribs->link_titles == 0): ?>
							<span class="brand"><?php echo $title; ?></span>
						<?php endif; ?>
					</h1>

					<div class="middle-nav clearfix">
						<p class="meta">
							<?php if ($item->attribs->show_author === '' && !empty($item->username)): ?>
								<?php if ($this->params->get('show_author') == 1): ?>
									<span class="icon-user"></span> <?php echo JText::_('JAUTHOR'); ?>: <?php echo $item->username; ?>
									<br/>
								<?php endif; ?>
							<?php elseif ($item->attribs->show_author == 1 && !empty($item->username)): ?>
								<span class="icon-user"></span> <?php echo JText::_('JAUTHOR'); ?>: <?php echo $item->username; ?>
								<br/>
							<?php endif; ?>

							<?php if ($item->attribs->show_create_date === ''): ?>
								<?php if ($this->params->get('show_pubdate') == 1): ?>
									<span class="icon-calendar"></span> <?php echo JText::_('COM_KA_CREATED_DATE_ON'); ?>
									<time itemprop="dateCreated" datetime="<?php echo JHtml::_('date', $item->created, 'c'); ?>"><?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC3')); ?></time>
								<?php endif; ?>
							<?php elseif ($item->attribs->show_create_date == 1): ?>
								<span class="icon-calendar"></span> <?php echo JText::_('COM_KA_CREATED_DATE_ON'); ?>
								<time itemprop="dateCreated" datetime="<?php echo JHtml::_('date', $item->created, 'c'); ?>"><?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC3')); ?></time>
							<?php endif; ?>

							<?php
							if ((
									($item->attribs->show_create_date === '' && $this->params->get('show_pubdate') == 1) || $item->attribs->show_create_date == 1
								) && (
									($item->attribs->show_modify_date === '' && $this->params->get('show_moddate') == 1) || $item->attribs->show_modify_date == 1
								)
							):
								echo ' &bull; ';
							endif; ?>

							<?php if ($item->attribs->show_modify_date === ''): ?>
								<?php if ($this->params->get('show_moddate') == 1): ?>
									<?php echo JText::_('COM_KA_LAST_UPDATED'); ?>
									<time itemprop="dateModified" datetime="<?php echo JHtml::_('date', $item->modified, 'c'); ?>"><?php echo JHtml::_('date', $item->modified, JText::_('DATE_FORMAT_LC3')); ?></time>
								<?php endif; ?>
							<?php elseif ($item->attribs->show_modify_date == 1): ?>
								<?php echo JText::_('COM_KA_LAST_UPDATED'); ?>
								<time itemprop="dateModified" datetime="<?php echo JHtml::_('date', $item->modified, 'c'); ?>"><?php echo JHtml::_('date', $item->modified, JText::_('DATE_FORMAT_LC3')); ?></time>
							<?php endif; ?>
						</p>
						<?php if (!$this->user->guest && $this->params->get('link_favorite') == 1): ?>
							<p class="favorite">
								<?php if ($item->favorite == 1): ?>
									<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movies&task=favorite&action=delete&Itemid=' . $this->itemid . '&id=' . $item->id); ?>" class="cmd-favorite delete"><?php echo JText::_('COM_KA_REMOVEFROM_FAVORITE'); ?></a>
								<?php else: ?>
									<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movies&task=favorite&action=add&Itemid=' . $this->itemid . '&id=' . $item->id); ?>" class="cmd-favorite add"><?php echo JText::_('COM_KA_ADDTO_FAVORITE'); ?></a>
								<?php endif; ?>
							</p>
						<?php endif; ?>
					</div>
				</header>
				<?php echo $item->event->afterDisplayTitle; ?>
				<?php echo $item->event->beforeDisplayContent; ?>
				<div class="clear"></div>
				<div class="content content-list clearfix">
					<div>
						<div class="poster">
							<a href="<?php echo $item->params->get('url'); ?>" title="<?php echo $title; ?>"><img data-original="<?php echo $item->poster; ?>" class="lazy" border="0" alt="<?php echo JText::_('COM_KA_POSTER_ALT') . $this->escape($item->title); ?>" width="<?php echo $item->poster_width; ?>" height="<?php echo $item->poster_height; ?>"/></a>
						</div>
						<div class="introtext premiere <?php echo (!empty($item->premiere_date) && $item->premiere_date != '0000-00-00 00:00:00') ? 'hasPremiere' : ''; ?>">
							<div class="text"><?php echo $item->text; ?></div>
							<div class="separator"></div>
							<div class="plot"><?php echo $item->plot; ?></div>
							<?php if ($this->params->get('ratings_show_frontpage') == 1): ?>
								<div class="separator"></div>
								<div class="ratings-frontpage">
									<?php if (!empty($item->rate_custom)): ?>
										<div><?php echo $item->rate_custom; ?></div>
									<?php else: ?>
										<?php if ($this->params->get('ratings_show_img') == 1): ?>
											<div style="text-align: center; display: inline-block;">
												<?php if (!empty($item->imdb_id))
												{
													if (file_exists($this->params->get('media_rating_image_root') . '/imdb/' . $item->id . '_big.png'))
													{ ?>
														<a href="http://www.imdb.com/title/tt<?php echo $item->imdb_id; ?>/" rel="nofollow" target="_blank"><img src="<?php echo $rating_image_www; ?>/imdb/<?php echo $item->id; ?>_big.png" border="0"/></a>
													<?php }
												} ?>
												<?php if (!empty($item->kp_id)): ?>
													<a href="https://www.kinopoisk.ru/film/<?php echo $item->kp_id; ?>/" rel="nofollow" target="_blank">
														<?php if ($this->params->get('ratings_img_kp_remote') == 0): ?>
															<img src="<?php echo $rating_image_www; ?>/kinopoisk/<?php echo $item->id; ?>_big.png" border="0"/>
														<?php else: ?>
															<img src="https://www.kinopoisk.ru/rating/<?php echo $item->kp_id; ?>.gif" border="0" style="padding-left: 1px;"/>
														<?php endif; ?>
													</a>
												<?php endif; ?>
												<?php if (!empty($item->rottentm_id)): ?>
													<?php if (file_exists($this->params->get('media_rating_image_root') . '/rottentomatoes/' . $item->id . '_big.png')): ?>
														<a href="https://www.rottentomatoes.com/m/<?php echo $item->rottentm_id; ?>/" rel="nofollow" target="_blank"><img src="<?php echo $rating_image_www; ?>/rottentomatoes/<?php echo $item->id; ?>_big.png" border="0"/></a>
													<?php endif; ?>
												<?php endif; ?>
											</div>
										<?php else: ?>
											<?php if (!empty($item->imdb_votesum) && !empty($item->imdb_votes)): ?>
												<div id="rate-imdb">
													<span class="a"><?php echo JText::_('COM_KA_RATE_IMDB'); ?></span>
													<span class="b"><a href="http://www.imdb.com/title/tt<?php echo $item->imdb_id; ?>/?ref_=fn_al_tt_1" rel="nofollow" target="_blank"><?php echo $item->imdb_votesum; ?>
															(<?php echo $item->imdb_votes; ?>)</a></span></div>
											<?php else: ?>
												<div id="rate-imdb">
													<span class="a"><?php echo JText::_('COM_KA_RATE_IMDB'); ?></span> <?php echo JText::_('COM_KA_RATE_NO'); ?>
												</div>
											<?php endif; ?>
											<?php if (!empty($item->kp_votesum) && !empty($item->kp_votes)): ?>
												<div id="rate-kp">
													<span class="a"><?php echo JText::_('COM_KA_RATE_KP'); ?></span>
													<span class="b"><a href="https://www.kinopoisk.ru/film/<?php echo $item->kp_id; ?>/" rel="nofollow" target="_blank"><?php echo $item->kp_votesum; ?>
															(<?php echo $item->kp_votes; ?>)</a></span></div>
											<?php else: ?>
												<div id="rate-kp">
													<span class="a"><?php echo JText::_('COM_KA_RATE_KP'); ?></span> <?php echo JText::_('COM_KA_RATE_NO'); ?>
												</div>
											<?php endif; ?>
										<?php endif; ?>
									<?php endif; ?>
									<div class="local-rt<?php echo $item->rate_loc_label_class; ?>">
										<div class="rateit" data-rateit-value="<?php echo $item->rate_loc_c; ?>" data-rateit-min="0" data-rateit-max="<?php echo (int) $this->params->get('vote_summ_num'); ?>" data-rateit-ispreset="true" data-rateit-readonly="true"></div>
										&nbsp;<?php echo $item->rate_loc_label; ?>
									</div>
								</div>
							<?php endif; ?>
						</div>

						<?php if (!empty($item->premiere_date) && $item->premiere_date != '0000-00-00 00:00:00'): ?>
							<div class="premiere-date">
								<div class="date"><?php echo date('d', strtotime($item->premiere_date)); ?></div>
								<div class="month"><?php echo JHtml::_('date', $item->premiere_date, 'F'); ?></div>
								<div class="vendor">
									<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=releases&vendor=' . $item->vendor_id . '&Itemid=' . $this->itemid); ?>"><?php echo $item->company_name; ?></a>
								</div>
							</div>
						<?php endif; ?>

					</div>
					<div class="links">
						<a href="<?php echo $item->params->get('url'); ?>" class="btn btn-default uk-button readmore-link hasTip" title="<?php echo $title; ?>"><?php echo JText::_('COM_KA_READMORE'); ?>
							<span class="icon-chevron-right"></span></a>
					</div>
				</div>
			</article>
			<?php echo $item->event->afterDisplayContent; ?>
		<?php endforeach; ?>
		<?php if ($this->params->get('pagevan_bottom') == 1): ?>
			<div class="pagination bottom">
				<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm" style="clear: both;" autocomplete="off">
					<?php echo $this->pagination->getPagesLinks(); ?><br/>
					<?php echo $this->pagination->getResultsCounter(); ?>
					<?php echo $this->pagination->getLimitBox(); ?>
				</form>
			</div>
		<?php endif;
	else: ?>
		<br/>
		<div><?php echo KAComponentHelper::showMsg(JText::_('COM_KA_NO_ITEMS')); ?></div>
	<?php endif; ?>
</div>
