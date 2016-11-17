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

JHtml::_('behavior.keepalive');

$this->input   = JFactory::getApplication()->input;
$this->section = $this->input->get('section', '', 'word');
$this->type    = $this->input->get('type', '', 'word');
$this->tab     = $this->input->get('tab', 0, 'int');
$this->id      = $this->input->get('id', 0, 'int');
?>
<script type="text/javascript" src="<?php echo JUri::root(); ?>components/com_kinoarhiv/assets/js/jquery.colorbox.min.js"></script>
<?php KAComponentHelper::getScriptLanguage('jquery.colorbox-', false, 'colorbox', true); ?>
<div id="j-main-container">
<?php if ($this->section == 'movie')
{
	if ($this->type == 'gallery')
	{
		echo $this->loadTemplate('movie_gallery_list');
	}
	elseif ($this->type == 'trailers')
	{
		echo $this->loadTemplate('movie_trailers_list');
	}
}
elseif ($this->section == 'name')
{
	if ($this->type == 'gallery')
	{
		echo $this->loadTemplate('name_gallery_list');
	}
}
else
{
	echo 'Wrong \'section\' variable in request!';
} ?>
</div>
