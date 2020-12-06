<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2018 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('JPATH_BASE') or die;
return;
JHtml::_('jquery.framework');
JHtml::_('bootstrap.framework');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery.bootstrap.wizard.min.js');
JHtml::_('script', 'media/com_kinoarhiv/js/parser.min.js');
?>
<div id="wizard">
	<div class="navbar">
		<div class="navbar-inner">
			<div class="container">
				<ul>
					<li><a href="#tab1" data-toggle="tab">Server</a></li>
					<li><a href="#tab2" data-toggle="tab">Second</a></li>
					<li><a href="#tab3" data-toggle="tab">Third</a></li>
					<li><a href="#tab4" data-toggle="tab">Forth</a></li>
				</ul>
			</div>
		</div>
	</div>

	<div class="tab-content">
		<br />

		<div class="tab-pane" id="tab1">
			<form action="">
				<div class="row-fluid">
					<div class="span3">
						<fieldset class="form-horizontal">
							<label class="radio">
								<input type="radio" name="source" id="source1" value="imdb" checked>
								Imdb
							</label>
							<label class="radio">
								<input type="radio" name="source" id="source2" value="kinopoisk">
								Kinopoisk
							</label>
						</fieldset>
					</div>
				</div>
			</form>
		</div>

		<div class="tab-pane" id="tab2">
			2
		</div>

		<div class="tab-pane" id="tab3">
			3
		</div>

		<div class="tab-pane" id="tab4">
			4
		</div>
		<br />

		<div class="btn-group">
			<button class="btn cmd-prev" type="button">&lsaquo; <?php echo JText::_('JPREV'); ?></button>
			<button class="btn btn-success cmd-next" type="button"><?php echo JText::_('JNEXT'); ?> &rsaquo;</button>
		</div>
	</div>
</div>
