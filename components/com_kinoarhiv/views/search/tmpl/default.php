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

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.tabstate');
?>
<div class="uk-article ka-content">
	<div class="row-fluid">
		<div class="span12">
		<?php echo JHtml::_('bootstrap.startTabSet', 'advanced_search', array('active' => 'movies'));
			if ($this->params->get('search_movies_enable')):
				echo JHtml::_(
					'bootstrap.addTab', 'advanced_search', 'movies', '<span class="tab-about"></span>' . JText::_('COM_KA_SEARCH_ADV_MOVIES_TITLE')
				);
					echo $this->loadTemplate('form_movies');
				echo JHtml::_('bootstrap.endTab');
			endif;

			if ($this->params->get('search_names_enable')):
				echo JHtml::_(
					'bootstrap.addTab', 'advanced_search', 'names', '<span class="tab-posters"></span>' . JText::_('COM_KA_SEARCH_ADV_NAMES_TITLE')
				);
					echo $this->loadTemplate('form_names');
				echo JHtml::_('bootstrap.endTab');
			endif;

			if ($this->params->get('search_albums_enable')):
				echo JHtml::_(
					'bootstrap.addTab', 'advanced_search', 'albums', '<span class="tab-sound"></span>' . JText::_('COM_KA_SEARCH_ADV_MUSIC_TITLE')
				);
					echo $this->loadTemplate('form_albums');
				echo JHtml::_('bootstrap.endTab');
			endif;
		 echo JHtml::_('bootstrap.endTabSet'); ?>
		</div>
	</div>
</div>
