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

use Joomla\String\StringHelper;

$params = $displayData['params'];
$item   = $displayData['item'];

// Display ratings vertical
$column = isset($displayData['column']) && $displayData['column'] ? true : false;

if (StringHelper::substr($params->get('media_rating_image_root_www'), 0, 1) == '/')
{
	$rating_image_www = JUri::base() . StringHelper::substr($params->get('media_rating_image_root_www'), 1);
}
else
{
	$rating_image_www = $params->get('media_rating_image_root_www');
}

if (!$column)
{
	echo '<div class="separator"></div>';
}
else
{
	echo '<br />';
}
?>
<div class="ratings-frontpage">
	<?php if (!empty($item->rate_custom)): ?>
		<div><?php echo $item->rate_custom; ?></div>
	<?php else:
		if ($params->get('ratings_show_img') == 1): ?>
			<div style="text-align: center; display: inline-block;">
				<?php
				// IMDB rating
				if ($params->get('ratings_img_imdb') != 0 && !empty($item->imdb_id)):
					if (file_exists($params->get('media_rating_image_root') . '/imdb/' . $item->id . '_big.png')): ?>
						<a href="http://www.imdb.com/title/<?php echo $item->imdb_id; ?>/" rel="nofollow" target="_blank"><img src="<?php echo $rating_image_www; ?>/imdb/<?php echo $item->id; ?>_big.png" border="0"/></a>
					<?php endif;
				endif;

				// Kinopoisk rating
				if ($params->get('ratings_img_kp') != 0 && !empty($item->kp_id)): ?>
					<a href="https://www.kinopoisk.ru/film/<?php echo $item->kp_id; ?>/" rel="nofollow" target="_blank">
						<?php if ($params->get('ratings_img_kp_remote') == 0): ?>
							<img src="<?php echo $rating_image_www; ?>/kinopoisk/<?php echo $item->id; ?>_big.png" border="0"/>
						<?php else: ?>
							<img src="https://www.kinopoisk.ru/rating/<?php echo $item->kp_id; ?>.gif" border="0" style="padding-left: 1px;"/>
						<?php endif; ?>
					</a>
				<?php endif;

				// Rottentomatoes rating
				if ($params->get('ratings_img_rotten') != 0 && !empty($item->rottentm_id)):
					if (file_exists($params->get('media_rating_image_root') . '/rottentomatoes/' . $item->id . '_big.png')): ?>
						<a href="https://www.rottentomatoes.com/m/<?php echo $item->rottentm_id; ?>/" rel="nofollow" target="_blank"><img src="<?php echo $rating_image_www; ?>/rottentomatoes/<?php echo $item->id; ?>_big.png" border="0"/></a>
					<?php endif;
				endif;

				if ($params->get('ratings_img_metacritic') != 0 && !empty($item->metacritics_id)):
					if (file_exists($params->get('media_rating_image_root') . '/metacritic/' . $item->id . '_big.png')): ?>
						<a href="http://www.metacritic.com/movie/<?php echo $item->metacritics_id; ?>" rel="nofollow" target="_blank"><img src="<?php echo $rating_image_www; ?>/metacritic/<?php echo $item->id; ?>_big.png" border="0"/></a>
					<?php endif;
				endif; ?>
			</div>
		<?php else:
			if (!empty($item->imdb_votesum) && !empty($item->imdb_votes)): ?>
				<div id="rate-imdb">
					<span class="a"><?php echo JText::_('COM_KA_RATE_IMDB'); ?></span>
					<span class="b">
						<a href="http://www.imdb.com/title/<?php echo $item->imdb_id; ?>/?ref_=fn_al_tt_1" rel="nofollow" target="_blank" title="<?php echo JText::_('COM_KA_RATE_DESC'); ?>"><?php echo $item->imdb_votesum; ?> (<?php echo (int) $item->imdb_votes; ?>)</a>
					</span>
				</div>
			<?php else: ?>
				<div id="rate-imdb">
					<span class="a"><?php echo JText::_('COM_KA_RATE_IMDB'); ?></span> <?php echo JText::_('COM_KA_RATE_NO'); ?>
				</div>
			<?php endif;

			if (!empty($item->kp_votesum) && !empty($item->kp_votes)): ?>
				<div id="rate-kp">
					<span class="a"><?php echo JText::_('COM_KA_RATE_KP'); ?></span>
					<span class="b">
						<a href="https://www.kinopoisk.ru/film/<?php echo $item->kp_id; ?>/" rel="nofollow" target="_blank" title="<?php echo JText::_('COM_KA_RATE_DESC'); ?>"><?php echo $item->kp_votesum; ?> (<?php echo $item->kp_votes; ?>)</a>
					</span>
				</div>
			<?php else: ?>
				<div id="rate-kp">
					<span class="a"><?php echo JText::_('COM_KA_RATE_KP'); ?></span> <?php echo JText::_('COM_KA_RATE_NO'); ?>
				</div>
			<?php endif;

			if (!empty($item->rate_fc)): ?>
				<div id="rate-rt">
					<span class="a"><?php echo JText::_('COM_KA_RATE_RT'); ?></span>
					<span class="b">
						<a href="https://www.rottentomatoes.com/m/<?php echo $item->rottentm_id; ?>/" rel="nofollow" target="_blank"><?php echo $item->rate_fc; ?>%</a>
					</span>
				</div>
			<?php else: ?>
				<div id="rate-rt">
					<span class="a"><?php echo JText::_('COM_KA_RATE_RT'); ?></span> <?php echo JText::_('COM_KA_RATE_NO'); ?>
				</div>
			<?php endif;

			if (!empty($item->metacritics)): ?>
				<div id="rate-mc">
					<span class="a"><?php echo JText::_('COM_KA_RATE_MC'); ?></span>
					<span class="b">
						<a href="http://www.metacritic.com/movie/<?php echo $item->metacritics_id; ?>/" rel="nofollow" target="_blank"><?php echo $item->metacritics; ?>%</a>
					</span></div>
			<?php else: ?>
				<div id="rate-mc">
					<span class="a"><?php echo JText::_('COM_KA_RATE_MC'); ?></span> <?php echo JText::_('COM_KA_RATE_NO'); ?>
				</div>
			<?php endif;
		endif;
	endif; ?>

	<?php if (!$column): ?>
		<div class="local-rt<?php echo $item->rate_loc_label_class; ?>">
			<div class="rateit" data-rateit-value="<?php echo $item->rate_loc_c; ?>" data-rateit-min="0" data-rateit-max="<?php echo (int) $params->get('vote_summ_num'); ?>" data-rateit-ispreset="true" data-rateit-readonly="true"></div>
			&nbsp;<?php echo $item->rate_loc_label; ?>
		</div>
	<?php endif; ?>
</div>
