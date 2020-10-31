<?php
/**
* @package		Joomla & QuickForm
* @Copyright ((c) juice-lab.ru
* @license    GNU/GPL
*/
defined('_JEXEC') or die;

class Qf3ViewShopmod extends JViewLegacy
{
    protected $form;
	protected $item;

	public function display($tpl = null)
	{
        $this->form  = $this->get('Form');
		$this->item  = $this->get('Item');

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
		$canDo = JHelperContent::getActions('com_qf3');
		JToolbarHelper::title(JText::_('QF_SHOPMOD_SET'), 'bookmark banners');

		if ($canDo->get('core.edit'))
		{
			JToolbarHelper::apply('shopmod.qfapply');
			JToolbarHelper::save('shopmod.qfsave');
		}

		JToolbarHelper::cancel('shopmod.cancel', 'JTOOLBAR_CLOSE');

		JToolbarHelper::divider();
        JToolbarHelper::help('', false, '/administrator/index.php?option=com_qf3&task=help');
	}

}
