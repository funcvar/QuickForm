<?php
/**
* @package		Joomla & QuickForm
* @Copyright ((c) juice-lab.ru
* @license    GNU/GPL
*/
defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

class JFormFieldStatus2 extends JFormFieldList
{
    protected $type = 'Status2';

    protected function getOptions()
    {
			require_once JPATH_COMPONENT.'/helpers/qf3.php';
			$st_status = Qf3Helper::getStatus();
			foreach($st_status as $k=>$v){
				$sections[] = JHTML::_('select.option',  $k, $v);
			}
			return $sections;
    }

}
