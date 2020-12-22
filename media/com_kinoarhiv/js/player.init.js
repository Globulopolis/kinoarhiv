/**
 * This file provides player initialization.
 *
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2018 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com/
 */

var queryLang = getParameterByName('lang', window.location.href);

if (typeof mejs !== 'undefined') {
	if (!empty(queryLang)) {
		mejs.i18n.language(queryLang);
	}
}

jQuery(document).ready(function($){
	if (typeof mejs !== 'undefined') {
		// Init player
		$('video').mediaelementplayer({
			success: function(player, node){
				$(player).closest('.mejs__container').attr('lang', mejs.i18n.language());
			}
		});
	}
});
