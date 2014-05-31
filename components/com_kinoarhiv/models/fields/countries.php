<?php defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

class JFormFieldCountries extends JFormFieldList {
	public $type = 'Countries';
	protected static $options = array();

	protected function getOptions() {
		$hash = md5($this->element);
		static::$options[$hash] = parent::getOptions();
		$options = array();
		$def_options = array();
		$name = (string)$this->element['name'];
		$db = JFactory::getDbo();
		$lang = JFactory::getLanguage();

		$query = $db->getQuery(true)
			->select('`id` AS `value`, `name` AS `text`')
			->from('#__ka_countries')
			->where('`state` = 1 AND `language` IN ('.$db->quote($lang->getTag()).','.$db->quote('*').')')
			->order('`name` ASC');

		$db->setQuery($query);

		try {
			$options = $db->loadObjectList();
		} catch (RuntimeException $e) {
			GlobalHelper::eventLog($e->getMessage());
			return false;
		}

		$def_options[] = array(
			'value' => 0,
			'text' => '-'
		);

		static::$options[$hash] = array_merge(static::$options[$hash], $def_options, $options);

		return static::$options[$hash];
	}
}
