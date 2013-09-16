<?php defined('_JEXEC') or die;

@ini_set('zend.ze1_compatibility_mode', 'Off');

$params = JComponentHelper::getParams('com_kinoarhiv');
JLoader::register('GlobalHelper', dirname(__FILE__).DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'global.php');

if ($params->get('offline') == 1) {
	$document = JFactory::getDocument();
	$user = JFactory::getUser();

	$doc_params = array(
		'template' => 'system',
		'file' => 'offline.php',
		'directory' => JPATH_THEMES,
		'params' => array()
	);

	if ($user->get('isRoot')) {
		echo GlobalHelper::showMsg(JText::_('COM_KA_OFFLINE_MESSAGE'), array('icon'=>'alert', 'type'=>'error'))."<br />";
	} else {
		$document->parse($doc_params);
		header('HTTP/1.1 503 Service Unavailable');
		
		echo $document->render(false, $doc_params);
		jexit();
	}
}

GlobalHelper::setHeadTags();

require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'controller.php');
$input = JFactory::getApplication()->input;

if($controller = $input->get('controller', null, 'word')) {
	$path = JPATH_COMPONENT.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.$controller.'.php';
	if (file_exists($path)) {
		require_once $path;
	} else {
		$controller = '';
	}
}

$classname = 'KinoarhivController'.$controller;
$controller = new $classname();
$controller->execute($input->get('task', 'display', 'cmd'));
$controller->redirect();
