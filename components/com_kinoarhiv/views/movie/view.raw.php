<?php defined('_JEXEC') or die;

class KinoarhivViewMovie extends JViewLegacy {
	protected $item = null;
	protected $items = null;

	public function display($tpl = null) {
		$app = JFactory::getApplication();
		$layout = $app->input->get('watch', 'trailer', 'cmd');

		$this->watch($layout);
	}

	public function watch($type) {
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$params = $app->getParams('com_kinoarhiv');

		if ($params->get('allow_guest_watch') == 0 && $user->get('guest') && $type == 'movie') {
			echo '<div style="width: 200px; margin: 20px 5px 5px 5px;">'.GlobalHelper::showMsg(JText::_('JGLOBAL_AUTH_ACCESS_DENIED'), array('icon'=>'alert', 'type'=>'error')).'</div>';
			return;
		}

		if ($params->get('watch_trailer_button') == 1 || $params->get('watch_movie_button') == 1) {
			$item = $this->get('Trailer');

			$this->item = &$item;
			$this->params = &$params;
			$this->user = &$user;

			parent::display('trailer');
		} else {
			echo '<div style="width: 200px; margin: 20px 5px 5px 5px;">'.GlobalHelper::showMsg(JText::_('ERROR'), array('icon'=>'alert', 'type'=>'error')).'</div>';
		}
	}
}
