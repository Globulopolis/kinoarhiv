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
?>
<div class="uk-article ka-content user-profile favorite">
	<?php echo $this->loadTemplate('tabs'); ?>

	<div class="subtabs breadcrumb">
		<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=favorite&tab=movies&Itemid=' . $this->itemid); ?>"
		   class="subtab-movie<?php echo ($this->tab == 'movies') ? ' current' : ''; ?>"><?php echo JText::_('COM_KA_MOVIES'); ?></a>
		<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=favorite&tab=names&Itemid=' . $this->itemid); ?>"
		   class="subtab-name<?php echo ($this->tab == 'names') ? ' current' : ''; ?>"><?php echo JText::_('COM_KA_PERSONS'); ?></a>
		<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=favorite&tab=albums&Itemid=' . $this->itemid); ?>"
		   class="subtab-album<?php echo ($this->tab == 'albums') ? ' current' : ''; ?>"><?php echo JText::_('COM_KA_MUSIC_ALBUMS'); ?></a>
	</div>

	<?php if (count($this->items) > 0): ?>
		<div class="total-favorite"><?php echo JText::_('COM_KA_PROFILE_TOTAL_FAVORITE') . JText::plural('COM_KA_PROFILE_N_TOTAL_ALBUMS', $this->pagination->total); ?></div>

		<form action="<?php JRoute::_('index.php'); ?>" method="post" id="profileForm" autocomplete="off">
			<table class="table table-striped items-list">
				<thead>
					<tr>
						<th></th>
						<th><?php echo JText::_('COM_KA_MUSIC_ALBUM_TITLE'); ?></th>
						<th><?php echo JText::_('JDATE'); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($this->items as $i => $item):
					$title = $this->escape(KAContentHelper::formatItemTitle($item->title, '', $item->year));
				?>
					<tr>
						<td width="2%">
							<input id="cb<?php echo $i; ?>" type="checkbox" value="<?php echo $item->id; ?>"
								   name="ids[]" title="<?php echo JText::_('JSELECT')?>" />
						</td>
						<td>
							<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=album&id=' . $item->id . '&Itemid=' . $this->itemid); ?>"><?php echo $title; ?></a>
						</td>
						<td width="17%">
							<?php echo $item->favorite_added == '0000-00-00 00:00:00' ? 'N/a' : $item->favorite_added; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
				<tfoot>
					<tr>
						<td colspan="3">
							<input type="checkbox" title="<?php echo JText::_('COM_KA_CHECK_ALL'); ?>" value=""
								   name="checkall-toggle" id="checkall-toggle">
							<label for="checkall-toggle"><?php echo JText::_('COM_KA_CHECK_ALL'); ?></label>
						</td>
					</tr>
				</tfoot>
			</table>

			<input type="hidden" name="option" value="com_kinoarhiv"/>
			<input type="hidden" name="view" value="albums"/>
			<input type="hidden" name="task" value="albums.favoriteRemove"/>
			<input type="hidden" name="action" value="delete"/>
			<input type="hidden" name="return" value="<?php echo base64_encode('view=profile&page=favorite&tab=albums'); ?>"/>
			<input type="hidden" name="Itemid" value="<?php echo $this->itemid; ?>"/>
			<?php echo JHtml::_('form.token'); ?>
			<input type="submit" class="btn btn-primary uk-button uk-button-primary" value="<?php echo JText::_('COM_KA_REMOVE_SELECTED'); ?>"/>
		</form>

		<?php
		echo JLayoutHelper::render('layouts.navigation.pagination',
			array('params' => $this->params, 'pagination' => $this->pagination),
			JPATH_COMPONENT
		);
	else: ?>
		<br/>
		<div><?php echo KAComponentHelper::showMsg(JText::_('COM_KA_NO_ITEMS')); ?></div>
	<?php endif; ?>
</div>
