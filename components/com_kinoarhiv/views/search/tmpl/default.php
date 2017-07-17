<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *  
 * @copyright   Copyright (C) 2017 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

if (JFactory::getDocument()->getType() == 'html')
{
	JHtml::_('behavior.formvalidator');
}
else
{
	echo '<style type="text/css">
		@import url("media/com_kinoarhiv/css/select.css");
		@import url("media/com_kinoarhiv/css/bootstrap-slider.css");
	</style>
	<script src="media/com_kinoarhiv/js/bootstrap-slider.min.js" type="text/javascript"></script>
	<script src="media/com_kinoarhiv/js/select2.min.js" type="text/javascript"></script>
	<script src="media/system/js/core.js" type="text/javascript"></script>
	<script src="media/system/js/punycode.js" type="text/javascript"></script>
	<script src="media/system/js/validate.js" type="text/javascript"></script>
	<script src="media/com_kinoarhiv/js/core.min.js" type="text/javascript"></script>' . "\n";
	KAComponentHelper::getScriptLanguage('select2_locale_', 'media/com_kinoarhiv/js/i18n/select', false);
}
?>
<div class="uk-article ka-content">
<?php
if (JFactory::getApplication()->input->get('task', '', 'cmd') == 'movies')
{
	echo $this->loadTemplate('form_movies');
}
elseif (JFactory::getApplication()->input->get('task', '', 'cmd') == 'names')
{
	echo $this->loadTemplate('form_names');
}
else
{
	echo $this->loadTemplate('form_movies');
	echo $this->loadTemplate('form_names');
}
?>
</div>
