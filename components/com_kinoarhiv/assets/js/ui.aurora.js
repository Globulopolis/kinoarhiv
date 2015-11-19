/*!
 * jQuery UI Aurora 0.9
 *
 * Copyright 2012
 * Licensed under the GPL Version 2 license.
 *
 * http://киноархив.com/ru/разное/jqueryui-aurora-ru.html
 * http://киноархив.com/en/others/jqueryui-aurora-en.html
*/
;(function($, window, document, undefined){
	$.fn.aurora = function(options){
		var opts = $.extend({}, $.fn.aurora.defaults, options),
			button = '';

		if (opts.button != '') {
			button = ' <a href="javascript:void(0);" class="ui-message-button">'+opts.button_title+'</a>';
		}

		var html = '<div class="ui-message" style="display: none; margin: '+opts.styles.indent.message_margin+';">'
			+'<div class="ui-widget">'
				+'<div style="padding: '+opts.styles.indent.div_padding+';" class="'+opts.container+' ui-corner-all">'
					+'<div style="margin: '+opts.styles.indent.div_margin+';">'
						+'<span style="float: left; margin-right: '+opts.styles.indent.span_margin+';" class="ui-icon '+opts.icon+'"></span>'
						+'<span style="'+opts.styles.span_text_style+'">'+opts.text+button+'</span>'
					+'</div>'
				+'</div>'
			+'</div>'
		+'</div>';

		if (opts.placement == 'prepend' || opts.placement == 'append') {
			if (opts.only_one) {
				if (this.find('.ui-message').length > 0) {
					$('.ui-message', this).remove();
				}
			}

			if (opts.placement == 'prepend') {
				var div = $(html).prependTo(this).animate({ opacity: 'toggle' }, opts.effect.speed, opts.effect.effect, opts.onComplete);
			} else {
				var div = $(html).appendTo(this).animate({ opacity: 'toggle' }, opts.effect.speed, opts.effect.effect, opts.onComplete);
			}
		} else if (opts.placement == 'before') {
			if (opts.only_one) {
				if (this.prev().hasClass('ui-message')) {
					this.prev().remove();
				}
			}

			var div = $(html).insertBefore(this).animate({ opacity: 'toggle' }, opts.effect.speed, opts.effect.effect, opts.onComplete);
		} else {
			if (opts.only_one) {
				if (this.next().hasClass('ui-message')) {
					this.next().remove();
				}
			}

			var div = $(html).insertAfter(this).animate({ opacity: 'toggle' }, opts.effect.speed, opts.effect.effect, opts.onComplete);
		}

		if (opts.button == 'hide') {
			$('.ui-message').on('click', 'a.ui-message-button', function(){
				var parent = $(this).closest('.ui-message');

				parent.find('span').eq(1).hide();
				parent.find('.ui-corner-all').css('height', '26');
				parent.css({
					'width': '46',
					'height': '34'
				});
				parent.find('.ui-icon').css('cursor', 'pointer');
			});
			$('.ui-message').on('click', 'span.ui-icon', function(){
				var parent = $(this).closest('.ui-message');

				parent.find('span').eq(1).show();
				parent.find('.ui-corner-all').css('height', 'auto');
				parent.css({
					'width': '100%',
					'height': 'auto'
				});
				parent.find('.ui-icon').css('cursor', 'default');
			});
		} else if (opts.button == 'close') {
			$('.ui-message').on('click', 'a.ui-message-button', function(){
				opts.onRemove();
				$(this).closest('.ui-message').remove();
			});
		}

		return this;
	};

	$.fn.aurora.destroy = function(options){
		var opts = $.extend({}, $.fn.aurora.defaults, options);

		if (typeof opts.indexes[0] === 'number' || opts.indexes == 'all') {
			if (opts.indexes == 'all') {
				$('body').find('.ui-message').remove();
			} else {
				$('.ui-message').filter(function(i){
					return opts.indexes.indexOf(i) != -1;
				});
			}
		}

		return this;
	};

	$.fn.aurora.defaults = {
		text: '',
		icon: 'ui-icon-info',
		container: 'ui-state-highlight',
		styles: {
			indent: {
				message_margin: '0.4em 0 0 0',
				div_padding: '0 0.5em',
				div_margin: '5px !important',
				span_margin: '0 0.3em 0 0'
			},
			span_text_style: 'overflow: hidden; display: block; padding-left: 5px;'
		},
		effect: {
			type: 'swing',
			speed: 400
		},
		placement: 'after',
		only_one: true,
		button: '',
		button_title: '[Hide]',
		onComplete: function(){},
		onRemove: function(){}
	};

	$.fn.aurora.destroy.defaults = {
		indexes: 'all'
	};
})(jQuery, window, document);
