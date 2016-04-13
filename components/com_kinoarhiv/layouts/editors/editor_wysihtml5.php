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

JHtml::_('bootstrap.framework');
JHtml::_('script', 'components/com_kinoarhiv/assets/editors/wysihtml5/editor.rules.advanced.min.js');
KAComponentHelper::getScriptLanguage('', 'editors/wysihtml5/lang/');
JHtml::_('script', 'components/com_kinoarhiv/assets/editors/wysihtml5/editor.min.js');
JHtml::_('stylesheet', 'components/com_kinoarhiv/assets/editors/wysihtml5/ui/default.css');

$params = $displayData->params;
$form   = $displayData->form;
?>
<script type="text/javascript">
	jQuery(document).ready(function($){
		var editor = new wysihtml5.Editor('form_review', {
			toolbar: document.querySelector('.editor-toolbar'),
			stylesheets: '<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/editors/wysihtml5/ui/default_editor.css',
			parserRules: wysihtml5ParserRules
		}).on('load', function(){
			if (typeof wysihtml5i18n !== 'undefined') {
				var toolbar_class = '.' + $(editor.config.toolbar).attr('class');

				// Destroy Bootstrap tooltips on toolbar buttons only
				$(toolbar_class + ' .hasTooltip').tooltip('destroy');

				$.each($(toolbar_class + ' > li'), function(){
					var $this = $(this);

					if ($this.hasClass('hasDropdown')) {
						$this.attr('title', wysihtml5i18n[$this.data('command')]);
					} else {
						$this.attr(
							'title',
							wysihtml5i18n[$this.data('wysihtml5-command-value') || $this.data('wysihtml5-command') || $this.data('wysihtml5-action')]
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
				quoted_link = review.find('.review-row-title a.permalink').attr('href'),
				username = review.find('.review-row-title span.username').text();

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
				submit = $('input[type="submit"]', this);

			editor.disable();
			submit.attr('disabled', true);

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
<form action="<?php echo htmlspecialchars(JURI::getInstance()->toString()); ?>" method="post" id="review-form" class="uk-form editor">
	<div class="toolbar-container">
		<ul class="editor-toolbar">
			<li data-wysihtml5-command="formatBlock" data-wysihtml5-command-value="h1" class="h1 hasTooltip"></li>
			<li data-wysihtml5-command="formatBlock" data-wysihtml5-command-value="h2" class="h2 hasTooltip"></li>
			<li data-wysihtml5-command="formatBlock" data-wysihtml5-command-value="h3" class="h3 hasTooltip"></li>
			<li data-wysihtml5-command="formatBlock" data-wysihtml5-command-value="h4" class="h4 hasTooltip"></li>
			<li data-wysihtml5-command="formatBlock" data-wysihtml5-command-value="h5" class="h5 hasTooltip"></li>
			<li data-wysihtml5-command="formatBlock" data-wysihtml5-command-value="h6" class="h6 hasTooltip"></li>
			<li class="separator"></li>
			<li data-wysihtml5-command="bold" class="bold hasTooltip"></li>
			<li data-wysihtml5-command="italic" class="italic hasTooltip"></li>
			<li data-wysihtml5-command="underline" class="underline hasTooltip"></li>
			<li class="separator"></li>
			<li data-wysihtml5-command="justifyLeft" class="justifyLeft hasTooltip"></li>
			<li data-wysihtml5-command="justifyCenter" class="justifyCenter hasTooltip"></li>
			<li data-wysihtml5-command="justifyRight" class="justifyRight hasTooltip"></li>
			<li data-wysihtml5-command="justifyFull" class="justifyFull hasTooltip"></li>
			<li class="separator"></li>
			<li data-wysihtml5-command="insertUnorderedList" class="ul hasTooltip"></li>
			<li data-wysihtml5-command="insertOrderedList" class="ol hasTooltip"></li>
			<li class="separator"></li>
			<li class="font hasTooltip hasDropdown" data-command="font">
				<div class="dropdown" data-uk-dropdown="{mode:'click'}">
					<span class="dropdown-toggle" data-toggle="dropdown"></span>
					<ul class="dropdown-menu uk-dropdown font-type-list" role="menu" aria-labelledby="dLabel">
						<li data-wysihtml5-command="fontType" data-wysihtml5-command-value="arial" class="arial">Arial</li>
						<li data-wysihtml5-command="fontType" data-wysihtml5-command-value="courier-new" class="courier-new">Courier New</li>
						<li data-wysihtml5-command="fontType" data-wysihtml5-command-value="georgia" class="georgia">Georgia</li>
						<li data-wysihtml5-command="fontType" data-wysihtml5-command-value="helvetica-neue" class="helvetica-neue">Helvetica Neue</li>
						<li data-wysihtml5-command="fontType" data-wysihtml5-command-value="times-new-roman" class="times-new-roman">Times New Roman</li>
						<li data-wysihtml5-command="fontType" data-wysihtml5-command-value="verdana" class="verdana">Verdana</li>
					</ul>
				</div>
			</li>
			<li class="font-size hasTooltip hasDropdown" data-command="fontsize">
				<div class="dropdown" data-uk-dropdown="{mode:'click'}">
					<span class="dropdown-toggle" data-toggle="dropdown"></span>
					<ul class="dropdown-menu uk-dropdown font-size-list" role="menu" aria-labelledby="dLabel">
						<li data-wysihtml5-command="fontSize" data-wysihtml5-command-value="8" class="size-8">8pt</li>
						<li data-wysihtml5-command="fontSize" data-wysihtml5-command-value="9" class="size-9">9pt</li>
						<li data-wysihtml5-command="fontSize" data-wysihtml5-command-value="10" class="size-10">10pt</li>
						<li data-wysihtml5-command="fontSize" data-wysihtml5-command-value="11" class="size-11">11pt</li>
						<li data-wysihtml5-command="fontSize" data-wysihtml5-command-value="12" class="size-12">12pt</li>
						<li data-wysihtml5-command="fontSize" data-wysihtml5-command-value="14" class="size-14">14pt</li>
						<li data-wysihtml5-command="fontSize" data-wysihtml5-command-value="16" class="size-16">16pt</li>
						<li data-wysihtml5-command="fontSize" data-wysihtml5-command-value="18" class="size-18">18pt</li>
						<li data-wysihtml5-command="fontSize" data-wysihtml5-command-value="20" class="size-20">20pt</li>
						<li data-wysihtml5-command="fontSize" data-wysihtml5-command-value="22" class="size-22">22pt</li>
						<li data-wysihtml5-command="fontSize" data-wysihtml5-command-value="24" class="size-24">24pt</li>
						<li data-wysihtml5-command="fontSize" data-wysihtml5-command-value="26" class="size-26">26pt</li>
						<li data-wysihtml5-command="fontSize" data-wysihtml5-command-value="28" class="size-28">28pt</li>
						<li data-wysihtml5-command="fontSize" data-wysihtml5-command-value="36" class="size-36">36pt</li>
						<li data-wysihtml5-command="fontSize" data-wysihtml5-command-value="48" class="size-48">48pt</li>
						<li data-wysihtml5-command="fontSize" data-wysihtml5-command-value="72" class="size-72">72pt</li>
					</ul>
				</div>
			</li>
			<li class="separator"></li>
			<li data-wysihtml5-command="formatInline" data-wysihtml5-command-value="q" class="q hasTooltip"></li>
			<li data-wysihtml5-command="formatBlock" data-wysihtml5-command-value="blockquote" class="blockquote hasTooltip"></li>
			<li class="separator"></li>
			<li data-wysihtml5-command="undo" class="undo hasTooltip"></li>
			<li data-wysihtml5-command="redo" class="redo hasTooltip"></li>
			<li class="separator"></li>
			<li data-wysihtml5-action="change_view" class="change_view hasTooltip"></li>
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
