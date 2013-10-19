<?php defined('_JEXEC') or die; ?>
<script src="<?php echo JURI::root(); ?>components/com_kinoarhiv/assets/js/ui.aurora.min.js" type="text/javascript"></script>
<script type="text/javascript" src="<?php echo JURI::root(); ?>components/com_kinoarhiv/assets/js/jquery-ui.min.js"></script>
<script type="text/javascript">
	function showMsg(selector, text) {
		jQuery(selector).aurora({
			text: text,
			placement: 'before',
			button: 'close',
			button_title: '[<?php echo JText::_('COM_KA_CLOSE'); ?>]'
		});
	}

	Joomla.submitbutton = function(task){
		jQuery(document).ready(function($){
			var _task = '<?php echo $this->task; ?>';
			if (task == 'menu') {
				$('.rel-menu').css({
					left: $('#toolbar-tools').offset().left+'px',
					top: ($('#toolbar-tools').offset().top+$('#toolbar-tools').height()+5)+'px'
				});
				$('.rel-menu').toggle();
				return;
			}<?php if (!empty($this->task)): ?> else if (task == 'relations_add') {
				document.location.href = 'index.php?option=com_kinoarhiv&controller=relations&task=add&param=<?php echo $this->task; ?>';
				return;
			} else if (task == 'relations_edit') {
				var items = $('#list .cbox').filter(':checked');
				if (items.length > 1) {
					showMsg('#j-main-container', '<?php echo JText::_('COM_KA_ITEMS_EDIT_DENIED'); ?>');
				} else if (items.length == 1) {
					var id = items.attr('id').substr(9).split('_');

					if (_task == 'countries') {
						document.location.href = 'index.php?option=com_kinoarhiv&controller=relations&task=edit&param='+_task+'&country_id='+id[0]+'&movie_id='+id[1];
					} else if (_task == 'genres') {
						document.location.href = 'index.php?option=com_kinoarhiv&controller=relations&task=edit&param='+_task+'&genre_id='+id[0]+'&movie_id='+id[1];
					} else if (_task == 'awards') {
						document.location.href = 'index.php?option=com_kinoarhiv&controller=relations&task=edit&param='+_task+'&award_id='+id[0]+'&item_id='+id[1]+'&award_type=<?php echo isset($this->award_type) ? (int)$this->award_type : ''; ?>';
					} else if (_task == 'careers') {
						document.location.href = 'index.php?option=com_kinoarhiv&controller=relations&task=edit&param='+_task+'&career_id='+id[0]+'&name_id='+id[1];
					}
				}
				return;
			} else if (task == 'relations_remove') {
				var items = $('#list .cbox').filter(':checked');

				if (items.length <= 0) {
					showMsg('#j-main-container', '<?php echo JText::_('JWARNING_TRASH_MUST_SELECT'); ?>');
					return;
				}

				if (!confirm("<?php echo JText::_('COM_KA_DELETE_SELECTED'); ?>")) {
					return;
				}

				$.post('index.php?option=com_kinoarhiv&controller=relations&task=delete&param=<?php echo $this->task; ?><?php echo isset($this->award_type) ? '&award_type='.$this->award_type : ''; ?>&format=json', {'data': items.serializeArray()}, function(response){
					showMsg('#j-main-container', response.message);
					$('#list').trigger('reloadGrid');
				}).fail(function(xhr, status, error){
					showMsg('#j-main-container', error);
				});
				return;
			} else if (task == '<?php echo $this->task; ?>') {
				document.location.href = 'index.php?option=com_kinoarhiv&view=<?php echo $this->task; ?>';
				return;
			}<?php endif; ?>
		});
	}

	jQuery(document).ready(function($){
		$('#relations_menu').menu();
	});
</script>
<div id="j-main-container">
	<form action="index.php?option=com_kinoarhiv&view=relations" method="post" id="adminForm" name="adminForm" autocomplete="off">
		<?php if (!empty($this->task)):
			echo $this->loadTemplate($this->task);
		endif; ?>
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</form>

	<div class="rel-menu">
		<ul id="relations_menu">
			<li><a href="index.php?option=com_kinoarhiv&view=relations&task=countries"><?php echo JText::_('COM_KA_CP_COUNTRIES').' <-> '.JText::_('COM_KA_CP_MOVIES'); ?></a></li>
			<li><a href="index.php?option=com_kinoarhiv&view=relations&task=genres"><?php echo JText::_('COM_KA_CP_GENRES').' <-> '.JText::_('COM_KA_CP_MOVIES'); ?></a></li>
			<li><a href="index.php?option=com_kinoarhiv&view=relations&task=awards&award_type=0"><?php echo JText::_('COM_KA_AW_FIELD_TITLE').' <-> '.JText::_('COM_KA_CP_MOVIES'); ?></a></li>
			<li><a href="index.php?option=com_kinoarhiv&view=relations&task=awards&award_type=1"><?php echo JText::_('COM_KA_AW_FIELD_TITLE').' <-> '.JText::_('COM_KA_CP_NAMES'); ?></a></li>
			<li><a href="index.php?option=com_kinoarhiv&view=relations&task=careers"><?php echo JText::_('COM_KA_CP_CAREERS').' <-> '.JText::_('COM_KA_CP_NAMES'); ?></a></li>
		</ul>
	</div>
</div>