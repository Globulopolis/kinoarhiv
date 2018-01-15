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
<div class="uk-article ka-content">
	<article class="uk-article">
		<header>
			<h1 class="uk-article-title title"><?php echo $this->escape($this->item->title); ?></h1>
		</header>
		<div class="info"><?php echo $this->item->desc; ?></div>
	</article>
</div>
