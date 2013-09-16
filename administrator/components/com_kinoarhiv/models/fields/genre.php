<?php defined('JPATH_BASE') or die;

JFormHelper::loadFieldClass('list');

class JFormFieldGenre extends JFormFieldList {
	public $type = 'Genre';
	protected $comParams = null;

	public function __construct() {
		parent::__construct();

		// Load com_kinoarhiv config
		$this->comParams = JComponentHelper::getParams('com_kinoarhiv');
	}

	protected function getInput() {
		if (!is_array($this->value) && !empty($this->value)) {
			if (is_string($this->value)) {
				$this->value = explode(',', $this->value);
			}
		}

		$input = parent::getInput();

		return $input;
	}

	protected function getOptions() {
		$options = array();
		$name = (string)$this->element['name'];
		$lang = JFactory::getLanguage();

		$db	= JFactory::getDBO();
		$query = $db->getQuery(true)
			->select('`id` AS `value`, `name` AS `text`, `state` AS `published`')
			->from('#__ka_genres');

		// Filter language
		$query->where('`language` IN ('.$db->quote($lang->getTag()).','.$db->quote('*').')');

		// Filter on the published state
		$query->where('`state` IN (0,1)');

		$query->order('`name` ASC');

		// Get the options.
		$db->setQuery($query);

		try {
			$options = $db->loadObjectList();
		} catch (RuntimeException $e) {
			return false;
		}

		$options[] = (object)array(
			'value' => 0,
			'text' => JText::_('JALL'),
			'published' => 1
		);

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
