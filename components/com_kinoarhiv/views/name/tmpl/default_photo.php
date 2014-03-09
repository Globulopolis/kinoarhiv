<?php defined('_JEXEC') or die; ?>
<script src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/jquery-ui.min.js" type="text/javascript"></script>
<script src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/jquery.colorbox-min.js" type="text/javascript"></script>
<script src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/i18n/colorbox/jquery.colorbox-<?php echo substr(JFactory::getLanguage()->getTag(), 0, 2); ?>.js" type="text/javascript"></script>
<script src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/jquery.lazyload.min.js" type="text/javascript"></script>
<script type="text/javascript">
//<![CDATA[
	jQuery(document).ready(function($){
		$('img.lazy').lazyload({ threshold: 100 });
		$('.photos-list .item a').colorbox({ maxHeight: '90%' });
	});
//]]>
</script>
<div class="content name photo">
	<article>
		<header>
			<h1 class="title">
				<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&id='.$this->item->id.'&Itemid='.$this->itemid); ?>" class="brand" title="<?php echo $this->item->title; ?>"><?php echo $this->item->title; ?></a>
			</h1>
		</header>
		<?php echo $this->loadTemplate('tabs'); ?>
		<div class="photos-list">
			<?php if (count($this->items) > 0):
				foreach ($this->items as $photo): ?>
				<div class="thumb">
					<div class="item">
						<a href="<?php echo $photo->image; ?>" title="<?php echo $this->item->title; ?>" rel="photos">
							<img data-original="<?php echo $photo->th_image; ?>" class="lazy" border="0" alt="<?php echo JText::_('COM_KA_PHOTO_ALT').$this->item->title; ?>" width="<?php echo $photo->th_width; ?>" height="<?php echo $photo->th_height; ?>" />
						</a>
					</div>
					<ul>
						<li class="size"><?php echo $photo->dimension; ?></li>
					</ul>
				</div>
				<?php endforeach; ?>
				<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm" style="clear: both;">
					<div class="pagination bottom">
						<?php echo $this->pagination->getPagesLinks(); ?><br />
						<?php echo $this->pagination->getResultsCounter(); ?><br />
						<label for="limit" class="element-invisible"><?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?></label>
						<?php echo $this->pagination->getLimitBox(); ?>
						<input type="hidden" name="limitstart" value="" />
						<input type="hidden" name="task" value="" />
						<div class="clearfix"></div>
					</div>
				</form>
				<div style="clear: both;">&nbsp;</div>
			<?php else: ?>
			<div><?php echo GlobalHelper::showMsg(JText::_('COM_KA_NO_ITEMS')); ?></div>
			<?php endif; ?>
		</div>
	</article>
</div>