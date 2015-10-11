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
<form action="index.php" method="post" style="margin: 0;" id="subtl_edit_form">
	<fieldset class="form-horizontal">
		<div class="control-group">
			<div class="control-label">
				<label for="jform_language" class="required"><?php echo JText::_('COM_KA_TRAILERS_HEADING_SUBTITLES_LANG_EDIT_SELECT'); ?></label>
			</div>
			<div class="controls">
				<select id="jform_language_subtl" class="inputbox" name="language_subtl">
				<?php foreach ($this->data['langs'] as $lang_code=>$lang):
					$selected = ($lang_code == $this->data['lang_code']) ? ' selected="selected"' : '';
				?>
					<option value="<?php echo htmlspecialchars(json_encode(array('lang_code'=>$lang_code,'lang'=>$lang))); ?>"<?php echo $selected; ?>><?php echo $lang; ?></option>
				<?php endforeach; ?>
				</select>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<label for="jform_desc"><?php echo JText::_('COM_KA_TRAILERS_HEADING_SUBTITLES_LANG_EDIT_DESC'); ?></label>
			</div>
			<div class="controls">
				<input id="jform_desc" type="text" size="50" value="<?php echo $this->data['lang']; ?>" name="desc" />
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<label for="jform_default"><?php echo JText::_('JDEFAULT'); ?></label>
			</div>
			<div class="controls">
				<select id="jform_default" class="inputbox" name="default_lang">
					<option value="false"<?php echo ($this->data['is_default'] == 0) ? ' selected="selected"' : ''; ?>><?php echo JText::_('JNO'); ?></option>
					<option value="true"<?php echo ($this->data['is_default'] == 1) ? ' selected="selected"' : ''; ?>><?php echo JText::_('JYES'); ?></option>
				</select>
			</div>
		</div>
		<div class="message"></div>
	</fieldset>
</form>
