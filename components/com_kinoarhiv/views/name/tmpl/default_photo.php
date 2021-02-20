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
			'layouts.navigation.name_alphabet',
			array('params' => $this->params),
			JPATH_COMPONENT
		);
	endif; ?>

	<article class="uk-article">
		<?php
		echo JLayoutHelper::render(
			'layouts.navigation.name_item_header',
			array(
				'params' => $this->params,
				'item'   => $this->item,
				'itemid' => $this->itemid,
				'guest'  => $this->user->get('guest'),
				'url'    => 'index.php?option=com_kinoarhiv&view=name&id=' . $this->item->id . '&Itemid=' . $this->itemid
			),
			JPATH_COMPONENT
		);

		echo JLayoutHelper::render('layouts.navigation.name_item_tabs',
			array('item' => $this->item, 'params' => $this->params, 'page' => $this->page),
			JPATH_COMPONENT
		);
		?>

		<div class="photos-list">
			<?php if (count($this->items) > 0):
				foreach ($this->items as $photo): ?>
					<div class="thumb">
						<div class="item">
							<a href="<?php echo $photo->photo->photo; ?>" title="<?php echo $this->item->title; ?>" rel="photos">
								<img data-original="<?php echo $photo->photo->photoThumb; ?>" class="lazy"
									 alt="<?php echo JText::_('COM_KA_PHOTO_ALT') . $this->item->title; ?>"
									 width="<?php echo $photo->photo->photoThumbWidth; ?>"
									 height="<?php echo $photo->photo->photoThumbHeight; ?>"/>
							</a>
						</div>
						<ul>
							<li class="size"><?php echo $photo->dimension; ?></li>
						</ul>
					</div>
				<?php endforeach; ?>
				<div style="clear: both;">&nbsp;</div>
				<?php
				echo JLayoutHelper::render('layouts.navigation.pagination',
					array('params' => $this->params, 'pagination' => $this->pagination, 'limitstart' => true, 'task' => true),
					JPATH_COMPONENT
				);
				?>
			<?php else: ?>
				<div><?php echo KAComponentHelper::showMsg(JText::_('COM_KA_NO_ITEMS')); ?></div>
			<?php endif; ?>
		</div>
	</article>
</div>
