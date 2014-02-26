<?php defined('_JEXEC') or die;
JHtml::_('behavior.tooltip');
?>
<script type="text/javascript" src="<?php echo JURI::root(); ?>components/com_kinoarhiv/assets/js/select2.min.js"></script>
<script type="text/javascript" src="<?php echo JURI::root(); ?>components/com_kinoarhiv/assets/js/i18n/select/select2_locale_<?php echo substr($this->lang->getTag(), 0, 2); ?>.js"></script>
<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/jquery-ui.custom.min.js"></script>
<script type="text/javascript" src="<?php echo JURI::root(); ?>components/com_kinoarhiv/assets/js/ui.aurora.min.js"></script>
<script type="text/javascript">
	function showMsg(selector, text) {
		jQuery(selector).aurora({
			text: text,
			placement: 'before',
			button: 'close',
			button_title: '[<?php echo JText::_('COM_KA_CLOSE'); ?>]'
		});
	}

	Joomla.submitbutton = function(task) {
		jQuery(document).ready(function($){
			var form = $('#application-form');
			if (task != 'cancel' && task != 'save') {
				$.post(form.attr('action'), form.serialize()+'&task='+task+'&format=json', function(response){
					showMsg('#myTabTabs', response.message);
					$(document).scrollTop(0);
				}).fail(function(xhr, status, error){
					showMsg('#myTabTabs', error);
				});
				return;
			} else {
				Joomla.submitform(task, document.getElementById('application-form'));
			}
		});
	}

	jQuery(document).ready(function($){
		$('#jform_filter_genres, #jform_filter_names').select2();
		$('#jform_premieres_list_limit, #jform_releases_list_limit').spinner({
			spin: function(event, ui){
				if (ui.value > 5) {
					$(this).spinner('value', 0);
					return false;
				} else if (ui.value < 0) {
					$(this).spinner('value', 5);
					return false;
				}
			}
		});
		$('#jform_person_list_limit').spinner({
			spin: function(event, ui){
				if (ui.value > 10) {
					$(this).spinner('value', 1);
					return false;
				} else if (ui.value < 1) {
					$(this).spinner('value', 10);
					return false;
				}
			}
		});
		$('#jform_introtext_actors_list_limit').spinner({
			spin: function(event, ui){
				if (ui.value > 10) {
					$(this).spinner('value', 0);
					return false;
				} else if (ui.value < 0) {
					$(this).spinner('value', 10);
					return false;
				}
			}
		});
	});
</script>
<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv');?>" id="application-form" method="post" name="adminForm" autocomplete="off">
	<div class="row-fluid">
		<!-- Begin Content -->
		<div class="span12">
			<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'page-global')); ?>

				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'page-global', JText::_('COM_KA_SETTINGS_TAB', true)); ?>
					<div class="row-fluid">
						<div class="span6">
							<?php echo $this->loadTemplate('global'); ?>
						</div>
						<div class="span6">
							<?php echo $this->loadTemplate('metadata'); ?>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<?php echo $this->loadTemplate('paths'); ?>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<?php echo $this->loadTemplate('gallery'); ?>
						</div>
					</div>
				<?php echo JHtml::_('bootstrap.endTab'); ?>

				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'page-appearance', JText::_('COM_KA_APPEARANCE_TAB', true)); ?>
					<div class="row-fluid">
						<div class="span6">
							<?php echo $this->loadTemplate('ap_global'); ?>
							<?php echo $this->loadTemplate('ap_nav'); ?>
						</div>
						<div class="span6">
							<?php echo $this->loadTemplate('ap_item'); ?>
							<?php echo $this->loadTemplate('ap_rate'); ?>
						</div>
					</div>
				<?php echo JHtml::_('bootstrap.endTab'); ?>

				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'page-reviews', JText::_('COM_KA_REVIEWS_TAB', true)); ?>
					<div class="row-fluid">
						<div class="span6">
							<?php echo $this->loadTemplate('reviews'); ?>
						</div>
						<div class="span6">
							<?php echo $this->loadTemplate('reviews_save'); ?>
						</div>
					</div>
				<?php echo JHtml::_('bootstrap.endTab'); ?>

				<?php if ($this->userIsSuperAdmin):
					echo JHtml::_('bootstrap.addTab', 'myTab', 'page-access', JText::_('COM_KA_PERMISSIONS_LABEL', true)); ?>
					<div class="row-fluid">
						<div class="span12">
							<?php echo $this->loadTemplate('access'); ?>
						</div>
					</div>
					<?php echo JHtml::_('bootstrap.endTab');
				endif; ?>

			<?php echo JHtml::_('bootstrap.endTabSet'); ?>

			<input type="hidden" name="controller" value="settings" />
			<input type="hidden" name="task" value="" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
		<!-- End Content -->
	</div>
</form>
