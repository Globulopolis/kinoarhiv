<?php defined('_JEXEC') or die;

$css = JURI::base().'components/com_kinoarhiv/assets/themes/component/'.$this->params->get('ka_theme').'/css/select.css';
$script = JURI::base().'components/com_kinoarhiv/assets/js/select2.min.js';
$script_lang = JURI::base().'components/com_kinoarhiv/assets/js/i18n/select/select2_locale_'.substr(JFactory::getLanguage()->getTag(), 0, 2).'.js';

if (JFactory::getDocument()->getType() == 'html') {
	JFactory::getDocument()->addHeadLink($css, 'stylesheet', 'rel', array('type'=>'text/css'));
	JHtml::_('script', $script);
	JHtml::_('script', $script_lang);
} else {
	echo '<style type="text/css">@import url("'.$css.'");</style>';
	echo '<script src="'.$script.'" type="text/javascript"></script>';
	echo '<script src="'.$script_lang.'" type="text/javascript"></script>';
}
?>
<script type="text/javascript">
	jQuery(document).ready(function($){
		$('.cmd-reset').click(function(){
			$(this).closest('form').find('#filters_movies_country, #filters_movies_vendor, #filters_names_birthcountry, #filters_movies_genre').select2('val', '');
		});

		$('#filters_movies_country, #filters_names_birthcountry').select2({
			placeholder: '<?php echo JText::_('JGLOBAL_SELECT_AN_OPTION'); ?>',
			allowClear: true,
			formatSelection: function(data){
				return "<img class='flag-dd' src='<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/images/icons/countries/" + $(data.element).data('code') + ".png'/> " + data.text;
			},
			escapeMarkup: function(m) { return m; }
		});
		$('#filters_movies_vendor').select2({placeholder: '<?php echo JText::_('JGLOBAL_SELECT_AN_OPTION'); ?>', allowClear: true});
		$('#filters_movies_genre').select2({placeholder: '<?php echo JText::_('JGLOBAL_SELECT_SOME_OPTIONS'); ?>'});
		$('#filters_names_mtitle').select2({
			placeholder: '<?php echo JText::_('JGLOBAL_KEEP_TYPING'); ?>',
			allowClear: true,
			minimumInputLength: 1,
			maximumSelectionSize: 1,
			ajax: {
				cache: true,
				url: '<?php echo JRoute::_('index.php?option=com_kinoarhiv&task=ajaxData&element=movies&format=json&Itemid='.$this->home_itemid['movies'], false); ?>',
				data: function(term, page){
					return {
						term: term,
						showAll: 0
					}
				},
				results: function(data, page){
					return {results: data};
				}
			},
			initSelection: function(element, callback){
				var id = parseInt($(element).val(), 10);

				if (id !== 0) {
					$.ajax('<?php echo JRoute::_('index.php?option=com_kinoarhiv&task=ajaxData&element=movies&format=json&Itemid='.$this->home_itemid['movies'], false); ?>', {
						data: {
							id: id
						}
					}).done(function(data){
						callback(data);
					});
				}
			},
			formatResult: function(data){
				if (data.year == '0000') return data.title;
				return data.title+' ('+data.year+')';
			},
			formatSelection: function(data){
				if (data.year == '0000') return data.title;
				return data.title+' ('+data.year+')';
			},
			escapeMarkup: function(m) { return m; }
		});

		$('#filters_movies_rate').slider({
			range: true,
			min: 0,
			max: <?php echo (int)$this->params->get('vote_summ_num'); ?>,
			values: [<?php echo (int)$this->activeFilters->def('filters.movies.rate.min', 0); ?>, <?php echo (int)$this->activeFilters->def('filters.movies.rate.max', $this->params->get('vote_summ_num')); ?>],
			slide: function(event, ui){
				$('#filters_movies_rate_min').spinner('value', ui.values[0]);
				$('#filters_movies_rate_max').val(ui.values[1]);
			}
		});
		$('#filters_movies_rate_min').val($('#filters_movies_rate').slider('values', 0)).spinner({
			spin: function(event, ui){
				if (ui.value > <?php echo (int)$this->params->get('vote_summ_num'); ?>) {
					$(this).spinner('value', <?php echo (int)$this->params->get('vote_summ_num'); ?>);
					return false;
				} else if (ui.value < 0) {
					$(this).spinner('value', 0);
					return false;
				}
				$('#filters_movies_rate').slider('values', 0, ui.value);
			}
		});
		$('#filters_movies_rate_max').val($('#filters_movies_rate').slider('values', 1)).spinner({
			spin: function(event, ui){
				if (ui.value > <?php echo (int)$this->params->get('vote_summ_num'); ?>) {
					$(this).spinner('value', <?php echo (int)$this->params->get('vote_summ_num'); ?>);
					return false;
				} else if (ui.value < 0) {
					$(this).spinner('value', 0);
					return false;
				}
				$('#filters_movies_rate').slider('values', 1, ui.value);
			}
		});

		$('#filters_movies_imdbrate').slider({
			range: true,
			min: 0,
			max: 10,
			values: [<?php echo (int)$this->activeFilters->def('filters.movies.imdbrate.min', 6); ?>, <?php echo (int)$this->activeFilters->def('filters.movies.imdbrate.max', 10); ?>],
			slide: function(event, ui){
				$('#filters_movies_imdbrate_min').spinner('value', ui.values[0]);
				$('#filters_movies_imdbrate_max').val(ui.values[1]);
			}
		});
		$('#filters_movies_imdbrate_min').val($('#filters_movies_imdbrate').slider('values', 0)).spinner({
			spin: function(event, ui){
				if (ui.value > 10) {
					$(this).spinner('value', 10);
					return false;
				} else if (ui.value < 0) {
					$(this).spinner('value', 0);
					return false;
				}
				$('#filters_movies_imdbrate').slider('values', 0, ui.value);
			}
		});
		$('#filters_movies_imdbrate_max').val($('#filters_movies_imdbrate').slider('values', 1)).spinner({
			spin: function(event, ui){
				if (ui.value > 10) {
					$(this).spinner('value', 10);
					return false;
				} else if (ui.value < 0) {
					$(this).spinner('value', 0);
					return false;
				}
				$('#filters_movies_imdbrate').slider('values', 1, ui.value);
			}
		});

		$('#filters_movies_kprate').slider({
			range: true,
			min: 0,
			max: 10,
			values: [<?php echo (int)$this->activeFilters->def('filters.movies.kprate.min', 6); ?>, <?php echo (int)$this->activeFilters->def('filters.movies.kprate.max', 10); ?>],
			slide: function(event, ui){
				$('#filters_movies_kprate_min').spinner('value', ui.values[0]);
				$('#filters_movies_kprate_max').val(ui.values[1]);
			}
		});
		$('#filters_movies_kprate_min').val($('#filters_movies_kprate').slider('values', 0)).spinner({
			spin: function(event, ui){
				if (ui.value > 10) {
					$(this).spinner('value', 10);
					return false;
				} else if (ui.value < 0) {
					$(this).spinner('value', 0);
					return false;
				}
				$('#filters_movies_kprate').slider('values', 0, ui.value);
			}
		});
		$('#filters_movies_kprate_max').val($('#filters_movies_kprate').slider('values', 1)).spinner({
			spin: function(event, ui){
				if (ui.value > 10) {
					$(this).spinner('value', 10);
					return false;
				} else if (ui.value < 0) {
					$(this).spinner('value', 0);
					return false;
				}
				$('#filters_movies_kprate').slider('values', 1, ui.value);
			}
		});

		$('#filters_movies_rtrate').slider({
			range: true,
			min: 0,
			max: 100,
			values: [<?php echo (int)$this->activeFilters->def('filters.movies.rtrate.min', 0); ?>, <?php echo (int)$this->activeFilters->def('filters.movies.rtrate.max', 100); ?>],
			slide: function(event, ui){
				$('#filters_movies_rtrate_min').spinner('value', ui.values[0]);
				$('#filters_movies_rtrate_max').val(ui.values[1]);
			}
		});
		$('#filters_movies_rtrate_min').val($('#filters_movies_rtrate').slider('values', 0)).spinner({
			spin: function(event, ui){
				if (ui.value > 100) {
					$(this).spinner('value', 100);
					return false;
				} else if (ui.value < 0) {
					$(this).spinner('value', 0);
					return false;
				}
				$('#filters_movies_rtrate').slider('values', 0, ui.value);
			}
		});
		$('#filters_movies_rtrate_max').val($('#filters_movies_rtrate').slider('values', 1)).spinner({
			spin: function(event, ui){
				if (ui.value > 100) {
					$(this).spinner('value', 100);
					return false;
				} else if (ui.value < 0) {
					$(this).spinner('value', 0);
					return false;
				}
				$('#filters_movies_rtrate').slider('values', 1, ui.value);
			}
		});
	});
</script>
<div class="uk-article ka-content">
	<?php if (JFactory::getApplication()->input->get('task', '', 'cmd') == 'movies'):
		echo $this->loadTemplate('form_movies');
	elseif (JFactory::getApplication()->input->get('task', '', 'cmd') == 'names'):
		echo $this->loadTemplate('form_names');
	else:
		echo $this->loadTemplate('form_movies');
		echo $this->loadTemplate('form_names');
	endif; ?>
</div>
