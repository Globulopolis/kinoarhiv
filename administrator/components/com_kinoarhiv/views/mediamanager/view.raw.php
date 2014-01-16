<?php defined('_JEXEC') or die;

class KinoarhivViewMediamanager extends JViewLegacy {
	protected $item;
	protected $form;

	public function display($tpl = null) {
		$input = JFactory::getApplication()->input;

		if ($input->get('type', '', 'word') == 'trailers') {
			$item = $this->get('Item');
			$form = $this->get('Form');

			$this->item = &$item;
			$this->form = &$form;
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
