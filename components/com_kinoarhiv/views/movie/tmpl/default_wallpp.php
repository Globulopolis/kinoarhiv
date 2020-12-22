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

JHtml::_('stylesheet', 'media/com_kinoarhiv/css/colorbox.css');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery.colorbox.min.js');
KAComponentHelper::getScriptLanguage('jquery.colorbox-', 'media/com_kinoarhiv/js/i18n/colorbox');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery.lazyload.min.js');
?>
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

		<div class="wp-list">
			<?php if (count($this->items) > 0): ?>
				<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post"
					  name="adminForm" id="adminForm" style="clear: both;">
					<div class="list-filter">
						<?php echo $this->filters['dimensions.list']; ?>
					</div>
					<div style="clear: both;"></div>
					<?php foreach ($this->items as $wp): ?>
						<div class="thumb">
							<div class="item">
								<a href="<?php echo $wp->image; ?>" title="<?php echo $this->escape(KAContentHelper::formatItemTitle($this->item->title, '', $this->item->year)); ?>" rel="wp">
									<img data-original="<?php echo $wp->th_image; ?>" width="<?php echo $wp->th_image_width; ?>" height="<?php echo $wp->th_image_height; ?>" class="lazy" alt="<?php echo JText::_('COM_KA_WP_ALT') . $this->escape($this->item->title); ?>"/>
								</a>
							</div>
							<ul>
								<li class="size"><?php echo $wp->dimension; ?></li>
							</ul>
						</div>
					<?php endforeach; ?>
					<div style="clear: both;"></div>
					<?php
					echo JLayoutHelper::render('layouts.navigation.pagination',
						array('params' => $this->params, 'pagination' => $this->pagination, 'limitstart' => true, 'task' => true, 'form' => false),
						JPATH_COMPONENT
					);
					?>
				</form>
			<?php else: ?>
				<div><?php echo KAComponentHelper::showMsg(JText::_('COM_KA_NO_ITEMS')); ?></div>
			<?php endif; ?>
		</div>
	</article>
	<?php echo $this->item->event->afterDisplayContent; ?>
</div>
