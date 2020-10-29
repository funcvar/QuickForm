<?php
/**
* @package		Joomla & QuickForm
* @Copyright ((c) plasma-web.ru
* @license    GNU/GPL
*/
defined('_JEXEC') or die;
JFormHelper::loadFieldClass('list');

class JFormFieldTmpllist extends JFormFieldList
{
    protected $type = 'tmpllist';

    protected function getOptions()
    {
        $tmpls  = scandir(JPATH_COMPONENT_SITE.'/classes/email');
        for ($i=0, $n=count($tmpls); $i < $n; $i++) {
            if (substr($tmpls[$i], strrpos($tmpls[$i], '.') + 1)=='php') {
                $tmpl = substr($tmpls[$i], 0, strrpos($tmpls[$i], '.'));
                if($tmpl == 'default') {
                    $sections[] = JHTML::_('select.option', 'default', 'default (table output)');
                }
                elseif($tmpl == 'json') {
                    $sections[] = JHTML::_('select.option', 'json', 'json (for developers)');
                }
                elseif($tmpl == 'simplehtml') {
                    $sections[] = JHTML::_('select.option', 'simplehtml', 'simple (may contain html)');
                }
                elseif($tmpl == 'simple') {
                    $sections[] = JHTML::_('select.option', 'simple', 'simple (without html)');
                }
                else $sections[] = JHTML::_('select.option', $tmpl, $tmpl);
            }
        }
        return $sections;
    }
}
