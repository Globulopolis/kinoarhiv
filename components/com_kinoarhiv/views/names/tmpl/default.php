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

JHtml::_('script', 'media/com_kinoarhiv/js/jquery.lazyload.min.js');
?>
<div class="uk-article ka-content">
	<?php if ($this->params->get('use_alphabet') == 1):
		echo JLayoutHelper::render('layouts.navigation.name_alphabet', array('params' => $this->params), JPATH_COMPONENT);
	endif; ?>

	<?php if (count($this->items) > 0):
		if (is_object($this->filtersData) && $this->filtersData->exists('names')):
			$plural = $this->lang->getPluralSuffixes($this->pagination->total);
			echo '<br />' . JText::sprintf('COM_KA_SEARCH_PERSON_N_RESULTS_' . $plural[0], $this->pagination->total);
		endif; ?>

		<?php if ($this->params->get('pagevan_top') == 1): ?>
		<div class="pagination top">
			<?php echo $this->pagination->getPagesLinks(); ?>
		</div>
	<?php endif;

		foreach ($this->items as $item): ?>
			<article class="item" data-permalink="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $item->id . '&Itemid=' . $this->itemid); ?>">
				<?php
				echo JLayoutHelper::render(
					'layouts.navigation.name_item_header',
					array(
						'params' => $this->params,
						'item'   => $item,
						'itemid' => $this->itemid,
						'guest'  => $this->user->get('guest'),
						'url'    => 'index.php?option=com_kinoarhiv&view=name&id=' . $item->id . '&Itemid=' . $this->itemid
					),
					JPATH_COMPONENT
				);
				?>
				<div class="content content-list clearfix">
					<div>
						<div class="poster">
							<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $item->id . '&Itemid=' . $this->itemid); ?>" title="<?php echo $this->escape($item->title); ?>">
								<div>
									<img data-original="<?php echo $item->poster; ?>" class="lazy"
										 alt="<?php echo JText::_('COM_KA_PHOTO_ALT') . $this->escape($item->title); ?>"
										 width="<?php echo $item->poster_width; ?>" height="<?php echo $item->poster_height; ?>"/>
								</div>
							</a>
						</div>
						<div class="introtext">
							<!--<div class="middle-nav clearfix">
								<?php if (!$this->user->guest && $this->params->get('link_favorite') == 1): ?>
									<p class="favorite">
										<?php if ($item->favorite == 1): ?>
											<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=names&task=names.favorite&action=delete&Itemid=' . $this->itemid . '&id=' . $item->id, false); ?>" class="cmd-favorite delete" data-ka-msg-place=".middle-nav"><?php echo JText::_('COM_KA_REMOVEFROM_FAVORITE'); ?></a>
										<?php else: ?>
											<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=names&task=names.favorite&action=add&Itemid=' . $this->itemid . '&id=' . $item->id, false); ?>" class="cmd-favorite add" data-ka-msg-place=".middle-nav"><?php echo JText::_('COM_KA_ADDTO_FAVORITE'); ?></a>
										<?php endif; ?>
									</p>
								<?php endif; ?>
							</div>-->

							<?php if ($item->career != ''): ?>
								<div class="name-career"><?php echo JText::_('COM_KA_NAMES_CAREER'); ?><?php echo StringHelper::strtolower($item->career); ?></div>
							<?php endif; ?>

							<?php if (!empty($item->birthplace) || !empty($item->country)): ?>
								<div class="name-bd">
									<?php echo JText::_('COM_KA_NAMES_BIRTHPLACE'); ?>
									<?php echo !empty($item->birthplace) ? $item->birthplace . ', ' : ''; ?>
									<img class="ui-icon-country" alt="<?php echo $item->country; ?>"
										 src="media/com_kinoarhiv/images/icons/countries/<?php echo $item->code; ?>.png"> <?php echo $item->country; ?>
								</div>
							<?php endif; ?>
							<?php if ($item->genres != ''): ?>
								<div class="name-genres"><?php echo JText::_('COM_KA_GENRES'); ?>: <?php echo StringHelper::strtolower($item->genres); ?></div>
							<?php endif; ?>
							<div class="separator"></div>
							<?php
								echo JLayoutHelper::render('layouts.navigation.name_item_tabs',
									array('item' => $item, 'params' => $this->params, 'page' => ''),
									JPATH_COMPONENT
								);
							?>
						</div>
					</div>
					<div class="links">
						<?php
						echo JLayoutHelper::render('layouts.content.readmore',
							array(
								'link'   => JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $item->id . '&Itemid=' . $this->itemid),
								'item'   => $item,
								'params' => $this->params,
								'lang'   => $this->lang
							),
							JPATH_COMPONENT
						);
						?>
					</div>
				</div>
			</article>
		<?php endforeach; ?>

		<?php
		echo JLayoutHelper::render('layouts.navigation.pagination',
			array('params' => $this->params, 'pagination' => $this->pagination),
			JPATH_COMPONENT
		);
	else: ?>
		<br/>
		<div><?php echo $this->filtersData->exists('names') ? JText::sprintf('COM_KA_SEARCH_ADV_N_RESULTS', 0) : KAComponentHelper::showMsg(JText::_('COM_KA_NO_ITEMS')); ?></div>
	<?php endif; ?>
</div>
