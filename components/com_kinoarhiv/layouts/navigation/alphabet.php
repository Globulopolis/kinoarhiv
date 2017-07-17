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

$data = $displayData;
$view = JFactory::getApplication()->input->getWord('view', 'movies');

if (stripos($view, 'movie') !== false)
{
	$param_key = 'movie';
}
elseif (stripos($view, 'name') !== false)
{
	$param_key = 'name';
}
else
{
	return;
}
?>
<div class="alphabet-nav">
	<?php foreach ($data['params']->get($param_key . '_alphabet') as $alphabet): ?>
	<div>
		<?php if (!empty($alphabet->lang)): ?><span class="ab_lang"><?php echo $alphabet->lang; ?><span><?php endif; ?>
		<span class="ab_letters btn-toolbar">
			<span class="btn-group uk-button-group">
				<?php foreach ($alphabet->letters as $letters): ?>
					<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=' . $param_key . 's&letter=' . $letters . '&Itemid=' . $data['itemid']); ?>"
					   class="btn btn-mini btn-default uk-button uk-button-small"><?php echo $letters; ?></a>
				<?php endforeach; ?>
			</span>
		</span>
	</div>
	<?php endforeach; ?>
</div>
<br/>
