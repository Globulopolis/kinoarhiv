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

JHtml::_('script', 'media/com_kinoarhiv/js/jquery.plugin.min.js');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery.more.min.js');
?>
<script type="text/javascript">
	jQuery(document).ready(function ($) {
		$('.actor-desc').more({
			length: <?php echo $this->params->get('limit_text'); ?>,
			moreText: '<?php echo JText::_('COM_KA_READ_MORE'); ?>',
			lessText: '<?php echo JText::_('COM_KA_READ_LESS'); ?>'
		});
	});
</script>
<div class="ka-content">
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

		<div class="crew-hrefs">
			<?php if (isset($this->item->careers['crew']) && count($this->item->careers['crew']) > 0):
				for ($i = 0, $n = count($this->item->careers['crew']); $i < $n; $i++):
					$career = $this->item->careers['crew'][$i]; ?>
					<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=cast&id=' . $this->item->id) . '#' . JFilterOutput::stringURLSafe($career); ?>"><?php echo $career; ?></a><?php echo ($i + 1 == $n) ? '' : ', '; ?>
				<?php endfor;
			endif; ?>
		</div>
		<div class="cast-hrefs">
			<?php if (!empty($this->item->careers['cast'])): ?>
				<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=cast&id=' . $this->item->id) . '#' . JFilterOutput::stringURLSafe($this->item->careers['cast']); ?>"><?php echo $this->item->careers['cast']; ?></a><?php endif; ?><?php if (!empty($this->item->careers['dub'])): ?>,
				<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=cast&id=' . $this->item->id) . '#' . JFilterOutput::stringURLSafe($this->item->careers['dub']); ?>"><?php echo $this->item->careers['dub']; ?></a><?php endif; ?>
		</div>


		<div class="cast-info">
			<?php if (count($this->item->crew) > 0): ?>
			<div class="item-crew">

				<?php foreach ($this->item->crew as $row): ?>
				<div class="rows-container">
					<div class="corner-all header header-small">
						<span id="<?php echo JFilterOutput::stringURLSafe($row['career']); ?>"></span><?php echo $row['career']; ?>
					</div>
					<div class="item-rows">

						<?php foreach ($row['items'] as $key => $name):
							$actorName = KAContentHelper::formatItemTitle($name['name'], $name['latin_name']); ?>
						<div class="item-row">
							<div class="item-col1">
								<div class="actor-photo">
									<span class="number"><?php echo $key + 1; ?>. </span>
									<span class="photo">
										<img src="<?php echo $name['photo']->photoThumb; ?>" width="64"
											 class="<?php echo $name['gender'] ? 'm' : 'f'; ?>"/>
									</span>
								</div>
							</div>
							<div class="item-col2">
								<div class="actor-info">
									<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $name['id'] . '&Itemid=' . $this->namesItemid); ?>" title="<?php echo $actorName; ?>"><?php echo $actorName; ?></a><br/>
									<span class="actor-role"><?php echo $name['role']; ?></span>
									<div class="actor-desc"><?php echo $name['desc']; ?></div>
								</div>
							</div>
						</div>
						<?php endforeach; ?>

					</div>
				</div>
				<?php endforeach; ?>

			</div>
			<?php endif; ?>

			<?php if (count($this->item->cast) > 0): ?>
			<div class="item-cast">

				<?php foreach ($this->item->cast as $row): ?>
				<div class="rows-container">
					<div class="corner-all header header-small">
						<span id="<?php echo JFilterOutput::stringURLSafe($row['career']); ?>"></span><?php echo $row['career']; ?>

						<?php if (!empty($this->item->dub)): ?>
						<span class="dub hidden-phone"><?php echo JText::_('COM_KA_CAST_DUB'); ?></span>
						<?php endif; ?>
					</div>
					<div class="item-rows">

						<?php foreach ($row['items'] as $key => $name):
							$actorName = KAContentHelper::formatItemTitle($name['name'], $name['latin_name']);
							$dubActorName = KAContentHelper::formatItemTitle($name['dub_name'], $name['dub_latin_name']); ?>
						<div class="item-row">
							<div class="item-col1">
								<div class="actor-photo">
									<span class="number"><?php echo $key + 1; ?>. </span>
									<span class="photo">
										<img src="<?php echo $name['photo']->photoThumb; ?>" width="64"
											 class="<?php echo $name['gender'] ? 'm' : 'f'; ?>"/>
									</span>
								</div>
							</div>
							<div class="item-col2">
								<div class="actor-info">
									<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $name['id'] . '&Itemid=' . $this->namesItemid); ?>" title="<?php echo $actorName; ?>"><?php echo $actorName; ?></a><br/>
									<span class="actor-role"><?php echo $name['role']; ?></span>
									<div class="actor-desc"><?php echo $name['desc']; ?></div>
								</div>
							</div>
							<div class="item-col3 hidden-phone">
								<?php if (!empty($name['dub_id'])): ?>
									<div class="actor-dub-photo">
										<span class="photo">
											<img src="<?php echo $name['photoDub']->photoThumb; ?>" width="64"
												 class="<?php echo $name['dub_gender'] ? 'm' : 'f'; ?>"/>
										</span>
									</div>
									<div class="actor-dub-info">
										<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $name['dub_id'] . '&Itemid=' . $this->namesItemid); ?>" title="<?php echo $dubActorName; ?>"><?php echo $dubActorName; ?></a>
									</div>

								<?php endif; ?>
							</div>
						</div>
						<?php endforeach; ?>

					</div>
				</div>
				<?php endforeach; ?>

			</div>
			<?php endif; ?>

			<?php if (count($this->item->dub) > 0): ?>
			<div class="item-dub">

				<?php foreach ($this->item->dub as $row): ?>
				<div class="rows-container">
					<div class="corner-all header header-small">
						<span id="<?php echo JFilterOutput::stringURLSafe($row['career']); ?>"></span><?php echo $row['career']; ?>
					</div>
					<div class="item-rows">

						<?php foreach ($row['items'] as $key => $name):
							$actorName = KAContentHelper::formatItemTitle($name['name'], $name['latin_name']); ?>
						<div class="item-row">
							<div class="item-col1">
								<div class="actor-photo">
									<span class="number"><?php echo $key + 1; ?>. </span>
									<span class="photo">
										<img src="<?php echo $name['photo']->photoThumb; ?>" width="64"
											 class="<?php echo $name['gender'] ? 'm' : 'f'; ?>"/>
									</span>
								</div>
							</div>
							<div class="item-col2">
								<div class="actor-info">
									<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $name['id'] . '&Itemid=' . $this->namesItemid); ?>" title="<?php echo $actorName; ?>"><?php echo $actorName; ?></a><br/>
									<span class="actor-role"><?php echo $name['role']; ?></span>
									<div class="actor-desc"><?php echo $name['desc']; ?></div>
								</div>
							</div>
						</div>
						<?php endforeach; ?>

					</div>
				</div>
				<?php endforeach; ?>

			</div>
			<?php endif; ?>
		</div>
	</article>
</div>
