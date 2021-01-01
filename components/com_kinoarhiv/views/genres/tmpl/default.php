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

$cols  = (int) $this->params->get('genres_cols');
$total = count($this->items);
?>
<div class="uk-article ka-content">
	<div class="genre-list">
		<div class="row-fluid">

		<?php for ($i = 0; $i < $total; $i++):
			$item = $this->items[$i];

			if ($item->type == 1)
			{
				$url = 'index.php?option=com_kinoarhiv&view=albums&content=albums&albums[genre][]=' . $item->id . '&Itemid=' . $this->itemid;
			}
			else
			{
				$url = 'index.php?option=com_kinoarhiv&view=movies&content=movies&movies[genre][]=' . $item->id . '&Itemid=' . $this->itemid;
			}
		?>
		<?php if ($i % $cols == 0): ?>
		</div>
		<div class="row-fluid">
		<?php endif; ?>

			<div class="span3">
				<a href="<?php echo JRoute::_($url); ?>"><?php echo StringHelper::ucfirst($item->name); ?></a>&nbsp;
				<span class="badge badge-info pull-right"><?php echo $item->stats; ?></span>
			</div>

		<?php endfor; ?>

		</div>
	</div>
	<div class="clear"></div>
</div>
