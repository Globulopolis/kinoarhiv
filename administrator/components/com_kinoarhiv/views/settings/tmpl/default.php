<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.modal', 'collapseModal');
JHtml::_('script', JURI::root() . 'components/com_kinoarhiv/assets/js/cookie.min.js');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		jQuery(document).ready(function($){
			var form = $('#application-form');
			if (task != 'cancel' && task != 'save' && task != 'saveConfig' && task != 'restoreConfigLayout' && task != 'restoreConfig') {
				$.post(form.attr('action'), form.serialize()+'&task='+task+'&format=json', function(response){
					showMsg('.container-main', response.message);
					$(document).scrollTop(0);
				}).fail(function(xhr, status, error){
					showMsg('.container-main', error);
				});
			} else {
				if (task == 'saveConfig') {
					window.location = '<?php echo JUri::base(); ?>index.php?option=com_kinoarhiv&controller=settings&task=saveConfig&format=raw';
				} else if (task == 'restoreConfigLayout') {
					$('#collapseModal').modal();
				} else if (task == 'restoreConfig') {
					Joomla.submitform(task, document.getElementById('adminRestoreConfig'));
				} else {
					Joomla.submitform(task, document.getElementById('application-form'));
				}
			}
		});
	};

	jQuery(document).ready(function($){
		var active_tab = 0;
		if (typeof $.cookie('com_kinoarhiv.settings.tabs') == 'undefined') {
			$.cookie('com_kinoarhiv.settings.tabs', 0);
		} else {
			active_tab = $.cookie('com_kinoarhiv.settings.tabs');
		}

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
		$('#jform_slider_min_item').spinner({
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
		$('#jform_slider_max_item').spinner({
			spin: function(event, ui){
				if (ui.value > 100) {
					$(this).spinner('value', 10);
					return false;
				} else if (ui.value < 10) {
					$(this).spinner('value', 100);
					return false;
				}
			}
		});

		// For movie alphabet
		var cloned_m_rows = $('.movie-ab .letters-lang').length;
		$('.cmd-abm-new-row').click(function(e){
			e.preventDefault();
			var row = $(this).closest('.row-fluid');
			var cloned_row = row.clone(true);

			row.after(cloned_row);
			$('.letters-lang', cloned_row).val('');
			$('.letters', cloned_row).val('');
			cloned_m_rows++;
		});
		$('.cmd-abm-remove-row').click(function(e){
			e.preventDefault();

			if (cloned_m_rows > 1) {
				$(this).closest('.row-fluid').remove();
				cloned_m_rows--;
			}
		});
		// End

		// For persons(names) alphabet
		var cloned_n_rows = $('.name-ab .letters-lang').length;
		$('.cmd-abn-new-row').click(function(e){
			e.preventDefault();
			var row = $(this).closest('.row-fluid');
			var cloned_row = row.clone(true);

			row.after(cloned_row);
			$('.letters-lang', cloned_row).val('');
			$('.letters', cloned_row).val('');
			cloned_n_rows++;
		});
		$('.cmd-abn-remove-row').click(function(e){
			e.preventDefault();

			if (cloned_n_rows > 1) {
				$(this).closest('.row-fluid').remove();
				cloned_n_rows--;
			}
		});
		// End
	});
</script>
<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv');?>" id="application-form" method="post" name="adminForm" autocomplete="off">
	<div class="row-fluid">
		<!-- Begin Content -->
		<div class="span12">
			<div id="settings_tabs">
				<ul>
					<li><a href="#page-global"><?php echo JText::_('COM_KA_SETTINGS_TAB'); ?></a></li>
					<li><a href="#page-updl"><?php echo JText::_('COM_KA_UPLOAD_DOWNLOAD_TAB'); ?></a></li>
					<li><a href="#page-music"><?php echo JText::_('COM_KA_MUSIC_TAB'); ?></a></li>
					<li><a href="#page-appearance"><?php echo JText::_('COM_KA_APPEARANCE_TAB'); ?></a></li>
					<li><a href="#page-reviews"><?php echo JText::_('COM_KA_REVIEWS_TAB'); ?></a></li>
					<li><a href="#page-search"><?php echo JText::_('COM_KA_SEARCH_TAB'); ?></a></li>
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
				</div>

				<div id="page-updl">
					<div class="row-fluid">
						<?php echo $this->loadTemplate('updl'); ?>
					</div>
				</div>

				<div id="page-music">
					<div class="row-fluid">
						<div class="span6">
							<?php echo $this->loadTemplate('music_global'); ?>
						</div>
						<div class="span6">
							<?php echo $this->loadTemplate('music_covers'); ?>
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
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<?php echo $this->loadTemplate('ap_rate'); ?>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<?php echo $this->loadTemplate('ap_alphabet'); ?>
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

				<div id="page-search">
					<div class="row-fluid">
						<div class="span6">
							<?php echo $this->loadTemplate('search_movies'); ?>
						</div>
						<div class="span6">
							<?php echo $this->loadTemplate('search_names'); ?>
						</div>
					</div>
					<?php echo $this->loadTemplate('search_other'); ?>
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

<!-- Upload config layout -->
<?php echo $this->loadTemplate('upload_config');
