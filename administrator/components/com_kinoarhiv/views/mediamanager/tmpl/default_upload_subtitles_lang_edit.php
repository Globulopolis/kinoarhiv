<?php defined('_JEXEC') or die; ?>
<form action="index.php" method="post" style="margin: 0;" id="subtl_edit_form">
	<fieldset class="form-horizontal">
		<div class="control-group">
			<div class="control-label">
				<label for="jform_language" class="required"><?php echo JText::_('COM_KA_TRAILERS_HEADING_SUBTITLES_LANG_EDIT_SELECT'); ?></label>
			</div>
			<div class="controls">
				<select id="jform_language" class="inputbox" name="jform[language]">
				<?php foreach ($this->data['langs'] as $lang_code=>$lang):
					$selected = ($lang_code == $this->data['lang_code']) ? ' selected="selected"' : '';
				?>
					<option value="<?php echo htmlspecialchars(json_encode(array($lang_code=>$lang))); ?>"<?php echo $selected; ?>><?php echo $lang; ?></option>
				<?php endforeach; ?>
				</select>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<label for="jform_language"><?php echo JText::_('COM_KA_TRAILERS_HEADING_SUBTITLES_LANG_EDIT_DESC'); ?></label>
			</div>
			<div class="controls">
				<input id="jform_desc" type="text" size="50" value="<?php echo $this->data['lang']; ?>" name="jform[desc]">
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<label for="jform_default"><?php echo JText::_('JDEFAULT'); ?></label>
			</div>
			<div class="controls">
				<select id="jform_default" class="inputbox" name="jform[default]">
					<option value="0"<?php echo ($this->data['is_default'] == 0) ? ' selected="selected"' : ''; ?>><?php echo JText::_('JNO'); ?></option>
					<option value="1"<?php echo ($this->data['is_default'] == 1) ? ' selected="selected"' : ''; ?>><?php echo JText::_('JYES'); ?></option>
				</select>
			</div>
		</div>

		<input type="hidden" name="option" value="com_kinoarhiv" />
		<input type="hidden" name="controller" value="mediamanager" />
		<input type="hidden" name="task" value="saveSubtitles" />
		<input type="hidden" name="trailer_id" value="<?php echo $this->data['trailer_id']; ?>" />
		<input type="hidden" name="subtitle_id" value="<?php echo $this->data['subtitle_id']; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</fieldset>
</form>
