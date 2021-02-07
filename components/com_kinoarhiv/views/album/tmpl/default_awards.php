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

		<div class="awards-list">
			<?php if (count($this->item->awards) > 0):
				foreach ($this->item->awards as $award): ?>
					<div class="well uk-panel uk-panel-box">
						<h5 class="uk-panel-title">
							<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=awards&id=' . $award->id . '&Itemid=' . $this->awardsItemid); ?>"><?php echo $this->escape($award->aw_title); ?></a><?php echo (!empty($award->year) && $award->year != '0000') ? ', ' . $award->year : ''; ?>
						</h5>
						<?php echo $award->desc; ?>
					</div>
				<?php endforeach; ?>
			<?php else: ?>
				<div><?php echo KAComponentHelper::showMsg(JText::sprintf('COM_KA_NO_AWARDS', JText::_('COM_KA_ALBUM'))); ?></div>
			<?php endif; ?>
		</div>
	</article>
	<?php echo $this->item->event->afterDisplayContent; ?>
</div>
