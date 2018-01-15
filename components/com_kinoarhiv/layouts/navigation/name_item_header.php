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

$data = $displayData;
?>
<header>
	<h1 class="uk-article-title title">
		<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $data['item']->id . '&Itemid=' . $data['itemid']); ?>" class="brand" title="<?php echo $data['item']->title; ?>"><?php echo $data['item']->title; ?></a>
	</h1>
</header>
