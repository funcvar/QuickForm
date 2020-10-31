<?php
defined('_JEXEC') or die;


class Qf3ViewFolders extends JViewLegacy
{
	public function display($tpl = null)
	{
        require_once JPATH_COMPONENT.'/helpers/qf3.php';
		Qf3Helper::addSubmenu('folders');

        $this->addToolbar();
        $this->sidebar = JHtmlSidebar::render();

        return parent::display($tpl);
	}

	protected function addToolbar()
	{
        JToolbarHelper::title('QuickForm 3: ' . JText::_('QF_DEL_FILES'), 'bookmark banners');
        JHtmlSidebar::setAction('index.php?option=com_qf3&view=folders');
		JToolbarHelper::divider();
		JToolbarHelper::help('', false, '/administrator/index.php?option=com_qf3&task=help');

	}
}
