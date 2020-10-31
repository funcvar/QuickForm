<?php
/**
* @package		Joomla & QuickForm 3
* @Copyright ((c) juice-lab.ru
* @license    GNU/GPL
*/

defined('_JEXEC') or die;

class Qf3Controller extends JControllerLegacy
{
	protected $default_view = 'projects';

	public function display($cachable = false, $urlparams = array())
	{
		require_once JPATH_COMPONENT . '/helpers/qf3.php';

		if($this->input->get('task')=='ajax'){
			require_once JPATH_COMPONENT.'/helpers/ajax.php';
			exit;
		}

		if($this->input->get('task')=='help'){
			echo JText::_('QF_HELP_WINDOW');
			exit;
		}

		$view   = $this->input->get('view', 'projects');
		$layout = $this->input->get('layout', 'default');
		$id     = $this->input->getInt('id');

		if ($view == 'form' && $layout == 'edit' && !$this->checkEditId('com_qf3.edit.form', $id))
		{
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_qf3&view=projects', false));

			return false;
		}


		return parent::display();
	}

}
