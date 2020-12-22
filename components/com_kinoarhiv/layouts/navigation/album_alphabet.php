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

use Joomla\Utilities\ArrayHelper;

/** @var array $displayData */
$alphabets = $displayData['params']->get('album_alphabet');

// Always convert to array because in Item view we not merge menu parameters and item parameters(this result object to array conversion).
$alphabets = ArrayHelper::fromObject($alphabets);
?>
<div class="alphabet-nav">
	<?php foreach ($alphabets as $alphabet): ?>
	<p>
		<?php if (!empty($alphabet['lang'])): ?><span class="ab_lang"><?php echo $alphabet['lang']; ?></span><?php endif; ?>
		<span class="ab_letters btn-toolbar">
			<span class="btn-group uk-button-group">
				<?php foreach ($alphabet['letters'] as $letters): ?>
					<a href="<?php echo JRoute::_($displayData['url'] . '&albums[title]=' . $letters); ?>"
					   class="btn btn-mini btn-default uk-button uk-button-small" rel="noindex, nofollow"><?php echo $letters; ?></a>
				<?php endforeach; ?>
			</span>
		</span>
	</p>
	<?php endforeach; ?>
</div>
