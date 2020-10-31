<?php
/**
* @package		Joomla & QuickForm
* @Copyright ((c) juice-lab.ru
* @license    GNU/GPL
*/
defined('_JEXEC') or die;

class Qf3ViewStatistic extends JViewLegacy
{
	protected $form;
	protected $item;
	protected $state;

	public function display($tpl = null)
	{
		$this->form  = $this->get('Form');
		$this->item  = $this->get('Item');
		$this->state = $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		$this->addToolbar();

		return parent::display($tpl);
	}

	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);
		$user       = JFactory::getUser();
		$userId     = $user->id;

		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $userId);
		$canDo = JHelperContent::getActions('com_qf3');
		JToolbarHelper::title(JText::_('QF_HISTORY'), 'bookmark banners');

		if (!$checkedOut)
		{
			if ($canDo->get('core.edit'))
			{
				JToolbarHelper::apply('statistic.apply');
				JToolbarHelper::save('statistic.save');
			}
		}

		JToolbarHelper::cancel('statistic.cancel', 'JTOOLBAR_CLOSE');

		JToolbarHelper::divider();
		JToolbarHelper::help('', false, '/administrator/index.php?option=com_qf3&task=help');
	}

}
