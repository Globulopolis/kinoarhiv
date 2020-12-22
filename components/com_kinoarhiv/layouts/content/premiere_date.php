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
$item = $displayData['item'];

if (property_exists($item, 'premiere_date') && (!empty($item->premiere_date) && $item->premiere_date != '0000-00-00 00:00:00')): ?>
	<div class="premiere-date">
		<?php if (property_exists($item, 'country')): ?>
			<div class="premiere-country">
				<?php echo ($item->country == '') ? JText::_('COM_KA_PREMIERE_DATE_WORLDWIDE') : JText::sprintf(JText::_('COM_KA_PREMIERE_DATE_LOC'), $item->country); ?>
			</div>
		<?php endif; ?>

		<div class="date">
			<?php echo JHtml::_('date', $item->premiere_date, 'd'); ?>
		</div>
		<div class="month">
			<?php echo JHtml::_('date', $item->premiere_date, 'F'); ?> <?php echo JHtml::_('date', $item->premiere_date, 'Y'); ?>
		</div>

		<?php if (property_exists($item, 'company_name')): ?>
			<div class="vendor">
				<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=premieres&vendor=' . $item->vendor_id . '&Itemid=' . $displayData['itemid']); ?>"><?php echo $this->escape($item->company_name); ?></a>
			</div>
		<?php endif; ?>
	</div>
<?php endif;
