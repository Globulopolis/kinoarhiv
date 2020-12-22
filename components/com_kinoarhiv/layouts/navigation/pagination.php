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
$params     = $displayData['params'];
$pagination = $displayData['pagination'];
$limitstart = isset($displayData['limitstart']);
$task       = isset($displayData['task']);

// If set when do not wrap in form
$formWrap   = !isset($displayData['form']);

if ($params->get('pagevan_bottom') == 1): ?>
	<div class="pagination bottom">
	<?php if ($formWrap): ?>
		<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post"
			  name="adminForm" id="adminForm" style="clear: both;" autocomplete="off">
	<?php endif; ?>
			<?php echo $pagination->getPagesLinks(); ?><br/>
			<?php echo $pagination->getResultsCounter(); ?>&nbsp;
			<label for="limit" class="element-invisible"><?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?></label>
			<?php if ($params->get('show_pagination_limit')):
				echo $pagination->getLimitBox();
			endif;

			if ($limitstart): ?>
				<input type="hidden" name="limitstart" value=""/>
			<?php endif;

			if ($task): ?>
				<input type="hidden" name="task" value=""/>
			<?php endif; ?>
	<?php if ($formWrap): ?>
		</form>
	<?php endif; ?>
	</div>
<?php endif;
