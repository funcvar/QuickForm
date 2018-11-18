<?php
/**
 * @package		Joomla & QuickForm
* @Copyright ((c) plasma-web.ru
        * @license    GNU/GPL
        */

defined('_JEXEC') or die();

abstract class qfEmail
{
    public static function getEmailHtml($project, $data, $calculator)
    {
        $file = JPATH_COMPONENT.'/classes/email/'.$project->emailparams->tmpl.'.php';
        if (file_exists($file)) {
            require_once($file);
        } else {
            jexit('email template not found');
        }

        $qfEmail_tmpl =  new qfEmail_tmpl;
        $html = $qfEmail_tmpl->getTmpl($project, $data, $calculator);
        return $html;
    }

    public function mlangLabel($val)
    {
        if (strpos($val, 'QF_')===0) {
            return JText::_($val);
        }
        return $val;
    }

    protected function letLable($field)
    {
        if (!$field->label) {
            if (isset($field->placeholder) && $field->placeholder) {
                return $field->placeholder;
            }
        }
        return $field->label;
    }
}
