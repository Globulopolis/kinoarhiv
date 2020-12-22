<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2018 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

/** @var object $displayData */
$params = $displayData->params;
$form   = $displayData->form;

JHtml::_('jquery.framework');

if ($params->get('use_cdn', 0) == 1)
{
	$document = JFactory::getDocument();
	$document->addScript('https://cdn.jsdelivr.net/npm/sceditor@2.1.3/minified/sceditor.min.js');
	KAComponentHelper::getScriptLanguage('', 'media/com_kinoarhiv/editors/sceditor/lang/');
	$document->addScript('https://cdn.jsdelivr.net/npm/sceditor@2.1.3/minified/plugins/format.min.js');
	$document->addScript('https://cdn.jsdelivr.net/npm/sceditor@2.1.3/minified/plugins/undo.min.js');
	$document->addStyleSheet('https://cdn.jsdelivr.net/npm/sceditor@2.1.3/minified/themes/square.min.css');
}
else
{
	JHtml::_('script', 'media/com_kinoarhiv/editors/sceditor/sceditor.min.js');
	JHtml::_('stylesheet', 'media/com_kinoarhiv/editors/sceditor/themes/square.min.css');
	KAComponentHelper::getScriptLanguage('', 'media/com_kinoarhiv/editors/sceditor/lang/');
	JHtml::_('script', 'media/com_kinoarhiv/editors/sceditor/plugins/format.js');
	JHtml::_('script', 'media/com_kinoarhiv/editors/sceditor/plugins/undo.js');
}
?>
<script type="text/javascript">
	jQuery(document).ready(function($){
		var editor_input = document.getElementById('form_review');

		// Init editor
		sceditor.create(editor_input, {
			format: 'xhtml',
			plugins: 'format,undo',
			toolbar: 'bold,italic,underline|left,center,right,justify|bulletlist,orderedlist|font,size,format|quote|undo,maximize,source',
			height: '300',
			style: '<?php echo JUri::base(); ?>media/com_kinoarhiv/editors/sceditor/themes/content/default.css',
			emoticonsEnabled: false
		});

		// Insert username into editor
		$('.cmd-insert-username').click(function(){
			var username = $(this).text();

			sceditor.instance(editor_input).focus().insert('<strong>' + username + '</strong><br />');
		});

		// Insert cite into editor
		$('.cmd-insert-quote').click(function(e){
			e.preventDefault();

			var review = $(this).closest('.review-row');
			var quoted_text = review.find('.review').html(),
				quoted_link = review.find('.review-title a.permalink').attr('href'),
				username = review.find('.review-title span.username').text();

			sceditor.instance(editor_input).focus().insert(
				'<a href="' + quoted_link + '"><strong>' + username + '</strong><?php echo JText::_('COM_KA_REVIEWS_QUOTEWROTE'); ?>:</a>'
					+ '<br /><blockquote cite="' + quoted_link + '">' + quoted_text + '</blockquote><br />'
			);
		});

		$('form.editor').submit(function(e){
			var editor_inst = sceditor.instance(editor_input),
				editor_text = editor_inst.val(),
				min_length = <?php echo (int) $params->get('reviews_length_min'); ?>,
				max_length = <?php echo (int) $params->get('reviews_length_max'); ?>,
				submit = $('input[type="submit"]', this);

			editor_inst.readOnly(true);
			submit.attr('disabled', true);

			if (editor_text.length < min_length || editor_text.length > max_length) {
				Aurora.message(
					[{
						text: '<?php echo JText::sprintf(
							JText::_('COM_KA_EDITOR_EMPTY'),
							(int) $params->get('reviews_length_min'),
							(int) $params->get('reviews_length_max')
						); ?>',
						type: 'alert'
					}],
					'#review-form',
					{replace: true}
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
	<input type="hidden" name="view" value="<?php echo $displayData->view; ?>"/>
	<input type="hidden" name="return" value="<?php echo base64_encode('view=' . $displayData->view . '&id=' . (int) $displayData->id); ?>"/>
	<br/>
	<input type="submit" class="btn btn-primary uk-button uk-button-primary" value="<?php echo JText::_('JSUBMIT'); ?>"/>
	<input type="reset" class="btn btn-default uk-button cmd-reset" value="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"/>
</form>
