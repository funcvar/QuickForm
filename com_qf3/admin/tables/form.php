<?php
/**
* @package		Joomla & QuickForm
* @Copyright ((c) juice-lab.ru
* @license    GNU/GPL
*/
defined('_JEXEC') or die;

class Qf3TableForm extends JTable
{

	function __construct(& $db) {
		parent::__construct('#__qf3_forms', 'id', $db);
	}

	function bind($array, $ignore = '')
	{
		if (isset($array['params']) && is_array($array['params'])) {
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = (string)$registry;
		}

		return parent::bind($array, $ignore);
	}

	public function store($updateNulls = false)
	{
		if ($this->id) {
//			// Existing item
		} else {
			$this->ordering = $this->getNextOrder();
			$this->setDef();
		}
		return parent::store($updateNulls);
	}

	public function setDef()
	{
		$this->_db->setQuery(
			'SELECT id FROM #__qf3_forms' .
			' WHERE def = 1'  .
			' AND projectid = '.(int) $this->projectid
		);
		if(!$this->_db->loadResult()){
			$this->def = 1;
		}
	}

	public function check()
	{
		if (trim($this->title) == '') {
			$this->setError(JText::_('Error. Title not found'));
			return false;
		}

		return true;
	}


}
