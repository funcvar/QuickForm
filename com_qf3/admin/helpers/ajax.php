<?php
/**
* @package		Joomla & QuickForm
* @Copyright ((c) juice-lab.ru
* @license    GNU/GPL
*/

defined('_JEXEC') or die;

$input = JFactory::getApplication()->input;
switch($input->get( 'mod' ))
{
	case 'jtext' :
	{
		echo	JText::_($input->get('str'));
	}
}
