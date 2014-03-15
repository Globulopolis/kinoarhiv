<?php defined('_JEXEC') or die; ?>
<script type="text/javascript" src="<?php echo JURI::root(); ?>components/com_kinoarhiv/assets/js/cookie.min.js"></script>
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
					showMsg('.container-main', response.message);
					$(document).scrollTop(0);
				}).fail(function(xhr, status, error){
					showMsg('.container-main', error);
				});
				return;
			} else {
				Joomla.submitform(task, document.getElementById('application-form'));
			}
		});
	}

	jQuery(document).ready(function($){
		var active_tab = 0;
		if (typeof $.cookie('com_kinoarhiv.settings.tabs') == 'undefined') {
			$.cookie('com_kinoarhiv.settings.tabs', 0);
		} else {
			active_tab = $.cookie('com_kinoarhiv.settings.tabs');
		}

		$('.hasTip, .hasTooltip, td[title]').tooltip({
			show: null,
			position: {
				my: 'left top',
				at: 'left bottom'
			},
			open: function(event, ui){
				ui.tooltip.animate({ top: ui.tooltip.position().top + 10 }, 'fast');
			},
			content: function(){
				var parts = $(this).attr('title').split('::', 2),
					title = '';

				if (parts.length == 2) {
					if (parts[0] != '') {
						title += '<div style="text-align: center; border-bottom: 1px solid #EEEEEE;">' + parts[0] + '</div>' + parts[1];
					} else {
						title += parts[1];
					}
				} else {
					title += $(this).attr('title');
				}

				return title;
			}
		});

		$('#settings_tabs').tabs({
			create: function(event, ui){
				$(this).tabs('option', 'active', parseInt(active_tab, 10));
			},
			activate: function(event, ui){
				$.cookie('com_kinoarhiv.settings.tabs', ui.newTab.index());
			}
		});

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
			<div id="settings_tabs">
				<ul>
					<li><a href="#page-global"><?php echo JText::_('COM_KA_SETTINGS_TAB'); ?></a></li>
					<li><a href="#page-appearance"><?php echo JText::_('COM_KA_APPEARANCE_TAB'); ?></a></li>
					<li><a href="#page-reviews"><?php echo JText::_('COM_KA_REVIEWS_TAB'); ?></a></li>
					<?php if ($this->userIsSuperAdmin): ?>
					<li><a href="#page-access"><?php echo JText::_('COM_KA_PERMISSIONS_LABEL'); ?></a></li>
					<?php endif; ?>
				</ul>
				<div id="page-global">
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
				</div>

				<div id="page-appearance">
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
				</div>

				<div id="page-reviews">
					<div class="row-fluid">
						<div class="span6">
							<?php echo $this->loadTemplate('reviews'); ?>
						</div>
						<div class="span6">
							<?php echo $this->loadTemplate('reviews_save'); ?>
						</div>
					</div>
				</div>

				<?php if ($this->userIsSuperAdmin): ?>
				<div id="page-access">
					<div class="row-fluid">
						<div class="span12">
							<?php echo $this->loadTemplate('access'); ?>
						</div>
					</div>
				</div>
				<?php endif; ?>
			</div>

			<input type="hidden" name="controller" value="settings" />
			<input type="hidden" name="task" value="" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
		<!-- End Content -->
	</div>
</form>
