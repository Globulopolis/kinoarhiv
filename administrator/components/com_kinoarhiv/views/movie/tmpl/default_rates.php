<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2018 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;
?>
<div class="row-fluid">
	<div class="span6">
		<fieldset class="form-horizontal ratings-fields">
			<legend><?php echo JText::_('COM_KA_FIELD_MOVIE_RATES'); ?></legend>
			<div class="well well-small success">
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('imdb_votesum'); ?></div>
					<div class="controls">
						<div class="input-append">
							<?php echo $this->form->getInput('imdb_votesum'); ?>
							<div class="btn-group">
								<button class="btn dropdown-toggle" data-toggle="dropdown">
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									<li>
										<a href="#" class="cmd-update-vote" data-source="imdb"><?php echo JText::_('JTOOLBAR_REFRESH'); ?></a>
									</li>
									<li>
										<a href="#" class="cmd-update-vote-image"
										   data-source="imdb"><?php echo JText::_('COM_KA_FIELD_MOVIE_RATES_IMG_UPDATE'); ?></a>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('imdb_votes'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('imdb_votes'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('imdb_id'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('imdb_id'); ?></div>
				</div>
			</div>

			<div class="well well-small success">
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('kp_votesum'); ?></div>
					<div class="controls">
						<div class="input-append">
							<?php echo $this->form->getInput('kp_votesum'); ?>
							<div class="btn-group">
								<button class="btn dropdown-toggle" data-toggle="dropdown">
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									<li>
										<a href="#" class="cmd-update-vote" data-source="kinopoisk"><?php echo JText::_('JTOOLBAR_REFRESH'); ?></a>
									</li>
									<li>
										<a href="#" class="cmd-update-vote-image"
										   data-source="kinopoisk"><?php echo JText::_('COM_KA_FIELD_MOVIE_RATES_IMG_UPDATE'); ?></a>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('kp_votes'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('kp_votes'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('kp_id'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('kp_id'); ?></div>
				</div>
			</div>

			<div class="well well-small success">
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('rate_fc'); ?></div>
					<div class="controls">
						<div class="input-append">
							<?php echo $this->form->getInput('rate_fc'); ?>
							<div class="btn-group">
								<button class="btn dropdown-toggle" data-toggle="dropdown">
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									<li>
										<a href="#" class="cmd-update-vote"
										   data-source="rottentomatoes"><?php echo JText::_('JTOOLBAR_REFRESH'); ?></a>
									</li>
									<!-- This is require additional field with critics in database
									<li>
										<a href="#" class="cmd-update-vote-image"
										   data-source="rottentomatoes"><?php echo JText::_('COM_KA_FIELD_MOVIE_RATES_IMG_UPDATE'); ?></a>
									</li>
									-->
								</ul>
							</div>
						</div>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('rottentm_id'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('rottentm_id'); ?></div>
				</div>
			</div>

			<div class="well well-small success">
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('metacritics'); ?></div>
					<div class="controls">
						<div class="input-append">
							<?php echo $this->form->getInput('metacritics'); ?>
							<div class="btn-group">
								<button class="btn dropdown-toggle" data-toggle="dropdown">
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									<li>
										<a href="#" class="cmd-update-vote" data-source="metacritic"><?php echo JText::_('JTOOLBAR_REFRESH'); ?></a>
									</li>
									<!--  This is require additional field with critics in database
									<li>
										<a href="#" class="cmd-update-vote-image"
										   data-source="metacritic"><?php echo JText::_('COM_KA_FIELD_MOVIE_RATES_IMG_UPDATE'); ?></a>
									</li>
									-->
								</ul>
							</div>
						</div>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('metacritics_id'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('metacritics_id'); ?></div>
				</div>
			</div>

			<div class="well well-small success">
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('myshows_votesum'); ?></div>
					<div class="controls">
						<div class="input-append">
							<?php echo $this->form->getInput('myshows_votesum'); ?>
							<div class="btn-group">
								<button class="btn dropdown-toggle" data-toggle="dropdown">
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									<li>
										<a href="#" class="cmd-update-vote" data-source="myshows"><?php echo JText::_('JTOOLBAR_REFRESH'); ?></a>
									</li>
									<li>
										<a href="#" class="cmd-update-vote-image"
										   data-source="myshows"><?php echo JText::_('COM_KA_FIELD_MOVIE_RATES_IMG_UPDATE'); ?></a>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('myshows_votes'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('myshows_votes'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('myshows_id'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('myshows_id'); ?></div>
				</div>
			</div>

			<div class="well well-small success">
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('rate_sum_loc'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('rate_sum_loc'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('rate_loc'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('rate_loc'); ?></div>
				</div>
				<div class="control-group">
					<div class="span12">
					<?php echo JText::_('COM_KA_FIELD_MOVIE_VOTESUMM'); ?> / <?php echo JText::_('COM_KA_FIELD_MOVIE_VOTES'); ?> = <span id="vote">0</span>
					</div>
				</div>
			</div>
		</fieldset>
	</div>
	<div class="span6">
		<fieldset class="form-horizontal">
			<legend>&nbsp;</legend>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('mpaa'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('mpaa'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('age_restrict'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('age_restrict'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('ua_rate'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('ua_rate'); ?></div>
			</div>
		</fieldset>
	</div>
</div>
<div class="row-fluid">
	<div class="span12">
		<fieldset class="form-horizontal">
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('rate_custom'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('rate_custom'); ?></div>
			</div>
		</fieldset>
	</div>
</div>
