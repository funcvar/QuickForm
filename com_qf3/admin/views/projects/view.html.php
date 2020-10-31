<?php
/**
* @package		Joomla & QuickForm
* @Copyright ((c) juice-lab.ru
* @license    GNU/GPL
*/


defined('_JEXEC') or die;

class Qf3ViewProjects extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;

	public function display($tpl = null)
	{
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');

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
		JToolbarHelper::title(JText::_('QF_PROGECTS_LIST'), 'bookmark banners');

		if ($canDo->get('core.create'))
		{
			JToolbarHelper::addNew('project.add');
		}

		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::divider();
			JToolbarHelper::publish('projects.publish');
			JToolbarHelper::unpublish('projects.unpublish');
		}

		if ($canDo->get('core.delete'))
		{
			JToolbarHelper::divider();
			JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'projects.delete');
		}

		if ($canDo->get('core.admin'))
		{
			JToolbarHelper::preferences('com_qf3');
		}

		JToolbarHelper::divider();
		JToolbarHelper::help('', false, '/administrator/index.php?option=com_qf3&task=help');

		JHtmlSidebar::setAction('index.php?option=com_qf3&view=projects');

		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_PUBLISHED'),
			'filter_published',
			JHtml::_('select.options', Qf3Helper::getStateOptions(), 'value', 'text', $this->state->get('filter.published'), true)
		);

		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_ACCESS'),
			'filter_access',
			JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'))
		);

		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_LANGUAGE'),
			'filter_language',
			JHtml::_('select.options', JHtml::_('contentlanguage.existing', true, true), 'value', 'text', $this->state->get('filter.language'))
		);

	}

}
