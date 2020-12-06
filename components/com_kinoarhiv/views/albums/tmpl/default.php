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

JHtml::_('script', 'media/com_kinoarhiv/js/jquery.rateit.min.js');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery.lazyload.min.js');
?>
<script type="text/javascript">
	jQuery(document).ready(function($){
		<?php if ($this->params->get('search_albums_enable') == 1 && is_object($this->filtersData) && $this->filtersData->exists('albums')): ?>
		$('#searchForm #search_form_content').load('<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=search&task=albums&format=raw&' . JSession::getFormToken() . '=1', false); ?>', <?php echo json_encode(array('form' => $this->filtersData)); ?>, function (response, status, xhr) {
			if (status == 'error') {
				Aurora.message([{text: 'Sorry but there was an error: ' + xhr.status + ' ' + xhr.statusText, type: 'error'}], '#system-message-container', {replace: true});
				return false;
			}

			$(this).removeClass('loading');
		});
		<?php endif; ?>
	});
</script>
<div class="uk-article ka-content ka-albums">
	<?php if ($this->params->get('use_alphabet') == 1):
		echo JLayoutHelper::render('layouts.navigation.alphabet', array('params' => $this->params, 'itemid' => $this->itemid), JPATH_COMPONENT);
	endif; ?>

	<?php if ($this->params->get('show_feed_link', 1)):
		$link = 'index.php?option=com_kinoarhiv&view=albums&format=feed&Itemid=' . $this->itemid . '&limitstart='; ?>
		<div class="feed-link">
			<a href="<?php echo JRoute::_($link . '&type=rss'); ?>" title="RSS" rel="noindex">RSS</a>
			<a href="<?php echo JRoute::_($link . '&type=atom'); ?>" title="Atom" rel="noindex">Atom</a>
		</div>
	<?php endif; ?>

	<?php if ($this->params->get('search_albums_enable') == 1 && is_object($this->filtersData) && $this->filtersData->exists('albums')): ?>
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
		if ($this->params->get('search_albums_enable') == 1 && is_object($this->filtersData) && $this->filtersData->exists('albums')):
			$plural = $this->lang->getPluralSuffixes($this->pagination->total);
			echo '<br />' . JText::sprintf('COM_KA_SEARCH_VIDEO_N_RESULTS_' . $plural[0], $this->pagination->total);
		endif; ?>

		<?php if ($this->params->get('pagevan_top') == 1): ?>
		<div class="pagination top">
			<?php echo $this->pagination->getPagesLinks(); ?>
		</div>
		<?php endif;
			echo $this->loadTemplate((string) $this->menuParams->get('page_type'));
		?>
		<?php if ($this->params->get('pagevan_bottom') == 1): ?>
		<div class="pagination bottom">
			<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" name="adminForm"
				  id="adminForm" style="clear: both;" autocomplete="off">
				<?php echo $this->pagination->getPagesLinks(); ?><br/>
				<?php echo $this->pagination->getResultsCounter(); ?>
				<?php echo $this->pagination->getLimitBox(); ?>
			</form>
		</div>
	<?php endif;
	else: ?>
		<br/>
		<div><?php echo ($this->params->get('search_albums_enable') == 1 && $this->filtersData->exists('albums')) ? JText::sprintf('COM_KA_SEARCH_VIDEO_N_RESULTS', 0) : KAComponentHelper::showMsg(JText::_('COM_KA_NO_ITEMS')); ?></div>
	<?php endif; ?>
</div>