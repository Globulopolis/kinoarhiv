<?php defined('_JEXEC') or die; ?>
<script src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/jquery-ui.min.js" type="text/javascript"></script>
<script src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/jquery.colorbox-min.js" type="text/javascript"></script>
<script src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/i18n/colorbox/jquery.colorbox-<?php echo substr(JFactory::getLanguage()->getTag(), 0, 2); ?>.js" type="text/javascript"></script>
<script src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/jquery.lazyload.min.js" type="text/javascript"></script>
<script type="text/javascript">
//<![CDATA[
	jQuery(document).ready(function($){
		$('img.lazy').lazyload();
		$('.scr-list .item a').colorbox({ maxHeight: '90%', maxWidth: '90%' });
	});
//]]>
</script>
<div class="content movie screenshots">
	<article>
		<header>
			<h1 class="title">
				<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&id='.$this->item->id.'&Itemid='.$this->itemid); ?>" class="brand" title="<?php echo $this->escape($this->item->title.$this->item->year_str); ?>"><?php echo $this->escape($this->item->title.$this->item->year_str); ?></a>
			</h1>
		</header>
		<?php echo $this->item->event->afterDisplayTitle; ?>
		<?php echo $this->loadTemplate('tabs'); ?>
		<?php echo $this->item->event->beforeDisplayContent; ?>
		<div class="scr-list">
			<?php if (count($this->items) > 0):
				foreach ($this->items as $scr): ?>
				<div class="thumb">
					<div class="item">
						<a href="<?php echo $scr->image; ?>" title="<?php echo $this->escape($this->item->title.$this->item->year_str); ?>" rel="scrsh">
							<img data-original="<?php echo $scr->th_image; ?>" width="<?php echo $scr->th_image_width; ?>" height="<?php echo $scr->th_image_height; ?>" class="lazy" border="0" alt="<?php echo JText::_('COM_KA_SCR_ALT').$this->escape($this->item->title); ?>" />
						</a>
					</div>
					<ul>
						<li class="size"><?php echo $scr->dimension; ?></li>
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
	<?php echo $this->item->event->afterDisplayContent; ?>
</div>