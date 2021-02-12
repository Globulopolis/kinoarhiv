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
					<span class="rel-link">
						<a href="index.php?option=com_kinoarhiv&task=genres.add" target="_blank"><span class="icon-new"></span></a>
					</span>
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
				<div class="controls">
					<div class="input-append">
						<?php echo $this->form->getInput('covers_path'); ?>
						<button class="btn btn-default" data-toggle="modal" data-target="#importAlbumImageModal"><i class="icon-refresh"></i></button>
					</div>
				</div>
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
						<a href="<?php echo $this->item->cover . '?_=' . time(); ?>" class="img-preview">
							<img src="<?php echo $this->item->th_cover . '?_=' . time(); ?>"
								 style="width: <?php echo $this->item->coverWidth; ?>px; height: <?php echo $this->item->coverHeight; ?>px;" />
						</a>
						<div class="caption">
							<a class="hasTooltip" data-toggle="modal" data-target="#selectPosterModal"
							   title="<?php echo JText::_('JSELECT'); ?>" style="cursor: pointer;"><span class="icon-pictures"></span></a>
							<a href="#" class="cmd-file-upload hasTooltip"
							   title="<?php echo JText::_('JTOOLBAR_UPLOAD'); ?>"><span class="icon-upload"></span></a>
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
