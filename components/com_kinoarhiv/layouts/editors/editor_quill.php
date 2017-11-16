<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *  
 * @copyright   Copyright (C) 2017 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

$params = $displayData->params;
$form   = $displayData->form;

JHtml::_('jquery.framework');

if ($params->get('use_cdn', 0) == 1)
{
	$document = JFactory::getDocument();
	$document->addScript('https://cdn.jsdelivr.net/npm/quill@1.3.4/dist/quill.min.js');
	$document->addStyleSheet('https://cdn.jsdelivr.net/npm/quill@1.3.4/dist/quill.snow.min.css');
}
else
{
	JHtml::_('script', 'media/com_kinoarhiv/editors/quill/quill.min.js');
	JHtml::_('stylesheet', 'media/com_kinoarhiv/editors/quill/themes/quill.snow.css');
}
?>
<script type="text/javascript">
	jQuery(document).ready(function($){
		var editor = new Quill('#form_review', {
			theme: 'snow',
			modules: {
				toolbar: [
					[{ 'header': [1, 2, 3, 4, 5, 6, false] }],
					[{ 'font': [] }],
					['bold', 'italic', 'underline', 'strike'],
					[{ 'align': [] }],
					[{ 'list': 'ordered'}, { 'list': 'bullet' }],
					['blockquote'],
					[{ 'color': [] }, { 'background': [] }],
					['clean']
				]
			}
		});

		// Insert username into editor
		$('.cmd-insert-username').click(function(){
			var username = $(this).text() + '\n';

			editor.setContents([
				{insert: username, attributes: {bold: true}}
			]);
			editor.focus();
		});

		// Insert cite into editor
		$('.cmd-insert-quote').click(function(e){
			e.preventDefault();

			var review = $(this).closest('.review-row');
			var quoted_text = review.find('.review').html(),
				quoted_link = review.find('.review-title a.permalink').attr('href'),
				username = review.find('.review-title span.username').text();

			editor.clipboard.dangerouslyPasteHTML(
				'<a href="' + quoted_link + '"><strong>' + username + '</strong><?php echo JText::_('COM_KA_REVIEWS_QUOTEWROTE'); ?>:</a>'
				+ '<br /><blockquote cite="' + quoted_link + '">' + quoted_text + '</blockquote><br />\n'
			);
			editor.focus();
		});

		Quill.prototype.getHtml = function() {
			return this.container.querySelector('.ql-editor').innerHTML;
		};

		$('form.editor').submit(function(e){
			var min_length = <?php echo (int) $params->get('reviews_length_min'); ?>,
				max_length = <?php echo (int) $params->get('reviews_length_max'); ?>,
				submit = $('input[type="submit"]', this);

			editor.enable(false);
			submit.attr('disabled', true);
			$('#form_review_raw').val(editor.getHtml());

			if (editor.getLength() < min_length || editor.getLength() > max_length) {
				showMsg(
					$('.cmd-reset', this),
					'<?php echo JText::sprintf(
						JText::_('COM_KA_EDITOR_EMPTY'),
						(int) $params->get('reviews_length_min'),
						(int) $params->get('reviews_length_max')
					); ?>'
				);
				editor.enable();

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
	<div style="margin: 10px 0;">
		<input name="form[review]" id="form_review_raw" type="hidden"/>
		<div id="form_review" style="height: 300px;"></div>
	</div>
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
