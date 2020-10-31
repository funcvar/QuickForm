<?php
/**
 * @package		Joomla & QuickForm
* @Copyright ((c) plasma-web.ru
        * @license    GNU/GPL
        */

defined('_JEXEC') or die;

class Qf3ViewForms extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;

	public function display($tpl = null)
	{
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->projectTitle  = $this->get('projectTitle');

		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		require_once JPATH_COMPONENT.'/helpers/qf3.php';
		Qf3Helper::addSubmenu('projects');

		$this->addToolbar();
		$this->sidebar = JHtmlSidebar::render();

		return parent::display($tpl);
	}


	protected function addToolbar()
	{
		$canDo = JHelperContent::getActions('com_qf3');
		JToolbarHelper::title('QuickForm 3: ' . JText::_('QF_FIELD_GROUPS'), 'bookmark banners');

		if ($canDo->get('core.create'))
		{
			JToolbarHelper::addNew('form.add');
		}

		if ($canDo->get('core.delete'))
		{
			JToolbarHelper::divider();
			JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'forms.delete');
		}

		if ($canDo->get('core.admin'))
		{
			JToolbarHelper::preferences('com_qf3');
		}

		JToolbarHelper::divider();
		JToolbarHelper::help('', false, '/administrator/index.php?option=com_qf3&task=help');

		JHtmlSidebar::setAction('index.php?option=com_qf3&view=forms');
	}

	protected function getSortFields()
	{
		return array(
			'ordering' => JText::_('JGRID_HEADING_ORDERING'),
			'a.id' => JText::_('JGRID_HEADING_ID')
		);
	}
}
