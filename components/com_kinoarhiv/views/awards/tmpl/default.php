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
	<div class="awards-list">
	<?php if (count($this->items) > 0):
		if ($this->params->get('pagevan_top') == 1): ?>
		<div class="pagination top">
			<?php echo $this->pagination->getPagesLinks(); ?>
		</div>
	<?php endif;

		foreach ($this->items as $item):
			echo JLayoutHelper::render('layouts.content.award',
				array('item' => $item, 'params' => $this->params),
				JPATH_COMPONENT
			);
		endforeach; ?>

	<?php
		echo JLayoutHelper::render('layouts.navigation.pagination',
			array('params' => $this->params, 'pagination' => $this->pagination),
			JPATH_COMPONENT
		);
	else: ?>
		<br /><div><?php echo KAComponentHelper::showMsg(JText::_('COM_KA_NO_ITEMS')); ?></div>
	<?php endif; ?>
	</div>
</div>
