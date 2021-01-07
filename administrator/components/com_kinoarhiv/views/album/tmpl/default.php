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

				<div id="page1">
					<div class="row-fluid">
						<?php //echo $this->loadTemplate('edit_composer'); ?>
					</div>
				</div>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'albums', 'page2', JText::_('COM_KA_MUSIC_TRACKS_TITLE')); ?>

				<div id="page2">
					<div class="row-fluid">
						<?php //echo $this->loadTemplate('edit_tracks'); ?>
					</div>
				</div>

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
