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

JHtml::_('behavior.keepalive');
JHtml::_('stylesheet', 'media/com_kinoarhiv/css/colorbox.css');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery.colorbox.min.js');
KAComponentHelper::getScriptLanguage('jquery.colorbox-', 'media/com_kinoarhiv/js/i18n/colorbox/', true, true);
JHtml::_('script', 'media/com_kinoarhiv/js/jquery.plugin.min.js');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery.more.min.js');

$this->input   = JFactory::getApplication()->input;
$this->section = $this->input->get('section', '', 'word');
$this->type    = $this->input->get('type', '', 'word');
$this->tab     = $this->input->get('tab', 0, 'int');
$this->id      = $this->input->get('id', 0, 'int');
$this->layout  = $this->input->get('layoutview', 'list', 'word');
?>
<div id="j-main-container">
<?php if ($this->section == 'movie' || $this->section == 'name' || $this->section == 'album')
{
	if ($this->type == 'gallery')
	{
		echo $this->loadTemplate('gallery');
	}
	elseif ($this->type == 'trailers')
	{
		echo $this->loadTemplate('trailers_list');
	}
}
else
{
	echo 'Wrong \'section\' variable in request!';
} ?>
</div>
