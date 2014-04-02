<?php defined('_JEXEC') or die; ?>
<a name="reviews"></a>
<div class="reviews">
	<?php if ($this->params->get('allow_reviews') == 1 && $this->params->get('custom_review_component') != 'default'):
		// JComments
		if ($this->params->get('custom_review_component') == 'jc' && file_exists(JPATH_ROOT.'/components/com_jcomments/jcomments.php')):
			include_once(JPATH_ROOT.'/components/com_jcomments/jcomments.php');
			$jc = new JComments;
			echo $jc::show($this->item->id, 'com_kinoarhiv', $this->escape($this->item->title.$this->item->year_str));
		endif;
	elseif ($this->params->get('allow_reviews') == 1 && $this->params->get('custom_review_component') == 'default'):
		$review_number = $this->pagination->limitstart + 1;

		if ($this->params->get('allow_reviews') == 1 && !$this->user->guest):
			// Default review system
			$cmd_insert_username = ' cmd-insert-username';

			GlobalHelper::loadEditorAssets(); ?>
			<script type="text/javascript">
			//<![CDATA[
				jQuery(document).ready(function($){
					var editor = new wysihtml5.Editor('form_review', {
						toolbar: 'form-editor-toolbar',
						stylesheets: '<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/css/editor.css',
						parserRules: wysihtml5ParserRules
					});

					$('#review-form').submit(function(e){
						editor.disable();
						$('input[type="submit"]', this).attr('disabled', true);

						if (editor.parse(editor.getValue()).length < <?php echo $this->params->get('reviews_length_min'); ?> || editor.parse(editor.getValue()).length > <?php echo $this->params->get('reviews_length_max'); ?>) {
							showMsg($(this), '<?php echo JText::sprintf(JText::_('COM_KA_EDITOR_EMPTY'), $this->params->get('reviews_length_min'), $this->params->get('reviews_length_max')); ?>');
							editor.enable();
							window.setTimeout(function(){
								$('#review-form input[type="submit"]').removeAttr('disabled');
							}, 5000);
							return false;
						}

						return true;
					});

					$('.cmd-insert-username').click(function(){
						var username = $(this).text();

						editor.focus();
						editor.composer.commands.exec('insertHTML', '<strong>' + username + '</strong><br />');
					});
					$('.cmd-insert-quote').click(function(e){
						e.preventDefault();
						var quoted_text = $(this).closest('.review-row').find('.review').html(),
							quoted_link = $(this).closest('.review-row').find('.review-row-title a.permalink').attr('href'),
							username = $(this).closest('.review-row').find('.review-row-title span.username').text();

						editor.focus();
						editor.composer.commands.exec('insertHTML', '<a href="'+ quoted_link +'"><strong>'+ username +'</strong><?php echo JText::_('COM_KA_REVIEWS_QUOTEWROTE'); ?>:</a><br /><blockquote cite="'+ quoted_link +'">'+ quoted_text +'</blockquote><br />');
					});
				});
			//]]>
			</script>
		<?php endif; ?>

		<div class="ui-corner-all ui-widget-header header-small"><?php echo JText::_('COM_KA_REVIEWS'); ?></div>
		<?php if (count($this->items) > 0): ?>
		<div class="content">

			<?php for ($i=0,$n=count($this->items); $i<$n; $i++):
				$review = $this->items[$i];

				if ($review->type == 2) {
					$ui_class = 'ui-state-highlight';
				} elseif ($review->type == 3) {
					$ui_class = 'ui-state-error';
				} else {
					$ui_class = 'ui-state-default';
				} ?>
				<div class="review-row">
					<a name="review-<?php echo $review->id; ?>"></a>
					<div class="review-row-title ui-widget ui-widget-content ui-corner-top <?php echo $ui_class; ?>">
						<span class="number"><?php echo $review_number++; ?>. </span>
						<span class="username<?php echo $cmd_insert_username; ?>"><?php echo !empty($review->name) ? $review->name : $review->username; ?></span>
						<span><a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&id='.$this->item->id.'&review='.$review->id.'&Itemid='.$this->itemid).'#review-'.$review->id; ?>" title="<?php echo JText::_('COM_KA_REVIEWS_PERMALINK'); ?>" class="hasTip permalink"><img src="components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/images/icons/link_16.png" border="0" /></a></span>
						<span class="date"><?php echo $review->created; ?></span>
					</div>
					<?php if (!$this->user->guest): ?>
						<div class="ui-widget ui-widget-content review"><?php echo $review->review; ?></div>
						<div class="ui-widget ui-widget-content ui-corner-bottom footer">
							<a href="#" class="cmd-insert-quote"><?php echo JText::_('COM_KA_REVIEWS_QUOTELINK'); ?></a>
							<?php if ($this->user->authorise('core.delete.reviews', 'com_kinoarhiv') && $review->uid == $this->user->get('id')): ?>
								<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&controller=reviews&task=delete&return=movie&review_id='.$review->id.'&id='.$review->movie_id); ?>" class="cmd-delete-quote" rel="nofollow"><?php echo JText::_('JACTION_DELETE'); ?></a>
							<?php endif; ?>
						</div>
					<?php else: ?>
						<div class="ui-widget ui-widget-content review ui-corner-bottom footer"><?php echo $review->review; ?></div>
					<?php endif; ?>
				</div>
			<?php endfor; ?>

			<div class="pagination bottom">
				<form action="<?php echo htmlspecialchars(JURI::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm" style="clear: both;" autocomplete="off">
					<?php echo $this->pagination->getPagesLinks(); ?><br />
					<?php echo $this->pagination->getResultsCounter(); ?>&nbsp;
					<label for="limit" class="element-invisible"><?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?></label>
					<?php echo $this->pagination->getLimitBox(); ?>
					<input type="hidden" name="limitstart" value="" />
					<input type="hidden" name="task" value="" />
				</form>
			</div>
		</div>
		<?php else: ?>
		<div><?php echo GlobalHelper::showMsg(JText::_('COM_KA_REVIEWS_NO')); ?></div>
		<?php endif; ?>
		<?php if (!$this->user->guest): // Show "Add review" form ?>
			<?php if ($this->params->get('show_reviews') == 1): ?>
			<div style="clear: both;">&nbsp;</div>
			<form action="<?php echo htmlspecialchars(JURI::getInstance()->toString()); ?>" method="post" id="review-form" class="uk-form editor">
				<ul id="form-editor-toolbar">
					<li data-wysihtml5-command="formatBlock" data-wysihtml5-command-value="h1" class="e-btn hasTip" id="h1" title="<?php echo JText::_('COM_KA_EDITOR_H1'); ?>"></li>
					<li data-wysihtml5-command="formatBlock" data-wysihtml5-command-value="h2" class="e-btn hasTip" id="h2" title="<?php echo JText::_('COM_KA_EDITOR_H2'); ?>"></li>
					<li data-wysihtml5-command="formatBlock" data-wysihtml5-command-value="h3" class="e-btn hasTip" id="h3" title="<?php echo JText::_('COM_KA_EDITOR_H3'); ?>"></li>
					<li data-wysihtml5-command="formatBlock" data-wysihtml5-command-value="h4" class="e-btn hasTip" id="h4" title="<?php echo JText::_('COM_KA_EDITOR_H4'); ?>"></li>
					<li data-wysihtml5-command="formatBlock" data-wysihtml5-command-value="h5" class="e-btn hasTip" id="h5" title="<?php echo JText::_('COM_KA_EDITOR_H5'); ?>"></li>
					<li data-wysihtml5-command="formatBlock" data-wysihtml5-command-value="h6" class="e-btn hasTip" id="h6" title="<?php echo JText::_('COM_KA_EDITOR_H6'); ?>"></li>
					<li class="e-btn separator"></li>
					<li data-wysihtml5-command="bold" class="e-btn hasTip" id="bold" title="<?php echo JText::_('COM_KA_EDITOR_BOLD'); ?> CTRL+B"></li>
					<li data-wysihtml5-command="italic" class="e-btn hasTip" id="italic" title="<?php echo JText::_('COM_KA_EDITOR_ITALIC'); ?> CTRL+I"></li>
					<li data-wysihtml5-command="underline" class="e-btn hasTip" id="underline" title="<?php echo JText::_('COM_KA_EDITOR_UNDERLINE'); ?> CTRL+U"></li>
					<li class="e-btn separator"></li>
					<li data-wysihtml5-command="justifyLeft" class="e-btn hasTip" id="justifyLeft" title="<?php echo JText::_('COM_KA_EDITOR_JLEFT'); ?>"></li>
					<li data-wysihtml5-command="justifyCenter" class="e-btn hasTip" id="justifyCenter" title="<?php echo JText::_('COM_KA_EDITOR_JCENTER'); ?>"></li>
					<li data-wysihtml5-command="justifyRight" class="e-btn hasTip" id="justifyRight" title="<?php echo JText::_('COM_KA_EDITOR_JRIGHT'); ?>"></li>
					<li data-wysihtml5-command="justifyFull" class="e-btn hasTip" id="justifyFull" title="<?php echo JText::_('COM_KA_EDITOR_J'); ?>"></li>
					<li class="e-btn separator"></li>
					<li data-wysihtml5-command="insertUnorderedList" class="e-btn hasTip" id="ul" title="<?php echo JText::_('COM_KA_EDITOR_UL'); ?>"></li>
					<li data-wysihtml5-command="insertOrderedList" class="e-btn hasTip" id="ol" title="<?php echo JText::_('COM_KA_EDITOR_OL'); ?>"></li>
					<li class="e-btn separator"></li>
					<li class="e-btn hasTip font" title="<?php echo JText::_('COM_KA_EDITOR_FONT'); ?>">
						<div class="dropdown" data-uk-dropdown="{mode:'click'}">
							<span class="dropdown-toggle" data-toggle="dropdown"></span>
							<ul id="font-type-list" class="dropdown-menu uk-dropdown" role="menu" aria-labelledby="dLabel">
								<li data-wysihtml5-command="fontType" data-wysihtml5-command-value="arial" class="arial">Arial</li>
								<li data-wysihtml5-command="fontType" data-wysihtml5-command-value="courier-new" class="courier-new">Courier New</li>
								<li data-wysihtml5-command="fontType" data-wysihtml5-command-value="georgia" class="georgia">Georgia</li>
								<li data-wysihtml5-command="fontType" data-wysihtml5-command-value="helvetica-neue" class="helvetica-neue">Helvetica Neue</li>
								<li data-wysihtml5-command="fontType" data-wysihtml5-command-value="times-new-roman" class="times-new-roman">Times New Roman</li>
								<li data-wysihtml5-command="fontType" data-wysihtml5-command-value="verdana" class="verdana">Verdana</li>
							</ul>
						</div>
					</li>
					<li class="e-btn hasTip font-size" title="<?php echo JText::_('COM_KA_EDITOR_FONTSIZE'); ?>">
						<div class="dropdown" data-uk-dropdown="{mode:'click'}">
							<span class="dropdown-toggle" data-toggle="dropdown"></span>
							<ul id="font-size-list" class="dropdown-menu uk-dropdown" role="menu" aria-labelledby="dLabel">
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
					<li class="e-btn separator"></li>
					<li data-wysihtml5-command="formatInline" data-wysihtml5-command-value="q" class="e-btn hasTip" id="q" title="<?php echo JText::_('COM_KA_EDITOR_QUOTE'); ?>"></li>
					<li data-wysihtml5-command="formatBlock" data-wysihtml5-command-value="blockquote" class="e-btn hasTip" id="blockquote" title="<?php echo JText::_('COM_KA_EDITOR_BLOCKQUOTE'); ?>"></li>
					<li class="e-btn separator"></li>
					<li data-wysihtml5-command="undo" class="e-btn hasTip" id="undo" title="<?php echo JText::_('COM_KA_EDITOR_UNDO'); ?>"></li>
					<li data-wysihtml5-command="redo" class="e-btn hasTip" id="redo" title="<?php echo JText::_('COM_KA_EDITOR_REDO'); ?>"></li>
					<li class="e-btn separator"></li>
					<li data-wysihtml5-action="change_view" class="e-btn hasTip" id="change_view" title="<?php echo JText::_('COM_KA_EDITOR_HTML'); ?>"></li>
				</ul>
				<p><?php echo $this->form->getInput('review'); ?></p>
				<div class="select-type"><?php echo $this->form->getLabel('type'); ?><?php echo $this->form->getInput('type'); ?></div>

				<div class="clear"></div>
				<?php if ($this->config->get('captcha') != '0'):
					echo $this->form->getInput('captcha');
				endif; ?>
				<br />
				<input type="hidden" name="controller" id="controller" value="reviews" />
				<input type="hidden" name="task" id="task" value="save" />
				<input type="hidden" name="movie_name" value="<?php echo $this->escape($this->item->title.$this->item->year_str); ?>" />
				<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
				<?php echo JHtml::_('form.token'); ?>
				<input type="submit" class="btn btn-primary uk-button uk-button-primary" value="<?php echo JText::_('JSUBMIT'); ?>" />
				<input type="reset" class="btn btn-default uk-button cmd-reset" value="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" />
			</form>
			<?php endif; ?>
		<?php else: ?>
		<br /><div><?php echo GlobalHelper::showMsg(JText::sprintf(JText::_('COM_KA_REVIEWS_AUTHREQUIRED'), '<a href="'.JRoute::_('index.php?option=com_users&view=registration').'">'.JText::_('COM_KA_REGISTER').'</a>', '<a href="'.JRoute::_('index.php?option=com_users&view=login').'">'.JText::_('COM_KA_LOGIN').'</a>')); ?></div>
		<?php endif; ?>
	<?php endif; ?>
</div>
