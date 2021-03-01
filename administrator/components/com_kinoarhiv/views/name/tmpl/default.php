<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2018 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select:not(.hasAutocomplete)');
JHtml::_('stylesheet', 'media/com_kinoarhiv/jqueryui/' . $this->params->get('ui_theme') . '/jquery-ui.min.css');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery-ui.min.js');
JHtml::_('stylesheet', 'media/com_kinoarhiv/css/colorbox.css');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery.colorbox.min.js');
KAComponentHelper::getScriptLanguage('jquery.colorbox-', 'media/com_kinoarhiv/js/i18n/colorbox/', true, true);
KAComponentHelperBackend::loadMediamanagerAssets();

$this->input = JFactory::getApplication()->input;
$this->id    = $this->form->getValue('id');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if ((task === 'names.cancel' || task === 'gallery') || document.formvalidator.isValid(document.getElementById('item-form'))) {
			if (task === 'gallery') {
				var tab = (task === 'gallery') ? '&tab=3' : '',
					url = 'index.php?option=com_kinoarhiv&view=mediamanager&section=name&type=' + task + tab + '<?php echo ($this->id != 0) ? '&id=' . $this->id : ''; ?>';

				Kinoarhiv.openWindow(url);

				return false;
			}

			Joomla.submitform(task, document.getElementById('item-form'));
		}
	};

	jQuery(document).ready(function($){
		// Bind 'remove photo' functional
		$('.cmd-file-remove').click(function(e){
			e.preventDefault();

			var item_id  = parseInt($('input[name="image_id"]').val(), 10),
				no_cover = '<?php echo JUri::root(); ?>media/com_kinoarhiv/images/themes/<?php echo $this->params->get('ka_theme'); ?>/no_name_cover_f.png';

			if (isNaN(item_id)) {
				return false;
			}

			if (!confirm('<?php echo JText::_('JTOOLBAR_DELETE', true); ?>?')) {
				return;
			}

			Kinoarhiv.showLoading('show', $('body'));

			$.ajax({
				type: 'POST',
				url: 'index.php?option=com_kinoarhiv&task=mediamanager.removePoster&section=name&type=gallery&tab=3&id=<?php echo $this->id; ?>&item_id[]=' + item_id + '&format=json',
				data: {'<?php echo JSession::getFormToken(); ?>': 1}
			}).done(function(response){
				Aurora.message([{text: response.message ? response.message : $(response).text()}], '#system-message-container', {replace: true});

				$('a.img-preview').attr('href', no_cover);
				$('a.img-preview img').attr({
					src: no_cover,
					width: 128,
					height: 128,
					style: 'width: 128px; height: 128px;'
				});
				Kinoarhiv.showLoading('hide', $('body'));
			}).fail(function (xhr, status, error) {
				Aurora.message([{text: error, type: 'error'}], '#system-message-container', {replace: true});
				Kinoarhiv.showLoading('hide', $('body'));
			});
		});

		// Check if person allready exists in DB
		$('.field_name').blur(function(){
			if (!empty(this.value)) {
				$.ajax({
					type: 'POST',
					url: 'index.php?option=com_kinoarhiv&task=api.data&content=names&multiple=0&data_lang=*&ignore_ids[]=<?php echo $this->id; ?>&format=json&term=' + this.value,
					data: {'<?php echo JSession::getFormToken(); ?>': 1}
				}).done(function(response){
					if (Object.keys(response).length > 0) {
						var _text = '<?php echo JText::_('COM_KA_NAMES_EXISTS', true); ?><br/>';

						$.each(response, function(i, val){
							_text += '<a href="index.php?option=com_kinoarhiv&view=name&task=names.edit&id=' + val.id + '">' +
								Kinoarhiv.formatItemTitle(val.name, val.latin_name, val.date_of_birth, '/') +
							'</a><br/>';
						});

						Aurora.message([{text: _text, type: 'alert'}], '#system-message-container', {replace: true});
					}
				}).fail(function (xhr, status, error) {
					var _error = JSON.parse(xhr.responseText);
					Aurora.message([{text: _error.msg, type: 'error'}], '#system-message-container', {replace: true});
				});
			}
		});
	});
</script>
<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv&id=' . (int) $this->id); ?>" method="post"
	  name="adminForm" id="item-form" class="form-validate" autocomplete="off">
	<div id="j-main-container">
		<div class="row-fluid">
			<div class="span12">
			<?php echo JHtml::_('bootstrap.startTabSet', 'names', array('active' => 'page0')); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'names', 'page0', JText::_('COM_KA_NAMES_TAB_MAIN')); ?>

				<?php echo $this->loadTemplate('info'); ?>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'names', 'page1', JText::_('COM_KA_NAMES_TAB_AWARDS')); ?>

				<?php
				if ($this->id != 0)
				{
					$lang = JFactory::getLanguage();
					$options = array(
						'url'   => JRoute::_('index.php?option=com_kinoarhiv&task=api.data&content=nameAwards&format=json'
							. '&lang=' . substr($lang->getTag(), 0, 2) . '&id=' . $this->id . '&' . JSession::getFormToken() . '=1'),
						'add_url'  => 'index.php?option=com_kinoarhiv&task=names.editNameAwards&item_id=' . $this->id,
						'edit_url' => 'index.php?option=com_kinoarhiv&task=names.editNameAwards&item_id=' . $this->id,
						'del_url'  => 'index.php?option=com_kinoarhiv&task=names.removeNameAwards&format=json&id=' . $this->id,
						'width' => '#j-main-container', 'height' => '#item-form',
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

				<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'names', 'page2', JText::_('COM_KA_NAMES_TAB_META')); ?>

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

				<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'names', 'page3', JText::_('COM_KA_NAMES_TAB_PUB')); ?>

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
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('ordering'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('ordering'); ?></div>
							</div>
						</fieldset>
					</div>
				</div>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'names', 'page4', JText::_('COM_KA_PERMISSIONS_LABEL')); ?>

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
