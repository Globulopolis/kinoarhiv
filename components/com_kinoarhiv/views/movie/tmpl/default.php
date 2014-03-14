<?php defined('_JEXEC') or die;
$total_trailers = count($this->item->trailer);
$total_movies = count($this->item->movie);
?>
<script src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/ui.aurora.min.js" type="text/javascript"></script>
<script src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/jquery.rateit.min.js" type="text/javascript"></script>
<script type="text/javascript">
//<![CDATA[
	function showMsg(selector, text) {
		jQuery(selector).aurora({
			text: text,
			button: 'close',
			button_title: '[<?php echo JText::_('COM_KA_CLOSE'); ?>]'
		});
	}

	jQuery(document).ready(function($){
		<?php if (!$this->user->guest): ?>
		<?php if ($this->params->get('allow_votes') == 1): ?>
		$('.rateit').bind('over', function(e, v){ $(this).attr('title', v); });
		$('.rate .rateit').bind('rated reset', function(e){
			var _this = $(this);
			var value = _this.rateit('value');

			$.ajax({
				type: 'POST',
				url: '<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&task=vote&id='.$this->item->id.'&Itemid='.$this->itemid.'&format=raw', false); ?>',
				data: {'value': value}
			}).done(function(response){
				if ($('.rate .my_votes').is(':hidden')) { $('.rate .my_votes').show(); }

				if (value != 0) {
					if ($('.rate .my_vote').is(':hidden')) { $('.rate .my_vote').show(); }
					$('.rate .my_vote span').text(value);
				} else {
					$('.rate .my_vote span').text('').parent().hide();
				}
				showMsg($('.my_vote').next(), response.message);
			}).fail(function(xhr, status, error){
				showMsg($('.my_vote').next(), error);
			});
		});
		<?php endif; ?>
		<?php if ($this->params->get('link_favorite') == 1): ?>
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
					showMsg($('.mark-links'), response.message);
				} else {
					showMsg($('.mark-links'), '<?php echo JText::_('JERROR_AN_ERROR_HAS_OCCURRED'); ?> ' + response.message);
				}
			}).fail(function(xhr, status, error){
				showMsg($('.mark-links'), error);
			});
		});
		<?php endif; ?>
		<?php if ($this->params->get('link_watched') == 1): ?>
		$('.watched a').click(function(e){
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
					showMsg($('.mark-links'), response.message);
				} else {
					showMsg($('.mark-links'), '<?php echo JText::_('JERROR_AN_ERROR_HAS_OCCURRED'); ?>');
				}
			}).fail(function(xhr, status, error){
				showMsg($('.mark-links'), error);
			});
		});
		<?php endif; ?>
		<?php endif; ?>
		$('#mpaa').click(function(e){
			e.preventDefault();
			$.colorbox({ html: '<div class="desc">'+$(this).next('.mpaa-desc').html()+'</div>', height: '80%', width: '80%' });
		});
		$('#rrate').click(function(e){
			e.preventDefault();
			$.colorbox({ html: '<div class="desc">'+$(this).next('.rrate-desc').html()+'</div>', height: '80%', width: '80%' });
		});
		$('#ua-rate').click(function(e){
			e.preventDefault();
			$.colorbox({ html: '<div class="desc">'+$(this).next('.uarate-desc').html()+'</div>', height: '80%', width: '80%' });
		});
		$('#open-desc').click(function(e){
			e.preventDefault();
			$.colorbox({ html: '<div class="desc"><div class="pre">'+$(this).parent().next('.content').find('p').html()+'</div></div>', height: '90%', width: '90%' });
		});
		$('.toggle').click(function(){
			var _this = $(this), _tg_content = _this.parent().next();
			if (_this.hasClass('down')) {
				_tg_content.show();
				_this.removeClass('down').addClass('up');
			} else {
				_tg_content.hide();
				_this.removeClass('up').addClass('down');
			}
		});
		$('.premiere-info-icon').click(function(e){
			e.preventDefault();
			var _this = $(this);

			$.colorbox({ html: '<div class="overlay">'+_this.next('div').html()+'</div>' });
		});
		<?php if (($this->params->get('player_type') == 'flowplayer' || $this->params->get('player_type') == 'jwplayer') && ($total_trailers > 0 || $total_movies > 0)): ?>
		$('.watch-buttons a').button({
			icons: { primary: 'ui-icon-play' }
		}).click(function(e){
			e.preventDefault();

			if ($(this).hasClass('watch-trailer')) {
				if (!window.open('<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&task=watch&type=trailer&id='.$this->item->id.'&Itemid='.$this->itemid.'&format=raw', false); ?>')) {
					showMsg('.watch-buttons', '<?php echo JText::sprintf('COM_KA_NEWWINDOW_BLOCKED', JRoute::_('index.php?option=com_kinoarhiv&view=movie&task=watch&type=trailer&id='.$this->item->id.'&Itemid='.$this->itemid.'&format=raw', false))?>');
				}
			} else if ($(this).hasClass('watch-movie')) {
				if (!window.open('<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&task=watch&type=movie&id='.$this->item->id.'&Itemid='.$this->itemid.'&format=raw', false); ?>')) {
					showMsg('.watch-buttons', '<?php echo JText::sprintf('COM_KA_NEWWINDOW_BLOCKED', JRoute::_('index.php?option=com_kinoarhiv&view=movie&task=watch&type=movie&id='.$this->item->id.'&Itemid='.$this->itemid.'&format=raw', false))?>');
				}
			}
		});
		<?php endif; ?>
	});
//]]>
</script>
<div class="ka-content uk-grid content movie">
	<article class="<?php echo $this->class['article']; ?>">
		<header class="<?php echo $this->class['header']; ?>">
			<h3 class="<?php echo $this->class['header_subhead']; ?>">
				<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&id='.$this->item->id.'&Itemid='.$this->itemid); ?>" class="brand" title="<?php echo $this->escape($this->item->title.$this->item->year_str); ?>"><?php echo $this->escape($this->item->title); ?></a>
			</h3>
			<?php echo $this->item->event->afterDisplayTitle; ?>
		</header>

		<div class="content clearfix">
			<div class="<?php echo $this->class['tabs']; ?>"><?php echo $this->loadTemplate('tabs'); ?></div>
			<?php echo $this->item->event->beforeDisplayContent; ?>
			<div class="info">
				<div class="left-col">
					<div class="poster<?php echo $this->item->y_poster; ?>">
						<div style="text-align: center;">
							<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=posters&id='.$this->item->id.'&Itemid='.$this->itemid); ?>" title="<?php echo $this->escape($this->item->title.$this->item->year_str); ?>"><img src="<?php echo $this->item->poster; ?>" border="0" alt="<?php echo JText::_('COM_KA_POSTER_ALT').$this->escape($this->item->title); ?>" /></a>
						</div>
					</div>
					<div class="ratings">
						<?php if (!empty($this->item->rate_custom)): ?>
						<div><?php echo $this->item->rate_custom; ?></div>
						<?php else: ?>
							<?php if ($this->params->get('ratings_show_img') == 1): ?>
								<div style="text-align: center;">
									<?php if (!empty($this->item->imdb_id)) {
										if (file_exists($this->params->get('media_rating_image_root').'/imdb/'.$this->item->id.'_big.png')) { ?>
										<a href="http://www.imdb.com/title/tt<?php echo $this->item->imdb_id; ?>/" rel="nofollow" target="_blank"><img src="<?php echo $this->params->get('media_rating_image_root_www'); ?>/imdb/<?php echo $this->item->id; ?>_big.png" border="0" /></a>
										<?php }
									} ?>
									<?php if (!empty($this->item->kp_id)): ?>
										<a href="http://www.kinopoisk.ru/film/<?php echo $this->item->kp_id; ?>/" rel="nofollow" target="_blank">
										<?php if ($this->params->get('ratings_img_kp_remote') == 0): ?>
											<img src="<?php echo $this->params->get('media_rating_image_root_www'); ?>/kinopoisk/<?php echo $this->item->id; ?>_big.png" border="0" />
										<?php else: ?>
											<img src="http://www.kinopoisk.ru/rating/<?php echo $this->item->kp_id; ?>.gif" border="0" style="padding-left: 1px;" />
										<?php endif; ?>
										</a>
									<?php endif; ?>
									<?php if (!empty($this->item->rottentm_id)) {
										if (file_exists($this->params->get('media_rating_image_root').'/rottentomatoes/'.$this->item->id.'_big.png')) { ?>
										<a href="http://www.rottentomatoes.com/m/<?php echo $this->item->rottentm_id; ?>/" rel="nofollow" target="_blank"><img src="<?php echo $this->params->get('media_rating_image_root_www'); ?>/rottentomatoes/<?php echo $this->item->id; ?>_big.png" border="0" /></a>
										<?php }
									} ?>
								</div>
							<?php else: ?>
								<?php if (!empty($this->item->imdb_votesum) && !empty($this->item->imdb_votes)): ?>
									<div id="rate-imdb"><span class="a"><?php echo JText::_('COM_KA_RATE_IMDB'); ?></span> <span class="b"><a href="http://www.imdb.com/title/tt<?php echo $this->item->imdb_id; ?>/?ref_=fn_al_tt_1" rel="nofollow" target="_blank"><?php echo $this->item->imdb_votesum; ?> (<?php echo $this->item->imdb_votes; ?>)</a></span></div>
								<?php else: ?>
									<div id="rate-imdb"><span class="a"><?php echo JText::_('COM_KA_RATE_IMDB'); ?></span> <?php echo JText::_('COM_KA_RATE_NO'); ?></div>
								<?php endif; ?>
								<?php if (!empty($this->item->kp_votesum) && !empty($this->item->kp_votes)): ?>
									<br /><br /><div id="rate-kp"><span class="a"><?php echo JText::_('COM_KA_RATE_KP'); ?></span> <span class="b"><a href="http://www.kinopoisk.ru/film/<?php echo $this->item->kp_id; ?>/" rel="nofollow" target="_blank"><?php echo $this->item->kp_votesum; ?> (<?php echo $this->item->kp_votes; ?>)</a></span></div>
								<?php else: ?>
									<div id="rate-kp"><span class="a"><?php echo JText::_('COM_KA_RATE_KP'); ?></span> <?php echo JText::_('COM_KA_RATE_NO'); ?></div>
								<?php endif; ?>
							<?php endif; ?>
						<?php endif; ?>
					</div>
				</div>
				<div class="right-col">
					<?php if (!$this->user->guest): ?>
					<div class="mark-links">
						<?php if ($this->params->get('link_watched') == 1): ?>
						<div class="watched">
							<?php if ($this->item->watched == 1): ?>
							<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&task=watched&action=delete&Itemid='.$this->itemid.'&id='.$this->item->id); ?>" class="delete"><?php echo JText::_('COM_KA_REMOVEFROM_WATCHED'); ?></a>
							<?php else: ?>
							<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&task=watched&action=add&Itemid='.$this->itemid.'&id='.$this->item->id); ?>" class="add"><?php echo JText::_('COM_KA_ADDTO_WATCHED'); ?></a>
							<?php endif; ?>
						</div>
						<?php endif; ?>
						<?php if ($this->params->get('link_favorite') == 1): ?>
						<div class="fav">
							<?php if ($this->item->favorite == 1): ?>
							<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&task=favorite&action=delete&Itemid='.$this->itemid.'&id='.$this->item->id); ?>" class="delete"><?php echo JText::_('COM_KA_REMOVEFROM_FAVORITE'); ?></a>
							<?php else: ?>
							<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&task=favorite&action=add&Itemid='.$this->itemid.'&id='.$this->item->id); ?>" class="add"><?php echo JText::_('COM_KA_ADDTO_FAVORITE'); ?></a>
							<?php endif; ?>
						</div>
						<?php endif; ?>
					</div>
					<div class="clear"></div>
					<?php endif; ?>
					<div class="movie-info">
						<div>
							<span class="f-col"><?php echo JText::_('COM_KA_YEAR'); ?></span>
							<span class="s-col"><a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=search&task=movie&filter_by[]=year&year[start]='.$this->item->year.'&Itemid='.$this->itemid); ?>"><?php echo $this->item->year; ?></a></span>
						</div>
						<?php if (!empty($this->item->countries)): ?>
						<div>
							<span class="f-col"><?php echo count($this->item->countries) > 1 ? JText::_('COM_KA_COUNTRIES') : JText::_('COM_KA_COUNTRY'); ?></span>
							<span class="s-col">
								<?php for ($i=0, $n=count($this->item->countries); $i<$n; $i++):
								$country = $this->item->countries[$i]; ?>
								<img src="components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/images/icons/countries/<?php echo $country->code; ?>.png" border="0" class="ui-icon-country" alt="<?php echo $country->name; ?>" /> <a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=search&task=movie&filter_by[]=country&country_id[]='.$country->id.'&Itemid='.$this->itemid); ?>" title="<?php echo $country->name; ?>"><?php echo $country->name; ?></a><?php echo ($i+1 == $n) ? '' : ', '; ?>
								<?php endfor; ?>
							</span>
						</div>
						<?php endif; ?>
						<?php if (!empty($this->item->slogan)): ?>
						<div>
							<span class="f-col"><?php echo JText::_('COM_KA_SLOGAN'); ?></span>
							<span class="s-col">
								<span lang="<?php echo substr(JFactory::getLanguage()->getTag(), 0, 2); ?>"><q><?php echo $this->item->slogan; ?></q></span>
							</span>
						</div>
						<?php endif; ?>
						<?php if (isset($this->item->crew) && count($this->item->crew) > 0):
							foreach ($this->item->crew as $person): ?>
							<div>
								<span class="f-col"><?php echo $person['career']; ?></span>
								<span class="s-col">
									<?php for ($i=0, $n=count($person['items']); $i<$n; $i++):
									$name = $person['items'][$i]; ?>
										<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&id='.$name['id'].'&Itemid='.$this->itemid); ?>" title="<?php echo $name['name']; ?>"><?php echo $name['name']; ?></a><?php if ($i+1 == $n): ?><?php if ($n < $person['total_items']): ?>,&nbsp;<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=cast&id='.$this->item->id.'&Itemid='.$this->itemid); ?>#<?php echo JFilterOutput::stringURLSafe($person['career']); ?>" title="<?php echo JText::_('COM_KA_READMORE'); ?>" class="hasTooltip ui-icon-next"></a><?php endif; ?>
									<?php else:
										echo ', ';
									endif; ?>
									<?php endfor; ?>
								</span>
							</div>
							<?php endforeach;
						endif; ?>
						<?php if (isset($this->item->cast) && count($this->item->cast) > 0):
							foreach ($this->item->cast as $person): ?>
							<div>
								<span class="f-col"><?php echo $person['career']; ?></span>
								<span class="s-col">
									<?php for ($i=0, $n=count($person['items']); $i<$n; $i++):
									$name = $person['items'][$i]; ?>
										<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&id='.$name['id'].'&Itemid='.$this->itemid); ?>" title="<?php echo $name['name']; ?>"><?php echo $name['name']; ?></a><?php if ($i+1 == $n): ?><?php if ($n < $person['total_items']): ?>,&nbsp;<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=cast&id='.$this->item->id.'&Itemid='.$this->itemid); ?>#<?php echo JFilterOutput::stringURLSafe($person['career']); ?>" title="<?php echo JText::_('COM_KA_READMORE'); ?>" class="hasTooltip ui-icon-next"></a><?php endif; ?>
									<?php else:
										echo ', ';
									endif; ?>
									<?php endfor; ?>
								</span>
							</div>
							<?php endforeach;
						endif; ?>
						<div>
							<span class="f-col"><?php echo JText::_('COM_KA_GENRE'); ?></span>
							<span class="s-col">
								<?php for ($i=0,$n=count($this->item->genres); $i<$n; $i++):
								$genre = $this->item->genres[$i]; ?>
								<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=search&task=movie&filter_by[]=genre&genre_id[]='.$genre->id.'&Itemid='.$this->itemid); ?>" title="<?php echo $genre->name; ?>"><?php echo $genre->name; ?></a><?php echo ($i+1 == $n) ? '' : ', '; ?>
								<?php endfor; ?>
							</span>
						</div>
						<?php if (!empty($this->item->budget)): ?>
						<div>
							<span class="f-col"><?php echo JText::_('COM_KA_BUDGET'); ?></span>
							<span class="s-col"><?php echo $this->item->budget; ?></span>
						</div>
						<?php endif; ?>
						<?php if (count($this->item->premieres) > 0):
							foreach ($this->item->premieres as $premiere): ?>
							<div>
								<span class="f-col"><?php echo ($premiere->country == '') ? JText::_('COM_KA_PREMIERE_DATE_WORLDWIDE') : JText::sprintf(JText::_('COM_KA_PREMIERE_DATE_LOC'), $premiere->country); ?></span>
								<span class="s-col">
									<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=premieres&id='.$premiere->id.'&Itemid='.$this->itemid); ?>"><?php echo JHtml::_('date', $premiere->premiere_date, JText::_('DATE_FORMAT_LC3')); ?></a><?php if ($premiere->company_name != '' || $premiere->company_name_intl != ''): ?>, <a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=premieres&vendor='.$premiere->vendor_id.'&Itemid='.$this->itemid); ?>"><?php echo ($premiere->company_name_intl != '') ? $premiere->company_name.' / '.$premiere->company_name_intl : $premiere->company_name; ?></a>
										<?php if ($premiere->info != ''): ?><a href="#" class="ui-icon-bullet-arrow-down premiere-info-icon"></a><div class="premiere-info"><?php echo $premiere->info; ?></div><?php endif; ?>
									<?php endif; ?>
								</span>
							</div>
							<?php endforeach;
						endif; ?>
						<?php if (count($this->item->releases) > 0):
							foreach ($this->item->releases as $release): ?>
							<div>
								<span class="f-col"><?php echo ($release->media_type == 0) ? JText::_('COM_KA_RELEASE_MEDIA_TYPE_DVD') : JText::_('COM_KA_RELEASE_MEDIA_TYPE_BD'); ?></span>
								<span class="s-col">
									<?php echo JHtml::_('date', $release->release_date, JText::_('DATE_FORMAT_LC3')); ?><?php if ($release->company_name != '' || $release->company_name_intl != ''): ?>, <?php echo ($release->company_name_intl != '') ? $release->company_name.' / '.$release->company_name_intl : $release->company_name; ?><?php endif; ?><?php echo ($release->country != '') ? ', '.$release->country : ''; ?>,&nbsp;<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=releases&id='.$release->id.'&Itemid='.$this->itemid); ?>" title="<?php echo JText::_('COM_KA_READMORE'); ?>" class="hasTooltip ui-icon-next"></a>
								</span>
							</div>
							<?php endforeach;
						endif; ?>
						<?php if ($this->item->mpaa == -1 && $this->item->age_restrict == -1 && $this->item->ua_rate == -1): ?>
						<?php else: ?>
						<div>
							<span class="f-col"><?php echo JText::_('COM_KA_RATES'); ?></span>
							<span class="s-col">
								<?php if ($this->item->mpaa > -1): ?>
								<div class="rating">
									<div id="mpaa" class="mpaa-icon hasTooltip" title="<?php echo JText::sprintf(JText::_('COM_KA_RATE_HELP'), JText::_('COM_KA_MPAA')); ?>"><strong><?php echo strtoupper($this->item->mpaa); ?></strong></div>
									<div class="mpaa-desc"><?php echo JText::_('COM_KA_MPAA_DESC'); ?></div>
								</div>
								<?php endif; ?>
								<?php if ($this->item->age_restrict > -1): ?>
								<div class="rating">
									<div id="rrate" class="rrate-icon hasTooltip" title="<?php echo JText::sprintf(JText::_('COM_KA_RATE_HELP'), JText::_('COM_KA_RU_RATE')); ?>"><strong><?php echo strtoupper($this->item->age_restrict); ?>+</strong></div>
									<div class="rrate-desc"><?php echo JText::_('COM_KA_RU_RATE_DESC'); ?></div>
								</div>
								<?php endif; ?>
								<?php if ($this->item->ua_rate > -1): ?>
								<div class="rating">
									<div id="ua-rate" class="uar-icon uar-icon-<?php echo (int)$this->item->ua_rate; ?> hasTooltip" title="<?php echo JText::sprintf(JText::_('COM_KA_RATE_HELP'), JText::_('COM_KA_UA_RATE')); ?>">&nbsp;</div>
									<div class="uarate-desc"><?php echo JText::_('COM_KA_UA_RATE_DESC'); ?></div>
								</div>
								<?php endif; ?>
							</span>
						</div>
						<?php endif; ?>
						<div>
							<span class="f-col"><?php echo JText::_('COM_KA_LENGTH'); ?></span>
							<span class="s-col"><?php echo $this->item->_hr_length; ?><?php echo JText::_('COM_KA_LENGTH_MINUTES'); ?> | <?php echo $this->item->_length; ?></span>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
			$player_layout = ($this->params->get('player_type') == '-1') ? 'trailer' : 'trailer_'.$this->params->get('player_type');
			if ($total_trailers > 0 || $total_movies > 0) {
				// Needed to avoid a bugs. Flowplayer redirecting when SEF is turned on. JWplayer show an error(but playing w/o errors).
				if ($this->params->get('player_type') == 'flowplayer' || $this->params->get('player_type') == 'jwplayer') {
				?>
					<div class="clear"></div>
					<div class="watch-buttons">
						<?php if ($total_trailers > 0): ?>
							<a href="#" class="watch-trailer"><?php echo JText::_('COM_KA_WATCH_TRAILER'); ?></a>
						<?php endif; ?>
						<?php if ($total_movies > 0): ?>
							<a href="#" class="watch-movie"><?php echo JText::_('COM_KA_WATCH_MOVIE'); ?></a>
						<?php endif; ?>
					</div>
				<?php
				} else {
					echo $this->loadTemplate($player_layout);
				}
			}
		?>
		<?php if (!$this->user->get('guest') && $this->params->get('allow_votes') == 1): ?>
			<div class="clear"></div>
			<div class="rate">
				<strong><?php echo JText::_('COM_KA_RATE'); ?></strong><br />
				<select id="rate_field" autocomplete="off">
					<?php for ($i=0, $n=(int)$this->params->get('vote_summ_num')+1; $i<$n; $i++): ?>
					<option value="<?php echo $i; ?>"<?php echo ($i == round($this->item->rate_loc_label)) ? ' selected="selected"' : ''; ?>><?php echo $i; ?></option>
					<?php endfor; ?>
				</select>
				<div class="rateit" data-rateit-value="<?php echo round($this->item->rate_loc_label); ?>" data-rateit-backingfld="#rate_field"></div>&nbsp;<span><?php echo $this->item->rate_loc_label; ?></span>
				<div class="my_votes" style="<?php echo ($this->item->my_vote == 0) ? 'display: none;' : ''; ?>">
					<div class="my_vote"><?php echo JText::_('COM_KA_RATE_MY'); ?><span><?php echo $this->item->my_vote; ?></span> <?php echo JText::_('COM_KA_FROM'); ?> <?php echo (int)$this->params->get('vote_summ_num'); ?> <span class="small">(<?php echo JHtml::_('date', $this->item->_datetime, JText::_('DATE_FORMAT_LC3')); ?>)</span></div>
					<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=votes&Itemid='.$this->itemid); ?>" class="small"><?php echo JText::_('COM_KA_RATE_MY_ALL'); ?></a>
				</div>
			</div>
		<?php else: ?>
			<div class="clear"></div>
			<div class="rate">
				<strong><?php echo JText::_('COM_KA_RATE'); ?></strong><br />
				<div class="rateit" data-rateit-value="<?php echo $this->item->rate_loc; ?>" data-rateit-min="0" data-rateit-max="<?php echo (int)$this->params->get('vote_summ_num'); ?>" data-rateit-ispreset="true" data-rateit-readonly="true"></div>&nbsp;<?php echo $this->item->rate_loc_label; ?>
				<div><?php echo GlobalHelper::showMsg(JText::sprintf(JText::_('COM_KA_VOTES_AUTHREQUIRED'), '<a href="'.JRoute::_('index.php?option=com_users&view=registration').'">'.JText::_('COM_KA_REGISTER').'</a>', '<a href="'.JRoute::_('index.php?option=com_users&view=login').'">'.JText::_('COM_KA_LOGIN').'</a>')); ?></div>
			</div>
		<?php endif; ?>
		<div class="clear"></div>
		<?php if (!empty($this->item->plot)): ?>
		<div class="plot">
			<h3 class="<?php echo $this->class['header_subhead']; ?>"><?php echo JText::_('COM_KA_PLOT'); ?></h3>
			<p><?php echo $this->item->plot; ?></p>
		</div>
		<?php endif; ?>
		<?php if (!empty($this->item->known)): ?>
		<div class="known">
			<h3 class="<?php echo $this->class['header_subhead']; ?>"><?php echo JText::_('COM_KA_KNOWN'); ?></h3>
			<div class="content"><?php echo $this->item->known; ?></div>
		</div>
		<?php endif; ?>
		<?php if (!empty($this->item->desc)): ?>
		<div class="desc">
			<h3 class="<?php echo $this->class['header_subhead']; ?>"><?php echo JText::_('COM_KA_TECH'); ?> <span class="toggle down">&nbsp;</span> <a href="#" id="open-desc"><img src="components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/images/icons/new_window_14.png" border="0" /></a></h3>
			<div class="content"><p><?php echo $this->item->desc; ?></p></div>
		</div>
		<?php endif; ?>
		<?php if ($this->params->get('allow_movie_download') == 1): ?>
		<div class="urls">
			<div class="content"><p><?php echo $this->item->urls; ?></p></div>
		</div>
		<?php endif; ?>
	
	<?php echo $this->item->event->afterDisplayContent; ?>
	<?php if ($this->params->get('show_reviews') == 1):
		echo $this->loadTemplate('reviews');
	endif; ?>
	</article>
</div>
