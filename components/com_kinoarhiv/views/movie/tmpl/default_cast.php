<?php defined('_JEXEC') or die; ?>
<div class="content movie">
	<article>
		<header>
			<h1 class="title">
				<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&id='.$this->item->id.'&Itemid='.$this->itemid); ?>" class="brand" title="<?php echo $this->escape($this->item->title.$this->item->year_str); ?>"><?php echo $this->escape($this->item->title.$this->item->year_str); ?></a>
			</h1>
		</header>
		<?php echo $this->loadTemplate('tabs'); ?>
		<div class="info">
			<div>
				<?php for ($i=0, $n=count($this->item->careers['crew']); $i<$n; $i++):
					$career = $this->item->careers['crew'][$i];?>
					<a href="#<?php echo JFilterOutput::stringURLSafe($career); ?>"><?php echo $career; ?></a><?php if ($i+1 == $n) {
					} else {
						echo ', ';
					} ?>
				<?php endfor; ?>
			</div>
			<div>
				<a href="#<?php echo JFilterOutput::stringURLSafe($this->item->careers['cast']); ?>"><?php echo $this->item->careers['cast']; ?></a><?php if (isset($this->item->careers['dub']) && count($this->item->careers['dub']) > 0): ?>,&nbsp;<a href="#<?php echo JFilterOutput::stringURLSafe($this->item->careers['dub']); ?>"><?php echo $this->item->careers['dub']; ?></a><?php endif; ?>
			</div><br />
			<?php if (count($this->item->crew) > 0):
				foreach ($this->item->crew as $row): ?>
				<div class="ui-corner-all ui-widget-header header-small"><a name="<?php echo JFilterOutput::stringURLSafe($row['career']); ?>"></a><?php echo $row['career']; ?></div>
				<div class="content">
					<?php foreach ($row['items'] as $key=>$name): ?>
					<div class="cast-row">
						<span class="number"><?php echo $key+1; ?>. </span>
						<span class="photo<?php echo $name['y_poster']; ?>"><img src="<?php echo $name['poster']; ?>" border="0" /></span>
						<span class="actor">
							<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&id='.$name['id'].'&Itemid='.$this->itemid); ?>" title="<?php echo $name['name']; ?>"><?php echo $name['name']; ?><?php echo !empty($name['latin_name']) ? ' / '.$name['latin_name'] : ''; ?></a><br />
							<span class="actor-role"><?php echo $name['role']; ?></span>
						</span>
					</div>
					<?php endforeach; ?>
				</div>
				<?php endforeach;
			else: ?>
				<div><?php echo GlobalHelper::showMsg(JText::_('COM_KA_NO_ITEMS')); ?></div>
			<?php endif; ?>
			<?php if (count($this->item->cast) > 0):
				foreach ($this->item->cast as $row): ?>
				<div class="ui-corner-all ui-widget-header header-small"><a name="<?php echo JFilterOutput::stringURLSafe($row['career']); ?>"></a><?php echo $row['career']; ?><span class="dub"><?php echo JText::_('COM_KA_CAST_DUB'); ?></span></div>
				<div class="content">
					<?php foreach ($row['items'] as $key=>$name): ?>
					<div class="cast-row<?php echo ($name['y_poster'] != '' || $name['dub_y_poster'] != '') ? ' hasPoster' : ''; ?>">
						<span class="number"><?php echo $key+1; ?>. </span>
						<span class="photo<?php echo $name['y_poster']; ?>"><img src="<?php echo $name['poster']; ?>" border="0" /></span>
						<span class="actor">
							<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&id='.$name['id'].'&Itemid='.$this->itemid); ?>" title="<?php echo $name['name']; ?>"><?php echo $name['name']; ?><?php echo !empty($name['latin_name']) ? ' / '.$name['latin_name'] : ''; ?></a><br />
							<span class="actor-role"><?php echo $name['role']; ?></span>
						</span>
						<?php if (!empty($name['dub_id'])): ?>
						<span class="actor-dub">
							<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&id='.$name['dub_id'].'&Itemid='.$this->itemid); ?>" title="<?php echo $name['dub_name']; ?>"><?php echo $name['dub_name']; ?><?php echo !empty($name['dub_latin_name']) ? ' / '.$name['dub_latin_name'] : ''; ?></a>
							<span class="photo<?php echo $name['dub_y_poster']; ?>"><img src="<?php echo $name['dub_url_photo']; ?>" border="0" /></span>
						</span>
						<?php endif; ?>
					</div>
					<?php endforeach; ?>
				</div>
				<?php endforeach;
			else: ?>
				<div><?php echo GlobalHelper::showMsg(JText::_('COM_KA_NO_ITEMS')); ?></div>
			<?php endif; ?>
			<?php if (count($this->item->dub) > 0):
				foreach ($this->item->dub as $row): ?>
				<div class="ui-corner-all ui-widget-header header-small"><a name="<?php echo JFilterOutput::stringURLSafe($row['career']); ?>"></a><?php echo $row['career']; ?></div>
				<div class="content">
					<?php foreach ($row['items'] as $key=>$name): ?>
					<div class="cast-row">
						<span class="number"><?php echo $key+1; ?>. </span>
						<span class="photo<?php echo $name['y_poster']; ?>"><img src="<?php echo $name['poster']; ?>" border="0" /></span>
						<span class="actor">
							<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&id='.$name['id'].'&Itemid='.$this->itemid); ?>" title="<?php echo $name['name']; ?>"><?php echo $name['name']; ?><?php echo !empty($name['latin_name']) ? ' / '.$name['latin_name'] : ''; ?></a><br />
							<span class="actor-role"><?php echo $name['role']; ?></span>
						</span>
					</div>
					<?php endforeach; ?>
				</div>
				<?php endforeach;
			else: ?>
				<div><?php echo GlobalHelper::showMsg(JText::_('COM_KA_NO_ITEMS')); ?></div>
			<?php endif; ?>
		</div>
	</article>
</div>
