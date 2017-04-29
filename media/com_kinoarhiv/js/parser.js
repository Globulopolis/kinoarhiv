/**
 * This file provide basic support for parser used in backend only.
 *
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

KinoarhivParser = window.KinoarhivParser || {};

(function(KinoarhivParser, document){
	'use strict';
}(KinoarhivParser, document));

jQuery(document).ready(function($){
	// Wizard
	$('#wizard').bootstrapWizard({'nextSelector': '.cmd-next', 'previousSelector': '.cmd-prev'});
});
