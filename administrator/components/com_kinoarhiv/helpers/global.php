<?php defined('_JEXEC') or die;

class GlobalHelper {
	static function setHeadTags() {
		$document = JFactory::getDocument();
		$params = JComponentHelper::getParams('com_kinoarhiv');

		if ($document->getType() != 'html') {
			return;
		}

		$document->addHeadLink(JURI::base().'components/com_kinoarhiv/assets/css/style.css', 'stylesheet', 'rel', array('type'=>'text/css'));
		$document->addHeadLink(JURI::base().'components/com_kinoarhiv/assets/css/plugins.css', 'stylesheet', 'rel', array('type'=>'text/css'));
		$document->addHeadLink(JURI::root().'components/com_kinoarhiv/assets/themes/ui/'.$params->get('ui_theme').'/jquery-ui.css', 'stylesheet', 'rel', array('type'=>'text/css'));
		JHtml::_('jquery.framework');
		JHtml::_('script', JURI::root().'components/com_kinoarhiv/assets/js/jquery-ui.min.js');
	}

	/**
	 * Strip text to 'limit' of 'chars'
	 *
	 * @param   string  $text		Text for limit.
	 * @param   integer	$limit		Number of chars.
	 * @param   string	$end_chr	End symbol. Default ASCII char code
	 *
	 * @return  string
	 *
	*/
	static function limitText($text, $limit=400, $end_chr='&#8230;') {
		if (JString::strlen($text = $text) <= $limit) return $text;

		return JString::substr($text, 0, $limit - 3).$end_chr;
	}

	static public function getRemoteData($url, $headers=null, $timeout=30, $transport='curl') {
		$options = new JRegistry;

		$http = JHttpFactory::getHttp($options, $transport);
		$response = $http->get($url, $headers, $timeout);

		return $response;
	}
}
