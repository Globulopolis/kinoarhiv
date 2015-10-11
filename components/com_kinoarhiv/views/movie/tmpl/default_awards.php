<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('_JEXEC') or die;
?>
<div class="content movie awards">
	<?php if ($this->params->get('use_alphabet') == 1):
		echo JLayoutHelper::render('layouts/navigation/alphabet', array('params' => $this->params, 'itemid' => $this->itemid), JPATH_COMPONENT);
	endif; ?>

	<article class="uk-article">
		<?php
		echo JLayoutHelper::render('layouts/navigation/movie_item_header', array('params' => $this->params, 'item' => $this->item, 'itemid' => $this->itemid), JPATH_COMPONENT);
		echo $this->item->event->afterDisplayTitle;
		echo $this->loadTemplate('tabs');
		echo $this->item->event->beforeDisplayContent; ?>

		<div class="awards-list">
			<?php if (count($this->item->awards) > 0):
				foreach ($this->item->awards as $award): ?>
					<div class="well uk-panel uk-panel-box">
						<h5 class="uk-panel-title">
							<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=awards&id=' . $award->id . '&Itemid=' . $this->itemid); ?>"><?php echo $this->escape($award->aw_title); ?></a><?php echo ($award->year != '0000') ? ', ' . $award->year : ''; ?>
						</h5>
						<?php echo $award->desc; ?>
					</div>
				<?php endforeach; ?>
			<?php else: ?>
				<div><?php echo KAComponentHelper::showMsg(JText::sprintf('COM_KA_NO_AWARDS', JText::_('COM_KA_MOVIE'))); ?></div>
			<?php endif; ?>
		</div>
	</article>
	<?php echo $this->item->event->afterDisplayContent; ?>
</div>
