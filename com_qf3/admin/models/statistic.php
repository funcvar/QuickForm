<?php
/**
* @package		Joomla & QuickForm
* @Copyright ((c) juice-lab.ru
* @license    GNU/GPL
*/
defined('_JEXEC') or die;

class Qf3ModelStatistic extends JModelAdmin
{
	public function getTable($type = 'Statistic', $prefix = 'Qf3Table', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm('com_qf3.statistic', 'statistic', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState('com_qf3.edit.statistic.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		$this->preprocessData('com_qf3.statistic', $data);

		return $data;
	}

	protected function prepareTable($table)
	{
		$table->st_desk = htmlspecialchars_decode($table->st_desk, ENT_QUOTES);
	}
}
