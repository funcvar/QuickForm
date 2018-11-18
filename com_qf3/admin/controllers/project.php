<?php
/**
* @package		Joomla & QuickForm
* @Copyright ((c) juice-lab.ru
* @license    GNU/GPL
*/

defined('_JEXEC') or die;

class Qf3ControllerProject extends JControllerForm
{
	public function canceltofields()
	{
		$this->view_list = 'forms&projectid='.$this->input->get('id');
		return $this->cancel();
	}

}
