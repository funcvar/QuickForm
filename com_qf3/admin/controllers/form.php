<?php
/**
* @package		Joomla & QuickForm
* @Copyright ((c) juice-lab.ru
* @license    GNU/GPL
*/

defined('_JEXEC') or die;

class Qf3ControllerForm extends JControllerForm
{
	public function __construct($config = array())
	{
		parent::__construct($config);

		if ($this->input->get('projectid'))
		{
			$this->view_list = 'forms&projectid='.$this->input->get('projectid');
			$this->view_item = 'form&projectid='.$this->input->get('projectid');
		}
	}

}
