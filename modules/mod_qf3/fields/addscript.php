<?php
/**
* @package		Joomla
* @Copyright ((c) bigemot.ru
* @license    GNU/GPL
*/
defined('JPATH_PLATFORM') or die;

class JFormFieldAddscript extends JFormField
{
    protected $type = 'Addscript';

    protected function getInput()
    {
        $doc = JFactory::getDocument();
        $doc->addScript(JURI::root(true) . "/modules/mod_qf3/qf_mod.js");
    }
}
