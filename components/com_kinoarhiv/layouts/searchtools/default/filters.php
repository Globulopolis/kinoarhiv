<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('JPATH_BASE') or die;

$data = $displayData;
$fieldset_class = !empty($data['fieldset_class']) ? $data['fieldset_class'] : '';

// Load the form filters
$filters = $data['view']->filterForm->getGroup('filter');
?>
<?php if ($filters): ?>
<fieldset class="<?php echo $fieldset_class; ?>">
	<?php foreach ($filters as $fieldName => $field): ?>
		<?php if ($fieldName != 'filter_search'): ?>
			<div class="js-stools-field-filter control-group">
				<div class="control-label"><?php echo $field->label; ?></div>
				<div class="controls"><?php echo $field->input; ?></div>
			</div>
		<?php endif; ?>
	<?php endforeach; ?>
</fieldset>
<?php endif; ?>
