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

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select:not(.hasAutocomplete)');
JHtml::_('formbehavior.chosen', '#jform_tags', null, array('placeholder_text_multiple' => JText::_('JGLOBAL_TYPE_OR_SELECT_SOME_TAGS')));
JHtml::_('stylesheet', 'media/com_kinoarhiv/jqueryui/' . $this->params->get('ui_theme') . '/jquery-ui.min.css');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery-ui.min.js');
JHtml::_('stylesheet', 'media/com_kinoarhiv/css/colorbox.css');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery.colorbox.min.js');
KAComponentHelper::getScriptLanguage('jquery.colorbox-', 'media/com_kinoarhiv/js/i18n/colorbox/', true, true);
KAComponentHelperBackend::loadMediamanagerAssets();

$this->input = JFactory::getApplication()->input;
$this->id    = $this->form->getValue('id');
$navgridOpts = array(
	'btn' => array(
		'lang' => array(
			'addtext'     => JText::_('JTOOLBAR_ADD'), 'edittext' => JText::_('JTOOLBAR_EDIT'),
			'deltext'     => JText::_('JTOOLBAR_REMOVE'), 'searchtext' => JText::_('JSEARCH_FILTER'),
			'refreshtext' => JText::_('JTOOLBAR_REFRESH'), 'viewtext' => JText::_('JGLOBAL_PREVIEW')
		)
	)
);
$token       = JSession::getFormToken();
$languageTag = substr($this->lang->getTag(), 0, 2);
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if ((task === 'movies.cancel' || task === 'gallery' || task === 'trailers')
			|| document.formvalidator.isValid(document.getElementById('item-form'))) {
			if (task === 'gallery') {
				var tab = (task === 'gallery') ? '&tab=2' : '',
					url = 'index.php?option=com_kinoarhiv&view=mediamanager&section=movie&type=' + task + tab
						+ '<?php echo $this->id != 0 ? '&id=' . $this->id : ''; ?>';
				Kinoarhiv.openWindow(url);

				return false;
			} else if (task === 'trailers') {
				Kinoarhiv.openWindow('index.php?option=com_kinoarhiv&view=mediamanager&section=movie&type=' + task + '&id=<?php echo $this->id; ?>');

				return false;
			}

			Joomla.submitform(task, document.getElementById('item-form'));
		}
	};

	jQuery(document).ready(function($){
		// Bind 'remove poster' functional
		$('.cmd-file-remove').click(function(e){
			e.preventDefault();

			var item_id  = parseInt($('input[name="image_id"]').val(), 10),
				no_cover = KA_vars.uri_root + 'media/com_kinoarhiv/images/themes/<?php echo $this->params->get('ka_theme'); ?>/no_movie_cover.png';

			if (isNaN(item_id)) {
				return false;
			}

			if (!confirm('<?php echo JText::_('JTOOLBAR_DELETE'); ?>?')) {
				return;
			}

			Kinoarhiv.showLoading('show', $('body'));

			$.ajax({
				type: 'POST',
				url: 'index.php?option=com_kinoarhiv&task=mediamanager.removePoster&section=movie&type=gallery&tab=2&id=<?php echo $this->id; ?>&item_id[]=' + item_id + '&format=json',
				data: {'<?php echo $token ?>': 1}
			}).done(function(response){
				Aurora.message([{text: response.message ? response.message : $(response).text()}], '#system-message-container', {replace: true});

				$('a.img-preview').attr('href', no_cover);
				$('a.img-preview img').attr({
					src: no_cover,
					width: 128,
					height: 128,
					style: 'width: 128px; height: 128px;'
				});
				Kinoarhiv.showLoading('hide', $('body'));
			}).fail(function (xhr, status, error) {
				Aurora.message([{text: error, type: 'error'}], '#system-message-container', {replace: true});
				Kinoarhiv.showLoading('hide', $('body'));
			});
		});

		// Check if movie allready exists in DB
		$('.field_title').blur(function(){
			if (!empty(this.value)) {
				$.ajax({
					type: 'POST',
					url: 'index.php?option=com_kinoarhiv&task=api.data&content=movies&multiple=0&data_lang=*&ignore_ids[]=<?php echo $this->id; ?>&format=json&term=' + this.value,
					data: {'<?php echo JSession::getFormToken(); ?>': 1}
				}).done(function(response){
					if (Object.keys(response).length > 0) {
						var _text = '<?php echo JText::_('COM_KA_MOVIES_EXISTS', true); ?><br/>';

						$.each(response, function(i, val){
							_text += '<a href="index.php?option=com_kinoarhiv&view=movie&task=movies.edit&id=' + val.id + '">' +
								Kinoarhiv.formatItemTitle(val.title, '', val.year, '/') +
							'</a><br/>';
						});

						Aurora.message([{text: _text, type: 'alert'}], '#system-message-container', {replace: true});
					}
				}).fail(function (xhr, status, error) {
					var _error = JSON.parse(xhr.responseText);
					Aurora.message([{text: _error.msg, type: 'error'}], '#system-message-container', {replace: true});
				});
			}
		});

		// Update total votes
		$('.field_rate_sum_loc, .field_rate_loc').blur(function(){
			var rate_loc = parseInt($('.field_rate_loc').val(), 10),
				votesum = parseInt($('.field_rate_sum_loc').val(), 10);

			if (isNaN(rate_loc) || isNaN(votesum)) {
				$('#vote').text('0');

				return;
			}

			var vote = parseFloat(votesum / rate_loc).toFixed(<?php echo (int) $this->params->get('vote_summ_precision'); ?>);

			if (empty(vote) || empty(rate_loc) || rate_loc === 0) {
				$('#vote').text('0');
			} else {
				$('#vote').text(vote);
			}
		}).trigger('blur');

		// Update rating
		$('.cmd-update-vote').click(function(e){
			e.preventDefault();

			var $this  = $(this),
				source = $this.data('source'),
				id     = $('.field_' + source + '_id').val(),
				key    = 'parser[' + source + '][' + id + ']',
				page   = '',
				sl_ov  = $('body');

			if (empty(id)) {
				return;
			}

			if (source === 'kinopisk') {
				page = '&page=rating';
			}

			Kinoarhiv.showLoading('show', sl_ov);
			Aurora.remove();

			$.ajax({
				url: KA_vars.uri_root + 'index.php?option=com_kinoarhiv&task=api.parser&lang=' +
					 KA_vars.language.tag.slice(0, 2) + '&format=raw&' + key + '[action]=movie.info&' + key + '[data]=rating' + page
			}).done(function(response){
				if (response[source][id]['success'] === false) {
					Kinoarhiv.showLoading('hide', $('body'));
					Aurora.message([{text: response[source][id]['message'], type: 'error'}], '#system-message-container', {replace: true});
				}

				if (typeof response === 'string') {
					response = JSON.parse(response);
				}

				var votes, votesum;

				if (source === 'imdb' || source === 'kinopoisk') {
					votes = response[source][id]['rating']['votes'];
					votesum = response[source][id]['rating']['votesum'];

					$('.field_' + source + '_votesum').val(votesum);
					$('.field_' + source + '_votes').val(votes);
				} else if (source === 'rottentomatoes' || source === 'metacritic') {
					votes = response[source][id]['rating']['critics'];
					votesum = response[source][id]['rating']['score'];

					$('.field_' + source + '_votesum').val(votesum);
				}

				updateRatingImage(parseInt($('.field_id').val(), 10), source, votes, votesum);

				Kinoarhiv.showLoading('hide', sl_ov);
			}).fail(function(xhr, status, error){
				Aurora.message([{text: error, type: 'error'}], '#system-message-container', {replace: true});
				Kinoarhiv.showLoading('hide', sl_ov);
			});
		});

		$('.cmd-update-vote-image').click(function(e){
			e.preventDefault();

			var source = $(this).data('source');
			updateRatingImage(
				parseInt($('.field_id').val(), 10),
				source,
				$('.field_' + source + '_votes').val(),
				$('.field_' + source + '_votesum').val()
			);
		});

		/**
		 * Update rating images.
		 *
		 * @param   {number}  id        Movie ID from component database.
		 * @param   {string}  source    Type of source(server).
		 * @param   {number}  votes     Votes.
		 * @param   {string}  votesum   Rating.
		 *
		 * @return  {void}
		 */
		function updateRatingImage(id, source, votes, votesum) {
			$.ajax({
				url: KA_vars.uri_root + 'index.php?option=com_kinoarhiv&task=api.updateRatingImage&lang=' +
					 KA_vars.language.tag.slice(0, 2) + '&format=raw&id=' + id +
					'&source=' + source + '&votes=' + votes + '&votesum=' + votesum
			}).done(function(response){
				if (typeof response === 'string') {
					response = JSON.parse(response);
				}

				if (response.success) {
					// Show dialog
					var modal = $('#ratingImgModal');

					$('.modal-header h3', modal).text(source).css('text-transform', 'capitalize');
					$('.modal-body', modal).html('<div class="container-fluid"><img src="' + response.image + '"/></div>');
					modal.modal('toggle');
				} else {
					Aurora.message([{text: response.message, type: 'alert'}], '#system-message-container', {replace: true});
				}

				Kinoarhiv.showLoading('hide', $('body'));
			}).fail(function(xhr, status, error){
				Aurora.message([{text: error, type: 'error'}], '#system-message-container', {replace: true});
				Kinoarhiv.showLoading('hide', $('body'));
			});
		}
	});
</script>
<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv&id=' . (int) $this->id); ?>" method="post" name="adminForm"
	  id="item-form" class="form-validate" autocomplete="off">
	<div id="j-main-container">
		<div class="row-fluid">
			<div class="span12">
			<?php echo JHtml::_('bootstrap.startTabSet', 'movies', array('active' => 'page0')); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'movies', 'page0', JText::_('COM_KA_MOVIES_TAB_MAIN')); ?>

					<?php echo $this->loadTemplate('info'); ?>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'movies', 'page1', JText::_('COM_KA_MOVIES_TAB_RATE')); ?>

					<?php echo $this->loadTemplate('rates'); ?>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'movies', 'page2', JText::_('COM_KA_MOVIES_TAB_CAST_CREW')); ?>

				<?php
				if ($this->id != 0)
				{
					echo KAComponentHelper::showMsg(JText::_('COM_KA_SAVE_REQUIRED'), 'alert-info', true);

					$options = array(
						'url'   => JRoute::_('index.php?option=com_kinoarhiv&task=api.data&content=movieCastAndCrew&format=json'
							. '&lang=' . $languageTag . '&id=' . $this->id . '&' . $token . '=1'
						),
						'add_url'  => JRoute::_('index.php?option=com_kinoarhiv&task=movies.editMovieCast&item_id=' . $this->id),
						'edit_url' => JRoute::_('index.php?option=com_kinoarhiv&task=movies.editMovieCast&item_id=' . $this->id),
						'del_url'  => JRoute::_('index.php?option=com_kinoarhiv&task=movies.removeMovieCast&format=json&id=' . $this->id),
						'width'    => '#j-main-container', 'height' => '#item-form',
						'order'    => 't.ordering', 'orderby' => 'asc',
						'idprefix' => 'cc_',
						'grouping' => true,
						'groupingview' => (object) array(
							'groupField'      => array('type'),
							'groupColumnShow' => array(false),
							'groupText'       => array('<b>{0} - {1}' . JText::_('COM_KA_ITEMS_NUM') . '</b>'),
							'groupCollapse'   => false,
							'groupSummary'    => array(false),
							'groupDataSorted' => false
						),
						'rownum'    => 0,
						'pgbuttons' => false,
						'pginput'   => false,
						'colModel'  => array(
							'JGRID_HEADING_ID' => (object) array(
								'name' => 'row_id', 'index' => 't.id', 'width' => 50, 'title' => false,
								'sorttype' => 'int',
								'searchoptions' => (object) array(
									'sopt' => array('cn', 'eq', 'le', 'ge')
								)
							),
							'COM_KA_FIELD_NAME' => (object) array(
								'name' => 'name', 'index' => 'n.name', 'width' => 300, 'title' => false,
								'sorttype' => 'text',
								'searchoptions' => (object) array(
									'sopt' => array('cn', 'eq', 'bw', 'ew')
								)
							),
							'COM_KA_FIELD_NAME_ROLE' => (object) array(
								'name' => 'role', 'index' => 't.role', 'width' => 300, 'title' => false,
								'sorttype' => 'text',
								'searchoptions' => (object) array(
									'sopt' => array('cn', 'eq', 'bw', 'ew')
								)
							),
							'COM_KA_FIELD_NAME_DUB' => (object) array(
								'name' => 'dub_name', 'index' => 'd.name', 'width' => 300, 'title' => false,
								'sorttype' => 'text',
								'searchoptions' => (object) array(
									'sopt' => array('cn', 'eq', 'bw', 'ew')
								)
							),
							'JGRID_HEADING_ORDERING' => (object) array(
								'name' => 'ordering', 'index' => 't.ordering', 'width' => 65, 'title' => false,
								'align' => 'right', 'sortable' => false, 'search' => false
							),
							'' => (object) array(
								'name' => 'type', 'width' => 1, 'sortable' => false, 'search' => false
							)
						),
						'navgrid' => $navgridOpts
					);

					echo JLayoutHelper::render('administrator.components.com_kinoarhiv.layouts.edit.grid', $options, JPATH_ROOT);
				}
				else
				{
					echo JText::_('COM_KA_NO_ID');
				}
				?>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'movies', 'page3', JText::_('COM_KA_MOVIES_TAB_AWARDS')); ?>

				<?php
				if ($this->id != 0)
				{
					$options = array(
						'url'   => JRoute::_('index.php?option=com_kinoarhiv&task=api.data&content=movieAwards&format=json'
							. '&lang=' . $languageTag . '&id=' . $this->id . '&' . $token . '=1'
						),
						'add_url'  => JRoute::_('index.php?option=com_kinoarhiv&task=movies.editMovieAward&item_id=' . $this->id),
						'edit_url' => JRoute::_('index.php?option=com_kinoarhiv&task=movies.editMovieAward&item_id=' . $this->id),
						'del_url'  => JRoute::_('index.php?option=com_kinoarhiv&task=movies.removeMovieAwards&format=json&id=' . $this->id),
						'width' => '#j-main-container', 'height' => '#item-form',
						'order' => 'rel.id', 'orderby' => 'desc',
						'idprefix' => 'aw_',
						'rowlist'  => array(5, 10, 15, 20, 25, 30, 50, 100, 200, 500),
						'colModel' => array(
							'JGRID_HEADING_ID' => (object) array(
								'name' => 'id', 'index' => 'rel.id', 'width' => 60, 'title' => false,
								'sorttype' => 'int',
								'searchoptions' => (object) array(
									'sopt' => array('cn', 'eq', 'le', 'ge')
								)
							),
							'COM_KA_FIELD_AW_ID' => (object) array(
								'name' => 'award_id', 'index' => 'rel.award_id', 'width' => 55, 'title' => false,
								'sorttype' => 'int',
								'searchoptions' => (object) array(
									'sopt' => array('cn', 'eq', 'le', 'ge')
								)
							),
							'COM_KA_FIELD_AW_LABEL' => (object) array(
								'name' => 'title', 'index' => 'aw.title', 'width' => 350, 'title' => false,
								'sorttype' => 'text',
								'searchoptions' => (object) array(
									'sopt' => array('cn', 'eq', 'bw', 'ew')
								)
							),
							'COM_KA_FIELD_AW_YEAR' => (object) array(
								'name' => 'year', 'index' => 'rel.year', 'width' => 100, 'title' => false,
								'sorttype' => 'int',
								'searchoptions' => (object) array(
									'sopt' => array('cn', 'eq', 'le', 'ge')
								)
							),
							'COM_KA_FIELD_AW_DESC' => (object) array(
								'name' => 'desc', 'index' => 'rel.desc', 'width' => 350, 'title' => false,
								'sortable' => false,
								'searchoptions' => (object) array(
									'sopt' => array('cn', 'eq', 'bw', 'ew')
								)
							)
						),
						'navgrid' => $navgridOpts
					);

					echo JLayoutHelper::render('administrator.components.com_kinoarhiv.layouts.edit.grid', $options, JPATH_ROOT);
				}
				else
				{
					echo JText::_('COM_KA_NO_ID');
				}
				?>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'movies', 'page4', JText::_('COM_KA_MOVIES_TAB_PREMIERES')); ?>

				<?php
				if ($this->id != 0)
				{
					$options = array(
						'url'   => JRoute::_('index.php?option=com_kinoarhiv&task=api.data&content=moviePremieres&format=json'
							. '&lang=' . $languageTag . '&id=' . $this->id . '&' . $token . '=1'
						),
						'add_url'  => JRoute::_('index.php?option=com_kinoarhiv&task=movies.editMoviePremiere&item_id=' . $this->id),
						'edit_url' => JRoute::_('index.php?option=com_kinoarhiv&task=movies.editMoviePremiere&item_id=' . $this->id),
						'del_url'  => JRoute::_('index.php?option=com_kinoarhiv&task=movies.removeMoviePremieres&format=json&id=' . $this->id),
						'width' => '#j-main-container', 'height' => '#item-form',
						'order' => 'p.ordering', 'orderby' => 'asc',
						'idprefix' => 'p_',
						'rowlist'  => array(5, 10, 15, 20, 25, 30, 50, 100, 200, 500),
						'colModel' => array(
							'JGRID_HEADING_ID' => (object) array(
								'name' => 'id', 'index' => 'p.id', 'width' => 60, 'title' => false,
								'sorttype' => 'int',
								'searchoptions' => (object) array(
									'sopt' => array('cn', 'eq', 'le', 'ge')
								)
							),
							'COM_KA_FIELD_PREMIERE_DATE_LABEL' => (object) array(
								'name' => 'premiere_date', 'index' => 'p.premiere_date', 'width' => 100, 'title' => false,
								'sorttype' => 'date',
								'searchoptions' => (object) array(
									'sopt' => array('cn', 'eq', 'le', 'ge')
								)
							),
							'COM_KA_FIELD_PREMIERE_VENDOR' => (object) array(
								'name' => 'company_name', 'index' => 'v.company_name', 'width' => 350, 'title' => false,
								'sorttype' => 'text',
								'searchoptions' => (object) array(
									'sopt' => array('cn', 'eq', 'bw', 'ew')
								)
							),
							'COM_KA_FIELD_RELEASE_COUNTRY' => (object) array(
								'name' => 'name', 'index' => 'cn.name', 'width' => 200, 'title' => false,
								'sorttype' => 'int',
								'searchoptions' => (object) array(
									'sopt' => array('cn', 'eq', 'le', 'ge')
								)
							),
							'JGRID_HEADING_ORDERING' => (object) array(
								'name' => 'ordering', 'index' => 'p.ordering', 'width' => 60, 'title' => false,
								'sorttype' => 'int', 'search' => false
							)
						),
						'navgrid' => $navgridOpts
					);

					echo JLayoutHelper::render('administrator.components.com_kinoarhiv.layouts.edit.grid', $options, JPATH_ROOT);
				}
				else
				{
					echo JText::_('COM_KA_NO_ID');
				}
				?>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'movies', 'page5', JText::_('COM_KA_MOVIES_TAB_RELEASES')); ?>

				<?php
				if ($this->id != 0)
				{
					$options = array(
						'url'   => JRoute::_('index.php?option=com_kinoarhiv&task=api.data&content=releases&format=json'
							. '&lang=' . $languageTag . '&id=' . $this->id . '&item_type=0&' . $token . '=1'
						),
						'add_url'  => JRoute::_('index.php?option=com_kinoarhiv&task=movies.editMovieRelease&item_id=' . $this->id),
						'edit_url' => JRoute::_('index.php?option=com_kinoarhiv&task=movies.editMovieRelease&item_id=' . $this->id),
						'del_url'  => JRoute::_('index.php?option=com_kinoarhiv&task=movies.removeMovieReleases&format=json&id=' . $this->id),
						'width' => '#j-main-container', 'height' => '#item-form',
						'order' => 'r.ordering', 'orderby' => 'asc',
						'idprefix' => 'r_',
						'rowlist'  => array(5, 10, 15, 20, 25, 30, 50, 100, 200, 500),
						'colModel' => array(
							'JGRID_HEADING_ID' => (object) array(
								'name' => 'id', 'index' => 'r.id', 'width' => 60, 'title' => false,
								'sorttype' => 'int',
								'searchoptions' => (object) array(
									'sopt' => array('cn', 'eq', 'le', 'ge')
								)
							),
							'COM_KA_FIELD_RELEASE_DATE_LABEL' => (object) array(
								'name' => 'release_date', 'index' => 'r.release_date', 'width' => 100, 'title' => false,
								'sorttype' => 'date',
								'searchoptions' => (object) array(
									'sopt' => array('cn', 'eq', 'le', 'ge')
								)
							),
							'COM_KA_FIELD_RELEASE_VENDOR' => (object) array(
								'name' => 'company_name', 'index' => 'v.company_name', 'width' => 350, 'title' => false,
								'sorttype' => 'text',
								'searchoptions' => (object) array(
									'sopt' => array('cn', 'eq', 'bw', 'ew')
								)
							),
							'COM_KA_FIELD_RELEASE_COUNTRY' => (object) array(
								'name' => 'name', 'index' => 'cn.name', 'width' => 200, 'title' => false,
								'sorttype' => 'int',
								'searchoptions' => (object) array(
									'sopt' => array('cn', 'eq', 'le', 'ge')
								)
							),
							'COM_KA_RELEASES_MEDIATYPE_TITLE' => (object) array(
								'name' => 'title', 'index' => 'mt.title', 'width' => 200, 'title' => false,
								'sorttype' => 'text',
								'searchoptions' => (object) array(
									'sopt' => array('cn', 'eq', 'bw', 'ew')
								)
							),
							'JGRID_HEADING_ORDERING' => (object) array(
								'name' => 'ordering', 'index' => 'r.ordering', 'width' => 60, 'title' => false,
								'sorttype' => 'int', 'search' => false
							)
						),
						'navgrid' => $navgridOpts
					);

					echo JLayoutHelper::render('administrator.components.com_kinoarhiv.layouts.edit.grid', $options, JPATH_ROOT);
				}
				else
				{
					echo JText::_('COM_KA_NO_ID');
				}
				?>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'movies', 'page6', JText::_('COM_KA_MOVIES_TAB_META')); ?>

				<div class="row-fluid">
					<div class="span6">
						<fieldset class="form-horizontal">
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('metakey'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('metakey'); ?></div>
							</div>
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('metadesc'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('metadesc'); ?></div>
							</div>
						</fieldset>
					</div>
					<div class="span6">
						<fieldset class="form-horizontal">
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('tags'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('tags'); ?></div>
							</div>
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('robots'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('robots'); ?></div>
							</div>
						</fieldset>
					</div>
				</div>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'movies', 'page7', JText::_('COM_KA_MOVIES_TAB_PUB')); ?>

				<div class="row-fluid">
					<div class="span6">
						<fieldset class="form-horizontal">
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('publish_up'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('publish_up'); ?></div>
							</div>
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('publish_down'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('publish_down'); ?></div>
							</div>
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('created'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('created'); ?></div>
							</div>
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('created_by'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('created_by'); ?></div>
							</div>
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('modified'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('modified'); ?></div>
							</div>
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('modified_by'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('modified_by'); ?></div>
							</div>
							<?php foreach ($this->form->getFieldset('basic') as $field): ?>
								<div class="control-group">
									<div class="control-label"><?php echo $field->label; ?></div>
									<div class="controls"><?php echo $field->input; ?></div>
								</div>
							<?php endforeach; ?>
						</fieldset>
					</div>
					<div class="span6">
						<fieldset class="form-horizontal">
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('language'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('language'); ?></div>
							</div>
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('state'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('state'); ?></div>
							</div>
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('access'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('access'); ?></div>
							</div>
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('ordering'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('ordering'); ?></div>
							</div>
							<?php foreach ($this->form->getFieldset('tabs') as $field): ?>
								<div class="control-group">
									<div class="control-label"><?php echo $field->label; ?></div>
									<div class="controls"><?php echo $field->input; ?></div>
								</div>
							<?php endforeach; ?>
						</fieldset>
					</div>
				</div>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'movies', 'page8', JText::_('COM_KA_PERMISSIONS_LABEL')); ?>

				<div class="row-fluid">
					<div class="span12">
						<fieldset class="form-horizontal">
							<div class="control-group">
								<div class="controls" style="margin-left: 0 !important;"><?php echo $this->form->getInput('rules'); ?></div>
							</div>
						</fieldset>
					</div>
				</div>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php echo JHtml::_('bootstrap.endTabSet'); ?>
			</div>
		</div>
	</div>

	<?php echo $this->form->getInput('albums_orig') . "\n"; ?>
	<?php echo $this->form->getInput('countries_orig') . "\n"; ?>
	<?php echo $this->form->getInput('genres_orig') . "\n"; ?>
	<?php echo $this->form->getInput('id') . "\n"; ?>
	<input type="hidden" name="image_id" value="<?php echo $this->form->getValue('image_id'); ?>" />
	<input type="hidden" name="img_folder" value="<?php echo $this->items->get('img_folder'); ?>" />
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>

<?php
echo JHtml::_(
	'bootstrap.renderModal',
	'ratingImgModal',
	array(
		'title'  => '',
		'footer' => '<a class="btn" type="button" data-dismiss="modal">' . JText::_('COM_KA_CLOSE') . '</a>',
		'modalWidth' => '25'
	),
	''
);

echo JHtml::_(
	'bootstrap.renderModal',
	'parserModal',
	array(
		'title'  => JText::_('COM_KA_PARSER_TOOLBAR_BUTTON'),
		'footer' => JLayoutHelper::render('layouts.parser.footer', array(), JPATH_COMPONENT)
	),
	JLayoutHelper::render('layouts.parser.main', array(), JPATH_COMPONENT)
);
