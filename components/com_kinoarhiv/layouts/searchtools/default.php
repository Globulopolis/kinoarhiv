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

// Receive overridable options
$data['options'] = !empty($data['options']) ? $data['options'] : array();

// Set some basic options
$customOptions = array(
	'filtersHidden' => isset($data['options']['filtersHidden']) ? $data['options']['filtersHidden'] : empty($data['view']->activeFilters),
	'defaultLimit'  => isset($data['options']['defaultLimit']) ? $data['options']['defaultLimit'] : JFactory::getApplication()->get('list_limit', 20),
	'searchFieldSelector' => '#filter_search',
	'orderFieldSelector'  => '#list_fullordering',
	'context'             => !empty($data['context']) ? 'filter_' . $data['context'] . '_' : ''
);

$data['options'] = array_merge($customOptions, $data['options']);

$formSelector = !empty($data['options']['formSelector']) ? $data['options']['formSelector'] : '#adminForm';

// Load search tools
JHtml::_('searchtools.form', $formSelector, $data['options']);

?>
<div class="js-stools clearfix">
	<div class="clearfix">
		<div class="js-stools-container-bar">
			<?php echo JLayoutHelper::render('layouts.searchtools.default.bar', $data, JPATH_COMPONENT); ?>
		</div>
		<div class="js-stools-container-list">
			<?php echo JLayoutHelper::render('layouts.searchtools.default.list', $data, JPATH_COMPONENT); ?>
		</div>
	</div>
	<!-- Filters div -->
	<div class="js-stools-container-filters clearfix">
		<?php echo JLayoutHelper::render('layouts.searchtools.default.filters', $data, JPATH_COMPONENT); ?>
	</div>
</div>
