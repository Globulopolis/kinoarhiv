<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('_JEXEC') or die;

if (JFactory::getDocument()->getType() == 'html')
{
	JHtml::_('stylesheet', 'components/com_kinoarhiv/assets/themes/component/' . $this->params->get('ka_theme') . '/css/select.css');
	JHtml::_('stylesheet', 'components/com_kinoarhiv/assets/themes/component/' . $this->params->get('ka_theme') . '/css/bootstrap-datepicker.css');
	JHtml::_('stylesheet', 'components/com_kinoarhiv/assets/themes/component/' . $this->params->get('ka_theme') . '/css/bootstrap-slider.css');
	JHtml::_('script', 'components/com_kinoarhiv/assets/js/select2.min.js');
	KAComponentHelper::getScriptLanguage('select2_locale_', 'js/i18n/select');
	JHtml::_('script', 'components/com_kinoarhiv/assets/js/bootstrap-datepicker.min.js');
	KAComponentHelper::getScriptLanguage('bootstrap-datepicker.', 'js/i18n/bootstrap/datepicker');
	JHtml::_('script', 'components/com_kinoarhiv/assets/js/bootstrap-slider.min.js');
}
else
{
	echo '<style type="text/css">
		@import url("components/com_kinoarhiv/assets/themes/component/' . $this->params->get('ka_theme') . '/css/select.css");
		@import url("components/com_kinoarhiv/assets/themes/component/' . $this->params->get('ka_theme') . '/css/bootstrap-datepicker.css");
		@import url("components/com_kinoarhiv/assets/themes/component/' . $this->params->get('ka_theme') . '/css/bootstrap-slider.css");
	</style>
	<script src="components/com_kinoarhiv/assets/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
	<script src="components/com_kinoarhiv/assets/js/bootstrap-slider.min.js" type="text/javascript"></script>
	<script src="components/com_kinoarhiv/assets/js/select2.min.js" type="text/javascript"></script>' . "\n";
	KAComponentHelper::getScriptLanguage('select2_locale_', 'js/i18n/select', false, false);
	KAComponentHelper::getScriptLanguage('bootstrap-datepicker.', 'js/i18n/bootstrap/datepicker', false, false);
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
