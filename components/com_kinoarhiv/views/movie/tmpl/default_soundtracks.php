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

JHtml::_('bootstrap.loadcss');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery.rateit.min.js');
?>
<div class="ka-content">
	<?php if ($this->params->get('use_alphabet') == 1):
		echo JLayoutHelper::render('layouts.navigation.alphabet', array('params' => $this->params, 'itemid' => $this->itemid), JPATH_COMPONENT);
	endif; ?>

	<article class="uk-article">
		<?php
		echo JLayoutHelper::render(
			'layouts.navigation.movie_item_header',
			array('params' => $this->params, 'item' => $this->item, 'itemid' => $this->itemid),
			JPATH_COMPONENT
		);
		echo $this->item->event->afterDisplayTitle;
		echo $this->loadTemplate('tabs');
		echo $this->item->event->beforeDisplayContent; ?>

		<div class="snd-list">
			<?php if (!empty($this->item->albums)): ?>
			<ul class="media-list">

			<?php foreach ($this->item->albums as $album):
					$composer = KAContentHelper::formatItemTitle($album->name, $album->latin_name);
					$cover_size = explode('x', $this->params->get('music_covers_size'));
			?>

				<li class="media">
					<a class="pull-left" href="#"><img src="media/com_kinoarhiv/images/themes/default/no_album_cover.png" class="media-object" width="<?php echo (int) $cover_size[0]; ?>" height="<?php echo (int) $cover_size[1]; ?>" /></a>
					<div class="media-body">
						<h4 class="media-heading album-title"><?php echo $album->title; ?></h4>
						<span class="album-info">
							<?php if (!empty($composer)): ?>
							<span class="album-composer"><?php echo $composer; ?></span>
							<?php endif; ?>
							<?php if (!empty($album->year) && $album->year != '0000'): ?>
							<span class="album-year">(<?php echo $album->year; ?>)</span>
							<?php endif; ?>
						</span>
						<?php if (($album->attribs['allow_votes'] == '' && $this->params->get('allow_votes') == 1) || $album->attribs['allow_votes'] == 1): ?>
							<?php if (!$this->user->get('guest') && $this->params->get('allow_votes') == 1): ?>
								<?php if ($this->params->get('ratings_show_local') == 1): ?>
									<div class="clear"></div>
									<div class="rate">
										<strong><?php echo JText::_('COM_KA_MUSIC_RATE'); ?></strong><br/>
										<select id="rate_field_<?php echo $album->id; ?>" autocomplete="off">
											<?php for ($i = 0, $n = (int) $this->params->get('vote_summ_num') + 1; $i < $n; $i++): ?>
												<option value="<?php echo $i; ?>"<?php echo ($i == round($album->rate_loc_label)) ? ' selected="selected"' : ''; ?>><?php echo $i; ?></option>
											<?php endfor; ?>
										</select>

										<div class="rateit" data-rateit-value="<?php echo round($album->rate_loc_label); ?>" data-rateit-backingfld="#rate_field_<?php echo $album->id; ?>"></div>
										&nbsp;<span><?php echo $album->rate_loc_label; ?></span>

										<div class="my_votes" style="<?php echo ($album->my_vote == 0) ? 'display: none;' : ''; ?>">
											<div class="my_vote"><?php echo JText::sprintf('COM_KA_RATE_MY', $album->my_vote, (int) $this->params->get('vote_summ_num')); ?>
												&nbsp;<span class="small">(<?php echo JHtml::_('date', $album->_datetime, JText::_('DATE_FORMAT_LC3')); ?>
													)</span></div>
											<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=votes&Itemid=' . $this->itemid); ?>" class="small"><?php echo JText::_('COM_KA_RATE_MY_ALL'); ?></a>
										</div>
									</div>
								<?php endif; ?>
							<?php else: ?>
								<?php if ($this->params->get('ratings_show_local') == 1): ?>
									<div class="clear"></div>
									<div class="rate">
										<strong><?php echo JText::_('COM_KA_MUSIC_RATE'); ?></strong><br/>

										<div class="rateit" data-rateit-value="<?php echo $album->rate_loc_c; ?>" data-rateit-min="0" data-rateit-max="<?php echo (int) $this->params->get('vote_summ_num'); ?>" data-rateit-ispreset="true" data-rateit-readonly="true"></div>
										&nbsp;<?php echo $album->rate_loc_label; ?>

										<?php if ($this->params->get('allow_votes') == 1): ?>
											<div><?php echo KAComponentHelper::showMsg(JText::sprintf(JText::_('COM_KA_VOTES_AUTHREQUIRED'), '<a href="' . JRoute::_('index.php?option=com_users&view=registration') . '">' . JText::_('COM_KA_REGISTER') . '</a>', '<a href="' . JRoute::_('index.php?option=com_users&view=login') . '">' . JText::_('COM_KA_LOGIN') . '</a>')); ?></div>
										<?php endif; ?>
									</div>
								<?php endif; ?>
							<?php endif; ?>
							<div class="clear"></div>
						<?php endif; ?>
					</div>
				</li>
			<?php endforeach; ?>
			</ul>
			<?php else: ?>
				<div><?php echo KAComponentHelper::showMsg(JText::_('COM_KA_NO_ITEMS')); ?></div>
			<?php endif; ?>
		</div>
	</article>
	<?php echo $this->item->event->afterDisplayContent; ?>
</div>
