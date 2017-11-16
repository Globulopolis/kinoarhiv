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
JHtml::_('bootstrap.framework');

if ($params->get('use_cdn', 0) == 1)
{
	$document = JFactory::getDocument();
	$document->addScript('https://cdn.jsdelivr.net/npm/wysihtml@0.6.0-beta1/dist/minified/wysihtml.min.js');
	$document->addScript('https://cdn.jsdelivr.net/npm/wysihtml@0.6.0-beta1/dist/minified/wysihtml.all-commands.min.js');
	$document->addScript('https://cdn.jsdelivr.net/npm/wysihtml@0.6.0-beta1/dist/minified/wysihtml.toolbar.min.js');
}
else
{
	JHtml::_('script', 'media/com_kinoarhiv/editors/wysihtml/wysihtml.min.js');
	JHtml::_('script', 'media/com_kinoarhiv/editors/wysihtml/wysihtml.all-commands.min.js');
	JHtml::_('script', 'media/com_kinoarhiv/editors/wysihtml/wysihtml.toolbar.min.js');
}

JHtml::_('script', 'media/com_kinoarhiv/editors/wysihtml/parser_rules/advanced_custom.js');
JHtml::_('stylesheet', 'media/com_kinoarhiv/editors/wysihtml/themes/default/default.css');
KAComponentHelper::getScriptLanguage('', 'media/com_kinoarhiv/editors/wysihtml/lang/');
?>
<script type="text/javascript">
	jQuery(document).ready(function($){
		var editor = new wysihtml.Editor('form_review', {
			toolbar: document.querySelector('.editor-toolbar'),
			stylesheets: '<?php echo JUri::base(); ?>media/com_kinoarhiv/editors/wysihtml/themes/default/default_editor.css',
			parserRules: wysihtmlParserRules
		}).on('load', function(){
			if (typeof wysihtml_i18n !== 'undefined') {
				var toolbar_class = '.' + $(editor.config.toolbar).attr('class');

				// Destroy Bootstrap tooltips on toolbar buttons only
				$(toolbar_class + ' .hasTooltip').tooltip('destroy');

				$.each($(toolbar_class + ' > li'), function(){
					var $this = $(this);

					if ($this.hasClass('hasDropdown')) {
						$this.attr('title', wysihtml_i18n[$this.data('command')]);
					} else {
						$this.attr(
							'title',
							wysihtml_i18n[$this.data('wysihtml-command-value') || $this.data('wysihtml-command') || $this.data('wysihtml-action')]
						);
					}
				});

				// Reinitialize Bootstrap tooltips
				$(toolbar_class + ' .hasTooltip').tooltip({ container: 'body' });
			}
		});

		// Insert username into editor
		$('.cmd-insert-username').click(function(){
			var username = $(this).text();

			editor.focus();
			editor.composer.commands.exec('insertHTML', '<strong>' + username + '</strong><br />');
		});

		// Insert cite into editor
		$('.cmd-insert-quote').click(function(e){
			e.preventDefault();

			var review = $(this).closest('.review-row');
			var quoted_text = review.find('.review').html(),
				quoted_link = review.find('.review-title a.permalink').attr('href'),
				username = review.find('.review-title span.username').text();

			editor.focus();
			editor.composer.commands.exec(
				'insertHTML',
				'<a href="' + quoted_link + '"><strong>' + username + '</strong><?php echo JText::_('COM_KA_REVIEWS_QUOTEWROTE'); ?>:</a>'
					+ '<br /><blockquote cite="' + quoted_link + '">' + quoted_text + '</blockquote><br />'
			);
		});

		$('form.editor').submit(function(e){
			var editor_text = editor.getValue(),
				min_length = <?php echo (int) $params->get('reviews_length_min'); ?>,
				max_length = <?php echo (int) $params->get('reviews_length_max'); ?>,
				submit_btn = $('input[type="submit"]', this);

			editor.disable();
			submit_btn.attr('disabled', true);

			if (editor.parse(editor_text).length < min_length || editor.parse(editor_text).length > max_length) {
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
					submit_btn.removeAttr('disabled');
				}, 5000);

				return false;
			} else {
				submit_btn.removeAttr('disabled');
			}

			return true;
		});
	});
</script>
<div style="clear: both;">&nbsp;</div>
<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" id="review-form" class="uk-form editor">
	<div class="toolbar-container">
		<ul class="editor-toolbar">
			<li data-wysihtml-command="formatBlock" data-wysihtml-command-value="h1" class="h1 hasTooltip"></li>
			<li data-wysihtml-command="formatBlock" data-wysihtml-command-value="h2" class="h2 hasTooltip"></li>
			<li data-wysihtml-command="formatBlock" data-wysihtml-command-value="h3" class="h3 hasTooltip"></li>
			<li data-wysihtml-command="formatBlock" data-wysihtml-command-value="h4" class="h4 hasTooltip"></li>
			<li data-wysihtml-command="formatBlock" data-wysihtml-command-value="h5" class="h5 hasTooltip"></li>
			<li data-wysihtml-command="formatBlock" data-wysihtml-command-value="h6" class="h6 hasTooltip"></li>
			<li class="separator"></li>
			<li data-wysihtml-command="bold" class="bold hasTooltip"></li>
			<li data-wysihtml-command="italic" class="italic hasTooltip"></li>
			<li data-wysihtml-command="underline" class="underline hasTooltip"></li>
			<li class="separator"></li>
			<li data-wysihtml-command="justifyLeft" class="justifyLeft hasTooltip"></li>
			<li data-wysihtml-command="justifyCenter" class="justifyCenter hasTooltip"></li>
			<li data-wysihtml-command="justifyRight" class="justifyRight hasTooltip"></li>
			<li data-wysihtml-command="justifyFull" class="justifyFull hasTooltip"></li>
			<li class="separator"></li>
			<li data-wysihtml-command="insertUnorderedList" class="ul hasTooltip"></li>
			<li data-wysihtml-command="insertOrderedList" class="ol hasTooltip"></li>
			<li class="separator"></li>
			<li class="font-size hasTooltip hasDropdown" data-command="fontsize">
				<div class="dropdown" data-uk-dropdown="{mode:'click'}">
					<span class="dropdown-toggle" data-toggle="dropdown"></span>
					<ul class="dropdown-menu uk-dropdown font-size-list" role="menu" aria-labelledby="dLabel">
						<li data-wysihtml-command="fontSize" data-wysihtml-command-value="8" class="size-8">8pt</li>
						<li data-wysihtml-command="fontSize" data-wysihtml-command-value="9" class="size-9">9pt</li>
						<li data-wysihtml-command="fontSize" data-wysihtml-command-value="10" class="size-10">10pt</li>
						<li data-wysihtml-command="fontSize" data-wysihtml-command-value="11" class="size-11">11pt</li>
						<li data-wysihtml-command="fontSize" data-wysihtml-command-value="12" class="size-12">12pt</li>
						<li data-wysihtml-command="fontSize" data-wysihtml-command-value="14" class="size-14">14pt</li>
						<li data-wysihtml-command="fontSize" data-wysihtml-command-value="16" class="size-16">16pt</li>
						<li data-wysihtml-command="fontSize" data-wysihtml-command-value="18" class="size-18">18pt</li>
						<li data-wysihtml-command="fontSize" data-wysihtml-command-value="20" class="size-20">20pt</li>
						<li data-wysihtml-command="fontSize" data-wysihtml-command-value="22" class="size-22">22pt</li>
						<li data-wysihtml-command="fontSize" data-wysihtml-command-value="24" class="size-24">24pt</li>
						<li data-wysihtml-command="fontSize" data-wysihtml-command-value="26" class="size-26">26pt</li>
						<li data-wysihtml-command="fontSize" data-wysihtml-command-value="28" class="size-28">28pt</li>
						<li data-wysihtml-command="fontSize" data-wysihtml-command-value="36" class="size-36">36pt</li>
						<li data-wysihtml-command="fontSize" data-wysihtml-command-value="48" class="size-48">48pt</li>
						<li data-wysihtml-command="fontSize" data-wysihtml-command-value="72" class="size-72">72pt</li>
					</ul>
				</div>
			</li>
			<li class="separator"></li>
			<li data-wysihtml-command="formatInline" data-wysihtml-command-value="q" class="q hasTooltip"></li>
			<li data-wysihtml-command="formatBlock" data-wysihtml-command-value="blockquote" class="blockquote hasTooltip"></li>
			<li class="separator"></li>
			<li data-wysihtml-command="undo" class="undo hasTooltip"></li>
			<li data-wysihtml-command="redo" class="redo hasTooltip"></li>
			<li class="separator"></li>
			<li data-wysihtml-action="change_view" class="change_view hasTooltip"></li>
		</ul>
	</div>

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
