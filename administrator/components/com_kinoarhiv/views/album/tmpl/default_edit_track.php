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

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select:not(.hasAutocomplete)');
JHtml::_('stylesheet', 'media/com_kinoarhiv/jqueryui/' . $this->params->get('ui_theme') . '/jquery-ui.min.css');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery-ui.min.js');

$this->id    = $this->form->getValue('id');
$navgridOpts = array(
	'btn' => array(
		'lang' => array(
			'addtext'     => JText::_('JTOOLBAR_ADD'), 'edittext' => JText::_('JTOOLBAR_EDIT'),
			'deltext'     => JText::_('JTOOLBAR_REMOVE'), 'searchtext' => JText::_('JSEARCH_FILTER'),
			'refreshtext' => JText::_('JTOOLBAR_REFRESH'), 'viewtext' => JText::_('JGLOBAL_PREVIEW')
		),
		'search' => false
	)
);
$token       = JSession::getFormToken();
$languageTag = substr($this->lang->getTag(), 0, 2);
?>
<div id="j-main-container">
	<div class="row-fluid">
		<div class="span12">
		<?php echo JHtml::_('bootstrap.startTabSet', 'track', array('active' => 'page0')); ?>
			<?php echo JHtml::_('bootstrap.addTab', 'track', 'page0', JText::_('COM_KA_MOVIES_TAB_MAIN')); ?>
				<?php echo JLayoutHelper::render('layouts.edit.relations', array('form' => $this->form), JPATH_COMPONENT_ADMINISTRATOR); ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php echo JHtml::_('bootstrap.addTab', 'track', 'page1', JText::_('COM_KA_MUSIC_GROUP_HEADING')); ?>

			<?php
			if ($this->id != 0)
			{
				$options = array(
					'url'   => JRoute::_('index.php?option=com_kinoarhiv&task=api.data&content=albumCrew&format=json'
						. '&lang=' . $languageTag . '&id=' . $this->id . '&' . $token . '=1&item_type=1'
					),
					'add_url'  => JRoute::_('index.php?option=com_kinoarhiv&task=albums.editAlbumCrew&item_id=' . $this->id . '&item_type=1'),
					'edit_url' => JRoute::_('index.php?option=com_kinoarhiv&task=albums.editAlbumCrew&item_id=' . $this->id . '&item_type=1'),
					'del_url'  => JRoute::_('index.php?option=com_kinoarhiv&task=albums.removeAlbumCrew&format=json&id=' . $this->id . '&item_type=1'),
					'width'    => '#j-main-container', 'height' => '#j-main-container',
					'order'    => 't.ordering', 'orderby' => 'asc',
					'idprefix' => 'c_',
					'grouping' => true,
					'groupingview' => (object) array(
						'groupField'      => array('type'),
						'groupColumnShow' => array(false),
						'groupText'       => array('<b>{0} - {1}' . JText::_('COM_KA_ITEMS_NUM') . '</b>'),
						'groupCollapse'   => false,
						'groupSummary'    => array(false),
						'groupDataSorted' => false
					),
					'rownum'    => 0,
					'pgbuttons' => false,
					'pginput'   => false,
					'colModel'  => array(
						'JGRID_HEADING_ID' => (object) array(
							'name' => 'row_id', 'index' => 't.id', 'width' => 60, 'title' => false,
							'sorttype' => 'int',
							'searchoptions' => (object) array(
								'sopt' => array('cn', 'eq', 'le', 'ge')
							)
						),
						'COM_KA_FIELD_NAME' => (object) array(
							'name' => 'name', 'index' => 'n.name', 'width' => 350, 'title' => false,
							'sorttype' => 'text',
							'searchoptions' => (object) array(
								'sopt' => array('cn', 'eq', 'bw', 'ew')
							)
						),
						'COM_KA_FIELD_NAME_ROLE' => (object) array(
							'name' => 'role', 'index' => 't.role', 'width' => 325, 'title' => false,
							'sorttype' => 'text',
							'searchoptions' => (object) array(
								'sopt' => array('cn', 'eq', 'bw', 'ew')
							)
						),
						'JGRID_HEADING_ORDERING' => (object) array(
							'name' => 'ordering', 'index' => 't.ordering', 'width' => 65, 'title' => false,
							'align' => 'right', 'sortable' => false, 'search' => false
						),
						'' => (object) array(
							'name' => 'type', 'width' => 1, 'sortable' => false, 'search' => false
						)
					),
					'navgrid' => $navgridOpts
				);

				echo JLayoutHelper::render('administrator.components.com_kinoarhiv.layouts.edit.grid', $options, JPATH_ROOT);
			}
			else
			{
				echo JText::_('COM_KA_NO_ID');
			}
			?>

			<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php echo JHtml::_('bootstrap.endTabSet'); ?>
		</div>
	</div>
</div>
