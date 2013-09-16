<?php defined('_JEXEC') or die;

class KinoarhivModelSettings extends JModelForm {
	/**
	 * Method to get a form object.
	 *
	 * @param   array  $data		Data for the form.
	 * @param   boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 */
	public function getForm($data = array(), $loadData = true) {
		$form = $this->loadForm(
				'com_kinoarhiv.settings',
				'settings',
				array('control' => 'jform', 'load_data' => $loadData)
			);

		if (empty($form)) {
			return false;
		}

		return $form;
	}

	/**
	 * Get the component information.
	 *
	 * @return  object
	 */
	public function getSettings() {
		$result = JComponentHelper::getComponent('com_kinoarhiv');

		return $result;
	}

	/**
	 * Method to save the configuration data.
	 *
	 * @param   array  An array containing config data.
	 *
	 * @return  bool	True on success, false on failure.
	 */
	public function save($data) {
		//print_r($data['rules']);
		$db = $this->getDBO();
		$form_rules = $data['rules'];
		// Unset rules array because we do not need it in the component parameters
		unset($data['rules']);
		$rules = array();

		foreach ($form_rules as $rule=>$groups) {
			foreach ($groups as $group=>$value) {
				if ($value != '') {
					$rules[$rule][$group] = $value;
				} else {
					unset($form_rules[$rule][$group]);
				}
			}
		}

		$params = json_encode($data);
		$rules = json_encode($rules);

		$db->setQuery("UPDATE ".$db->quoteName('#__extensions')
			. "\n SET `params` = '".$db->escape($params)."'"
			. "\n WHERE `element` = 'com_kinoarhiv' AND `type` = 'component'");
		$result = $db->execute();

		if (!$result) {
			$this->setError($db->get('errorMsg'));
			return false;
		}

		$db->setQuery("UPDATE ".$db->quoteName('#__assets')
			. "\n SET `rules` = '".$rules."'"
			. "\n WHERE `name` = 'com_kinoarhiv' AND `level` = 1 AND `parent_id` = 1");
		$query = $db->execute();

		// Clean the component cache.
		$this->cleanCache('_system');

		return true;
	}
}
