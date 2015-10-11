<?php defined('_JEXEC') or die;
$batch_types = array(
	'' => JText::_('COM_KA_REVIEWS_TYPE_BATCH_NONE'),
	0  => JText::_('COM_KA_REVIEWS_TYPE_0'),
	1  => JText::_('COM_KA_REVIEWS_TYPE_1'),
	2  => JText::_('COM_KA_REVIEWS_TYPE_2'),
	3  => JText::_('COM_KA_REVIEWS_TYPE_3')
);
?>
<div class="modal hide fade" id="collapseModal">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">&#215;</button>
		<h3><?php echo JText::_('COM_KA_BATCH_OPTIONS'); ?></h3>
	</div>
	<div class="modal-body modal-batch">
		<div class="row-fluid">
			<div class="control-group span6">
				<div class="controls">
					<label id="batch-type-lbl" for="batch-type"><?php echo JText::_('COM_KA_REVIEWS_FIELD_TYPE_TITLE'); ?></label>
					<?php echo JHTML::_('select.genericlist', $batch_types, 'batch[type]', null, 'value', 'text', '', 'batch-type'); ?>
				</div>
			</div>
			<div class="control-group span6">
				<div class="controls">
					<?php echo JHtml::_('batch.user'); ?>
				</div>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<button class="btn btn-primary" type="submit" onclick="Joomla.submitbutton('batch');">
			<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
		</button>
		<button class="btn" type="button" onclick="document.getElementById('batch-type').value='';document.getElementById('batch-user-id').value='';" data-dismiss="modal">
			<?php echo JText::_('JCANCEL'); ?>
		</button>
	</div>
</div>
