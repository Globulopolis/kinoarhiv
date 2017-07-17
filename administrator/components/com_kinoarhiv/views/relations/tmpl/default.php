<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *  
 * @copyright   Copyright (C) 2017 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task){
		jQuery(document).ready(function($){
			var _task = '<?php echo $this->task; ?>',
				menu = $('.rel-menu'),
				toolbar_link = $('#toolbar-link'),
				items = $('#list .cbox');

			if (task == 'menu') {
				menu.css({
					left: toolbar_link.offset().left + 'px',
					top: (toolbar_link.offset().top + toolbar_link.height() + 5) + 'px'
				});
				menu.toggle();
			}<?php if (!empty($this->task)): ?> else if (task == 'relations_add') {
				document.location.href = 'index.php?option=com_kinoarhiv&controller=relations&task=add&element=<?php echo $this->element; ?>&param=<?php echo $this->task; ?>';
			} else if (task == 'relations_edit') {
				items.filter(':checked');
				if (items.length > 1) {
					showMsg('#system-message-container', '<?php echo JText::_('COM_KA_ITEMS_EDIT_DENIED'); ?>');
				} else if (items.length == 1) {
					var id = items.attr('id').split('_'); // Split 'id' attribute value of the checkbox by '_' separator

					if (_task == 'countries') {
						document.location.href = 'index.php?option=com_kinoarhiv&controller=relations&task=edit&element=<?php echo $this->element; ?>&param='+_task+'&country_id='+id[2]+'&movie_id='+id[3];
					} else if (_task == 'genres') {
						<?php if ($this->element == 'movies'): ?>
						document.location.href = 'index.php?option=com_kinoarhiv&controller=relations&task=edit&element=<?php echo $this->element; ?>&param='+_task+'&genre_id='+id[2]+'&movie_id='+id[3];
						<?php elseif ($this->element == 'names'): ?>
						document.location.href = 'index.php?option=com_kinoarhiv&controller=relations&task=edit&element=<?php echo $this->element; ?>&param='+_task+'&genre_id='+id[2]+'&name_id='+id[3];
						<?php endif; ?>
					} else if (_task == 'awards') {
						document.location.href = 'index.php?option=com_kinoarhiv&controller=relations&task=edit&element=<?php echo $this->element; ?>&param='+_task+'&award_id='+id[2]+'&item_id='+id[3]+'&award_type=<?php echo isset($this->award_type) ? (int)$this->award_type : ''; ?>';
					} else if (_task == 'careers') {
						document.location.href = 'index.php?option=com_kinoarhiv&controller=relations&task=edit&element=<?php echo $this->element; ?>&param='+_task+'&career_id='+id[2]+'&name_id='+id[3];
					}
				}
			} else if (task == 'relations_remove') {
				items.filter(':checked');

				if (items.length <= 0) {
					showMsg('#system-message-container', '<?php echo JText::_('JWARNING_TRASH_MUST_SELECT'); ?>');
					return;
				}

				if (!confirm("<?php echo JText::_('COM_KA_DELETE_SELECTED'); ?>")) {
					return;
				}

				$.post('index.php?option=com_kinoarhiv&controller=relations&task=delete&element=<?php echo $this->element; ?>&param=<?php echo $this->task; ?><?php echo isset($this->award_type) ? '&award_type=' . $this->award_type : ''; ?>&format=json', {'data': items.serializeArray()}, function(response){
					showMsg('#system-message-container', response.message);
					$('#list').trigger('reloadGrid');
				}).fail(function(xhr, status, error){
					showMsg('#system-message-container', error);
				});
			} else if (task == '<?php echo $this->task; ?>') {
				document.location.href = 'index.php?option=com_kinoarhiv&view=<?php echo $this->task; ?>';
			}<?php endif; ?>
		});
	};

	jQuery(document).ready(function($){
		$('#relations_menu').menu();
	});
</script>
<div id="j-main-container">
	<form action="index.php?option=com_kinoarhiv&view=relations" method="post" id="adminForm" name="adminForm" autocomplete="off">
		<?php
		if (!empty($this->task)):
			echo $this->loadTemplate($this->task);
		endif;
		?>
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</form>

	<div class="rel-menu">
		<ul id="relations_menu">
			<li><a href="index.php?option=com_kinoarhiv&view=relations&task=countries&element=movies"><?php echo JText::_('COM_KA_COUNTRIES_TITLE') . ' &harr; ' . JText::_('COM_KA_MOVIES_TITLE'); ?></a></li>
			<li><a href="index.php?option=com_kinoarhiv&view=relations&task=genres&element=movies"><?php echo JText::_('COM_KA_GENRES_TITLE').' &harr; ' . JText::_('COM_KA_MOVIES_TITLE'); ?></a></li>
			<li><a href="index.php?option=com_kinoarhiv&view=relations&task=awards&element=movies&award_type=0"><?php echo JText::_('COM_KA_AWARDS_TITLE') . ' &harr; ' . JText::_('COM_KA_MOVIES_TITLE'); ?></a></li>
			<li><a href="index.php?option=com_kinoarhiv&view=relations&task=awards&element=names&award_type=1"><?php echo JText::_('COM_KA_AWARDS_TITLE') . ' &harr; ' . JText::_('COM_KA_NAMES_TITLE'); ?></a></li>
			<li><a href="index.php?option=com_kinoarhiv&view=relations&task=careers&element=names"><?php echo JText::_('COM_KA_CAREERS_TITLE') . ' &harr; ' . JText::_('COM_KA_NAMES_TITLE'); ?></a></li>
			<li><a href="index.php?option=com_kinoarhiv&view=relations&task=genres&element=names"><?php echo JText::_('COM_KA_GENRES_TITLE') . ' &harr; ' . JText::_('COM_KA_NAMES_TITLE'); ?></a></li>
		</ul>
	</div>
</div>
