<?php
/**
* @package		Joomla & QuickForm
* @Copyright ((c) juice-lab.ru
* @license    GNU/GPL
*/

defined('_JEXEC') or die;

class Qf3ModelForm extends JModelAdmin
{

	public function getTable($type = 'Form', $prefix = 'Qf3Table', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm('com_qf3.form', 'form', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState('com_qf3.edit.form.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		$this->preprocessData('com_qf3.form', $data);

		return $data;
	}

	protected function prepareTable($table)
	{
		$input = JFactory::getApplication()->input;
		$table->title = htmlspecialchars_decode($table->title, ENT_QUOTES);
		$table->fields = $input->get( "fields", '', 'RAW');
		if (empty($table->id))
		{
			$table->projectid = $input->getInt( "projectid");
		}
	}


	public function setHome($id = 0)
	{
		$table = $this->getTable();
		$user	= JFactory::getUser();
		$db		= $this->getDbo();

		if (!$user->authorise('core.edit.state', 'com_qf3')) {
			throw new Exception(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
		}

		if (!$table->load((int)$id)) {
			throw new Exception(JText::_('ERROR'));
		}

		$db->setQuery(
			'UPDATE #__qf3_forms' .
			' SET def = 0' .
			' WHERE projectid = '.(int) $table->projectid
		);

		if (!$db->query()) {
			throw new Exception($db->getErrorMsg());
		}

		$db->setQuery(
			'UPDATE #__qf3_forms' .
			' SET def = \'1\'' .
			' WHERE id = '.(int) $id
		);

		if (!$db->query()) {
			throw new Exception($db->getErrorMsg());
		}

		// Clean the cache.
		$this->cleanCache();

		return true;
	}

}
