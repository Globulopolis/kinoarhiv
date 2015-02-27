<?php defined('_JEXEC') or die; ?>
<style type="text/css">
	.container-main { padding-left: 5px; padding-right: 10px; }
</style>
<script type="text/javascript">
	Joomla.submitbutton = function(task){
		jQuery(document).ready(function($){
			var _task = '<?php echo $this->task; ?>';
			if (task == 'menu') {
				$('.rel-menu').css({
					left: $('#toolbar-link').offset().left+'px',
					top: ($('#toolbar-link').offset().top+$('#toolbar-link').height()+5)+'px'
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
			<li><a href="index.php?option=com_kinoarhiv&view=relations&task=countries&element=movies"><?php echo JText::_('COM_KA_COUNTRIES_TITLE').' &harr; '.JText::_('COM_KA_MOVIES_TITLE'); ?></a></li>
			<li><a href="index.php?option=com_kinoarhiv&view=relations&task=genres&element=movies"><?php echo JText::_('COM_KA_GENRES_TITLE').' &harr; '.JText::_('COM_KA_MOVIES_TITLE'); ?></a></li>
			<li><a href="index.php?option=com_kinoarhiv&view=relations&task=awards&element=movies&award_type=0"><?php echo JText::_('COM_KA_AWARDS_TITLE').' &harr; '.JText::_('COM_KA_MOVIES_TITLE'); ?></a></li>
			<li><a href="index.php?option=com_kinoarhiv&view=relations&task=awards&element=names&award_type=1"><?php echo JText::_('COM_KA_AWARDS_TITLE').' &harr; '.JText::_('COM_KA_NAMES_TITLE'); ?></a></li>
			<li><a href="index.php?option=com_kinoarhiv&view=relations&task=careers&element=names"><?php echo JText::_('COM_KA_CAREERS_TITLE').' &harr; '.JText::_('COM_KA_NAMES_TITLE'); ?></a></li>
			<li><a href="index.php?option=com_kinoarhiv&view=relations&task=genres&element=names"><?php echo JText::_('COM_KA_GENRES_TITLE').' &harr; '.JText::_('COM_KA_NAMES_TITLE'); ?></a></li>
		</ul>
	</div>
</div>
