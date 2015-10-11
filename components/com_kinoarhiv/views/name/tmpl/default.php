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
?>
<script src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/ui.aurora.min.js" type="text/javascript"></script>
<script type="text/javascript">
	//<![CDATA[
	function showMsg(selector, text) {
		jQuery(selector).aurora({
			text: text,
			button: 'close',
			button_title: '[<?php echo JText::_('COM_KA_CLOSE'); ?>]'
		});
	}

	jQuery(document).ready(function ($) {
		<?php if ($this->params->get('link_favorite') == 1): ?>
		$('.fav a').click(function (e) {
			e.preventDefault();
			var _this = $(this);

			$.ajax({
				url: _this.attr('href') + '&format=raw'
			}).done(function (response) {
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
			}).fail(function (xhr, status, error) {
				showMsg($('.mark-links'), error);
			});
		});
		<?php endif; ?>
	});
	//]]>
</script>
<div class="content name">
	<?php if ($this->params->get('use_alphabet') == 1):
		echo JLayoutHelper::render('layouts/navigation/alphabet', array('params' => $this->params, 'itemid' => $this->itemid), JPATH_COMPONENT);
	endif; ?>

	<article class="uk-article">
		<?php
		echo JLayoutHelper::render('layouts/navigation/name_item_header', array('item' => $this->item, 'itemid' => $this->itemid), JPATH_COMPONENT);
		echo $this->loadTemplate('tabs'); ?>

		<div class="info">
			<div class="left-col">
				<div class="poster">
					<div style="text-align: center;">
						<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&tab=photo&id=' . $this->item->id . '&Itemid=' . $this->itemid); ?>" title="<?php echo $this->item->title; ?>"><img src="<?php echo $this->item->poster; ?>" border="0" alt="<?php echo JText::_('COM_KA_PHOTO_ALT') . $this->item->title; ?>"/></a>
					</div>
				</div>
			</div>
			<div class="right-col">
				<?php if (!$this->user->guest): ?>
					<div class="mark-links">
						<?php if ($this->params->get('link_favorite') == 1): ?>
							<div class="fav">
								<?php if ($this->item->favorite == 1): ?>
									<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=names&task=favorite&action=delete&Itemid=' . $this->itemid . '&id=' . $this->item->id); ?>" class="delete"><?php echo JText::_('COM_KA_REMOVEFROM_FAVORITE'); ?></a>
								<?php else: ?>
									<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=names&task=favorite&action=add&Itemid=' . $this->itemid . '&id=' . $this->item->id); ?>" class="add"><?php echo JText::_('COM_KA_ADDTO_FAVORITE'); ?></a>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>
					<div class="clear"></div>
				<?php endif; ?>
				<div class="name-info">
					<?php if ($this->item->date_of_birth_raw != '0000-00-00'): ?>
						<div>
							<span class="f-col"><?php echo JText::_('COM_KA_NAMES_DATE_OF_BIRTH'); ?></span>
						<span class="s-col">
							<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=names&filters[names][birthday]=' . $this->item->date_of_birth_raw . '&Itemid=' . $this->itemid); ?>">
								<?php echo JHtml::_('date', $this->item->date_of_birth_raw, JText::_('DATE_FORMAT_LC3')); ?></a>,
							<?php if ($this->item->zodiac !== ''): ?>
								<img src="components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/images/icons/zodiac/<?php echo $this->item->zodiac; ?>.png" border="0"/> <?php echo JText::_('COM_KA_NAMES_ZODIAC_' . JString::strtoupper($this->item->zodiac)); ?>,
							<?php endif; ?>
							<?php echo $this->item->date_of_birth_interval_str; ?>
						</span>
						</div>
					<?php endif; ?>

					<?php if ($this->item->date_of_death_raw != '0000-00-00'): ?>
						<div>
							<span class="f-col"><?php echo JText::_('COM_KA_NAMES_DATE_OF_DEATH'); ?></span>
							<span class="s-col"><?php echo JHtml::_('date', $this->item->date_of_death_raw, JText::_('DATE_FORMAT_LC3')); ?></span>
						</div>
					<?php endif; ?>
					<?php if (!empty($this->item->birthplace) || !empty($this->item->country)): ?>
						<div>
							<span class="f-col"><?php echo JText::_('COM_KA_NAMES_BIRTHPLACE_1'); ?></span>
						<span class="s-col">
							<?php echo !empty($this->item->birthplace) ? $this->item->birthplace : ''; ?><?php if (!empty($this->item->birthplace) && !empty($this->item->country)): ?>, <?php endif; ?><?php if (!empty($this->item->country)): ?>
								<img class="ui-icon-country" border="0" alt="<?php echo $this->item->country; ?>" src="components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/images/icons/countries/<?php echo $this->item->code; ?>.png">
								<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=names&filters[names][birthcountry]=' . $this->item->birthcountry . '&Itemid=' . $this->itemid); ?>"><?php echo $this->item->country; ?></a><?php endif; ?>
						</span>
						</div>
					<?php endif; ?>
					<?php if (!empty($this->item->height)): ?>
						<div>
							<span class="f-col"><?php echo JText::_('COM_KA_NAMES_HEIGHT'); ?></span>
							<span class="s-col"><?php echo $this->item->height; ?></span>
						</div>
					<?php endif; ?>
					<?php if (!empty($this->item->career)): ?>
						<div>
							<span class="f-col"><?php echo JText::_('COM_KA_FILTERS_NAMES_CAREER_PLACEHOLDER'); ?></span>
						<span class="s-col">
							<?php for ($i = 0, $n = count($this->item->career); $i < $n; $i++):
								$career = $this->item->career[$i]; ?>
								<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=names&filters[names][amplua]=' . $career->id); ?>"><?php echo JString::strtolower($career->title); ?></a><?php echo $i + 1 == $n ? '' : ', '; ?>
							<?php endfor; ?>
						</span>
						</div>
					<?php endif; ?>
					<?php if (!empty($this->item->genres)): ?>
						<div>
							<span class="f-col"><?php echo JText::_('COM_KA_GENRES'); ?></span>
						<span class="s-col">
							<?php for ($i = 0, $n = count($this->item->genres); $i < $n; $i++):
								$genre = $this->item->genres[$i]; ?>
								<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=names&filters[names][genre]=' . $genre->id); ?>"><?php echo JString::strtolower($genre->name); ?></a><?php echo $i + 1 == $n ? '' : ', '; ?>
							<?php endfor; ?>
						</span>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<div class="clear"></div>
		<?php if (!empty($this->item->desc)): ?>
			<div class="known">
				<div class="ui-corner-all ui-widget-header header-small"><?php echo JText::_('COM_KA_KNOWN'); ?></div>
				<div class="content"><?php echo $this->item->desc; ?></div>
			</div>
		<?php endif; ?>
		<div class="clear"></div>

		<?php if (count($this->item->movies) > 0): ?>
			<div class="movies-list">
				<div class="ui-corner-all ui-widget-header header-small"><?php echo JText::_('COM_KA_NAMES_FILMOGRAPHY'); ?></div>
				<div class="content">
					<?php $mi = 0;
					foreach ($this->item->movies as $movie):
						$mi++; ?>
						<div class="item">
							<div class="number"><?php echo $mi; ?>.</div>
							<div class="data">
								<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&id=' . $movie->id . '&Itemid=' . $this->item->itemid); ?>" target="_blank"><?php echo $movie->title; ?><?php echo ($movie->year != '0000') ? '&nbsp;(' . $movie->year . ')' : ''; ?></a>

								<div class="role"><?php echo $movie->role; ?></div>
							</div>
							<div class="clear"></div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

	</article>
</div>
