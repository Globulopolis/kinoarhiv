<?php defined('_JEXEC') or die;

class KinoarhivModelNames extends JModelList {
	public function quickSave() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();

		// We need set alias for quick save on movie page
		$name = 'n_name';
		$latin_name = 'n_latin_name';
		$date_of_birth = 'n_date_of_birth';
		$ordering = 'n_ordering';
		$language = 'n_language';

		$data = $app->input->getArray(array(
			'form'=>array(
				$name=>'string', $latin_name=>'string', $date_of_birth=>'string', $ordering=>'int', $language=>'string'
			)
		));
		$name = $data['form']['n_name'];
		$latin_name = $data['form']['n_latin_name'];
		$alias = $name != '' ? $name : $latin_name;
		$date_of_birth = (empty($data['form']['n_date_of_birth']) && $data['form']['n_date_of_birth'] == '0000-00-00') ? date('Y-m-d') : $data['form']['n_date_of_birth'];
		$ordering = empty($data['form']['n_ordering']) ? 0 : $data['form']['n_ordering'];
		$metadata = json_encode(array('tags'=>array(), 'robots'=>''));
		$language = empty($data['form']['n_language']) ? '*' : $data['form']['n_language'];

		if (empty($name) && empty($latin_name)) {
			return array('success'=>false, 'message'=>JText::_('COM_KA_REQUIRED'));
		}

		$db->setQuery("INSERT INTO ".$db->quoteName('#__ka_names')." (`id`, `asset_id`, `name`, `latin_name`, `alias`, `url_photo`, "
			. "\n `date_of_birth`, `date_of_death`, `birthplace`, `birthcountry`, `gender`, `height`, `desc`, `ordering`, `state`, "
			. "\n `access`, `metakey`, `metadesc`, `metadata`, `language`)"
			. "\n VALUES ('', '0', '".$db->escape($name)."', '".$db->escape($latin_name)."', '".JFilterOutput::stringURLSafe($alias)."', '', "
			. "\n '".$date_of_birth."', '', '', '', '', '', '', '".(int)$ordering."', '1', '1', '', '', '".$metadata."', '".$language."')");
		$query = $db->execute();

		if ($query !== true) {
			return array('success'=>false, 'message'=>JText::_('JERROR_AN_ERROR_HAS_OCCURRED'));
		} else {
			$insertid = $db->insertid();
			$rules = json_encode((object)array());

			$db->setQuery("SELECT MAX(`rgt`) + 1 FROM ".$db->quoteName('#__assets'));
			$lft = $db->loadResult();

			$db->setQuery("SELECT `id` FROM ".$db->quoteName('#__assets')." WHERE `name` = 'com_kinoarhiv' AND `parent_id` = 1 AND `level` = 1");
			$parent_id = $db->loadResult();

			$db->setQuery("INSERT INTO ".$db->quoteName('#__assets')." (`id`, `parent_id`, `lft`, `rgt`, `level`, `name`, `title`, `rules`)"
				. "\n VALUES ('', '".$parent_id."', '".$lft."', '".($lft+1)."', '1', 'com_kinoarhiv.name.".(int)$insertid."', '".$alias."', '".$rules."')");
			$assets_query = $db->execute();
			$assets_id = $db->insertid();

			$db->setQuery("UPDATE ".$db->quoteName('#__ka_names')." SET `asset_id` = '".$assets_id."' WHERE `id` = ".$insertid);
			$update_query = $db->execute();

			return array(
				'success'	=> true,
				'message'	=> JText::_('COM_KA_ITEMS_SAVE_SUCCESS'),
				'data'		=> array('id'=>$insertid, 'name'=>$name, 'latin_name'=>$latin_name)
			);
		}
	}
}
