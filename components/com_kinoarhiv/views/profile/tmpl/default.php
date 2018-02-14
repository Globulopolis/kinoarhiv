<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2018 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

JHtml::_('stylesheet', 'media/com_kinoarhiv/css/colorbox.css');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery.colorbox.min.js');
KAComponentHelper::getScriptLanguage('jquery.colorbox-', 'media/com_kinoarhiv/js/i18n/colorbox');

JHtml::addIncludePath(JPATH_ROOT . '/components/com_users/helpers/html');
?>
<div class="uk-article ka-content user-profile">
<?php
	echo $this->loadTemplate('tabs');

	$this->addTemplatePath(JPATH_ROOT . '/components/com_users/views/profile/tmpl/');
?>
	<div class="profile<?php echo $this->pageclass_sfx; ?>">
		<?php if ($this->params->get('show_page_heading')) : ?>
			<div class="page-header">
				<h1>
					<?php echo $this->escape($this->params->get('page_heading')); ?>
				</h1>
			</div>
		<?php endif; ?>
		<?php if (JFactory::getUser()->id == $this->data->id) : ?>
			<ul class="btn-toolbar pull-right">
				<li class="btn-group">
					<a class="btn edit_profile" target="_blank" href="<?php echo JRoute::_('index.php?option=com_users&task=profile.edit&user_id=' . (int) $this->data->id); ?>">
						<span class="icon-user"></span>
						<?php echo JText::_('COM_USERS_EDIT_PROFILE'); ?>
					</a>
				</li>
			</ul>
		<?php endif; ?>
		<?php echo $this->loadTemplate('core'); ?>
		<?php echo $this->loadTemplate('params'); ?>
		<?php echo $this->loadTemplate('custom'); ?>
	</div>
</div>
