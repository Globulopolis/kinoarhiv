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

use Joomla\Registry\Registry;

$authorised = JFactory::getUser()->getAuthorisedViewLevels();
$view       = JFactory::getApplication()->input->get('view');
?>
<?php if (!empty($displayData)): ?>
	<ul class="tags inline">
		<?php foreach ($displayData as $i => $tag): ?>
			<?php if (in_array($tag->access, $authorised)): ?>
				<?php $tagParams = new Registry($tag->params); ?>
				<?php $linkClass = $tagParams->get('tag_link_class', 'label label-info'); ?>
				<li class="tag-<?php echo $tag->tag_id; ?> tag-list<?php echo $i; ?>" itemprop="keywords">
					<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=' . $view . 's&content=' . $view . 's&' . $view . 's[tags]=' . $tag->tag_id); ?>"
					   class="<?php echo $linkClass; ?>">
						<?php echo $this->escape($tag->title); ?>
					</a>
				</li>
			<?php endif; ?>
		<?php endforeach; ?>
	</ul>
<?php endif;
