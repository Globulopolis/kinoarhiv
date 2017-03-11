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

use Joomla\String\StringHelper;

if (StringHelper::substr($this->params->get('media_actor_photo_root_www'), 0, 1) == '/')
{
	$poster_url = JUri::root() . StringHelper::substr($this->params->get('media_actor_photo_root_www'), 1) . '/'
		. urlencode($this->form->getValue('fs_alias', $this->form_edit_group)) . '/' . $this->id . '/photo/';
}
else
{
	$poster_url = $this->params->get('media_actor_photo_root_www') . '/' . urlencode($this->form->getValue('fs_alias', $this->form_edit_group))
		. '/' . $this->id . '/photo/';
}
?>
<script type="text/javascript">
	jQuery(document).ready(function($){
		$('#form_name_name, #form_name_latin_name').blur(function(){
			$.each($(this), function(i, el){
				if ($(el).val() != "") {
					$.ajax({
						url: 'index.php?option=com_kinoarhiv&task=ajaxData&element=names&multiple=0&format=json',
						type: 'POST',
						data: { term: $(el).val(), ignore: [<?php echo $this->id; ?>] },
						cache: true
					}).done(function(response){
						if (response.length > 0) {
							showMsg('#system-message-container', '<?php echo JText::_('COM_KA_NAMES_EXISTS'); ?>');
						}
					});
				}
			});
		});

		<?php if ($this->id != 0): ?>
		$('a.cmd-scr-delete').click(function (e) {
			e.preventDefault();

			if (!confirm('<?php echo JText::_('JTOOLBAR_DELETE'); ?>?')) {
				return false;
			}

			blockUI('show');
			$.post($(this).attr('href'), {
				'<?php echo JSession::getFormToken(); ?>': 1,
				'reload': 0
			}, function (response) {

				if (typeof response !== 'object' && response != "") {
					showMsg('#system-message-container', response);
				} else {
					$('img.movie-poster-preview').attr('src', '<?php echo JUri::root(); ?>components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/images/no_movie_cover.png');
					$('img.movie-poster-preview').parent('a').attr('href', '<?php echo JUri::root(); ?>components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/images/no_movie_cover.png');
				}
				blockUI();
			}).fail(function (xhr, status, error) {
				showMsg('#system-message-container', error);
				blockUI();
			});
		});
		<?php endif; ?>

		$('.cmd-alias').click(function(e){
			e.preventDefault();

			var dialog = $('<div id="dialog_alias" title="<?php echo JText::_('NOTICE'); ?>"><p><?php echo $this->params->get('media_actor_photo_root') . '/' . $this->form->getValue('fs_alias', $this->form_edit_group) . '/' . $this->id . '/'; ?><hr /><?php echo JText::_('COM_KA_FIELD_MOVIE_FS_ALIAS_DESC', true); ?><hr /><?php echo JText::_('COM_KA_FIELD_MOVIE_ALIAS_CHANGE_NOTICE', true); ?></p></div>');

			if ($(this).hasClass('info')) {
				$(dialog).dialog({
					modal: true,
					width: 800,
					height: $(window).height() - 100,
					draggable: false,
					close: function(event, ui){
						dialog.remove();
					}
				});
			} else if ($(this).hasClass('get-alias')) {
				$.getJSON('<?php echo JUri::base(); ?>index.php?option=com_kinoarhiv&controller=names&task=getFilesystemAlias&form_name_alias=' + $('#form_name_alias').val() + '&format=json', function(response){
					if (response.success) {
						$('#form_name_fs_alias').val(response.data);
					} else {
						showMsg('#system-message-container', response.message);
					}
				});
			}
		});
	});
</script>
<div class="row-fluid">
	<div class="span6">
		<fieldset class="form-horizontal">
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('name', $this->form_edit_group); ?></div>
				<div class="controls"><?php echo $this->form->getInput('name', $this->form_edit_group); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('latin_name', $this->form_edit_group); ?></div>
				<div class="controls"><?php echo $this->form->getInput('latin_name', $this->form_edit_group); ?></div>
			</div>
			<div class="control-group">
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('alias', $this->form_edit_group); ?></div>
					<div class="controls">
						<?php echo $this->form->getInput('alias', $this->form_edit_group); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('fs_alias', $this->form_edit_group); ?></div>
					<div class="controls">
						<div class="input-append">
							<?php echo $this->form->getInput('fs_alias', $this->form_edit_group); ?>
							<?php echo $this->form->getInput('fs_alias_orig', $this->form_edit_group); ?>
							<button class="btn btn-default cmd-alias get-alias hasTooltip" title="<?php echo JText::_('COM_KA_FIELD_NAME_FS_ALIAS_GET'); ?>"><i class="icon-refresh"></i></button>
							<button class="btn btn-default cmd-alias info"><i class="icon-help"></i></button>
						</div>
					</div>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('careers', $this->form_edit_group); ?></div>
				<div class="controls">
					<?php echo $this->form->getInput('careers', $this->form_edit_group); ?>
					<span class="rel-link">
						<a href="index.php?option=com_kinoarhiv&task=careers.add" target="_blank"><span class="icon-new"></span></a>
					</span>

					<?php if ($this->id != 0): ?>
						<span class="rel-link"><a href="index.php?option=com_kinoarhiv&view=relations&task=careers&element=names&nid=<?php echo $this->id; ?>" class="hasTip" title="<?php echo JText::_('COM_KA_TABLES_RELATIONS'); ?>" target="_blank"><span class="icon-out-2"></span></a></span>
					<?php endif; ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('birthplace', $this->form_edit_group); ?></div>
				<div class="controls"><?php echo $this->form->getInput('birthplace', $this->form_edit_group); ?></div>
			</div>
		</fieldset>
	</div>
	<div class="span6">
		<div class="span9">
			<fieldset class="form-horizontal">
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('date_of_birth', $this->form_edit_group); ?></div>
					<div class="controls"><?php echo $this->form->getInput('date_of_birth', $this->form_edit_group); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('date_of_death', $this->form_edit_group); ?></div>
					<div class="controls"><?php echo $this->form->getInput('date_of_death', $this->form_edit_group); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('gender', $this->form_edit_group); ?></div>
					<div class="controls"><?php echo $this->form->getInput('gender', $this->form_edit_group); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('height', $this->form_edit_group); ?></div>
					<div class="controls"><?php echo $this->form->getInput('height', $this->form_edit_group); ?></div>
				</div>
			</fieldset>
		</div>
		<div class="span3">
			<?php if ($this->id): ?>

			<ul class="thumbnails">
				<li class="span12">
					<div class="thumbnail center">
						<a href="<?php echo $this->items->get('poster'); ?>" class="img-preview">
							<img src="<?php echo $this->items->get('th_poster'); ?>" style="width: 98px; height: 120px;"/>
						</a>
						<div class="caption">
							<a href="#" class="cmd-poster-upload hasTip" title="<?php echo JText::_('JTOOLBAR_UPLOAD'); ?>"><span class="icon-upload"></span></a>
							<a href="index.php?option=com_kinoarhiv&task=mediamanager.remove&section=name&type=gallery&tab=3&id=<?php echo $this->id; ?>&_id[]=<?php echo $this->form->getValue('gid', $this->form_edit_group); ?>&format=json" class="cmd-poster-delete hasTip" title="<?php echo JText::_('JTOOLBAR_DELETE'); ?>"><span class="icon-delete"></span></a>
						</div>
					</div>
				</li>
			</ul>

			<?php endif; ?>
		</div>
		<fieldset class="form-horizontal">
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('birthcountry', $this->form_edit_group); ?></div>
				<div class="controls">
					<?php echo $this->form->getInput('birthcountry', $this->form_edit_group); ?>
					<span class="rel-link">
						<a href="index.php?option=com_kinoarhiv&task=countries.add" target="_blank"><span class="icon-new"></span></a>
					</span>
				</div>
			</div>
		</fieldset>
	</div>
</div>
<div class="row-fluid">
	<div class="span12">
		<fieldset class="form-horizontal">
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('genres', $this->form_edit_group); ?></div>
				<div class="controls">
					<?php echo $this->form->getInput('genres', $this->form_edit_group); ?>
					<span class="rel-link">
						<a href="index.php?option=com_kinoarhiv&task=genres.add" target="_blank"><span class="icon-new"></span></a>
					</span>

					<?php if ($this->id != 0): ?>
						<span class="rel-link"><a href="index.php?option=com_kinoarhiv&view=relations&task=genres&element=names&nid=<?php echo $this->id; ?>" class="hasTip" title="<?php echo JText::_('COM_KA_TABLES_RELATIONS'); ?>" target="_blank"><span class="icon-out-2"></span></a></span>
					<?php endif; ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('desc', $this->form_edit_group); ?></div>
				<div class="controls"><?php echo $this->form->getInput('desc', $this->form_edit_group); ?></div>
			</div>
		</fieldset>
	</div>
</div>

<?php
echo JHtml::_(
	'bootstrap.renderModal',
	'imgModalUpload',
	array(
		'title'  => JText::_('COM_KA_TRAILERS_UPLOAD_IMAGES'),
		'footer' => JLayoutHelper::render('layouts.edit.upload_file_footer', array(), JPATH_COMPONENT)
	),
	JLayoutHelper::render(
		'layouts.edit.upload_image',
		array(
			'view'          => $this,
			'params'        => $this->params,
			'remote_upload' => true,
			'remote_url'    => 'index.php?option=com_kinoarhiv&task=mediamanager.uploadRemote&format=json&section='
				. $this->section . '&type=' . $this->type . '&tab=' . $this->tab . '&id=' . $this->id
		),
		JPATH_COMPONENT
	)
);
