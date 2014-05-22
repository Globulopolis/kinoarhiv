<?php defined('_JEXEC') or die;

@set_time_limit(0);

class com_kinoarhivInstallerScript {
	public function postflight($type, $parent) {
		// Need to add default parameters for the component after install
		JFactory::getApplication()->redirect('index.php?option=com_kinoarhiv&view=settings');
	}
}
