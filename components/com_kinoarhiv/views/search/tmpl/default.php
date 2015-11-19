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

$css = JURI::base() . 'components/com_kinoarhiv/assets/themes/component/' . $this->params->get('ka_theme') . '/css/select.css';
$script = JURI::base() . 'components/com_kinoarhiv/assets/js/select2.min.js';

if (JFactory::getDocument()->getType() == 'html')
{
	JFactory::getDocument()->addHeadLink($css, 'stylesheet', 'rel', array('type' => 'text/css'));
	JHtml::_('script', $script);
	KAComponentHelper::getScriptLanguage('select2_locale_', 'js/i18n/select');
	KAComponentHelper::getScriptLanguage('datepicker-', true, 'ui');
}
else
{
	echo '<style type="text/css"> @import url("' . $css . '"); </style>'."\n";
	echo '<script src="' . $script . '" type="text/javascript"></script>'."\n";
	KAComponentHelper::getScriptLanguage('select2_locale_', 'js/i18n/select', false, false);
}
?>
<div class="uk-article ka-content">
	<?php if (JFactory::getApplication()->input->get('task', '', 'cmd') == 'movies'):
		echo $this->loadTemplate('form_movies');
	elseif (JFactory::getApplication()->input->get('task', '', 'cmd') == 'names'):
		echo $this->loadTemplate('form_names');
	else:
		echo $this->loadTemplate('form_movies');
		echo $this->loadTemplate('form_names');
	endif; ?>
</div>
