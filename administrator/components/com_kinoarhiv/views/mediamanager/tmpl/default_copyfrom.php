<?php defined('_JEXEC') or die;
JFormHelper::addFieldPath(JPATH_COMPONENT . '/models/fields');
$movies_field = JFormHelper::loadFieldType('movies');
?>
<div class="row-fluid">
	<div class="span12">
		<fieldset class="form-horizontal copy">
			<legend>Directory paths</legend>
			<div class="control-group">
				<div class="control-label">
					<label id="jform_item-lbl" class="required" for="jform_item">123</label>
				</div>
				<div class="controls copy-from">
					<?php //echo $movies_field->getInput('copy_item'); ?>
					<input id="jform_item" class="span11 required" type="hidden" aria-required="true" required="" size="100" value="" name="jform[copy_item]" />
				</div>
			</div>
		</fieldset>
	</div>
</div>
