<?php
/**
* @package		Joomla & QuickForm
* @Copyright ((c) juice-lab.ru
* @license    GNU/GPL
*/
defined('_JEXEC') or die;
JFormHelper::loadFieldClass('list');

class JFormFieldCartcss extends JFormFieldList
{
    protected $type = 'cartcss';

    protected function getOptions()
    {
			$sections[] = JHTML::_('select.option',  'none', '');
			$css  = scandir(JPATH_SITE.'/modules/mod_qf3/css');
			for ($i=0, $n=count($css); $i < $n; $i++) {
				if(substr($css[$i], strrpos($css[$i], '.') + 1)=='css'){
					$sections[] = JHTML::_('select.option',  $css[$i], $css[$i]);
				}
			}
			return $sections;
    }

}
