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

$image = @getimagesize($this->item->th_poster);

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
				<div class="control-label"><?php echo $this->form->getLabel('title'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('title'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('alias'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('alias'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('fs_alias'); ?></div>
				<div class="controls">
					<div class="input-append">
						<?php echo $this->form->getInput('fs_alias'); ?>
						<?php echo $this->form->getInput('fs_alias_orig'); ?>
						<button class="btn btn-default cmd-get-alias hasTooltip" data-getalias-task="movies.getFilesystemAlias"
								data-getalias-fields='{"name": ".field_title", "alias": ".field_alias"}'
								title="<?php echo JText::_('COM_KA_FIELD_MOVIE_FS_ALIAS_GET'); ?>"><i class="icon-refresh"></i></button>
						<button class="btn btn-default" data-toggle="modal" data-target="#helpAliasModal"><i class="icon-help"></i></button>
					</div>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('genres'); ?></div>
				<div class="controls">
					<?php echo $this->form->getInput('genres'); ?>
					<span class="rel-link"><a href="index.php?option=com_kinoarhiv&task=genres.add" target="_blank"><span class="icon-new"></span></a></span>
				</div>
			</div>
		</fieldset>
	</div>
	<div class="span6">
		<div class="span9">
			<fieldset class="form-horizontal">
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('year'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('year'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('length'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('length'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('isrc'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('isrc'); ?></div>
				</div>
			</fieldset>
		</div>
	</div>
</div>
<div class="row-fluid">
	<div class="span9">
		<fieldset class="form-horizontal">
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('covers_path'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('covers_path'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('covers_path_www'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('covers_path_www'); ?></div>
			</div>
		</fieldset>
	</div>
	<div class="span3">
		<?php if ($this->form->getValue('id') != 0): ?>
			<ul class="thumbnails">
				<li class="span12">
					<div class="thumbnail center">
						<a href="<?php echo $this->item->poster . '?_=' . time(); ?>" class="img-preview">
							<img src="<?php echo $this->item->th_poster . '?_=' . time(); ?>" style="width: <?php echo $width; ?>px; height: <?php echo $height; ?>px;" />
						</a>
						<div class="caption">
							<a class="hasTooltip" data-toggle="modal" data-target="#selectPosterModal" title="<?php echo JText::_('JSELECT'); ?>" style="cursor: pointer;"><span class="icon-pictures"></span></a>
							<a href="#" class="cmd-file-upload hasTooltip" title="<?php echo JText::_('JTOOLBAR_UPLOAD'); ?>"><span class="icon-upload"></span></a>
							<a href="#" class="cmd-file-remove"><span class="icon-delete"></span></a>
						</div>
					</div>
				</li>
			</ul>
		<?php endif; ?>
	</div>
</div>
<div class="row-fluid">
	<div class="span12">
		<fieldset class="form-horizontal">
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('desc'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('desc'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('buy_urls'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('buy_urls'); ?></div>
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
			'url'             => 'index.php?option=com_kinoarhiv&task=mediamanager.upload&format=raw&section=album&type=gallery&tab=1&id=' . $this->id . '&frontpage=1&upload=images',
			'params'          => $this->params,
			'content-type'    => 'cover', // Required to update an image after upload.
			'multi_selection' => false,
			'max_files'       => 1,
			'remote_upload'   => true,
			'remote_url'      => 'index.php?option=com_kinoarhiv&task=mediamanager.uploadRemote&format=json&section=album&type=gallery&tab=1&id=' . $this->id . '&max_files=1&frontpage=1'
		),
		JPATH_COMPONENT
	)
);

$path = JPath::clean(
	$this->params->get('media_music_root') . '/' . $this->form->getValue('fs_alias') . '/' . $this->id . '/'
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
	'selectPosterModal',
	array(
		'title'  => JText::_('COM_KA_MOVIES_GALLERY') . ' - ' . $this->form->getValue('title'),
		'footer' => JLayoutHelper::render('layouts.edit.upload_file_footer', array(), JPATH_COMPONENT),
		'animation' => false,
		'height' => '500',
		'url' => 'index.php?option=com_kinoarhiv&view=mediamanager&section=album&type=gallery&tab=1&id=' . $this->id . '&layout=modal&tmpl=component'
	)
);
