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

JHtml::_('stylesheet', 'media/com_kinoarhiv/css/colorbox.css');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery.colorbox.min.js');
KAComponentHelper::getScriptLanguage('jquery.colorbox-', 'media/com_kinoarhiv/js/i18n/colorbox');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery.lazyload.min.js');
?>
<div class="content name photo">
	<?php if ($this->params->get('use_alphabet') == 1):
		echo JLayoutHelper::render('layouts.navigation.alphabet', array('params' => $this->params, 'itemid' => $this->itemid), JPATH_COMPONENT);
	endif; ?>

	<article class="uk-article">
		<?php
		echo JLayoutHelper::render('layouts.navigation.name_item_header', array('item' => $this->item, 'itemid' => $this->itemid), JPATH_COMPONENT);
		echo $this->loadTemplate('tabs'); ?>

		<div class="photos-list">
			<?php if (count($this->items) > 0):
				foreach ($this->items as $photo): ?>
					<div class="thumb">
						<div class="item">
							<a href="<?php echo $photo->image; ?>" title="<?php echo $this->item->title; ?>" rel="photos">
								<img data-original="<?php echo $photo->th_image; ?>" class="lazy" border="0" alt="<?php echo JText::_('COM_KA_PHOTO_ALT') . $this->item->title; ?>" width="<?php echo $photo->th_image_width; ?>" height="<?php echo $photo->th_image_height; ?>"/>
							</a>
						</div>
						<ul>
							<li class="size"><?php echo $photo->dimension; ?></li>
						</ul>
					</div>
				<?php endforeach; ?>
				<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm" style="clear: both;">
					<div class="pagination bottom">
						<?php echo $this->pagination->getPagesLinks(); ?><br/>
						<?php echo $this->pagination->getResultsCounter(); ?><br/>
						<label for="limit" class="element-invisible"><?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?></label>
						<?php echo $this->pagination->getLimitBox(); ?>
						<input type="hidden" name="limitstart" value=""/>
						<input type="hidden" name="task" value=""/>

						<div class="clearfix"></div>
					</div>
				</form>
				<div style="clear: both;">&nbsp;</div>
			<?php else: ?>
				<div><?php echo KAComponentHelper::showMsg(JText::_('COM_KA_NO_ITEMS')); ?></div>
			<?php endif; ?>
		</div>
	</article>
</div>
