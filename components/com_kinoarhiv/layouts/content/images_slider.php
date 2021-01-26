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

/** @var array $displayData */
$params  = $displayData['params'];
$items   = $displayData['items'];
$attribs = $displayData['attribs'];
?>
<?php if (isset($items) && !empty($items)):
	if (($attribs === '' && $params->get('slider') == 1) || $attribs == 1):
		JHtml::_('jquery.framework');
		JHtml::_('stylesheet', 'media/com_kinoarhiv/css/jquery.bxslider.min.css');
		JHtml::_('script', 'media/com_kinoarhiv/js/jquery.bxslider.min.js');
?>
		<script type="text/javascript">
			jQuery(document).ready(function($){
				$('.slider-images').bxSlider({
					pager: false,
					minSlides: <?php echo (int) $params->get('slider_min_item'); ?>,
					maxSlides: <?php echo count($items); ?>,
					slideWidth: <?php echo (int) $params->get('size_x_scr'); ?>,
					slideMargin: 5,
					infiniteLoop: true
				});

				$('.screenshot-slider li a').colorbox({returnFocus: false, maxHeight: '90%', maxWidth: '90%', rel: 'slideGroup', photo: true});
			});
		</script>
		<div class="screenshot-slider">
			<ul class="slider-images">
				<?php foreach ($items as $slide): ?>
					<li>
						<a href="<?php echo $slide->image; ?>" target="_blank" rel="slideGroup">
							<img src="<?php echo $slide->th_image; ?>" width="<?php echo $slide->th_image_width; ?>"
								 height="<?php echo $slide->th_image_height; ?>" alt=""/>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif;
endif;
