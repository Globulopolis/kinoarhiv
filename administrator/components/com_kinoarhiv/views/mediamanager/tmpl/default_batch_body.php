<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2018 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

$input = JFactory::getApplication()->input;
$section = $input->getWord('section');
?>
<div class="container-fluid">
	<div class="row-fluid">
		<?php if ($section === 'movie' || $section === 'name'): ?>
		<div class="control-group span6">
			<div class="controls">
				<?php echo JLayoutHelper::render('joomla.html.batch.language', array()); ?>
			</div>
		</div>
		<div class="control-group span6">
			<div class="controls">
				<?php echo JLayoutHelper::render('joomla.html.batch.access', array()); ?>
			</div>
		</div>
		<?php elseif ($section === 'album'): ?>
			<div class="control-group span6">
				<div class="controls">
					<?php echo JLayoutHelper::render('administrator.components.com_kinoarhiv.layouts.batch.type', array(), JPATH_ROOT); ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>
