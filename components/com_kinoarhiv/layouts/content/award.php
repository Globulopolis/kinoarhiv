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

/** @var array $displayData */
$params = $displayData['params'];
$item   = $displayData['item'];
?>
<div class="item">
	<h2 class="item-title uk-article-title title" itemprop="headline">
		<?php if ($params->get('link_titles')): ?>
		<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=awards&id=' . $item->id); ?>"
		   itemprop="url"><?php echo $this->escape($item->title); ?></a>
		<?php else: ?>
			<?php echo $this->escape($item->title); ?>
		<?php endif; ?>
	</h2>
	<?php echo $item->event->afterDisplayTitle; ?>
	<?php echo $item->event->beforeDisplayContent; ?>

	<div class="award-desc"><?php echo $item->desc; ?></div>

	<?php echo $item->event->afterDisplayContent; ?>
</div>
