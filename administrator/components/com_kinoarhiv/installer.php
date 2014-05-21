<?php defined('_JEXEC') or die;

@set_time_limit(0);

class com_kinoarhivInstallerScript {
	public function postflight($type, $parent) {
		JFactory::getApplication()->redirect('index.php?option=com_kinoarhiv&task=settings');
	}
}
