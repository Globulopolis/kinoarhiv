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

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('stylesheet', 'media/com_kinoarhiv/css/colorbox.css');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery.colorbox.min.js');
KAComponentHelper::getScriptLanguage('jquery.colorbox-', 'media/com_kinoarhiv/js/i18n/colorbox/', true, true);
KAComponentHelperBackend::loadMediamanagerAssets();

$this->input = JFactory::getApplication()->input;
$this->id    = $this->form->getValue('id');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if ((task == 'names.cancel' || task == 'gallery') || document.formvalidator.isValid(document.getElementById('item-form'))) {
			if (task == 'gallery') {
				var tab = (task == 'gallery') ? '&tab=3' : '',
					url = 'index.php?option=com_kinoarhiv&view=mediamanager&section=name&type=' + task + tab + '<?php echo ($this->id != 0) ? '&id=' . $this->id : ''; ?>',
					handler = window.open(url);

				if (!handler) {
					showMsg(
						'#system-message-container',
						KA_vars.language.COM_KA_NEWWINDOW_BLOCKED_A + url + KA_vars.language.COM_KA_NEWWINDOW_BLOCKED_B
					);
				}

				return false;
			}

			Joomla.submitform(task, document.getElementById('item-form'));
		}
	};

	jQuery(document).ready(function($){
		// Bind 'show modal' functional for photo upload
		$('.cmd-file-upload').click(function(e){
			e.preventDefault();

			$('#imgModalUpload').modal('toggle');
		});

		// Bind 'remove photo' functional
		$('.cmd-file-remove').click(function(e){
			e.preventDefault();

			var item_id  = parseInt($('input[name="image_id"]').val(), 10),
				no_cover = '<?php echo JUri::root(); ?>components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/images/no_movie_cover.png';

			if (isNaN(item_id)) {
				return false;
			}

			if (!confirm('<?php echo JText::_('JTOOLBAR_DELETE'); ?>?')) {
				return;
			}

			Kinoarhiv.showLoading('show', $('body'));

			$.ajax({
				type: 'POST',
				url: 'index.php?option=com_kinoarhiv&task=mediamanager.removePoster&section=name&type=gallery&tab=3&id=<?php echo $this->id; ?>&item_id[]=' + item_id + '&format=json',
				data: {'<?php echo JSession::getFormToken(); ?>': 1}
			}).done(function(response){
				showMsg('#system-message-container', response.message ? response.message : $(response).text());

				$('a.img-preview').attr('href', no_cover);
				$('a.img-preview img').attr({
					src: no_cover,
					width: 128,
					height: 128,
					style: 'width: 128px; height: 128px;'
				});
			 	Kinoarhiv.showLoading('hide', $('body'));
			}).fail(function (xhr, status, error) {
				showMsg('#system-message-container', error);
			 	Kinoarhiv.showLoading('hide', $('body'));
			});
		});

		// Check if person allready exists in DB
		$('.field_name').blur(function(){
			if (!empty(this.value)) {
				$.getJSON('index.php?option=com_kinoarhiv&task=api.data&content=names&multiple=0&format=json&data_lang=*&showAll=0&term=' + this.value + '&' + Kinoarhiv.getFormToken() + '=1&ignore_ids[]=<?php echo $this->id; ?>')
				.done(function(response){
					if (Object.keys(response).length > 0) {
						showMsg('#system-message-container', '<?php echo JText::_('COM_KA_NAMES_EXISTS'); ?>');
					}
				});
			}
		});

		// Create filesystem alias
		$('.cmd-get-alias').click(function(e){
			e.preventDefault();

			$.post('index.php?option=com_kinoarhiv&task=names.getFilesystemAlias&format=json',
				{
					'name': $('.field_name').val(),
					'latin_name': $('.field_latin_name').val(),
					'alias': $('.field_alias').val()
				},
				function(response){
					if (response.success) {
						$('.field_fs_alias').val(response.fs_alias);
					} else {
						showMsg('#system-message-container', response.message);
					}
			});
		});
	});
</script>
<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv&id=' . (int) $this->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate" autocomplete="off">
	<div id="j-main-container">
		<div class="row-fluid">
			<div class="span12">
			<?php echo JHtml::_('bootstrap.startTabSet', 'names', array('active' => 'page0')); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'names', 'page0', JText::_('COM_KA_NAMES_TAB_MAIN')); ?>

				<div id="page0">
					<?php echo $this->loadTemplate('info'); ?>
				</div>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'names', 'page1', JText::_('COM_KA_NAMES_TAB_AWARDS')); ?>

				<div id="page1">
					<?php
					if ($this->id != 0)
					{
						$lang = JFactory::getLanguage();
						$options = array(
							'url'   => JRoute::_('index.php?option=com_kinoarhiv&task=api.data&content=nameAwards&format=json&showAll=1'
								. '&lang=' . substr($lang->getTag(), 0, 2) . '&id=' . $this->id . '&' . JSession::getFormToken() . '=1'),
							'add_url'  => 'index.php?option=com_kinoarhiv&task=names.editNameAwards&item_id=' . $this->id,
							'edit_url' => 'index.php?option=com_kinoarhiv&task=names.editNameAwards&item_id=' . $this->id,
							'del_url'  => 'index.php?option=com_kinoarhiv&task=names.removeNameAwards&format=json&id=' . $this->id,
							'width' => '#namesContent', 'height' => '#item-form',
							'order' => 'rel.id', 'orderby' => 'desc',
							'idprefix' => 'aw_',
							'rowlist'  => array(5, 10, 15, 20, 25, 30, 50, 100, 200, 500),
							'colModel' => array(
								'JGRID_HEADING_ID' => (object) array(
									'name' => 'id', 'index' => 'rel.id', 'width' => 55, 'title' => false,
									'sorttype' => 'int',
									'searchoptions' => (object) array(
										'sopt' => array('cn', 'eq', 'le', 'ge')
									)
								),
								'COM_KA_FIELD_AW_ID' => (object) array(
									'name' => 'award_id', 'index' => 'rel.award_id', 'width' => 55, 'title' => false,
									'sorttype' => 'int',
									'searchoptions' => (object) array(
										'sopt' => array('cn', 'eq', 'le', 'ge')
									)
								),
								'COM_KA_FIELD_AW_LABEL' => (object) array(
									'name' => 'title', 'index' => 'aw.title', 'width' => 350, 'title' => false,
									'sorttype' => 'text',
									'searchoptions' => (object) array(
										'sopt' => array('cn', 'eq', 'bw', 'ew')
									)
								),
								'COM_KA_FIELD_AW_YEAR' => (object) array(
									'name' => 'year', 'index' => 'rel.year', 'width' => 150, 'title' => false,
									'sorttype' => 'int',
									'searchoptions' => (object) array(
										'sopt' => array('cn', 'eq', 'le', 'ge')
									)
								),
								'COM_KA_FIELD_AW_DESC' => (object) array(
									'name' => 'desc', 'index' => 'rel.desc', 'width' => 350, 'title' => false,
									'sortable' => false,
									'searchoptions' => (object) array(
										'sopt' => array('cn', 'eq', 'bw', 'ew')
									)
								)
							),
							'navgrid' => array(
								'btn' => array(
									'lang' => array(
										'addtext' => JText::_('JTOOLBAR_ADD'), 'edittext' => JText::_('JTOOLBAR_EDIT'),
										'deltext' => JText::_('JTOOLBAR_REMOVE'), 'searchtext' => JText::_('JSEARCH_FILTER'),
										'refreshtext' => JText::_('JTOOLBAR_REFRESH'), 'viewtext' => JText::_('JGLOBAL_PREVIEW')
									)
								)
							)
						);

						echo JLayoutHelper::render('administrator.components.com_kinoarhiv.layouts.edit.grid', $options, JPATH_ROOT);
					}
					else
					{
						echo JText::_('COM_KA_NO_ID');
					}
					?>
				</div>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'names', 'page2', JText::_('COM_KA_NAMES_TAB_META')); ?>

				<div id="page2">
					<div class="row-fluid">
						<div class="span6">
							<fieldset class="form-horizontal">
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('metakey'); ?></div>
									<div class="controls"><?php echo $this->form->getInput('metakey'); ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('metadesc'); ?></div>
									<div class="controls"><?php echo $this->form->getInput('metadesc'); ?></div>
								</div>
							</fieldset>
						</div>
						<div class="span6">
							<fieldset class="form-horizontal">
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('robots'); ?></div>
									<div class="controls"><?php echo $this->form->getInput('robots'); ?></div>
								</div>
							</fieldset>
						</div>
					</div>
				</div>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'names', 'page3', JText::_('COM_KA_NAMES_TAB_PUB')); ?>

				<div id="page3">
					<div class="row-fluid">
						<div class="span6">
							<fieldset class="form-horizontal">
								<?php foreach ($this->form->getFieldset('basic') as $field): ?>
									<div class="control-group">
										<div class="control-label"><?php echo $field->label; ?></div>
										<div class="controls"><?php echo $field->input; ?></div>
									</div>
								<?php endforeach; ?>
							</fieldset>
						</div>
						<div class="span6">
							<fieldset class="form-horizontal">
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('language'); ?></div>
									<div class="controls"><?php echo $this->form->getInput('language'); ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('access'); ?></div>
									<div class="controls"><?php echo $this->form->getInput('access'); ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('state'); ?></div>
									<div class="controls"><?php echo $this->form->getInput('state'); ?></div>
								</div>
							</fieldset>
						</div>
					</div>
				</div>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'names', 'page4', JText::_('COM_KA_PERMISSIONS_LABEL')); ?>

				<div id="page4">
					<div class="row-fluid">
						<div class="span12">
							<fieldset class="form-horizontal">
								<div class="control-group">
									<div class="controls" style="margin-left: 0 !important;">
										<?php echo $this->form->getInput('rules'); ?>
										<input type="hidden" name="jform_title" id="jform_title" value="<?php echo $this->form->getValue('title'); ?>" />
									</div>
								</div>
							</fieldset>
						</div>
					</div>
				</div>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php echo JHtml::_('bootstrap.endTabSet'); ?>
			</div>
		</div>
	</div>

	<?php echo $this->form->getInput('genres_orig') . "\n"; ?>
	<?php echo $this->form->getInput('careers_orig') . "\n"; ?>
	<?php echo $this->form->getInput('id') . "\n"; ?>
	<input type="hidden" name="image_id" value="<?php echo $this->form->getValue('image_id'); ?>" />
	<input type="hidden" name="img_folder" value="<?php echo $this->items->get('img_folder'); ?>" />
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>

<?php
echo JHtml::_(
	'bootstrap.renderModal',
	'parserModal',
	array(
		'title'  => JText::_('COM_KA_PARSER_TOOLBAR_BUTTON'),
		'footer' => JLayoutHelper::render('layouts.parser.footer', array(), JPATH_COMPONENT)
	),
	JLayoutHelper::render('layouts.parser.main', array(), JPATH_COMPONENT)
);