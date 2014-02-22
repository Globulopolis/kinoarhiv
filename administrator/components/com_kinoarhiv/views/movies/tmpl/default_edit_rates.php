<?php defined('_JEXEC') or die; ?>
<div class="row-fluid">
	<legend><?php echo JText::_('COM_KA_FIELD_MOVIE_RATES'); ?></legend>
	<div class="span7">
		<fieldset class="form-horizontal">
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('mpaa', $this->form_group); ?></div>
				<div class="controls"><?php echo $this->form->getInput('mpaa', $this->form_group); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('age_restrict', $this->form_group); ?></div>
				<div class="controls"><?php echo $this->form->getInput('age_restrict', $this->form_group); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('ua_rate', $this->form_group); ?></div>
				<div class="controls"><?php echo $this->form->getInput('ua_rate', $this->form_group); ?></div>
			</div>
		</fieldset>
	</div>
	<div class="span5">
		<fieldset class="form-horizontal">
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('imdb_votesum', $this->form_group); ?></div>
				<div class="controls"><?php echo $this->form->getInput('imdb_votesum', $this->form_group); ?> <a href="#" id="imdb_vote" class="update-vote hasTip" title="::<?php echo JText::_('JTOOLBAR_REFRESH'); ?>"><img src="components/com_kinoarhiv/assets/images/icons/arrow_refresh_small.png" border="0" /></a></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('imdb_votes', $this->form_group); ?></div>
				<div class="controls"><?php echo $this->form->getInput('imdb_votes', $this->form_group); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('imdb_id', $this->form_group); ?></div>
				<div class="controls"><?php echo $this->form->getInput('imdb_id', $this->form_group); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('kp_votesum', $this->form_group); ?></div>
				<div class="controls"><?php echo $this->form->getInput('kp_votesum', $this->form_group); ?> <a href="#" id="kp_vote" class="update-vote hasTip" title="::<?php echo JText::_('JTOOLBAR_REFRESH'); ?>"><img src="components/com_kinoarhiv/assets/images/icons/arrow_refresh_small.png" border="0" /></a></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('kp_votes', $this->form_group); ?></div>
				<div class="controls"><?php echo $this->form->getInput('kp_votes', $this->form_group); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('kp_id', $this->form_group); ?></div>
				<div class="controls"><?php echo $this->form->getInput('kp_id', $this->form_group); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('rate_fc', $this->form_group); ?></div>
				<div class="controls"><?php echo $this->form->getInput('rate_fc', $this->form_group); ?> <a href="#" id="rt_vote" class="update-vote hasTip" title="::<?php echo JText::_('JTOOLBAR_REFRESH'); ?>"><img src="components/com_kinoarhiv/assets/images/icons/arrow_refresh_small.png" border="0" /></a></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('rottentm_id', $this->form_group); ?></div>
				<div class="controls"><?php echo $this->form->getInput('rottentm_id', $this->form_group); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('rate_sum_loc', $this->form_group); ?></div>
				<div class="controls"><?php echo $this->form->getInput('rate_sum_loc', $this->form_group); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('rate_loc', $this->form_group); ?></div>
				<div class="controls"><?php echo $this->form->getInput('rate_loc', $this->form_group); ?></div>
			</div>
			<div class="control-group">
				<div class="span12"><?php echo JText::_('COM_KA_FIELD_MOVIE_VOTESUMM'); ?> / <?php echo JText::_('COM_KA_FIELD_MOVIE_VOTES'); ?> = <span id="vote">0</span></div>
			</div>
		</fieldset>
	</div>
	<div class="span12">
		<fieldset class="form-horizontal">
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('rate_custom', $this->form_group); ?></div>
				<div class="controls"><?php echo $this->form->getInput('rate_custom', $this->form_group); ?></div>
			</div>
		</fieldset>
	</div>
</div>
