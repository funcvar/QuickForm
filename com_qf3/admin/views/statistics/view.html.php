<?php
/**
* @package		Joomla & QuickForm
* @Copyright ((c) juice-lab.ru
* @license    GNU/GPL
*/

defined('_JEXEC') or die;

class Qf3ViewStatistics extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;

	public function display($tpl = null)
	{
		$this->state		= $this->get('State');
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');

		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		require_once JPATH_COMPONENT.'/helpers/qf3.php';

		Qf3Helper::addSubmenu('statistics');

		$this->addToolbar();
		$this->sidebar = JHtmlSidebar::render();

		parent::display($tpl);
	}

	protected function addToolbar()
	{
		$canDo = JHelperContent::getActions('com_qf3');
		JToolbarHelper::title(JText::_('QF_HISTORY_LIST'), 'bookmark banners');

		if ($canDo->get('core.edit.state')) {
			JToolBarHelper::divider();
			JToolBarHelper::checkin('statistics.checkin');
		}

		if ($canDo->get('core.delete'))
		{
			JToolbarHelper::divider();
			JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'statistics.delete');
		}

		if ($canDo->get('core.admin'))
		{
			JToolbarHelper::preferences('com_qf3');
		}

		JToolbarHelper::divider();
		JToolbarHelper::help('', false, '/administrator/index.php?option=com_qf3&task=help');

		JHtmlSidebar::setAction('index.php?option=com_qf3&view=statistics');

		JHtmlSidebar::addFilter(
			JText::_('QF_SELECT_STATUS'),
			'filter_st_status',
			JHtml::_('select.options', Qf3Helper::getStatus(), 'value', 'text', $this->state->get('filter.st_status'), true)
		);
	}

}
