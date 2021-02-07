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

$image = @getimagesize($this->items->get('th_poster'));

if ($image === false)
{
	$height = 0;
	$width  = 0;
}
else
{
	$height = $image[1];
	$width  = $image[0];
}
?>
<div class="row-fluid">
	<div class="span6">
		<fieldset class="form-horizontal">
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('name'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('name'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('latin_name'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('latin_name'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('alias'); ?></div>
					<div class="controls">
						<?php echo $this->form->getInput('alias'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('fs_alias'); ?></div>
					<div class="controls">
						<div class="input-append">
							<?php echo $this->form->getInput('fs_alias'); ?>
							<?php echo $this->form->getInput('fs_alias_orig'); ?>
							<button class="btn btn-default cmd-get-alias hasTooltip" data-getalias-task="names.getFilesystemAlias"
									data-getalias-fields='{"name": ".field_name", "latin_name": ".field_latin_name", "alias": ".field_alias"}'
									title="<?php echo JText::_('COM_KA_FIELD_NAME_FS_ALIAS_GET'); ?>"><i class="icon-refresh"></i></button>
							<button class="btn btn-default" data-toggle="modal" data-target="#helpAliasModal"><i class="icon-help"></i></button>
						</div>
					</div>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('birthplace'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('birthplace'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('birthcountry'); ?></div>
				<div class="controls">
					<?php echo $this->form->getInput('birthcountry'); ?>
					<span class="rel-link">
					<a href="index.php?option=com_kinoarhiv&task=countries.add" target="_blank"><span class="icon-new"></span></a>
				</span>
				</div>
			</div>
		</fieldset>
	</div>
	<div class="span6">
		<div class="span9">
			<fieldset class="form-horizontal">
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('date_of_birth'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('date_of_birth'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('date_of_death'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('date_of_death'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('gender'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('gender'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('height'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('height'); ?></div>
				</div>
			</fieldset>
		</div>
		<div class="span3">
			<?php if ($this->id): ?>

				<ul class="thumbnails">
					<li class="span12">
						<div class="thumbnail center">
							<a href="<?php echo $this->items->get('poster') . '?_=' . time(); ?>" class="img-preview">
								<img src="<?php echo $this->items->get('th_poster') . '?_=' . time(); ?>" style="width: <?php echo $width; ?>px; height: <?php echo $height; ?>px;" />
							</a>
							<div class="caption">
								<a class="hasTooltip" data-toggle="modal" data-target="#selectPhotoModal" title="<?php echo JText::_('JSELECT'); ?>" style="cursor: pointer;"><span class="icon-pictures"></span></a>
								<a href="#" class="cmd-file-upload hasTooltip" title="<?php echo JText::_('JTOOLBAR_UPLOAD'); ?>"><span class="icon-upload"></span></a>
								<a href="#" class="cmd-file-remove"><span class="icon-delete"></span></a>
							</div>
						</div>
					</li>
				</ul>

			<?php endif; ?>
		</div>
	</div>
</div>
<div class="row-fluid">
	<div class="span12">
		<fieldset class="form-horizontal">
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('careers'); ?></div>
				<div class="controls">
					<?php echo $this->form->getInput('careers'); ?>
					<span class="rel-link">
					<a href="index.php?option=com_kinoarhiv&task=careers.add" target="_blank"><span class="icon-new"></span></a>
				</span>

					<?php if ($this->id != 0): ?>
						<span class="rel-link"><a href="index.php?option=com_kinoarhiv&view=relations&task=careers&element=names&nid=<?php echo $this->id; ?>" class="hasTooltip" title="<?php echo JText::_('COM_KA_TABLES_RELATIONS'); ?>" target="_blank"><span class="icon-out-2"></span></a></span>
					<?php endif; ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('genres'); ?></div>
				<div class="controls">
					<?php echo $this->form->getInput('genres'); ?>
					<span class="rel-link">
					<a href="index.php?option=com_kinoarhiv&task=genres.add" target="_blank"><span class="icon-new"></span></a>
				</span>

					<?php if ($this->id != 0): ?>
						<span class="rel-link"><a href="index.php?option=com_kinoarhiv&view=relations&task=genres&element=names&nid=<?php echo $this->id; ?>" class="hasTooltip" title="<?php echo JText::_('COM_KA_TABLES_RELATIONS'); ?>" target="_blank"><span class="icon-out-2"></span></a></span>
					<?php endif; ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('desc'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('desc'); ?></div>
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
			'view'            => $this,
			'url'             => 'index.php?option=com_kinoarhiv&task=mediamanager.upload&format=raw&section=name&type=gallery&tab=3&id=' . $this->id . '&frontpage=1&upload=images',
			'params'          => $this->params,
			'content-type'    => 'poster',
			'multi_selection' => false,
			'max_files'       => 1,
			'remote_upload'   => true,
			'remote_url'      => 'index.php?option=com_kinoarhiv&task=mediamanager.uploadRemote&format=json&section=name&type=gallery&tab=3&id=' . $this->id . '&max_files=1&frontpage=1'
		),
		JPATH_COMPONENT
	)
);

$path = JPath::clean(
	$this->params->get('media_actor_photo_root') . '/' . $this->form->getValue('fs_alias') . '/' . $this->id . '/'
);

echo JHtml::_(
	'bootstrap.renderModal',
	'helpAliasModal',
	array(
		'title'  => JText::_('NOTICE'),
		'footer' => '<a class="btn" data-dismiss="modal">' . JText::_('COM_KA_CLOSE') . '</a>'
	),
	'<div class="container-fluid">' . JText::sprintf('COM_KA_FIELD_MOVIE_FS_ALIAS_DESC', $path) . '</div>'
);

echo JHtml::_(
	'bootstrap.renderModal',
	'selectPhotoModal',
	array(
		'title'  => JText::_('COM_KA_MOVIES_GALLERY') . ' - ' . $this->items->get('title'),
		'footer' => JLayoutHelper::render('layouts.edit.upload_file_footer', array(), JPATH_COMPONENT),
		'animation' => false,
		'height' => '500',
		'url' => 'index.php?option=com_kinoarhiv&view=mediamanager&section=name&type=gallery&tab=3&id=' . $this->id . '&layout=modal&tmpl=component'
	)
);
