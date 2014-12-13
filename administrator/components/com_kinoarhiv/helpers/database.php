<?php defined('_JEXEC') or die;
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url			http://киноархив.com/
 */

class DatabaseHelper {
	/**
	 * Converts the operand of the query grid in operand for mySQL query
	 *
	 * @param   string   $field     Name of field to look for data.
	 * @param   string   $operand   The operand from the request.
	 * @param   string   $data      Data for search.
	 *
	 * @return  string
	 *
	*/
	static function transformOperands($field, $operand='eq', $data) {
		switch ($operand) {
			case 'ne':
				$request = $field." <> '".$data."'";
				break;
			case 'lt':
				$request = $field." < '".$data."'";
				break;
			case 'le':
				$request = $field." <= '".$data."'";
				break;
			case 'gt':
				$request = $field." > '".$data."'";
				break;
			case 'ge':
				$request = $field." >= '".$data."'";
				break;
			case 'bw':
				$request = $field." LIKE '".$data."%'";
				break;
			case 'bn':
				$request = $field." NOT LIKE '".$data."%'";
				break;
			case 'in':
				$request = $field." IN (".$data.")";
				break;
			case 'ni':
				$request = $field." NOT IN (".$data.")";
				break;
			case 'ew':
				$request = $field." LIKE '%".$data."'";
				break;
			case 'en':
				$request = $field." NOT LIKE '%".$data."'";
				break;
			case 'cn':
				$request = $field." LIKE '%".$data."%'";
				break;
			case 'nc':
				$request = $field." NOT LIKE '%".$data."%'";
				break;
			case 'eq':
			default:
				$request = $field." = '".$data."'";
				break;
		}

		return $request;
	}
}
