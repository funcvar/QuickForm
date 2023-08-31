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
          $html .= strip_tags($this->checkUrl()) . "\r\n\r\n";
      }

      $html .= $this->getSimplRows($data);

      if ($project->params->calculatortype) {
          $html .= $this->getSum($calculator);
      }

      return $html;

    }

    protected function getSum($calculator)
    {
        $html = '';
        foreach ($calculator as $arr) {
            if (!$arr[1]->format) {
                $sum = number_format($arr[0], (int) $arr[1]->fixed, ',', ' ');
            } elseif ($arr[1]->format == 1) {
                $sum = number_format($arr[0], (int) $arr[1]->fixed, '.', ',');
            } else {
                $sum = number_format($arr[0], (int) $arr[1]->fixed, '.', '');
            }

            $html .= $arr[1]->label . ' ';
            if ($arr[1]->pos) {
                $html .= $sum . ' ' . $arr[1]->unit . "\r\n";
            } else {
                $html .= $arr[1]->unit . ' ' . $sum . "\r\n";
            }
        }

        return $html;
    }

}
