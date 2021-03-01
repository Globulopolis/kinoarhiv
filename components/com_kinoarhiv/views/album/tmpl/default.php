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

use Joomla\Utilities\ArrayHelper;

$totalGenres = count($this->item->genres);
$totalTracks = count($this->item->tracks);
?>
<div class="ka-content" itemscope itemtype="https://schema.org/MusicAlbum">
	<meta content="<?php echo $totalTracks; ?>" itemprop="numTracks" />
<?php if ($totalGenres > 0):
	$genres = ArrayHelper::getColumn($this->item->genres, 'name'); ?>
	<meta content="<?php echo implode(',', $genres); ?>" itemprop="genre" />
<?php endif; ?>

	<?php if ($this->params->get('use_alphabet') == 1):
		echo JLayoutHelper::render(
			'layouts.navigation.album_alphabet',
			array('url' => 'index.php?option=com_kinoarhiv&view=albums&content=albums&Itemid=' . $this->itemid, 'params' => $this->params),
			JPATH_COMPONENT
		);
	endif; ?>

	<article class="uk-article item">
		<?php
		echo JLayoutHelper::render(
			'layouts.navigation.album_item_header',
			array(
				'params' => $this->params,
				'item'   => $this->item,
				'itemid' => $this->itemid,
				'guest'  => $this->user->get('guest'),
				'url'    => 'index.php?option=com_kinoarhiv&view=album&id=' . $this->item->id . '&Itemid=' . $this->itemid
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
					<img itemprop="image" src="<?php echo $this->item->cover->coverThumb; ?>"
						 alt="<?php echo JText::_('COM_KA_ARTWORK_ALT') . $this->escape($this->item->title); ?>"
						 width="<?php echo $this->item->cover->coverThumbWidth; ?>"
						 height="<?php echo $this->item->cover->coverThumbHeight; ?>" />
				</div>
			</div>
			<div class="right-col">
				<div class="album-info">
					<div class="item-info-row">
						<span class="f-col"><?php echo JText::_('COM_KA_YEAR'); ?></span>
						<span class="s-col">
							<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=albums&content=albums&albums[year]=' . $this->item->year . '&Itemid=' . $this->itemid); ?>" rel="nofollow"><?php echo $this->item->year; ?></a>
						</span>
					</div>
					<div class="item-info-row">
						<span class="f-col"><?php echo JText::_('COM_KA_MUSIC_LABEL'); ?></span>
						<span class="s-col">
							<?php $totalLabels = count($this->item->vendors);
							for ($i = 0, $n = $totalLabels; $i < $n; $i++):
								$vendor = $this->item->vendors[$i]; ?>
								<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=albums&content=albums&albums[vendor]=' . $vendor->id . '&Itemid=' . $this->itemid); ?>" title="<?php echo $vendor->company_name; ?>" rel="nofollow"><?php echo $vendor->company_name; ?></a><?php echo ($i + 1 == $n) ? '' : ', '; ?>
							<?php endfor; ?>
						</span>
					</div>
					<?php if (!empty($this->item->countries)): ?>
						<div class="item-info-row">
							<span class="f-col">
								<?php echo count($this->item->countries) > 1 ? JText::_('COM_KA_COUNTRIES') : JText::_('COM_KA_COUNTRY'); ?>
							</span>
							<span class="s-col">
								<?php $totalCountries = count($this->item->countries);
								for ($i = 0, $n = $totalCountries; $i < $n; $i++):
									$country = $this->item->countries[$i]; ?>
									<img src="media/com_kinoarhiv/images/icons/countries/<?php echo $country->code; ?>.png"
										 class="ui-icon-country" alt="<?php echo $country->name; ?>"/>
									<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=albums&filters[albums][country]=' . $country->id . '&Itemid=' . $this->itemid); ?>" title="<?php echo $country->name; ?>" rel="nofollow"><?php echo $country->name; ?></a><?php echo ($i + 1 == $n) ? '' : ', '; ?>
								<?php endfor; ?>
							</span>
						</div>
					<?php endif; ?>
					<?php if (isset($this->item->crew) && count($this->item->crew) > 0):
						foreach ($this->item->crew as $person): ?>
							<div class="item-info-row">
								<span class="f-col"><?php echo $person['career']; ?></span>
								<span class="s-col">
									<?php $totalCrewItems = count($person['items']);
									for ($i = 0, $n = $totalCrewItems; $i < $n; $i++):
										$name = $person['items'][$i]; ?>
										<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $name['id'] . '&Itemid=' . $this->namesItemid); ?>" title="<?php echo $name['name']; ?>"><?php echo $name['name']; ?></a><?php if ($i + 1 == $n): ?><?php if ($n < $person['total_items']): ?>,&nbsp;
										<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=album&page=crew&id=' . $this->item->id . '&Itemid=' . $this->itemid); ?>#<?php echo JFilterOutput::stringURLSafe($person['career']); ?>" title="<?php echo JText::_('COM_KA_READMORE'); ?>" class="hasTooltip ui-icon-next"></a><?php endif; ?>
									<?php else:
										echo ', ';
									endif; ?>
									<?php endfor; ?>
								</span>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
					<?php if (isset($this->item->genres) && count($this->item->genres) > 0): ?>
						<div class="item-info-row">
							<span class="f-col"><?php echo JText::_('COM_KA_GENRE'); ?></span>
							<span class="s-col">
								<?php
								for ($i = 0, $n = $totalGenres; $i < $n; $i++):
									$genre = $this->item->genres[$i]; ?>
									<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=albums&content=albums&albums[genre][0]=' . $genre->id . '&Itemid=' . $this->itemid); ?>" title="<?php echo $genre->name; ?>" itemprop="genre" rel="nofollow"><?php echo $genre->name; ?></a><?php echo ($i + 1 == $n) ? '' : ', '; ?>
								<?php endfor; ?>
							</span>
						</div>
					<?php endif; ?>
					<?php if (count($this->item->releases) > 0):
						foreach ($this->item->releases as $release): ?>
							<div class="item-info-row">
								<span class="f-col">
									<?php echo JText::sprintf('COM_KA_RELEASES_MEDIATYPE', JHtml::_('string.truncate', $release->media_type, 14)); ?>
								</span>
								<span class="s-col">
									<?php echo JHtml::_('date', $release->release_date, JText::_('DATE_FORMAT_LC3')); ?><?php if (!empty($release->company_name)): ?>, <?php echo $release->company_name; ?><?php endif; ?><?php echo ($release->country != '') ? ', ' . $release->country : ''; ?>&nbsp;<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=release&id=' . $release->item_id . '&Itemid=' . $this->releasesItemid); ?>#row-<?php echo $release->id; ?>" title="<?php echo JText::_('COM_KA_READMORE'); ?>" class="hasTooltip ui-icon-next"></a>
								</span>
							</div>
						<?php endforeach;
					endif; ?>
					<div class="item-info-row">
						<span class="f-col"><?php echo JText::_('COM_KA_LENGTH'); ?></span>
						<span class="s-col"><?php echo $this->item->minutes; ?><?php echo JText::_('COM_KA_LENGTH_MINUTES'); ?>
							| <?php echo $this->item->length; ?></span>
					</div>
					<?php if (
						(($this->item->attribs->show_tags === '' && $this->params->get('show_tags') == 1) || $this->item->attribs->show_tags == 1)
						&& !empty($this->item->tags->itemTags)): ?>
						<div class="item-info-row">
							<span class="f-col"><?php echo JText::_('JTAG'); ?></span>
							<span class="s-col"><?php echo $this->item->tagLayout->render($this->item->tags->itemTags); ?></span>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="clear"></div>
		<div class="buy">
			<p><?php echo $this->item->buy_urls; ?></p>
		</div>
		<br/>

		<?php if ($totalTracks > 0 && $this->params->get('watch_trailer') == 1):
			if (!$this->user->get('guest')):
				$playerLayout = ($this->params->get('player_type') == '-1') ? 'player' : 'player_' . $this->params->get('player_type');

				echo JLayoutHelper::render('layouts.content.audio_' . $playerLayout,
					array(
						'id' => $this->item->id,
						'tracks' => json_encode($this->item->playlist),
						'total' => count($this->item->playlist)),
					JPATH_COMPONENT
				);
			endif;

			echo JLayoutHelper::render('layouts.content.tracklist',
				array(
					'params' => $this->params,
					'item'   => $this->item,
					'guest'  => $this->user->get('guest')
				),
				JPATH_COMPONENT
			);
		endif; ?>

		<?php if ($this->params->get('ratings_show_frontpage') == 1):
			echo JLayoutHelper::render('layouts.content.votes_album',
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
		endif; ?>

		<?php if (!empty($this->item->desc)): ?>
			<div class="desc" id="desc">
				<div class="accordion-group">
					<div class="accordion-heading">
						<a class="accordion-toggle" data-toggle="collapse" data-parent="#desc"
						   href="#showTechDescription"><?php echo JText::_('JGLOBAL_DESCRIPTION'); ?></a>
					</div>
					<div id="showTechDescription" class="accordion-body collapse">
						<div class="accordion-inner"><?php echo $this->item->desc; ?></div>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<?php echo $this->item->event->afterDisplayContent; ?>
		<?php echo $this->loadTemplate('reviews'); ?>
	</article>
</div>
