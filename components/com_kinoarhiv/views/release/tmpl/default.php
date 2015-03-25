<?php defined('_JEXEC') or die;
if (JString::substr($this->params->get('media_rating_image_root_www'), 0, 1) == '/') {
	$rating_image_www = JURI::base().JString::substr($this->params->get('media_rating_image_root_www'), 1);
} else {
	$rating_image_www = $this->params->get('media_rating_image_root_www');
}
?>
<script src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/jquery.colorbox-min.js" type="text/javascript"></script>
<?php GlobalHelper::getScriptLanguage('jquery.colorbox-', false, 'colorbox'); ?>
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
		$('.hasTooltip, .hasTooltip').attr('data-uk-tooltip', '');
	});
//]]>
</script>
<div class="uk-article ka-content">
	<?php if ($this->params->get('use_alphabet') == 1):
		echo $this->loadTemplate('alphabet');
	endif; ?>

	<article class="uk-article item">
		<header>
			<h1 class="uk-article-title title">
				<?php if ($this->item->attribs->link_titles === ''): ?>
					<?php if ($this->params->get('link_titles') == 1): ?>
						<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&id='.$this->item->id.'&Itemid='.$this->itemid); ?>" class="brand" title="<?php echo $this->escape($this->item->title.$this->item->year_str); ?>"><?php echo $this->escape($this->item->title.$this->item->year_str); ?></a>
					<?php else: ?>
						<span class="brand"><?php echo $this->escape($this->item->title.$this->item->year_str); ?></span>
					<?php endif; ?>
				<?php elseif ($this->item->attribs->link_titles == 1): ?>
					<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&id='.$this->item->id.'&Itemid='.$this->itemid); ?>" class="brand" title="<?php echo $this->escape($this->item->title.$this->item->year_str); ?>"><?php echo $this->escape($this->item->title.$this->item->year_str); ?></a>
				<?php elseif ($this->item->attribs->link_titles == 0): ?>
					<span class="brand"><?php echo $this->escape($this->item->title.$this->item->year_str); ?></span>
				<?php endif; ?>
			</h1>
		</header>
		<div class="middle-nav clearfix">
			<p class="meta">
				<?php if ($this->item->attribs->show_author === '' && !empty($this->item->username)): ?>
					<?php if ($this->params->get('show_author') == 1): ?>
						<span class="icon-user"></span> <?php echo JText::_('JAUTHOR'); ?>: <?php echo $this->item->username; ?><br />
					<?php endif; ?>
				<?php elseif ($this->item->attribs->show_author == 1 && !empty($this->item->username)): ?>
					<span class="icon-user"></span> <?php echo JText::_('JAUTHOR'); ?>: <?php echo $this->item->username; ?><br />
				<?php endif; ?>

				<?php if ($this->item->attribs->show_create_date === ''): ?>
					<?php if ($this->params->get('show_pubdate') == 1): ?>
						<span class="icon-calendar"></span> <?php echo JText::_('COM_KA_CREATED_DATE_ON'); ?><time pubdate="" datetime="<?php echo $this->item->created; ?>"><?php echo date('j F Y', strtotime($this->item->created)); ?></time>
					<?php endif; ?>
				<?php elseif ($this->item->attribs->show_create_date == 1): ?>
					<span class="icon-calendar"></span> <?php echo JText::_('COM_KA_CREATED_DATE_ON'); ?><time pubdate="" datetime="<?php echo $this->item->created; ?>"><?php echo date('j F Y', strtotime($this->item->created)); ?></time>
				<?php endif; ?>

				<?php
				if ((
						($this->item->attribs->show_create_date === '' && $this->params->get('show_pubdate') == 1) || $this->item->attribs->show_create_date == 1
					) && (
						($this->item->attribs->show_modify_date === '' && $this->params->get('show_moddate') == 1) || $this->item->attribs->show_modify_date == 1
					)):
					echo ' &bull; ';
				endif; ?>

				<?php if ($this->item->attribs->show_modify_date === ''): ?>
					<?php if ($this->params->get('show_moddate') == 1): ?>
						<?php echo JText::_('COM_KA_LAST_UPDATED'); ?><time pubdate="" datetime="<?php echo $this->item->modified; ?>"><?php echo date('j F Y', strtotime($this->item->modified)); ?></time>
					<?php endif; ?>
				<?php elseif ($this->item->attribs->show_modify_date == 1): ?>
					<?php echo JText::_('COM_KA_LAST_UPDATED'); ?><time pubdate="" datetime="<?php echo $this->item->modified; ?>"><?php echo date('j F Y', strtotime($this->item->modified)); ?></time>
				<?php endif; ?>
			</p>
		</div>
		<?php echo $this->item->event->afterDisplayTitle; ?>
		<?php echo $this->item->event->beforeDisplayContent; ?>
		<div class="clear"></div>
		<div class="content clearfix ui-helper-clearfix">
			<div>
				<div class="poster<?php echo $this->item->y_poster; ?>">
					<div style="text-align: center;">
						<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=posters&id='.$this->item->id.'&Itemid='.$this->itemid); ?>" title="<?php echo $this->escape($this->item->title.$this->item->year_str); ?>"><img src="<?php echo $this->item->poster; ?>" border="0" alt="<?php echo JText::_('COM_KA_POSTER_ALT').$this->escape($this->item->title); ?>" /></a>
					</div>
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
									<?php if ($this->params->get('ratings_img_imdb') != 0 && !empty($this->item->imdb_id)) {
										if (file_exists($this->params->get('media_rating_image_root').'/imdb/'.$this->item->id.'_big.png')) { ?>
										<a href="http://www.imdb.com/title/tt<?php echo $this->item->imdb_id; ?>/" rel="nofollow" target="_blank"><img src="<?php echo $rating_image_www; ?>/imdb/<?php echo $this->item->id; ?>_big.png" border="0" /></a>
										<?php }
									} ?>
									<?php if ($this->params->get('ratings_img_kp') != 0 && !empty($this->item->kp_id)): ?>
										<a href="http://www.kinopoisk.ru/film/<?php echo $this->item->kp_id; ?>/" rel="nofollow" target="_blank">
										<?php if ($this->params->get('ratings_img_kp_remote') == 0): ?>
											<img src="<?php echo $rating_image_www; ?>/kinopoisk/<?php echo $this->item->id; ?>_big.png" border="0" />
										<?php else: ?>
											<img src="http://www.kinopoisk.ru/rating/<?php echo $this->item->kp_id; ?>.gif" border="0" style="padding-left: 1px;" />
										<?php endif; ?>
										</a>
									<?php endif; ?>
									<?php if ($this->params->get('ratings_img_rotten') != 0 && !empty($this->item->rottentm_id)): ?>
										<?php if (file_exists($this->params->get('media_rating_image_root').'/rottentomatoes/'.$this->item->id.'_big.png')): ?>
										<a href="http://www.rottentomatoes.com/m/<?php echo $this->item->rottentm_id; ?>/" rel="nofollow" target="_blank"><img src="<?php echo $rating_image_www; ?>/rottentomatoes/<?php echo $this->item->id; ?>_big.png" border="0" /></a>
										<?php endif; ?>
									<?php endif; ?>
									<?php if ($this->params->get('ratings_img_metacritic') != 0 && !empty($this->item->metacritics_id)): ?>
										<?php if (file_exists($this->params->get('media_rating_image_root').'/metacritic/'.$this->item->id.'_big.png')): ?>
										<a href="http://www.metacritic.com/movie/<?php echo $this->item->metacritics_id; ?>/" rel="nofollow" target="_blank"><img src="<?php echo $rating_image_www; ?>/metacritic/<?php echo $this->item->id; ?>_big.png" border="0" /></a>
										<?php endif; ?>
									<?php endif; ?>
								</div>
							<?php else: ?>
								<?php if (!empty($this->item->imdb_votesum) && !empty($this->item->imdb_votes)): ?>
									<div id="rate-imdb"><span class="a"><?php echo JText::_('COM_KA_RATE_IMDB'); ?></span> <span class="b"><a href="http://www.imdb.com/title/tt<?php echo $this->item->imdb_id; ?>/?ref_=fn_al_tt_1" rel="nofollow" target="_blank"><?php echo $this->item->imdb_votesum; ?> (<?php echo $this->item->imdb_votes; ?>)</a></span></div>
								<?php else: ?>
									<div id="rate-imdb"><span class="a"><?php echo JText::_('COM_KA_RATE_IMDB'); ?></span> <?php echo JText::_('COM_KA_RATE_NO'); ?></div>
								<?php endif; ?>
								<?php if (!empty($this->item->kp_votesum) && !empty($this->item->kp_votes)): ?>
									<div id="rate-kp"><span class="a"><?php echo JText::_('COM_KA_RATE_KP'); ?></span> <span class="b"><a href="http://www.kinopoisk.ru/film/<?php echo $this->item->kp_id; ?>/" rel="nofollow" target="_blank"><?php echo $this->item->kp_votesum; ?> (<?php echo $this->item->kp_votes; ?>)</a></span></div>
								<?php else: ?>
									<div id="rate-kp"><span class="a"><?php echo JText::_('COM_KA_RATE_KP'); ?></span> <?php echo JText::_('COM_KA_RATE_NO'); ?></div>
								<?php endif; ?>
								<?php if (!empty($this->item->rate_fc)): ?>
									<div id="rate-rt"><span class="a"><?php echo JText::_('COM_KA_RATE_RT'); ?></span> <span class="b"><a href="http://www.rottentomatoes.com/m/<?php echo $this->item->rottentm_id; ?>/" rel="nofollow" target="_blank"><?php echo $this->item->rate_fc; ?>%</a></span></div>
								<?php else: ?>
									<div id="rate-rt"><span class="a"><?php echo JText::_('COM_KA_RATE_RT'); ?></span> <?php echo JText::_('COM_KA_RATE_NO'); ?></div>
								<?php endif; ?>
								<?php if (!empty($this->item->metacritics)): ?>
									<div id="rate-rt"><span class="a"><?php echo JText::_('COM_KA_RATE_MC'); ?></span> <span class="b"><a href="http://www.metacritic.com/movie/<?php echo $this->item->metacritics_id; ?>/" rel="nofollow" target="_blank"><?php echo $this->item->metacritics; ?>%</a></span></div>
								<?php else: ?>
									<div id="rate-rt"><span class="a"><?php echo JText::_('COM_KA_RATE_MC'); ?></span> <?php echo JText::_('COM_KA_RATE_NO'); ?></div>
								<?php endif; ?>
							<?php endif; ?>
						<?php endif; ?>
						<div class="local-rt<?php echo $this->item->rate_loc_label_class; ?>">
							<div class="rateit" data-rateit-value="<?php echo $this->item->rate_loc_c; ?>" data-rateit-min="0" data-rateit-max="<?php echo (int)$this->params->get('vote_summ_num'); ?>" data-rateit-ispreset="true" data-rateit-readonly="true"></div>&nbsp;<?php echo $this->item->rate_loc_label; ?>
						</div>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<?php if (count($this->item->items) > 0): ?>
		<table class="table table-striped table-hover uk-table uk-table-striped uk-table-hover">
			<thead>
				<tr>
					<th><?php echo JText::_('COM_KA_RELEASES_MEDIATYPE_DATE_TITLE'); ?></th>
					<th><?php echo JText::_('COM_KA_COUNTRY'); ?></th>
					<th><?php echo JText::_('COM_KA_RELEASES_MEDIATYPE_TITLE'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($this->item->items as $row): ?>
				<tr>
					<td><span class="hasTooltip" title="<?php echo $row->release_date; ?>"><?php echo JHtml::_('date', $row->release_date, JText::_('DATE_FORMAT_LC3')); ?></span></td>
					<td><img class="flag-dd" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/images/icons/countries/<?php echo $row->code; ?>.png" /><?php echo $row->name; ?></td>
					<td><?php echo JText::_('COM_KA_RELEASES_MEDIATYPE_'.$row->media_type); ?></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php endif; ?>

		<?php if (!empty($this->item->desc)): ?>
		<div class="ui-widget desc">
			<h3><?php echo JText::_('COM_KA_TECH'); ?></h3>
			<div><p><?php echo $this->item->desc; ?></p></div>
		</div>
		<?php endif; ?>
	
		<?php echo $this->item->event->afterDisplayContent; ?>
	</article>
</div>
