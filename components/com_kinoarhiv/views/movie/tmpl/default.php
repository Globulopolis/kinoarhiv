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

use Joomla\String\StringHelper;

$totalTrailers = count(get_object_vars($this->item->trailer));
$totalMovies = count(get_object_vars($this->item->movie));

if (StringHelper::substr($this->params->get('media_rating_image_root_www'), 0, 1) == '/')
{
	$ratingImageWWW = JUri::base() . StringHelper::substr($this->params->get('media_rating_image_root_www'), 1);
}
else
{
	$ratingImageWWW = $this->params->get('media_rating_image_root_www');
}

JHtml::_('stylesheet', 'media/com_kinoarhiv/css/colorbox.css');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery.colorbox.min.js');
KAComponentHelper::getScriptLanguage('jquery.colorbox-', 'media/com_kinoarhiv/js/i18n/colorbox');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery.plugin.min.js');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery.countdown.min.js');
KAComponentHelper::getScriptLanguage('jquery.countdown-', 'media/com_kinoarhiv/js/i18n/countdown');
?>
<script type="text/javascript">
	jQuery(document).ready(function ($) {
		$('#mpaa').click(function (e) {
			e.preventDefault();
			$.colorbox({
				html: '<div class="desc">' + $(this).next('.mpaa-desc').html() + '</div>',
				height: '80%',
				width: '80%'
			});
		});
		$('#rrate').click(function (e) {
			e.preventDefault();
			$.colorbox({
				html: '<div class="desc">' + $(this).next('.rrate-desc').html() + '</div>',
				height: '80%',
				width: '80%'
			});
		});
		$('#ua-rate').click(function (e) {
			e.preventDefault();
			$.colorbox({
				html: '<div class="desc">' + $(this).next('.uarate-desc').html() + '</div>',
				height: '80%',
				width: '80%'
			});
		});
		$('.premiere-info-icon').click(function (e) {
			e.preventDefault();
			var _this = $(this);

			$.colorbox({html: '<div style="margin: 1em 2em 1em .5em;">' + _this.next('div').html() + '</div>'});
		});

		$('.countdown-premiere').each(function(){
			var el = $(this);
			var el_datetime = el.data('premiere-datetime');

			if (typeof el_datetime === 'string') {
				var time = el_datetime.split(/[- :]/);
				var datetime = new Date(time[0], time[1] - 1, time[2], time[3] || 0, time[4] || 0, time[5] || 0);

				el.countdown({
					until: datetime,
					format: 'yodHM',
					layout: '{y<}{yn} {yl}{y>} {o<}{on} {ol}{o>} {d<}{dn} {dl}{d>} {hn} {hl} {mn} {ml}',
					alwaysExpire: true,
					onExpiry: function () {
						el.countdown('destroy');
					}
				});
			}
		});

		var embed = $("iframe[src^='//player.vimeo.com'], iframe[src*='www.youtube.com'], iframe[src*='www.youtube-nocookie.com'], object, embed"),
			embed_container = $('.video-embed');

		embed.each(function(){
			$(this).attr('data-aspectRatio', this.height / this.width).removeAttr('height').removeAttr('width');
		});

		$(window).resize(function(){
			var new_width = embed_container.width();

			embed.each(function(){
				var $this = $(this);

				$this.width(new_width).height(new_width * $this.attr('data-aspectRatio'));
			});
		}).resize();
	});
</script>
<div class="uk-article ka-content" itemscope itemtype="https://schema.org/Movie">
	<meta itemprop="contentRating" content="MPAA <?php echo strtoupper($this->item->mpaa); ?>">
	<meta itemprop="duration" content="<?php echo $this->item->_length; ?>">
	<meta itemprop="isFamilyFriendly" content="<?php echo ($this->item->mpaa == 'g' || $this->item->mpaa == 'pg') ? 'True' : 'False';?>">

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
				'itemid' => $this->itemid,
				'guest'  => $this->user->get('guest'),
				'url'    => 'index.php?option=com_kinoarhiv&view=movie&id=' . $this->item->id . '&Itemid=' . $this->itemid
			),
			JPATH_COMPONENT
		);
		?>
		<?php echo $this->item->event->afterDisplayTitle; ?>
		<?php echo $this->loadTemplate('tabs'); ?>
		<?php echo $this->item->event->beforeDisplayContent; ?>

		<div class="info">
			<div class="left-col">
				<div class="poster">
					<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=posters&id=' . $this->item->id . '&Itemid=' . $this->itemid); ?>"
					   title="<?php echo $this->escape(KAContentHelper::formatItemTitle($this->item->title, '', $this->item->year)); ?>">
					   <img src="<?php echo $this->item->poster; ?>" itemprop="image"
							alt="<?php echo JText::_('COM_KA_POSTER_ALT') . $this->escape($this->item->title); ?>" />
					</a>
				</div>

				<?php if ($this->params->get('ratings_show_frontpage') == 1):
					echo JLayoutHelper::render(
						'layouts.content.ratings_movie',
						array('params' => $this->params, 'item' => $this->item, 'column' => true),
						JPATH_COMPONENT
					);
				endif; ?>
			</div>
			<div class="right-col">
				<div class="movie-info">
					<div>
						<span class="f-col"><?php echo JText::_('COM_KA_YEAR'); ?></span>
						<span class="s-col"><a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movies&content=movies&movies[year]=' . $this->item->year . '&Itemid=' . $this->itemid); ?>" rel="nofollow"><?php echo $this->item->year; ?></a></span>
					</div>
					<?php if (!empty($this->item->countries)): ?>
						<div>
							<span class="f-col"><?php echo count($this->item->countries) > 1 ? JText::_('COM_KA_COUNTRIES') : JText::_('COM_KA_COUNTRY'); ?></span>
							<span class="s-col">
								<?php $cn_count = count($this->item->countries);
								for ($i = 0, $n = $cn_count; $i < $n; $i++):
									$country = $this->item->countries[$i]; ?>
									<img src="media/com_kinoarhiv/images/icons/countries/<?php echo $country->code; ?>.png" class="ui-icon-country" alt="<?php echo $country->name; ?>"/>
									<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movies&content=movies&movies[country]=' . $country->id . '&Itemid=' . $this->itemid); ?>" title="<?php echo $country->name; ?>" rel="nofollow"><?php echo $country->name; ?></a><?php echo ($i + 1 == $n) ? '' : ', '; ?>
								<?php endfor; ?>
							</span>
						</div>
					<?php endif; ?>
					<?php if (!empty($this->item->slogan)): ?>
						<div>
							<span class="f-col"><?php echo JText::_('COM_KA_SLOGAN'); ?></span>
							<span class="s-col">
								<span lang="<?php echo substr($this->lang->getTag(), 0, 2); ?>"><q><?php echo $this->item->slogan; ?></q></span>
							</span>
						</div>
					<?php endif; ?>
					<?php if (isset($this->item->crew) && count($this->item->crew) > 0):
						foreach ($this->item->crew as $person): ?>
							<div>
								<span class="f-col"><?php echo $person['career']; ?></span>
								<span class="s-col">
									<?php $person_count = count($person['items']);
									for ($i = 0, $n = $person_count; $i < $n; $i++):
										$name = $person['items'][$i];
										$itemprop = ($name['directors'] == 1) ? 'itemprop="director"' : '';
									?>
										<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $name['id'] . '&Itemid=' . $this->namesItemid); ?>" title="<?php echo $name['name']; ?>" <?php echo $itemprop; ?>><?php echo $name['name']; ?></a><?php if ($i + 1 == $n): ?><?php if ($n < $person['total_items']): ?>,&nbsp;
									<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=cast&id=' . $this->item->id . '&Itemid=' . $this->itemid); ?>#<?php echo JFilterOutput::stringURLSafe($person['career']); ?>" title="<?php echo JText::_('COM_KA_READMORE'); ?>" class="hasTooltip ui-icon-next"></a><?php endif; ?>
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
									<?php $person_count = count($person['items']);
									for ($i = 0, $n = $person_count; $i < $n; $i++):
										$name = $person['items'][$i]; ?>
										<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $name['id'] . '&Itemid=' . $this->namesItemid); ?>" title="<?php echo $name['name']; ?>" itemprop="actor"><?php echo $name['name']; ?></a><?php if ($i + 1 == $n): ?><?php if ($n < $person['total_items']): ?>,&nbsp;
									<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=cast&id=' . $this->item->id . '&Itemid=' . $this->itemid); ?>#<?php echo JFilterOutput::stringURLSafe($person['career']); ?>" title="<?php echo JText::_('COM_KA_READMORE'); ?>" class="hasTooltip ui-icon-next"></a><?php endif; ?>
									<?php else:
										echo ', ';
									endif; ?>
									<?php endfor; ?>
								</span>
							</div>
						<?php endforeach;
					endif; ?>
					<?php if (isset($this->item->genres) && count($this->item->genres) > 0): ?>
						<div>
							<span class="f-col"><?php echo JText::_('COM_KA_GENRE'); ?></span>
							<span class="s-col">
								<?php $genre_count = count($this->item->genres);
								for ($i = 0, $n = $genre_count; $i < $n; $i++):
									$genre = $this->item->genres[$i]; ?>
									<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movies&content=movies&movies[genre][]=' . $genre->id . '&Itemid=' . $this->itemid); ?>" title="<?php echo $genre->name; ?>" itemprop="genre" rel="nofollow"><?php echo $genre->name; ?></a><?php echo ($i + 1 == $n) ? '' : ', '; ?>
								<?php endfor; ?>
							</span>
						</div>
					<?php endif; ?>
					<?php if (!empty($this->item->budget)): ?>
						<div>
							<span class="f-col"><?php echo JText::_('COM_KA_BUDGET'); ?></span>
							<span class="s-col"><a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movies&content=movies&movies[budget][]=' . $this->item->budget . '&Itemid=' . $this->itemid); ?>" rel="nofollow"><?php echo $this->item->budget; ?></a></span>
						</div>
					<?php endif; ?>
					<?php if (count($this->item->premieres) > 0):
						foreach ($this->item->premieres as $premiere): ?>
							<div>
								<span class="f-col"><?php echo ($premiere->country == '') ? JText::_('COM_KA_PREMIERE_DATE_WORLDWIDE') : JText::sprintf(JText::_('COM_KA_PREMIERE_DATE_LOC'), $premiere->country); ?></span>
								<span class="s-col">
									<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movies&content=movies&movies[premiere_date]=' . date('Y-m', strtotime($premiere->premiere_date)) . '&Itemid=' . $this->itemid); ?>"><?php echo JHtml::_('date', $premiere->premiere_date, JText::_('DATE_FORMAT_LC3')); ?></a><?php if (!empty($premiere->company_name)): ?>, <a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movies&content=movies&movies[vendor]=' . $premiere->vendor_id . '&Itemid=' . $this->itemid); ?>"><?php echo $premiere->company_name; ?></a>
										<?php if ($premiere->info != ''): ?>
											<a href="#" class="ui-icon-bullet-arrow-down premiere-info-icon"></a>
											<div class="premiere-info"><?php echo $premiere->info; ?></div><?php endif; ?>
									<?php endif; ?>
									<div class="countdown-premiere" data-premiere-datetime="<?php echo $premiere->premiere_date; ?>"></div>
								</span>
							</div>
						<?php endforeach;
					endif; ?>
					<?php if (count($this->item->releases) > 0):
						foreach ($this->item->releases as $release): ?>
							<div>
								<span class="f-col">
									<?php echo JText::sprintf('COM_KA_RELEASES_MEDIATYPE', JHtml::_('string.truncate', $release->media_type, 14)); ?>
								</span>
								<span class="s-col">
									<?php echo JHtml::_('date', $release->release_date, JText::_('DATE_FORMAT_LC3')); ?><?php if (!empty($release->company_name)): ?>, <?php echo $release->company_name; ?><?php endif; ?><?php echo ($release->country != '') ? ', ' . $release->country : ''; ?>&nbsp;<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=release&id=' . $release->movie_id . '&Itemid=' . $this->releasesItemid); ?>#row-<?php echo $release->id; ?>" title="<?php echo JText::_('COM_KA_READMORE'); ?>" class="hasTooltip ui-icon-next"></a>
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
										<div id="mpaa" class="mpaa-icon hasTooltip"
											 title="<?php echo JText::sprintf(JText::_('COM_KA_RATE_HELP'), JText::_('COM_KA_MPAA')); ?>">
											<strong><?php echo strtoupper($this->item->mpaa); ?></strong></div>
										<div class="mpaa-desc"><?php echo JText::_('COM_KA_MPAA_DESC'); ?></div>
									</div>
								<?php endif; ?>
								<?php if ($this->item->age_restrict > -1): ?>
									<div class="rating">
										<div id="rrate" class="rrate-icon hasTooltip" title="<?php echo JText::sprintf(JText::_('COM_KA_RATE_HELP'), JText::_('COM_KA_RU_RATE')); ?>">
											<strong><?php echo strtoupper($this->item->age_restrict); ?>+</strong></div>
										<div class="rrate-desc"><?php echo JText::_('COM_KA_RU_RATE_DESC'); ?></div>
									</div>
								<?php endif; ?>
								<?php if ($this->item->ua_rate > -1): ?>
									<div class="rating">
										<div id="ua-rate" class="uar-icon uar-icon-<?php echo (int) $this->item->ua_rate; ?> hasTooltip" title="<?php echo JText::sprintf(JText::_('COM_KA_RATE_HELP'), JText::_('COM_KA_UA_RATE')); ?>">
											&nbsp;</div>
										<div class="uarate-desc"><?php echo JText::_('COM_KA_UA_RATE_DESC'); ?></div>
									</div>
								<?php endif; ?>
							</span>
						</div>
					<?php endif; ?>
					<div>
						<span class="f-col"><?php echo JText::_('COM_KA_LENGTH'); ?></span>
						<span class="s-col"><?php echo $this->item->_hr_length; ?><?php echo JText::_('COM_KA_LENGTH_MINUTES'); ?>
							| <?php echo $this->item->_length; ?></span>
					</div>
					<?php if (count($this->item->tags->itemTags) > 0): ?>
					<div>
						<span class="f-col"><?php echo JText::_('JTAG'); ?></span>
						<span class="s-col">
						<?php foreach ($this->item->tags->itemTags as $tag): ?>
							<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movies&content=movies&movies[tags]=' . $tag->tag_id . '&Itemid=' . $this->itemid); ?>" class="label label-info uk-badge tags" title="<?php echo $tag->title; ?>"><?php echo $tag->title; ?></a>
						<?php endforeach; ?>
						</span>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="clear"></div>
		<div class="buy">
			<p><?php echo $this->item->buy_urls; ?></p>
		</div>

		<?php
		echo JLayoutHelper::render('layouts.content.votes_movie',
			array(
				'params'        => $this->params,
				'item'          => $this->item,
				'guest'         => $this->user->get('guest'),
				'itemid'        => $this->itemid,
				'profileItemid' => $this->profileItemid,
				'view'          => $this->view
			),
			JPATH_COMPONENT
		);
		?>

		<?php
		echo JLayoutHelper::render('layouts.content.images_slider',
			array(
				'params'  => $this->params,
				'items'   => $this->item->slides,
				'attribs' => $this->item->attribs->slider
			),
			JPATH_COMPONENT
		);
		?>

		<?php
		if (($totalTrailers > 0 || $totalMovies > 0) && ($this->params->get('watch_trailer') == 1 || $this->params->get('watch_movie') == 1))
		{
			echo $this->loadTemplate('trailer');
		}
		?>

		<?php if (!empty($this->item->plot)): ?>
			<div class="plot">
				<h3><?php echo JText::_('COM_KA_PLOT'); ?></h3>
				<div class="content" itemprop="description"><?php echo $this->item->plot; ?></div>
			</div>
		<?php endif; ?>

		<?php if (!empty($this->item->known)): ?>
			<br />
			<div class="known">
				<div class="accordion-group">
					<div class="accordion-heading">
						<h4>
							<a class="accordion-toggle" data-toggle="collapse" data-parent="#desc"
							   href="#showKnownDescription"><?php echo JText::_('COM_KA_KNOWN'); ?></a>
						</h4>
					</div>
					<div id="showKnownDescription" class="accordion-body collapse">
						<div class="content"><?php echo $this->item->known; ?></div>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<?php if (!empty($this->item->desc)): ?>
			<br />
			<div class="desc" id="desc">
				<div class="accordion-group">
					<div class="accordion-heading">
						<a class="accordion-toggle" data-toggle="collapse" data-parent="#desc"
						   href="#showTechDescription"><?php echo JText::_('COM_KA_TECH'); ?></a>
					</div>
					<div id="showTechDescription" class="accordion-body collapse">
						<div class="accordion-inner"><?php echo $this->item->desc; ?></div>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<?php if ($this->params->get('allow_movie_download') == 1): ?>
			<div class="urls">
				<div class="content"><p><?php echo $this->item->urls; ?></p></div>
			</div>
		<?php endif; ?>

		<?php echo $this->item->event->afterDisplayContent; ?>
		<?php echo $this->loadTemplate('reviews'); ?>
	</article>
</div>
