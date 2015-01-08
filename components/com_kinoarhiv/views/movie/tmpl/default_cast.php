<?php defined('_JEXEC') or die; ?>
<script src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/jquery.plugin.min.js" type="text/javascript"></script>
<script src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/jquery.more.min.js" type="text/javascript"></script>
<script type="text/javascript">
//<![CDATA[
	jQuery(document).ready(function($){
		$('.actor-desc').more({
			length: <?php echo $this->params->get('limit_text'); ?>,
			moreText: '<?php echo JText::_('COM_KA_READ_MORE'); ?>',
			lessText: '<?php echo JText::_('COM_KA_READ_LESS'); ?>'
		});
		$.each($('.y-poster img'), function(i, obj){
			if ($(obj).width() == 0) {
				var gender = ($(obj).hasClass('f')) ? 'no_name_cover_small_f' : 'no_name_cover_small_m';
				$(obj).attr('src', '<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/images/'+ gender +'.png');
				$(obj).parent().removeClass('y-poster');
			}
		});
	});
//]]>
</script>
<div class="content movie cast">
	<?php if ($this->params->get('use_alphabet') == 1):
		echo $this->loadTemplate('alphabet');
	endif; ?>

	<article class="uk-article">
		<header>
			<h1 class="uk-article-title title">
				<?php if ($this->item->attribs->link_titles === ''): ?>
					<?php if ($this->params->get('link_titles') == 1): ?>
						<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&id='.$this->item->id.'&Itemid='.$this->itemid); ?>" class="brand" title="<?php echo $this->escape($this->item->title.$this->item->year_str); ?>"><?php echo $this->escape($this->item->title.$this->item->year_str); ?></a>
					<?php else: ?>
						<span class="brand"><?php echo $this->escape($this->item->title.$this->item->year_str); ?></span>
					<?php endif; ?>
				<?php elseif ($this->item->attribs->link_titles == 1): ?>
					<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&id='.$this->item->id.'&Itemid='.$this->itemid); ?>" class="brand" title="<?php echo $this->escape($this->item->title.$this->item->year_str); ?>"><?php echo $this->escape($this->item->title.$this->item->year_str); ?></a>
				<?php elseif ($this->item->attribs->link_titles == 0): ?>
					<span class="brand"><?php echo $this->escape($this->item->title.$this->item->year_str); ?></span>
				<?php endif; ?>
			</h1>
		</header>
		<div class="middle-nav clearfix">
			<p class="meta">
				<?php if ($this->item->attribs->show_author === '' && !empty($this->item->username)): ?>
					<?php if ($this->params->get('show_author') == 1): ?>
						<span class="icon-user"></span> <?php echo JText::_('JAUTHOR'); ?>: <?php echo $this->item->username; ?><br />
					<?php endif; ?>
				<?php elseif ($this->item->attribs->show_author == 1 && !empty($this->item->username)): ?>
					<span class="icon-user"></span> <?php echo JText::_('JAUTHOR'); ?>: <?php echo $this->item->username; ?><br />
				<?php endif; ?>

				<?php if ($this->item->attribs->show_create_date === ''): ?>
					<?php if ($this->params->get('show_pubdate') == 1): ?>
						<span class="icon-calendar"></span> <?php echo JText::_('COM_KA_CREATED_DATE_ON'); ?><time pubdate="" datetime="<?php echo $this->item->created; ?>"><?php echo date('j F Y', strtotime($this->item->created)); ?></time>
					<?php endif; ?>
				<?php elseif ($this->item->attribs->show_create_date == 1): ?>
					<span class="icon-calendar"></span> <?php echo JText::_('COM_KA_CREATED_DATE_ON'); ?><time pubdate="" datetime="<?php echo $this->item->created; ?>"><?php echo date('j F Y', strtotime($this->item->created)); ?></time>
				<?php endif; ?>

				<?php
				if ((
						($this->item->attribs->show_create_date === '' && $this->params->get('show_pubdate') == 1) || $this->item->attribs->show_create_date == 1
					) && (
						($this->item->attribs->show_modify_date === '' && $this->params->get('show_moddate') == 1) || $this->item->attribs->show_modify_date == 1
					)):
					echo ' &bull; ';
				endif; ?>

				<?php if ($this->item->attribs->show_modify_date === ''): ?>
					<?php if ($this->params->get('show_moddate') == 1): ?>
						<?php echo JText::_('COM_KA_LAST_UPDATED'); ?><time pubdate="" datetime="<?php echo $this->item->modified; ?>"><?php echo date('j F Y', strtotime($this->item->modified)); ?></time>
					<?php endif; ?>
				<?php elseif ($this->item->attribs->show_modify_date == 1): ?>
					<?php echo JText::_('COM_KA_LAST_UPDATED'); ?><time pubdate="" datetime="<?php echo $this->item->modified; ?>"><?php echo date('j F Y', strtotime($this->item->modified)); ?></time>
				<?php endif; ?>
			</p>
		</div>
		<?php echo $this->loadTemplate('tabs'); ?>
		<div class="info">
			<div>
				<?php if (isset($this->item->careers['crew']) && count($this->item->careers['crew']) > 0):
					for ($i=0, $n=count($this->item->careers['crew']); $i<$n; $i++):
						$career = $this->item->careers['crew'][$i];?>
						<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=cast&id='.$this->item->id).'#'.JFilterOutput::stringURLSafe($career); ?>"><?php echo $career; ?></a><?php if ($i+1 == $n) {
						} else {
							echo ', ';
						} ?>
					<?php endfor;
				endif; ?>
			</div>
			<div>
				<?php if (!empty($this->item->careers['cast'])): ?><a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=cast&id='.$this->item->id).'#'.JFilterOutput::stringURLSafe($this->item->careers['cast']); ?>"><?php echo $this->item->careers['cast']; ?></a><?php endif; ?><?php if (!empty($this->item->careers['dub'])): ?>, <a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=cast&id='.$this->item->id).'#'.JFilterOutput::stringURLSafe($this->item->careers['dub']); ?>"><?php echo $this->item->careers['dub']; ?></a><?php endif; ?>
			</div><br />

			<?php if (count($this->item->crew) > 0):
				foreach ($this->item->crew as $row): ?>
				<div class="ui-corner-all ui-widget-header header-small"><a name="<?php echo JFilterOutput::stringURLSafe($row['career']); ?>"></a><?php echo $row['career']; ?></div>
				<div class="content">
					<?php foreach ($row['items'] as $key=>$name): ?>
					<div class="cast-row<?php echo ($name['y_poster'] != '') ? ' hasPoster' : ''; ?>">
						<div class="cast-row-col-left fullwidth">
							<div class="actor-photo">
								<span class="number"><?php echo $key+1; ?>. </span>
								<span class="photo<?php echo $name['y_poster']; ?>"><img src="<?php echo $name['poster']; ?>" width="64" border="0" class="<?php echo $name['gender'] ? 'm' : 'f'; ?>" /></span>
							</div>
							<div class="actor-info">
								<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&id='.$name['id'].'&Itemid='.$this->itemid); ?>" title="<?php echo $name['name']; ?>"><?php echo $name['name']; ?><?php echo !empty($name['latin_name']) ? ' / '.$name['latin_name'] : ''; ?></a><br />
								<span class="actor-role"><?php echo $name['role']; ?></span>
								<div class="actor-desc"><?php echo $name['desc']; ?></div>
							</div>
						</div>
						<div class="clear"></div>
					</div>
					<?php endforeach; ?>
				</div>
				<?php endforeach;
			endif; ?>

			<?php if (count($this->item->cast) > 0):
				foreach ($this->item->cast as $row): ?>
				<div class="ui-corner-all ui-widget-header header-small"><a name="<?php echo JFilterOutput::stringURLSafe($row['career']); ?>"></a><?php echo $row['career']; ?><?php if (!empty($this->item->dub)): ?><span class="dub"><?php echo JText::_('COM_KA_CAST_DUB'); ?></span><?php endif; ?></div>
				<div class="content">
					<?php foreach ($row['items'] as $key=>$name): ?>
					<div class="cast-row<?php echo ($name['y_poster'] != '' || $name['dub_y_poster'] != '') ? ' hasPoster' : ''; ?>">
						<div class="cast-row-col-left">
							<div class="actor-photo">
								<span class="number"><?php echo $key+1; ?>. </span>
								<span class="photo<?php echo $name['y_poster']; ?>"><img src="<?php echo $name['poster']; ?>" width="64" border="0" class="<?php echo $name['gender'] ? 'm' : 'f'; ?>" /></span>
							</div>
							<div class="actor-info">
								<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&id='.$name['id'].'&Itemid='.$this->itemid); ?>" title="<?php echo $name['name']; ?>"><?php echo $name['name']; ?><?php echo !empty($name['latin_name']) ? ' / '.$name['latin_name'] : ''; ?></a><br />
								<span class="actor-role"><?php echo $name['role']; ?></span>
								<div class="actor-desc"><?php echo $name['desc']; ?></div>
							</div>
						</div>
						<div class="cast-row-col-right">
							<?php if (!empty($name['dub_id'])): ?>
							<div class="actor-dub-photo">
								<span class="photo<?php echo $name['dub_y_poster']; ?>"><img src="<?php echo $name['dub_url_photo']; ?>" width="64" border="0" class="<?php echo $name['dub_gender'] ? 'm' : 'f'; ?>" /></span>
							</div>
							<div class="actor-dub-info">
								<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&id='.$name['dub_id'].'&Itemid='.$this->itemid); ?>" title="<?php echo $name['dub_name']; ?>"><?php echo $name['dub_name']; ?><?php echo !empty($name['dub_latin_name']) ? ' / '.$name['dub_latin_name'] : ''; ?></a>
							</div>
							
							<?php endif; ?>
						</div>
						<div class="clear"></div>
					</div>
					<?php endforeach; ?>
				</div>
				<?php endforeach;
			endif; ?>

			<?php if (count($this->item->dub) > 0):
				foreach ($this->item->dub as $row): ?>
				<div class="ui-corner-all ui-widget-header header-small"><a name="<?php echo JFilterOutput::stringURLSafe($row['career']); ?>"></a><?php echo $row['career']; ?></div>
				<div class="content">
					<?php foreach ($row['items'] as $key=>$name): ?>
					<div class="cast-row<?php echo ($name['y_poster'] != '') ? ' hasPoster' : ''; ?>">
						<div class="cast-row-col-left fullwidth">
							<div class="actor-photo">
								<span class="number"><?php echo $key+1; ?>. </span>
								<span class="photo<?php echo $name['y_poster']; ?>"><img src="<?php echo $name['poster']; ?>" width="64" border="0" class="<?php echo $name['gender'] ? 'm' : 'f'; ?>" /></span>
							</div>
							<div class="actor-info">
								<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&id='.$name['id'].'&Itemid='.$this->itemid); ?>" title="<?php echo $name['name']; ?>"><?php echo $name['name']; ?><?php echo !empty($name['latin_name']) ? ' / '.$name['latin_name'] : ''; ?></a><br />
								<span class="actor-role"><?php echo $name['role']; ?></span>
								<div class="actor-desc"><?php echo $name['desc']; ?></div>
							</div>
						</div>
						<div class="clear"></div>
					</div>
					<?php endforeach; ?>
				</div>
				<?php endforeach;
			endif; ?>

		</div>
	</article>
</div>
