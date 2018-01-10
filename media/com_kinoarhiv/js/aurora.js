/*!
 * Aurora 1.1
 *
 * @copyright   Copyright (C) 2018 Viper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 *
 * http://киноархив.com/ru/разное/заметки-по-html-jquery-php-mysql/25-aurora-плагин-сообщений-в-стиле-jqueryui-bootstrap-uikit.html
 * http://киноархив.com/en/others/notes-on-html-jquery-php-mysql/26-the-aurora-plugin-messages-in-style-of-jquery-ui,-bootstrap,-uikit.html
*/

Aurora = window.Aurora || {};

(function(Aurora, document) {
	'use strict';

	/**
	 * Adds an event listener for the specified events.
	 *
	 * Events should be a space separated list of events. If selector is specified the handler will only be called
	 * when the event target matches the selector.
	 *
	 * @param   {Node}              node      DOM node
	 * @param   {string}            events
	 * @param   {string}            selector
	 * @param   {function(Object)}  fn
	 * @param   {object|boolean}    capture
	 */
	function on(node, events, selector, fn, capture) {
		events.split(' ').forEach(function (event) {
			var handler;

			if (typeof selector === 'string') {
				handler = fn['_au-event-' + event + selector] || function (e) {
					var target = e.target;
					while (target && target !== node) {
						if (is(target, selector)) {
							fn.call(target, e);

							return;
						}

						target = target.parentNode;
					}
				};

				fn['_au-event-' + event + selector] = handler;
			} else {
				handler = selector;
				capture = fn;
			}

			node.addEventListener(event, handler, capture || false);
		});
	}

	/**
	 * Checks if node matches the given selector.
	 *
	 * @param   {HTMLElement}  node      DOM node
	 * @param   {string}       selector
	 *
	 * @return  {boolean}
	 */
	function is(node, selector) {
		var result = false;

		if (node && node.nodeType === 1) {
			result = (node.matches || node.msMatchesSelector || node.webkitMatchesSelector).call(node, selector);
		}

		return result;
	}

	/**
	 * Gets the first parent node that matches the selector
	 *
	 * @param   {HTMLElement}  node      DOM node
	 * @param   {string}       selector
	 *
	 * @return  {HTMLElement|undefined}
	 */
	function parent(node, selector) {
		var parent = node || {};

		while ((parent = parent.parentNode) && !/(9|11)/.test(parent.nodeType)) {
			if (!selector || is(parent, selector)) {
				return parent;
			}
		}
	}

	/**
	 * Checks the passed node and all parents and returns the first matching node if any.
	 *
	 * @param   {HTMLElement}  node     DOM node
	 * @param   {string}      selector
	 *
	 * @return  {HTMLElement|undefined}
	 */
	function closest(node, selector) {
		return is(node, selector) ? node : parent(node, selector);
	}

	/**
	 * Removes the node from the DOM
	 *
	 * @param  {Node}  node  DOM node
	 */
	function remove(node) {
		node.parentNode.removeChild(node);
	}

	/*
	 * A JavaScript equivalent of PHP's empty. See http://phpjs.org/functions/empty/
	 *
	 * @param   {mixed}  mixedVar  Value to test.
	 *
	 * @return  {boolean}
	 */
	function empty(mixedVar) {
		var undef,
			key,
			i,
			len,
			emptyValues = [undef, null, false, 0, '', '0'];

		for (i = 0, len = emptyValues.length; i < len; i++) {
			if (mixedVar === emptyValues[i]) {
				return true
			}
		}

		if (typeof mixedVar === 'object') {
			for (key in mixedVar) {
				if (mixedVar.hasOwnProperty(key)) {
					return false
				}
			}

			return true
		}

		return false
	}

	/*
	 * Insert element after the target.
	 *
	 * @param   {string}  newNode        HTML to insert.
	 * @param   {Node}    referenceNode  Where to insert.
	 */
	function insertAfter(newNode, referenceNode) {
		referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
	}

	/*
	 * Insert element before the target.
	 *
	 * @param   {string}  newNode        HTML to insert.
	 * @param   {Node}    referenceNode  Where to insert.
	 */
	function insertBefore(newNode, referenceNode) {
		referenceNode.parentNode.insertBefore(newNode, referenceNode);
	}

	/*
	 * Insert element to the end of the target.
	 *
	 * @param   {string}  newNode        HTML to insert.
	 * @param   {Node}    referenceNode  Where to insert.
	 */
	function appendTo(newNode, referenceNode) {
		referenceNode.appendChild(newNode);
	}

	/*
	 * Insert element to the beginning of the target.
	 *
	 * @param   {string}  newNode        HTML to insert.
	 * @param   {Node}    referenceNode  Where to insert.
	 */
	function prependTo(newNode, referenceNode) {
		referenceNode.insertBefore(newNode, referenceNode.firstChild);
	}

	/**
	 * Merge two objects.
	 *
	 * @param   {object}  object1
	 * @param   {object}  object2
	 *
	 * @return  {object}
	 */
	function extend(object1, object2) {
		var i = 0,
			new_object = {};

		for (; i < arguments.length; i++) {
			var source = arguments[i];

			for (var key in source) {
				if (source.hasOwnProperty(key) && typeof source[key] !== 'undefined') {
					new_object[key] = source[key];
				}
			}
		}

		return new_object;
	}

	/**
	 * @type  {object}
	 */
	var default_options = {
		attachTo: 'document', // Can be 'document' or 'window'
		position: 'top-left', // Applied only if attachTo is set to 'window'
		/**
		 * Where to place the message container. Can be insertAfter, insertBefore, appendTo. Default to prependTo.
		 * Applied only if attachTo is set to 'document'.
		 */
		place: '',
		replace: false // If true, replace existing message(s), so only one message always be visible.
	};

	/*
	 * Build message string.
	 *
	 * @param   {object}  data  Array of objects with messages.
	 *
	 * @return  {string}
	 */
	function buildMessage(data) {
		var message = '';

		if (typeof data === 'object') {
			data.forEach(function(item){
				var icon = '',
					type = empty(item.type) ? 'info' : item.type,
					css = empty(item.cssClass) ? '' : ' ' + item.cssClass;

				if (!empty(item.icon)) {
					// Test if icon from UIkit
					if (/^icon:/.test(item.icon)) {
						icon = '<span class="ui-message-icon uk-margin-small-right uk-icon" uk-icon="' + item.icon + '"></span>';
					} else {
						icon = '<span class="ui-message-icon ' + item.icon + '" aria-hidden="true"></span>';
					}
				}

				message += '<div class="ui-message-body ui-message-' + type + css + '">' +
					'<div style="margin: 5px !important;">' +
					icon +
					'<span class="ui-message-close">&times;</span>' +
					'<span style="overflow: hidden; display: block;">' + item.text + '</span>' +
					'</div>' +
					'</div>';
			});
		} else {
			return false;
		}

		return message;
	}

	/*
	 * Insert HTML string into DOM.
	 *
	 * @param   {Node}    container  HTML.
	 * @param   {Node}    node       HTML.
	 * @param   {string}  place      Where to attach.
	 */
	function insertMessage(container, node, place) {
		// Check if element is DOM element
		if (node) {
			if (node.tagName.toLowerCase() === 'body') {
				switch (place) {
					case 'appendTo':
						appendTo(container, node);
						break;
					default:
						prependTo(container, node);
				}
			} else {
				switch (place) {
					case 'insertAfter':
						insertAfter(container, node);
						break;
					case 'insertBefore':
						insertBefore(container, node);
						break;
					case 'appendTo':
						appendTo(container, node);
						break;
					default:
						prependTo(container, node);
				}
			}
		}
	}

	/**
	 * Display messages.
	 * Example: [{text: 'foo', type: 'bar', icon: 'baz', cssClass: 'qux'}, ...]
	 * where - 'type' - message class. Can be 'success', 'info', 'error' or custom class name.
	 * 'icon' - CSS class name for icon image. Example: 'icon-user icon-white' from Bootstrap (Glyphicons)
	 * or 'ui-icon ui-icon-lightbulb' from jQueryUI or 'icon: heart' from UIkit.
	 *
	 * @param   {object}                      data          Array of objects with messages.
	 * @param   {HTMLElement|Element|string}  node          DOM node or string where to attach message.
	 * @param   {object}                      user_options  Additional options.
	 */
	Aurora.message = function(data, node, user_options){
		var container = document.createElement('div'),
			options = extend(default_options, user_options),
			attach_to = options.attachTo.toLowerCase(),
			msg_nodes = '',
			last_child = '';

		container.innerHTML = buildMessage(data);
		container.setAttribute('data-au-attachto', attach_to);

		if (attach_to === 'window') {
			msg_nodes = document.querySelectorAll('.ui-message[data-au-attachto="window"]');
			container.className = 'ui-message ui-pos-window ' + options.position;

			if (options.replace) {
				this.remove(msg_nodes);
			}

			if (msg_nodes.length > 0) {
				last_child = document.querySelectorAll('.ui-message[data-au-attachto="window"] .ui-message-body:last-child');

				if (last_child.length === 0) {
					insertMessage(container, document.body);
				} else {
					insertAfter(container.firstChild, last_child[0]);
				}
			} else {
				insertMessage(container, document.body);
			}
		} else {
			if (!empty(node)) {
				if (typeof node === 'string') {
					node = document.querySelectorAll(node)[0];
				} else if (typeof node === 'object') {
					node = node[0];
				}
			} else {
				node = document.querySelector('body');
			}

			msg_nodes = document.querySelectorAll('.ui-message[data-au-attachto="document"]');
			container.className = 'ui-message';

			if (options.replace) {
				this.remove(msg_nodes);
			}

			if (msg_nodes.length > 0) {
				last_child = document.querySelectorAll('.ui-message[data-au-attachto="document"] .ui-message-body:last-child');

				if (last_child.length === 0) {
					insertMessage(container, node, options.place);
				} else {
					insertAfter(container.firstChild, last_child[0]);
				}
			} else {
				insertMessage(container, node, options.place);
			}
		}
	};

	/**
	 * Remove messages from the DOM.
	 *
	 * @param   {NodeList|string}  element  CSS selector or NodeList returned by querySelector(All).
	 */
	Aurora.remove = function(element){
		var nodes = {};

		if (typeof element === 'string') {
			nodes = empty(element) ? document.querySelectorAll('.ui-message') : document.querySelectorAll(element);
		} else if (typeof element === 'object') {
			nodes = element;
		} else {
			nodes = document.querySelectorAll('.ui-message');
		}

		if (Object.keys(nodes).length > 0) {
			nodes.forEach(function(node){
				remove(node);
			});
		}
	};

	function init() {
		// Bind 'click' event on 'close button' for existing ui messages
		on(document, 'click', '.ui-message-close', function (e) {
			e.preventDefault();

			remove(closest(this, '.ui-message-body'));
		});
	}

	init();
}(Aurora, document));
