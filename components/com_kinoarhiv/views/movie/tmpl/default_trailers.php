<?php defined('_JEXEC') or die; ?>
<div class="content movie trailers">
	<article>
		<header>
			<h1 class="title">
				<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&id='.$this->item->id.'&Itemid='.$this->itemid); ?>" class="brand" title="<?php echo $this->escape($this->item->title.$this->item->year_str); ?>"><?php echo $this->escape($this->item->title.$this->item->year_str); ?></a>
			</h1>
		</header>
		<?php echo $this->loadTemplate('tabs'); ?>
		<?php if (isset($this->item->trailers) && count($this->item->trailers) > 0):
			GlobalHelper::loadPlayerAssets(); ?>
		<div class="trailer">
			<?php foreach ($this->item->trailers as $trailer):
				$tracks = json_decode($trailer->filename); ?>

				<h3 class="title title-small">
					<?php echo ($trailer->title == '') ? JText::_('COM_KA_TRAILER') : $trailer->title; ?><?php if ($trailer->duration != '00:00:00'): ?> <img src="components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/images/icons/clock_16.png" border="0"> <?php echo $trailer->duration; ?><?php endif; ?>
				</h3>
			<div class="content center video">
				<?php if ($trailer->embed_code != ''):
					echo $trailer->embed_code;
				else:
					if (count($tracks) > 0): ?>
						<video class="video-js vjs-default-skin vjs-big-play-centered" controls preload="none" poster="<?php echo $trailer->path.$trailer->screenshot; ?>" width="<?php echo $trailer->player_width; ?>" height="<?php echo $trailer->player_height; ?>" data-setup="{&quot;techOrder&quot;: [&quot;html5&quot;, &quot;flash&quot;], &quot;plugins&quot;: {&quot;persistVolume&quot;: {&quot;namespace&quot;: &quot;<?php echo $this->user->get('guest') ? md5('video-js'.$trailer->id) : md5(crc32($this->user->get('id')).$trailer->id); ?>&quot;}}}">
						<?php if (is_object($tracks) && count($tracks) > 0):
							foreach ($tracks as $item): ?>
								<source type="<?php echo $item->type; ?>" src="<?php echo $trailer->path.$item->src; ?>" />
							<?php endforeach;
						endif; ?>
						<?php $subtitles = json_decode($trailer->_subtitles);
						if (is_object($subtitles) && count($subtitles) > 0):
							foreach ($subtitles as $subtitle): ?>
								<track kind="subtitles" src="<?php echo $trailer->path.$subtitle->file; ?>" srclang="<?php echo $subtitle->lang_code; ?>" label="<?php echo $subtitle->lang; ?>"<?php echo $subtitle->default ? ' default' : ''; ?> />
							<?php endforeach;
						endif; ?>
						<?php /*$chapters = json_decode($trailer->_chapters);
						if (is_object($chapters) && count($chapters) > 0): // Chapters is broken in VideoJS 4.x ?>
							<track kind="chapters" src="<?php echo $trailer->path.$chapters->file; ?>" srclang="en" default />
						<?php endif;*/ ?>
						</video>
					<?php endif;
				endif; ?>
			</div>
			<div class="clear"></div>
			<?php endforeach; ?>
		</div>
		<?php else: ?>
		<div><?php echo GlobalHelper::showMsg(JText::_('COM_KA_NO_ITEMS')); ?></div>
		<?php endif; ?>
	</article>
</div>
