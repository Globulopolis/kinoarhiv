<?php defined('_JEXEC') or die;
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url			http://киноархив.com/
 */

class KinoarhivViewMediamanager extends JViewLegacy {
	protected $form;

	public function display($tpl = null) {
		$input = JFactory::getApplication()->input;

		if ($input->get('type', '', 'word') == 'trailers') {
			$this->form = $this->get('Form');
		}

		if ($tpl == 'upload_subtitles_lang_edit') {
			$data = $this->get('SubtitleEdit');

			$this->data = &$data;
		}

		$params = JComponentHelper::getParams('com_kinoarhiv');

		$this->params = &$params;

		parent::display($tpl);
	}
}
