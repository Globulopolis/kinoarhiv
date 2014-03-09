<?php defined('_JEXEC') or die;

JLoader::register('DatabaseHelper', JPATH_COMPONENT.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'database.php');

class KinoarhivModelPremieres extends JModelList {
	protected $context = null;

	public function __construct($config = array()) {
		parent::__construct($config);

		$this->context = strtolower($this->option.'.'.$this->getName().'.premieres');
	}

	public function getListQuery() {
		$db = $this->getDBO();

		$query = $db->getQuery(true);

		$query->select('`id`, `movie_id`, `vendor_id`, `premiere_date`, `country_id`, `info`, `ordering`')
			->from($db->quoteName('#__ka_premieres'));

		return $query;
	}

	public function savePremiere() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$id = $app->input->get('id', null, 'int');
		$movie_id = $app->input->get('movie_id', null, 'int');
		$data = $app->input->get('form', array(), 'array');
		$is_new = $app->input->get('new', 0, 'int');

		if (isset($data['p_vendor_id'][0]) && !empty($data['p_vendor_id'][0])) {
			if (count($data['p_country_id']) > 1) {
				$country_id = $data['p_country_id'][1];
			} else {
				$country_id = $data['p_country_id'][0];
			}

			// Prevent duplicates
			$db->setQuery("SELECT COUNT(`id`) FROM ".$db->quoteName('#__ka_premieres')." WHERE `movie_id` = ".$movie_id." AND `vendor_id` = ".$data['p_vendor_id'][0]." AND `country_id` = ".$country_id);
			$c = $db->loadResult();

			if ($c > 0 && $is_new != 0) {
				return array('success'=>false, 'message'=>'Error');
			}

			if ($is_new == 1) {
				$db->setQuery("INSERT INTO ".$db->quoteName('#__ka_premieres')." (`id`, `movie_id`, `vendor_id`, `premiere_date`, `country_id`, `info`, `ordering`)"
					. "\n VALUES ('', '".$movie_id."', '".$data['p_vendor_id'][0]."', '".$data['p_premiere_date']."', '".$country_id."', '".$db->escape($data['p_info'])."', '".$data['p_ordering']."')");
			} else {
				$db->setQuery("UPDATE ".$db->quoteName('#__ka_premieres')
					. "\n SET `vendor_id` = '".$data['p_vendor_id'][0]."', `premiere_date` = '".$data['p_premiere_date']."', `country_id` = '".$country_id."', `info` = '".$db->escape($data['p_info'])."', `ordering` = '".$data['p_ordering']."'"
					. "\n WHERE `id` = ".(int)$id);
			}

			try {
				$db->execute();
				return array('success'=>true, 'message'=>JText::_('COM_KA_SAVED'));
			} catch(Exception $e) {
				return array('success'=>false, 'message'=>$e->getMessage());
			}
		} else {
			return array('success'=>false, 'message'=>JText::_('COM_KA_FIELD_PREMIERE_VENDOR_REQUIRED'));
		}
	}

	public function deletePremieres() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$data = $app->input->post->get('data', array(), 'array');
		$query = true;

		$db->setDebug(true);
		$db->lockTable('#__ka_premieres');
		$db->transactionStart();

		foreach ($data as $key=>$value) {
			$_name = explode('_', $value['name']);
			$item_id = $_name[3];

			$db->setQuery("DELETE FROM ".$db->quoteName('#__ka_premieres')." WHERE `id` = ".(int)$item_id.";");
			$result = $db->execute();

			if ($result === false) {
				$query = false;
				break;
			}
		}

		if ($query === false) {
			$db->transactionRollback();
		} else {
			$db->transactionCommit();
		}

		$db->unlockTables();
		$db->setDebug(false);

		if ($query) {
			$success = true;
			$message = JText::_('COM_KA_ITEMS_DELETED_SUCCESS');
		} else {
			$success = false;
			$message = JText::_('COM_KA_ITEMS_DELETED_ERROR');
		}

		return array('success'=>$success, 'message'=>$message);
	}
}
