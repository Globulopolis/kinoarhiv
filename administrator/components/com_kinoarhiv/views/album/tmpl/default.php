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
JHtml::_('stylesheet', 'media/com_kinoarhiv/jqueryui/' . $this->params->get('ui_theme') . '/jquery-ui.min.css');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery-ui.min.js');
JHtml::_('stylesheet', 'media/com_kinoarhiv/css/colorbox.css');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery.colorbox.min.js');
KAComponentHelper::getScriptLanguage('jquery.colorbox-', 'media/com_kinoarhiv/js/i18n/colorbox/', true, true);
KAComponentHelperBackend::loadMediamanagerAssets();

$this->input = JFactory::getApplication()->input;
$this->id    = $this->form->getValue('id');
$navgridOpts = array(
	'btn' => array(
		'lang' => array(
			'addtext'     => JText::_('JTOOLBAR_ADD'), 'edittext' => JText::_('JTOOLBAR_EDIT'),
			'deltext'     => JText::_('JTOOLBAR_REMOVE'), 'searchtext' => JText::_('JSEARCH_FILTER'),
			'refreshtext' => JText::_('JTOOLBAR_REFRESH'), 'viewtext' => JText::_('JGLOBAL_PREVIEW')
		)
	)
);
$token       = JSession::getFormToken();
$languageTag = substr($this->lang->getTag(), 0, 2);
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task === 'albums.cancel' || document.formvalidator.isValid(document.getElementById('item-form'))) {
			Joomla.submitform(task, document.getElementById('item-form'));
		}
	};

	jQuery(document).ready(function($){
		// Update total votes
		$('.field_rate_sum, .field_rate').blur(function(){
			var rate = parseInt($('.field_rate').val(), 10),
				votesum = parseInt($('.field_rate_sum').val(), 10);

			if (isNaN(rate) || isNaN(votesum)) {
				$('#vote').text('0');

				return;
			}

			var vote = parseFloat(votesum / rate).toFixed(<?php echo (int) $this->params->get('vote_summ_precision'); ?>);

			if (empty(vote) || empty(rate) || rate === 0) {
				$('#vote').text('0');
			} else {
				$('#vote').text(vote);
			}
		}).trigger('blur');
	});
</script>
<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv&id=' . (int) $this->id); ?>" method="post" name="adminForm"
	  id="item-form" class="form-validate" autocomplete="off">
	<div id="j-main-container">
		<div class="row-fluid">
			<div class="span12">
			<?php echo JHtml::_('bootstrap.startTabSet', 'albums', array('active' => 'page0')); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'albums', 'page0', JText::_('COM_KA_MOVIES_TAB_MAIN')); ?>

					<?php echo $this->loadTemplate('info'); ?>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'albums', 'page1', JText::_('COM_KA_MUSIC_GROUP_HEADING')); ?>

				<?php
				if ($this->id != 0)
				{
					$options = array(
						'url'   => JRoute::_('index.php?option=com_kinoarhiv&task=api.data&content=albumCrew&format=json'
							. '&lang=' . $languageTag . '&id=' . $this->id . '&' . $token . '=1'
						),
						'add_url'  => JRoute::_('index.php?option=com_kinoarhiv&task=albums.editAlbumCrew&item_id=' . $this->id),
						'edit_url' => JRoute::_('index.php?option=com_kinoarhiv&task=albums.editAlbumCrew&item_id=' . $this->id),
						'del_url'  => JRoute::_('index.php?option=com_kinoarhiv&task=albums.removeAlbumCrew&format=json&id=' . $this->id),
						'width'    => '#j-main-container', 'height' => '#item-form',
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
				<?php echo JHtml::_('bootstrap.addTab', 'albums', 'page2', JText::_('COM_KA_MUSIC_TRACKS_TITLE')); ?>

				<div class="row-fluid">
					<div class="span6">
						<fieldset class="form-horizontal">
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('tracks_path'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('tracks_path'); ?></div>
							</div>
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('tracks_path_www'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('tracks_path_www'); ?></div>
							</div>
						</fieldset>
					</div>
					<div class="span6">
						<fieldset class="form-horizontal">
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('tracks_preview_path'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('tracks_preview_path'); ?></div>
							</div>
						</fieldset>
					</div>
				</div>
				<?php
				if ($this->id != 0)
				{
					$options = array(
						'url'   => JRoute::_('index.php?option=com_kinoarhiv&task=api.data&content=albumTraks&format=json'
							. '&lang=' . $languageTag . '&id=' . $this->id . '&' . $token . '=1'
						),
						'add_url'  => JRoute::_('index.php?option=com_kinoarhiv&task=albums.editTracks&item_id=' . $this->id),
						'edit_url' => JRoute::_('index.php?option=com_kinoarhiv&task=albums.editTracks&item_id=' . $this->id),
						'del_url'  => JRoute::_('index.php?option=com_kinoarhiv&task=albums.removeTracks&format=json&id=' . $this->id),
						'width' => '#j-main-container', 'height' => '#item-form',
						'order' => 't.id', 'orderby' => 'asc',
						'idprefix' => 't_',
						'rowlist'  => array(5, 10, 15, 20, 25, 30, 50, 100, 200, 500),
						'colModel' => array(
							'JGRID_HEADING_ID' => (object) array(
								'name' => 'id', 'index' => 't.id', 'width' => 50, 'title' => false,
								'sorttype' => 'int', 'search' => false
							),
							'COM_KA_MUSIC_TRACKS_CD_NUMBER' => (object) array(
								'name' => 'cd_number', 'index' => 't.cd_number', 'width' => 50, 'title' => false,
								'sorttype' => 'int', 'search' => false
							),
							'COM_KA_MUSIC_TRACKS_NUMBER' => (object) array(
								'name' => 'track_number', 'index' => 't.track_number', 'width' => 50, 'title' => false,
								'sorttype' => 'int', 'search' => false
							),
							'COM_KA_TRACK_TITLE' => (object) array(
								'name' => 'title', 'index' => 't.title', 'width' => 250, 'title' => false,
								'sorttype' => 'text',
								'searchoptions' => (object) array(
									'sopt' => array('cn', 'eq', 'bw', 'ew')
								)
							),
							'COM_KA_MOVIES_GALLERY_HEADING_FILENAME' => (object) array(
								'name' => 'filename', 'index' => 't.filename', 'width' => 250, 'title' => false,
								'sorttype' => 'text',
								'searchoptions' => (object) array(
									'sopt' => array('cn', 'eq', 'bw', 'ew')
								)
							),
							'COM_KA_MUSIC_ALBUMS_FIELD_LENGTH' => (object) array(
								'name' => 'length', 'index' => 't.length', 'width' => 80, 'title' => false,
								'sorttype' => 'text',
								'searchoptions' => (object) array(
									'sopt' => array('cn', 'eq', 'le', 'ge')
								)
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
				<?php echo JHtml::_('bootstrap.addTab', 'albums', 'page3', JText::_('COM_KA_MOVIES_TAB_RATE')); ?>

				<div class="row-fluid">
					<div class="span6">
						<fieldset class="form-horizontal ratings-fields">
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('rate_sum'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('rate_sum'); ?></div>
							</div>
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('rate'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('rate'); ?></div>
							</div>
							<div class="control-group">
								<div class="span12">
									<?php echo JText::_('COM_KA_FIELD_MOVIE_VOTESUMM'); ?> / <?php echo JText::_('COM_KA_FIELD_MOVIE_VOTES'); ?> = <span id="vote">0</span>
								</div>
							</div>
						</fieldset>
					</div>
				</div>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'albums', 'page4', JText::_('COM_KA_MOVIES_TAB_META')); ?>

				<div class="row-fluid">
					<div class="span6">
						<fieldset class="form-horizontal">
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('metakey'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('metakey'); ?></div>
							</div>
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('metadesc'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('metadesc'); ?></div>
							</div>
						</fieldset>
					</div>
					<div class="span6">
						<fieldset class="form-horizontal">
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('tags'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('tags'); ?></div>
							</div>
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('robots'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('robots'); ?></div>
							</div>
						</fieldset>
					</div>
				</div>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'albums', 'page5', JText::_('COM_KA_MOVIES_TAB_PUB')); ?>

				<div class="row-fluid">
					<div class="span6">
						<fieldset class="form-horizontal">
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('publish_up'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('publish_up'); ?></div>
							</div>
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('publish_down'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('publish_down'); ?></div>
							</div>
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('created'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('created'); ?></div>
							</div>
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('created_by'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('created_by'); ?></div>
							</div>
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('modified'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('modified'); ?></div>
							</div>
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('modified_by'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('modified_by'); ?></div>
							</div>
							<?php foreach ($this->form->getFieldset('basic') as $field): ?>
								<div class="control-group">
									<div class="control-label"><?php echo $field->label; ?></div>
									<div class="controls"><?php echo $field->input; ?></div>
								</div>
							<?php endforeach; ?>
						</fieldset>
					</div>
					<div class="span6">
						<fieldset class="form-horizontal">
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('language'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('language'); ?></div>
							</div>
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('state'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('state'); ?></div>
							</div>
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('access'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('access'); ?></div>
							</div>
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('ordering'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('ordering'); ?></div>
							</div>
							<?php foreach ($this->form->getFieldset('tabs') as $field): ?>
								<div class="control-group">
									<div class="control-label"><?php echo $field->label; ?></div>
									<div class="controls"><?php echo $field->input; ?></div>
								</div>
							<?php endforeach; ?>
						</fieldset>
					</div>
				</div>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'albums', 'page6', JText::_('COM_KA_PERMISSIONS_LABEL')); ?>

				<div class="row-fluid">
					<div class="span12">
						<fieldset class="form-horizontal">
							<div class="control-group">
								<div class="controls" style="margin-left: 0 !important;"><?php echo $this->form->getInput('rules'); ?></div>
							</div>
						</fieldset>
					</div>
				</div>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php echo JHtml::_('bootstrap.endTabSet'); ?>
			</div>
		</div>
	</div>

	<?php echo $this->form->getInput('genres_orig') . "\n"; ?>
	<?php echo $this->form->getInput('id') . "\n"; ?>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
