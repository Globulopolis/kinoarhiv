<?php defined('_JEXEC') or die;

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
