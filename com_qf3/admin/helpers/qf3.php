<?php
/**
* @package		Joomla & QuickForm
* @Copyright ((c) juice-lab.ru
* @license    GNU/GPL
*/
defined('_JEXEC') or die;

class Qf3Helper
{
	public static function addSubmenu($vName)
	{
		$qf_config = JComponentHelper::getParams('com_qf3');

		JHtmlSidebar::addEntry(
			JText::_('QF_PROGECTS_LIST'),
			'index.php?option=com_qf3&view=projects',
			$vName == 'projects'
		);

		if($qf_config->get('shopmod')) {
			JHtmlSidebar::addEntry(
				JText::_('QF_SHOPMOD_SET'),
				'index.php?option=com_qf3&view=shopmod',
				$vName == 'shopmod'
			);
		}

		if($qf_config->get('filesmod')) {
			JHtmlSidebar::addEntry(
				JText::_('QF_FILES_SET'),
				'index.php?option=com_qf3&view=folders',
				$vName == 'folders'
			);
		}

		JHtmlSidebar::addEntry(
			JText::_('QF_HISTORY'),
			'index.php?option=com_qf3&view=statistics',
			$vName == 'statistics'
		);

		JHtmlSidebar::addEntry(
			JText::_('QF_GLOBAL_SET'),
			'index.php?option=com_config&view=component&component=com_qf3',
			$vName == 'component'
		);
	}

	public static function getStateOptions()
	{
		$options   = array();
		$options[] = JHtml::_('select.option', '1', JText::_('JPUBLISHED'));
		$options[] = JHtml::_('select.option', '0', JText::_('JUNPUBLISHED'));
		$options[] = JHtml::_('select.option', '*', JText::_('JALL'));

		return $options;
	}

	public static function getActions($categoryId = 0)
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		$assetName = 'com_qf3';

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action) {
			$result->set($action,	$user->authorise($action, $assetName));
		}

		return $result;
	}

	public static function getStatus()
	{
		return array(
			'0' => JText::_('QF_NEW'),
			'1' => JText::_('QF_UNDERWAY'),
			'2' => JText::_('QF_ACHIEVED'),
		 );
	}

}
