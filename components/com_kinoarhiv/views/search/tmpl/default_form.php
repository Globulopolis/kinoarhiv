<?php defined('_JEXEC') or die;
JHtml::_('formbehavior.chosen', 'select');
?>
<div class="uk-article ka-content">
	<div class="advsearch-movies">
		<form action="<?php echo JRoute::_('index.php');?>" id="form_movies" method="post" autocomplete="off">
			<fieldset class="form-horizontal">
				<legend><?php echo JText::_('COM_KA_SEARCH_ADV_MOVIES_TITLE'); ?></legend>
				<?php foreach ($this->form->getFieldset('search_movies') as $field): ?>
					<div class="control-group">
						<div class="control-label"><?php echo $field->label; ?></div>
						<div class="controls"><?php echo $field->input; ?></div>
					</div>
				<?php endforeach; ?>
			</fieldset>

			<input type="hidden" name="option" value="com_kinoarhiv" />
			<input type="hidden" name="controller" value="search" />
			<input type="hidden" name="task" value="advanced" />
			<?php echo JHtml::_('form.token'); ?>
			<input type="submit" class="btn btn-primary uk-button uk-button-primary" value="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>" />
			<input type="reset" class="btn btn-default uk-button cmd-reset" value="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" />
		</form>
	</div>
</div>
