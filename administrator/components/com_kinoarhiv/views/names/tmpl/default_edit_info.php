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

$image  = @getimagesize($this->items->get('th_poster'));
$height = $image[1];
$width  = $image[0];
?>
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
						<span class="rel-link"><a href="index.php?option=com_kinoarhiv&view=relations&task=careers&element=names&nid=<?php echo $this->id; ?>" class="hasTooltip" title="<?php echo JText::_('COM_KA_TABLES_RELATIONS'); ?>" target="_blank"><span class="icon-out-2"></span></a></span>
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
						<a href="<?php echo $this->items->get('poster') . '?_=' . time(); ?>" class="img-preview">
							<img src="<?php echo $this->items->get('th_poster') . '?_=' . time(); ?>" style="width: <?php echo $width; ?>px; height: <?php echo $height; ?>px;" />
						</a>
						<div class="caption">
							<a href="#" class="cmd-upload hasTooltip" title="<?php echo JText::_('JTOOLBAR_UPLOAD'); ?>"><span class="icon-upload"></span></a>
							<a href="#" class="cmd-remove-file hasTooltip" title="<?php echo JText::_('JTOOLBAR_DELETE'); ?>"><span class="icon-delete"></span></a>
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
						<span class="rel-link"><a href="index.php?option=com_kinoarhiv&view=relations&task=genres&element=names&nid=<?php echo $this->id; ?>" class="hasTooltip" title="<?php echo JText::_('COM_KA_TABLES_RELATIONS'); ?>" target="_blank"><span class="icon-out-2"></span></a></span>
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
