<?php
/**
* @Copyright ((c) plasma-web.ru
        * @license    GPLv2 or later
        */

namespace QuickForm;

\defined('QF3_VERSION') or die;

abstract class qfEmail extends qfFilds
{
    public static function getEmailHtml($project, $data, $calculator)
    {
        $file = __DIR__.'/email/'.$project->params->tmpl.'.php';
        if (file_exists($file)) {
            require_once($file);
        } else {
            exit('email template not found');
        }

        $qfEmail_tmpl =  new qfEmail_tmpl();
        $html = $qfEmail_tmpl->getTmpl($project, $data, $calculator);
        return $html;
    }

    public function checkUrl()
    {
        $link = filter_var(qf::get('root', $_POST), FILTER_SANITIZE_STRING);
        $host =  ((! empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
        if(strpos($link, $host) === 0) {
            return '<br>QF_SOURCE' . ': <a href="' . $link . '">'.$link.'</a><br><br>';
        }
        return '';
    }
}
