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
?>
<div id="j-main-container" class="control-panel">
	<a class="btn" href="index.php?option=com_kinoarhiv&view=movies"><span class="icon-play"> </span> <?php echo JText::_('COM_KA_MOVIES_TITLE'); ?></a>
	<a class="btn" href="index.php?option=com_kinoarhiv&view=names"><span class="icon-users"> </span> <?php echo JText::_('COM_KA_NAMES_TITLE'); ?></a>
	<a class="btn" href="index.php?option=com_kinoarhiv&view=genres"><span class="icon-smiley-2"> </span> <?php echo JText::_('COM_KA_GENRES_TITLE'); ?></a>
	<a class="btn" href="index.php?option=com_kinoarhiv&view=countries"><span class="icon-location"> </span> <?php echo JText::_('COM_KA_COUNTRIES_TITLE'); ?></a>
	<a class="btn" href="index.php?option=com_kinoarhiv&view=careers"><span class="icon-address"> </span> <?php echo JText::_('COM_KA_CAREERS_TITLE'); ?></a>
	<a class="btn" href="index.php?option=com_kinoarhiv&view=vendors"><span class="icon-basket"> </span> <?php echo JText::_('COM_KA_VENDORS_TITLE'); ?></a>
	<a class="btn" href="index.php?option=com_kinoarhiv&view=awards"><span class="icon-asterisk"> </span> <?php echo JText::_('COM_KA_AWARDS_TITLE'); ?></a>
	<a class="btn" href="index.php?option=com_kinoarhiv&view=reviews"><span class="icon-comments-2"> </span> <?php echo JText::_('COM_KA_REVIEWS_TITLE'); ?></a>
	<br />
	<a class="btn" href="index.php?option=com_kinoarhiv&view=premieres"><span class="icon-calendar"> </span> <?php echo JText::_('COM_KA_PREMIERES_TITLE'); ?></a>
	<a class="btn" href="index.php?option=com_kinoarhiv&view=releases"><span class="icon-calendar"> </span> <?php echo JText::_('COM_KA_RELEASES_TITLE'); ?></a>
	<br />
	<a class="btn" href="index.php?option=com_kinoarhiv&view=settings"><span class="icon-options"> </span> <?php echo JText::_('COM_KA_SETTINGS_TITLE'); ?></a>
	<a class="btn" href="index.php?option=com_kinoarhiv&view=tools"><span class="icon-wrench"> </span> <?php echo JText::_('COM_KA_TOOLS_TITLE'); ?></a>
	<p class="text-center"><a href="<?php echo $this->component['authorUrl']; ?>" target="_blank"><?php echo $this->component['name'] . ' ' . $this->component['version']; ?></a></p>
</div>
