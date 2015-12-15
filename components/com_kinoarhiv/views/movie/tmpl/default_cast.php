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

JHtml::_('script', 'components/com_kinoarhiv/assets/js/jquery.plugin.min.js');
JHtml::_('script', 'components/com_kinoarhiv/assets/js/jquery.more.min.js');
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
<div class="content movie cast">
	<?php if ($this->params->get('use_alphabet') == 1):
		echo JLayoutHelper::render('layouts.navigation.alphabet', array('params' => $this->params, 'itemid' => $this->itemid), JPATH_COMPONENT);
	endif; ?>

	<article class="uk-article">
		<?php
		echo JLayoutHelper::render('layouts.navigation.movie_item_header', array('params' => $this->params, 'item' => $this->item, 'itemid' => $this->itemid), JPATH_COMPONENT);
		echo $this->item->event->afterDisplayTitle;
		echo $this->loadTemplate('tabs');
		echo $this->item->event->beforeDisplayContent; ?>

		<div class="info">
			<div>
				<?php if (isset($this->item->careers['crew']) && count($this->item->careers['crew']) > 0):
					for ($i = 0, $n = count($this->item->careers['crew']); $i < $n; $i++):
						$career = $this->item->careers['crew'][$i]; ?>
						<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=cast&id=' . $this->item->id) . '#' . JFilterOutput::stringURLSafe($career); ?>"><?php echo $career; ?></a><?php if ($i + 1 == $n)
					{
					}
					else
					{
						echo ', ';
					} ?>
					<?php endfor;
				endif; ?>
			</div>
			<div>
				<?php if (!empty($this->item->careers['cast'])): ?>
					<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=cast&id=' . $this->item->id) . '#' . JFilterOutput::stringURLSafe($this->item->careers['cast']); ?>"><?php echo $this->item->careers['cast']; ?></a><?php endif; ?><?php if (!empty($this->item->careers['dub'])): ?>,
					<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=cast&id=' . $this->item->id) . '#' . JFilterOutput::stringURLSafe($this->item->careers['dub']); ?>"><?php echo $this->item->careers['dub']; ?></a><?php endif; ?>
			</div>
			<br/>

			<?php if (count($this->item->crew) > 0):
				foreach ($this->item->crew as $row): ?>
					<div class="ui-corner-all ui-widget-header header-small">
						<a name="<?php echo JFilterOutput::stringURLSafe($row['career']); ?>"></a><?php echo $row['career']; ?>
					</div>
					<div class="content">
						<?php foreach ($row['items'] as $key => $name):
							$actor_name = KAContentHelper::formatItemTitle($name['name'], $name['latin_name']); ?>
							<div class="cast-row">
								<div class="cast-row-col-left fullwidth">
									<div class="actor-photo">
										<span class="number"><?php echo $key + 1; ?>. </span>
										<span class="photo"><img src="<?php echo $name['poster']; ?>" width="64" border="0" class="<?php echo $name['gender'] ? 'm' : 'f'; ?>"/></span>
									</div>
									<div class="actor-info">
										<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $name['id'] . '&Itemid=' . $this->itemid); ?>" title="<?php echo $actor_name; ?>"><?php echo $actor_name; ?></a><br/>
										<span class="actor-role"><?php echo $name['role']; ?></span>

										<div class="actor-desc"><?php echo $name['desc']; ?></div>
									</div>
								</div>
								<div class="clear"></div>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endforeach;
			endif; ?>

			<?php if (count($this->item->cast) > 0):
				foreach ($this->item->cast as $row): ?>
					<div class="ui-corner-all ui-widget-header header-small">
						<a name="<?php echo JFilterOutput::stringURLSafe($row['career']); ?>"></a><?php echo $row['career']; ?><?php if (!empty($this->item->dub)): ?>
							<span class="dub"><?php echo JText::_('COM_KA_CAST_DUB'); ?></span><?php endif; ?></div>
					<div class="content">
						<?php foreach ($row['items'] as $key => $name):
							$actor_name = KAContentHelper::formatItemTitle($name['name'], $name['latin_name']);
							$dub_actor_name = KAContentHelper::formatItemTitle($name['dub_name'], $name['dub_latin_name']); ?>
							<div class="cast-row">
								<div class="cast-row-col-left">
									<div class="actor-photo">
										<span class="number"><?php echo $key + 1; ?>. </span>
										<span class="photo"><img src="<?php echo $name['poster']; ?>" width="64" border="0" class="<?php echo $name['gender'] ? 'm' : 'f'; ?>"/></span>
									</div>
									<div class="actor-info">
										<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $name['id'] . '&Itemid=' . $this->itemid); ?>" title="<?php echo $actor_name; ?>"><?php echo $actor_name; ?></a><br/>
										<span class="actor-role"><?php echo $name['role']; ?></span>

										<div class="actor-desc"><?php echo $name['desc']; ?></div>
									</div>
								</div>
								<div class="cast-row-col-right">
									<?php if (!empty($name['dub_id'])): ?>
										<div class="actor-dub-photo">
											<span class="photo"><img src="<?php echo $name['dub_url_photo']; ?>" width="64" border="0" class="<?php echo $name['dub_gender'] ? 'm' : 'f'; ?>"/></span>
										</div>
										<div class="actor-dub-info">
											<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $name['dub_id'] . '&Itemid=' . $this->itemid); ?>" title="<?php echo $dub_actor_name; ?>"><?php echo $dub_actor_name; ?></a>
										</div>

									<?php endif; ?>
								</div>
								<div class="clear"></div>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endforeach;
			endif; ?>

			<?php if (count($this->item->dub) > 0):
				foreach ($this->item->dub as $row): ?>
					<div class="ui-corner-all ui-widget-header header-small">
						<a name="<?php echo JFilterOutput::stringURLSafe($row['career']); ?>"></a><?php echo $row['career']; ?>
					</div>
					<div class="content">
						<?php foreach ($row['items'] as $key => $name):
							$actor_name = KAContentHelper::formatItemTitle($name['name'], $name['latin_name']); ?>
							<div class="cast-row">
								<div class="cast-row-col-left fullwidth">
									<div class="actor-photo">
										<span class="number"><?php echo $key + 1; ?>. </span>
										<span class="photo"><img src="<?php echo $name['poster']; ?>" width="64" border="0" class="<?php echo $name['gender'] ? 'm' : 'f'; ?>"/></span>
									</div>
									<div class="actor-info">
										<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $name['id'] . '&Itemid=' . $this->itemid); ?>" title="<?php echo $actor_name; ?>"><?php echo $actor_name; ?></a><br/>
										<span class="actor-role"><?php echo $name['role']; ?></span>

										<div class="actor-desc"><?php echo $name['desc']; ?></div>
									</div>
								</div>
								<div class="clear"></div>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endforeach;
			endif; ?>

		</div>
	</article>
</div>
