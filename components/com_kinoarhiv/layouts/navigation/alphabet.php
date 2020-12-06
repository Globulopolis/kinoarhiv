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

/** @var object $displayData */
$data = $displayData;
$view = JFactory::getApplication()->input->getWord('view', 'movies');

if (stripos($view, 'movie') !== false)
{
	$viewName  = 'movie';
	$fieldName = 'title';
}
elseif (stripos($view, 'name') !== false)
{
	$viewName = 'name';
	$fieldName = $viewName;
}
else
{
	return;
}
?>
<div class="alphabet-nav">
	<?php foreach ($data['params']->get($viewName . '_alphabet') as $alphabet): ?>
	<div>
		<?php if (!empty($alphabet->lang)): ?><span class="ab_lang"><?php echo $alphabet->lang; ?><span><?php endif; ?>
		<span class="ab_letters btn-toolbar">
			<span class="btn-group uk-button-group">
				<?php foreach ($alphabet->letters as $letters): ?>
					<noindex><a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=' . $viewName . 's&content=' . $viewName . 's&' . $viewName . 's[' . $fieldName . ']=' . $letters); ?>"
					   class="btn btn-mini btn-default uk-button uk-button-small" rel="noindex, nofollow"><?php echo $letters; ?></a></noindex>
				<?php endforeach; ?>
			</span>
		</span>
	</div>
	<?php endforeach; ?>
</div>
<br/>
