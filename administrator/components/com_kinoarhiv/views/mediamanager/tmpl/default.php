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

$input = JFactory::getApplication()->input;
$section = $input->get('section', '', 'word');
$type = $input->get('type', '', 'word');
?>
<script type="text/javascript" src="<?php echo JUri::root(); ?>components/com_kinoarhiv/assets/js/jquery.colorbox.min.js"></script>
<?php KAComponentHelper::getScriptLanguage('jquery.colorbox-', false, 'colorbox', true); ?>
<div id="j-main-container">
<?php if ($section == 'movie')
{
	if ($type == 'gallery')
	{
		echo $this->loadTemplate('movie_gallery_list');
	}
	elseif ($type == 'trailers')
	{
		echo $this->loadTemplate('movie_trailers_list');
	}
	elseif ($type == 'sounds')
	{
		echo $this->loadTemplate('movie_soundtracks_list');
	}
}
elseif ($section == 'name')
{
	if ($type == 'gallery')
	{
		echo $this->loadTemplate('name_gallery_list');
	}
}
else
{
	echo 'Wrong \'section\' variable in request!';
} ?>
</div>
