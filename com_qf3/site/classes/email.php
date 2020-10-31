<?php
/**
 * @package		Joomla & QuickForm
* @Copyright ((c) plasma-web.ru
        * @license    GNU/GPL
        */

defined('_JEXEC') or die();

abstract class qfEmail extends qfFilds
{
    public static function getEmailHtml($project, $data, $calculator)
    {
        $file = __DIR__.'/email/'.$project->emailparams->tmpl.'.php';
        if (file_exists($file)) {
            require_once($file);
        } else {
            exit('email template not found');
        }

        $qfEmail_tmpl =  new qfEmail_tmpl;
        $html = $qfEmail_tmpl->getTmpl($project, $data, $calculator);
        return $html;
    }

    protected function findLable($field)
    {
        return $field->label ? $field->label : $this->get('placeholder', $field);
    }
}
