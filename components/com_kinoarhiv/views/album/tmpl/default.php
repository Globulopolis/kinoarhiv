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

$totalTracks = count(get_object_vars($this->item->tracks));

JHtml::_('stylesheet', 'media/com_kinoarhiv/css/colorbox.css');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery.colorbox.min.js');
KAComponentHelper::getScriptLanguage('jquery.colorbox-', 'media/com_kinoarhiv/js/i18n/colorbox');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery.rateit.min.js');
?>
<script type="text/javascript">
	jQuery(document).ready(function ($) {
	});
</script>
<div class="uk-article ka-content" itemscope itemtype="https://schema.org/MusicAlbum">
	<meta content="8" itemprop="numTracks" />
	<meta content="Alt/Punk" itemprop="genre" />

	<?php if ($this->params->get('use_alphabet') == 1):
		echo JLayoutHelper::render('layouts.navigation.alphabet', array('params' => $this->params, 'itemid' => $this->itemid), JPATH_COMPONENT);
	endif; ?>

	<article class="uk-article">
		<?php
		echo JLayoutHelper::render(
			'layouts.navigation.album_item_header',
			array('params' => $this->params, 'item' => $this->item, 'itemid' => $this->itemid),
			JPATH_COMPONENT
		);
		echo $this->item->event->afterDisplayTitle;
		//echo $this->loadTemplate('tabs');
		echo $this->item->event->beforeDisplayContent; ?>

		<div class="album-info">
			<div class="left-col span3">
				<div class="poster">
					<img itemprop="image" src="<?php echo $this->item->cover; ?>"
						 alt="<?php echo JText::_('COM_KA_ARTWORK_ALT') . $this->escape($this->item->title); ?>"
						 width="<?php echo $this->item->coverWidth; ?>" height="<?php echo $this->item->coverHeight; ?>" />
				</div>

				<?php if ($this->params->get('ratings_show_frontpage') == 1):
					echo JLayoutHelper::render(
						'layouts.content.ratings_albums',
						array('params' => $this->params, 'item' => $this->item, 'column' => true),
						JPATH_COMPONENT
					);
				endif; ?>
			</div>
			<div class="right-col span9">
				<?php if (!$this->user->guest): ?>
					<div class="mark-links">
						<?php if ($this->params->get('link_favorite') == 1): ?>
							<div class="favorite">
								<?php if ($this->item->favorite == 1): ?>
									<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&task=movies.favorite&action=delete&Itemid=' . $this->itemid . '&id=' . $this->item->id); ?>" class="cmd-favorite delete" data-ka-msg-place=".mark-links"><?php echo JText::_('COM_KA_REMOVEFROM_FAVORITE'); ?></a>
								<?php else: ?>
									<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&task=movies.favorite&action=add&Itemid=' . $this->itemid . '&id=' . $this->item->id); ?>" class="cmd-favorite add" data-ka-msg-place=".mark-links"><?php echo JText::_('COM_KA_ADDTO_FAVORITE'); ?></a>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>
					<div class="clear"></div>
				<?php endif; ?>
				<div class="movie-info">
					<div>
						<span class="f-col"><?php echo JText::_('COM_KA_YEAR'); ?></span>
						<span class="s-col"><a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movies&task=search&filters[movies][year]=' . $this->item->year . '&Itemid=' . $this->itemid); ?>" rel="nofollow"><?php echo $this->item->year; ?></a></span>
					</div>
					<?php if (!empty($this->item->countries)): ?>
						<div>
							<span class="f-col"><?php echo count($this->item->countries) > 1 ? JText::_('COM_KA_COUNTRIES') : JText::_('COM_KA_COUNTRY'); ?></span>
							<span class="s-col">
								<?php $cn_count = count($this->item->countries);
								for ($i = 0, $n = $cn_count; $i < $n; $i++):
									$country = $this->item->countries[$i]; ?>
									<img src="media/com_kinoarhiv/images/icons/countries/<?php echo $country->code; ?>.png" class="ui-icon-country" alt="<?php echo $country->name; ?>"/>
									<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movies&filters[movies][country]=' . $country->id . '&Itemid=' . $this->itemid); ?>" title="<?php echo $country->name; ?>" rel="nofollow"><?php echo $country->name; ?></a><?php echo ($i + 1 == $n) ? '' : ', '; ?>
								<?php endfor; ?>
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
										<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $name['id'] . '&Itemid=' . $this->itemid); ?>" title="<?php echo $name['name']; ?>" <?php echo $itemprop; ?>><?php echo $name['name']; ?></a><?php if ($i + 1 == $n): ?><?php if ($n < $person['total_items']): ?>,&nbsp;
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
										<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $name['id'] . '&Itemid=' . $this->itemid); ?>" title="<?php echo $name['name']; ?>" itemprop="actor"><?php echo $name['name']; ?></a><?php if ($i + 1 == $n): ?><?php if ($n < $person['total_items']): ?>,&nbsp;
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
									<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movies&filters[movies][genre][]=' . $genre->id . '&Itemid=' . $this->itemid); ?>" title="<?php echo $genre->name; ?>" itemprop="genre" rel="nofollow"><?php echo $genre->name; ?></a><?php echo ($i + 1 == $n) ? '' : ', '; ?>
								<?php endfor; ?>
							</span>
						</div>
					<?php endif; ?>
					<?php if (count($this->item->releases) > 0):
						foreach ($this->item->releases as $release): ?>
							<div>
								<span class="f-col"><?php echo JText::sprintf('COM_KA_RELEASES_MEDIATYPE', JHtml::_('string.truncate', $release->media_type, 14)); ?></span>
								<span class="s-col">
									<?php echo JHtml::_('date', $release->release_date, JText::_('DATE_FORMAT_LC3')); ?><?php if (!empty($release->company_name)): ?>, <?php echo $release->company_name; ?><?php endif; ?><?php echo ($release->country != '') ? ', ' . $release->country : ''; ?>&nbsp;<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=release&id=' . $release->movie_id . '&Itemid=' . $this->itemid); ?>#row-<?php echo $release->id; ?>" title="<?php echo JText::_('COM_KA_READMORE'); ?>" class="hasTooltip ui-icon-next"></a>
								</span>
							</div>
						<?php endforeach;
					endif; ?>
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
							<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movies&filters[movies][tags]=' . $tag->tag_id . '&Itemid=' . $this->itemid); ?>" class="label label-info uk-badge tags" title="<?php echo $tag->title; ?>"><?php echo $tag->title; ?></a>
						<?php endforeach; ?>
						</span>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="clear"></div>
		<?php
		echo JLayoutHelper::render('layouts.content.tracklist',
			array(
				'tracks'  => $this->item->tracks,
				'guest'   => $this->user->get('guest'),
				//'albumID' => $album->id
			),
			JPATH_COMPONENT
		);
		?>

		<div class="buy">
			<p><?php echo $this->item->buy_urls; ?></p>
		</div>

		<?php
		echo JLayoutHelper::render('layouts.content.votes_album',
			array(
				'params'  => $this->params,
				'item'    => $this->item,
				'guest'   => $this->user->get('guest'),
				'itemid'  => $this->itemid
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
		if ($totalTracks > 0 && $this->params->get('watch_trailer') == 1)
		{
			$player_layout = ($this->params->get('player_type') == '-1') ? 'trailer' : 'trailer_' . $this->params->get('player_type');

			try
			{
				echo $this->loadTemplate($player_layout);
			}
			catch (Exception $e)
			{
				KAComponentHelper::eventLog(JText::sprintf('COM_KA_PLAYER_FOLDER_NOT_FOUND', $player_layout));
				echo $this->loadTemplate('trailer');
			}
		}
		?>

		<?php if (!empty($this->item->desc)): ?>
			<br />
			<div class="desc" id="desc">
				<div class="accordion-group">
					<div class="accordion-heading">
						<a class="accordion-toggle" data-toggle="collapse" data-parent="#desc" href="#showTechDescription"><?php echo JText::_('COM_KA_TECH'); ?></a>
					</div>
					<div id="showTechDescription" class="accordion-body collapse">
						<div class="accordion-inner"><p><?php echo $this->item->desc; ?></p></div>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<?php echo $this->item->event->afterDisplayContent; ?>
		<?php if ($this->params->get('show_reviews') == 1):
			echo $this->loadTemplate('reviews');
		endif; ?>
	</article>
</div>
