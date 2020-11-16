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
$params   = $displayData['params'];
$item     = $displayData['item'];
$colClass = '';

// Display ratings vertical
if (isset($displayData['column']) && $displayData['column'])
{
	$colClass = 'rating-col';
}

if ($colClass == '')
{
	echo '<div class="separator"></div>';
}
else
{
	echo '<br />';
}
?>
<div class="ratings-frontpage <?php echo $colClass; ?>">
	<?php if ($colClass == ''): ?>
		<div class="local-rt<?php echo $item->rate_label_class; ?>">
			<div class="rateit" data-rateit-value="<?php echo $item->rate_c; ?>" data-rateit-min="0"
				 data-rateit-max="<?php echo (int) $params->get('vote_summ_num'); ?>" data-rateit-ispreset="true"
				 data-rateit-readonly="true">
			</div>
			&nbsp;<?php echo $item->rate_label; ?>
			<?php if (isset($item->total_votes)): ?><span class="total-votes small">(<?php echo $item->total_votes; ?>)</span><?php endif; ?>
		</div>
	<?php endif; ?>
</div>
