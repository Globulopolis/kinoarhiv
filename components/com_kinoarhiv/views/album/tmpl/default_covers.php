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

		<div class="scr-list">
			<?php if (count($this->files) > 0):
				foreach ($this->files as $type => $item): ?>
					<div class="container-fluid">
						<div class="cover-type"><?php echo JText::_('COM_KA_ALBUM_TAB_COVERS_' . $type); ?></div>

						<?php foreach ($item as $image): ?>
						<div class="thumb">
							<div class="item">
								<a href="<?php echo $image->cover; ?>" rel="covers"
								   title="<?php echo $this->escape(KAContentHelper::formatItemTitle($this->item->title, '', $this->item->year)); ?>">
									<img data-original="<?php echo $image->coverThumb; ?>"
										 width="<?php echo $image->coverThumbWidth; ?>"
										 height="<?php echo $image->coverThumbHeight; ?>" class="lazy"
										 alt="<?php echo JText::_('COM_KA_SCR_ALT') . $this->escape($this->item->title); ?>"/>
								</a>
							</div>
							<ul>
								<li class="size"><?php echo $image->dimension; ?></li>
							</ul>
						</div>
						<?php endforeach; ?>

					</div><br />
				<?php endforeach; ?>
			<?php else: ?>
				<div><?php echo KAComponentHelper::showMsg(JText::_('COM_KA_NO_ITEMS')); ?></div>
			<?php endif; ?>
		</div>
	</article>
	<?php echo $this->item->event->afterDisplayContent; ?>
</div>
