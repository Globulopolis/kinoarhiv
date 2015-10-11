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
?>
<script type="text/javascript">
	jQuery(document).ready(function($){
		$('#form_movie_tags').select2({
			placeholder: '<?php echo JText::_('COM_KA_SEARCH_AJAX'); ?>',
			quietMillis: 100,
			minimumInputLength: 1,
			maximumSelectionSize: 10,
			multiple: true,
			ajax: {
				cache: true,
				url: 'index.php?option=com_kinoarhiv&task=ajaxData&element=tags&format=json',
				data: function(term, page){
					return { term: term, showAll: 0 }
				},
				results: function(data, page){
					return { results: data };
				}
			},
			<?php if ($this->form->getValue('tags', $this->form_edit_group) != ''):
				$tags = $this->form->getValue('tags', $this->form_edit_group); ?>
			initSelection: function(element, callback){
				var data = <?php echo json_encode($tags['data']); ?>;
				callback(data);
			},
			<?php endif; ?>
			formatResult: function(data){
				return data.title;
			},
			formatSelection: function(data, container){
				return data.title;
			},
			escapeMarkup: function(m) { return m; }
		}).select2('container').find('ul.select2-choices').sortable({
			containment: 'parent',
			start: function() { $("#form_movie_tags").select2('onSortStart'); },
			update: function() { $("#form_movie_tags").select2('onSortEnd'); }
		});
	});
</script>
<div class="row-fluid">
	<div class="span6">
		<fieldset class="form-horizontal">
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('metakey', $this->form_edit_group); ?></div>
				<div class="controls"><?php echo $this->form->getInput('metakey', $this->form_edit_group); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('metadesc', $this->form_edit_group); ?></div>
				<div class="controls"><?php echo $this->form->getInput('metadesc', $this->form_edit_group); ?></div>
			</div>
		</fieldset>
	</div>
	<div class="span6">
		<fieldset class="form-horizontal">
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('tags', $this->form_edit_group); ?></div>
				<div class="controls">
					<?php echo $this->form->getInput('tags', $this->form_edit_group); ?>
					<span class="rel-link"><a href="<?php echo JRoute::_('index.php?option=com_tags'); ?>" class="hasTip" title="::<?php echo JText::_('COM_KA_COM_TAGS'); ?>" target="_blank"><img src="components/com_kinoarhiv/assets/images/icons/tag_edit.png" border="0" /></a></span>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('robots', $this->form_edit_group); ?></div>
				<div class="controls"><?php echo $this->form->getInput('robots', $this->form_edit_group); ?></div>
			</div>
		</fieldset>
	</div>
</div>
