<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('_JEXEC') or die;

JHtml::_('script', 'components/com_kinoarhiv/assets/editors/sceditor/jquery.sceditor.min.js');
JHtml::_('stylesheet', 'components/com_kinoarhiv/assets/editors/sceditor/themes/square.css');
KAComponentHelper::getScriptLanguage('', 'editors/sceditor/lang/');
JHtml::_('script', 'components/com_kinoarhiv/assets/editors/sceditor/plugins/format.js');
JHtml::_('script', 'components/com_kinoarhiv/assets/editors/sceditor/plugins/undo.js');

$params = $displayData->params;
$form   = $displayData->form;
?>
<script type="text/javascript">
	jQuery(document).ready(function($){
		var editor = $('#form_review').sceditor({
			plugins: 'format,undo',
			toolbar: 'bold,italic,underline|left,center,right,justify|bulletlist,orderedlist|font,size,format|quote|undo,maximize,source',
			height: '300',
			style: '<?php echo JUri::base(); ?>components/com_kinoarhiv/assets/editors/sceditor/themes/default_editor.css',
			emoticonsEnabled: false
		});

		// Insert username into editor
		$('.cmd-insert-username').click(function(){
			var username = $(this).text();

			editor.sceditor('instance').focus().insert('<strong>' + username + '</strong><br />');
		});

		// Insert cite into editor
		$('.cmd-insert-quote').click(function(e){
			e.preventDefault();

			var review = $(this).closest('.review-row');
			var quoted_text = review.find('.review').html(),
				quoted_link = review.find('.review-row-title a.permalink').attr('href'),
				username = review.find('.review-row-title span.username').text();

			editor.sceditor('instance').focus().insert(
				'<a href="' + quoted_link + '"><strong>' + username + '</strong><?php echo JText::_('COM_KA_REVIEWS_QUOTEWROTE'); ?>:</a>'
					+ '<br /><blockquote cite="' + quoted_link + '">' + quoted_text + '</blockquote><br />'
			);
		});

		$('form.editor').submit(function(e){
			var editor_inst = editor.sceditor('instance'),
				editor_text = editor_inst.val(),
				min_length = <?php echo (int) $params->get('reviews_length_min'); ?>,
				max_length = <?php echo (int) $params->get('reviews_length_max'); ?>,
				submit = $('input[type="submit"]', this);

			editor_inst.readOnly(true);
			submit.attr('disabled', true);

			if (editor_text.length < min_length || editor_text.length > max_length) {
				showMsg(
					$('.cmd-reset', this),
					'<?php echo JText::sprintf(
						JText::_('COM_KA_EDITOR_EMPTY'),
						(int) $params->get('reviews_length_min'),
						(int) $params->get('reviews_length_max')
					); ?>'
				);
				editor_inst.readOnly(false);

				window.setTimeout(function(){
					submit.removeAttr('disabled');
				}, 5000);

				return false;
			} else {
				submit.removeAttr('disabled');
			}

			return true;
		});
	});
</script>
<div style="clear: both;">&nbsp;</div>
<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" id="review-form" class="uk-form editor">
	<p><?php echo $form->getInput('review'); ?></p>
	<div class="select-type"><?php echo $form->getLabel('type'); ?><?php echo $form->getInput('type'); ?></div>
	<div class="clear"></div>
	<?php
	echo $form->getInput('captcha');
	echo JHtml::_('form.token'); ?>
	<input type="hidden" name="task" id="task" value="<?php echo $displayData->task; ?>"/>
	<input type="hidden" name="id" value="<?php echo $displayData->id; ?>"/>
	<br/>
	<input type="submit" class="btn btn-primary uk-button uk-button-primary" value="<?php echo JText::_('JSUBMIT'); ?>"/>
	<input type="reset" class="btn btn-default uk-button cmd-reset" value="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"/>
</form>
