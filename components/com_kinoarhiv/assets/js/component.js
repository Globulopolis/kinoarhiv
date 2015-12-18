jQuery(document).ready(function($){
	if ($.fn.lazyload) {
		$('img.lazy').lazyload({
			threshold: 200
		});
	} else {
		console.log('Lazyload plugin not loaded!');
	}

	if ($.fn.colorbox) {
		$('.thumb .item a').colorbox({
			maxHeight: '90%',
			maxWidth: '90%',
			photo: true
		});
	} else {
		console.log('Colorbox plugin not loaded!');
	}
});
