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

JHtml::_('script', 'components/com_kinoarhiv/assets/js/jquery.colorbox.min.js');
KAComponentHelper::getScriptLanguage('jquery.colorbox-', 'js/i18n/colorbox');
JHtml::_('script', 'components/com_kinoarhiv/assets/js/jquery.lazyload.min.js');
?>
<div class="content movie wallpp">
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

		<div class="wp-list">
			<?php if (count($this->items) > 0): ?>
				<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm" style="clear: both;">
					<div class="list-filter">
						<?php echo $this->filters['dimensions.list']; ?>
					</div>
					<div style="clear: both;"></div>
					<?php foreach ($this->items as $wp): ?>
						<div class="thumb">
							<div class="item">
								<a href="<?php echo $wp->image; ?>" title="<?php echo $this->escape(KAContentHelper::formatItemTitle($this->item->title, '', $this->item->year)); ?>" rel="wp">
									<img data-original="<?php echo $wp->th_image; ?>" width="<?php echo $wp->th_image_width; ?>" height="<?php echo $wp->th_image_height; ?>" class="lazy" border="0" alt="<?php echo JText::_('COM_KA_WP_ALT') . $this->escape($this->item->title); ?>"/>
								</a>
							</div>
							<ul>
								<li class="size"><?php echo $wp->dimension; ?></li>
							</ul>
						</div>
					<?php endforeach; ?>
					<div style="clear: both;"></div>
					<div class="pagination bottom">
						<?php echo $this->pagination->getPagesLinks(); ?><br/>
						<?php echo $this->pagination->getResultsCounter(); ?><br/>
						<label for="limit" class="element-invisible"><?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?></label>
						<?php echo $this->pagination->getLimitBox(); ?>
						<input type="hidden" name="limitstart" value=""/>
						<input type="hidden" name="task" value=""/>

						<div class="clearfix"></div>
					</div>
					<div style="clear: both;">&nbsp;</div>
				</form>
			<?php else: ?>
				<div><?php echo KAComponentHelper::showMsg(JText::_('COM_KA_NO_ITEMS')); ?></div>
			<?php endif; ?>
		</div>
	</article>
	<?php echo $this->item->event->afterDisplayContent; ?>
</div>
