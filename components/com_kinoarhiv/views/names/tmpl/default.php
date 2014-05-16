<?php defined('_JEXEC') or die; ?>
<script src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/jquery.colorbox-min.js" type="text/javascript"></script>
<script src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/i18n/colorbox/jquery.colorbox-<?php echo substr(JFactory::getLanguage()->getTag(), 0, 2); ?>.js" type="text/javascript"></script>
<script src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/ui.aurora.min.js" type="text/javascript"></script>
<script src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/jquery.lazyload.min.js" type="text/javascript"></script>
<script type="text/javascript">
//<![CDATA[
	jQuery(document).ready(function($){
		function showMsg(selector, text) {
			$(selector).aurora({
				text: text,
				placement: 'after',
				button: 'close',
				button_title: '[<?php echo JText::_('COM_KA_CLOSE'); ?>]'
			});
		}

		$('.hasTip, .hasTooltip').attr('data-uk-tooltip', '');
		$('img.lazy').lazyload({ threshold: 200 });
		$('a.zoom-icon').colorbox({
			title: function(){
				return $(this).closest('.poster').find('img').attr('alt');
			},
			maxHeight: '90%'
		});

		<?php if (!$this->user->guest && $this->params->get('link_favorite') == 1): ?>
		$('.fav a').click(function(e){
			e.preventDefault();
			var _this = $(this);

			$.ajax({
				url: _this.attr('href') + '&format=raw'
			}).done(function(response){
				if (response.success) {
					_this.text(response.text);
					_this.attr('href', response.url);
					if (_this.hasClass('delete')) {
						_this.removeClass('delete').addClass('add');
					} else {
						_this.removeClass('add').addClass('delete');
					}
					showMsg(_this.closest('.middle-nav'), response.message);
				} else {
					showMsg(_this.closest('.middle-nav'), '<?php echo JText::_('JERROR_AN_ERROR_HAS_OCCURRED'); ?>');
				}
			}).fail(function(xhr, status, error){
				showMsg(_this.closest('.middle-nav'), error);
			});
		});
		<?php endif; ?>
	});
//]]>
</script>
<div class="uk-article ka-content">
	<?php if ($this->params->get('use_alphabet') == 1):
		echo $this->loadTemplate('alphabet');
	endif; ?>

	<?php if (count($this->items['names']) > 0):
	if ($this->params->get('pagevan_top') == 1 && $this->pagination->total >= $this->pagination->limit): ?>
		<div class="pagination top">
			<?php echo $this->pagination->getPagesLinks(); ?>
		</div>
	<?php endif;

		foreach ($this->items['names'] as $item): ?>
		<article class="item" data-permalink="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&id='.$item->id.'&Itemid='.$this->itemid); ?>">
			<header>
				<h1 class="uk-article-title title title-small">
					<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&id='.$item->id.'&Itemid='.$this->itemid); ?>" class="brand" title="<?php echo $this->escape($item->title); ?>"><?php echo $this->escape($item->title); ?><?php echo $item->date_range; ?></a>
				</h1>
			</header>
			<div class="content clearfix ui-helper-clearfix">
				<div>
					<div class="poster<?php echo $item->y_poster; ?>">
						<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&id='.$item->id.'&Itemid='.$this->itemid); ?>" title="<?php echo $this->escape($item->title); ?>">
							<div><img data-original="<?php echo $item->poster; ?>" class="lazy" border="0" alt="<?php echo JText::_('COM_KA_PHOTO_ALT').$this->escape($item->title); ?>" width="<?php echo $item->poster_width; ?>" height="<?php echo $item->poster_height; ?>" /></div>
						</a>
						<?php if ($item->y_poster != ''): ?><div class="overlay-poster">
							<a href="<?php echo $item->big_poster; ?>" title="<?php echo JText::_('COM_KA_PHOTO_ZOOM'); ?>" class="zoom-icon hasTooltip"><div></div></a>
						</div><?php endif; ?>
					</div>
					<div class="introtext">
						<div class="middle-nav clearfix">
							<?php if (!$this->user->guest && $this->params->get('link_favorite') == 1): ?>
							<p class="fav">
								<?php if ($item->favorite == 1): ?>
								<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&task=favorite&view=names&action=delete&Itemid='.$this->itemid.'&id='.$item->id); ?>" class="delete"><?php echo JText::_('COM_KA_REMOVEFROM_FAVORITE'); ?></a>
								<?php else: ?>
								<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&task=favorite&view=names&action=add&Itemid='.$this->itemid.'&id='.$item->id); ?>" class="add"><?php echo JText::_('COM_KA_ADDTO_FAVORITE'); ?></a>
								<?php endif; ?>
							</p>
							<?php endif; ?>
						</div>
						<?php if ($item->career != ''): ?>
						<div class="name-career"><?php echo JText::_('COM_KA_NAMES_CAREER'); ?><?php echo JString::strtolower($item->career); ?></div>
						<?php endif; ?>
						<?php if (!empty($item->birthplace) || !empty($item->country)): ?>
						<div class="name-bd">
							<?php echo JText::_('COM_KA_NAMES_BIRTHPLACE'); ?>
							<?php echo !empty($item->birthplace) ? $item->birthplace.', ': ''; ?><img class="ui-icon-country" border="0" alt="<?php echo $item->country; ?>" src="components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/images/icons/countries/<?php echo $item->code; ?>.png"> <?php echo $item->country; ?>
						</div>
						<?php endif; ?>
						<?php if ($item->genres != ''): ?>
						<div class="name-genres"><?php echo JText::_('COM_KA_GENRES'); ?>: <?php echo JString::strtolower($item->genres); ?></div>
						<?php endif; ?>
						<div class="separator"></div>
						<div class="tabs breadcrumb">
							<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&page=wallpapers&id='.$item->id.'&Itemid='.$this->itemid); ?>" class="tab-wallpp"><?php echo JText::_('COM_KA_NAMES_TAB_WALLPP'); ?></a>
							<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&page=photos&id='.$item->id.'&Itemid='.$this->itemid); ?>" class="tab-posters"><?php echo JText::_('COM_KA_NAMES_TAB_PHOTO'); ?></a>
							<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&page=awards&id='.$item->id.'&Itemid='.$this->itemid); ?>" class="tab-awards"><?php echo JText::_('COM_KA_NAMES_TAB_AWARDS'); ?></a>
						</div>
					</div>
				</div>
				<div class="links">
					<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&id='.$item->id.'&Itemid='.$this->itemid); ?>" class="btn btn-default uk-button readmore-link hasTip" title="<?php echo $item->title; ?>"><?php echo JText::_('COM_KA_READMORE'); ?><span class="icon-chevron-right"></span></a>
				</div>
			</div>
		</article>
		<?php endforeach; ?>
		<?php if ($this->params->get('pagevan_bottom') == 1 && $this->pagination->total >= $this->pagination->limit): ?>
			<div class="pagination bottom">
				<form action="<?php echo htmlspecialchars(JURI::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm" style="clear: both;" autocomplete="off">
				<?php echo $this->pagination->getPagesLinks(); ?><br />
				<?php echo $this->pagination->getResultsCounter(); ?>
				<?php echo $this->pagination->getLimitBox(); ?>
				</form>
			</div>
		<?php endif;
	else: ?>
		<br /><div><?php echo GlobalHelper::showMsg(JText::_('COM_KA_NO_ITEMS')); ?></div>
	<?php endif; ?>
</div>
