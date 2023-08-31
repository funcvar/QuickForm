<?php
/**
* @Copyright ((c) plasma-web.ru
        * @license    GPLv2 or later
        */

namespace QuickForm;

\defined('QF3_VERSION') or die;

class qfEmail_tmpl extends qfEmail
{
    public function getTmpl($project, $data, $calculator)
    {
        $html = '';

        if ($project->params->showurl) {
            $html .= $this->checkUrl();
        }

        if ($project->params->calculatortype) {
            $data ['sum'] = $calculator;
        }
        $html .= json_encode($data);
        return $html;
    }
}
