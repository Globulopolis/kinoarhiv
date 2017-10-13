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

JHtml::_('jquery.framework');
JHtml::_('stylesheet', 'media/com_kinoarhiv/editors/trumbowyg/ui/trumbowyg.min.css');
JHtml::_('stylesheet', 'media/com_kinoarhiv/editors/trumbowyg/plugins/colors/ui/trumbowyg.colors.min.css');
JHtml::_('script', 'media/com_kinoarhiv/editors/trumbowyg/trumbowyg.min.js');
JHtml::_('script', 'media/com_kinoarhiv/editors/trumbowyg/plugins/colors/trumbowyg.colors.min.js');
KAComponentHelper::getScriptLanguage('', 'media/com_kinoarhiv/editors/trumbowyg/langs/');

$params = $displayData->params;
$form   = $displayData->form;
?>
<script type="text/javascript">
	jQuery(document).ready(function($){
		var editor = $('#form_review').trumbowyg({
			lang: '<?php echo substr(JFactory::getLanguage()->getTag(), 0, 2); ?>',
			btns: [
				['formatting'],
				['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
				['strong', 'em', 'del'],
				['foreColor', 'backColor'],
				['viewHTML'],
				['removeformat'],
				['fullscreen']
			],
			resetCss: true,
			svgPath: '<?php echo JUri::base(); ?>media/com_kinoarhiv/editors/trumbowyg/ui/icons.svg'
		});

		// Insert username into editor
		$('.cmd-insert-username').click(function(){
			var username = $(this).text();

			editor.trumbowyg('html', '<strong>' + username + '</strong><br />');
		});

		// Insert cite into editor
		$('.cmd-insert-quote').click(function(e){
			e.preventDefault();

			var review = $(this).closest('.review-row');
			var quoted_text = review.find('.review').html(),
				quoted_link = review.find('.review-row-title a.permalink').attr('href'),
				username = review.find('.review-row-title span.username').text();

			editor.trumbowyg(
				'html',
				'<a href="' + quoted_link + '"><strong>' + username + '</strong><?php echo JText::_('COM_KA_REVIEWS_QUOTEWROTE'); ?>:</a>'
					+ '<br /><blockquote cite="' + quoted_link + '">' + quoted_text + '</blockquote><br />'
			);
		});

		$('.cmd-reset').click(function(){
			editor.trumbowyg('empty');

			return true;
		});

		$('form.editor').submit(function(e){
			var editor_text = editor.trumbowyg('html'),
				min_length = <?php echo (int) $params->get('reviews_length_min'); ?>,
				max_length = <?php echo (int) $params->get('reviews_length_max'); ?>,
				submit = $('input[type="submit"]', this);

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
<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" id="review-form" class="form-horizontal uk-form editor">
	<div class="editor-container"><?php echo $form->getInput('review'); ?></div>
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
