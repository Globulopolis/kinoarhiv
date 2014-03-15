<?php defined('_JEXEC') or die; ?>
<script src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/jquery.colorbox-min.js" type="text/javascript"></script>
<script src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/i18n/colorbox/jquery.colorbox-<?php echo substr(JFactory::getLanguage()->getTag(), 0, 2); ?>.js" type="text/javascript"></script>
<script src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/ui.aurora.min.js" type="text/javascript"></script>
<script src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/jquery-ui.min.js" type="text/javascript"></script>
<script src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/jquery.rateit.min.js" type="text/javascript"></script>
<script src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/jquery.lazyload.min.js" type="text/javascript"></script>
<script type="text/javascript">
//<![CDATA[
	jQuery(document).ready(function($){
		function showMsg(selector, text) {
			$(selector).aurora({
				text: text,
				placement: 'after',
				button: 'close',
				button_title: '[<?php echo JText::_('COM_KA_CLOSE'); ?>]'
			});
		}

		$('img.lazy').lazyload({ threshold: 100 });

		$('a.zoom-icon').colorbox({
			title: function(){
				return $(this).closest('.poster').find('img').attr('alt');
			},
			maxHeight: '90%',
			maxWidth: '90%',
			returnFocus: false
		});

		$('.tabbar .movies, .tabbar .names, .tabbar .premieres, .filters .filter-submit').button();

		<?php if (!$this->user->guest && $this->params->get('link_favorite') == 1): ?>
		$('.fav a').click(function(e){
			e.preventDefault();
			var _this = $(this);

			$.ajax({
				url: _this.attr('href') + '&format=raw'
			}).done(function(response){
				if (response.success) {
					_this.text(response.text);
					_this.attr('href', response.url);
					if (_this.hasClass('delete')) {
						_this.removeClass('delete').addClass('add');
					} else {
						_this.removeClass('add').addClass('delete');
					}
					showMsg(_this.closest('header'), response.message);
				} else {
					showMsg(_this.closest('header'), '<?php echo JText::_('JERROR_AN_ERROR_HAS_OCCURRED'); ?>');
				}
			}).fail(function(xhr, status, error){
				showMsg(_this.closest('header'), error);
			});
		});
		<?php endif; ?>
	});
//]]>
</script>
<div class="ka-content">
	<?php if ($this->params->get('tabbar_frontpage') == 1): ?>
	<div class="tabbar">
		<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movies&Itemid='.$this->itemid); ?>" class="button movies"><?php echo JText::_('COM_KA_MOVIES'); ?></a>
		<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=names&Itemid='.$this->itemid); ?>" class="button names"><?php echo JText::_('COM_KA_PERSONS'); ?></a>
		<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=premieres&Itemid='.$this->itemid); ?>" class="button premieres"><?php echo JText::_('COM_KA_PREMIERES'); ?></a>
	</div>
	<div class="clear"></div><br />
	<?php endif; ?>

	<?php if ($this->params->get('pagevan_top') == 1 && $this->pagination->total >= $this->pagination->limit): ?>
		<div class="pagination top">
			<?php echo $this->pagination->getPagesLinks(); ?>
		</div>
	<?php endif; ?>
	<?php if (count($this->items['movies']) > 0):
		foreach ($this->items['movies'] as $item): ?>
		<article class="item" data-permalink="<?php echo $item->params->get('url'); ?>">
			<header>
				<h1 class="title title-small">
					<a href="<?php echo $item->params->get('url'); ?>" class="brand" title="<?php echo $this->escape($item->title.$item->year_str); ?>"><?php echo $this->escape($item->title.$item->year_str); ?></a>
				</h1>
				<div class="middle-nav clearfix">
					<p class="meta">
						<?php if ($this->params->get('show_pubdate') == 1 && $item->created !== '0000-00-00'): ?>
							<span class="icon-calendar"></span> <?php echo JText::_('COM_KA_CREATED_DATE_ON'); ?><time pubdate="" datetime="<?php echo $item->created; ?>"><?php echo date('j F Y', strtotime($item->created)); ?></time>
						<?php endif;
						if ($this->params->get('show_pubdate') == 1 && $this->params->get('show_moddate') == 1) { echo ' &bull; '; }
						if ($this->params->get('show_moddate') == 1):
							if ($item->modified !== '0000-00-00'): ?>
							<?php echo JText::_('COM_KA_LAST_UPDATED'); ?><time pubdate="" datetime="<?php echo $item->modified; ?>"><?php echo date('j F Y', strtotime($item->modified)); ?></time>
							<?php endif;
						endif; ?>
					</p>
					<?php if (!$this->user->guest && $this->params->get('link_favorite') == 1): ?>
					<p class="fav">
						<?php if ($item->favorite == 1): ?>
						<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&task=favorite&action=delete&Itemid='.$this->itemid.'&id='.$item->id); ?>" class="delete"><?php echo JText::_('COM_KA_REMOVEFROM_FAVORITE'); ?></a>
						<?php else: ?>
						<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&task=favorite&action=add&Itemid='.$this->itemid.'&id='.$item->id); ?>" class="add"><?php echo JText::_('COM_KA_ADDTO_FAVORITE'); ?></a>
						<?php endif; ?>
					</p>
					<?php endif; ?>
				</div>
			</header>
			<?php echo $item->event->afterDisplayTitle; ?>
			<?php echo $item->event->beforeDisplayContent; ?>
			<div class="clear"></div>
			<div class="content clearfix">
				<div>
					<div class="poster<?php echo $item->y_poster; ?>">
						<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&id='.$item->id.'&Itemid='.$this->itemid); ?>" title="<?php echo $this->escape($item->title.$item->year_str); ?>">
							<div>
								<img data-original="<?php echo $item->poster; ?>" class="lazy" border="0" alt="<?php echo JText::_('COM_KA_POSTER_ALT').$this->escape($item->title); ?>" width="<?php echo $item->poster_width; ?>" height="<?php echo $item->poster_height; ?>" />
							</div>
						</a>
						<?php if ($item->y_poster != ''): ?><div class="overlay-poster">
							<a href="<?php echo $item->big_poster; ?>" title="<?php echo JText::_('COM_KA_POSTER_ZOOM'); ?>" class="zoom-icon hasTooltip"><div></div></a>
						</div><?php endif; ?>
					</div>
					<div class="introtext">
						<?php echo $item->text; ?>
						<div class="separator"></div>
						<?php echo $item->plot; ?>
						<?php if ($this->params->get('ratings_show_frontpage') == 1): ?>
						<div class="separator"></div>
						<div class="ratings-frontpage">
							<?php if (!empty($item->rate_custom)): ?>
							<div><?php echo $item->rate_custom; ?></div>
							<?php else: ?>
								<?php if ($this->params->get('ratings_show_img') == 1): ?>
									<div style="text-align: center; display: inline-block;">
										<?php if (!empty($item->imdb_id)) {
											if (file_exists($this->params->get('media_rating_image_root').'/imdb/'.$item->id.'_big.png')) { ?>
											<a href="http://www.imdb.com/title/tt<?php echo $item->imdb_id; ?>/" rel="nofollow" target="_blank"><img src="<?php echo $this->params->get('media_rating_image_root_www'); ?>/imdb/<?php echo $item->id; ?>_big.png" border="0" /></a>
											<?php }
										} ?>
										<?php if (!empty($item->kp_id)): ?>
											<a href="http://www.kinopoisk.ru/film/<?php echo $item->kp_id; ?>/" rel="nofollow" target="_blank">
											<?php if ($this->params->get('ratings_img_kp_remote') == 0): ?>
												<img src="<?php echo $this->params->get('media_rating_image_root_www'); ?>/kinopoisk/<?php echo $item->id; ?>_big.png" border="0" />
											<?php else: ?>
												<img src="http://www.kinopoisk.ru/rating/<?php echo $item->kp_id; ?>.gif" border="0" style="padding-left: 1px;" />
											<?php endif; ?>
											</a>
										<?php endif; ?>
										<?php if (!empty($item->rottentm_id)): ?>
											<?php if (file_exists($this->params->get('media_rating_image_root').'/rottentomatoes/'.$item->id.'_big.png')): ?>
											<a href="http://www.rottentomatoes.com/m/<?php echo $item->rottentm_id; ?>/" rel="nofollow" target="_blank"><img src="<?php echo $this->params->get('media_rating_image_root_www'); ?>/rottentomatoes/<?php echo $item->id; ?>_big.png" border="0" /></a>
											<?php endif; ?>
										<?php endif; ?>
									</div>
								<?php else: ?>
									<?php if (!empty($item->imdb_votesum) && !empty($item->imdb_votes)): ?>
										<div id="rate-imdb"><span class="a"><?php echo JText::_('COM_KA_RATE_IMDB'); ?></span> <span class="b"><a href="http://www.imdb.com/title/tt<?php echo $item->imdb_id; ?>/?ref_=fn_al_tt_1" rel="nofollow" target="_blank"><?php echo $item->imdb_votesum; ?> (<?php echo $item->imdb_votes; ?>)</a></span></div>
									<?php else: ?>
										<div id="rate-imdb"><span class="a"><?php echo JText::_('COM_KA_RATE_IMDB'); ?></span> <?php echo JText::_('COM_KA_RATE_NO'); ?></div>
									<?php endif; ?>
									<?php if (!empty($item->kp_votesum) && !empty($item->kp_votes)): ?>
										<div id="rate-kp"><span class="a"><?php echo JText::_('COM_KA_RATE_KP'); ?></span> <span class="b"><a href="http://www.kinopoisk.ru/film/<?php echo $item->kp_id; ?>/" rel="nofollow" target="_blank"><?php echo $item->kp_votesum; ?> (<?php echo $item->kp_votes; ?>)</a></span></div>
									<?php else: ?>
										<div id="rate-kp"><span class="a"><?php echo JText::_('COM_KA_RATE_KP'); ?></span> <?php echo JText::_('COM_KA_RATE_NO'); ?></div>
									<?php endif; ?>
								<?php endif; ?>
							<?php endif; ?>
							<div class="local-rt<?php echo $item->rate_loc_label_class; ?>">
								<div class="rateit" data-rateit-value="<?php echo $item->rate_loc; ?>" data-rateit-min="0" data-rateit-max="<?php echo (int)$this->params->get('vote_summ_num'); ?>" data-rateit-ispreset="true" data-rateit-readonly="true"></div>&nbsp;<?php echo $item->rate_loc_label; ?>
							</div>
						</div>
						<?php endif; ?>
					</div>
				</div>
				<div class="links">
					<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&id='.$item->id.'&Itemid='.$this->itemid); ?>" class="brand readmore-link hasTooltip" title="<?php echo $item->title.$item->year_str; ?>"><?php echo JText::_('COM_KA_READMORE'); ?></a> <span class="icon-chevron-right"></span>
				</div>
			</div>
		</article>
		<?php echo $item->event->afterDisplayContent; ?>
		<?php endforeach;
	else: ?>
		<br /><div><?php echo GlobalHelper::showMsg(JText::_('COM_KA_NO_ITEMS')); ?></div>
	<?php endif; ?>
	<?php if ($this->params->get('pagevan_bottom') == 1 && $this->pagination->total >= $this->pagination->limit): ?>
		<div class="pagination bottom">
			<form action="<?php echo htmlspecialchars(JURI::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm" style="clear: both;" autocomplete="off">
			<?php echo $this->pagination->getPagesLinks(); ?><br />
			<?php echo $this->pagination->getResultsCounter(); ?>
			<?php echo $this->pagination->getLimitBox(); ?>
			</form>
		</div>
	<?php endif; ?>
</div>
