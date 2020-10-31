<?php
/**
* @package		Joomla & QuickForm
* @Copyright ((c) juice-lab.ru
* @license    GNU/GPL
*/
defined('_JEXEC') or die;

class Qf3ViewProject extends JViewLegacy
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
		$isNew      = ($this->item->id == 0);
		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $userId);

		$canDo = JHelperContent::getActions('com_qf3');
		JToolbarHelper::title($isNew ? JText::_('QF_ADD_PROGECT') : JText::_('QF_EDIT_PROGECT'), 'bookmark banners');

		if ($isNew)
		{
			if ($isNew && $canDo->get('core.create'))
			{
				JToolbarHelper::apply('project.apply');
				JToolbarHelper::save('project.save');
				JToolbarHelper::save2new('project.save2new');
			}

			JToolbarHelper::cancel('project.cancel');
		}
		else
		{
			if (!$checkedOut)
			{
				if ($canDo->get('core.edit'))
				{
					JToolbarHelper::apply('project.apply');
					JToolbarHelper::save('project.save');

					if ($canDo->get('core.create'))
					{
						JToolbarHelper::save2new('project.save2new');
					}
				}
			}

			JToolbarHelper::cancel('project.cancel', 'JTOOLBAR_CLOSE');
			JToolBarHelper::custom('project.canceltofields', 'cancel', 'cancel', 'QF_JTOOLBAR_CLOSE', false, false );
		}

		JToolbarHelper::divider();
		JToolbarHelper::help('', false, '/administrator/index.php?option=com_qf3&task=help');
	}

}
