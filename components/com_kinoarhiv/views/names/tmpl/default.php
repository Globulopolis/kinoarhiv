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
<script type="text/javascript">
	//<![CDATA[
	jQuery(document).ready(function ($) {
		<?php if ($this->params->get('search_names_enable') == 1 && is_object($this->filtersData) && $this->filtersData->exists('names')): ?>
		$('#searchForm #search_form_content').load('<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=search&task=names&format=raw&' . JSession::getFormToken() . '=1', false); ?>', <?php echo json_encode(array('form' => $this->filtersData)); ?>, function (response, status, xhr) {
			if (status == 'error') {
				Aurora.message([{text: 'Sorry but there was an error: ' + xhr.status + ' ' + xhr.statusText, type: 'error'}], '#system-message-container', {replace: true});
				return false;
			}

			$(this).removeClass('loading');
		});
		<?php endif; ?>
	});
	//]]>
</script>
<div class="uk-article ka-content">
	<?php if ($this->params->get('use_alphabet') == 1):
		echo JLayoutHelper::render('layouts.navigation.alphabet', array('params' => $this->params, 'itemid' => $this->itemid), JPATH_COMPONENT);
	endif; ?>

	<?php if ($this->params->get('search_names_enable') == 1 && is_object($this->filtersData) && $this->filtersData->exists('names')): ?>
		<div class="accordion" id="searchForm">
			<div class="accordion-group">
				<div class="accordion-heading">
					<a class="accordion-toggle" data-toggle="collapse" data-parent="#searchForm" href="#toggleSearchForm"><strong><?php echo JText::_('COM_KA_SEARCH_ADV'); ?></strong></a>
				</div>
				<div id="toggleSearchForm" class="accordion-body collapse">
					<div class="accordion-inner">
						<div id="search_form_content" class="loading"></div>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<?php if (count($this->items) > 0):
		if ($this->params->get('search_names_enable') == 1 && is_object($this->filtersData) && $this->filtersData->exists('names')):
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
				<header>
					<h1 class="uk-article-title title title-small">
						<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $item->id . '&Itemid=' . $this->itemid); ?>" class="brand" title="<?php echo $this->escape($item->title); ?>"><?php echo $this->escape($item->title); ?><?php echo $item->date_range; ?></a>
					</h1>
				</header>
				<div class="content content-list clearfix">
					<div>
						<div class="poster">
							<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $item->id . '&Itemid=' . $this->itemid); ?>" title="<?php echo $this->escape($item->title); ?>">
								<div>
									<img data-original="<?php echo $item->poster; ?>" class="lazy" border="0" alt="<?php echo JText::_('COM_KA_PHOTO_ALT') . $this->escape($item->title); ?>" width="<?php echo $item->poster_width; ?>" height="<?php echo $item->poster_height; ?>"/>
								</div>
							</a>
						</div>
						<div class="introtext">
							<div class="middle-nav clearfix">
								<?php if (!$this->user->guest && $this->params->get('link_favorite') == 1): ?>
									<p class="favorite">
										<?php if ($item->favorite == 1): ?>
											<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=names&task=names.favorite&action=delete&Itemid=' . $this->itemid . '&id=' . $item->id, false); ?>" class="cmd-favorite delete" data-ka-msg-place=".middle-nav"><?php echo JText::_('COM_KA_REMOVEFROM_FAVORITE'); ?></a>
										<?php else: ?>
											<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=names&task=names.favorite&action=add&Itemid=' . $this->itemid . '&id=' . $item->id, false); ?>" class="cmd-favorite add" data-ka-msg-place=".middle-nav"><?php echo JText::_('COM_KA_ADDTO_FAVORITE'); ?></a>
										<?php endif; ?>
									</p>
								<?php endif; ?>
							</div>

							<?php if ($item->career != ''): ?>
								<div class="name-career"><?php echo JText::_('COM_KA_NAMES_CAREER'); ?><?php echo StringHelper::strtolower($item->career); ?></div>
							<?php endif; ?>

							<?php if (!empty($item->birthplace) || !empty($item->country)): ?>
								<div class="name-bd">
									<?php echo JText::_('COM_KA_NAMES_BIRTHPLACE'); ?>
									<?php echo !empty($item->birthplace) ? $item->birthplace . ', ' : ''; ?>
									<img class="ui-icon-country" border="0" alt="<?php echo $item->country; ?>" src="media/com_kinoarhiv/images/icons/countries/<?php echo $item->code; ?>.png"> <?php echo $item->country; ?>
								</div>
							<?php endif; ?>
							<?php if ($item->genres != ''): ?>
								<div class="name-genres"><?php echo JText::_('COM_KA_GENRES'); ?>: <?php echo StringHelper::strtolower($item->genres); ?></div>
							<?php endif; ?>
							<div class="separator"></div>
							<div class="tabs">
								<?php if (($item->attribs->tab_name_wallpp == '' && $this->params->get('tab_name_wallpp') == 1) || $item->attribs->tab_name_wallpp == 1): ?>
									<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&page=wallpapers&id=' . $item->id . '&Itemid=' . $this->itemid); ?>" class="tab-wallpp"><?php echo JText::_('COM_KA_NAMES_TAB_WALLPAPERS'); ?></a>
								<?php endif; ?>

								<?php if (($item->attribs->tab_name_photos == '' && $this->params->get('tab_name_photos') == 1) || $item->attribs->tab_name_photos == 1): ?>
									<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&page=photos&id=' . $item->id . '&Itemid=' . $this->itemid); ?>" class="tab-posters"><?php echo JText::_('COM_KA_NAMES_TAB_PHOTOS'); ?></a>
								<?php endif; ?>

								<?php if (($item->attribs->tab_name_awards == '' && $this->params->get('tab_name_awards') == 1) || $item->attribs->tab_name_awards == 1): ?>
									<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&page=awards&id=' . $item->id . '&Itemid=' . $this->itemid); ?>" class="tab-awards"><?php echo JText::_('COM_KA_NAMES_TAB_AWARDS'); ?></a>
								<?php endif; ?>
							</div>
						</div>
					</div>
					<div class="links">
						<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $item->id . '&Itemid=' . $this->itemid); ?>" class="btn btn-default uk-button readmore-link hasTooltip" title="<?php echo $item->title; ?>"><?php echo JText::_('COM_KA_READMORE'); ?>
							<span class="icon-chevron-right"></span></a>
					</div>
				</div>
			</article>
		<?php endforeach; ?>
		<?php if ($this->params->get('pagevan_bottom') == 1): ?>
		<div class="pagination bottom">
			<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm" style="clear: both;" autocomplete="off">
				<?php echo $this->pagination->getPagesLinks(); ?><br/>
				<?php echo $this->pagination->getResultsCounter(); ?>
				<?php echo $this->pagination->getLimitBox(); ?>
			</form>
		</div>
	<?php endif;
	else: ?>
		<br/>
		<div><?php echo ($this->params->get('search_names_enable') == 1 && $this->filtersData->exists('names')) ? JText::sprintf('COM_KA_SEARCH_PERSON_N_RESULTS', 0) : KAComponentHelper::showMsg(JText::_('COM_KA_NO_ITEMS')); ?></div>
	<?php endif; ?>
</div>
