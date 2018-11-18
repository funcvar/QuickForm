<?php
/**
* @package		Joomla & QuickForm
* @Copyright ((c) juice-lab.ru
* @license    GNU/GPL
*/
defined('_JEXEC') or die;
JFormHelper::loadFieldClass('list');

class JFormFieldJsfile extends JFormFieldList
{
    protected $type = 'Jsfile';

    protected function getOptions()
    {
			$js  = scandir(JPATH_COMPONENT_SITE.'/assets/js');
			for ($i=0, $n=count($js); $i < $n; $i++) {
				if(substr($js[$i], strrpos($js[$i], '.') + 1)=='js'){
					$sections[] = JHTML::_('select.option',  $js[$i], $js[$i]);
				}
			}
			return $sections;
    }

}
