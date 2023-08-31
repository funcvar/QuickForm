<?php
/**
* @Copyright ((c) juice-lab.ru
* @license    GNU/GPL
*/

namespace QuickForm;

defined('_JEXEC') or die;

class Qf3Helper
{
	public static function addSubmenu($vName)
	{
		\JHtmlSidebar::addEntry(
			Text::_('QF_PROGECTS_LIST'),
			'index.php?option=com_qf3&view=projects',
			$vName == 'projects'
		);

		if(qf::conf()->get('shopmod')) {
			\JHtmlSidebar::addEntry(
				Text::_('QF_SHOP_SETTINGS'),
				'index.php?option=com_qf3&view=shop',
				$vName == 'shop'
			);
		}

		if(qf::conf()->get('filesmod')) {
			\JHtmlSidebar::addEntry(
				Text::_('QF_ATTACHMENT_SETTINGS'),
				'index.php?option=com_qf3&view=attachment',
				$vName == 'attachment'
			);
		}

		\JHtmlSidebar::addEntry(
			Text::_('QF_EMAIL_HISTORY'),
			'index.php?option=com_qf3&view=historys',
			$vName == 'historys'
		);

		\JHtmlSidebar::addEntry(
			Text::_('QF_GLOBAL_SETTINGS'),
			'index.php?option=com_qf3&view=settings',
			$vName == 'settings'
		);
	}

}
