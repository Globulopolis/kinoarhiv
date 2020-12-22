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
$data = $displayData;
?>
<header>
	<h1 class="uk-article-title title" itemprop="name">
		<?php if ($data['item']->attribs->link_titles === ''): ?>
			<?php if ($data['params']->get('link_titles') == 1): ?>
				<a href="<?php echo JRoute::_($displayData['url']); ?>" class="brand"
				   title="<?php echo $data['item']->title; ?>"><?php echo $data['item']->title; ?></a>
			<?php else: ?>
				<span class="brand"><?php echo $data['item']->title; ?></span>
			<?php endif; ?>
		<?php elseif ($data['item']->attribs->link_titles == 1): ?>
			<a href="<?php echo JRoute::_($displayData['url']); ?>" class="brand"
			   title="<?php echo $data['item']->title; ?>"><?php echo $data['item']->title; ?></a>
		<?php elseif ($data['item']->attribs->link_titles == 0): ?>
			<span class="brand"><?php echo $data['item']->title; ?></span>
		<?php endif; ?>
	</h1>
</header>
<div class="middle-nav clearfix">
	<?php
	echo JLayoutHelper::render(
		'layouts.navigation.mark_links',
		array(
			'params'     => $data['params'],
			'item'       => $data['item'],
			'guest'      => $displayData['guest'],
			'itemid'     => $data['itemid'],
			'controller' => 'names',
			'msgPlaceAt' => 'div.middle-nav'
		),
		JPATH_COMPONENT
	);
	?>
</div>
