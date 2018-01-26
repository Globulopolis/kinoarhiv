/**
 * This file provide custom validation rules for fields.
 *
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com/
 */

jQuery(document).ready(function($){
	/**
	 * Set of cutom validator rules.
	 *
	 * @see  /media/system/js/validate-uncompressed.js
	 */
	document.formvalidator.setHandler('year', function(value){
		return /^\d{4,}?$/.test(value);
	});

	document.formvalidator.setHandler('date', function(value){
		var regex = /^\d{4}-\d{2}-\d{2}$/,
			date;

		if (!value.match(regex)) {
			return false;
		}

		if (!((date = new Date(value)) || 0)) {
			return false;
		}

		return date.toISOString().slice(0, 10) === value;
	});

	document.formvalidator.setHandler('datetime', function(value){
		var regex = /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/;

		if (!value.match(regex)) {
			return false;
		}

		return !!Date.parse(value);
	});

	document.formvalidator.setHandler('filename', function(value){
		return !/[^a-z0-9_.,[]%@'()\s-]/i.test(value);
	});

	// Validate screenshot time value in 'createScreenshot' dialog
	document.formvalidator.setHandler('time', function(value){
		return /^\d{2,}:(?:[0-5]\d):(?:[0-5]\d)(?:.\d{3,})?$/.test(value);
	});
});
