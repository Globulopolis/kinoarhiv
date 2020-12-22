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
$direction = $displayData['lang']->isRtl() ? 'left' : 'right';
?>
<p class="readmore">
	<a class="btn uk-button" href="<?php echo $displayData['link']; ?>" itemprop="url"
	   aria-label="<?php echo JText::_('COM_KA_READMORE'); ?> <?php echo htmlspecialchars($displayData['item']->title, ENT_QUOTES, 'UTF-8'); ?>">
		<?php echo '<span class="icon-chevron-' . $direction . '" aria-hidden="true"></span>'; ?>
		<?php echo JText::sprintf('COM_KA_READMORE'); ?>
	</a>
</p>
